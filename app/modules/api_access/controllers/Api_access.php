<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Api_access extends MX_Controller {
    
    private $tb_users;
    private $tb_orders;
    private $tb_api_providers;
    private $api_token;
    
    public function __construct(){
        parent::__construct();
        $this->load->model(get_class($this).'_model', 'model');
        
        $this->tb_users        = USERS;
        $this->tb_orders       = ORDER;
        $this->tb_api_providers = API_PROVIDERS;
        
        // Set your secure API token here (should be stored in config or database in production)
        $this->api_token = 'd0f2218c1bc017a75de5d5732f966f4b36444af94f0c262d45f2d1a0a10a32a9';
    }
    
    /**
     * Validate API token
     */
    private function validate_token($token) {
        return $token === $this->api_token;
    }
    
    /**
     * API endpoint to update latest orders with token authentication
     * Usage: /api_access/update_orders/500?token=YOUR_API_TOKEN
     */
    public function update_orders($limit = 200){
        // Validate token
        $token = $this->input->get('token');
        if (!$this->validate_token($token)) {
            echo json_encode([
                "status" => "error",
                "message" => "Invalid or missing API token"
            ]);
            return;
        }
        
        // Start timing
        $start_time = microtime(true);
        
        // Validate and cap the limit
        $limit = (int)$limit;
        if ($limit <= 0) $limit = 200;
        if ($limit > 500) $limit = 500; // Hard cap at 500 orders
        
        // Get latest orders that need status updates
        $orders = $this->model->fetch(
            "*", 
            $this->tb_orders, 
            "api_order_id != '' AND api_provider_id > 0 AND status IN ('pending','processing','inprogress')", 
            "id", 
            "DESC", 
            0, 
            $limit
        );
        
        if (empty($orders)) {
            echo json_encode([
                "status" => "error",
                "message" => "No pending orders found to update"
            ]);
            return;
        }
        
        $updated_count = 0;
        $error_count = 0;
        $no_change_count = 0;
        $results = [];
        
        foreach ($orders as $row) {
            // Get API provider details
            $api = $this->model->get("url, key", $this->tb_api_providers, ["id" => $row->api_provider_id]);
            if (empty($api)) {
                $results[] = [
                    'order_id' => $row->id, 
                    'status' => 'error',
                    'message' => 'API Provider not found'
                ];
                $error_count++;
                continue;
            }
            
            // Make API request
            $data_post = ['key' => $api->key, 'action' => 'status', 'order' => $row->api_order_id];
            $response = json_decode($this->connect_api($api->url, $data_post));
            
            if (!$response) {
                $results[] = [
                    'order_id' => $row->id, 
                    'status' => 'error',
                    'message' => 'No response from API provider'
                ];
                $error_count++;
                continue;
            }
            
            if (!empty($response->error)) {
                $results[] = [
                    'order_id' => $row->id, 
                    'status' => 'error',
                    'message' => $response->error
                ];
                $error_count++;
                
                // Update error in database
                $this->db->update($this->tb_orders, [
                    "note" => $response->error,
                    "changed" => NOW,
                ], ["id" => $row->id]);
                continue;
            }
            
            if (isset($response->status) && $response->status != "") {
                // Standardize status values
                if (!in_array($response->status, ['Completed', 'Processing', 'In progress', 'Partial', 'Canceled', 'Refunded'])) {
                    $response->status = 'Pending';
                }
                
                $data = [];
                $old_status = $row->status;
                
                // Handle drip-feed orders
                if ($row->is_drip_feed) {
                    $status_dripfeed = (strrpos($response->status, 'progress') || strrpos(strtolower($response->status), 'active')) ? 'inprogress'
                        : strtolower(str_replace([" ", "_"], "", $response->status));
                        
                    if (!in_array($status_dripfeed, ['canceled', 'inprogress', 'completed'])) {
                        $status_dripfeed = 'inprogress';
                    }
                    
                    $data = [
                        "changed" => NOW,
                        "status" => $status_dripfeed,
                    ];
                    
                    if (isset($response->runs)) {
                        $data['sub_response_orders'] = json_encode($response);
                    } else {
                        switch ($response->status) {
                            case 'Completed':
                                $response->runs = $row->runs;
                                break;
                            case 'In progress':
                            case 'Canceled':
                                $response->runs = 0;
                                break;
                        }
                        $data['sub_response_orders'] = json_encode($response);
                    }
                    
                    // Handle child orders for drip feed
                    if (isset($response->orders)) {
                        $db_drip = json_decode($row->sub_response_orders);
                        if (isset($db_drip->orders)) {
                            $new_drip_orders = array_diff($response->orders, $db_drip->orders);
                        } else {
                            $new_drip_orders = $response->orders;
                        }
                        if (!empty($new_drip_orders)) {
                            $this->insert_order_from_dripfeed_subscription($row, $api, $new_drip_orders);
                        }
                    }
                } else {
                    // Handle regular orders
                    $remains = $response->remains;
                    if ($remains < 0) {
                        $remains = "+" . abs($remains);
                    }
                    
                    $data = [
                        "start_counter" => $response->start_count,
                        "remains" => $remains,
                        "note" => "",
                        "changed" => NOW,
                        "status" => ($response->status == "In progress") ? "inprogress" : strtolower($response->status),
                    ];
                }
                
                if (!empty($data)) {
                    // Handle refunds and compensation
                    if ($row->sub_response_posts != 1 && in_array($response->status, ["Refunded", "Canceled", "Partial"])) {
                        $data['charge'] = 0;
                        $formal_charge = 0;
                        $profit = 0;
                        $return_funds = $charge = $row->charge;
                        
                        if ($response->status == "Partial") {
                            $order_remains = $response->remains;
                            if ($row->quantity < $response->remains) {
                                $order_remains = $row->quantity;
                                $data['remains'] = $order_remains;
                            }
                            $return_funds = $charge * ($order_remains / $row->quantity);
                            $real_charge = $charge - $return_funds;
                            $formal_charge = $row->formal_charge * (1 - ($order_remains / $row->quantity));
                            $profit = $row->profit * (1 - ($order_remains / $row->quantity));
                            $data['charge'] = $real_charge;
                        }
                        
                        $data['formal_charge'] = $formal_charge;
                        $data['profit'] = $profit;
                        
                        $user = $this->model->get("id, balance", $this->tb_users, ["id" => $row->uid]);
                        if (!empty($user)) {
                            $balance = $user->balance + $return_funds;
                            $this->db->update($this->tb_users, ["balance" => $balance], ["id" => $row->uid]);
                        }
                    }
                    
                    // Update the order
                    $this->db->update($this->tb_orders, $data, ["id" => $row->id]);
                    
                    // Check if status actually changed
                    if ($old_status != $data['status']) {
                        $results[] = [
                            'order_id' => $row->id,
                            'old_status' => $old_status,
                            'new_status' => $data['status'],
                            'status' => 'success',
                            'message' => 'Status updated'
                        ];
                        $updated_count++;
                    } else {
                        $results[] = [
                            'order_id' => $row->id,
                            'status' => 'info',
                            'message' => 'No status change needed'
                        ];
                        $no_change_count++;
                    }
                }
            }
        }
        
        // Calculate execution time
        $execution_time = microtime(true) - $start_time;
        
        // Return summary response
        echo json_encode([
            "status" => "success",
            "message" => "Order status update completed",
            "summary" => [
                "total_processed" => count($orders),
                "updated" => $updated_count,
                "no_change" => $no_change_count,
                "errors" => $error_count,
                "execution_time" => round($execution_time, 2) . " seconds"
            ],
            "results" => $results
        ]);
    }
    
    /**
     * API endpoint to update specific order with token authentication
     * Usage: /api_access/update_order/12345?token=YOUR_API_TOKEN
     */
    public function update_order($order_id = null){
        // Validate token
        $token = $this->input->get('token');
        if (!$this->validate_token($token)) {
            echo json_encode([
                "status" => "error",
                "message" => "Invalid or missing API token"
            ]);
            return;
        }
        
        if ($order_id === null) {
            echo json_encode([
                "status" => "error",
                "message" => "Order ID is required"
            ]);
            return;
        }
        
        // Get order details
        $order = $this->model->get("*", $this->tb_orders, ["id" => $order_id]);
        if (empty($order)) {
            echo json_encode([
                "status" => "error",
                "message" => "Order ID #{$order_id} not found"
            ]);
            return;
        }
        
        // Check if order has API details
        if (empty($order->api_order_id) || empty($order->api_provider_id)) {
            echo json_encode([
                "status" => "error",
                "message" => "Order ID #{$order_id} is not an API order or missing API details"
            ]);
            return;
        }
        
        // Get API provider
        $api = $this->model->get("url, key", $this->tb_api_providers, ["id" => $order->api_provider_id]);
        if (empty($api)) {
            echo json_encode([
                "status" => "error",
                "message" => "API Provider for order ID #{$order_id} not found"
            ]);
            return;
        }
        
        // Check API status
        $data_post = ['key' => $api->key, 'action' => 'status', 'order' => $order->api_order_id];
        $response = json_decode($this->connect_api($api->url, $data_post));
        
        if (!$response) {
            echo json_encode([
                "status" => "error",
                "message" => "No response from API provider for order ID #{$order_id}"
            ]);
            return;
        }
        
        if (!empty($response->error)) {
            $this->db->update($this->tb_orders, [
                "note" => $response->error,
                "changed" => NOW,
            ], ["id" => $order->id]);
            
            echo json_encode([
                "status" => "error",
                "message" => "API Error: " . $response->error
            ]);
            return;
        }
        
        if (isset($response->status) && $response->status != "") {
            if (!in_array($response->status, ['Completed', 'Processing', 'In progress', 'Partial', 'Canceled', 'Refunded'])) {
                $response->status = 'Pending';
            }
            
            $data = [];
            $old_status = $order->status;
            
            // Use the same logic from cron_status_orders()
            if ($order->is_drip_feed) {
                $status_dripfeed = (strrpos($response->status, 'progress') || strrpos(strtolower($response->status), 'active')) ? 'inprogress'
                    : strtolower(str_replace([" ", "_"], "", $response->status));
                    
                if (!in_array($status_dripfeed, ['canceled', 'inprogress', 'completed'])) {
                    $status_dripfeed = 'inprogress';
                }
                
                $data = [
                    "changed" => NOW,
                    "status" => $status_dripfeed,
                ];
                
                if (isset($response->runs)) {
                    $data['sub_response_orders'] = json_encode($response);
                } else {
                    switch ($response->status) {
                        case 'Completed':
                            $response->runs = $order->runs;
                            break;
                        case 'In progress':
                        case 'Canceled':
                            $response->runs = 0;
                            break;
                    }
                    $data['sub_response_orders'] = json_encode($response);
                }
                
                // Handle child orders
                if (isset($response->orders)) {
                    $db_drip = json_decode($order->sub_response_orders);
                    if (isset($db_drip->orders)) {
                        $new_drip_orders = array_diff($response->orders, $db_drip->orders);
                    } else {
                        $new_drip_orders = $response->orders;
                    }
                    if (!empty($new_drip_orders)) {
                        $this->insert_order_from_dripfeed_subscription($order, $api, $new_drip_orders);
                    }
                }
            } else {
                $remains = $response->remains;
                if ($remains < 0) {
                    $remains = "+" . abs($remains);
                }
                
                $data = [
                    "start_counter" => $response->start_count,
                    "remains" => $remains,
                    "note" => "",
                    "changed" => NOW,
                    "status" => ($response->status == "In progress") ? "inprogress" : strtolower($response->status),
                ];
            }
            
            if (!empty($data)) {
                // Handle refunds
                if ($order->sub_response_posts != 1 && in_array($response->status, ["Refunded", "Canceled", "Partial"])) {
                    $data['charge'] = 0;
                    $formal_charge = 0;
                    $profit = 0;
                    $return_funds = $charge = $order->charge;
                    
                    if ($response->status == "Partial") {
                        $order_remains = $response->remains;
                        if ($order->quantity < $response->remains) {
                            $order_remains = $order->quantity;
                            $data['remains'] = $order_remains;
                        }
                        $return_funds = $charge * ($order_remains / $order->quantity);
                        $real_charge = $charge - $return_funds;
                        $formal_charge = $order->formal_charge * (1 - ($order_remains / $order->quantity));
                        $profit = $order->profit * (1 - ($order_remains / $order->quantity));
                        $data['charge'] = $real_charge;
                    }
                    
                    $data['formal_charge'] = $formal_charge;
                    $data['profit'] = $profit;
                    
                    // Update user balance for refunds
                    $user = $this->model->get("id, balance", $this->tb_users, ["id" => $order->uid]);
                    if (!empty($user)) {
                        $balance = $user->balance + $return_funds;
                        $this->db->update($this->tb_users, ["balance" => $balance], ["id" => $order->uid]);
                    }
                }
                
                // Update the order
                $this->db->update($this->tb_orders, $data, ["id" => $order->id]);
                
                echo json_encode([
                    "status" => "success",
                    "message" => "Order ID #{$order_id} updated successfully",
                    "details" => [
                        "order_id" => $order->id,
                        "old_status" => $old_status,
                        "new_status" => $data['status'],
                        "start_count" => isset($response->start_count) ? $response->start_count : null,
                        "remains" => isset($response->remains) ? $response->remains : null,
                        "api_status" => $response->status
                    ]
                ]);
            } else {
                echo json_encode([
                    "status" => "error",
                    "message" => "No data to update for order ID #{$order_id}"
                ]);
            }
        } else {
            echo json_encode([
                "status" => "error",
                "message" => "Invalid API response format for order ID #{$order_id}"
            ]);
        }
    }
    
    /**
     * Helper method to connect to API
     */
    private function connect_api($url, $post = []) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        $response = curl_exec($ch);
        curl_close($ch);
        return $response;
    }
    
    /**
     * Helper method for inserting order from drip feed subscription
     */
    private function insert_order_from_dripfeed_subscription($order, $api, $new_drip_orders) {
        // This method would need to be implemented based on your existing code
        // For now, it's just a placeholder to avoid errors
        return;
    }
}
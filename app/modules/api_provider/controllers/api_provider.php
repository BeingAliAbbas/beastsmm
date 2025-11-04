<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * API Provider Controller
 *
 * Responsibilities:
 *  - CRUD for API providers
 *  - Fetch / bulk add / sync of remote services
 *  - Order placement & status cron routines
 *  - Auto‑sync (manual modal + cron endpoints)
 *
 * Improvements added in this version:
 *  1. Added auto_sync_cron($secret = '') endpoint (CLI or HTTP) with secret check.
 *  2. Unified cron sync_services branch to use enable_sync_options (previous bug: used is_enable_sync_price only).
 *  3. Reused the same sync_services_by_api() logic for manual and cron sync options.
 *  4. Added defensive checks & docblocks.
 *  5. Fixed service_name sync condition (was checking desc instead of name).
 */
class api_provider extends MX_Controller {

	public $tb_users;
	public $tb_categories;
	public $tb_services;
	public $tb_api_providers;
	public $tb_orders;
	public $columns;
	public $module_name;
	public $module_icon;
	private $auto_sync_secret; // Secret for auto sync cron

	public function __construct(){
		parent::__construct();
		$this->load->model(get_class($this).'_model', 'model');
		$this->model->get_class();

		$this->tb_users         = USERS;
		$this->tb_categories    = CATEGORIES;
		$this->tb_services      = SERVICES;
		$this->tb_api_providers = API_PROVIDERS;
		$this->tb_orders        = ORDER;

		$this->columns = array(
			"name"    => lang("Name"),
			"balance" => lang("Balance"),
			"desc"    => lang("Description"),
			"status"  => lang("Status"),
		);

		// Load secret from config (define $config['auto_sync_cron_secret'] in config.php)
		$this->auto_sync_secret = config_item('auto_sync_cron_secret');
	}

	/*--------------------------------------------------------------
	| UI: Providers list
	--------------------------------------------------------------*/
	public function index(){
		if (!get_role('admin') || !session('uid')) {
			redirect(cn('statistics'));
		}
		$api_lists = $this->model->get_api_lists();
		$data = array(
			"module"    => get_class($this),
			"columns"   => $this->columns,
			"api_lists" => $api_lists,
		);
		$this->template->build('index', $data);
	}

	/*--------------------------------------------------------------
	| Add / Edit provider (modal)
	--------------------------------------------------------------*/
	public function update($ids = ""){
		if (!get_role('admin')) _validation('error', "Permission Denied!");

		$api = $this->model->get("*", $this->tb_api_providers, "ids = '{$ids}' ");
		$data = array(
			"module" => get_class($this),
			"api"    => $api,
		);
		$this->load->view('update', $data);
	}

	public function ajax_update($ids = ""){
		if (!get_role('admin')) _validation('error', "Permission Denied!");

		$name        = post("name");
		$api_url     = trim(post("api_url"));
		$api_key     = trim(post("api_key"));
		$status      = (int)post("status");
		$description = $this->input->post("description");
		$description = htmlspecialchars(stripslashes(trim($description)), ENT_QUOTES);

		if ($name == "")      ms(["status"=>"error","message"=>lang("name_is_required")]);
		if ($api_url == "")   ms(["status"=>"error","message"=>lang("api_url_is_required")]);
		if ($api_key == "")   ms(["status"=>"error","message"=>lang("api_key_is_required")]);

		$data = array(
			"uid"         => session('uid'),
			"name"        => $name,
			"key"         => $api_key,
			"url"         => $api_url,
			"description" => $description,
			"status"      => $status,
		);

		// Validate API balance endpoint
		if (!empty($api_key) && !empty($api_url)) {
			$data_post = ['key'=>$api_key,'action'=>'balance'];
			$data_connect = $this->connect_api($api_url, $data_post);
			$data_connect = json_decode($data_connect);
			if (empty($data_connect) || !isset($data_connect->balance)) {
				ms([
					"status"  => "error",
					"message" => lang("there_seems_to_be_an_issue_connecting_to_api_provider_please_check_api_key_and_token_again")
				]);
			} else {
				$data["balance"]       = $data_connect->balance;
				if (isset($data_connect->currency)) {
					$data["currency_code"] = $data_connect->currency;
				}
			}
		}

		$check_item = $this->model->get("ids, id", $this->tb_api_providers, "ids = '{$ids}'");
		if (empty($check_item)){
			$data["ids"]     = ids();
			$data["changed"] = NOW;
			$data["created"] = NOW;
			$this->db->insert($this->tb_api_providers, $data);
		}else{
			$data["changed"] = NOW;
			$this->db->update($this->tb_api_providers, $data, ["ids" => $check_item->ids]);
		}

		ms(["status"=>"success","message"=>lang("Update_successfully")]);
	}

	public function ajax_update_api_provider($ids){
		if (!get_role('admin')) _validation('error', "Permission Denied!");
		if ($ids == "") return;

		$api = $this->model->get("*", $this->tb_api_providers, ["ids" => $ids]);
		if (empty($api)) {
			ms(["status"=>"error","message"=>lang("api_provider_does_not_exists")]);
		}

		$data_post = ['key'=>$api->key,'action'=>'balance'];
		$data_connect = json_decode($this->connect_api($api->url, $data_post));

		if (empty($data_connect) || !isset($data_connect->balance)) {
			ms([
				"status"  => "error",
				"message" => lang("there_seems_to_be_an_issue_connecting_to_api_provider_please_check_api_key_and_token_again")
			]);
		}

		$data = [
			"balance"       => $data_connect->balance,
			"currency_code" => (isset($data_connect->currency)? $data_connect->currency : ""),
			"changed"       => NOW,
		];
		$this->db->update($this->tb_api_providers, $data, ["ids" => $api->ids]);

		ms(["status"=>"success","message"=>lang("Update_successfully")]);
	}

	public function ajax_delete_item($ids = ""){
		$this->model->delete($this->tb_api_providers, $ids, true);
	}

	/*--------------------------------------------------------------
	| View: Remote services list (select provider)
	--------------------------------------------------------------*/
	public function services(){
		if (!get_role('admin')) _validation('error', "Permission Denied!");
		
		$api_lists = $this->model->get_api_lists(true);
		$data = [
			"module"    => get_class($this),
			"api_lists" => $api_lists,
		];

		$this->template->build('api/get_services', $data);
	}

	/*--------------------------------------------------------------
	| Sync Services (modal & ajax)
	--------------------------------------------------------------*/
	public function sync_services($ids = ""){
		if ($ids == "") return;
		$api = $this->model->get("id, name, ids, url, key",  $this->tb_api_providers, "ids = '{$ids}'");
		if (empty($api)) return;
		$data = [
			"module" => get_class($this),
			"api"    => $api,
		];
		$this->load->view('api/sync_update', $data);
	}

	public function ajax_sync_services($ids){
		if (!get_role('admin')) _validation('error', "Permission Denied!");

		$price_percentage_increase = (int)post("price_percentage_increase");
		$request                  = (int)post("request"); // 0=current, 1=all
		$decimal_places           = get_option("auto_rounding_x_decimal_places", 2);

		// Flags
		$is_convert_to_new_currency = post("is_convert_to_new_currency");
		$enable_sync_options        = post("enable_sync_options"); // array of checkboxes

		$new_currency_rate = ($is_convert_to_new_currency) ? get_option('new_currecry_rate', 1) : 1;
		if ($price_percentage_increase === "") {
			ms(["status"=>"error","message"=>lang("price_percentage_increase_in_invalid_format")]);
		}

		if ($ids == "") {
			ms(["status"=>"error","message"=>lang("api_provider_does_not_exists")]);
		}

		$api = $this->model->get("id, name, ids, url, key",  $this->tb_api_providers, "ids = '{$ids}' AND status = 1");
		if (empty($api)) {
			ms(["status"=>"error","message"=>lang("there_seems_to_be_an_issue_connecting_to_api_provider_please_check_api_key_and_token_again")]);
		}

		$data_post    = ['key'=>$api->key,'action'=>'services'];
		$data_services= $this->connect_api($api->url, $data_post);
		$api_services = json_decode($data_services);

		if (empty($api_services) || !is_array($api_services)) {
			ms(["status"=>"error","message"=>lang("there_seems_to_be_an_issue_connecting_to_api_provider_please_check_api_key_and_token_again")]);
		}

		$services = $this->model->fetch("`id`,`ids`,`uid`,`cate_id`,`name`,`desc`,`price`,original_price,`min`,`max`,`add_type`,`type`,`api_service_id` as service,`api_provider_id`,`dripfeed`,`status`,`changed`,`created`", $this->tb_services, ["api_provider_id" => $api->id, 'status' => 1]);

		if (empty($services) && !$request) {
			ms(["status"=>"error","message"=>lang("service_lists_are_empty_unable_to_sync_services")]);
		}

		$data_item = (object)[
			'api'                        => $api,
			'api_services'               => $api_services,
			'services'                   => $services,
			'price_percentage_increase'  => $price_percentage_increase,
			'request'                    => $request,
			'decimal_places'             => $decimal_places,
			'new_currency_rate'          => $new_currency_rate,
			'enable_sync_options'        => $enable_sync_options,
		];

		$response = $this->sync_services_by_api($data_item);

		$data = [
			"api_id"            => $api->id,
			"api_name"          => $api->name,
			"services_new"      => ($request) ? $response->new_services : "",
			"services_disabled" => $response->disabled_services,
		];
		$this->load->view("api/results_sync", $data);
	}

	/*--------------------------------------------------------------
	| Auto Sync Settings (modal + ajax)
	--------------------------------------------------------------*/
	public function auto_sync_services_setting(){
		if (!get_role('admin')) _validation('error', "Permission Denied!");
		$data = ["module" => get_class($this)];
		$this->load->view('api/auto_sync_update', $data);
	}

	public function ajax_auto_sync_services_setting(){
		if (!get_role('admin')) _validation('error', "Permission Denied!");

		$price_percentage_increase = (int)post("price_percentage_increase");
		$sync_request              = (int)post("request");

		$is_enable_sync_price      = post('is_enable_sync_price') ? 1 : 0;
		$is_convert_to_new_currency= post('is_convert_to_new_currency') ? 1 : 0;
		$new_currency_rate         = ($is_convert_to_new_currency) ? get_option('new_currecry_rate', 1) : 1;

		$data = [
			'price_percentage_increase' => $price_percentage_increase,
			'sync_request'              => $sync_request,
			'new_currency_rate'         => $new_currency_rate,
			'is_enable_sync_price'      => $is_enable_sync_price,
			'is_convert_to_new_currency'=> $is_convert_to_new_currency,
		];

		update_option('defaut_auto_sync_service_setting', json_encode($data));

		ms(["status"=>"success","message"=>lang('Update_successfully')]);
	}

	/*--------------------------------------------------------------
	| Public Cron Endpoint (new): safer dedicated entry
	| Usage (CLI preferred):
	|   php index.php api_provider auto_sync_cron YOUR_SECRET
	| HTTP (less secure, restrict by IP & HTTPS):
	|   https://domain.com/api_provider/auto_sync_cron/YOUR_SECRET
	--------------------------------------------------------------*/
	public function auto_sync_cron($secret = ''){
		$is_cli = is_cli();
		if (!$is_cli) {
			// If not CLI, enforce secret
			if (!$this->auto_sync_secret || $secret !== $this->auto_sync_secret) {
				log_message('error','auto_sync_cron unauthorized attempt');
				show_404();
				return;
			}
		} else {
			// CLI: allow secret mismatch but warn if not set
			if (!$this->auto_sync_secret) {
				echo "WARN: auto_sync_cron_secret not set in config. Consider adding it.\n";
			}
		}

		$settings_raw = get_option("defaut_auto_sync_service_setting", '');
		if (!$settings_raw){
			echo "NO_SETTINGS\n";
			return;
		}
		$settings = json_decode($settings_raw);
		if (!$settings){
			echo "INVALID_SETTINGS\n";
			return;
		}

		$price_percentage_increase = (int)($settings->price_percentage_increase ?? 0);
		$sync_request              = (int)($settings->sync_request ?? 0);
		$is_enable_sync_price      = (int)($settings->is_enable_sync_price ?? 0);
		$is_convert_to_currency    = (int)($settings->is_convert_to_new_currency ?? 0);
		$new_currency_rate         = (float)($settings->new_currency_rate ?? 1);
		if ($new_currency_rate <= 0) $new_currency_rate = 1;
		$decimal_places            = (int)get_option("auto_rounding_x_decimal_places", 2);

		$enable_sync_options = [
			'new_price'        => $is_enable_sync_price,
			'original_price'   => 1, // always keep provider baseline updated
			'min_max_dripfeed' => 1, // keep limits updated
			// Optionally add 'service_name'=>1, 'description'=>1 if you want
		];

		// Fetch all active providers (limit concurrency if needed)
		$providers = $this->model->get_api_lists(true);
		if (empty($providers)) {
			echo "NO_PROVIDERS\n";
			return;
		}

		$total_providers_synced = 0;
		$total_new = 0;
		$total_disabled = 0;

		foreach ($providers as $api) {
			// Only sync if changed < NOW (like existing scheduling)
			if (isset($api->changed) && $api->changed > NOW) {
				continue;
			}

			$data_post = ['key'=>$api->key,'action'=>'services'];
			$response  = $this->connect_api($api->url, $data_post);
			$api_services = json_decode($response);

			if (empty($api_services) || !is_array($api_services)) {
				log_message('error','Auto Sync: failed services fetch for provider ID '.$api->id);
				continue;
			}

			$services = $this->model->fetch("`id`,`ids`,`uid`,`cate_id`,`name`,`desc`,`price`,original_price,`min`,`max`,`add_type`,`type`,`api_service_id` as service,`api_provider_id`,`dripfeed`,`status`,`changed`,`created`",
											$this->tb_services,
											["api_provider_id" => $api->id, 'status' => 1]);

			if (empty($services) && !$sync_request) {
				// skip if no local services and not allowed to add new
				continue;
			}

			$data_item = (object)[
				'api'                       => $api,
				'api_services'              => $api_services,
				'services'                  => $services,
				'price_percentage_increase' => $price_percentage_increase,
				'request'                   => $sync_request,
				'decimal_places'            => $decimal_places,
				'new_currency_rate'         => ($is_convert_to_currency ? $new_currency_rate : 1),
				'enable_sync_options'       => $enable_sync_options,
			];

			$result = $this->sync_services_by_api($data_item);
			$total_providers_synced++;
			if ($sync_request) {
				$total_new      += (!empty($result->new_services)) ? count($result->new_services) : 0;
			}
			$total_disabled += (!empty($result->disabled_services)) ? count($result->disabled_services) : 0;

			// Throttle lightly to avoid hitting rate limits
			usleep(200000); // 0.2s
		}

		$summary = "SYNC_DONE providers={$total_providers_synced} new={$total_new} disabled={$total_disabled}";
		echo $summary."\n";
		log_message('info','auto_sync_cron summary: '.$summary);
	}

	/*--------------------------------------------------------------
	| LOW-LEVEL: Core sync logic (used by manual + cron)
	--------------------------------------------------------------*/
	private function sync_services_by_api($data_item){
		$api                        = $data_item->api;
		$api_services               = $data_item->api_services;
		$services                   = $data_item->services;
		$price_percentage_increase  = $data_item->price_percentage_increase;
		$request                    = $data_item->request;
		$decimal_places             = $data_item->decimal_places;
		$new_currency_rate          = $data_item->new_currency_rate;
		$enable_sync_options        = $data_item->enable_sync_options;

		// Compute diff sets
		$disabled_services = array_udiff($services, $api_services,
			function ($a, $b){ return $a->service - $b->service; }
		);

		$new_services = array_udiff($api_services, $services,
			function ($a, $b){ return $a->service - $b->service; }
		);

		$exists_services = array_udiff($api_services, $new_services,
			function ($a, $b){ return $a->service - $b->service; }
		);

		// Disable services not present anymore
		if (!empty($disabled_services)) {
			foreach ($disabled_services as $disabled_service) {
				$this->db->update($this->tb_services,
					["status" => 0, "changed" => NOW],
					["api_provider_id" => $api->id, "api_service_id" => $disabled_service->service, 'id' => $disabled_service->id]);
			}
		}

		// Update existing
		if (!empty($exists_services) && !empty($enable_sync_options)) {
			foreach ($exists_services as $exists_service) {
				$service_type = isset($exists_service->type) ? strtolower(str_replace(" ", "_", $exists_service->type)) : 'default';
				$data_service = [
					"type"    => $service_type,
					"changed" => NOW,
				];

				// New price
				if (!empty($enable_sync_options['new_price'])) {
					$rate     = $exists_service->rate;
					$new_rate = round($rate + (($rate * $price_percentage_increase) / 100), $decimal_places);
					if ($new_rate <= 0.004) $new_rate = 0.01;
					$data_service['price'] = $new_rate * $new_currency_rate;
				}

				// Description
				if (!empty($enable_sync_options['description']) && isset($exists_service->desc) && $exists_service->desc != "") {
					$data_service['desc'] = $exists_service->desc;
				}

				// Service name
				if (!empty($enable_sync_options['service_name']) && isset($exists_service->name) && $exists_service->name != "") {
					$data_service['name'] = $exists_service->name;
				}

				// Original price
				if (!empty($enable_sync_options['original_price'])) {
					$data_service['original_price'] = $exists_service->rate;
				}

				// Min/Max/Dripfeed
				if (!empty($enable_sync_options['min_max_dripfeed'])) {
					$data_service['min']      = $exists_service->min;
					$data_service['max']      = $exists_service->max;
					$data_service['dripfeed'] = (isset($exists_service->dripfeed) && $exists_service->dripfeed) ? 1 : 0;
				}

				$this->db->update(
					$this->tb_services,
					$data_service,
					["api_service_id" => $exists_service->service, "api_provider_id" => $api->id]
				);
			}
		}

		// Add new
		if (!empty($new_services) && $request) {
			$sort_index = 1;
			foreach ($new_services as $new_service) {

				$exists_service = $this->model->get('ids', $this->tb_services, [
					'api_service_id'  => $new_service->service,
					'api_provider_id' => $api->id
				]);
				if ($exists_service) continue;

				$category_name  = trim($new_service->category);
				$check_category = $this->model->get("ids, id, name", $this->tb_categories, "name = '{$category_name}'");
				$service_type   = isset($new_service->type) ? strtolower(str_replace(" ", "_", $new_service->type)) : 'default';

				$rate = $new_service->rate;
				$new_rate = round($rate + (($rate*$price_percentage_increase)/100), $decimal_places);
				if ($new_rate <= 0.004) $new_rate = 0.01;

				$data_service = [
					"uid"             => session('uid'),
					"name"            => $new_service->name,
					"min"             => $new_service->min,
					"max"             => $new_service->max,
					"price"           => $new_rate * $new_currency_rate,
					"original_price"  => $rate,
					"add_type"        => 'api',
					"type"            => $service_type,
					"api_provider_id" => $api->id,
					"api_service_id"  => $new_service->service,
					"dripfeed"        => (isset($new_service->dripfeed) && $new_service->dripfeed) ? 1 : 0,
					"ids"             => ids(),
					"status"          => 1,
					"changed"         => NOW,
					"created"         => NOW,
				];

				if (isset($new_service->desc)) {
					$data_service['desc'] = $new_service->desc;
				}

				if (!empty($check_category)) {
					$data_service["cate_id"] = $check_category->id;
				} else {
					// Create category
					$data_category = [
						"ids"     => ids(),
						"uid"     => session('uid'),
						"name"    => $category_name,
						"sort"    => $sort_index,
						"changed" => NOW,
						"created" => NOW,
					];
					$this->db->insert($this->tb_categories, $data_category);
					if ($this->db->affected_rows() > 0) {
						$data_service["cate_id"] = $this->db->insert_id();
					}
				}
				$this->db->insert($this->tb_services, $data_service);
				$sort_index++;
			}
		}

		// Schedule next random update time
		$rand_time = get_random_time("api");
		$this->db->update($this->tb_api_providers, ['changed' => date('Y-m-d H:i:s', strtotime(NOW) + $rand_time)], ['id' => $api->id]);

		return (object)[
			'new_services'      => $new_services,
			'disabled_services' => $disabled_services,
		];
	}

	/*--------------------------------------------------------------
	| Bulk services (modal + ajax)
	--------------------------------------------------------------*/
	public function bulk_services($ids = ""){
		if ($ids == "") return;
		$api = $this->model->get("id, name, ids, url, key",  $this->tb_api_providers, "ids = '{$ids}' AND status = 1");
		if (empty($api)) return;
		$data = [
			"module" => get_class($this),
			"api"    => $api,
		];
		$this->load->view('api/bulk_update', $data);
	}

	public function ajax_bulk_services($ids){
		if (!get_role('admin')) _validation('error', "Permission Denied!");

		$price_percentage_increase = (int)post("price_percentage_increase");
		$bulk_limit                = post("bulk_limit");
		$decimal_places            = get_option("auto_rounding_x_decimal_places", 2);

		$is_convert_to_new_currency = post("is_convert_to_new_currency");
		$new_currency_rate          = ($is_convert_to_new_currency == "on") ? get_option('new_currecry_rate', 1) : 1;

		if ($price_percentage_increase === "")
			ms(["status"=>"error","message"=>lang("price_percentage_increase_in_invalid_format")]);

		if ($bulk_limit === "")
			ms(["status"=>"error","message"=>lang("bulk_add_limit_in_invalid_format")]);

		if ($ids == "")
			ms(["status"=>"error","message"=>lang("api_provider_does_not_exists")]);

		$api = $this->model->get("id, name, ids, url, key",  $this->tb_api_providers, "ids = '{$ids}' AND status = 1");
		if (empty($api))
			ms(["status"=>"error","message"=>lang("api_provider_does_not_exists")]);

		$data_post = ['key'=>$api->key,'action'=>'services'];
		$data_services = json_decode($this->connect_api($api->url, $data_post));
		if (empty($data_services) || !is_array($data_services)) {
			ms(["status"=>"error","message"=>lang("there_seems_to_be_an_issue_connecting_to_api_provider_please_check_api_key_and_token_again")]);
		}

		$i=0;
		foreach ($data_services as $row) {
			$rate     = $row->rate;
			$new_rate = round($rate + (($rate*$price_percentage_increase)/100), $decimal_places);
			if ($new_rate <= 0.004) $new_rate = 0.01;

			if ($i < $bulk_limit || $bulk_limit == "all") {
				$check_services = $this->model->get("id, ids, api_provider_id, api_service_id", $this->tb_services, "api_provider_id ='{$api->id}' AND api_service_id ='{$row->service}' ");
				$service_type = (isset($row->type)) ? strtolower(str_replace(" ", "_", $row->type)) : 'default';

				if (empty($check_services)) {
					$category_name  = trim($row->category);
					$check_category = $this->model->get("ids, id, name", $this->tb_categories, "name = '{$category_name}'");

					$data_service = [
						"uid"             => session('uid'),
						"name"            => $row->name,
						"min"             => $row->min,
						"max"             => $row->max,
						"price"           => $new_rate * $new_currency_rate,
						"original_price"  => $rate,
						"add_type"        => 'api',
						"type"            => $service_type,
						"api_provider_id" => $api->id,
						"api_service_id"  => $row->service,
						"dripfeed"        => (isset($row->dripfeed) && $row->dripfeed) ? 1 : 0,
						"ids"             => ids(),
						"status"          => 1,
						"changed"         => NOW,
						"created"         => NOW,
					];
					if (isset($row->desc)) $data_service['desc'] = $row->desc;

					if (!empty($check_category)) {
						$data_service["cate_id"] = $check_category->id;
						$this->db->insert($this->tb_services, $data_service);
						$i++;
					} else {
						$data_category = [
							"ids"     => ids(),
							"uid"     => session('uid'),
							"name"    => $category_name,
							"sort"    => $i,
							"changed" => NOW,
							"created" => NOW,
						];
						$this->db->insert($this->tb_categories, $data_category);
						if ($this->db->affected_rows() > 0) {
							$data_service["cate_id"] = $this->db->insert_id();
							$this->db->insert($this->tb_services, $data_service);
							$i++;
						}
					}

				} else {
					$data_service = [
						"uid"            => session('uid'),
						"min"            => $row->min,
						"max"            => $row->max,
						"dripfeed"       => (isset($row->dripfeed) && $row->dripfeed) ? 1 : 0,
						"price"          => $new_rate * $new_currency_rate,
						"original_price" => $rate,
						"type"           => $service_type,
						"changed"        => NOW,
					];
					if (isset($row->desc)) $data_service['desc'] = $row->desc;

					$this->db->update($this->tb_services, $data_service, [
						"api_service_id"  => $row->service,
						"api_provider_id" => $api->id,
						"ids"             => $check_services->ids
					]);
				}
			} else {
				break;
			}
		}

		ms(["status"=>"success","message"=>lang("Update_successfully")]);
	}

	/*--------------------------------------------------------------
	| AJAX: Provider service list for Add Single Service
	--------------------------------------------------------------*/
	public function ajax_api_provider_services($ids = ""){
		if ($ids == "") {
			echo $this->error_alert();
			return;
		}
		$api = $this->model->get("id, name, ids, url, key",  $this->tb_api_providers, "ids = '{$ids}'");
		if (empty($api)) {
			echo $this->error_alert();
			return;
		}

		$data_post     = ['key'=>$api->key,'action'=>'services'];
		$data_services = json_decode($this->connect_api($api->url, $data_post));
		if (empty($data_services) || !is_array($data_services)) {
			echo $this->error_alert();
			return;
		}

		$data_columns = [
			"service_id" => lang("service_id"),
			"name"       => lang("Name"),
			"category"   => lang("Category"),
			"price"      => lang("rate_per_1000"),
			"min_max"    => lang("min__max_order"),
			"drip_feed"  => lang("dripfeed"),
		];

		$categories = $this->model->fetch("*", $this->tb_categories, "status = 1", 'sort','ASC');
		$data = [
			"api_id"     => $api->id,
			"api_ids"    => $api->ids,
			"module"     => get_class($this),
			"columns"    => $data_columns,
			"services"   => $data_services,
			"categories" => $categories,
		];
		$this->load->view("api/ajax_get_services", $data);
	}

	private function error_alert(){
		return '<div class="alert alert-icon alert-danger" role="alert">
		          <i class="fe fe-alert-triangle mr-2"></i> '.lang("there_seems_to_be_an_issue_connecting_to_api_provider_please_check_api_key_and_token_again").'
		        </div>';
	}

	/*--------------------------------------------------------------
	| AJAX: Add Single Provider Service
	--------------------------------------------------------------*/
	public function ajax_add_api_provider_service(){
		if (!get_role('admin')) _validation('error', "Permission Denied!");

		$api_provider_id = post("api_provider_id");
		$api_service_id  = post("service_id");
		$type            = strtolower(str_replace(" ", "_", post("type")));
		$name            = post("name");
		$category        = post("category");
		$min             = post("min");
		$dripfeed        = post("dripfeed");
		$max             = post("max");
		$price           = (double)post("price");
		$desc            = $this->input->post("service_desc");

		$price_percentage_increase = (int)post("price_percentage_increase");
		$decimal_places            = get_option("auto_rounding_x_decimal_places", 2);
		$is_convert_to_new_currency= post("is_convert_to_new_currency");
		$new_currency_rate         = ($is_convert_to_new_currency == "on") ? get_option('new_currecry_rate', 1) : 1;

		if ($name == "")      ms(["status"=>"error","message"=>lang("name_is_required")]);
		if ($category == "")  ms(["status"=>"error","message"=>lang("category_is_required")]);
		if ($min == "")       ms(["status"=>"error","message"=>lang("min_order_is_required")]);
		if ($max == "")       ms(["status"=>"error","message"=>lang("max_order_is_required")]);
		if ($min > $max)      ms(["status"=>"error","message"=>lang("max_order_must_to_be_greater_than_min_order")]);
		if ($price == "")     ms(["status"=>"error","message"=>lang("price_invalid")]);

		$new_rate = round($price + (($price*$price_percentage_increase)/100), $decimal_places);
		if ($new_rate <= 0.004 && $decimal_places == 2) $new_rate = 0.01;

		$data = [
			"uid"             => session('uid'),
			"cate_id"         => $category,
			"desc"            => $desc,
			"min"             => $min,
			"max"             => $max,
			"price"           => $new_currency_rate*$new_rate,
			"original_price"  => $price,
			"add_type"        => 'api',
			"type"            => $type,
			"api_provider_id" => $api_provider_id,
			"api_service_id"  => $api_service_id,
			"dripfeed"        => $dripfeed,
		];

		$check_item = $this->model->get("ids", $this->tb_services, [
			'api_provider_id' => $api_provider_id,
			'api_service_id'  => $api_service_id
		]);

		if (empty($check_item)){
			$data["ids"]     = ids();
			$data["name"]    = $name;
			$data["status"]  = 1;
			$data["changed"] = NOW;
			$data["created"] = NOW;
			$this->db->insert($this->tb_services, $data);
		} else {
			$this->db->update($this->tb_services, $data, ["ids" => $check_item->ids]);
		}

		ms(["status"=>"success","message"=>lang("Update_successfully")]);
	}

	/*--------------------------------------------------------------
	| Legacy Cron Multiplexer (still supported)
	|   /api_provider/cron/order
	|   /api_provider/cron/status
	|   /api_provider/cron/status_subscriptions
	|   /api_provider/cron/sync_services
	| NOTE: sync_services here now maps is_enable_sync_price => enable_sync_options
	--------------------------------------------------------------*/
	public function cron($type = ""){
		switch ($type) {

			case 'order':
				$this->cron_place_orders();
				break;

			case 'status_subscriptions':
				$this->cron_status_subscriptions();
				break;

			case 'status':
				$this->cron_status_orders();
				break;

			case 'sync_services':
				$this->cron_sync_services();
				break;
		}
	}

	private function cron_place_orders(){
		$orders = $this->model->get_all_orders();
		if (empty($orders)) {
			echo "There is no order at the present.<br>Successfully";
			return;
		}

		foreach ($orders as $row) {
			$api = $this->model->get("url, key", $this->tb_api_providers, ["id" => $row->api_provider_id]);
			if (empty($api)) {
				echo "API Provider does not exists.<br>";
				continue;
			}
			$data_post = [
				'key'     => $api->key,
				'action'  => 'add',
				'service' => $row->api_service_id,
			];

			// Build service-specific payload
			switch ($row->service_type) {
				case 'subscriptions':
					$data_post["username"] = $row->username;
					$data_post["min"]      = $row->sub_min;
					$data_post["max"]      = $row->sub_max;
					$data_post["posts"]    = ($row->sub_posts == -1) ? 0 : $row->sub_posts;
					$data_post["delay"]    = $row->sub_delay;
					$data_post["expiry"]   = (!empty($row->sub_expiry))? date("d/m/Y",  strtotime($row->sub_expiry)) : "";
					break;
				case 'custom_comments':
					$data_post["link"]     = $row->link;
					$data_post["comments"] = json_decode($row->comments);
					break;
				case 'mentions_with_hashtags':
					$data_post["link"]      = $row->link;
					$data_post["quantity"]  = $row->quantity;
					$data_post["usernames"] = $row->usernames;
					$data_post["hashtags"]  = $row->hashtags;
					break;
				case 'mentions_custom_list':
					$data_post["link"]      = $row->link;
					$data_post["usernames"] = json_decode($row->usernames);
					break;
				case 'mentions_hashtag':
					$data_post["link"]     = $row->link;
					$data_post["quantity"] = $row->quantity;
					$data_post["hashtag"]  = $row->hashtag;
					break;
				case 'mentions_user_followers':
					$data_post["link"]     = $row->link;
					$data_post["quantity"] = $row->quantity;
					$data_post["username"] = $row->username;
					break;
				case 'mentions_media_likers':
					$data_post["link"]     = $row->link;
					$data_post["quantity"] = $row->quantity;
					$data_post["media"]    = $row->media;
					break;
				case 'package':
					$data_post["link"]     = $row->link;
					break;
				case 'custom_comments_package':
					$data_post["link"]     = $row->link;
					$data_post["comments"] = json_decode($row->comments);
					break;
				case 'comment_likes':
					$data_post["link"]     = $row->link;
					$data_post["quantity"] = $row->quantity;
					$data_post["username"] = $row->username;
					break;
				default:
					$data_post["link"]     = $row->link;
					$data_post["quantity"] = $row->quantity;
					if (isset($row->is_drip_feed) && $row->is_drip_feed == 1) {
						$data_post["runs"]     = $row->runs;
						$data_post["interval"] = $row->interval;
						$data_post["quantity"] = $row->dripfeed_quantity;
					}
					break;
			}

			$response = json_decode($this->connect_api($api->url, $data_post));
			if (!$response) {
				echo "OrderID - ". $row->id."<br>";
				$this->db->update($this->tb_orders, [
					"status"  => 'error',
					"note"    => 'Troubleshooting API requests',
					"changed" => NOW,
				], ["id" => $row->id]);
				continue;
			}

			if (!empty($response->error)) {
				echo "OrderID - ". $row->id." :". $response->error."<br>";
				$this->db->update($this->tb_orders, [
					"status"  => 'error',
					"note"    => $response->error,
					"changed" => NOW,
				], ["id" => $row->id]);
				continue;
			}

			if (!empty($response->order)) {
				$this->db->update($this->tb_orders, ["api_order_id" => $response->order, "changed" => NOW], ["id" => $row->id]);
			}
		}
		echo "Successfully";
	}

	private function cron_status_subscriptions(){
		$orders = $this->model->get_all_subscriptions_status();
		$new_currency_rate = get_option('new_currecry_rate', 1);
		if ($new_currency_rate == 0) $new_currency_rate = 1;

		if (empty($orders)) {
			echo "There is no order at the present.<br>Successfully";
			return;
		}

		foreach ($orders as $row) {
			$api = $this->model->get("id, url, key", $this->tb_api_providers, ["id" => $row->api_provider_id] );
			if (empty($api)) {
				echo "API Provider does not exists.<br>";
				continue;
			}
			$data_post = ['key'=>$api->key,'action'=>'status','order'=>$row->api_order_id];
			$response  = json_decode($this->connect_api($api->url, $data_post));

			if (!empty($response->error)) {
				echo $response->error."<br>";
				$this->db->update($this->tb_orders, [
					"note"    => $response->error,
					"changed" => NOW,
				], ["id" => $row->id]);
			}

			if (!empty($response->status)) {
				$rand_time = get_random_time();
				$data = [
					"sub_status"         => $response->status,
					"sub_response_orders"=> json_encode($response->orders),
					"sub_response_posts" => $response->posts,
					"note"               => "",
					"changed"            => date('Y-m-d H:i:s', strtotime(NOW) + $rand_time),
				];

				if (in_array($response->status, ["Completed","Canceled"])) {
					$data["status"] = ($response->status == "Completed") ? "completed" : "canceled";
				}

				// New subscription child orders
				if (isset($response->orders)) {
					$db_resp_orders = json_decode($row->sub_response_orders);
					if (isset($db_resp_orders->orders)) {
						$new_subscription_orders = array_diff($response->orders, $db_resp_orders->orders);
					} else {
						$new_subscription_orders = $response->orders;
					}
					if (!empty($new_subscription_orders)) {
						$this->insert_order_from_dripfeed_subscription($row, $api, $new_subscription_orders);
					}
				}
				$this->db->update($this->tb_orders, $data, ["id" => $row->id]);
			}
		}
		echo "Successfully";
	}

	private function cron_status_orders(){
    $limit = 50;
    $orders = $this->model->get_all_orders_status($limit, 0);

    if (empty($orders)) {
        echo "There is no order at the present.<br>Successfully";
        return;
    }

    $new_currency_rate = get_option('new_currecry_rate', 1);
    if ($new_currency_rate == 0) $new_currency_rate = 1;

    foreach ($orders as $row) {
        echo "Checking Order ID: {$row->id}<br>"; // ✅ log every checked order

        $api = $this->model->get("url, key", $this->tb_api_providers, ["id" => $row->api_provider_id]);
        if (empty($api)) {
            echo $row->id." → API Provider missing.<br>";
            continue;
        }

        $data_post = ['key'=>$api->key,'action'=>'status','order'=>$row->api_order_id];
        $response  = json_decode($this->connect_api($api->url, $data_post));

        if (!empty($response->error)) {
            echo $row->id." → Error: ".$response->error."<br>";
            $this->db->update($this->tb_orders, [
                "note"    => $response->error,
                "changed" => NOW,
            ], ["id" => $row->id]);
        }

        if (isset($response->status) && $response->status != "") {
            echo $row->id." → API Status: ".$response->status."<br>"; // ✅ log API status

            if (!in_array($response->status, ['Completed','Processing','In progress','Partial','Canceled','Refunded'])) {
                $response->status = 'Pending';
            }

            $data = [];
            $rand_time = get_random_time();

            if ($row->is_drip_feed) {
                $status_dripfeed = (strrpos($response->status, 'progress') || strrpos(strtolower($response->status), 'active')) ? 'inprogress'
                    : strtolower(str_replace([" ","_"], "", $response->status));
                if (!in_array($status_dripfeed, ['canceled','inprogress','completed'])) {
                    $status_dripfeed = 'inprogress';
                }
                $data = [
                    "changed" => date('Y-m-d H:i:s', strtotime(NOW) + $rand_time),
                    "status"  => $status_dripfeed,
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
                $remains = $response->remains;
                if ($remains < 0) {
                    $remains = "+".abs($remains);
                }
                $data = [
                    "start_counter" => $response->start_count,
                    "remains"       => $remains,
                    "note"          => "",
                    "changed"       => date('Y-m-d H:i:s', strtotime(NOW) + $rand_time),
                    "status"        => ($response->status == "In progress") ? "inprogress" : strtolower($response->status),
                ];
            }

            if (!empty($data)) {
                // Refund / Partial / Canceled compensation
                if ($row->sub_response_posts != 1 && in_array($response->status, ["Refunded","Canceled","Partial"])) {
                    $data['charge'] = 0;
                    $formal_charge  = 0;
                    $profit         = 0;
                    $return_funds   = $charge = $row->charge;

                    if ($response->status == "Partial") {
                        $order_remains = $response->remains;
                        if ($row->quantity < $response->remains) {
                            $order_remains     = $row->quantity;
                            $data['remains']   = $order_remains;
                        }
                        $return_funds     = $charge * ($order_remains / $row->quantity);
                        $real_charge      = $charge - $return_funds;
                        $formal_charge    = $row->formal_charge * (1 - ($order_remains / $row->quantity ));
                        $profit           = $row->profit * (1 - ($order_remains / $row->quantity ));
                        $data['charge']   = $real_charge;
                    }

                    $data['formal_charge'] = $formal_charge;
                    $data['profit']        = $profit;

                    $user = $this->model->get("id, balance", $this->tb_users, ["id"=> $row->uid]);
                    if (!empty($user)) {
                        $balance = $user->balance + $return_funds;
                        $this->db->update($this->tb_users, ["balance" => $balance], ["id"=> $row->uid]);
                    }
                }
                $this->db->update($this->tb_orders, $data, ["id" => $row->id]);
            }
        }
    }

    echo "Successfully";
}


	private function cron_sync_services(){
		ini_set('max_execution_time', 300000);

		$defaut_auto_sync = get_option("defaut_auto_sync_service_setting", '{"price_percentage_increase":50,"sync_request":0,"new_currency_rate":"1","is_enable_sync_price":0,"is_convert_to_new_currency":0}');
		$defaut_auto_sync = json_decode($defaut_auto_sync);

		$price_percentage_increase = (isset($defaut_auto_sync->price_percentage_increase)) ? $defaut_auto_sync->price_percentage_increase : 0;
		$request                   = (isset($defaut_auto_sync->sync_request)) ? $defaut_auto_sync->sync_request : 0;
		$is_enable_sync_price      = (isset($defaut_auto_sync->is_enable_sync_price)) ? $defaut_auto_sync->is_enable_sync_price : 0;
		$new_currency_rate         = (isset($defaut_auto_sync->is_convert_to_new_currency) && $defaut_auto_sync->is_convert_to_new_currency) ? get_option('new_currecry_rate', 1) : 1;
		$decimal_places            = get_option("auto_rounding_x_decimal_places", 2);

		// Build enable_sync_options to match manual sync (original_price + min_max always on)
		$enable_sync_options = [
			'new_price'        => $is_enable_sync_price ? 1 : 0,
			'original_price'   => 1,
			'min_max_dripfeed' => 1,
		];

		$apis = $this->model->fetch(
    "id, name, ids, url, key",
    $this->tb_api_providers,
    "`status` = 1",
    "changed",
    "ASC",
    0,
    2
);


		foreach ($apis as $api) {
			$data_post = ['key'=>$api->key,'action'=>'services'];
			$api_services = json_decode($this->connect_api($api->url, $data_post));
			if (empty($api_services) || !is_array($api_services)) {
				echo "<br> Error! Connection issue with provider ".$api->name;
				continue;
			}

			$services = $this->model->fetch("`id`,`ids`,`uid`,`cate_id`,`name`,`desc`,`price`,`min`,`max`,`add_type`,`type`,`api_service_id` as service,`api_provider_id`,`dripfeed`,`status`,`changed`,`created`",
				$this->tb_services,
				["api_provider_id" => $api->id, 'status' => 1]);

			if (empty($services) && !$request) {
				echo "<br> Error! Service list empty cannot sync ".$api->name;
				continue;
			}

			$data_item = (object)[
				'api'                       => $api,
				'api_services'              => $api_services,
				'services'                  => $services,
				'price_percentage_increase' => $price_percentage_increase,
				'request'                   => $request,
				'decimal_places'            => $decimal_places,
				'new_currency_rate'         => $new_currency_rate,
				'enable_sync_options'       => $enable_sync_options,
			];

			$this->sync_services_by_api($data_item);
		}
		echo "Successfully";
	}

	/*--------------------------------------------------------------
	| Helper: Insert child orders for dripfeed / subscription
	--------------------------------------------------------------*/
	private function insert_order_from_dripfeed_subscription($main_order = "", $provider = "", $new_dripfeed_orders = "" ){
		if ($main_order == "" || $provider == "" || $new_dripfeed_orders == "") {
			return false;
		}
		$service = $this->model->get("price, original_price, id", $this->tb_services, ['id' => $main_order->service_id]);
		$user    = $this->model->get("id, balance", $this->tb_users, ["id"=> $main_order->uid]);
		$user_service_price = get_user_price($main_order->uid, $service);

		$data_orders_batch = [];
		foreach ($new_dripfeed_orders as $order_id) {
			$exists_order = $this->model->get('id', $this->tb_orders, [
				'api_order_id'    => $order_id,
				'service_id'      => $main_order->service_id,
				'api_provider_id' => $main_order->api_provider_id
			]);
			if (!empty($exists_order)) continue;

			$data_order = [
				"ids"                => ids(),
				"uid"                => $main_order->uid,
				"cate_id"            => $main_order->cate_id,
				"service_id"         => $main_order->service_id,
				"main_order_id"      => $main_order->id,
				"service_type"       => "default",
				"api_provider_id"    => $main_order->api_provider_id,
				"api_service_id"     => $main_order->api_service_id,
				"api_order_id"       => $order_id,
				"status"             => 'pending',
				"changed"            => NOW,
				"created"            => NOW,
			];

			if ($main_order->is_drip_feed) {
				$data_order['link']     = $main_order->link;
				$data_order['quantity'] = $main_order->dripfeed_quantity;
				$total_charge           = ($main_order->dripfeed_quantity * $user_service_price)/1000;
				$data_order['charge']   = $total_charge;
			} else if ($main_order->service_type == "subscriptions") {
				$data_order['link']               = "https://www.instagram.com/".$main_order->username;
				$data_order['quantity']           = $main_order->sub_max;
				$data_order['sub_response_posts'] = 1;
				$total_charge                     = ($main_order->sub_max * $user_service_price)/1000;
				$data_order['charge']             = $total_charge;
				$this->update_fund_to_user($main_order->uid, $total_charge);
			} else {
				$total_charge = 0;
			}

			$data_order['formal_charge'] = ($service->original_price * $total_charge) / ($user_service_price ?: 1);
			$data_order['profit']        = $total_charge - $data_order['formal_charge'];

			$data_orders_batch[] = $data_order;
		}

		if (!empty($data_orders_batch)) {
			$this->db->insert_batch($this->tb_orders, $data_orders_batch);
			return true;
		}
		return false;
	}

	private function update_fund_to_user($uid, $funds, $type = ""){
		$user =  $this->model->get("id, balance", $this->tb_users, ["id" => $uid]);
		if (empty($user)) return;
		$balance = $user->balance;
		if ($type == 'add') {
			$balance += $funds;
		} else {
			$balance -= $funds;
		}
		$this->db->update($this->tb_users, ["balance" => $balance], ["id"=> $uid]);
	}

	/*--------------------------------------------------------------
	| HTTP Client wrapper
	--------------------------------------------------------------*/
    private function connect_api($url, $post = array("")) {
      	$_post = [];
      	if (is_array($post)) {
          	foreach ($post as $name => $value) {
              	$_post[] = $name.'='.urlencode($value);
          	}
      	}
      	$ch = curl_init($url);
      	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  		curl_setopt($ch, CURLOPT_POST, 1);
      	curl_setopt($ch, CURLOPT_HEADER, 0);
      	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
      	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
      	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
      	if (is_array($post)) {
          	curl_setopt($ch, CURLOPT_POSTFIELDS, join('&', $_post));
      	}
      	curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (SmartPanel API Sync)');
      	$result = curl_exec($ch);
      	if (curl_errno($ch) != 0 && empty($result)) {
          	$result = false;
      	}
      	curl_close($ch);
      	return $result;
    }


// new status cron

public function update_order_status($order_id = ""){
    if (!get_role('admin') || !session('uid')) {
        redirect(cn('statistics'));
    }

    if ($order_id == "") {
        $data = [
            "module" => get_class($this),
        ];
        $this->load->view('update_status_form', $data);
        return;
    }

    $order = $this->model->get("*", $this->tb_orders, ["id" => $order_id]);
    if (empty($order)) {
        echo json_encode([
            "status" => "error",
            "message" => "Order ID #{$order_id} not found",
            "api_order_id" => null,
            "api_charge" => null
        ]);
        return;
    }

    // Prepare fallback api_charge from order record if present
    $api_charge = null;
    if (isset($order->provider_charge) && $order->provider_charge !== '') {
        $api_charge = $order->provider_charge;
    } elseif (isset($order->api_charge) && $order->api_charge !== '') {
        $api_charge = $order->api_charge;
    }

    if (empty($order->api_order_id) || empty($order->api_provider_id)) {
        echo json_encode([
            "status" => "error",
            "message" => "Order ID #{$order_id} is not an API order or missing API details",
            "api_order_id" => isset($order->api_order_id) ? $order->api_order_id : null,
            "api_charge" => $api_charge
        ]);
        return;
    }

    $api = $this->model->get("url, key", $this->tb_api_providers, ["id" => $order->api_provider_id]);
    if (empty($api)) {
        echo json_encode([
            "status" => "error",
            "message" => "API Provider for order ID #{$order_id} not found",
            "api_order_id" => $order->api_order_id,
            "api_charge" => $api_charge
        ]);
        return;
    }

    $data_post = ['key' => $api->key, 'action' => 'status', 'order' => $order->api_order_id];
    $response = json_decode($this->connect_api($api->url, $data_post));

    // If we got a response and it contains a price/charge, use it (override fallback)
    if ($response) {
        if (isset($response->charge)) {
            $api_charge = $response->charge;
        } elseif (isset($response->price)) {
            $api_charge = $response->price;
        } elseif (isset($response->provider_price)) {
            $api_charge = $response->provider_price;
        }
    }

    if (!$response) {
        echo json_encode([
            "status" => "error",
            "message" => "No response from API provider for order ID #{$order_id}",
            "api_order_id" => $order->api_order_id,
            "api_charge" => $api_charge
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
            "message" => "API Error: " . $response->error,
            "api_order_id" => $order->api_order_id,
            "api_charge" => $api_charge
        ]);
        return;
    }

    if (isset($response->status) && $response->status != "") {
        $api_status_raw = trim($response->status);
        $api_status = strtolower($api_status_raw);

        // If partial -> do not change DB, just notify and return (include api_charge)
        if ($api_status === 'partial') {
            echo json_encode([
                "status" => "info",
                "message" => "Partial status detected — no update applied",
                "details" => [
                    "order_id" => $order->id,
                    "api_status" => $api_status_raw,
                    "remains" => isset($response->remains) ? $response->remains : null,
                    "api_order_id" => $order->api_order_id,
                    "api_charge" => $api_charge
                ],
                "api_order_id" => $order->api_order_id,
                "api_charge" => $api_charge
            ]);
            return;
        }

        if (!in_array($api_status_raw, ['Completed', 'Processing', 'In progress', 'Partial', 'Canceled', 'Refunded'])) {
            $response->status = 'Pending';
        }

        $data = [];
        $old_status = $order->status;

        if ($order->is_drip_feed) {
            $status_dripfeed = (strrpos(strtolower($response->status), 'progress') !== false || strrpos(strtolower($response->status), 'active') !== false) ? 'inprogress'
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
            $remains = isset($response->remains) ? $response->remains : null;
            if ($remains !== null && $remains < 0) {
                $remains = "+" . abs($remains);
            }

            $data = [
                "start_counter" => isset($response->start_count) ? $response->start_count : null,
                "remains" => $remains,
                "note" => "",
                "changed" => NOW,
                "status" => ($response->status == "In progress") ? "inprogress" : strtolower($response->status),
            ];
        }

        if (!empty($data)) {
            // Refund only for canceled/refunded. Partial already skipped above.
            if ($order->sub_response_posts != 1 && in_array($api_status, ["refunded", "canceled"])) {
                $data['charge'] = 0;
                $formal_charge = 0;
                $profit = 0;
                $return_funds = $charge = $order->charge;

                $data['formal_charge'] = $formal_charge;
                $data['profit'] = $profit;

                $user = $this->model->get("id, balance", $this->tb_users, ["id" => $order->uid]);
                if (!empty($user)) {
                    $balance = $user->balance + $return_funds;
                    $this->db->update($this->tb_users, ["balance" => $balance], ["id" => $order->uid]);
                }
            }

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
                    "api_status" => $response->status,
                    "api_order_id" => $order->api_order_id,
                    "api_charge" => $api_charge
                ],
                "api_order_id" => $order->api_order_id,
                "api_charge" => $api_charge
            ]);
        } else {
            echo json_encode([
                "status" => "error",
                "message" => "No data to update for order ID #{$order_id}",
                "api_order_id" => $order->api_order_id,
                "api_charge" => $api_charge
            ]);
        }
    } else {
        echo json_encode([
            "status" => "error",
            "message" => "Invalid API response format for order ID #{$order_id}",
            "api_order_id" => $order->api_order_id,
            "api_charge" => $api_charge
        ]);
    }
}
public function update_latest_orders($limit = 200){
    if (!get_role('admin') || !session('uid')) {
        redirect(cn('statistics'));
    }

    $limit = (int)$limit;
    if ($limit <= 0) $limit = 200;
    if ($limit > 500) $limit = 500;

    $start_time = microtime(true);

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
    $partial_count = 0;
    $partial_ids = [];
    $results = [];

    foreach ($orders as $row) {
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

            $this->db->update($this->tb_orders, [
                "note" => $response->error,
                "changed" => NOW,
            ], ["id" => $row->id]);
            continue;
        }

        if (isset($response->status) && $response->status != "") {
            // Normalize status for comparisons
            $api_status_raw = trim($response->status);
            $api_status = strtolower($api_status_raw);

            // If Partial -> skip updating this order, only highlight it
            if ($api_status === 'partial') {
                $results[] = [
                    'order_id' => $row->id,
                    'status' => 'partial_detected',
                    'message' => 'Partial status detected — update skipped',
                    'api_status' => $api_status_raw,
                    'remains' => isset($response->remains) ? $response->remains : null
                ];
                $partial_count++;
                $partial_ids[] = $row->id;
                continue; // skip further processing for partial
            }

            // Standardize status values (fallback to pending if unknown)
            if (!in_array($api_status_raw, ['Completed', 'Processing', 'In progress', 'Partial', 'Canceled', 'Refunded'])) {
                $response->status = 'Pending';
            }

            $data = [];
            $old_status = $row->status;

            if ($row->is_drip_feed) {
                $status_dripfeed = (strrpos(strtolower($response->status), 'progress') !== false || strrpos(strtolower($response->status), 'active') !== false) ? 'inprogress'
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
                $remains = isset($response->remains) ? $response->remains : null;
                if ($remains !== null && $remains < 0) {
                    $remains = "+" . abs($remains);
                }

                $data = [
                    "start_counter" => isset($response->start_count) ? $response->start_count : null,
                    "remains" => $remains,
                    "note" => "",
                    "changed" => NOW,
                    "status" => ($response->status == "In progress") ? "inprogress" : strtolower($response->status),
                ];
            }

            if (!empty($data)) {
                // Refunds/compensation only for canceled/refunded (partial intentionally skipped above)
                if ($row->sub_response_posts != 1 && in_array($api_status, ["refunded", "canceled"])) {
                    $data['charge'] = 0;
                    $formal_charge = 0;
                    $profit = 0;
                    $return_funds = $charge = $row->charge;

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

    $execution_time = microtime(true) - $start_time;

    echo json_encode([
        "status" => "success",
        "message" => "Order status update completed",
        "summary" => [
            "total_processed" => count($orders),
            "updated" => $updated_count,
            "no_change" => $no_change_count,
            "errors" => $error_count,
            "partial_detected" => $partial_count,
            "partial_ids" => $partial_ids,
            "execution_time" => round($execution_time, 2) . " seconds"
        ],
        "results" => $results
    ]);
}

}
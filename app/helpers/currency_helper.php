<?php 

/**
 * Multi-Currency Support Helper Functions
 * 
 * This file provides currency management, conversion, and formatting functions
 * for the multi-currency support system.
 */

/**
 * Get the current selected currency
 * Returns: array with code, symbol, rate, name, enabled
 */
if (!function_exists("get_current_currency")) {
    function get_current_currency() {
        $CI =& get_instance();
        
        // Check session first, then cookie, then default
        $selected_code = $CI->session->userdata('selected_currency');
        
        if (empty($selected_code) && isset($_COOKIE['selected_currency'])) {
            $selected_code = $_COOKIE['selected_currency'];
            $CI->session->set_userdata('selected_currency', $selected_code);
        }
        
        // Query the currency from database
        if (!empty($selected_code)) {
            $currency = $CI->db->where('code', $selected_code)
                               ->where('enabled', 1)
                               ->get('currencies')
                               ->row();
            
            if ($currency) {
                return [
                    'code'    => $currency->code,
                    'symbol'  => $currency->symbol,
                    'rate'    => (float)$currency->rate,
                    'name'    => $currency->name,
                    'enabled' => (bool)$currency->enabled
                ];
            }
        }
        
        // Fallback to default currency
        $default = $CI->db->where('is_default', 1)
                          ->get('currencies')
                          ->row();
        
        if ($default) {
            // Save to session
            $CI->session->set_userdata('selected_currency', $default->code);
            
            return [
                'code'    => $default->code,
                'symbol'  => $default->symbol,
                'rate'    => (float)$default->rate,
                'name'    => $default->name,
                'enabled' => (bool)$default->enabled
            ];
        }
        
        // Ultimate fallback to PKR if currencies table doesn't exist yet
        return [
            'code'    => 'PKR',
            'symbol'  => 'Rs',
            'rate'    => 1.0,
            'name'    => 'Pakistani Rupee',
            'enabled' => true
        ];
    }
}

/**
 * Convert amount from one currency to another
 * Formula: converted_amount = original_amount × (target_rate ÷ base_rate)
 * Base currency is PKR with rate = 1.0
 */
if (!function_exists("convert_currency")) {
    function convert_currency($amount, $from_code, $to_code) {
        if ($from_code === $to_code) {
            return $amount;
        }
        
        $CI =& get_instance();
        
        // Get rates for both currencies
        $from_currency = $CI->db->where('code', $from_code)->get('currencies')->row();
        $to_currency = $CI->db->where('code', $to_code)->get('currencies')->row();
        
        if (!$from_currency || !$to_currency) {
            return $amount; // Return original if currencies not found
        }
        
        $from_rate = (float)$from_currency->rate;
        $to_rate = (float)$to_currency->rate;
        
        // Convert to base (PKR) first, then to target currency
        // Since PKR has rate 1.0, we divide by from_rate to get PKR amount
        // Then multiply by to_rate to get target currency amount
        $pkr_amount = $amount / $from_rate;
        $converted = $pkr_amount * $to_rate;
        
        return $converted;
    }
}

/**
 * Format currency amount with symbol and proper decimal places
 */
if (!function_exists("format_currency")) {
    function format_currency($amount, $currency = null) {
        if ($currency === null) {
            $currency = get_current_currency();
        } elseif (is_string($currency)) {
            // If currency code is passed as string, get full currency data
            $CI =& get_instance();
            $curr_obj = $CI->db->where('code', $currency)->get('currencies')->row();
            
            if ($curr_obj) {
                $currency = [
                    'code'   => $curr_obj->code,
                    'symbol' => $curr_obj->symbol,
                    'rate'   => (float)$curr_obj->rate,
                    'name'   => $curr_obj->name,
                    'enabled' => (bool)$curr_obj->enabled
                ];
            } else {
                $currency = get_current_currency();
            }
        }
        
        // Get formatting options
        $decimal = get_option('currency_decimal', 2);
        
        switch (get_option('currency_decimal_separator', 'dot')) {
            case 'dot':
                $decimalpoint = '.';
                break;
            case 'comma':
                $decimalpoint = ',';
                break;
            default:
                $decimalpoint = '.';
                break;
        }
        
        switch (get_option('currency_thousand_separator', 'comma')) {
            case 'dot':
                $separator = '.';
                break;
            case 'comma':
                $separator = ',';
                break;
            case 'space':
                $separator = ' ';
                break;
            default:
                $separator = ',';
                break;
        }
        
        $formatted = number_format($amount, $decimal, $decimalpoint, $separator);
        
        return $currency['symbol'] . $formatted;
    }
}

/**
 * Get all active currencies
 */
if (!function_exists("get_active_currencies")) {
    function get_active_currencies() {
        $CI =& get_instance();
        
        $currencies = $CI->db->where('enabled', 1)
                             ->order_by('is_default', 'DESC')
                             ->order_by('code', 'ASC')
                             ->get('currencies')
                             ->result();
        
        $result = [];
        foreach ($currencies as $curr) {
            $result[] = [
                'code'       => $curr->code,
                'symbol'     => $curr->symbol,
                'rate'       => (float)$curr->rate,
                'name'       => $curr->name,
                'enabled'    => (bool)$curr->enabled,
                'is_default' => (bool)$curr->is_default
            ];
        }
        
        return $result;
    }
}

/**
 * Legacy function for PayPal currency codes (kept for backward compatibility)
 */
if (!function_exists("currency_codes")) {
	function currency_codes(){
		$data = array(
			"AUD" => "Australian dollar",
			"BRL" => "Brazilian dollar",
			"CAD" => "Canadian dollar",
			"CZK" => "Czech koruna",
			"DKK" => "Danish krone",
			"EUR" => "Euro",
			"HKD" => "Hong Kong dollar",
			"HUF" => "Hungarian forint",
			"INR" => "Indian rupee",
			"ILS" => "Israeli",
			"JPY" => "Japanese yen",
			"MYR" => "Malaysian ringgit",
			"MXN" => "Mexican peso",
			"TWD" => "New Taiwan dollar",
			"NZD" => "New Zealand dollar",
			"NOK" => "Norwegian krone",
			"PHP" => "Philippine peso",
			"PLN" => "Polish złoty",
			"GBP" => "Pound sterling",
			"RUB" => "Russian ruble",
			"SGD" => "Singapore dollar",
			"SEK" => "Swedish krona",
			"CHF" => "Swiss franc",
			"THB" => "Thai baht",
			"USD" => "United States dollar",
		);

		return $data;
	}
}

/**
 * Legacy currency format function (kept for backward compatibility)
 */
if (!function_exists("currency_format")) {
	function currency_format($number, $number_decimal = "", $decimalpoint = "", $separator = ""){
		$decimal = 2;

		if ($number_decimal == "") {
			$decimal = get_option('currency_decimal', 2);
		}else{
			$decimal = $number_decimal;
		}

		if ($decimalpoint == "") {
			$decimalpoint = ".";
		}

		if ($separator == "") {
			$separator = ",";
		}	

		$number = number_format($number, $decimal, $decimalpoint, $separator);
		return $number;
	}
}

/**
 * Legacy local currency code function (kept for backward compatibility)
 */
if (!function_exists("local_currency_code")) {
	function local_currency_code(){
		$data = array(   
		      	'USD',
			    'EUR',
			    'JPY',
			    'GBP',
			    'AUD',
			    'CAD',
			    'CHF',
			    'CNY',
			    'SEK',
			    'NZD',
			    'MXN',
			    'SGD',
			    'HKD',
			    'NOK',
			    'KRW',
			    'TRY',
			    'RUB',
			    'INR',
			    'BRL',
			    'ZAR',
			    'AED',
			    'AFN',
			    'ALL',
			    'AMD',
			    'ANG',
			    'AOA',
			    'ARS',
			    'AWG',
			    'AZN',
			    'BAM',
			    'BBD',
			    'BDT',
			    'BGN',
			    'BHD',
			    'BIF',
			    'BMD',
			    'BND',
			    'BOB',
			    'BSD',
			    'BTN',
			    'BWP',
			    'BYN',
			    'BZD',
			    'CDF',
			    'CLF',
			    'CLP',
			    'COP',
			    'CRC',
			    'CUC',
			    'CUP',
			    'CVE',
			    'CZK',
			    'DJF',
			    'DKK',
			    'DOP',
			    'DZD',
			    'EGP',
			    'ERN',
			    'ETB',
			    'FJD',
			    'FKP',
			    'GEL',
			    'GGP',
			    'GHS',
			    'GIP',
			    'GMD',
			    'GNF',
			    'GTQ',
			    'GYD',
			    'HNL',
			    'HRK',
			    'HTG',
			    'HUF',
			    'IDR',
			    'ILS',
			    'IMP',
			    'IQD',
			    'IRR',
			    'ISK',
			    'JEP',
			    'JMD',
			    'JOD',
			    'KES',
			    'KGS',
			    'KHR',
			    'KMF',
			    'KPW',
			    'KWD',
			    'KYD',
			    'KZT',
			    'LAK',
			    'LBP',
			    'LKR',
			    'LRD',
			    'LSL',
			    'LYD',
			    'MAD',
			    'MDL',
			    'MGA',
			    'MKD',
			    'MMK',
			    'MNT',
			    'MOP',
			    'MRO',
			    'MUR',
			    'MVR',
			    'MWK',
			    'MYR',
			    'MZN',
			    'NAD',
			    'NGN',
			    'NIO',
			    'NPR',
			    'OMR',
			    'PAB',
			    'PEN',
			    'PGK',
			    'PHP',
			    'PKR',
			    'PLN',
			    'PYG',
			    'QAR',
			    'RON',
			    'RSD',
			    'RWF',
			    'SAR',
			    'SBD',
			    'SCR',
			    'SDG',
			    'SHP',
			    'SLL',
			    'SOS',
			    'SRD',
			    'SSP',
			    'STD',
			    'SVC',
			    'SYP',
			    'SZL',
			    'THB',
			    'TJS',
			    'TMT',
			    'TND',
			    'TOP',
			    'TTD',
			    'TWD',
			    'TZS',
			    'UAH',
			    'UGX',
			    'UYU',
			    'UZS',
			    'VEF',
			    'VND',
			    'VUV',
			    'WST',
			    'XAF',
			    'XAG',
			    'XAU',
			    'XCD',
			    'XDR',
			    'XOF',
			    'XPD',
			    'XPF',
			    'XPT',
			    'YER',
			    'ZMW',
			    'ZWL',
		);
		return $data;
	}

}

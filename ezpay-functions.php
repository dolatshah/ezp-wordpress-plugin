<?php

function ezpay_get_plugin_version() {
    if(!function_exists('get_plugin_data')) {
        include(ABSPATH . "wp-admin/includes/plugin.php"); 
    }
    $plugin_data = get_plugin_data(dirname(__FILE__).DIRECTORY_SEPARATOR.'ezpay.php', FALSE, FALSE );
    return $plugin_data['Version'];
}

function convert_fa_to_en($string) {
	$persian_num 	= array('۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'); 
	$persian_num2 	= array('٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩');
	$latin_num 		= range(0, 9);
	$string 		= str_replace($persian_num, $latin_num, $string);
	$string 		= str_replace($persian_num2, $latin_num, $string);
	return $string;
}

function bill_type($bill_type){
	switch ($bill_type) {
		case 'water':	$new_bill_type="قبض آب"; break;
		case 'power':	$new_bill_type="قبض برق"; break;
		case 'gas':		$new_bill_type="قبض گاز";break;
		case 'phone':	$new_bill_type="تلفن ثابت"; break;
		case 'mobile':	$new_bill_type="تلفن همراه"; break;
		case 'taxes':	$new_bill_type="عوارض شهرداری"; break;
		case 'tax':	$new_bill_type = 'سازمان مالیات';break;
		case 'traffic_fines':	$new_bill_type = 'جریمه راهنمایی و رانندگی';break;
		default : $new_bill_type=""; break;
	}
	return $new_bill_type;
}

function ezpay_url_decrypt($string){
	$counter = 0;
	$data = str_replace(array('-','_','.'),array('+','/','='),$string);
	$mod4 = strlen($data) % 4;
	if ($mod4) {
		$data .= substr('====', $mod4);
	}
	$decrypted = base64_decode($data);
	$check = array('id','order_id','amount','ref_code','status');
	foreach($check as $str){
		str_replace($str,'',$decrypted,$count);
		if($count > 0){
			$counter++;
		}
	}
	if($counter === 5){
		return array('data'=>$decrypted , 'status'=>true);
	}else{
		return array('data'=>'' , 'status'=>false);
	}
}

function jdate_format($string, $format=null){
    if ($format === null) {
        $format = 'Y/m/d ساعت H:i:s';
    }
	require_once(SMARTY_PLUGINS_DIR . 'shared.make_timestamp.php'); 
    $timestamp = smarty_make_timestamp($string);
	return jdate($format, $timestamp);
}

function filter($value='',$type=''){
	global $mysqli;
	$value = strip_tags($value); // Strip HTML and PHP tags from a string
	$value = htmlspecialchars($value);
	$value = addslashes($value);
	$value = str_replace('<','',$value);
	$value = str_replace('>','',$value);
	
	if($type == 'number'){
		$value = trim($value);
	}
	if($type == 'get_int'){
		$value = intval($value);
	}
	
	return $value;
}
function check_referer_and_token($Token){
	global $mysqli,$CSRF_Token,$system_url;
	if( isset($Token) && $Token!='' && $Token==$CSRF_Token  ){
		$CSRF_result = true;
	}else{
		$CSRF_result = false;
	}
	// check referer
	if(isset($_SERVER['HTTP_REFERER']) && $_SERVER['HTTP_REFERER'] != ''){
		$sender_domain = parse_url($_SERVER['HTTP_REFERER'],PHP_URL_HOST);
		$sender_domain = str_replace("http://","",$sender_domain);
		$sender_domain = str_replace("https://","",$sender_domain);
		$sender_domain = str_replace("www.","",$sender_domain);
		
		$url = parse_url($system_url.'/panel',PHP_URL_HOST);
		
		echo "sender_domain: $sender_domain<br/>";
		echo "url: $url<br/>" ;
		
		$rrr = strpos($sender_domain, $url);
		var_dump($rrr);
		
		if( strpos($sender_domain, $url)!==false ){
			$referer_result = true;
		}else{
			$referer_result = false;
		}
	}else{
		$referer_result = false;
	}

	if( $referer_result===false || $CSRF_result===false ){
		header( "Location: ./index.php" );
		$last_result = false;
	}else{
		$last_result = true;
	}
	
	return $last_result;
}
function RequestJson_Last($method,$param,$which=''){	
	global $ezpay_option;
	$api_url 	= "https://ezpay.ir/webservice.php";

	if (!is_string($method)) {
		error_log("Method name must be a string\n");
		return false;
	}

	if (!$param) {
		$param = array();
	}else if (!is_array($param)) {
		error_log("Parameters must be an array\n");
		return false;
	}

	$parameters = array(
		'username'	=> $ezpay_option['username'],
		'password'	=> $ezpay_option['password'],
		'method'	=> $method
	);
	

	if(isset($param) && !empty($param)){
		foreach( $param as $key => $value)
			$parameters[$key] = $value;		
	}


	$handle = curl_init($api_url);
	curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($handle, CURLOPT_CONNECTTIMEOUT, 20);
	curl_setopt($handle, CURLOPT_TIMEOUT, 60);
	curl_setopt($handle, CURLOPT_ENCODING, "");
	curl_setopt($handle, CURLOPT_POSTFIELDS, json_encode($parameters));
	curl_setopt($handle, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));

	return exec_curl_request1($handle);
}

function exec_curl_request1($handle) {
	$response = curl_exec($handle);

	if ($response === false) {
		$errno = curl_errno($handle);
		$error = curl_error($handle);
		error_log("Curl returned error $errno: $error\n");
		curl_close($handle);
		return false;
	}
	
	$http_code = intval(curl_getinfo($handle, CURLINFO_HTTP_CODE));
	curl_close($handle);	
	
	if ($http_code >= 500) {
		sleep(10);
		return false;
	}
	elseif ($http_code != 200) {
		$response = json_decode($response, true);
		error_log("Request has failed with error {$response['error_code']}: {$response['msg']}\n");
		if ($http_code == 401) {
			throw new Exception('Invalid access token provided');
		}
		return false;	
	}
	else{
		$response = json_decode($response, true);
		if (isset($response['msg'])  ) {
		}		
	}	
	return $response;
}

?>
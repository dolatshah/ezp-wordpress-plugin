<?php

if( isset($_POST['action']) && $_POST['action']=='check_bill' ){
	
	require_once( dirname( __FILE__ ) .'/validation.php' );
	$validate = new SimaNet_Validate;
	
	require_once EZPAY_DIR.'ezpay-functions.php';
	
	$mysqli = @new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
	mysqli_set_charset($mysqli,"utf8");
	date_default_timezone_set("Asia/Tehran");
	
	$bill_id = (isset($_POST['bill_id']) && $_POST['bill_id']!='' ) ? filter($_POST['bill_id'],'number') : '';
	$pay_id = (isset($_POST['pay_id']) && $_POST['pay_id']!='' ) ? filter($_POST['pay_id'],'number') : '';
	$mobile = (isset($_POST['mobile']) && $_POST['mobile']!='' ) ? filter($_POST['mobile'],'number') : '';
	 
	if($bill_id == ""){
		$error_msg = 'لطفا شناسه قبض را وارد کنید !';
	}
	elseif($pay_id == ""){
		$error_msg = 'لطفا شناسه پرداخت را وارد کنید !';
	}
	elseif($mobile == ""){
		$error_msg = 'شماره موبایل را وارد کنید !';
	}
	elseif(!$validate->Number($bill_id)){
		$error_msg = 'شناسه قبض صحیح نیست !';
	}
	elseif(!$validate->Number($pay_id)){
		$error_msg = 'شناسه پرداخت صحیح نیست !';
	}
	elseif(!$validate->Mobile($mobile)){
		$error_msg = 'شماره موبایل صحیح نیست !';
	}
	else{
		$param = array(
			'bill_id'		=> $bill_id,
			'pay_id'		=> $pay_id,
		);

		$result = RequestJson_Last('check_bill',$param,'ezpay');

		
		if( isset($result) && $result!=false ){
			$res_code = $result['code'];
			if($res_code!=1){
				$error_msg = $result['msg'];
			}else{
				$type_en 		= $result['type_en'];
				$amount 		= $result['amount'];
				$pay_type 		= $result['pay_type'];
				$amount_rial 	= $amount*10; 
				$bill_type_name = bill_type($type_en);
				
				if($pay_type=='online'){
					$pay_type_fa = 'پرداخت آنلاین';
				}elseif($pay_type=='credit'){
					$pay_type_fa = 'پرداخت از اعتبار';
				}

				$check_bill_result = json_encode($result,JSON_UNESCAPED_UNICODE);
				
				$date 	= date('Y-m-d H:i:s');
				$client_id=0;
				$mysqli->query("INSERT INTO wp_ezpay_bill (client_id, bill_id, pay_id,bill_type, amount,mobile,check_bill_result, date ) VALUES ('$client_id', '$bill_id', '$pay_id', '$type_en', '$amount_rial', '$mobile', '$check_bill_result', '$date' )");
				$db_id = $mysqli->insert_id;
				
				$error_msg = 'no';
			}
		}
	}
	
	if($error_msg =='no'){
		$res = json_encode( array( "error_msg"=>$error_msg, "bill_dbid"=>$db_id, "type"=>$bill_type_name, "pay_type"=>$pay_type_fa, "amount" => number_format($amount_rial) ) ); 
	}else{
		$res = json_encode( array( "error_msg"=>$error_msg ) ); 
	}

	echo $res;
	
}
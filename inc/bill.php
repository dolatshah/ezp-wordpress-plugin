<?php
if(isset($_GET['list']) ){
	$smarty->assign('bill_list',true);
	$smarty->assign('title', 'لیست قبض ها');
	
	if(isset($_GET['ezpay_token']) && $_GET['ezpay_token']!=''){
		$smarty->assign('bill_list',true);
		$smarty->assign('title', 'نتیجه تراکنش');
		$ezpay_token 	= $_GET['ezpay_token'];
		$decrypted 		= ezpay_url_decrypt( $ezpay_token );
		if($decrypted['status']){
			parse_str($decrypted['data'], $ir_output);
			$trans_id 	= $ir_output['id'];
			$order_id 	= $ir_output['order_id'];
			$amount 	= $ir_output['amount'];
			$ref_code	= $ir_output['ref_code'];
			$status 	= $ir_output['status'];
			
			$can_display_result = true;
			
			$order_id = substr($order_id, 0, -4);
			
			$date 	= date('Y-m-d H:i:s');
			$pay_bill_result = json_encode($ir_output,JSON_UNESCAPED_UNICODE);
			
			if($status == 'paid'){
				$mysqli->query("update wp_ezpay_bill set status='$status',refcode='$ref_code',pay_date='$date',pay_bill_result='$pay_bill_result' where id='$order_id' and status='unpaid' ");
			}else{
				$mysqli->query("update wp_ezpay_bill set status='$status',pay_bill_result='$pay_bill_result' where id='$order_id' ");
			}
		}
	}
	
	$cond='';
	if(  isset($can_display_result) && isset($_GET['id']) && $_GET['id']!='' ){
		$bill_id= filter($_GET['id']);
		$cond = " and id='$bill_id' ";
		
		$client_id 	= 0;
		$sql_bill = $mysqli->query("SELECT * FROM wp_ezpay_bill where client_id='$client_id' $cond ORDER BY id DESC ") or die($mysqli->error);
		$rows_bill = $sql_bill->fetch_assoc();
		$rows_bill['name_family'] 	= '';
		$smarty->append('rows_bill', $rows_bill);
		
	}
	
}
else{
	$smarty->assign('pay_bill',true);
	$smarty->assign('title', 'پرداخت قبض');
		
	if(isset($_POST['pay_submit']) ){
		
		check_admin_referer( 'name_of_my_action', 'Token' );

		
		if( empty($_POST['bill_dbid']) || $_POST['bill_dbid']=='' ) {
			$error_msg = 'شناسه پرداخت موجود نیست';
		}
		else{
			$db_id 		= $_POST['bill_dbid'];
			$bill_row 	= $mysqli->query("SELECT * FROM wp_ezpay_bill WHERE id='$db_id' ")->fetch_assoc();
			if( !isset($bill_row['id']) ){
				$error_msg = 'چنین صورتحسابی یافت نشد';
			}else{
				$bill_id 	= $bill_row['bill_id'];
				$pay_id 	= $bill_row['pay_id'];
				$amount 	= $bill_row['amount'];
				$mobile 	= $bill_row['mobile'];
				
				$get_permalink = esc_url( get_permalink() );
				$callback = "{$get_permalink}?list&id=$db_id";

				$param = array(
					'bill_id'		=> $bill_id,
					'pay_id'		=> $pay_id,
					'mobile'		=> $mobile,
					'order_id'		=> $db_id.rand(1000, 9999),
					'callback'		=> $callback,
				);
				$result = RequestJson_Last('pay_bill',$param,'ezpay');
	
				
				if( isset($result) && $result!=false ){
					$res_code = $result['code'];
					if($res_code!=1){
						$error_msg = $result['msg'];
					}else{
						$type_en 		= $result['type_en'];
						$amount 		= $result['amount'];
						$url 			= $result['url'];
						$pay_type 		= $result['pay_type'];
						
						$pay_bill_result = json_encode($result,JSON_UNESCAPED_UNICODE);
						$mysqli->query("update wp_ezpay_bill set pay_type='$pay_type',url='$url', pay_bill_result='$pay_bill_result' where id='$db_id' ");
						
						if($pay_type=='online'){
							if(headers_sent()){
								echo "<script>window.location.href = '$url'; </script>";
							}
							else{
								header("Location: $url");
							}
						}
						else{
							$clients_rows 	= Get_Clients_Rows('id',$client_id);
							$credit = $clients_rows['credit'];
							if($credit < $amount){
								$error_msg = "موجودی حساب کاربری شما جهت انجام این تراکنش کافی نیست !";
								$mysqli->query("update wp_ezpay_bill set code='-2020',date='$date' where id='$db_id' ");
							}else{
								echo "oflline";
							}
						}
					}
				}
			}
		}
	}
}

if(isset($error_msg)){$smarty->assign('error_msg',$error_msg);}
if(isset($success_msg)){$smarty->assign('success_msg',$success_msg);}
$smarty->display( dirname( __FILE__ ) . '/templates/bill.tpl');
?>		
paypal payment verification page
<?php

include_once($_SERVER['DOCUMENT_ROOT'].'/wp-config.php');		 
include_once($_SERVER['DOCUMENT_ROOT'].'/wp-load.php');
include_once($_SERVER['DOCUMENT_ROOT'].'/wp-includes/wp-db.php');

global $wpdb;
	

$to = "panoskatws@gmail.com";
$subject = "[cityger]";
$message = "RESPONCE FROM PAYPAL";
$from = "panos.lyrakis@gmail.com";
$headers = "From:" . $from;


if (isset($_POST["txn_id"]) && isset($_POST["txn_type"])){
	
	 $req = 'cmd=_notify-validate';
    foreach ($_POST as $key => $value) {
        $value = urlencode(stripslashes($value));
        $value = preg_replace('/(.*[^%^0^D])(%0A)(.*)/i','${1}%0D%0A${3}',$value);// IPN fix
        $req .= "&$key=$value";
    }
	
	$bookID = addslashes( $_POST['item_number'] );
	$payment_amount = addslashes( $_POST['mc_gross'] );
	
	
	
	
	// assign posted variables to local variables
	$data['item_name']			= addslashes( $_POST['item_name'] );
	$data['item_number'] 		= addslashes( $_POST['item_number'] );
	$data['payment_status'] 	= addslashes( $_POST['payment_status'] );
	$data['payment_amount'] 	= addslashes( $_POST['mc_gross'] );
	$data['payment_currency']	= addslashes( $_POST['mc_currency'] );
	$data['txn_id']				= addslashes( $_POST['txn_id'] );
	$data['receiver_email'] 	= addslashes( $_POST['receiver_email'] );
	$data['payer_email'] 		= addslashes( $_POST['payer_email'] );
	$data['custom'] 			= addslashes( $_POST['custom'] );
	
	$header = "POST /cgi-bin/webscr HTTP/1.0\r\n";
	$header .= "Content-Type: application/x-www-form-urlencoded\r\n";
	$header .= "Content-Length: " . strlen($req) . "\r\n\r\n";
	
	$fp = fsockopen ('ssl://www.paypal.com', 443, $errno, $errstr, 30);

	if (!$fp) {
		// HTTP ERROR
		mail($to,$subject,"HTTP ERROR bookID: ".$bookID,$headers);
	} else {
				//mail('ash@evoluted.net', '0', '0');
		fputs ($fp, $header . $req);
		while (!feof($fp)) {
			$res = fgets ($fp, 1024);
			if (strcmp ($res, "VERIFIED") == 0) {

				// Validate payment (Check unique txnid & correct price)
				$valid_txnid = check_txnid($data['txn_id']);
				//$valid_price = check_price($data['payment_amount'], $data['item_number']);
								
				// PAYMENT VALIDATED & VERIFIED!
				if($valid_txnid){
					$paymentMade = updatePayments($data);
					if($paymentMade){
						// Payment has been made & successfully inserted into the Database
						//mail($to,$subject.' New payment for booking #'.$bookID,"There has been a new payment for booking #".$bookID. '. Please enter with your administration account to update this booking\'s status',$headers);
						mail($to,$subject.' New Paypal payment for booking #'.$bookID,"There has been a new payment of ".$data['payment_amount']." ".$data['payment_currency']." for booking number #".$bookID." from paypal. Please check your paypal account and enter at your management panel and update payments and status of this booking.",$headers);
						if( $valid_price ){ //Gia na doulepsai ayto prepei na ftiaxtei h synarthsh check_price()
							//mail($to,$subject,"Payment has been made & successfully inserted into the Database bookID: ".$bookID,$headers);
							//$result = $wpdb->query('update eb_bookingdata set booking_status = "Confirmed" where bookingID = '.$bookID);
						}
						else{
							//mail($to,$subject.' [incomplete payment]',"Less Payment has been made & successfully inserted into the Database bookID: ".$bookID,$headers);
							
						}
					}else{
						// Error inserting into DB
						// E-mail admin or alert user
						mail($to,$subject,"Error inserting into DB for bookID: ".$bookID,$headers);
					}
				}else{
					// Payment made but data has been changed
					// E-mail admin or alert user
					mail($to,$subject,"Payment made but payment amount changed for bookID: ".$bookID,$headers);
				}

			}else if (strcmp ($res, "INVALID") == 0) {

				// PAYMENT INVALID & INVESTIGATE MANUALY!
				// E-mail admin or alert user
				mail($to,$subject,"Payment not verified by paypal for bookID: ".$bookID,$headers);
			}
		}
	fclose ($fp);
	}

	
	
	//mail($to,$subject.' OK',$req,$headers);
}
else mail($to,$subject.' ERROR',"ERROR with POST txn_id",$headers);


function check_txnid($txnid){
	//we just have to check if paypal sends same payment data twice (that may happen in case of slow transactions...)
    global $wpdb;
    $valid_txnid = true;
    //get result set
    $txn_id_exists = $wpdb->get_var('select txn_id from eb_bookingdata where txn_id = "'.$txnid.'" ');
    if( $txn_id_exists != '' ) $valid_txnid = false;

    return $valid_txnid;
}


function check_price($price, $id){
	global $wpdb;
   $valid_price = false;
   
   $booking_priceR = $wpdb->get_row('select booking_total, booking_paymentCharge from eb_bookingdata where bookingID = '.$id );
   $booking_price = $booking_priceR->booking_total;
   //$pp_costR = $wpdb->get_var('select booking_paymentCharge from eb_bookingdata where bookingID = '.$id );
   $pp_cost = $booking_priceR->booking_paymentCharge;
   $booking_price_withPPcost = number_format($booking_price, 2) + number_format($pp_cost, 2); 
   if( $booking_price_withPPcost == $price ) return true;
	else return false;
	//mail("panoskatws@gmail.com","test","booking price: ".$booking_price_withPPcost.' payed price: '.$price. ' book id: '.$id. ' - '.$valid_price);
   //return $valid_price;
}



function updatePayments($data){
   global $wpdb;
	if(is_array($data)){
		$wpdb->query('update eb_bookingdata set booking_deposit = "'.$data['payment_amount'].'", txn_id = "'.$data['txn_id'].'", booking_status = "Pending", booking_paymentMethod = "paypal" where bookingID = '.$data['item_number']);
		return true;
		//if ( $wpdb->query('update eb_bookingdata set booking_deposit = "'.$data['payment_amount'].'", txn_id = "'.$data['txn_id'].'", booking_status = "Confirmed" where bookingID = '.$data['item_number']) === FALSE )
			//return false;
		//else return true;
		//$result = $wpdb->query('update eb_bookingdata set booking_deposit = "'.$data['payment_amount'].'", txn_id = "'.$data['txn_id'].'", booking_status = "Confirmed" where bookingID = '.$data['item_number']);		
    }
    else return false;    
}


?>
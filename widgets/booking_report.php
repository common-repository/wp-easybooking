<?php
global $wpdb;
global $table_prefix;
$bookID = '';
$pin= rand(1000, 9999);

$lang = '';
if( function_exists(qtrans_getLanguage) ){
	if( isset($_REQUEST['lang']) && $_REQUEST['lang'] != '' )
		$lang = 'lang='.addslashes($_REQUEST['lang']);
	else 
		$lang = 'lang='.$q_config["default_language"];		
}


if( isset($_POST['payment']) && $_POST['payment'] != '' ){
	$bID = addslashes( $_POST['bID'] );

	$payment = addslashes( $_POST['payment'] );
	$paymentMethod = '';

	if( $payment == 'paypal'){

		$paymentMethod = "Paypal";

		$bookID = makeBooking( $pin );
		
		$bookIDQ = 'b_'.$bookID;
		$ccur = addslashes( $_POST['ccur'] );
		$lname = addslashes( $_POST['lname'] );
		$fname = addslashes( $_POST['fname'] );
		$email = addslashes( $_POST['email'] );
	
		$eb_BusinessPaypalAccount = get_post_meta($bID, "eb_paypalAccount");
		if(!empty($eb_BusinessPaypalAccount)) $eb_BusinessPaypalAccount = $eb_BusinessPaypalAccount[0]; else $eb_BusinessPaypalAccount ='';		
		
		$totalRoomCost = addslashes( $_POST['total-with-paypal'] );
	
		$pageID = get_option('eb-view-bookings');
		$permalink = '';
		if( get_option('permalink_structure')  == "") $permalink = get_site_url().'?page_id='.$pageID.'&';
		else $permalink = get_permalink( $pageID ).'?'; 
		
		$pageIDSuccess = get_option('eb-booking-success');
		$permalinkSuccess = '';
		if( get_option('permalink_structure')  == "") $permalinkSuccess = get_site_url().'?page_id='.$pageIDSuccess.'&';
		else $permalinkSuccess = get_permalink( $pageIDSuccess ).'?';
				
		$pin_encoded = md5($pin.AUTH_SALT); 
		$return_url = $permalinkSuccess.$lang.'&eb=pp_IPN';
		$cancel_url = $permalink.$lang.'&eb=report&cmd=cancel&b='.$bookID.'&p='.$pin_encoded;
		$siteURL = site_url();

		?>
		<h2>Please wait... You are being redirected to paypal to complete your booking...</h2>
		<form action="https://www.paypal.com/cgi-bin/webscr" method="post" id="paypal-frm" >
			<input type="hidden" name="business" value="<?php echo $eb_BusinessPaypalAccount;?>"/>
			<input name="cmd" type="hidden" value="_xclick" />
   		<input name="no_note" type="hidden" value="1" />
   		<input name="lc" type="hidden" value="UK" />
   		<input name="currency_code" type="hidden" value="<?php echo $ccur; ?>" />
   		<input name="bn" type="hidden" value="PP-BuyNowBF:btn_buynow_LG.gif:NonHostedGuest" />
   		<input name="first_name" type="hidden" value="<?php echo urlencode($fname); ?>" />
   		<input name="last_name" type="hidden" value="<?php echo urlencode($lname); ?>" />
   		<input name="payer_email" type="hidden" value="<?php echo addslashes( $_POST['email'] ); ?>" />
   		<input name="item_number" type="hidden" value="<?php echo urlencode($bookID); ?>" />
   		<input name="amount" type="hidden" value="<?php echo urlencode($totalRoomCost); ?>" />
   		<!--<input name="amount" type="hidden" value="0.01" />-->
   		<input name="item_name" type="hidden" value="<?php echo get_bloginfo('name').' '. urlencode($bookID); ?>" />
   		<input name="return" type="hidden" value="<?php echo stripslashes($return_url); ?>" />
   		<input name="cancel_return" type="hidden" value="<?php echo stripslashes($cancel_url); ?>" />
   		<input name="notify_url" type="hidden" value="<?php echo $siteURL; ?>/wp-content/plugins/wp-easybooking/widgets/paypal_functions.php" />
   		<!--<input name="notify_url" type="hidden" value="<?php echo stripslashes($return_url); ?>" />-->
   		<!--return with post-->
   		<input type="hidden" name="custom" value="<?php echo $pin; ?>" />
   		<input type="hidden" name="rm" value="2" /> 
   		<input type="hidden" name="cbt" value="Return to <?php echo get_bloginfo('name'); ?>">
   		
		</form>
		<script type="text/javascript" >
		jQuery("#paypal-frm").submit();
		</script>
		<?php	

	}
	if( $payment == 'bank'){

		$paymentMethod = "Bankwire";
		$bookID = makeBooking( $pin );

		include_once(ABSPATH.'wp-content/plugins/wp-easybooking/widgets/load_booking.php');
		
		$page_id = get_option('eb-view-bookings');
		$permalink = '';
		
		$lang = 'en';
		if( isset( $_REQUEST['lang'] ) && $_REQUEST['lang'] != '' ) $lang = addslashes( $_REQUEST['lang'] );
		$lang = explode('?', $lang);
		$lang = $lang[0];		

		
		if( get_option('permalink_structure')  == "") $permalink = get_site_url().'?page_id='.$page_id.'&';
		else $permalink = get_permalink( $page_id ).'?';
	?>
		<form action="<?php echo $permalink.'lang='.$lang; ?>" method="post" id="bankPaymentMethodFrm">
		<input type="hidden" name="eb" value="bookings" />
		<input type="hidden" name="bookID" value="<?php echo $bookID;?>"  />
		<input type="hidden" name="pin" value="<?php echo $pin;?>" />
		<input type="hidden" name="bID" value="<?php echo $bID;?>" />
		<br />
		<div style="width:100%;" align="center"><input type="submit" value="<?php _e( $eb_lang_ViewBooking ); ?>" class="eb-search-button" /></div>
	</form>
	Please wait! You are being redirected to your booking's page!
	
	<script type="text/javascript" >
		var redirect_action = jQuery('#bankPaymentMethodFrm').attr('action');				
		
		jQuery('#bankPaymentMethodFrm').submit();//gia na mas paei sth selida ths krathshs giati ayth gia kapoio logo spaei gamw to poutana mou
	</script>
	<?php
	}
	if( $payment == 'paylater'){
		//echo 'pay later';
	}

	//========DISPLAY BOOKING==========	

}
else {
	echo 'ERROR: No payment method selected by user';
}

function makeBooking( $pin ){
	global $wpdb;
	global $table_prefix;
	$bID = addslashes( $_POST['bID'] );
	$lname = addslashes( $_POST['lname'] );
	$fname = addslashes( $_POST['fname'] );
	$email = addslashes( $_POST['email'] );
	$tel = addslashes( $_POST['tel'] );
	$country = addslashes( $_POST['country'] );	
	
	$from = addslashes( $_POST['from'] );
	$to = addslashes( $_POST['to'] );
	$ccur = addslashes( $_POST['ccur'] );
	
	$totalRoomCost = addslashes( $_POST['totalRoomCost'] );
	$totalRoomCostBcur = addslashes( $_POST['totalRoomCostBcur'] );

	$paymentMethod = addslashes( $_POST['payment'] );
	$paymentCharge = 0.00;
	if( $paymentMethod == "paypal" ) $paymentCharge = addslashes( $_POST['paypal-fee'] );
	
	$interval = date_diff(date_create( $from ), date_create( $to ) );
	$daysNum = (int)$interval->format('%a');
	
	$roomHolder = explode( "|", addslashes( $_POST['roomHolder'] ) );
	$roomNum = sizeof($roomHolder) - 1;
	
	
	$wpdb->insert( 
			'eb_bookingdata', 
				array( 
					'businessID' => $bID,
					'pin' => $pin,
					'customerID' => $bs_clientID, 
					'customer_fname' => $fname,
					'customer_lname' => $lname,
					'customer_email' => $email,
					'customer_tel' => $tel,
					'customer_country' => $country,
					'dateRange_start' => gmdate(dateToYMD($from)." 00:00:00"),
					'dateRange_end' => gmdate(dateToYMD($to)." 00:00:00"),
					'booking_date' => gmdate("Y-m-d H:i:s"),										
					'booking_currency' => $ccur,
					'booking_total' => $totalRoomCost,
					'booking_totalBCUR' => $totalRoomCostBcur,
					'booking_paymentMethod' => $paymentMethod,
					'booking_paymentCharge' => $paymentCharge,
					'booking_status' => 'Pending',
					'bookedNights' => (int)$daysNum,
					'numberOfRooms' => (int)$roomNum
				), 
				array( '%d','%d','%d','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%d','%d' ) 
	);
		
	$newBookID = $wpdb->insert_id;
	
	$emailRoomsData = '';
	$rCounter = 0;
	for($rh=0;$rh< $roomNum;$rh++){
		$rCounter++;
		list($roomID, $roomCounter, $singleRoomPrice, $singleRoomPrice_siteCur) = explode( ':', $roomHolder[$rh] );
		
		$babies = 0;
		
		if( isset( $_POST['babies_'.$roomID.'_'.$roomCounter] ) && $_POST['babies_'.$roomID.'_'.$roomCounter] != '' ) $babies = addslashes( $_POST['babies_'.$roomID.'_'.$roomCounter] );
		
		$guest = addslashes( $_POST['guest_'.$roomID.'_'.$roomCounter] );
		$wpdb->insert( 
						'eb_bookingroomdata', 
							array( 
								'bookingID' => $newBookID, 
								'roomID' => $roomID,
								'roomCost' => $singleRoomPrice,
								'roomCost_siteCur' => $singleRoomPrice_siteCur,
								'businessID' => $bID,
								'noOfBabies' => $babies,
								'extraBedNum' => $rOptExtraBedNum,
								'extraBedPrice' => $rOptExtraBedPrice,								
								'guestFullName' => $guest,
								'dateRange_start' => gmdate(dateToYMD($from)." 00:00:00"),
								'dateRange_end' => gmdate(dateToYMD($to)." 00:00:00"),
								'canceled' => 'NO'
							), 
							array( '%d','%d','%s','%s','%d','%d','%d','%s','%s','%s','%s','%s','%s') 
		);
		$roomsAdults = get_post_meta($roomID, "eb_peopleNum");
					$roomsAdults = $roomsAdults[0];
					$roomsChildren = get_post_meta($roomID, "eb_childrenAllowed");
					$roomsChildren = $roomsChildren[0];
		
		$emailRoomsData .= '<div style="margin-top:10px;font-size:14px; width:100%;background-color: #48f;border:none;color: #fff;padding-left: 10px;">';
			$emailRoomsData .= 'Room #'.$rCounter.' type: <strong>'.get_the_title($roomID).'</strong>';
		$emailRoomsData .= '</div>';
			$emailRoomsData .= '<div style="width:100%;	margin: 10px 0 0 10px;padding : 0px 0px 0px 20px;border-left: 3px solid #f19c06;">';
				$emailRoomsData .= '<div style="color: #48f;font-size: 12px;padding-top:2px;">';
					$emailRoomsData .= 'Adults <strong>'. $roomsAdults.' </strong>';
					$emailRoomsData .= 'Children <strong>'. $roomsChildren.' </strong>';
				$emailRoomsData .= '</div>';
			$emailRoomsData .= '</div>';
		
	}
	
	$hotelCityID = get_post_meta($bID, "eb_cityID");
	$hotelCity = '';
	$hotelCountry = '';
	if( !empty($hotelCityID) ) {
		$hotelCityData = $wpdb->get_row('select CountryID, City from eb_cities where CityId = '.$hotelCityID[0] );
		$hotelCity = __($hotelCityData->City);	
		$hotelCountryData = $wpdb->get_row('select Country from eb_countries where CountryId = '.$hotelCityData->CountryID);
		$hotelCountry = __($hotelCountryData->Country);
	}
	$page_id = get_option('eb-view-bookings');
	$permalink = '';
	if( get_option('permalink_structure')  == "") $permalink = get_site_url().'?page_id='.$page_id;
	else $permalink = get_permalink( $page_id );
			
	$businessEmail = get_post_meta($bID, "eb_email");
	if(!empty($businessEmail)) $businessEmail = $businessEmail[0]; else $businessEmail ='';
		
	$hotelTitle = get_the_title($bID);
	$site_url = network_site_url( '/' );
	$customerSubject = '['.get_bloginfo('name').'] '.__( 'Your booking has been completed' );
	$ownerSubject = '['.$hotelTitle.'@'.get_bloginfo('name').'] '.__( 'New booking for your hotel (#'.$newBookID.')' );
	
	$getOwner = $wpdb->get_row('select post_author from '.$table_prefix.'posts where ID ='.$bID);
	$ownerData = get_userdata( $getOwner->post_author );
		
	$ownerContent = '';
	
	$bothContent = '<div style= "color:#48f;">';
	$customerContent = $bothContent;
	$ownerContent = $bothContent;
	
	$customerContentOnly = 'Hello Mr/Mrs '.$lname.' '.$fname.',<br /><br />
		Your booking has been completed successfully at <strong><a href="'.$site_url.'">'.get_bloginfo('name').'</a></strong>
		<div style="margin: 10px 0 10px 0;">
			<form action = "'.$permalink.'" method="post">
			<input type="hidden" name="eb" value="bookings" />
			<input type="hidden" name="bookID" value="'.$newBookID.'" />
			<input type="hidden" name="pin" value="" />	
			<input type="hidden" name="bID" value="10" />
			<em style="color:#999;font-size:11px;">To view your booking online please press the "View booking" button</em>
			<input type="submit" value="View booking" class="eb-search-button" />
			</form>
		';
		$customerContent .= $customerContentOnly;
		
		$ownerContent .= 'Hello Mr/Mrs '.$ownerData->last_name.' '.$ownerData->first_name.',<br /><br />
		You have a new booking for <strong>'.$hotelTitle.'</strong>';
		
		$bothContent = '</div>
		<div style="margin-top:10px;font-size:14px; width:100%;background-color: #48f;border:none;color: #fff;padding-left: 10px;"><strong>Booking number: <span style="font-size:16px;">#'.$newBookID.'</span></strong></div>
			<div style="padding: 10px 10px 10px 10px;font-size: 14px;color: #48f;">';
		
		$customerContent .= $bothContent;
		$ownerContent .= $bothContent;
		
			$customerContentOnly = '<div>PIN: <strong>'.$pin.'</strong> <em style="color:#999;font-size:11px;">*You will need this PIN each time you want to view your booking online.</em></div>';
			$customerContent .= $customerContentOnly;
				
			$bothContent = '<div>Status: <strong>Pending</strong></div>		
			<div id="booking-resort-details">
				<span id="resort-name">Hotel: <strong>'.$hotelTitle.'</strong></span>
				<span style="font-size: 11px;">
				&nbsp;( <span id="resort-city">City: <strong>'.$hotelCity.'</strong></span>
				<span id="resort-country">Country: <strong>'.$hotelCountry.'</strong></span> )
				</span>		
			</div>
			
			<div style="clear:both;line-height: 10px;">&nbsp;</div>
			
			<div>Stay for <strong>'.(int)$daysNum.' nights</strong></div>
			<div>Arrival: <strong>'.$from.'</strong></div>
			<div>Departure: <strong>'.$to.'</strong></div>
			
			<div style="clear:both;line-height: 10px;">&nbsp;</div>
			 
			<div>
				Number of booked rooms: <strong>'.(int)$roomNum.' rooms</strong>
				<div style="padding-left:20px">
					'.$emailRoomsData.'
				</div>
			</div>
		</div>	
		
		<div style="clear:both;line-height: 10px;">&nbsp;</div>
		';
		
		$customerContent .= $bothContent;
		$ownerContent .= $bothContent;
		
		$totalBcost = number_format($totalRoomCost ,2) + number_format($paymentCharge ,2);
		$pCharge = 0;
		if( $paymentCharge > 0) $pCharge = $paymentCharge;
		 
		$bothContent = '
		<div>Booking cost: <strong>'.$totalRoomCost.' '.$ccur.'</strong></div>
		<div>Payment method: <strong>'.$paymentMethod.'</strong></div>
		<div>Payment expenses: <strong>'.$pCharge.' '.$ccur.'</strong></div>
		<div>Total: <strong>'.$totalBcost.' '.$ccur.'</strong></div>	
		';
		 
		 if( $paymentMethod == "bank" ){
		 	$bothContent .= '<div style="margin-top:10px;font-size:14px; width:100%;background-color: #48f;border:none;color: #fff;padding-left: 10px;"><strong>Bank account</strong></div>';
		 	$eb_BusinessIBAN = get_post_meta($bID, "eb_IBAN");
		if(!empty($eb_BusinessIBAN)) $eb_BusinessIBAN = $eb_BusinessIBAN[0]; else $eb_BusinessIBAN ='';
		
		$eb_BusinessSWIFT = get_post_meta($bID, "eb_SWIFT");
		if(!empty($eb_BusinessSWIFT)) $eb_BusinessSWIFT = $eb_BusinessSWIFT[0]; else $eb_BusinessSWIFT ='';
		
		$eb_BusinessBankName = get_post_meta($bID, "eb_BankName");
		if(!empty($eb_BusinessBankName)) $eb_BusinessBankName = $eb_BusinessBankName[0]; else $eb_BusinessBankName ='';
		
		$eb_BusinessBankAccount = get_post_meta($bID, "eb_bankAccount");
		if(!empty($eb_BusinessBankAccount)) $eb_BusinessBankAccount = $eb_BusinessBankAccount[0]; else $eb_BusinessBankAccount ='';
		
		$businessCurrency =  get_post_meta($businessId, "eb_currency");
		if(!empty($businessCurrency)) $businessCurrency = $businessCurrency[0]; else $businessCurrency ='';
		
		$bothContent .= '
		<div>Bank name: <strong>'.$eb_BusinessBankName.'</strong></div>
		<div>Bank account: <strong>'.$eb_BusinessBankAccount.'</strong></div>
		<div>IBAN: <strong>'.$eb_BusinessIBAN.'</strong></div>';
		if( $eb_BusinessSWIFT !='' ) $bothContent .= ' <div>SWIFT: <strong>'.$eb_BusinessSWIFT.'</strong></div>';
		$bothContent .= '<div>Clarification code: <strong>'.get_bloginfo('name').'-'.$newBookID.'</strong> <em style="color:#666;font-size:12px;">So the payment will be recognized by receiver</em></div>	
		';
		 }
		
	$bothContent .= '	
	</div>
	';
	$customerContent .= $bothContent;
	$ownerContent .= $bothContent;
	
	$ownerContent .= '<div style="clear:both;line-height: 10px;">&nbsp;</div>';
	
	$ownerContent .= '<div style="margin-top:10px;font-size:14px; width:100%;background-color: #48f;border:none;color: #fff;padding-left: 10px;">Customer data</div>';
	$ownerContent .= '<div style="padding: 10px 10px 10px 10px;font-size: 14px;color: #48f;">
		<div>Last name: <strong>'.$lname.'</strong></div> 
		<div>First name: <strong>'.$fname.'</strong></div>
		<div>email: <strong>'.$email.'</strong></div>
		<div>Tel.: <strong>'.$tel.'</strong></div>
		<div>Country: <strong>'.$country.'</strong></div>
	</div>';
	
	$customerContent .= '<br /><br /><em style="color:#999;font-size:11px;">This email has been generated by '.get_bloginfo('name').' to inform it\'s customers. If it was not intended for you please ignore it or help us out by informing us at '.get_bloginfo('admin_email').'</em>';
	$ownerContent .= '<br /><br />Please visit <a href="'.get_bloginfo('url').'/wp-admin/admin.php?page=bookings_menu&bID='.$bID.'&book='.$newBookID.'">'.get_bloginfo('name').' ('.get_bloginfo('url').'/wp-admin/admin.php?page=bookings_menu&bID='.$bID.'&book='.$newBookID.')</a> to view details and edit this booking';
	$customerEmail = $email;
	
	add_filter('wp_mail_content_type',create_function('', 'return "text/html"; '));
  	add_filter('wp_mail_from', create_function('', 'return "'.get_bloginfo('admin_email').'"; '));
	add_filter('wp_mail_from_name', create_function('', 'return "'.get_bloginfo('name').'"; '));
	
	wp_mail($customerEmail, $customerSubject, $customerContent);
	
	wp_mail($businessEmail, $ownerSubject, $ownerContent);
	
	
	return $newBookID;

}


?>
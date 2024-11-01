<div class="booking-data">
<?php
global $wpdb;
global $eb_path;
include_once($eb_path.'/widgets/trans-vars/view_booking.trans.php');
if( isset($_REQUEST['cmd']) && $_REQUEST['cmd'] == "cancel" && isset( $_REQUEST['b'] ) && $_REQUEST['b'] != '' ){
	$bookID = addslashes($_REQUEST['b']);
	$pinr = $wpdb->get_row('select pin from eb_bookingdata where bookingID = '.$bookID);
	$pincheck = md5($pinr->pin . AUTH_SALT);
	if($pincheck == addslashes($_REQUEST['p'])) $pin = $pinr->pin;
}
if( isset($_POST['transaction_subject']) && $_POST['transaction_subject'] != '' && isset( $_POST['item_number'] ) && $_POST['item_number'] != ''){
	$bookID = addslashes( $_POST['item_number'] );
	$pin = addslashes( $_POST['transaction_subject'] );
}
$lang = '';
if( function_exists(qtrans_getLanguage) ){
	if( isset($_REQUEST['lang']) && $_REQUEST['lang'] != '' )
		$lang = 'lang='.addslashes($_REQUEST['lang']);
	else 
		$lang = 'lang='.$q_config["default_language"];		
}

if( ( isset( $_POST['bookID'] ) && $_POST['bookID'] != '')  || ( isset($bookID) && $bookID != '' ) ){
	global $table_prefix;
	
	if( $bookID == '' ) $bookID = addslashes( $_POST['bookID'] );

	$thereIsSomethingMissing = false;
	//if( !isset($_POST['bID']) || $_POST['bID'] == '' ) $thereIsSomethingMissing = true; 	
	if( $pin == '' ) {
		if( isset( $_POST['pin'] ) && $_POST['pin'] != '' ) $pin = addslashes( $_POST['pin'] );
		else $thereIsSomethingMissing = true;
	}

	//==========CHECK IF PIN IS VALID=========
	$pinCnf = $wpdb->get_var('select bookingID from eb_bookingdata where bookingID = '.$bookID.' AND pin = '.$pin);
	if( $pinCnf == '' ) $thereIsSomethingMissing = true; 	 
	
	//$bID = addslashes( $_POST['bID'] );
	
	if( !$thereIsSomethingMissing ){
		$businessIdR = $wpdb->get_row('select businessID from eb_bookingdata where bookingID = '.$bookID);
		$businessId = $businessIdR->businessID;
		$hasBankMethod = false;
		$hasPaypalMethod = false;
		$eb_BusinessIBAN = get_post_meta($businessId, "eb_IBAN");
		if(!empty($eb_BusinessIBAN)) $eb_BusinessIBAN = $eb_BusinessIBAN[0]; else $eb_BusinessIBAN ='';
		
		$eb_BusinessSWIFT = get_post_meta($businessId, "eb_SWIFT");
		if(!empty($eb_BusinessSWIFT)) $eb_BusinessSWIFT = $eb_BusinessSWIFT[0]; else $eb_BusinessSWIFT ='';
		
		$eb_BusinessBankName = get_post_meta($businessId, "eb_BankName");
		if(!empty($eb_BusinessBankName)) $eb_BusinessBankName = $eb_BusinessBankName[0]; else $eb_BusinessBankName ='';
		
		$eb_BusinessBankAccount = get_post_meta($businessId, "eb_bankAccount");
		if(!empty($eb_BusinessBankAccount)) $eb_BusinessBankAccount = $eb_BusinessBankAccount[0]; else $eb_BusinessBankAccount ='';
		
		if( $eb_BusinessBankAccount != '' && $eb_BusinessBankName != '' && $eb_BusinessIBAN != '' ) $hasBankMethod = true;
					
		$businessCurrency =  get_post_meta($businessId, "eb_currency");
		if(!empty($businessCurrency)) $businessCurrency = $businessCurrency[0]; else $businessCurrency ='';
		
		
		
		//===========FOR QTRANSLATE FIX=============
		$page_id = get_option('eb-view-bookings');
		$permalink = '';
		if( get_option('permalink_structure')  == "") $permalink = get_site_url().'?page_id='.$page_id;
		else $permalink = get_permalink( $page_id );
		?>
		<form action="<?php echo $permalink; ?>" method="post" id="load-booking-trans-fix-frm">
			<input type="hidden" name="eb" value="bookings" />
			<input type="hidden" name="bookID" value="<?php echo $bookID;?>" style="width:80px;margin-right:20px;" />  
			<input type="hidden" name="pin" value="<?php echo $pin;?>" style="width:80px;margin-right:20px;" />	
			<input type="hidden" name="bID" value="<?php echo $businessId; ?>" />			
		</form>		
		<?php
		
		
		//========== update payment method ==========
		if( isset( $_POST['p_m'] ) && $_POST['p_m'] == "bank" && isset($_POST['bID']) && $_POST['bID'] != ''){
			$wpdb->query('update eb_bookingdata set booking_paymentMethod = "bank" where bookingID = '.$bookID );
			echo '<div class="orangeMsg">';
			_e( $eb_lang_BookingPaymentMethSetTo. ' <strong>"'.$eb_lang_BookingPaymentMethSetToBank.'"</strong>.');
			echo '</div>';
		}
		
		//===================CANCELLATION VARS======================
		$eb_cancellationStr = get_post_meta($businessId, "eb_cancellationCharge");		
		if( !empty($eb_cancellationStr) ) $eb_cancellationStr = $eb_cancellationStr[0]; else $eb_cancellationStr = '';
				
		$eb_earlyCancellationStr = get_post_meta($businessId, "eb_earlyCancellationCharge");		
		if( !empty($eb_earlyCancellationStr) ) $eb_earlyCancellationStr = $eb_earlyCancellationStr[0]; else $eb_earlyCancellationStr = '';
	
		$eb_freeCancellationStr = get_post_meta($businessId, "eb_freeCancellationCharge");
		if( !empty($eb_freeCancellationStr) ) $eb_freeCancellationStr = $eb_freeCancellationStr[0]; else $eb_freeCancellationStr = '';
				
				
		//========== CANCEL BOOKING ==========
		if( isset( $_POST['cancellation'] ) && $_POST['cancellation'] == "CANCELED-BY-USER"){
			$wpdb->query('update eb_bookingdata set booking_status = "Canceled", booking_canceled_by_user = "YES", booking_cancelation_cost ="'.addslashes($_POST['cancellation-cost']).'", booking_cancelation_date = "'.date("d-m-Y").'" where bookingID = '.$bookID );
			$wpdb->query('update eb_bookingroomdata set canceled = "YES" where bookingID = '.$bookID );
			
			$eb_BusinessEmail = get_post_meta($businessId, "eb_email");
			if(!empty($eb_BusinessEmail)) $eb_BusinessEmail = $eb_BusinessEmail[0]; else $eb_BusinessEmail ='';
			
			add_filter('wp_mail_content_type',create_function('', 'return "text/html"; '));
	   	add_filter('wp_mail_from', create_function('', 'return "'.get_bloginfo('admin_email').'"; '));
			add_filter('wp_mail_from_name', create_function('', 'return "'.get_bloginfo('name').'"; '));
			
			$cancelSubject = '['.get_the_title( $businessId ).'@'.get_bloginfo('name').']-Booking #'.$bookID.' has been canceled';
			$cancelMsg = 'Booking # '.$bookID.' has been cancelled by user.';
			if( $_POST['cancellation-cost'] != '' ) $cancelMsg .= '<br />According to your cancellation policy, the user will be charged with '.addslashes($_POST['cancellation-cost']).' '.$businessCurrency;
			
			wp_mail($eb_BusinessEmail,$cancelSubject , $cancelMsg);
		}
		
		
					
		//===================EDIT BOOKING VARS=======================
		$eb_editBookingAllowedDatePeriod = get_post_meta($businessId, "eb_editBookingAllowedDatePeriod");
		if( !empty($eb_editBookingAllowedDatePeriod) ) $eb_editBookingAllowedDatePeriod = $eb_editBookingAllowedDatePeriod[0]; else $eb_editBookingAllowedDatePeriod = 0;
			
		$eb_editBookingDates = get_post_meta($businessId, "eb_editBookingDates");
		if( !empty($eb_editBookingDates) ) $eb_editBookingDates = $eb_editBookingDates[0]; else $eb_editBookingDates = "NO";
			
		$eb_editBookingAddRooms = get_post_meta($businessId, "eb_editBookingAddRooms");
		if( !empty($eb_editBookingAddRooms) ) $eb_editBookingAddRooms = $eb_editBookingAddRooms[0]; else $eb_editBookingAddRooms = "NO";
			
		$eb_editBookingRemoveRooms = get_post_meta($businessId, "eb_editBookingRemoveRooms");
		if( !empty($eb_editBookingRemoveRooms) ) $eb_editBookingRemoveRooms = $eb_editBookingRemoveRooms[0]; else $eb_editBookingRemoveRooms = "NO"; 
				
		$booking = $wpdb->get_row('select * from eb_bookingdata where bookingID = '.$bookID);
		
		$rooms = $wpdb->get_results('select * from  eb_bookingroomdata where bookingID ='.$bookID);
		//DATE RANGE VARS
		$arrival = dateX( $booking->dateRange_start );
		$departure = dateX( $booking->dateRange_end );				
		
		//RESORT COUNTRY AND CITY VARS
		$eb_BusinessCountry = '';
		$eb_BusinessCity = '';
		$eb_BusinessCityID = get_post_meta($booking->businessID, "eb_cityID");
		if(!empty($eb_BusinessCityID)) {
			$eb_BusinessCityID = $eb_BusinessCityID[0]; 
			global $countriesTable, $regionsTable, $citiesTable;
			$cityRes = $wpdb->get_row('select CountryID, RegionID, City from eb_cities where CityId = '.$eb_BusinessCityID);
			$eb_BusinessCity = __( $cityRes->City );			
			$countryRes = $wpdb->get_row('select Country from eb_countries where CountryId = '.$cityRes->CountryID);
			$eb_BusinessCountry = __( $countryRes->Country );
		}	
		else $eb_BusinessCityID ='';
		//currencies
		$bookingCurrency = $booking->booking_currency; 
		
		//booking cost and charges
		$bookingCost = $booking->booking_total;
		$bookingCost_bCur = $booking->booking_totalBCUR;
		$paymentMethod = $booking->booking_paymentMethod;
		$paymentCharge = $booking->booking_paymentCharge;
		
		$deposit = $booking->booking_deposit;		
		
		$page_id = get_option('eb-view-bookings');
		$this_permalink = '';
		if( get_option('permalink_structure')  == "") $this_permalink = get_site_url().'?page_id='.$page_id.'&';
		else $this_permalink = get_permalink( $page_id ).'?';
						
		?>
		<div id="booking-number-title" class="general-title">
			<strong><?php _e( $eb_lang_BookingNumber );?>: <span class="bigger-font">#<?php echo $bookID; ?></span></strong>
			<span style="float:right">
				<form id="cancellation-frm" style="display:none;" action="<?php echo $this_permalink.$lang; ?>" method="post" onsubmit=" return confirmCancellation();">
					<input type="hidden" name="cancellation" value="CANCELED-BY-USER" />
					<input type="hidden" name="cancellation-cost" value="0.00" id="cancellation-cost" />
					<input type="hidden" name="eb" value="bookings" />
					<input type="hidden" name="bookID" value="<?php echo $bookID;?>" />  
					<input type="hidden" name="pin" value="<?php echo $pin;?>" />	
					<input type="hidden" name="bID" value="<?php echo $booking->businessID; ?>" />
					<input type="submit" value="Cancel booking" class="eb-search-button" style="font-size:12px;height:21px;margin-right:2px;" />
				</form>
			</span>
		</div>
		<!--START OF BOOKING CONTENT-->	
		<div id="booking-content">
		<div class="sub-container">	
		<div id="booking-status"><?php _e( $eb_lang_BookingStatus );?>: <strong><?php echo $booking->booking_status ;?></strong></div>

		<?php
		if( $booking->booking_canceled_by_user == "YES" ){
		?>
		<div style="padding-left:20px;"><span class="resort-location-data" >This booking has been canceled by you at <?php echo $booking->booking_cancelation_date; ?>. According to hotel's cancellation policy you will be charged with <strong><?php echo $booking->booking_cancelation_cost.' '.$businessCurrency; ?></strong> for this cancellation. This amount will not be returned.</span></div>
		<?php	
		}
		
		$resort_page_id = get_option('eb-view-resort');
		$resortPermalink = '';
		if( get_option('permalink_structure')  == "") $resortPermalink = get_site_url().'?page_id='.$resort_page_id.'&';
		else $resortPermalink = get_permalink( $resort_page_id ).'?';
		
		$hotel_name = get_post($booking->businessID, ARRAY_A);
		$hotel_name = $hotel_name['post_name'];
		?>		
		<div id="booking-resort-details">
			<span id="resort-name"><?php _e( $eb_lang_BookingHotel ); ?>: <a href="<?php echo $resortPermalink.'resort='.$hotel_name; ?>" target="_blank"><strong><?php echo get_the_title( $booking->businessID ); ?></strong></a></span>
			<span class="resort-location-data">
			<?php if( $eb_BusinessCity != ''){ ?>
			<span id="resort-city"><?php _e( $eb_lang_BookingCity ); ?>: <strong><?php echo $eb_BusinessCity; ?></strong></span>
			<?php }?>
			<?php if( $eb_BusinessCountry != '' ){ ?>
			<span id="resort-country"><?php _e( $eb_lang_BookingCountry ); ?>: <strong><?php echo $eb_BusinessCountry; ?></strong></span>
			<?php } ?>
			</span>		
		</div>
		
		<div class="vsep">&nbsp;</div>
		<div><?php _e( $eb_lang_BookingStayFor ); ?>: <strong><?php echo $booking->bookedNights; ?> <?php _e( $eb_lang_BookingNights ); ?></strong></div>
		<div><?php _e( $eb_lang_BookingArrival ); ?>: <strong><?php echo $arrival; ?></strong></div>
		<div><?php _e( $eb_lang_BookingDeparture ); ?>: <strong><?php echo $departure; ?></strong></div>
		<div class="vsep">&nbsp;</div>
		<div style="padding-right:20px;">
			<?php _e( $eb_lang_BookingYouHaveBooked ); ?> <?php echo $booking->numberOfRooms; ?> <?php _e( $eb_lang_BookingRooms ); ?>
			
				<?php
				$roomCounter = 1;
				foreach( $rooms as $room ){
					?>
					<div class="room-pre-container">
					<?php
					$roomData = get_post($room->roomID);
					$roomsAdults = get_post_meta($room->roomID, "eb_peopleNum");
					$roomsAdults = $roomsAdults[0];
					$roomsChildren = get_post_meta($room->roomID, "eb_childrenAllowed");
					$roomsChildren = $roomsChildren[0];

					?>
					<div class="general-title"><?php _e( $eb_lang_BookingRoom ); ?> #<?php echo $roomCounter; ?> <?php _e( $eb_lang_BookingRoomType ); ?>: <strong><?php _e( $roomData->post_title ); ?></strong> </div>
					<div class="room-container">
						<div class="room-capacity-area">
							<?php _e( $eb_lang_BookingAdults ); ?> <strong><?php echo $roomsAdults; ?> </strong>
							<?php _e( $eb_lang_BookingChildren ); ?> <strong><?php echo $roomsChildren; ?> </strong>
							<?php if( $room->noOfBabies > 0 ){ ?>
							<?php _e( $eb_lang_BookingBabies ); ?> <strong><?php echo $room->noOfBabies; ?> </strong>
							<?php } 
							if( $room->guestFullName != '' ){?>
							| <?php _e( $eb_lang_BookingGuestName); ?> <strong><?php _e( $room->guestFullName ); ?></strong>
							<?php } else _e(' | '. $eb_lang_BookingNoGuestName);?>
						</div>
						<div class="room-price-area">
							<?php _e( $eb_lang_BookingPriceFor); ?> <?php echo $booking->bookedNights;?> <?php _e( $eb_lang_BookingNights ); ?> <strong><?php echo $room->roomCost_siteCur . ' '.$booking->booking_currency; ?></strong> 
							<?php 
							if( $booking->booking_currency != $businessCurrency )	
								_e( '('.$room->roomCost . ' '.$businessCurrency.' - '.$eb_lang_BookingHotelCur.' )' ); ?> 
						</div>
					</div>
					</div>
					<div class="vsep">&nbsp;</div>
					<?php
					$roomCounter++;
				}
				?>
			
			</div><!--end of sub-container-->
			

		</div>
		<div class="vsep">&nbsp;</div>
		<!--BOOKING CHARGE REPORT-->
		<div class="general-title"><strong><?php _e( $eb_lang_BookingPaymentInformation ); ?></strong></div>
		<div class="sub-container">
			<div><?php _e( $eb_lang_BookingPaymentMethod ); ?> : <strong><?php echo $paymentMethod; ?></strong></div>			
			<div><?php _e( $eb_lang_BookingCost ); ?> : <strong><?php echo $bookingCost.' '.$booking->booking_currency ;?></strong>
			<?php if( $booking->booking_currency != $businessCurrency ){?> 
			<span id="business-currency-price">( <?php echo $bookingCost_bCur.' '.$businessCurrency; ?><?php _e(' - '.$eb_lang_BookingHotelCur.' ');?>)</span>
			<?php } ?>
			</div>
			<?php 
			$bookingTotalPaymentInc = number_format($paymentCharge, 2) + number_format($bookingCost, 2); 
			if( $paymentCharge != '' && $paymentCharge != 0 ){ ?>
			<div><?php _e( $eb_lang_BookingPaymentCharge ); ?> : <strong><?php echo $paymentCharge.' '.$booking->booking_currency;?></strong></div>
			<div><?php _e( $eb_lang_BookingTotal ); ?> : <strong><?php echo ($paymentCharge + $bookingCost ).' '.$booking->booking_currency;?></strong></div>
			<?php } ?>
			
			<div><?php _e( $eb_lang_BookingAmountPayed ); ?> : <strong><?php if( $deposit == '' ) $deposit = 0;echo $deposit.' '.$booking->booking_currency; ?></strong></div>
			
			<?php
			$balance = number_format($bookingTotalPaymentInc, 2) - number_format($deposit, 2);
			?>
			<div style="margin-top:10px;"><?php _e( $eb_lang_BookingBalance ); ?> : <strong><?php echo $balance .' '.$booking->booking_currency; ?></strong></div>
		</div>
		
		<?php
		if( $paymentMethod == 'bank' && $balance > 0 ){
			if( $hasBankMethod ){
		?>
		<div class="vsep">&nbsp;</div>
		<div class="vsep">&nbsp;</div>		
		<div id="guest-data-area">
			<div id="booking-number-title" class="general-title"><strong><?php _e( $eb_lang_BookingBankAccInformation );?></strong> <em>*<?php _e( $eb_lang_BookingUseThisInfo ); ?></em></div>
				<div class="sub-container">
					<div><?php _e( $eb_lang_BookingBankName );?>: <strong><?php echo $eb_BusinessBankName; ?></strong></div>
					<div><?php _e( $eb_lang_BookingBankAcc );?>: <strong><?php echo $eb_BusinessBankAccount; ?></strong></div>
					<div><?php _e( $eb_lang_BookingIBAN );?>: <strong><?php echo $eb_BusinessIBAN; ?></strong></div>
					<?php
					if( $eb_BusinessSWIFT != '' ) {
					?>
					<div><?php _e( $eb_lang_BookingSWIFT );?>: <strong><?php echo $eb_BusinessSWIFT; ?></strong></div>
					<?php }?>
					<div class="bank-clarification-code-wrapper">
						<?php _e( $eb_lang_BookingUseClarifCodeStr );?>
						<div class="bank-clarification-code-container"><?php echo get_bloginfo('name').'-'.$bookID; ?></div>
						<em><?php _e( $eb_lang_BookingClarifSubNote );?></em> 
					</div>
					<div class="vsep">&nbsp;</div>
				</div>
		</div>
		<?php	
			}//end of if( $hasBankMethod )
		}
		?>
		<div class="vsep">&nbsp;</div>
		<div class="vsep">&nbsp;</div>
		<!-- =====GUEST CHARGING DATA===== -->
		<div id="guest-data-area">
			<div id="booking-number-title" class="general-title"><strong><?php _e( $eb_lang_BookingPersonalData ); ?></strong></div>
				<div class="sub-container">
					<div id="guest-lname"><?php _e( $eb_lang_BookingLastName ); ?>: <strong><?php echo $booking->customer_lname; ?></strong></div>
					<div id="guest-lname"><?php _e( $eb_lang_BookingFirstName ); ?>: <strong><?php echo $booking->customer_fname; ?></strong></div>
					<div id="guest-email"><?php _e( $eb_lang_BookingEmail ); ?>: <strong><?php echo $booking->customer_email; ?></strong></div>
					<?php if( $booking->customer_tel != '' ){?>
					<div id="guest-tel"><?php _e( $eb_lang_BookingTel ); ?>: <strong><?php echo $booking->customer_tel; ?></strong></div>
					<?php } ?>
					<?php if( $booking->customer_country != '' ){?>
					<div id="guest-tel"><?php _e( $eb_lang_BookingUsersCountry ); ?>: <strong><?php echo $booking->customer_country; ?></strong></div>
					<?php } ?>
				</div>
		</div>
		
		<?php
		
		if( $balance > 0 ){
		?>
		<div class="vsep">&nbsp;</div>
		<div class="vsep">&nbsp;</div>
		<div id="balance-data-area">
			<div id="booking-balance-payout" class="general-title"><strong><?php _e( $eb_lang_BookingPayRemainingBal ); ?></strong></div>
			<div class="sub-container" style="height:130px;text-align:center;"align="center" >
			<div class="redWarningMsg"><?php _e( $eb_lang_BookingCancellationWarningStr ); ?>.</div>
			
			<!--<table style="border:none;width:100%;">
				<tr>
					<td width="50%" align="right" style="border:none;width:50%;text-align:right;">-->
						<?php
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
						
						$eb_BusinessPaypalAccount = get_post_meta($booking->businessID, "eb_paypalAccount");
						if(!empty($eb_BusinessPaypalAccount)){
							$eb_BusinessPaypalAccount = $eb_BusinessPaypalAccount[0];
							$hasPaypalMethod = true;
						} 
						else $eb_BusinessPaypalAccount ='';
						
						if( $eb_BusinessPaypalAccount != '' ){
						?>
						<div style="display: inline-block;">
						<form action="https://www.paypal.com/cgi-bin/webscr" method="post" id="paypal-frm" style="text-align:right;" >
							<input type="hidden" name="business" value="<?php echo $eb_BusinessPaypalAccount;?>"/>
							<input name="cmd" type="hidden" value="_xclick" />
   						<input name="no_note" type="hidden" value="1" />
   						<input name="lc" type="hidden" value="UK" />
   						<input name="currency_code" type="hidden" value="<?php echo $booking->booking_currency; ?>" />
   						<input name="bn" type="hidden" value="PP-BuyNowBF:btn_buynow_LG.gif:NonHostedGuest" />
   						<input name="first_name" type="hidden" value="<?php echo urlencode($booking->customer_fname); ?>" />
   						<input name="last_name" type="hidden" value="<?php echo urlencode($booking->customer_lname); ?>" />
   						<input name="payer_email" type="hidden" value="<?php echo addslashes( $booking->customer_email ); ?>" />
   						<input name="item_number" type="hidden" value="<?php echo urlencode($bookID); ?>" />
   						<input name="amount" type="hidden" value="<?php echo urlencode($balance); ?>" />
   						<!--<input name="amount" type="hidden" value="0.01" />-->
   						<input name="item_name" type="hidden" value="<?php echo get_bloginfo('name').' '. urlencode($bookID); ?>" />
   						<input name="return" type="hidden" value="<?php echo stripslashes($return_url); ?>" />
   						<input name="cancel_return" type="hidden" value="<?php echo stripslashes($cancel_url); ?>" />
   						<input name="notify_url" type="hidden" value="<?php echo $siteURL; ?>/wp-content/plugins/wp-easybooking/widgets/paypal_functions.php" />
   						<!--return with post-->
   						<input type="hidden" name="custom" value="<?php echo $pin; ?>" />
   						<input type="hidden" name="rm" value="2" /> 
   						<input type="hidden" name="cbt" value="Return to <?php echo get_bloginfo('name'); ?>">
			   			
			   			
			   			<input type="submit" value="<?php _e( $eb_lang_BookingPayWithPaypal ); ?>" class="eb-search-button" />
						</form>
						</div>
					<?php } //end of if($hasPaypalMethod)?>
					<!--</td>-->
					<?php if( $paymentMethod != 'bank' ){ ?>
					<!--<td width="50%" align="left" style="border:none;">-->
					
										 
					 <?php
					  
						?>
						<div style="display: inline-block;">
						<form action="<?php echo $this_permalink.$lang; ?>" method="post" id="bankPaymentMethodFrm">
							<input type="hidden" name="eb" value="bookings" />
							<input type="hidden" name="bookID" value="<?php echo $bookID;?>" />  
							<input type="hidden" name="pin" value="<?php echo $pin;?>" />	
							<input type="hidden" name="bID" value="<?php echo $booking->businessID; ?>" />
							<input type="hidden" name="p_m" value="bank" />
							<input type="submit" value="<?php _e( $eb_lang_BookingPayWithBankWire ); ?>" class="eb-search-button" />			
					 </form>
					 </div>
					<!--</td>-->
					<?php }
					else{ ?>
					<!--<td style="border:none;"></td>-->
					<?php if( $hasBankMethod ){ ?>
					<!--<tr>
					<td style="border:none;" colspan="2">-->
					<div style="display: inline-block;">
					<span>
						<?php _e( $eb_lang_BookingOrPayWithBank ); ?>
					</span>
					</div>
					<!--</td>
					</tr>-->
					<?php } //end of if( $hasBankMethod )?>
					<?php } ?>
					
				<!--</tr>
			</table>-->
			<div class="vsep">&nbsp;</div>
			</div>
			
		</div>
		<?php
		}
		
		$arrivalDay = explode(' 00:00:00',$booking->dateRange_start);
		$arrivalDay = $arrivalDay[0];
		$today = date("Y-m-d");
		$dateDiff = ceil( strtotime($arrivalDay) - strtotime($today) )/86400;

		$eb_earlyCancellation = explode('::', $eb_earlyCancellationStr);
		$eb_cancellation = explode('::', $eb_cancellationStr);
		
		$cancellationCost = 'NOT_SET';
		$cancellationMode = $businessCurrency;
		$userCanCancel = false;
		
		if( $eb_cancellationStr != '' && $dateDiff >= $eb_cancellation[2]){
			$cancellationCost = $eb_cancellation[1];
			if( $eb_cancellation[0] == "PERSENTAGE" ) $cancellationMode = '%';
			else $cancellationMode = $businessCurrency;
			$userCanCancel = true;
		}
		if( $eb_earlyCancellationStr != '' && $dateDiff >= $eb_earlyCancellation[2]){
			$cancellationCost = $eb_earlyCancellation[1];
			if( $eb_earlyCancellation[0] == "PERSENTAGE" ) $cancellationMode = '%';
			else $cancellationMode = $businessCurrency;
			$userCanCancel = true;
		}
		if( $eb_freeCancellationStr != '' && $dateDiff >= $eb_freeCancellationStr ){
			$cancellationCost = 0;
			$cancellationMode = $businessCurrency;
			$userCanCancel = true;
		}
		
		if( $cancellationMode == "%" ){
			$cancellationCost = number_format( ( $bookingCost_bCur * $cancellationCost ) / 100 ,2 );
		}
		

		if( $userCanCancel && $balance <= 0 && ( $booking->booking_status == "Pending" || $booking->booking_status == "Confirmed" ) ){
		?>
		<script type="text/javascript" >
			jQuery("#cancellation-frm").show();
			jQuery('#cancellation-cost').val("<?php echo $cancellationCost; ?>");
			
			function confirmCancellation(){
				var confirmationText = "<?php _e( $eb_lang_BookingAreYouSureToCanc ); ?>? \n <?php _e( $eb_lang_BookingAccordingTo ); ?> <?php echo get_the_title( $booking->businessID ); ?> <?php _e( $eb_lang_BookingCancellationCostIs ); ?> <?php echo $cancellationCost; ?> <?php echo $businessCurrency; ?>";
				if( confirm( confirmationText ) )
					return true;
				else return false;
			}
		</script>
		<?php
		}//end of if $userCanCancel = true	
		?>
		
		</div><!--START OF BOOKING CONTENT-->
		<?php		
	}//END IF NOTHING IS MISSING
	else{
		if( isset( $_REQUET['eb'] ) && $_REQUET['eb'] == "pp_IPN" ){
			echo '<div>';
			_e("<h2>".$eb_lang_BookingBookingHasBeenMade."</h2>");
			_e( $eb_lang_BookingCheckEmailForPasPin."." );
			echo '</div>';
		}
		else{
			echo '<div class="input-error" style="display:block;width:100%;text-align:center;" >';
			_e( $eb_lang_BookingNotAllowedToView.'.' );
			echo '</div>';
		}
		echo '<div id="go-to-booking" style="margin-top:10px;" class="">';
			
			$page_id = get_option('eb-view-bookings');
			$permalink = '';
			if( get_option('permalink_structure')  == "") $permalink = get_site_url().'?page_id='.$page_id.'&';
			else $permalink = get_permalink( $page_id ).'?'; 
			
			_e( $eb_lang_BookingTryAgain.'.' );
			?>
			<form action="<?php echo $permalink.$lang; ?>" method="post">
				<input type="hidden" name="eb" value="bookings" />
				<?php _e( $eb_lang_BookingNumber ); ?>: <input type="text" name="bookID" value="<?php echo addslashes( $_POST['bookID']);?>" style="width:80px;margin-right:20px;" />  
				<?php _e( $eb_lang_BookingPIN ); ?>: <input type="password" name="pin" value="<?php echo addslashes( $_POST['pin']);?>" style="width:80px;margin-right:20px;" />	
				<input type="hidden" name="bID" value="<?php echo $booking->businessID; ?>" />
				<input type="submit" value="<?php _e( $eb_lang_BookingViewYourBooking ); ?>" class="eb-search-button" />
			</form>
			</div>
		<?php 
		
	}
	
	
	
}
else {
	echo '<div class="input-error" style="display:block;width:400px;font-size:14px;" >';
	_e( $eb_lang_BookingNoBookingSelected.'!' );
	echo '</div>';
}

						

function dateX( $date ){
	$d1 = explode('00:00:00',$date);
	$d2 = explode('-', $d1[0]);	
	$dTime = mktime(0,0,0, (int)$d2[1], (int)$d2[2], (int)$d2[0]);
	$dateX = date('d M Y', $dTime);
	return $dateX;
}
?>
</div><!--end of loader-->
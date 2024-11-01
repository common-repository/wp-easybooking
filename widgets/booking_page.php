<?php
$bookingData = addslashes( $_POST['booking-data'] );
$bookingData = explode( '|', $bookingData );
$bID = addslashes( $_POST['bID'] );

$from = addslashes( $_POST['from'] );
$to = addslashes( $_POST['to'] );
$ccur = addslashes( $_POST['ccur'] );

$businessLogo = get_post_meta( $bID, "eb_defaultLogo" );

$roomsLateCheckoutStr = '';

if( !empty( $bookingData ) ){
	global $wpdb;
	global $table_prefix;
	$bcur = $wpdb->get_row('select meta_value from '.$table_prefix.'postmeta where post_id = '.$bID.' and meta_key = "eb_currency"');
	$bcur = $bcur->meta_value;
	
	if( $ccur == htlcur ){
		$ccur = $bcur;
	}
	
	$lang = 'en';
	//if( isset( $_REQUEST['lang'] ) && $_REQUEST['lang'] != '' ) $lang = addslashes( $_REQUEST['lang'] );
	if( function_exists(qtrans_getLanguage))
			$lang = qtrans_getLanguage();
	
	//$resortPageID = get_option('eb-view-resort');
	
	$page_id = get_option('eb-booking-success');
		$permalink = '';
		if( get_option('permalink_structure')  == "") $permalink = get_site_url().'?page_id='.$page_id.'&';
		else $permalink = get_permalink( $page_id ).'?';
	?>
	<form action="<?php echo $permalink; ?>lang=<?php echo $lang; ?>" method="post" onsubmit="return checkBookingData();">
	<!--<form action="<?php echo $permalink; ?>lang=<?php echo $lang; ?>" method="post" onsubmit="alert('Sorry friend, bookings not allowed in Demo version!!');return false;">-->
	<?php 	
	$roomsSelected = 0;	
	
	include_once(ABSPATH.'wp-content/plugins/wp-easybooking/widgets/trans-vars/booking.trans.php');
	
	$interval = date_diff(date_create( $from ), date_create( $to ) );
	$daysNum = (int)$interval->format('%a');
	
	$stars = get_post_meta($bID, "eb_stars");
	$address = get_post_meta($bID, "eb_address");
	if(!empty($address)) $address = $address[0]; else $address ='';
	
	$addressNumber =  get_post_meta($bID, "eb_addressNum");
		if(!empty($addressNumber)) $addressNumber = $addressNumber[0]; else $addressNumber ='';
	
	$eb_BusinessCityID = get_post_meta($bID, "eb_cityID");
	$city = ''; $country = '';
	if(!empty($eb_BusinessCityID)) {
		$eb_BusinessCityID = $eb_BusinessCityID[0]; 
		global $countriesTable, $regionsTable, $citiesTable;
		$cityRes = $wpdb->get_row('select CountryID, RegionID, City from '.$citiesTable.' where CityId = '.$eb_BusinessCityID);
		$eb_BusinessCountryID = $cityRes->CountryID;
		$country = $wpdb->get_row('select Country from '.$countriesTable.' where CountryID = '.$cityRes->CountryID);
		$country = $country->Country;
		$city = $cityRes->City;
	}
	
	
	$fname = '';$lname = '';$email = '';
	if ( is_user_logged_in() ) {
	   $uID = get_current_user_id();
	   $user_info = get_userdata($uID);
	   $fname = $user_info->first_name;
	   $lname = $user_info->last_name;
	   $email = $user_info->user_email;
	}
	
	$businessData = $wpdb->get_row('select post_title from '.$table_prefix.'posts where ID = '.$bID. ' AND post_status = "publish"');
	
	$totalRoomCost = 0;
	$roomsSelections = '';	
	for( $rc = 0; $rc < sizeof( $bookingData ) -1 ; $rc++ ){
		$room = explode( "::", $bookingData[$rc] );
		$roomNumber = (int)$room[1];
		$room = $room[0];

		$singleRoomPrice = roomPrice($bID, $room, $from, $to, $ccur);
		//$singleRoomPrice = convert( $singleRoomPrice, $bcur, $ccur );
		$roomPrice = $singleRoomPrice * $roomNumber;
		//$roomPrice = convert( $roomPrice, $bcur, $ccur ); 
		$roomsSelected += (int)$roomNumber; 
		$totalRoomCost += $roomPrice;
		
		
		$roomData = $wpdb->get_row('select post_title, post_content from '.$table_prefix.'posts where ID = '.$room. ' AND post_parent = '.$bID);
		for( $r = 1; $r <= $roomNumber; $r++){
			$adultsInRoom = get_post_meta($room, "eb_peopleNum");
			$adultsInRoom = $adultsInRoom[0];
			$childrenInRoom = get_post_meta($room, "eb_childrenAllowed");
			$childrenInRoom = $childrenInRoom[0];
			$babiesInRoom = get_post_meta($room, "eb_babiesAllowed");			
			$babiesInRoom = $babiesInRoom[0];			 
				
			$roomLateCheckoutStr = get_post_meta($room, "eb_lateCheckoutPrice");
			if( !empty($roomLateCheckoutStr) && $roomLateCheckoutStr[0] > 0 ) $roomsLateCheckoutStr .= '<li>'.__( $eb_lang_LateCheckOutTheRoom ).' <strong>'.__($roomData->post_title).'</strong> '.__( $eb_lang_LateCheckOutOffersForPrice ).' <strong>'.$roomLateCheckoutStr[0].' '.$bcur.'</strong>';

			$roomsSelections .= '<div class="room-container">';
				$roomsSelections .= '<div class="room-title" title="'. strip_tags( __( $roomData->post_content ) ).'">';
					$roomsSelections .= __( $eb_lang_Room );
					$roomsSelections .= ': <strong>'.$roomData->post_title.'</strong>';
				$roomsSelections .= '</div>';
				$roomsSelections .= '<div class="">';
					$roomsSelections .= '<input type="text" placeholder="'.__( $eb_lang_GuestFullName ).'" name="guest_'.$room.'_'.$r.'" />';
					$roomsSelections .= '<div class = "room-capacity-area">';
						$roomsSelections .= $adultsInRoom.' '. __( $eb_lang_RoomsAdults );
						//if( $childrenInRoom > 0)
						//$roomsSelections .= ' <span style="padding-left:10px;"></span><select name="children_'.$room.'_'.$r.'">'.selectOptions( 0, $childrenInRoom).'</select> '.__( $eb_lang_RoomsChildren );
						$roomsSelections .= ' <span style="padding-left:10px;"></span>'.$childrenInRoom.' '. __( $eb_lang_RoomsChildren );
						if( $babiesInRoom > 0)
						$roomsSelections .= ' <span style="padding-left:10px;"></span><select name="babies_'.$room.'_'.$r.'">'.selectOptions( 0, $babiesInRoom).'</select> '.__( $eb_lang_RoomsBabies );
					$roomsSelections .= '</div>';
					$roomsSelections .= '<div class="room-price-area">'.__( $eb_lang_RoomsPrice ).' '.mb_strtolower( __( $eb_lang_For.' '.$daysNum. ' '.$eb_lang_Nights ) ).' <strong>'.$singleRoomPrice.' '.$ccur.'</strong></div>';
				$roomsSelections .= '</div>';
			$roomsSelections .= '</div>';
			$singleRoomPrice_siteCur = convert( $singleRoomPrice, $bcur, $ccur );
			$hiddenRoomHolder .= $room.':'.$r.':'.$singleRoomPrice.':'.$singleRoomPrice_siteCur.'|';
		}
	}
	$roomsTotalPriceBcur = $totalRoomCost;
	$totalRoomCost = convert( $totalRoomCost, $bcur, $ccur );
	$roomsSelected = $roomsSelected.' '.$eb_lang_Rooms.' <strong>'.$totalRoomCost.' '.$ccur.'</strong>';
	
	
	$eb_BusinessIBAN = get_post_meta($bID, "eb_IBAN");
	if(!empty($eb_BusinessIBAN)) $eb_BusinessIBAN = $eb_BusinessIBAN[0]; else $eb_BusinessIBAN ='';
		
	$eb_BusinessBankName = get_post_meta($bID, "eb_BankName");
	if(!empty($eb_BusinessBankName)) $eb_BusinessBankName = $eb_BusinessBankName[0]; else $eb_BusinessBankName ='';
		
	$eb_BusinessBankAccount = get_post_meta($bID, "eb_bankAccount");
	if(!empty($eb_BusinessBankAccount)) $eb_BusinessBankAccount = $eb_BusinessBankAccount[0]; else $eb_BusinessBankAccount ='';
		
	$eb_BusinessPaypalAccount = get_post_meta($bID, "eb_paypalAccount");
	if(!empty($eb_BusinessPaypalAccount)) $eb_BusinessPaypalAccount = $eb_BusinessPaypalAccount[0]; else $eb_BusinessPaypalAccount ='';
	
	//====================CHECK IN VARS=========================
	$checkIn = get_post_meta($bID, "eb_checkInTime");
	if(!empty($checkIn)) $checkIn = $checkIn[0]; else $checkIn ='';
	
	$checkOut = get_post_meta($bID, "eb_checkOutTime");
	if(!empty($checkOut)) $checkOut = $checkOut[0]; else $checkOut ='';
	
	//===================CANCELLATION VARS======================
		$eb_cancellationStr = get_post_meta($bID, "eb_cancellationCharge");		
		if( !empty($eb_cancellationStr) ) $eb_cancellationStr = $eb_cancellationStr[0]; else $eb_cancellationStr = '';
				
		$eb_earlyCancellationStr = get_post_meta($bID, "eb_earlyCancellationCharge");		
		if( !empty($eb_earlyCancellationStr) ) $eb_earlyCancellationStr = $eb_earlyCancellationStr[0]; else $eb_earlyCancellationStr = '';
	
		$eb_freeCancellationStr = get_post_meta($bID, "eb_freeCancellationCharge");
		if( !empty($eb_freeCancellationStr) ) $eb_freeCancellationStr = $eb_freeCancellationStr[0]; else $eb_freeCancellationStr = '';
	
		//===================EDIT BOOKING VARS=======================
		$eb_editBookingAllowedDatePeriod = get_post_meta($bID, "eb_editBookingAllowedDatePeriod");
		if( !empty($eb_editBookingAllowedDatePeriod) ) $eb_editBookingAllowedDatePeriod = $eb_editBookingAllowedDatePeriod[0]; else $eb_editBookingAllowedDatePeriod = 0;
			
		$eb_editBookingDates = get_post_meta($bID, "eb_editBookingDates");
		if( !empty($eb_editBookingDates) ) $eb_editBookingDates = $eb_editBookingDates[0]; else $eb_editBookingDates = "NO";
			
		$eb_editBookingAddRooms = get_post_meta($bID, "eb_editBookingAddRooms");
		if( !empty($eb_editBookingAddRooms) ) $eb_editBookingAddRooms = $eb_editBookingAddRooms[0]; else $eb_editBookingAddRooms = "NO";
			
		$eb_editBookingRemoveRooms = get_post_meta($bID, "eb_editBookingRemoveRooms");
		if( !empty($eb_editBookingRemoveRooms) ) $eb_editBookingRemoveRooms = $eb_editBookingRemoveRooms[0]; else $eb_editBookingRemoveRooms = "NO";
	
	$hasBank = false;
	$hasPaypal = false;
	if( $eb_BusinessIBAN != '' && $eb_BusinessBankName != '' && $eb_BusinessBankAccount != '' ) $hasBank = true;
	if( $eb_BusinessPaypalAccount != '') $hasPaypal = true;
	?>
	<div class="booking-page-general-header">
		<div class="general-title"><?php _e($eb_lang_BookingDetails);?></div>
		<div>
			<div class="resort-image-title" style="background-image:url(<?php echo WP_CONTENT_URL; ?>/plugins/wp-easybooking/images/businessImg/<?php echo $businessLogo[0];?>)">
				<div class="resort-title"></div>
			</div>
			<div style="float:left;padding-left:10px;color: #48f;">
				<strong><?php echo $businessData->post_title.' <img src = "'.WP_CONTENT_URL.'/plugins/wp-easybooking/images/stars/'.$stars[0].'star-small.png" height="12px" title="'.$stars[0].' stars" />'; ?></strong>
				<div style="font-size:12px;">
					<?php _e( $address. ' '. $addressNumber );?><br />
					<?php _e( $city. ', '. $country );?><br />
					<?php _e( $eb_lang_CheckIn.': '.$from );?><br />
					<?php _e( $eb_lang_CheckOut.': '.$to );?><br />
					<?php
											
					 		_e( $eb_lang_For.' '.$daysNum. ' '.$eb_lang_Nights );
					 		
					 ?>
				</div>
			</div>
			<div align="right" style="text-align:right;float:right;color: #48f;font-size:12px;padding-left:10px;height:100px;">
				<table style="border:none;height:130px">
					<tr valign="bottom">
						<td style="border:none;height:100%;vertical-align:bottom;text-align:right;" valign="bottom" align="right">
							<?php _e( $roomsSelected );?> 
							<input type="hidden" name="totalRoomCost" value="<?php echo $totalRoomCost; ?>" />
							<input type="hidden" name="totalRoomCostBcur" value="<?php echo $roomsTotalPriceBcur; ?>" />
							
							<?php 
							$paypalFee = paypalFee( $totalRoomCost, $ccur );
							$totalWithPaypalFee = paypalTotal($totalRoomCost, $paypalFee);
							?>
							<br />
							<label style="display:none;" id="paypal-fee"> <?php _e ( $eb_lang_PaypalFee.' <strong>'. $paypalFee . $ccur.'</strong>' ); ?></label>
							<input type="hidden" name="paypal-fee" value="<?php echo $paypalFee; ?>" />
							<br />
							<label style="display:none;" id="total-with-paypal"><?php _e( $eb_lang_TotalWithPaypal .' <strong>'. $totalWithPaypalFee . $ccur.'</strong>' );?></label>
							<input type="hidden" name="total-with-paypal" value="<?php echo $totalWithPaypalFee; ?>" />
						</td>
					</tr>
				</table></div>
		</div>
	</div>
	<div class="vsep">&nbsp;</div>
	<div class="booking-page-general-header">
		<div class="general-title"><?php _e( $eb_lang_YourDetails );?></div>
		<div class="user-details" style="padding:15px;">
			<table style="border:none;" >
				<tr>
					<td style="border:none;width:20%;vertical-align:top;" valign="top">
						<?php _e( $eb_lang_YourLastName );?><span style="color:#f19c06;font-size:12px">*</span>
					</td>
					<td style="border:none;vertical-align:top;" align="left">	
						<input type="text" name="lname" id="lname" value="<?php echo $lname ;?>" />
						<div id="lname_error" class="input-error"></div>
					</td>
			</tr>
			<tr>
				<td style="border:none;vertical-align:top;">
					<?php _e( $eb_lang_YourFirstName );?><span style="color:#f19c06;font-size:12px">*</span>
				</td>
				<td style="border:none;vertical-align:top;" align="left">	
					 <input type="text" name="fname" id="fname" value="<?php echo $fname ;?>" />
					 <div id="fname_error" class="input-error"></div>
				</td>
			</tr>
			
			<tr>
				<td style="border:none;vertical-align:top;">
					<?php _e( $eb_lang_YourEmail );?><span style="color:#f19c06;font-size:12px">*</span>
				</td>
				<td style="border:none;vertical-align:top;" align="left">	
					 <input type="email" name="email" id="email" value="<?php echo $email ;?>" />
					 <div id="email_error" class="input-error"></div>
				</td>
			</tr>
			<?php if( $email == '' ){?>
			<tr>
				<td style="border:none;">
					<?php _e( $eb_lang_YourConfirmEmail );?><span style="color:#f19c06;font-size:12px">*</span>
				</td>
				<td style="border:none;" align="left">
					<input type="email" name="conf-email" id="conf-email" value="" />
					<div id="conf_email_error" class="input-error"></div>
				</td>
			</tr>
			<?php }?>
			<tr>
				<td style="border:none;vertical-align:top;">
					<?php _e( $eb_lang_YourTel );?>
				</td>
				<td style="border:none;vertical-align:top;" align="left">	
					 <input type="text" name="tel" id="tel" value="" />
				</td>
			</tr>
			<tr>
				<td style="border:none;vertical-align:top;">
					<?php _e( $eb_lang_YourCountry );?>
				</td>
				<td style="border:none;vertical-align:top;" align="left">	
					<select name="country">
						<?php
						$countries = $wpdb->get_results('select CountryId, Country from eb_countries');
						_e( '<option style="font-style:italic" value="">'.$eb_lang_YourCountry.'</option>' );
						foreach($countries as $country){
						_e('<option value="'.$country->Country.'">'.$country->Country.'</option>');
						}
						?>
					</select>
				</td>
			</tr>
			</table>
			<div class="explain-required">*<?php _e( $eb_lang_FieldsAreRequired );?></div>
			<?php if( __( $eb_lang_OnlyLatinCharacters ) != '' ){?>
			<div class="explain-required"><em><?php _e( $eb_lang_OnlyLatinCharacters );?></em></div>
			<?php } ?>
		</div>
	</div>
	<div class="vsep">&nbsp;</div>
	<div class="booking-page-general-header">
		<div class="general-title"><?php _e( $eb_lang_RoomDetails .' <em>'.$eb_lang_RoomDetailsOptional.'</em>' );?></div>
		<?php _e( $roomsSelections ); ?>
	</div>
	<?php if( $hasBank || $hasPaypal ){?>
	<div class="vsep">&nbsp;</div>
	<div class="booking-page-general-header">
		<div class="general-title"><?php _e( $eb_lang_Payment_details ); ?></div>
		<?php if( $hasPaypal ){ ?>
		<div style="float:left;padding:5px;"><input type="radio" name="payment" value="paypal" onchange="dispPaypalFee('show');" id="paypalRadio" /> <label for="paypalRadio"><a title="<?php _e( $eb_lang_PayWithPaypalCreditCard );?>"><?php _e( $eb_lang_PayWithPaypal ); ?></a></label></div>
		<?php } ?>
		<?php if( $hasBank ){?>
		<div style="float:left;padding:5px;"><input type="radio" name="payment" value="bank" onchange="dispPaypalFee('hide');" id="bankRadio" /> <label for="bankRadio"><a><?php _e( $eb_lang_PayThroughBank );?></a></label></div>
		<?php } ?>
		<?php if( $hasPaypal ){ ?>
		<div class="explain-required" style="clear:both;"><em style="color:#ddd;"><?php _e( $eb_lang_PayWithPaypalCreditCard ); ?></em></div>
		<?php } ?>
		<div id="select_payment_error" class="input-error" style="clear:both;"></div>
		
	</div>
	<?php }
	else{?>
	<input type="hidden" name="payment" value="paylater" />		
	<?php }
	?>
	<div class="room-price-area" style="clear:both;">
		<input type="checkbox" id="terms-checkbox" onchange="activateCompleteBooking();" />
		<span><label for="terms-checkbox"><?php _e( $eb_lang_ReadThe ); ?> <a onclick="showTerms();"><?php _e( $eb_lang_TermsAndConditions ); ?></a> <?php _e( $eb_lang_AndIAgree ); ?>.</label></span>
	</div>
	<div id="terms-area" class="terms-area">
		<?php
		$termsStr = '';
		$termsStr .= '<div class="general-title">'.__( $eb_lang_PoliciesOf ).' <strong>'.$businessData->post_title.'</strong></div>';
		
		$termsStr .= '<strong>'.__($eb_lang_CheckInOutPolicies ).'</strong>';
		$termsStr .= '<ul>';
		if( $checkIn != ''){
			$checkIn = explode('[-]', $checkIn);
			$termsStr .= '<li>'.__( $eb_lang_CheckInFrom ).' '.$checkIn[0].' '.__( $eb_lang_To ).' '.$checkIn[1].'</li>';
		}
		
		if( $checkOut != ''){
			$checkOut = explode('[-]', $checkOut);
			$termsStr .= '<li>'.__( $eb_lang_CheckOutFrom ).' '.$checkOut[0].' '.__( $eb_lang_To ).' '.$checkOut[1].'</li>';
		}
		
		$termsStr .= '</ul>';
		 
		$termsStr .= '<strong>'.__( $eb_lang_Cancellation ).'</strong>';
		$termsStr .= '<ul>';
			
		$mode = '';
		if( $eb_cancellationStr != '' ){
			$cancellationStr = explode('::', $eb_cancellationStr);
			if( $cancellationStr[0] == "PERSENTAGE" ) $mode = '%';
			else $mode = $bcur;
			$termsStr .= '<li><strong>'.$cancellationStr[1].' '.$mode.'</strong> '.__( $eb_lang_ChargeForCancellations ).' <strong>'.$cancellationStr[2].'</strong> <strong>'.__( $eb_lang_BeforeDays ).'</strong> '.__( $eb_lang_BeforeCheckIn ).'</li>';
		}
		
		if( $eb_earlyCancellationStr != '' ){
			$earlyCancellationStr = explode('::', $eb_earlyCancellationStr);
			if( $earlyCancellationStr[0] == "PERSENTAGE" ) $mode = '%';
			else $mode = $bcur;
			$termsStr .= '<li><strong>'.$earlyCancellationStr[1].' '.$mode.'</strong> '.__( $eb_lang_ChargeForCancellations ).' <strong>'.$earlyCancellationStr[2].'</strong> <strong>'.__( $eb_lang_BeforeDays ).'</strong> '.__( $eb_lang_BeforeCheckIn ).'</li>';
		}
		
		if( $eb_freeCancellationStr != '' ) $termsStr .= '<li><strong>'.__( $eb_lang_NoCharge ).'</strong> '.__( $eb_lang_ChargeForCancellations ).' <strong>'.$eb_freeCancellationStr.'</strong> <strong>'.__( $eb_lang_BeforeDays ).'</strong> '.__( $eb_lang_BeforeCheckIn ).'</li>';

		$termsStr .= '</ul>';
		
		if( $roomsLateCheckoutStr != '' ){
			$termsStr .= '<strong>'.__( $eb_lang_LateCheckOutPolicy ).'</strong>';
			$termsStr .= '<ul>';
			$termsStr .= $roomsLateCheckoutStr;
			$termsStr .= '</ul>';
			$termsStr .= '<em>'.__( $eb_lang_LateCheckOutStr ).' <strong>'.get_bloginfo('name').'</strong></em>';
		}
		$terms_page_id = get_option('eb-terms');
		$terms_page_data = get_page( $terms_page_id );
		$termsStr .= '<div class="general-title">'.__( $eb_lang_PoliciesOf ).' <strong>'.get_bloginfo('name').'</strong></div>';
		$termsStr .= __( $terms_page_data->post_content );
		echo $termsStr;

		//echo $eb_editBookingAllowedDatePeriod.'<br />'.$eb_editBookingDates.'<br />'.$eb_editBookingAddRooms.'<br />'.$eb_editBookingRemoveRooms;
		?>
	</div>
	<div style="clear:both;" align="right">
		<input name="bID" type="hidden" value="<?php echo $bID; ?>" />		
   	<input name="ccur" type="hidden" value="<?php echo $ccur; ?>" />   	
   	<input type="hidden" value="<?php echo $hiddenRoomHolder; ?>" name="roomHolder" />
   	<input type="hidden" value="<?php echo $from; ?>" name="from" />
   	<input type="hidden" value="<?php echo $to; ?>" name="to" />
   	<input type="hidden" name="eb" value="report" />
		<input type="submit" value="<?php _e( $eb_lang_CompleteYourBooking );?>" id="complete-booking-btn" class="eb-search-button" disabled="disabled" />
	</div>

	</form>
	
	<!-- ====FOR THE QTRANSLATE FIX==== -->
	<?php
	$page_idLang = get_option('eb-booking-review');
	if( get_option('permalink_structure')  == "") $permalinkForLangs = get_site_url().'?page_id='.$page_idLang.'&lang='.$lang;
	else $permalinkForLangs = get_permalink( $page_idLang );
	?>
	<form id="booking-review-trans-fix-frm" action="<?php echo $permalinkForLangs; ?>" method="post">
		<input type="hidden" name="eb" value="booking" />
		<input type="hidden" name="booking-data" value="<?php echo addslashes( $_POST['booking-data']); ?>" />
		<input type="hidden" name="bID" value="<?php echo addslashes( $_POST['bID']); ?>" />
		<input type="hidden" name="from" value="<?php echo addslashes( $_POST['from']); ?>" />
		<input type="hidden" name="to" value="<?php echo addslashes( $_POST['to']); ?>" />
		<input type="hidden" name="ccur" value="<?php echo addslashes( $_POST['ccur']); ?>" />
		<?php
		$location = addslashes( $_POST['location'] );
		$locationType = addslashes( $_POST['type'] );
		$locationID = addslashes( $_POST['lid'] );
		?>
		<input type="hidden" name="location" value="<?php echo $location; ?>" />
		<input type="hidden" id="eb-location-type" name="type" value="<?php echo $locationType; ?>" />
		<input type="hidden" id="eb-location-id" name="lid" value="<?php echo $locationID; ?>" />
		<input type="hidden" id = "bID" name="b" value="<?php echo $bID; ?>" />
	</form>
	
<script type="text/javascript" >
	jQuery(document).ready( function(){
		jQuery("#paypalRadio").prop('checked', false);
		jQuery("#bankRadio").prop('checked', false);
		jQuery('#complete-booking-btn').attr('disabled','disabled');
	});
	jQuery("input").click(function(){jQuery('.input-error').hide('slow');});
	
	function showTerms(){
		if( jQuery("#terms-area").is(":visible") ){ jQuery("#terms-area").slideUp("fast", function(){
				jQuery("#terms-area").hide('fast');
			}); 
		}
		else jQuery("#terms-area").show('slow');
	}
	
	
	function activateCompleteBooking(){
		
		if( jQuery('#terms-checkbox').is(":checked") ) jQuery('#complete-booking-btn').removeAttr('disabled');
		else jQuery('#complete-booking-btn').attr('disabled','disabled');
		
	}
	
	
	function isValidEmail(emailAddress) {
   	var pattern = new RegExp(/^((([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+(\.([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+)*)|((\x22)((((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(([\x01-\x08\x0b\x0c\x0e-\x1f\x7f]|\x21|[\x23-\x5b]|[\x5d-\x7e]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(\\([\x01-\x09\x0b\x0c\x0d-\x7f]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]))))*(((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(\x22)))@((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?$/i);
   	return pattern.test(emailAddress);
	}
	function checkBookingData(){		
		var submitForm = true; 
		var hasEmptyFields = false;
		
		jQuery('.input-error').hide('slow');
		
		if( jQuery("#fname").val() == '' ) {
			hasEmptyFields = true;
			jQuery("#fname_error").html("<?php _e($eb_lang_EnterFname)?>").show('fast');
		}
		if( jQuery("#lname").val() == '' ){
			hasEmptyFields = true;
			jQuery("#lname_error").html("<?php _e($eb_lang_EnterLname)?>").show('fast');
		}
		if( jQuery("#email").val() == '' ){
			hasEmptyFields = true;
			jQuery("#email_error").html("<?php _e($eb_lang_EnterEmail)?>").show('fast');
		}
		if( !isValidEmail( jQuery("#email").val() ) && jQuery("#email").val() != '' ){
			hasEmptyFields = true;
			jQuery("#email_error").html("<?php _e($eb_lang_WrongEmail)?>").show('fast');
		}
		<?php if( $email == '' ){?>
		if( jQuery("#conf-email").val() == '' ){
			hasEmptyFields = true;
			jQuery("#conf_email_error").html("<?php _e($eb_lang_EnterEmailConf)?>").show('fast');
		}
		if( jQuery("#conf-email").val() != jQuery("#email").val() ){
			hasEmptyFields = true;
			jQuery("#conf_email_error").html("<?php _e($eb_lang_EmailNotMatch)?>").show('fast');
		}
		<?php 
		}
		if( $hasBank || $hasPaypal ){
		 ?>
		 if ( !jQuery('#paypalRadio').is(':checked') && !jQuery('#bankRadio').is(':checked') ) {
		 	hasEmptyFields = true;
			jQuery("#select_payment_error").html("<?php _e($eb_lang_SelectPayment)?>").show('fast');
		 }
		 <?php }?>
		
		if( hasEmptyFields ) submitForm = false; 
		return submitForm;
	}
	
	function dispPaypalFee( disp ){
		if( disp == "show" ){
			jQuery("#paypal-fee").show('fast');
			jQuery("#total-with-paypal").show('fast');
		}
		else{
			jQuery("#paypal-fee").hide('fast');
			jQuery("#total-with-paypal").hide('fast');
		}
	} 
</script>
	<?php
	
	
}

	function roomPrice( $bID, $rID, $fromDate, $toDate, $ccur ){
		global $wpdb;
		global $table_prefix;
		$interval = date_diff(date_create( $fromDate ), date_create( $toDate ) );
		$daysNum = (int)$interval->format('%a');
		
		$bcur = $wpdb->get_row('select meta_value from '.$table_prefix.'postmeta where post_id = '.$bID.' and meta_key = "eb_currency"');
		$bcur = $bcur->meta_value;
		
		$roomPrice = 0;
		
		$checkIfHasSeasons = $wpdb->get_row('select meta_value from '.$table_prefix.'postmeta where post_id = '.$bID.' and meta_key = "eb_hasSeasons"');
		if($checkIfHasSeasons->meta_value == "YES"){
			$fDate = explode('-', $fromDate);
			$fromYear = $fDate[2];
			$tDate = explode('-', $toDate);
			$toYear = $tDate[2];
			
			$lowSeason = $wpdb->get_row('select meta_value from '.$table_prefix.'postmeta where post_id = '.$bID.' and meta_key = "eb_lowSeason"');	
			$lowSeasonStart = explode('[-]',$lowSeason->meta_value);
			$lowSeasonEnd = $lowSeasonStart[1];			
			$lowSeasonStart = $lowSeasonStart[0];
			$lowSeasonStart = str_replace("2011",$fromYear,$lowSeasonStart);
			$lowSeasonEnd = str_replace("2011",$toYear,$lowSeasonEnd);
			
			$midSeason = $wpdb->get_row('select meta_value from '.$table_prefix.'postmeta where post_id = '.$bID.' and meta_key = "eb_midSeason"');	
			$midSeasonStart = explode('[-]',$midSeason->meta_value);
			$midSeasonEnd = $midSeasonStart[1];			
			$midSeasonStart = $midSeasonStart[0];
			$midSeasonStart = str_replace("2011",$fromYear,$midSeasonStart);
			$midSeasonEnd = str_replace("2011",$toYear,$midSeasonEnd);
			
			$highSeason = $wpdb->get_row('select meta_value from '.$table_prefix.'postmeta where post_id = '.$bID.' and meta_key = "eb_highSeason"');	
			$highSeasonStart = explode('[-]',$highSeason->meta_value);
			$highSeasonEnd = $highSeasonStart[1];			
			$highSeasonStart = $highSeasonStart[0];
			$highSeasonStart = str_replace("2011",$fromYear,$highSeasonStart);
			$highSeasonEnd = str_replace("2011",$toYear,$highSeasonEnd);
			
			$midSeason2 = $wpdb->get_row('select meta_value from '.$table_prefix.'postmeta where post_id = '.$bID.' and meta_key = "eb_midSeason2"');	
			$midSeasonStart2 = explode('[-]',$midSeason2->meta_value);
			$midSeasonEnd2 = $midSeasonStart2[1];			
			$midSeasonStart2 = $midSeasonStart2[0];
			$midSeasonStart2 = str_replace("2011",$fromYear,$midSeasonStart2);
			$midSeasonEnd2 = str_replace("2011",$toYear,$midSeasonEnd2);
			
			$lowSeason2 = $wpdb->get_row('select meta_value from '.$table_prefix.'postmeta where post_id = '.$bID.' and meta_key = "eb_lowSeason2"');	
			$lowSeasonStart2 = explode('[-]',$lowSeason2->meta_value);
			$lowSeasonEnd2 = $lowSeasonStart2[1];			
			$lowSeasonStart2 = $lowSeasonStart2[0];
			$lowSeasonStart2 = str_replace("2011",$fromYear,$lowSeasonStart2);
			$lowSeasonEnd2 = str_replace("2011",$toYear,$lowSeasonEnd2);
			
			$lowSeasonPrice = $wpdb->get_row('select meta_value from '.$table_prefix.'postmeta where post_id = '.$rID.' and meta_key = "eb_lprice"');
			$lowSeasonPrice = $lowSeasonPrice->meta_value;
			$midSeasonPrice = $wpdb->get_row('select meta_value from '.$table_prefix.'postmeta where post_id = '.$rID.' and meta_key = "eb_mprice"');
			$midSeasonPrice = $midSeasonPrice->meta_value;
			$highSeasonPrice = $wpdb->get_row('select meta_value from '.$table_prefix.'postmeta where post_id = '.$rID.' and meta_key = "eb_hprice"');
			$highSeasonPrice = $highSeasonPrice->meta_value;
						
			$pos = '0';
			for($dayCount = 0; $dayCount < $daysNum ; $dayCount++){
				$next_date = date('Y-m-d', strtotime($fromDate .' +'.$dayCount.' day'));
				if($next_date >= $lowSeasonStart && $next_date < $lowSeasonEnd){
					$roomPrice += $lowSeasonPrice;
					$pos = '1';					
				}
				if($next_date >= $midSeasonStart && $next_date < $midSeasonEnd){
					$roomPrice += $midSeasonPrice;
					$pos = '2: '.$midSeasonStart.' '.$midSeasonEnd.' from:'.$fromDate;					
				}
				if($next_date >= $highSeasonStart && $next_date < $highSeasonEnd){
					$roomPrice += $highSeasonPrice;
					$pos = '3';				
				}
				if($next_date >= $midSeasonStart2 && $next_date < $midSeasonEnd2){
					$roomPrice += $midSeasonPrice;
					$pos = '4';					
				}
				if($next_date >= $lowSeasonStart2 && $next_date <= $lowSeasonEnd2){
					$roomPrice += $lowSeasonPrice;
					$pos = '5';					
				}
			}
			
			
		}	
		else{
			$roomPrice = $wpdb->get_row('select meta_value from '.$table_prefix.'postmeta where post_id = '.$rID.' and meta_key = "eb_fprice"');
			$roomPrice = $roomPrice->meta_value;
		 	$roomPrice *= $daysNum;
			$roomPrice = round( $roomPrice );
		}
		if($ccur != "htlcur"){
			//if( $bcur != $ccur) $roomPrice = convert($roomPrice,$bcur,$ccur);
		//	return $roomPrice;
		}
		return $roomPrice;

		
	}
	
	function convert($amount,$from,$to,$decimals=2) {
		global $wpdb;
		$exchange_rates = array();
		$xRatesQ = $wpdb->get_results('select * from currencies where currency = "'.$from.'" OR currency = "'.$to.'"');  
		foreach($xRatesQ as $xRate){
			$exchange_rates[$xRate->currency] = $xRate->rate;
		}
		return(number_format(($amount/$exchange_rates[$from])*$exchange_rates[$to],$decimals));
	}
	
	function selectOptions( $start = 0, $count=10 ){
		$otpions = '';
		for($oc=$start;$oc<=$count;$oc++){
			$options .= "<option value = '$oc'>$oc</option>";
		}
		return $options;
	}
	
	function paypalFee( $cost, $ccur, $decimals=2 ){
		$extraCost = convert( 0.30, 'USD', $ccur );
		$extraCost += ($cost * 2.9) / 100;
		return ( number_format( $extraCost, $decimals ) );
	}
	
	function paypalTotal($totalRoomCost, $paypalFee, $decimals=2){
		return ( number_format( ( $totalRoomCost + $paypalFee ), $decimals ) );
	}
?>
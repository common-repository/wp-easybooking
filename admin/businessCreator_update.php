<?php
$errorMessage = '';
$hasErrors = false;
if($_POST['ebTitle'] ==''){
	$hasErrors = true;
	$hasErrors = true;
	$errorMessage .= '<p><em>You have to enter a name to the business</em></p>';
}
//*****Check if business name exist already*****
$nameExistRes = $wpdb->get_row('select ID, post_title from wp_posts where post_title = "'.$_POST['ebTitle'].'" and ID != '.$_REQUEST['bID']);
if(!empty($nameExistRes)){
	$hasErrors = true;
	$errorMessage .= '<p><em>The name of the business you entered <font color="red">('.$_POST['ebTitle'].')</font> already exist. Please try using a different one</em></p>';
}
if($_POST['ebOwnerID'] ==''){
	$hasErrors = true;
	$errorMessage .= '<p><em>You have to enter an owner to the business</em></p>';
}
if($_POST['eb_email'] !=''){
	 if ( !is_email( $_POST['eb_email'] ) ){
		$hasErrors = true;
		$errorMessage .= '<p><em>The email you entered is not valid</em></p>';
	}
}

if($_POST['eb_operatingPeriodStart'] !='' && $_POST['eb_operatingPeriodStart'] !='NOT_SET' && $_POST['eb_operatingPeriodEnd'] !='' && $_POST['eb_operatingPeriodEnd'] !='NOT_SET'){
	if($_POST['eb_operatingPeriodEnd'] < $_POST['eb_operatingPeriodStart']){
		$hasErrors = true;
		$errorMessage .= '<p><em>The date that the business starts operating must be earlier than the date it stops operating</em></p>';
	}	
}

if(!$hasErrors){
	$businessValuesUpdates = array();
	$businessValuesUpdates['ID'] = $_REQUEST['bID'];
	$businessValuesUpdates['post_author'] = $_POST['ebOwnerID'];
  	$businessValuesUpdates['post_name'] = $_POST['ebTitle'];
  	$businessValuesUpdates['post_title'] = $_POST['ebTitle'];
  	$businessValuesUpdates['post_content'] = $_POST['content'];
  	$businessValuesUpdates['post_modified'] = $_POST['curTime'];
  	$businessValuesUpdates['post_modified_gmt'] = gmdate("Y-m-d H:i:s");
  	
  	 wp_update_post( $businessValuesUpdates );
  	 
	if( isset( $_POST['eb_currency'] ) ) update_post_meta($_REQUEST['bID'],'eb_currency', $_POST['eb_currency']);
  	//if( isset( $_POST['eb_stars'] ) ) update_post_meta($_REQUEST['bID'],'eb_stars', $_POST['eb_stars']);
  	if(isset($_POST['eb_stars'])){
			update_post_meta($_REQUEST['bID'],'eb_stars', $_POST['eb_stars']);
			$wpdb->query('update eb_bushelpvals set stars = '.$_POST['eb_stars'].' where bID = '.$_REQUEST['bID']);
		}
  	if ( isset($_POST['eb_email'] ) ) update_post_meta($_REQUEST['bID'],'eb_email', $_POST['eb_email']);
  	if( isset( $_POST['eb_tel1'] ) ) update_post_meta($_REQUEST['bID'],'eb_tel1', $_POST['eb_tel1']);
  	if( isset( $_POST['eb_tel2'] ) ) update_post_meta($_REQUEST['bID'],'eb_tel2', $_POST['eb_tel2']);
  	if( isset( $_POST['eb_fax'] ) ) update_post_meta($_REQUEST['bID'],'eb_fax', $_POST['eb_fax']);
  	if( isset( $_POST['eb_addressNum'] ) ) update_post_meta($_REQUEST['bID'],'eb_addressNum', $_POST['eb_addressNum']);
  	if( isset( $_POST['eb_zip'] ) ) update_post_meta($_REQUEST['bID'],'eb_zip', $_POST['eb_zip']);
  	if( isset( $_POST['eb_coordinates'] ) ) update_post_meta($_REQUEST['bID'],'eb_coordinates', $_POST['eb_coordinates']);
  	if( isset( $_POST['eb_IBAN'] ) ) update_post_meta($_REQUEST['bID'],'eb_IBAN', $_POST['eb_IBAN']);
  	if( isset( $_POST['eb_bankSWIFT'] ) ) update_post_meta($_REQUEST['bID'],'eb_SWIFT', $_POST['eb_bankSWIFT']);
  	if( isset( $_POST['eb_BankName'] ) ) update_post_meta($_REQUEST['bID'],'eb_BankName', $_POST['eb_BankName']);
	if( isset( $_POST['eb_bankAccount'] ) ) update_post_meta($_REQUEST['bID'],'eb_bankAccount', $_POST['eb_bankAccount']);
	if( isset( $_POST['eb_paypalAccount'] ) ) update_post_meta($_REQUEST['bID'],'eb_paypalAccount', $_POST['eb_paypalAccount']);
	if( isset( $_POST['eb_payAtReception'] ) && $_POST['eb_payAtReception'] == 'YES') update_post_meta($_REQUEST['bID'],'eb_payAtReception', $_POST['eb_payAtReception']);
	else update_post_meta($_REQUEST['bID'],'eb_payAtReception', "NO");
	if(isset($_POST['eb_extraBedPrice'])){ 
		$extraBedPrice = correctPriceNum($_POST['eb_extraBedPrice']);
		update_post_meta($_REQUEST['bID'],'eb_extraBedPrice', $extraBedPrice);
	}
	
	/*====== CANCELLATION POLICIES ======*/
	$updateCancelationPolicies = false;
	$hasAnyKindOfCancellation = false;
	
	$hasCancelation = false;
	$hasEarlyCancelation = false;
	$hasFreeCancelation = false;
	if( isset( $_POST['cancellationCharge'] ) && $_POST['cancellationCharge'] == "YES" ) $hasCancelation = true;
	if( isset( $_POST['earlyCancellationCharge'] ) && $_POST['earlyCancellationCharge'] == "YES" ) $hasEarlyCancelation = true;
	if( isset( $_POST['freeCancellation'] ) && $_POST['freeCancellation'] == "YES" ) $hasFreeCancelation = true;
	
	if( $hasCancelation || $hasEarlyCancelation || $hasFreeCancelation ) $hasAnyKindOfCancellation = true;
	
	$cancelationChargeDays = 0;
	$earlyCancelationChargeDays = 0;
	$freeCancelationChargeDays = 0;
	if( isset( $_POST['chargedCancellationDaysLimit'] ) && $_POST['chargedCancellationDaysLimit'] != '' ) $cancelationChargeDays = $_POST['chargedCancellationDaysLimit'];
	if( isset( $_POST['chargedEarlyCancellationDaysLimit'] ) && $_POST['chargedEarlyCancellationDaysLimit'] != '' ) $earlyCancelationChargeDays = $_POST['chargedEarlyCancellationDaysLimit']; 
	if( isset( $_POST['freeCancellationDaysLimit'] ) && $_POST['freeCancellationDaysLimit'] != '' ) $freeCancelationChargeDays = $_POST['freeCancellationDaysLimit'];
	
	if( $hasCancelation && $cancelationChargeDays != 0 ){
		if( $hasEarlyCancelation ){
			if( $cancelationChargeDays < $earlyCancelationChargeDays ) $updateCancelationPolicies = true;
			else $updateCancelationPolicies = false;
		}
		if( $hasFreeCancelation ){
			if( $cancelationChargeDays < $freeCancelationChargeDays ) $updateCancelationPolicies = true;
			else $updateCancelationPolicies = false;
			if( $hasEarlyCancelation ) {
				if( $earlyCancelationChargeDays < $freeCancelationChargeDays ) $updateCancelationPolicies = true;
			else $updateCancelationPolicies = false;
			}
		}
		if( !$hasEarlyCancelation && !$hasFreeCancelation ) $updateCancelationPolicies = true;
	}
	
	if( $hasFreeCancelation && !$hasCancelation && !$hasEarlyCancelation ) $updateCancelationPolicies = true;
	
	if( $updateCancelationPolicies ){
		
		if( $hasCancelation && ( $_POST['cancellationChargePrice'] != '' || $_POST['cancellationPercentageCharge'] != '' )) {
			$mode = '';
			$charge = 0;
			if(isset( $_POST['cancellationChargePrice'] ) && $_POST['cancellationChargePrice'] != '' && $_POST['cancellationPercentageCharge'] == '') {
				$mode = 'CASH';
				$charge = $_POST['cancellationChargePrice'];
			}
			if(isset( $_POST['cancellationPercentageCharge'] ) && $_POST['cancellationPercentageCharge'] != '' && $_POST['cancellationChargePrice'] == '') {
				$mode = 'PERSENTAGE';
				$charge = $_POST['cancellationPercentageCharge'];
			}
			$cancellationStr = $mode.'::'.$charge.'::'.$cancelationChargeDays;
			update_post_meta($_REQUEST['bID'],'eb_cancellationCharge', $cancellationStr);
		}
		else delete_post_meta($_REQUEST['bID'],'eb_cancellationCharge');
		
		if( $hasEarlyCancelation && ( $_POST['earlyCancellationChargePrice'] != '' || $_POST['earlyCancellationPercentageCharge'] != '' ) ) {
			$cancellationStr = '';
			$mode = '';
			$charge = 0;
			if(isset( $_POST['earlyCancellationChargePrice'] ) && $_POST['earlyCancellationChargePrice'] != '' && $_POST['earlyCancellationPercentageCharge'] == '') {
				
				$mode = 'CASH';
				$charge = $_POST['earlyCancellationChargePrice'];
			}
			if(isset( $_POST['earlyCancellationPercentageCharge'] ) && $_POST['earlyCancellationPercentageCharge'] != '' && $_POST['earlyCancellationChargePrice'] == '') {
				$mode = 'PERSENTAGE';
				$charge = $_POST['earlyCancellationPercentageCharge'];
			}
			
			$cancellationStr = $mode.'::'.$charge.'::'.$earlyCancelationChargeDays;
			
			update_post_meta($_REQUEST['bID'],'eb_earlyCancellationCharge', $cancellationStr);
		}
		else delete_post_meta($_REQUEST['bID'],'eb_earlyCancellationCharge');
		
		if( $hasFreeCancelation) {
			$cancellationStr = '';
			$mode = 'FREE';
			
			$cancellationStr = $freeCancelationChargeDays;
			
			update_post_meta($_REQUEST['bID'],'eb_freeCancellationCharge', $cancellationStr);
		}
		else delete_post_meta($_REQUEST['bID'],'eb_freeCancellationCharge');
	}
	else{
		if( $hasAnyKindOfCancellation ) echo '<div class="updated">Sorry, could not save cancellation policy.<br /><p style="padding-left:10px">Please pay attention at the number of days you select in each occasion.<br />Earlier cancellations should be set to more days than later cancellations. <br />Free cancellation should be set to more days if combined with charged cancellations.</p></div>';
	}
	
	/**================ALLOW USERS TO EDIT BOOKINGS===================*/
	if($_POST['editBookingAllowedDatePeriod']!= '' )update_post_meta($_REQUEST['bID'],'eb_editBookingAllowedDatePeriod', $_POST['editBookingAllowedDatePeriod']);
	else delete_post_meta($_REQUEST['bID'],'eb_editBookingAllowedDatePeriod');
	
	if($_POST['editBookingDates']!= '' )update_post_meta($_REQUEST['bID'],'eb_editBookingDates', $_POST['editBookingDates']);
	else delete_post_meta($_REQUEST['bID'],'eb_editBookingDates');
	
	if($_POST['editBookingAddRooms']!= '' )update_post_meta($_REQUEST['bID'],'eb_editBookingAddRooms', $_POST['editBookingAddRooms']);
	else delete_post_meta($_REQUEST['bID'],'eb_editBookingAddRooms');
	
	if($_POST['editBookingRemoveRooms']!= '' )update_post_meta($_REQUEST['bID'],'eb_editBookingRemoveRooms', $_POST['editBookingRemoveRooms']);
	else delete_post_meta($_REQUEST['bID'],'eb_editBookingRemoveRooms');
	
	if($_POST['editBookingRemoveRoomsCostValue']!= '' )update_post_meta($_REQUEST['bID'],'eb_editBookingRemoveRoomsCost', $_POST['editBookingRemoveRoomsCostValue']);
	else delete_post_meta($_REQUEST['bID'],'eb_editBookingRemoveRoomsCost');
	
	if($_POST['editBookingRemoveDaysCostValue']!= '' )update_post_meta($_REQUEST['bID'],'eb_editBookingRemoveDaysCost', $_POST['editBookingRemoveDaysCostValue']);
	else delete_post_meta($_REQUEST['bID'],'eb_editBookingRemoveDaysCost');
	/*================================================================*/
		
	if($_POST['eb_operatingPeriodStart']!= '' )update_post_meta($_REQUEST['bID'],'eb_operatingPeriodStart', $_POST['eb_operatingPeriodStart']);
	if($_POST['eb_operatingPeriodEnd']!= '' )update_post_meta($_REQUEST['bID'],'eb_operatingPeriodEnd', $_POST['eb_operatingPeriodEnd']);
	if($_POST['eb_lateCheckoutTime']!= '' )update_post_meta($_REQUEST['bID'],'eb_lateCheckoutTime', $_POST['eb_lateCheckoutTime']);
	if($_POST['eb_lateCheckoutReadyTime']!= '' )update_post_meta($_REQUEST['bID'],'eb_lateCheckoutReadyTime', $_POST['eb_lateCheckoutReadyTime']);	
	
	
	$datesConflict = false;
	$datesErrorMsg = '';
	//*****LOW SEASON CHECK*****	
	if($_POST['eb_lowSeasonEnd'] < $_POST['eb_lowSeasonStart']){
		$datesConflict = true;
		$datesErrorMsg .= '<p><em>The low season can\'t end before it starts ...</em></p>';
	}
	//*****MID SEASON CHECK*****	
		
	if($_POST['eb_midSeasonEnd'] < $_POST['eb_midSeasonStart']){
		$datesConflict = true;
		$datesErrorMsg .= '<p><em>The mid season can\'t end before it starts ...</em></p>';
	}
	if($_POST['eb_midSeasonStart'] < $_POST['eb_lowSeasonEnd'] && $_POST['eb_midSeasonStart'] != 'NOT_SET'){
		$datesConflict = true;
		$datesErrorMsg .= '<p><em>The mid season can\'t start during low season</em></p>';
	}	
	//*****HIGH SEASON CHECK*****
	
	if($_POST['eb_highSeasonEnd'] < $_POST['eb_highSeasonStart']){
		$datesConflict = true;
		$datesErrorMsg .= '<p><em>The high season can\'t end before it starts ...</em></p>';
	}
	if($_POST['eb_highSeasonStart'] < $_POST['eb_midSeasonEnd'] && $_POST['eb_highSeasonStart'] != 'NOT_SET'){
		$datesConflict = true;
		$datesErrorMsg .= '<p><em>The high season can\'t start during mid season</em></p>';
	}
	//*****MID SEASON FOLLOWING THE HIGH SEASON CHECK*****
	
	if($_POST['eb_midSeasonEnd2'] < $_POST['eb_midSeasonStart2']){
		$datesConflict = true;
		$datesErrorMsg .= '<p><em>The mid season can\'t end before it starts ...</em></p>';
	}
	if($_POST['eb_midSeasonStart2'] < $_POST['eb_highSeasonEnd'] && $_POST['eb_midSeasonStart2'] != 'NOT_SET'){
		$datesConflict = true;
		$datesErrorMsg .= '<p><em>The mid season following the high season can\'t start during high season</em></p>';
	}
	//*****LOW SEASON FOLLOWING THE HIGH SEASON CHECK*****
		
	if($_POST['eb_lowSeasonEnd2'] < $_POST['eb_lowSeasonStart2']){
		$datesConflict = true;
		$datesErrorMsg .= '<p><em>The Low season can\'t end before it starts ...</em></p>';
	}
	if($_POST['eb_lowSeasonStart2'] < $_POST['eb_midSeasonEnd2'] && $_POST['eb_lowSeasonStart2'] != 'NOT_SET'){
		$datesConflict = true;
		$datesErrorMsg .= '<p><em>The low season following the high season can\'t start during previous season</em></p>';
	}
	
	
	if(!$datesConflict){
		if($_POST['eb_hasSeasons']!= '' && $_POST['eb_hasSeasons']== 'YES'){
			$endOperatingPeriodVal = '';
			update_post_meta($_REQUEST['bID'],'eb_hasSeasons','YES');
			if($_POST['eb_lowSeasonStart']!= 'NOT_SET' && $_POST['eb_lowSeasonEnd']!= 'NOT_SET'){ 
				update_post_meta($_REQUEST['bID'],'eb_lowSeason', $_POST['eb_lowSeasonStart'].'[-]'.$_POST['eb_lowSeasonEnd']);
				update_post_meta($_REQUEST['bID'],'eb_operatingPeriodStart', $_POST['eb_lowSeasonStart']);
			}
			if($_POST['eb_midSeasonStart']!= 'NOT_SET' && $_POST['eb_midSeasonEnd']!= 'NOT_SET') update_post_meta($_REQUEST['bID'],'eb_midSeason', $_POST['eb_midSeasonStart'].'[-]'.$_POST['eb_midSeasonEnd']);
			if($_POST['eb_highSeasonStart']!= 'NOT_SET' && $_POST['eb_highSeasonEnd']!= 'NOT_SET'){ 
				update_post_meta($_REQUEST['bID'],'eb_highSeason', $_POST['eb_highSeasonStart'].'[-]'.$_POST['eb_highSeasonEnd']);
				$endOperatingPeriodVal = $_POST['eb_highSeasonEnd'];
			}
			if($_POST['eb_midSeasonStart2']!= 'NOT_SET' && $_POST['eb_midSeasonEnd2']!= 'NOT_SET'){
				update_post_meta($_REQUEST['bID'],'eb_midSeason2', $_POST['eb_midSeasonStart2'].'[-]'.$_POST['eb_midSeasonEnd2']);
				$endOperatingPeriodVal = $_POST['eb_midSeasonEnd2'];
			}
			if($_POST['eb_lowSeasonStart2']!= 'NOT_SET' && $_POST['eb_lowSeasonEnd2']!= 'NOT_SET'){
				update_post_meta($_REQUEST['bID'],'eb_lowSeason2', $_POST['eb_lowSeasonStart2'].'[-]'.$_POST['eb_lowSeasonEnd2']);
				$endOperatingPeriodVal = $_POST['eb_lowSeasonEnd2'];
			}
			
			update_post_meta($_REQUEST['bID'],'eb_operatingPeriodEnd', $endOperatingPeriodVal);
			
		}
		else update_post_meta($_REQUEST['bID'],'eb_hasSeasons','NO');
	}
	else{?>
		<div align="center">
	<div id="message" class="updated" style="width:700px" align="center">
		<strong><?php echo $datesErrorMsg;?></strong>
	</div>
	</div>
		
	<?php }
	$timeErrorMsg ='';
	$hoursConflict = false;
	if($_POST['eb_checkInTimeTo'] < $_POST['eb_checkInTimeFrom']){
		$hoursConflict = true;
		$timeErrorMsg .= '<p><em>The check in end hour can\'t proceed the start hour</em></p>';
	}
	if($_POST['eb_checkOutTimeTo'] < $_POST['eb_checkOutTimeFrom']){
		$hoursConflict = true;
		$timeErrorMsg .= '<p><em>The check out end hour can\'t proceed the start hour</em></p>';
	}
	
	if(!$hoursConflict){
		if($_POST['eb_checkInTimeFrom']!= 'NOT_SET' && $_POST['eb_checkInTimeTo']!= 'NOT_SET') update_post_meta($_REQUEST['bID'],'eb_checkInTime', $_POST['eb_checkInTimeFrom'].'[-]'.$_POST['eb_checkInTimeTo']);
		if($_POST['eb_checkOutTimeFrom']!= 'NOT_SET' && $_POST['eb_checkOutTimeTo']!= 'NOT_SET') update_post_meta($_REQUEST['bID'],'eb_checkOutTime', $_POST['eb_checkOutTimeFrom'].'[-]'.$_POST['eb_checkOutTimeTo']);
	}
	else{?>
		<div align="center">
	<div id="message" class="updated" style="width:700px" align="center">
		<strong><?php echo $timeErrorMsg;?></strong>
	</div>
	</div>
		
	<?php }
	
	$facilitiesStr = '';
	$facilities = '';
		if(isset($_POST['facilitiesChBox']))
			$facilities = $_POST['facilitiesChBox'];
		if(!empty($facilities))
		for($i=0; $i < sizeof($facilities); $i++){
			$facilitiesStr .= $facilities[$i].'|';
		}
		if($facilitiesStr != '')
			update_post_meta($_REQUEST['bID'], 'eb_facilities', $facilitiesStr);
			
		if(isset($_POST['eb_cities']) && $_POST['eb_cities']!= '') update_post_meta($_REQUEST['bID'], 'eb_cityID', $_POST['eb_cities']);
			
		if(isset($_POST['eb_isMultyLang']) && $_POST['eb_isMultyLang'] == "true"){
			
			
			$ebaddressStr = '';
			global $q_config;
			foreach($q_config['enabled_languages'] as $language) {
				if(isset($_POST['eb_address_'.$language])){
					$ebaddressStr .= '<!--:'.$language.'-->'.$_POST['eb_address_'.$language].'<!--:-->';  	
				}
				
			}
			update_post_meta($_REQUEST['bID'], 'eb_address', $ebaddressStr);
		}
		else{
			update_post_meta($_REQUEST['bID'], 'eb_address', $_POST['eb_address']);	
		}

}//end of if has no errors
else {?>
	<div align="center">
	<div id="message" class="updated" style="width:700px" align="center">
		<strong><?php echo $errorMessage;?></strong>
	</div>
	</div>
	<?php
}//end of if has errors


?>
<script type="text/javascript" >jQuery("#error_message").html("<?php echo $errorMessage;?>");</script>
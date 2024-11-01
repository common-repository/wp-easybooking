 <?php


//set up all variables:
$eb_BusinessPackDeal = '';
$eb_BusinessPackDealTitle = '';
$eb_BusinessTitle = '';
$eb_BusinessDateReg = '';
$eb_BusinessType = '';
$eb_BusinessStars = '';
$eb_BusinessOwnerID = '';
$eb_BusinessLogo = '';
$eb_BusinessDefaultLogo = '';
$eb_BusinessCurrency = '';
$eb_BusinessFacilities = '';
$eb_BusinessEmail = '';
$eb_BusinessTel1 = '';
$eb_BusinessTel2 = '';
$eb_BusinessFax = '';
$eb_BusinessCityID = '';
$eb_BusinessRegionID = '';
$eb_BusinessCountryID = '';
$eb_BusinessAddress = '';
$eb_BusinessAddressNumber = '';
$eb_BusinessZip = '';
$eb_BusinessCoordinates = '';
$eb_BusinessSWIFT = '';
$eb_BusinessIBAN = '';
$eb_BusinessBankAccount = '';
$eb_BusinessBankName = '';
$eb_BusinessPaypalAccount = '';
$eb_BusinessDescription = ''; 
$eb_BusinessLastModified ='';
//date vars
$eb_BusinessOperatingPeriodStart ='';
$eb_BusinessOperatingPeriodEnd ='';

$eb_BusinessHasSeasons ='';

$eb_BusinessLowSeasonStart ='';
$eb_BusinessLowSeasonEnd ='';
$eb_BusinessMidSeasonStart ='';
$eb_BusinessMidSeasonEnd ='';
$eb_BusinessHighSeasonStart ='';
$eb_BusinessHighSeasonEnd ='';
$eb_BusinessLowSeasonStart2 ='';
$eb_BusinessLowSeasonEnd2 ='';
$eb_BusinessMidSeasonStart2 ='';
$eb_BusinessMidSeasonEnd2 ='';
$eb_BusinessCheckInFrom = '';
$eb_BusinessCheckInTo = '';
$eb_BusinessCheckOutFrom = '';
$eb_BusinessCheckOutTo = '';
$eb_lateCheckoutTime = '';

$eb_BusinessFormTitle = 'Enter the details of the new Business';
$eb_BusinessStatus = "";
$eb_BusinessSetStatus = "";

$eb_cancellationStr = '';
$eb_earlyCancellationStr = '';
$eb_freeCancellationStr = '';

$eb_editBookingAllowedDatePeriod = '';
$eb_editBookingDates = '';
$eb_editBookingAddRooms = '';
$eb_editBookingRemoveRooms = '';
$eb_editBookingRemoveDaysCost = '';
$eb_editBookingRemoveRoomsCost = '';

$eb_extraBedPrice = '';
 ?>
<div id="post-body">
<div id="post-body-content">
<?php
global $ebPluginFolderName;// <-- apaiteitai sta ajax calls sta paths
global $eb_adminUrl;
global $wpdb;
global $table_prefix;

$eb_page = addslashes( $_REQUEST['page'] );
$ebName = '';
$businessId = '';	
$action = '';


	//=========================================================
	//           CHECK USER LEVEL
	//=========================================================
//global $current_user;
$user_id = get_current_user_id();
$user_info = get_userdata( $user_id );
$targetPage = 'easy_booking_menu';
//$editBusinessLink = 'busines_menu';
$editBusinessLink = $eb_page;
$businessInBusinessmanList = false;
$noAdmin_whereStr = '';
if($user_info->user_level == 0) {
	$targetPage = 'business_list';
	//$editBusinessLink = 'business_list';
	$noAdmin_whereStr = 'and post_author = "'.$user_info->ID.'"';
	if(isset($_REQUEST['bID'])){
		$businesses = $wpdb->get_results('select ID from '.$table_prefix.'posts where post_author = '.$user_info->ID.' AND post_parent=0');
		foreach ($businesses as $business){
			if($_REQUEST['bID'] == $business->ID) $businessInBusinessmanList = true; 
		}//end of foreach
		if(!$businessInBusinessmanList ) die('<div class="error"><b>Not a valid business.</b> <br>'.$user_info->ID.'<i>Please go back and try again. For further instructions please contact your business administrator</i></div>');
	}
}
	//=========================================================
	//=========================================================	
		
	


if(!isset($_REQUEST['bID']) && !isset($_POST['action'])){
	$action = '&action=add';	
}

if(!isset($_REQUEST['bID']) && isset($_REQUEST['action']) && $_REQUEST['action'] == "add"){
	//edw ginetai to insert k meta emfanizetai to edit tou business
	include('businessCreator_insert.php');
	if( $_REQUEST['page'] == "business_control" ) $editBusinessLink = "busines_menu";
}
if(isset($_REQUEST['action']) && $_REQUEST['action'] == "update"){
	include('businessCreator_update.php');
}
if(isset($_REQUEST['bID']) || $businessId !=''){
	if($businessId == '') $businessId = $_REQUEST['bID'];
	
	//================================================================
	//		DELETE IMAGE 	
	//================================================================
	if (isset($_REQUEST['delimg'])){
		$imgPath = ABSPATH.'wp-content/plugins/'.$ebPluginFolderName.'/images/businessImg';
		if(is_file($imgPath.'/thumbs/'.$_REQUEST['delimg']))
		unlink($imgPath.'/thumbs/'.$_REQUEST['delimg']);
		if(is_file($imgPath.'/'.$_REQUEST['delimg']))
		unlink($imgPath.'/'.$_REQUEST['delimg']);

		$roomImages = get_post_meta($businessId, "eb_logo");	
		if(!empty($roomImages)) $roomImages = $roomImages[0]; else $roomImages ='';							
		$roomLogo = explode("|", $roomImages);
		
		$deletedImgStr = '';
		for($i=0; $i < count($roomLogo); $i++){
			if($roomLogo[$i] != $_REQUEST['delimg'] && $roomLogo[$i] != '')
				$deletedImgStr .= '|'.$roomLogo[$i];
		}		
		update_post_meta($businessId, 'eb_logo', $deletedImgStr);
		
		$businessDefLogo = get_post_meta($businessId, "eb_defaultLogo");
		if(!empty($businessDefLogo) && $businessDefLogo[0] == $_REQUEST['delimg']) update_post_meta($businessId, 'eb_defaultLogo', '');
	}
	
	//================================================================
	//       END OF DELETE IMAGE
	//================================================================
			//Gia na ginetai update to status| prepei na ginetai prin to businessData select
		if(isset($_REQUEST['statusAction']) && $_REQUEST['statusAction']== "updateBusinessStatus" && $_REQUEST['setStatus'] != ''){
			$eb_SetBusinessStatus = array();
  			$eb_SetBusinessStatus['ID'] = $businessId;
  			$eb_SetBusinessStatus['post_status'] = $_REQUEST['setStatus'];
  			if(wp_update_post( $eb_SetBusinessStatus )){
  				$businessStatusData = $wpdb->get_row('select post_author, post_title from '.$table_prefix.'posts where ID = '.$businessId. ' and post_parent=0');
				if(!empty($businessStatusData)){
						$ownerData = get_userdata( $businessStatusData->post_author );
						$eb_BusinessOwner = $ownerData->last_name.' '. $ownerData->first_name;

					if($_REQUEST['setStatus'] == 'publish'){
	  					$statusInformationMailSub = $businessStatusData->post_title.' is now Active for bookings at '.get_bloginfo('name');
	  					$statusInformationMailMsg = 'Hello Mr/Mrs '.$eb_BusinessOwner.',<br />';
	  					$statusInformationMailMsg .= '<br /><b>'.$businessStatusData->post_title.'</b> is now available for bookings!<br />
	  					You can start editing your business, adding rooms and controlling your bookings at <a href ="'.get_admin_url().'admin.php?page=business_list&bID='.$businessId.'">'.get_bloginfo('name').' <i>(Please press here to enter)</i></a>';
	  					$statusInformationMailMsg .= '<br /><br />Thank you for choosing <b>'.get_bloginfo('name').'</b><br />For any questions or instructions please contact us at '.get_bloginfo('admin_email');
  					}
  					
					if($_REQUEST['setStatus'] == 'draft'){
	  					$statusInformationMailSub = $businessStatusData->post_title.' is not Active any more for bookings at '.get_bloginfo('name');
	  					$statusInformationMailMsg = 'Hello Mr/Mrs '.$eb_BusinessOwner.',<br />';
	  					$statusInformationMailMsg .= '<br /><b>'.$businessStatusData->post_title.'</b> is not available for bookings any more!<br />
	  					You can still enter at your administration area and edit your business and add rooms at <a href ="'.get_admin_url().'admin.php?page=business_list&bID='.$businessId.'">'.get_bloginfo('name').'</a> but they will not show up in room search from users';
	  					$statusInformationMailMsg .= '<br /><br />For any questions or instructions please contact us at '.get_bloginfo('admin_email');
  					}	
  					if($statusInformationMailSub != '' && $statusInformationMailMsg != ''){
  						add_filter('wp_mail_content_type',create_function('', 'return "text/html"; '));
				   	add_filter('wp_mail_from', create_function('', 'return "'.get_bloginfo('admin_email').'"; '));
						add_filter('wp_mail_from_name', create_function('', 'return "'.get_bloginfo('name').'"; '));
	
						wp_mail($ownerData->user_email, $statusInformationMailSub, $statusInformationMailMsg);	
  					}
				}  				
  					
  			}
		}
		
		if( isset( $_REQUEST['unpub_reason'] ) && $_REQUEST['unpub_reason'] == "p_m" ) echo '<div class="updated">This business has been automatically unpublished because there were no payment methods set.</div>';	

	
	$businessData = $wpdb->get_row('select post_author, post_title, post_content, post_type, post_date, post_modified, post_status from '.$table_prefix.'posts where ID = '.$businessId. ' and post_parent=0');
	if(!empty($businessData)){
		
		$action = '&action=update';
		$eb_BusinessTitle = $businessData->post_title;	
		//================================================================
		//		LOGO THE IMAGE 	
		//================================================================
		if (isset($_REQUEST['logoimg'])){
			update_post_meta($businessId, 'eb_defaultLogo', $_REQUEST['logoimg']);
			echo '<div style="width:100%" align = "center"><div class="updated" align = "center"><strong>A new logo image has been set succesfully for <em>'.$eb_BusinessTitle.'</em></strong></div></div>';
		}	
		
		$eb_BusinessDateReg = $businessData->post_date;
		$eb_BusinessOwnerID = $businessData->post_author;
		$eb_BusinessType = $businessData->post_type;
		$eb_BusinessDescription = $businessData->post_content;
		$eb_BusinessLastModified = $businessData->post_modified;	
		
		$eb_BusinessEmail = get_post_meta($businessId, "eb_email");
		if(!empty($eb_BusinessEmail)) $eb_BusinessEmail = $eb_BusinessEmail[0]; else $eb_BusinessEmail ='';
		
		$eb_BusinessTel1 = get_post_meta($businessId, "eb_tel1");
		if(!empty($eb_BusinessTel1)) $eb_BusinessTel1 = $eb_BusinessTel1[0]; else $eb_BusinessTel1 ='';
		
		$eb_BusinessTel2 = get_post_meta($businessId, "eb_tel2");
		if(!empty($eb_BusinessTel2)) $eb_BusinessTel2 = $eb_BusinessTel2[0]; else $eb_BusinessTel2 ='';
		
		$eb_BusinessFax = get_post_meta($businessId, "eb_fax");
		if(!empty($eb_BusinessFax)) $eb_BusinessFax = $eb_BusinessFax[0]; else $eb_BusinessFax ='';
		
		$eb_BusinessZip = get_post_meta($businessId, "eb_zip");
		if(!empty($eb_BusinessZip)) $eb_BusinessZip = $eb_BusinessZip[0]; else $eb_BusinessZip ='';
		
		$eb_BusinessCoordinates = get_post_meta($businessId, "eb_coordinates");
		if(!empty($eb_BusinessCoordinates)) $eb_BusinessCoordinates = $eb_BusinessCoordinates[0]; else $eb_BusinessCoordinates ='';
		
		$eb_BusinessStars =  get_post_meta($businessId, "eb_stars");
		if(!empty($eb_BusinessStars)) $eb_BusinessStars = $eb_BusinessStars[0]; else $eb_BusinessStars ='';
		
		$eb_BusinessAddress =  get_post_meta($businessId, "eb_address");
		if(!empty($eb_BusinessAddress)) $eb_BusinessAddress = $eb_BusinessAddress[0]; else $eb_BusinessAddress ='';
		
		$eb_BusinessCurrency =  get_post_meta($businessId, "eb_currency");
		if(!empty($eb_BusinessCurrency)) $eb_BusinessCurrency = $eb_BusinessCurrency[0]; else $eb_BusinessCurrency ='';
		
		$eb_BusinessFacilities =  get_post_meta($businessId, "eb_facilities");
		if(!empty($eb_BusinessFacilities)) $eb_BusinessFacilities = $eb_BusinessFacilities[0]; else $eb_BusinessFacilities ='';
		
		$eb_BusinessAddressNumber =  get_post_meta($businessId, "eb_addressNum");
		if(!empty($eb_BusinessAddressNumber)) $eb_BusinessAddressNumber = $eb_BusinessAddressNumber[0]; else $eb_BusinessAddressNumber ='';
		
		$eb_BusinessCityID = get_post_meta($businessId, "eb_cityID");
		if(!empty($eb_BusinessCityID)) {
			$eb_BusinessCityID = $eb_BusinessCityID[0]; 
			global $countriesTable, $regionsTable, $citiesTable;
			$cityRes = $wpdb->get_row('select CountryID, RegionID, City from '.$citiesTable.' where CityId = '.$eb_BusinessCityID);
			$eb_BusinessRegionID = $cityRes->RegionID;
			$eb_BusinessCountryID = $cityRes->CountryID;
		}	
		else $eb_BusinessCityID ='';
		
		$eb_BusinessIBAN = get_post_meta($businessId, "eb_IBAN");
		if(!empty($eb_BusinessIBAN)) $eb_BusinessIBAN = $eb_BusinessIBAN[0]; else $eb_BusinessIBAN ='';
		
		$eb_BusinessSWIFT = get_post_meta($businessId, "eb_SWIFT");
		if(!empty($eb_BusinessSWIFT)) $eb_BusinessSWIFT = $eb_BusinessSWIFT[0]; else $eb_BusinessSWIFT ='';
		
		$eb_BusinessBankName = get_post_meta($businessId, "eb_BankName");
		if(!empty($eb_BusinessBankName)) $eb_BusinessBankName = $eb_BusinessBankName[0]; else $eb_BusinessBankName ='';
		
		$eb_BusinessBankAccount = get_post_meta($businessId, "eb_bankAccount");
		if(!empty($eb_BusinessBankAccount)) $eb_BusinessBankAccount = $eb_BusinessBankAccount[0]; else $eb_BusinessBankAccount ='';
		
		$eb_BusinessPaypalAccount = get_post_meta($businessId, "eb_paypalAccount");
		if(!empty($eb_BusinessPaypalAccount)) $eb_BusinessPaypalAccount = $eb_BusinessPaypalAccount[0]; else $eb_BusinessPaypalAccount ='';
	
		$eb_payAtReception = get_post_meta($businessId, "eb_payAtReception");
		if(!empty($eb_payAtReception)) $eb_payAtReception = $eb_payAtReception[0]; else $eb_payAtReception ='';
		
		$eb_BusinessDefaultLogo =  get_post_meta($businessId, "eb_defaultLogo");
		if(!empty($eb_BusinessDefaultLogo)) $eb_BusinessDefaultLogo = $eb_BusinessDefaultLogo[0]; else $eb_BusinessDefaultLogo ='';
				
		$eb_BusinessLogo = get_post_meta($businessId, "eb_logo");
		if(!empty($eb_BusinessLogo)) $eb_BusinessLogo = $eb_BusinessLogo[0]; else $eb_BusinessLogo ='';	
		$eb_BusinessLogo = explode("|", $eb_BusinessLogo);

		$eb_BusinessOperatingPeriodStart = get_post_meta($businessId, "eb_operatingPeriodStart");
		if(!empty($eb_BusinessOperatingPeriodStart)) $eb_BusinessOperatingPeriodStart = $eb_BusinessOperatingPeriodStart[0]; else $eb_BusinessOperatingPeriodStart ='';

		$eb_BusinessOperatingPeriodEnd = get_post_meta($businessId, "eb_operatingPeriodEnd");
		if(!empty($eb_BusinessOperatingPeriodEnd)) $eb_BusinessOperatingPeriodEnd = $eb_BusinessOperatingPeriodEnd[0]; else $eb_BusinessOperatingPeriodEnd ='';
		
		$eb_extraBedPrice = get_post_meta($businessId, "eb_extraBedPrice");
		if(!empty($eb_extraBedPrice)) $eb_extraBedPrice = $eb_extraBedPrice[0]; else $eb_extraBedPrice ='';

		$eb_BusinessHasSeasons = get_post_meta($businessId, "eb_hasSeasons");
		if(!empty($eb_BusinessHasSeasons)) $eb_BusinessHasSeasons = $eb_BusinessHasSeasons[0]; else $eb_BusinessHasSeasons ='';
		if($eb_BusinessHasSeasons == "YES"){
			$eb_getseason = get_post_meta($businessId, "eb_lowSeason");
			if(!empty($eb_getseason)) $eb_getseason = $eb_getseason[0]; else $eb_getseason ='';
			$eb_getseason = explode("[-]", $eb_getseason);	
			$eb_BusinessLowSeasonStart = $eb_getseason[0];
			if(isset($eb_getseason[1]))
			$eb_BusinessLowSeasonEnd =$eb_getseason[1];
			else $eb_BusinessLowSeasonEnd = '';
			
			$eb_getseason = get_post_meta($businessId, "eb_midSeason");
			if(!empty($eb_getseason)) $eb_getseason = $eb_getseason[0]; else $eb_getseason ='';
			$eb_getseason = explode("[-]", $eb_getseason);	
			$eb_BusinessMidSeasonStart = $eb_getseason[0];
			if(isset($eb_getseason[1]))
			$eb_BusinessMidSeasonEnd =$eb_getseason[1];
			else $eb_BusinessMidSeasonEnd = '';
			
			$eb_getseason = get_post_meta($businessId, "eb_highSeason");
			if(!empty($eb_getseason)) $eb_getseason = $eb_getseason[0]; else $eb_getseason ='';
			$eb_getseason = explode("[-]", $eb_getseason);	
			$eb_BusinessHighSeasonStart = $eb_getseason[0];
			if(isset($eb_getseason[1]))
			$eb_BusinessHighSeasonEnd =$eb_getseason[1];
			else $eb_BusinessHighSeasonEnd = '';
			
			$eb_getseason = get_post_meta($businessId, "eb_lowSeason2");
			if(!empty($eb_getseason)) $eb_getseason = $eb_getseason[0]; else $eb_getseason ='';
			$eb_getseason = explode("[-]", $eb_getseason);	
			$eb_BusinessLowSeasonStart2 = $eb_getseason[0];
			if(isset($eb_getseason[1]))
			$eb_BusinessLowSeasonEnd2 =$eb_getseason[1];
			else $eb_BusinessLowSeasonEnd2 = '';
			
			$eb_getseason = get_post_meta($businessId, "eb_midSeason2");
			if(!empty($eb_getseason)) $eb_getseason = $eb_getseason[0]; else $eb_getseason ='';
			$eb_getseason = explode("[-]", $eb_getseason);	
			$eb_BusinessMidSeasonStart2 = $eb_getseason[0];
			if(isset($eb_getseason[1]))
			$eb_BusinessMidSeasonEnd2 =$eb_getseason[1];
			else $eb_BusinessMidSeasonEnd2 = '';
		}
		
		$eb_getseason = get_post_meta($businessId, "eb_checkInTime");
		if(!empty($eb_getseason)) $eb_getseason = $eb_getseason[0]; else $eb_getseason ='';
		$eb_getseason = explode("[-]", $eb_getseason);
		if(!empty($eb_getseason)){	
		$eb_BusinessCheckInFrom = $eb_getseason[0];
		if(isset($eb_getseason[1]))
			$eb_BusinessCheckInTo =$eb_getseason[1];
			else $eb_BusinessCheckInTo = '';
		}
		else $eb_getseason = '';
		
		$eb_getseason = get_post_meta($businessId, "eb_checkOutTime");
		if(!empty($eb_getseason)) $eb_getseason = $eb_getseason[0]; else $eb_getseason ='';
		$eb_getseason = explode("[-]", $eb_getseason);	
		$eb_BusinessCheckOutFrom = $eb_getseason[0];
		if(isset($eb_getseason[1]))
			$eb_BusinessCheckOutTo =$eb_getseason[1];
		else $eb_BusinessCheckOutTo = '';
		
		$eb_lateCheckoutTime = get_post_meta($businessId, "eb_lateCheckoutTime");
		if( !empty($eb_lateCheckoutTime) ) $eb_lateCheckoutTime = $eb_lateCheckoutTime[0]; else $eb_lateCheckoutTime = '';
		
		$eb_lateCheckoutReadyTime = get_post_meta($businessId, "eb_lateCheckoutReadyTime");
		if( !empty($eb_lateCheckoutReadyTime) ) $eb_lateCheckoutReadyTime = $eb_lateCheckoutReadyTime[0]; else $eb_lateCheckoutReadyTime = '';

		$eb_cancellationStr = get_post_meta($businessId, "eb_cancellationCharge");		
		
		$eb_earlyCancellationStr = get_post_meta($businessId, "eb_earlyCancellationCharge");		
		
		$eb_freeCancellationStr = get_post_meta($businessId, "eb_freeCancellationCharge");
		
		$eb_editBookingAllowedDatePeriod = get_post_meta($businessId, "eb_editBookingAllowedDatePeriod");
		if( !empty($eb_editBookingAllowedDatePeriod) ) $eb_editBookingAllowedDatePeriod = $eb_editBookingAllowedDatePeriod[0]; else $eb_editBookingAllowedDatePeriod = 0;
		
		$eb_editBookingDates = get_post_meta($businessId, "eb_editBookingDates");
		if( !empty($eb_editBookingDates) ) $eb_editBookingDates = $eb_editBookingDates[0]; else $eb_editBookingDates = "NO";
		
		$eb_editBookingAddRooms = get_post_meta($businessId, "eb_editBookingAddRooms");
		if( !empty($eb_editBookingAddRooms) ) $eb_editBookingAddRooms = $eb_editBookingAddRooms[0]; else $eb_editBookingAddRooms = "NO";
		
		$eb_editBookingRemoveRooms = get_post_meta($businessId, "eb_editBookingRemoveRooms");
		if( !empty($eb_editBookingRemoveRooms) ) $eb_editBookingRemoveRooms = $eb_editBookingRemoveRooms[0]; else $eb_editBookingRemoveRooms = "NO";
		
		$eb_editBookingRemoveRoomsCost = get_post_meta($businessId, "eb_editBookingRemoveRoomsCost");
		if( !empty($eb_editBookingRemoveRoomsCost) ) $eb_editBookingRemoveRoomsCost = $eb_editBookingRemoveRoomsCost[0]; else $eb_editBookingRemoveRoomsCost = "";
		
		$eb_editBookingRemoveDaysCost = get_post_meta($businessId, "eb_editBookingRemoveDaysCost");
		if( !empty($eb_editBookingRemoveDaysCost) ) $eb_editBookingRemoveDaysCost = $eb_editBookingRemoveDaysCost[0]; else $eb_editBookingRemoveDaysCost = "";
		
		if($businessData->post_status == "publish"){
			$eb_BusinessStatus = "Unpublish";
			$eb_BusinessSetStatus = "draft";
		}
		else{
			$eb_BusinessStatus = "Publish";
			$eb_BusinessSetStatus = "publish";	
			if($user_info->user_level == 0){
				echo '<div class="error" align="center" style="font-size:12px;padding-bottom:5px;"><b>This Business has been deactivated.</b> <br />This means that your rooms will not show up in a user\'s search. <br />';
				if(!isset($_REQUEST['notifyadmin']))
				echo 'If you want this business to be activated please <a href="admin.php?page=business_list&bID='.$businessId.'&notifyadmin=activatebusiness" class="littleEditBtns" style="font-size:12px;">notify administrator</a><br />';
				else echo 'Your administrator has been notified. Please wait for response';
				echo '</div>';
			}
		}
		$eb_BusinessPackDeal = get_post_meta($businessId, "eb_packDeal");
		if(!empty($eb_BusinessPackDeal)) $eb_BusinessPackDeal = $eb_BusinessPackDeal[0]; else $eb_BusinessPackDeal ='';
		$eb_BusinessPackDealTitle = $wpdb->get_row('select post_title from '.$table_prefix.'posts where ID = '.$eb_BusinessPackDeal);
		if(!empty($eb_BusinessPackDealTitle)) $eb_BusinessPackDealTitle = $eb_BusinessPackDealTitle->post_title;
		else $eb_BusinessPackDealTitle = '';
		
		$eb_ServicesDetailsStr= '';
		$businessService = '';
		$businessServicePlural = '';	
		if( strtolower( $eb_BusinessType ) == "hotel"){
			$businessService = 'Room type';
			$businessServicePlural = 'Room types';		
		}
		if( strtolower( $eb_BusinessType ) == "apartments") {
			$businessService = 'Room';
			$businessServicePlural = 'Rooms';		
		}
		if( strtolower( $eb_BusinessType ) == "car rental") {
			$businessService = 'Vehicle';
			$businessServicePlural = 'Vehicles';		
		}
		if(strtolower( $eb_BusinessType ) == "shipping cruises") {
			$businessService = 'Shipping cruise';
			$businessServicePlural = 'Shipping cruises';		
		} 
		
		//====IF NO PAYMENT UNPUBLISH BUSINESS====
		$noPaymentMethod = false;		
		if( ($eb_BusinessIBAN == '' || $eb_BusinessBankAccount == '' || $eb_BusinessBankName == '') && $eb_BusinessPaypalAccount == '' ) $noPaymentMethod = true;

		if( $noPaymentMethod && $businessData->post_status == "publish" ){
			$eb_SetBusinessStatus = array();
  			$eb_SetBusinessStatus['ID'] = $businessId;
  			$eb_SetBusinessStatus['post_status'] = 'draft';
  			if(wp_update_post( $eb_SetBusinessStatus )){
  				echo '<div class="updated">This business has been automatically unpublished because there were no payment methods set.</div>';
  				$businessStatusData = $wpdb->get_row('select post_author, post_title from '.$table_prefix.'posts where ID = '.$businessId. ' and post_parent=0');
				if(!empty($businessStatusData)){
						$ownerData = get_userdata( $businessStatusData->post_author );
						$eb_BusinessOwner = $ownerData->last_name.' '. $ownerData->first_name;
						$statusInformationMailSub = $businessStatusData->post_title.' is not Active any more for bookings at '.get_bloginfo('name');
						$statusInformationMailMsg = 'Hello Mr/Mrs '.$eb_BusinessOwner.',<br />';
	  					$statusInformationMailMsg .= '<br /><b>'.$businessStatusData->post_title.'</b> is not available for bookings any more. This is because of payment methods missing. <br />To fix this problem please enter at your <a href ="'.get_admin_url().'admin.php?page=business_list&bID='.$businessId.'">administration panel</a> and add at least one payment method<br />';
	  					$statusInformationMailMsg .= '<br /><br />For any questions or instructions please contact us at '.get_bloginfo('admin_email');
	  					add_filter('wp_mail_content_type',create_function('', 'return "text/html"; '));
				   	add_filter('wp_mail_from', create_function('', 'return "'.get_bloginfo('admin_email').'"; '));
						add_filter('wp_mail_from_name', create_function('', 'return "'.get_bloginfo('name').'"; '));
						wp_mail($ownerData->user_email, $statusInformationMailSub, $statusInformationMailMsg);
						$refreshUrl = get_admin_url().'admin.php?page='.$_REQUEST['page'].'&bID='.$businessId;
						?>
						<script type="text/javascript" >
						var refreshUrl = "<?php echo $refreshUrl;?>&unpub_reason=p_m";
						document.location = refreshUrl;
						</script>
						<?php
				}
				
  			}
		}
		

		//=============================================================
		//===============NOTIFY ADMIN TO PUBLISH BUSINESS==============
		if(isset($_REQUEST['notifyadmin']) && $_REQUEST['notifyadmin'] == "activatebusiness"){
			add_filter('wp_mail_content_type',create_function('', 'return "text/html"; '));
			add_filter('wp_mail_from', create_function('', 'return "'.get_bloginfo('admin_email').'"; '));
			add_filter('wp_mail_from_name', create_function('', 'return "'.get_bloginfo('name').'"; '));
			//$ownerData = get_userdata( $eb_BusinessOwnerID );
			//$eb_BusinessOwner = $ownerData->last_name.' '. $ownerData->first_name;
			//echo 'ownerData->user_email: '.$ownerData->user_email;
			$activationMsg = 'This is a notification message from a business owner at <b>'.get_bloginfo('name').'</b><br /><br />';
			$activationMsg .= 'It seems like business <b>'.$eb_BusinessTitle.'</b> needs to be activated.<br />
			Please check first for any previous balance or debt.<br /><br />
			<a href="'.get_admin_url().'admin.php?page=busines_menu&bID='.$businessId.'">Follow this link to activate <b>'.$eb_BusinessTitle.'</b></a>';
			wp_mail(get_bloginfo('admin_email'), 'Business activation notification', $activationMsg);	
		}
		//=============================================================
		//=====================PACKAGE DEAL CHANGE=====================

if(isset($_REQUEST['pack']) && $_REQUEST['pack'] != ''){
	$bID = addslashes($_REQUEST['bID']);
	$pack = $_REQUEST['pack'];
	update_post_meta($bID,'eb_packDeal', $pack);	
}
		
		//=============================================================
		//==================PAYMENT CONFIRMATION MADE==================

if(isset($_REQUEST['pay']) && $_REQUEST['pay'] != ''){
	$bID = addslashes($_REQUEST['bID']);
	$ammount = addslashes($_REQUEST['ammount']);
	$prevPayments = get_post_meta($bID, "eb_businessPaymentHistory");
	if (is_numeric($ammount) && $ammount != '' && $ammount > 0){
		update_post_meta($bID,'eb_businessPaymentHistory',$prevPayments[0].'|'. date("Y-m-d H:i:s").'<:>'.$ammount);
		$paymentEmailConfirmationToBusinessOwnerSub = get_bloginfo('name').' - Payment Confirmation';
		$paymentEmailConfirmationToBusinessOwnerMsg = 'Thank you for settling your balance at <b>'.get_bloginfo('name').'</b>!';
		$paymentEmailConfirmationToBusinessOwnerMsg .= '<br />We successfully received the amount of <b>'.$ammount.' '.get_option('eb_siteCurrency').'</b> in behalf of your business titled: <font style="font-size:14px"><b><i>'.$eb_BusinessTitle.'</i></b></font>, member of <b>'.get_bloginfo('name').'</b>.';
		$paymentEmailConfirmationToBusinessOwnerMsg .= '<br /><a href="'.get_admin_url().'admin.php?page='.$editBusinessLink.'&bID='.$businessId.'&bill=show">Please follow this link to check out your new billing status of <b>'.$eb_BusinessTitle.'</b></a>';
		
		add_filter('wp_mail_content_type',create_function('', 'return "text/html"; '));
   	add_filter('wp_mail_from', create_function('', 'return "'.get_bloginfo('admin_email').'"; '));
		add_filter('wp_mail_from_name', create_function('', 'return "'.get_bloginfo('name').'"; '));
	
		wp_mail($eb_BusinessEmail, $paymentEmailConfirmationToBusinessOwnerSub, $paymentEmailConfirmationToBusinessOwnerMsg);		
	
	?>
	<script type="text/javascript" >
		var nUrl = "admin.php?page=<?php echo $_REQUEST['page']?>&bID=<?php echo $_REQUEST['bID']?>&bill=show";
		parent.document.location.href = nUrl;	
	</script>
	<?php
	}
	else echo '<div class="error">Please check the amount, of the payment, is not empty and is a number greater than 0.</div>';
}

if(isset($_REQUEST['cnfBusPmnt']) && $_REQUEST['cnfBusPmnt'] == 'true'){
		$bankNamePayedTo = addslashes( $_REQUEST['bankNameUsed']);
		$bankAmmountPayed = addslashes( $_REQUEST['amountPayedAtBank']);
		$bankJustificationCode = addslashes( $_REQUEST['jCodePayedAtBank']);
		if($bankNamePayedTo != '' && is_numeric($bankAmmountPayed) && $bankAmmountPayed > 0){
		$paymentEmailVerificationFromBusinessOwnerMsg = 'Business titled <b>'.$eb_BusinessTitle.'</b> verifies that there has been a payment by <b>Bank</b> to settle its balance<br />';
		$paymentEmailVerificationFromBusinessOwnerMsg .= '<table style="border:1px solid #ccc">
			<tr>
				<td colspan="2" style="background-color:#0055d8;color:#fff;font-size:16px"><b>Business data</b></td>
			</tr>
			<tr>
				<td>Business ID: </td>
				<td><b>'.$_REQUEST['bID'].'</b></td>
			</tr>
			<tr>
				<td>Business Title: </td>
				<td><b>'.$eb_BusinessTitle.'</b></td>
			</tr>
		</table>';
		$paymentEmailVerificationFromBusinessOwnerMsg .= '<br /><table style="border:1px solid #ccc">
			<tr>
				<td colspan="2" style="background-color:#0055d8;color:#fff;font-size:16px"><b>Payment data</b></td>
			</tr>
			<tr>
				<td>Bank Name: </td>
				<td><b>'.$bankNamePayedTo.'</b></td>
			</tr>
			<tr>
				<td>Payment amount: </td>
				<td><b>'.$bankAmmountPayed.' '.get_option('eb_siteCurrency').'</b></td>
			</tr>
			';
		if($bankJustificationCode != ''){
			$paymentEmailVerificationFromBusinessOwnerMsg .= '<tr>
				<td>Payment justification: </td>
				<td><b>'.$bankJustificationCode.'</b></td>
			</tr>
			';
		}
		$paymentEmailVerificationFromBusinessOwnerMsg .= '</table>';
		$paymentEmailVerificationFromBusinessOwnerMsg .= '<br /><br />Please check your bank report for this transaction.<br /><i>Keep in mind that bank transactions may take up to 3-4 business days.</i>';
		$paymentEmailVerificationFromBusinessOwnerMsg .= '<br /><br /><a href="'.get_admin_url().'admin.php?page='.$editBusinessLink.'&bID='.$businessId.'&bill=show">After checking your bank report you have to confirm this payment at the billing history of <b>'.$eb_BusinessTitle.'</b></a>';
		
		add_filter('wp_mail_content_type',create_function('', 'return "text/html"; '));
   	add_filter('wp_mail_from', create_function('', 'return "'.get_bloginfo('admin_email').'"; '));
		add_filter('wp_mail_from_name', create_function('', 'return "'.$eb_BusinessTitle.' "; '));
	
		wp_mail(get_bloginfo('admin_email'), "Payment Verification from ".$eb_BusinessTitle, $paymentEmailVerificationFromBusinessOwnerMsg);
		?>
	<script type="text/javascript" >
		var nUrl = "admin.php?page=<?php echo $_REQUEST['page']?>&bID=<?php echo $_REQUEST['bID']?>&bill=show";
		parent.document.location.href = nUrl;	
	</script>
	<?php
	}
	else echo '<div class="error"><font style="font-size:14px">Please make sure that</font> <br /><b>The Bank name is not empty<br>The amount you entered is not empty and greater than 0</b></div>';
}//=========================================
if(isset($_REQUEST['pprsp']) && $_REQUEST['pprsp'] == 'sxs'){
	$payedAmountToPP = addslashes($_REQUEST["ppamount"]);
	if(is_numeric($payedAmountToPP) && $payedAmountToPP > 0){
		$paymentEmailVerificationFromBusinessOwnerMsg = 'Business titled <b>'.$eb_BusinessTitle.'</b> verifies that there has been a payment by <b>PayPal</b> to settle its balance<br />';
		$paymentEmailVerificationFromBusinessOwnerMsg .= '<table style="border:1px solid #ccc">
			<tr>
				<td colspan="2" style="background-color:#0055d8;color:#fff;font-size:16px"><b>Business data</b></td>
			</tr>
			<tr>
				<td>Business ID: </td>
				<td><b>'.$_REQUEST['bID'].'</b></td>
			</tr>
			<tr>
				<td>Business Title: </td>
				<td><b>'.$eb_BusinessTitle.'</b></td>
			</tr>
		</table>';
		$paymentEmailVerificationFromBusinessOwnerMsg .= '<br /><table style="border:1px solid #ccc">
			<tr>
				<td colspan="2" style="background-color:#0055d8;color:#fff;font-size:16px"><b>Payment data</b></td>
			</tr>
			<tr>
				<td>Paypal account: </td>
				<td><b>'.get_bloginfo('admin_email').'</b></td>
			</tr>
			<tr>
				<td>Payment amount: </td>
				<td><b>'.$payedAmountToPP.' '.get_option('eb_siteCurrency').'</b></td>
			</tr>
			';		

		$paymentEmailVerificationFromBusinessOwnerMsg .= '</table>';
		$paymentEmailVerificationFromBusinessOwnerMsg .= '<br /><br />Please check your Paypal report for this transaction.<br /><i>Keep in mind that Paypal transactions may take up to 3-4 business days.</i>';
		$paymentEmailVerificationFromBusinessOwnerMsg .= '<br /><br /><a href="'.get_admin_url().'admin.php?page='.$editBusinessLink.'&bID='.$businessId.'&bill=show">After checking your Paypal report you have to confirm this payment at the billing history of <b>'.$eb_BusinessTitle.'</b></a>';
		
		$paymentEmailVerificationToBusinessOwnerMsg = 'Thank you for settling your balance at <b>'.get_bloginfo('name').'</b><br />';
		$paymentEmailVerificationToBusinessOwnerMsg .= 'Once we verify the payment through our Paypal report we will confirm your payment.<br />';
		$paymentEmailVerificationToBusinessOwnerMsg .= '<br /><br /><a href="'.get_admin_url().'admin.php?page='.$editBusinessLink.'&bID='.$businessId.'&bill=show">Click here to see the billing history for your business titled "<b>'.$eb_BusinessTitle.'</b>".</a>';
		$paymentEmailVerificationToBusinessOwnerMsg .= '<br /><br />If your billing history is not updated after 4 business days please contact your system administrator at '.get_bloginfo('admin_email');
		
		add_filter('wp_mail_content_type',create_function('', 'return "text/html"; '));
   	add_filter('wp_mail_from', create_function('', 'return "'.get_bloginfo('admin_email').'"; '));
		add_filter('wp_mail_from_name', create_function('', 'return "'.$eb_BusinessTitle.' "; '));
	
		wp_mail(get_bloginfo('admin_email'), "PayPal payment Verification from ".$eb_BusinessTitle, $paymentEmailVerificationFromBusinessOwnerMsg);
		wp_mail($eb_BusinessEmail, "PayPal payment Verification from ".$eb_BusinessTitle, $paymentEmailVerificationToBusinessOwnerMsg);
		?>
	<script type="text/javascript" >
		var nUrl = "admin.php?page=<?php echo $_REQUEST['page']?>&bID=<?php echo $_REQUEST['bID']?>&bill=show";
		parent.document.location.href = nUrl;	
	</script>
	<?php
	}
	else echo '<div class="error"><font style="font-size:14px">This transaction seems not to be confirmed by Paypal.<br />Please check your Paypal reports and inform your system administrator about this event at '.get_bloginfo('admin_email').'</div>';

}
		//=============================================================
		//=============================================================
		
		$serviceStr = '';
			$service_count = $wpdb->get_var("SELECT COUNT(*) from ".$table_prefix."posts where post_type='rooms' and post_parent =". $businessId);
			if(!empty($service_count)){
				$scount = $service_count;
				$serviceStr = '<i>('.$scount.' '.$businessServicePlural.')</i>
					   &nbsp;|&nbsp;<a class="littleEditBtns" style="font-size:10px;" href="'.$eb_adminUrl.'?page='.$targetPage.'&type=Hotel&bID='.$businessId.'" title = "Add a '.$businessService.' to '.$businessData->post_title.'">Add a '.$businessService.'</a>  <a class="littleEditBtns" style="font-size:10px;" href="'.$eb_adminUrl.'?page='.$targetPage.'&type=Hotel&action=view&bID='.$businessId.'" title = "View all '.$businessServicePlural.' of '.$businessData->post_title.'">View '.$businessServicePlural.'</a>';

			}else{
				$serviceStr = '<i>(There are no '.$businessServicePlural.'</i>)
				 <a class="littleEditBtns" style="font-size:10px;" href="'.$eb_adminUrl.'?page='.$targetPage.'&type=Hotel&bID='.$businessId.'" title = "Add a '.$businessService.' to '.$businessData->post_title.'">Add a '.$businessService.'</a>';			
			}
			$bBookings = $wpdb->get_var("SELECT COUNT(bookingID) from eb_bookingdata where businessID =". $businessId);
			$bBookingsPending = $wpdb->get_var("SELECT COUNT(bookingID) from eb_bookingdata where businessID =". $businessId.' and booking_status = "Pending"');
			
			if(!empty($bBookings)){
				$serviceStr .= '&nbsp;|&nbsp; <a class="littleEditBtns" href="'.$eb_adminUrl.'?page=bookings_menu&bID='.$businessId.'" title = "View booking details "><font style="font-size:12px;"><b>'.$bBookings.'</b></font> bookings</a>';
			}
			if(!empty($bBookingsPending)){
				$serviceStr .= '&nbsp; <a class="littleEditBtns" href="'.$eb_adminUrl.'?page=bookings_menu&bID='.$businessId.'&stat=p" title = "View Pending bookings" style="color:#6fbf4d;font-size:10px"><b><font style="font-size:14px">'.$bBookingsPending.'</font> bookings PENDING</b></a>';
			}
		
		$eb_BusinessFormTitle = 'Make your changes for <em style="font-size:18px">'.$eb_BusinessTitle.'</em>&nbsp;&nbsp;'.$serviceStr;
	}

echo '<em style="color:#999"><strong>'.$eb_BusinessTitle.'</strong> was last modified at <font style="color:#666">'.$eb_BusinessLastModified.'</font></em>';
	if($user_info->user_level == 10){
		?>
		<table style="border:none"><tr>
		<td style="border:none">
		<?php
			echo '<form name="updateBusinessStatusFrm" method="post" action="admin.php?page='.$_REQUEST['page'].'&bID='.$businessId.'&statusAction=updateBusinessStatus&setStatus='.$eb_BusinessSetStatus.'"><input type="submit" value="'.$eb_BusinessStatus.'"></form>';	
		?>
		</td>
		<td style="border:none"><span style="padding:5px;color:#ccc">|</span>
			<label>Package Deal: </label>
			<select id="selPackageDeal" onchange="setPackageDeal()">
			<?php
				$allDeals = $wpdb->get_results('select ID, post_title from '.$table_prefix.'posts where post_type = "eb_chargingDeal"');
				foreach($allDeals as $deal){			
					if($editDealName == $deal->post_title && $did != $deal->ID)
						$dealNameExists = true;
					echo '<option value="'.$deal->ID.'" ';
					if($eb_BusinessPackDeal == $deal->ID){
						echo ' selected';
						
					}
					echo '>'.$deal->post_title.'</option>';
				}
			?>
	</select>
	</td>
	</tr>
	</table>
	<?php
	}

}

?>
<?php if(isset($_REQUEST['bID']) || $businessId != ''){?>
							<br>
							 <br>
							<div style="border:1px solid #dfdfdf;padding:10px;background-color:#f8f8f8;-webkit-border-radius: 5px;-moz-border-radius: 5px;border-radius: 5px;width:96.7%;margin-left:3px;">
                            <a name="imagesList"></a>
                            <div class="imgHolderTitleDiv"><strong><?php echo $eb_BusinessTitle;?> images list</strong> <a href="#imagesList" class="littleEditBtns" onclick="showHideImageArea()"><span id="showHideImgLinkTitle">Show</span> <?php echo $eb_BusinessTitle;?> images</a></div>
                            <div style="line-height:5px;">&nbsp;</div>
                            <div id="b_logoHolder" class="imageAreaDiv" style="border:1px solid #dfdfdf;padding:2px;margin-top:5px;background-color:#f8f8f8;-webkit-border-radius: 5px;-moz-border-radius: 5px;border-radius: 5px;width:98.3%;margin-left:1px;">
                            <?php if($eb_BusinessDefaultLogo!=''){?>
                            <div class="imgHolderTitleDiv"><strong>Business logo</strong></div>
                            <div style="line-height:5px;">&nbsp;</div>
                            	<span>
										<span style="position:absolute; padding-top:5px; padding-left:128px;"> 
											<a onclick="deleteIamges('<?php echo $eb_BusinessDefaultLogo;?>')"  class="littleDelBtnsTrans" title="Delete your business logo <?php echo $eb_BusinessDefaultLogo;?>. Please replace it later on...">X</a>
										</span>
										<a target="_blank" href="<?php echo WP_CONTENT_URL?>/plugins/<?php echo $ebPluginFolderName?>/images/businessImg/<?php echo $eb_BusinessDefaultLogo?>" title="Click to view fool size of your business logo <?php echo $eb_BusinessDefaultLogo;?>"><img id="eb_defaultImg" width="150" src="<?php echo WP_CONTENT_URL?>/plugins/<?php echo $ebPluginFolderName?>/images/businessImg/thumbs/<?php echo $eb_BusinessDefaultLogo?>" ></a>
									</span>
                            <?php }
                            else {
                            ?>
                            	<div class="error" style="color:#666"><strong>You have not set a default image logo for your business yet.</strong><br />After uploading your images for your business, you can set one of them as your business logo by pressing the "logo it" button which appears on every image.<br /><em>You can change it in the future with the same way.</em></div>
                            <?php }?>
                            </div>
                            <div id="b_imgHolder" class="imageAreaDiv" style="border:1px solid #dfdfdf;padding:2px;margin-top:5px;background-color:#f8f8f8;-webkit-border-radius: 5px;-moz-border-radius: 5px;border-radius: 5px;width:98.3%;margin-left:1px;">
                            <div class="imgHolderTitleDiv"><strong>Business images list</strong></div>
                            <div style="line-height:5px;">&nbsp;</div>
							<?php 
							if(!empty($eb_BusinessLogo)){
								for($i=0; $i < count($eb_BusinessLogo); $i++){
									 if ($eb_BusinessLogo[$i]!="" && $eb_BusinessLogo[$i] != $eb_BusinessDefaultLogo){
									?>							
									<span>
										<span style="position:absolute; padding-top:5px; padding-left:78px;"> 
											<a onclick="deleteIamges('<?php echo $eb_BusinessLogo[$i];?>')"  class="littleDelBtnsTrans" title="Delete <?php echo $eb_BusinessLogo[$i];?> image">X</a>
										</span>
										<span style="position:absolute; padding-top:5px; padding-left:28px;">
											<a onclick="logoIt('<?php echo $eb_BusinessLogo[$i];?>')" class="littleDelBtnsTrans" title="Make this your default logo image"><strong>Logo it</strong></a> 
										</span>
										<a target="_blank" href="<?php echo WP_CONTENT_URL?>/plugins/<?php echo $ebPluginFolderName?>/images/businessImg/<?php echo $eb_BusinessLogo[$i]?>" title="Click to view fool size of image <?php echo $eb_BusinessLogo[$i];?>"><img id="eb_img" width="100" src="<?php echo WP_CONTENT_URL?>/plugins/<?php echo $ebPluginFolderName?>/images/businessImg/thumbs/<?php echo $eb_BusinessLogo[$i]?>" ></a>
									</span>&nbsp;&nbsp;
									
									<?php 
									}
								}
							} ?>
                                </div>
								<span>
								<div id="b_imgUploadArea" class="imageAreaDiv" style="border:1px solid #dfdfdf;padding:2px;margin-top:5px;background-color:#f8f8f8;-webkit-border-radius: 5px;-moz-border-radius: 5px;border-radius: 5px;width:98.3%;margin-left:1px;">
                         <div class="imgHolderTitleDiv"><strong>Upload new images</strong></div>
								<div id="logoUploadArea">
								<p id="result"></p>
								<p id="f1_upload_process" style="display:none">Loading...<br/><img src="<?php echo WP_CONTENT_URL;?>/plugins/wp-easybooking/images/ajax-loader.gif" /></p>
								<form action="<?php echo WP_CONTENT_URL.'/plugins/'.$ebPluginFolderName; ?>/uploadLogo.php?target=businessLogo&bID=<?php echo $businessId ?>" method="post" enctype="multipart/form-data" target="upload_target" onsubmit="startUpload();" >
								
    								<strong>Image</strong> <input name="Filedata" type="file" id="fileUploadArea" />
          					<input type="submit" name="submitBtn" value="Upload image" />
								</form>
 								
								<iframe id="upload_target" name="upload_target" src="#" style="width:0;height:0;border:0px solid #fff;"></iframe>
								</div>
								</div>
								</div>
							<?php } ?>
<form name="ebData_form" id="ebData_form" method="post" action="admin.php?page=<?php echo $editBusinessLink; if($businessId!='') echo '&bID='.$businessId; echo $action;?>">
	
	<table width="100%" style="width:100%" class="eb_class">
		<tr valign="top">
			<td width="70%" style="width:70%" valign="top">
				
				
				<table class="widefat" style="width:99%">
					<thead>
					<tr>
					<th><span><?php echo $eb_BusinessFormTitle; ?></span></th>
					</tr>
					</thead>
					<?php include('businessCreator_mainData.php'); 
					//if($eb_BusinessCityID !=''){					
					?>
					<script type="text/javascript" >
					jQuery(document).ready(function(){
						jQuery('#eb_countries_select').val(<?php echo $eb_BusinessCountryID;?>);
						jQuery("#sidemenu").show();
						<?php if(isset($_REQUEST['bill']) && $_REQUEST['bill'] == "show") {?>
						showInfo('debtInfoDiv');
						<?php } ?>
						
					
					});
			</script>
			<?php //	}?>
			<script type="text/javascript" >
				jQuery(document).ready(function(){
					fetchCountrysRegions(<?php echo $eb_BusinessRegionID. ', '.$eb_BusinessCityID;?>);
				});
				
			</script>
					</table>
					
			</td>
		</tr>
		<!--<tr>
			<td>
			
			</td>
			<td>
			
			</td>
		</tr>-->
	</table>
	<p class="submit">
		<input type="hidden" name="curTime" id="curTime" value="" />
		<input type="hidden" name="ebOwnerID" id="ebOwnerID" value="<?php echo $eb_BusinessOwnerID; ?>" />
		<input type="submit" name="Submit" value="<?php _e('Save') ?>" />
	</p>
</form> 
<?php
if($eb_BusinessOwnerID != ''){
	$ownerData = get_userdata( $eb_BusinessOwnerID );
	$eb_BusinessOwner = $ownerData->last_name.' '. $ownerData->first_name;
}
?>
			<form id="paypalFrm" action="https://www.paypal.com/cgi-bin/webscr" method="post">
				<input type="hidden" name="cmd" value="_ext-enter">
				<input type="hidden" name="redirect_cmd" value="_xclick">

				<input type="hidden" name="first_name" value="<?php echo $ownerData->first_name; ?>">
				<input type="hidden" name="last_name" value="<?php echo $ownerData->last_name; ?>">
								
				<input type="hidden" name="business" value="<?php echo get_option('eb_sitePPacount'); ?>">
				<input type="hidden" name="item_name" value="<?php echo get_bloginfo('name').' - '.$eb_BusinessTitle ;?> Balance Payment (Business owner ID: <?php echo $eb_BusinessOwnerID; ?>)">

				<input type="hidden" name="currency_code" value="<?php echo get_option('eb_siteCurrency');?>">
				<input type="hidden" name="amount" id="paypalFrm_ammount" value="0.10">
				<input type="hidden" name="shipping" value="0">
				<input type="hidden" name="return" id="paypalFrm_returnUrl" value="<?php echo site_url();?>/wp-admin/admin.php?page=<?php echo $editBusinessLink; ?>&bID=<?php echo $businessId;?>&bill=show&pprsp=sxs">
								
			</form>
			<div style="display:none;" id="bankDetailAreaStored">
				<a class="littleCloseBtns" style="font-size:12px;" onclick="javascript:jQuery('#bankDetailArea').hide()">X</a>
				<br />
				<span style="color:#f19c06;">Use the following information to pay your balance by bank.
				<br />There might be more than one bank accounts available in the following list. Choose the one which suits you best (in your country, close to your neighbourhood ...)
				<br />The justification code is a series of numbers which include your business ID and date followed by the name of your business. It will be very helpful if you use this code in your transaction.
				</span>
				<p style="font-size:14px;color:#333;padding:20px;">				
				<b>
				<font style="font-size:16px">Bank account details so you can pay your balance</font>
				<textarea readonly="readonly" style="border:none;resize:none;padding-left:100px;background-color:#fff;color:#707070;" cols="8" rows="8"><?php echo get_option('eb_siteBankName');?></textarea>
				<div style="color:#333;width:100%" align="center"> 
					If possible use this code &nbsp; <span style="border:1px solid #666;background-color:#fefefe;color:#666;font-size:14px;">&nbsp;
					<?php
					echo $businessId.'-'.date("d").date("m").date("Y").' '.$eb_BusinessTitle;
					?>
					</span> &nbsp; as justification (reason) for payment
				</div>
				</b>
				<hr style="width:80%;color:#ccc;bakcground-color:#ccc;border:1px solid #ccc;">
				<span style="color:#f19c06;">After paying your balance at the bank you have to inform <b> <?php echo get_bloginfo('name'); ?> </b> about your payment.
				<br />As soon as the recipient <i>(<?php echo get_bloginfo('name'); ?>)</i> notices the payment through his bank report, he will confirm it, and you will be able to see it from your business billing history.
				<br />The date of payment will be the date the recipient confirms it. <em>Please keep in mind that bank transactions may take up to 3-4 business days</em></span>
				<br />
				<div style="color:#333;width:100%" align="center">

						Since you have completed a payment you can send a notification email to inform<b> <?php echo get_bloginfo('name'); ?> </b>about your action.
						<br />Please enter the name of which bank of the list above you transferred the amount to:  <input id="bankNameUsed" type="text" style="width:180px" />
						<br />Please enter the amount you payed at the bank: <input id="amountPayedAtBank" type="text" style="width:100px" align="right" /> <?php echo get_option('eb_siteCurrency'); ?>
						<br />If you used a justification code, please enter it here: <input id="jCodePayedAtBank" type="text" style="width:250px" />
						<br /><br /><a class="littleEditBtns" style="width:180px;color:green;font-weight:bold;font-size:12px;" onclick="sendBusPmntVerif();">Send payment notification</a>

				</div>
				
				</p>
			</div>
</div>
</div>


<script type="text/javascript" >
function sendBusPmntVerif(){
	document.location="admin.php?page=<?php echo $editBusinessLink; ?>&bID=<?php echo $businessId; ?>&bill=show&cnfBusPmnt=true&bankNameUsed="+jQuery("#bankNameUsed").val()+"&amountPayedAtBank="+jQuery("#amountPayedAtBank").val()+"&jCodePayedAtBank="+jQuery("#jCodePayedAtBank").val();
}
var currentTime = new Date();
var month = currentTime.getMonth() + 1;
var day = currentTime.getDate();
var year = currentTime.getFullYear();
var hours = currentTime.getHours()
var minutes = currentTime.getMinutes()
var seconds = currentTime.getSeconds()

var curDate = year+"-"+month+"-"+day+" "+hours+":"+minutes+":"+seconds;
jQuery("#curTime").val(curDate);

if(jQuery("#select_busType option:selected").val() == "Hotel") jQuery("#buStarsContainer").show("slow");

<?php if(!isset($_REQUEST["bID"])){ echo '
else jQuery("#buStarsContainer").hide("slow");

jQuery("#select_busType").change(function() {
  var ebType = jQuery("#select_busType option:selected").val();
  if(ebType == "Hotel") jQuery("#buStarsContainer").show("slow");
  else jQuery("#buStarsContainer").hide("slow");
});
'; }
if($eb_BusinessCurrency !='') {
?>
jQuery('#eb_currency option[value=<?php echo $eb_BusinessCurrency; ?>]').attr('selected', 'selected');
<?php } ?>

//****Gia to position ths listas twn owners
var ownersList = jQuery("#eb_userlist"), list_pos, list_height;
//list_pos   = jQuery("#eb_ownerContainer").offset();
list_pos   = jQuery("#eb_ownerContainer").position();
list_height = jQuery("#eb_ownerContainer").height() + list_height;

ownersList.css({ "left": (list_pos.left) + "px", "top":(list_height + 10) + "px","width" : (jQuery("#eb_ownerContainer").width() + 200)+"px"}).show();
ownersList.hide();
//*****
function setOwner(owners_id, owners_name){
	jQuery("#eb_ownerBtn").val(owners_name);
	jQuery("#ebOwnerID").val(owners_id);

	hideUserList();	
}
function showUserList(){
	jQuery('#eb_userlist').show("fast");	
}
function hideUserList(){
	jQuery('#eb_userlist').hide("fast");	
}
function showFacilities(){
	jQuery("#facilitiesContainer").show("fast");
}

<?php
if($eb_BusinessFacilities != ''){
	$facilities = explode("|", $eb_BusinessFacilities);
	for($i=0; $i < count($facilities)-1; $i++){
		echo 'jQuery("#fclty_'.$facilities[$i].'").attr("checked","checked");';
	}
}
?>
function hideSeasons(){
	jQuery("#eb_seasonsArea").hide('fast');
	jQuery("#eb_operatingPeriodArea").show('slow');
}
function showSeasons(){
	jQuery("#eb_operatingPeriodArea").hide('fast');
	jQuery("#eb_seasonsArea").show('slow');
	}
	
jQuery("#eb_operatingPeriodStart").change(function(){
	 var startDate = jQuery("#eb_operatingPeriodStart").val();
	 var endDate = jQuery("#eb_operatingPeriodEnd").val();
	 if(startDate >= endDate || endDate == "NOT_SET") jQuery("#eb_operatingPeriodEnd").val(startDate);
	 jQuery('#eb_operatingPeriodEnd').find('option').each(function() {
	 	jQuery("#eb_operatingPeriodEnd option[value='"+jQuery(this).val()+"']").attr('disabled', false);
	 	if(jQuery(this).val() <= jQuery("#eb_operatingPeriodStart").val())
      	jQuery("#eb_operatingPeriodEnd option[value='"+jQuery(this).val()+"']").attr('disabled', 'disabled');
    });
	
});
jQuery("#eb_lowSeasonStart").change(function(){setStartUpDates("eb_lowSeasonStart", "eb_lowSeasonEnd", "eb_lowSeasonEnd|eb_midSeasonStart|eb_midSeasonEnd|eb_highSeasonStart|eb_highSeasonEnd||eb_midSeasonStart2|eb_midSeasonEnd2|eb_lowSeasonStart2|eb_lowSeasonEnd2", "");});
jQuery("#eb_lowSeasonEnd").change(function(){setStartUpDates("eb_lowSeasonEnd", "eb_midSeasonStart", "eb_midSeasonStart|eb_midSeasonEnd|eb_highSeasonStart|eb_highSeasonEnd||eb_midSeasonStart2|eb_midSeasonEnd2|eb_lowSeasonStart2|eb_lowSeasonEnd2", "");});
jQuery("#eb_midSeasonStart").change(function(){setStartUpDates("eb_midSeasonStart", "eb_midSeasonEnd", "eb_midSeasonEnd|eb_highSeasonStart|eb_highSeasonEnd||eb_midSeasonStart2|eb_midSeasonEnd2|eb_lowSeasonStart2|eb_lowSeasonEnd2", "eb_lowSeasonEnd");});
jQuery("#eb_midSeasonEnd").change(function(){setStartUpDates("eb_midSeasonEnd", "eb_highSeasonStart", "eb_highSeasonStart|eb_highSeasonEnd||eb_midSeasonStart2|eb_midSeasonEnd2|eb_lowSeasonStart2|eb_lowSeasonEnd2", "");});
jQuery("#eb_highSeasonStart").change(function(){setStartUpDates("eb_highSeasonStart", "eb_highSeasonEnd", "eb_highSeasonEnd|eb_midSeasonStart2|eb_midSeasonEnd2|eb_lowSeasonStart2|eb_lowSeasonEnd2", "eb_midSeasonEnd");});
jQuery("#eb_highSeasonEnd").change(function(){setStartUpDates("eb_highSeasonEnd", "eb_midSeasonStart2", "eb_midSeasonStart2|eb_midSeasonEnd2|eb_lowSeasonStart2|eb_lowSeasonEnd2", "");});
jQuery("#eb_midSeasonStart2").change(function(){setStartUpDates("eb_midSeasonStart2", "eb_midSeasonEnd2", "eb_midSeasonEnd2|eb_lowSeasonStart2|eb_lowSeasonEnd2", "eb_highSeasonEnd");});
jQuery("#eb_midSeasonEnd2").change(function(){setStartUpDates("eb_midSeasonEnd2", "eb_lowSeasonStart2", "eb_lowSeasonStart2|eb_lowSeasonEnd2", "");});
jQuery("#eb_lowSeasonStart2").change(function(){setStartUpDates("eb_lowSeasonStart2", "eb_lowSeasonEnd2", "eb_lowSeasonEnd2", "eb_midSeasonEnd2");});


function setStartUpDates(seasonChanged, nextSeason, restSeasonsArray, previousSeason){
	jQuery("#seasonLoadingImg").show("fast");
	jQuery("#eb_seasonsArea").hide("slow");
	var startDate = jQuery("#"+seasonChanged).val();
	var endDate = jQuery("#"+nextSeason).val();
	if(startDate >= endDate || endDate == "NOT_SET"){
		
		if(restSeasonsArray != ''){			
			var restSeasons = restSeasonsArray.split("|");		
			for (i=0;i<restSeasons.length;i++){
				if(jQuery('#'+restSeasons[i]).val() <= startDate) jQuery("#"+restSeasons[i]).val(startDate);
				//=================================================================================================
				//jQuery('#'+restSeasons[i]).find('option').each(function() {
				//if(jQuery(this).val() <= startDate ||jQuery(this).val() == "NOT_SET"){
    				//jQuery("#"+restSeasons[i]+" option[value='"+jQuery(this).val()+"']").attr('disabled', 'disabled');
    				// no->jQuery("#"+restSeasons[i]).val(startDate);
    			//}
    			//else jQuery("#"+restSeasons[i]+" option[value='"+jQuery(this).val()+"']").attr('disabled', false);
   			//});				
				//=================================================================================================
			}	
		}	
		
		
	}
	if(previousSeason !="" && jQuery("#"+previousSeason).val() < startDate) jQuery("#"+previousSeason).val(startDate);

	jQuery("#eb_seasonsArea").show("fast");
	jQuery("#seasonLoadingImg").hide("fast");
}

</script>
<script type="text/javascript" >
								function showHideImageArea(){
									if(jQuery(".imageAreaDiv").is(":visible")){
										jQuery(".imageAreaDiv").hide("slow");
										jQuery("#showHideImgLinkTitle").html("Show");
									}
									else{
										jQuery(".imageAreaDiv").show("slow");
										jQuery("#showHideImgLinkTitle").html("Hide");
									}
								}
								function logoIt(imgName){
									var confDel = confirm('If you continue you will set this image ("'+imgName+'") as your business logo! You can change it later if you change your mind...');
									if (confDel){
										var delUrl = "admin.php?page=<?php echo $_REQUEST['page']?>&bID=<?php echo $businessId?>&logoimg="+imgName;
										window.location = delUrl;
									}
								}
								function deleteIamges(imgName){
									var confDel = confirm('Are you sure you want to delete image "'+imgName+'"?');
									if (confDel){
										var delUrl = "admin.php?page=<?php echo $_REQUEST['page']?>&bID=<?php echo $businessId?>&delimg="+imgName;
										window.location = delUrl;
									}	
								}
							function startUpload(){									
    								document.getElementById('f1_upload_process').style.visibility = 'visible';
    								return true;
								}
							function stopUpload(resultStr){
							var result = resultStr.split("|");

      					if (result[0] == 1){
      						jQuery('#fileUploadArea').val('');
         					document.getElementById('result').innerHTML =
          					'<span class="msg">The image was uploaded successfully!<\/span><br/><br/>';
							var imgHolderCont= jQuery("#b_imgHolder").html();
							var newHolderStr = '<span><span style="position:absolute; padding-top:5px; padding-left:75px;"> <a onclick="deleteIamges(\''+result[1]+'\')" class="littleDelBtnsTrans" title="Delete '+result[1]+' image">X</a></span><span style="position:absolute; padding-top:5px; padding-left:28px;"><a onclick="logoIt(\''+result[1]+'\')" class="littleDelBtnsTrans" title="Make this your default logo image"><strong>Logo it</strong></a> </span><a target="_blank" href="<?php echo WP_CONTENT_URL?>/plugins/<?php echo $ebPluginFolderName?>/images/businessImg/'+result[1]+'" title = "Click to view fool size of image '+result[1]+'"><img id="eb_img" width="100" src="<?php echo WP_CONTENT_URL?>/plugins/<?php echo $ebPluginFolderName?>/images/businessImg/thumbs/'+result[1]+'" ></a></span>&nbsp;&nbsp;';
          			
							jQuery("#b_imgHolder").html(imgHolderCont+newHolderStr);
      					}
      					else if (result[0] == 2){
      						document.getElementById('result').innerHTML = '<div id="message" class="updated"><p><strong>This image already exists!<br>You can rename it or select a different image</strong></p></div>';
      					}
      					else if (result[0] == 3){
      						document.getElementById('result').innerHTML = '<div id="message" class="updated"><p><strong>This image type is not supported</strong></p></div>';
      					}
      					else if (result[0] == 4){
      						document.getElementById('result').innerHTML = '<div id="message" class="updated"><p><strong>The image size is to big.<br>Try resizing it or select a different image</strong></p></div>';
      					}
      					// if (result[0] != 1 && result[0] != 1.1 && result[0] != 2 && result[0] != 3 && result[0] != 4) {
      					else{
				         document.getElementById('result').innerHTML = 
           				'<span class="emsg"><div id="message" class="updated"><p><strong>There was an error during file upload!</strong></p></div><\/span><br/>';
      					}
      					document.getElementById('f1_upload_process').style.visibility = 'hidden';
      					return true;   
						}

function setPackageDeal(){
	var newDeal = jQuery("#selPackageDeal").val();
	var url = "<?php echo get_admin_url() .'admin.php?page='.$_REQUEST['page'].'&bID='.$_REQUEST['bID'].'&pack=';?>"+newDeal; 
	window.location = url;	
}
							
							
	function IpayFunc(){
		var iAmount = jQuery("#billingPaidAmount").val();
		var iConf;
		if(iAmount != ''){
			iConf = confirm('You are about to add a new payment! \nPlease press OK ONLY if you have received this amount in your bank account.');	
		}
		else alert("You have to enter the amount you have received at the text box.");
		var iLocation = "?page=<?php echo $editBusinessLink; ?>&pay=pay&bID=<?php echo $businessId; ?>&ammount="+iAmount;
		if(iConf)
			jQuery('#iPayFrame').attr('src', iLocation);
	}

//at Business policies
function showRemoveRoomsCost( showRhide ){
	if( showRhide == "show" ){
		jQuery("#removeRoomsCost").show('fast');
	}
	else {
		jQuery("#removeRoomsCost").hide('slow');
	}
}
function showRemoveDaysCost( showRhide ){
	if( showRhide == "show" ){
		jQuery("#removeDaysCost").show('fast');
	}
	else {
		jQuery("#removeDaysCost").hide('slow');
	}
}



jQuery("#editBookingAllowedDatePeriod").change( function() {
	if( jQuery("#editBookingAllowedDatePeriod").val() == 0 ){
		jQuery("#displayEditBookingOptions").hide('slow');
	}
	else jQuery("#displayEditBookingOptions").show('slow');
});

jQuery(document).ready(function(){
	jQuery("#displayEditBookingOptions").hide('fast');
	if( jQuery("#editBookingAllowedDatePeriod").val() == 0 ){
		jQuery("#displayEditBookingOptions").hide('fast');
	}
	else jQuery("#displayEditBookingOptions").show('fast');
});
      
</script>

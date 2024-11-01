<?php
global $wpdb;
global $ebPluginFolderName;
global $current_user;
global $countriesTable;
global $eb_adminUrl;

$editBusinessLink = 'busines_menu';

if($current_user->wp_user_level == 0) {
	$targetPage = 'business_list';
	$editBusinessLink = 'business_list';
}
else $targetPage = 'easy_booking_menu';

$rID = check_input ($_REQUEST["rID"]);
$bID = check_input ($_REQUEST["bID"]);


$business = $wpdb->get_row('select ID, post_title from wp_posts where ID ='.$bID);
$businessName = $business->post_title;
$businessCurrency = get_post_meta($business->ID, "eb_currency");
if(!empty($businessCurrency)) $businessCurrency = $businessCurrency[0]; else $businessCurrency ='';
$extraBedPrice = get_post_meta($business->ID, "eb_extraBedPrice");
if(!empty($extraBedPrice)) $extraBedPrice = $extraBedPrice[0]; else $extraBedPrice =0;
$businessEmail = get_post_meta($business->ID, "eb_email");
if(!empty($businessEmail)) $businessEmail = $businessEmail[0]; else $businessEmail = '';

$roomType = $wpdb->get_row('select ID, post_title from wp_posts where ID ='.$rID);
$roomTitle = $roomType->post_title;

if(isset($_REQUEST['bs']) && $_REQUEST['bs'] == "cnfbook" ){
	$bs_startRange = check_input ($_POST['cnf_dateRangeStart']);
	$bs_endRange = check_input ($_POST['cnf_dateRangeEnd']);
	$bs_numOfNights = check_input ($_POST['cnf_numOfNights']);
	$bs_numOfRooms = check_input ($_POST['cnf_numberOfRooms']);
	$bs_clientID = check_input ($_POST['cnf_clientID']);
	$bs_clientFname = check_input ($_POST['cnf_clientFname']);
	$bs_clientLname = check_input ($_POST['cnf_clientLname']);
	$bs_clientEmail = check_input ($_POST['cnf_clientEmail']);
	$bs_clientTel = check_input ($_POST['cnf_clientTel']);
	$bs_clientCountry = check_input ($_POST['cnf_clientCountry']);
	$bs_roomsOptions = check_input ($_POST['cnf_roomsOptions']);
	$bs_bookingTotal = check_input ($_POST['cnf_bookingTotal']);
	$bs_bookingCur = check_input ($_POST['cnf_bookingCur']);
	$bs_bookErrorStr = '<p><b>Booking Error. Please try again</b></p><p><em>';
	$hasBookError = false;
	
	if($bs_clientFname == ''){
		$bs_bookErrorStr.= '<br />Please enter clients first name';
		$hasBookError = true;
	}
	if($businessEmail != ''){
		$businessEmail = filter_var( $businessEmail, FILTER_VALIDATE_EMAIL );	
	}
	else {
		$bs_bookErrorStr.= '<br />This booking could not be successfully completed because of an error at your email address.Please correct it immediately from your<a class="littleEditBtns" href="'.$eb_adminUrl.'?page='.$editBusinessLink.'&bID='.$bID.'" title = "Go to business configuration"> business configuration page</a>';
		$hasBookError = true;
	}
	if(!$businessEmail){
		$bs_bookErrorStr.= '<br />This booking could not be successfully completed because of an error at your email address.Please correct it immediately from your<a class="littleEditBtns" href="'.$eb_adminUrl.'?page='.$editBusinessLink.'&bID='.$bID.'" title = "Go to business configuration"> business configuration page</a>';
		$hasBookError = true;
	}

	$bs_bookErrorStr .= '</em></p>';
	if($hasBookError){
		?>
		<script type="text/javascript" >
			jQuery(document).ready(function($) {
				jQuery("#dateErrorMsgDiv").html('<?php echo $bs_bookErrorStr; ?>');    				
    			jQuery("#dateErrorMsgArea").show();
    			
    		});
		</script>
		<?php
	}	//end of if has error	
	else{
		$bookingReportTblS = '<table style="width:90%;border:1px solid #ccc;font-family:verdana, san-serif;font-size:14px;color:#00a0ce;background-color:#ffffff;" align="left">';
		$bookingReportTitle = '<tr><td style="font-size:16px;padding:10px;padding-bottom:20px"><strong>New booking request for <font style="font-size:18px"><em>'.$businessName.'</em></font></strong></td></tr>';
		$bookingReportBusinessmanMsg = '<tr><td style="font-size:12px;color:#666">After reading the following booking request, you can confirm this request by pressing on the confirmation button at the bottom of this message.<br /><em>If you believe that this booking can not be completed, you can cancel it from the same page.</em></td></tr>';
		$wpdb->insert( 
			'eb_bookingdata', 
				array( 
					'businessID' => $bID,
					'customerID' => $bs_clientID, 
					'customer_fname' => $bs_clientFname,
					'customer_lname' => $bs_clientLname,
					'customer_email' => $bs_clientEmail,
					'customer_tel' => $bs_clientTel,
					'customer_country' => $bs_clientCountry,
					'dateRange_start' => gmdate(dateToYMD($bs_startRange)." 00:00:00"),
					'dateRange_end' => gmdate(dateToYMD($bs_endRange)." 00:00:00"),
					'booking_date' => gmdate("Y-m-d H:i:s"),										
					'booking_currency' => $bs_bookingCur,
					'booking_total' => $bs_bookingTotal,
					'booking_totalBCUR' => $bs_bookingTotal,
					'booking_status' => 'Pending',
					'bookedNights' => (int)$bs_numOfNights,
					'numberOfRooms' => (int)$bs_numOfRooms
				), 
				array( '%d','%d','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%d','%d' ) 
		);
		$newBookID = $wpdb->insert_id;
		
		list($arDay, $arMonth, $arYear) = explode('/', $bs_startRange);
		$arDate = gmdate("l d F Y", mktime(0,0,0, $arMonth, $arDay, $arYear));
		list($depDay, $depMonth, $depYear) = explode('/', $bs_endRange);
		$depDate = gmdate("l d F Y", mktime(0,0,0, $depMonth, $depDay, $depYear));
		
		$bookingReportData = '<tr><td style="padding-top:10px;background-color:#cee6ed">
			<p>
				Booking ID: <b>'.$newBookID.'</b>
			</p>
			<p>
				Arrival at: <b>'.$arDate.'</b><br />
				Departure at: <b>'.$depDate.'</b>
			</p>
			<p>
				Booking nights: <b>'.$bs_numOfNights.'</b> nights
			</p>
			<p>
				Number of rooms: <b>'.$bs_numOfRooms.'</b>
			</p>
			<p>
				Status: <b>Pending</b> <i>(Awaiting hotel management\'s booking confirmation. You will be informed with a new email about the status of this booking)</i>
			</p>
			<p>
				Booking total: <b>'.$bs_bookingTotal.' '.$businessCurrency.'</b>
			</p>
		</td></tr>';
		
		$bookingReportCustomerInfo = '<tr><td>';
		$bookingReportCustomerInfo .= '<div style = "line-height:15px">&nbsp;</div>';
		$bookingReportCustomerInfo .= '<table style="font-size:14px;width:100%;background-color:#cee6ed">';
			$bookingReportCustomerInfo .= '<tr><td style="padding-left:15px;font-size:12px"><u>Customer Data</u></td></tr>';
			$bookingReportCustomerInfo .= '<tr><td style="padding-left:15px;">Name: <b>'.$bs_clientLname.' '.$bs_clientFname.'</b></td></tr>';
			$bookingReportCustomerInfo .= '<tr><td style="padding-left:15px;">Email: <b>'.$bs_clientEmail.'</b> Tel: <b>'.$bs_clientTel.'</b></td></tr>';
			if($bs_clientCountry != ''){
				$bookingReportCustomerInfo .= '<tr><td style="padding-left:15px;">Country: <b>'.$bs_clientCountry.'</b></td></tr>';
			}
		$bookingReportCustomerInfo .= '</table>';
		$bookingReportCustomerInfo .= '<div style = "line-height:15px">&nbsp;</div>';
		$bookingReportCustomerInfo .= '</td></tr>';
		 
		$bs_roomsOptions = explode('[-]',$bs_roomsOptions);
		$bookingReportRoomsOpts = '<tr><td>'; 
		for( $r=0; $r < (sizeof($bs_roomsOptions) - 1); $r++){
			if($bs_roomsOptions[$r] != ''){	
				$optionsArr = explode('|', $bs_roomsOptions[$r]);
				$rOptBID = $optionsArr[0];
				$rOptID = $optionsArr[1];
				$rOptPrice = $optionsArr[2];
				$rOptGuest = $optionsArr[3];
				$rOptBabies = $optionsArr[4];
				$rOptExtraBedNum = $optionsArr[5];
				$rOptExtraBedPrice = $optionsArr[6];
				$rOptHBstr = '';
				$rCount = (int)$r + 1;
				$bgCol = '#cee6ed';
				$fontCol = '#00a0ce';
				if($rCount % 2 == 0 ){
					$bgCol = '#00a0ce';
					$fontCol = '#ffffff';
				}
				$bookingReportRoomsOpts .= '<table style="font-size:12px;width:100%;padding-top:20px;background-color:'.$bgCol.';color:'.$fontCol.';">';
				$bookingReportRoomsOpts .= '<tr><td style="font-size:14px;">#' .$rCount. ' <b>'.$roomTitle.'</b></td><td align="right">Price for '.$bs_numOfNights.' nights : <b>'.$rOptPrice.' '.$businessCurrency.'</b>&nbsp;&nbsp;&nbsp;</td></tr>';
				$bookingReportRoomsOpts .= '<tr><td style="padding-left:30px" colspan="2">Guest name: <b>'.$rOptGuest.'</b></td></tr>';
				if($rOptBabies != '' && (int)$rOptBabies > 0)
					$bookingReportRoomsOpts .= '<tr><td style="padding-left:30px" colspan="2">Number of babies: <b>'.$rOptBabies.'</b></td></tr>';
					
				if($rOptExtraBedNum != '' && (int)$rOptExtraBedNum > 0){
					$extraBedsSum = (int)$rOptExtraBedNum * (int)$bs_numOfNights * $rOptExtraBedPrice;
					$bookingReportRoomsOpts .= '<tr><td style="padding-left:30px" colspan="2">Number of extra beds: <b>'.$rOptExtraBedNum.'</b></td><td align="right">'.$rOptExtraBedNum.' beds * '.$bs_numOfNights.' nights * '.$rOptExtraBedPrice.' '.$businessCurrency.' = <b>'.$extraBedsSum.' '.$businessCurency.'</b>&nbsp;&nbsp;&nbsp;</td></tr>';
				}
				for($o=7; $o <= sizeof($optionsArr); $o++){					 
					if($optionsArr[$o] != ''){						
						$HBdayCount = explode(':',$optionsArr[$o]);
						$arDateHB = mktime(12,0,0,$arMonth ,$arDay ,$arYear );
						$arDateHB += ((int)$HBdayCount[0] * 86400);
						$bookingReportRoomsOpts .= '<tr><td style="padding-left:30px">Half Board Meal for: '.date('l d F Y',$arDateHB).'</td><td align="right">Extra cost: <b>'.$HBdayCount[1].' '.$businessCurrency.'</b>&nbsp;&nbsp;&nbsp;</td></tr>';
						$rOptHBstr .= '|'.$optionsArr[$o];
					}											
				}
				$bookingReportRoomsOpts .= ' </table> ';
				$wpdb->insert( 
						'eb_bookingroomdata', 
							array( 
								'bookingID' => $newBookID, 
								'roomID' => $rOptID,
								'roomCost' => $rOptPrice,
								'businessID' => $rOptBID,
								'noOfBabies' => $rOptBabies,
								'extraBedNum' => $rOptExtraBedNum,
								'extraBedPrice' => $rOptExtraBedPrice,								
								'guestFullName' => $rOptGuest,
								'HBoptions' => $rOptHBstr,
								'dateRange_start' => gmdate(dateToYMD($bs_startRange)." 00:00:00"),
								'dateRange_end' => gmdate(dateToYMD($bs_endRange)." 00:00:00"),
								'canceled' => 'NO'
							), 
							array( '%d','%d','%s','%d','%d','%d','%s','%s','%s','%s','%s','%s') 
					);
					//$wpdb->show_errors();
					//$wpdb->print_error();
			}
		}
	$bookingReportRoomsOpts .= '</td></tr>';
	
	
	$bookRepMsg = $bookingReportTblS;
	$bookRepMsg .= $bookingReportTitle;
	$bookRepMsg .= $bookingReportBusinessmanMsg;
	$bookRepMsg .= $bookingReportCustomerInfo;
	$bookRepMsg .= $bookingReportData;
	$bookRepMsg .= $bookingReportRoomsOpts;
	
	$bookRepMsg .= '</table>';
	
   add_filter('wp_mail_content_type',create_function('', 'return "text/html"; '));
   add_filter('wp_mail_from', create_function('', 'return "'.$businessEmail.'"; '));
	add_filter('wp_mail_from_name', create_function('', 'return "'.$businessName.' an '.get_bloginfo('name').' Partner"; '));
	
   wp_mail($businessEmail, $businessName.' booking report', $bookRepMsg);
		?>
		<script type="text/javascript" >
			document.location = "admin.php?page=<?php echo $targetPage; ?>&type=Hotel&rID=<?php echo $rID; ?>&bID=<?php echo $bID; ?>&action=bookit&bs=sccs";
			
		</script>
		<?php

	}//end of has no error
}

$roomLogo = get_post_meta($roomType->ID, "eb_defaultLogo");
if(!empty($roomLogo)) $roomLogo = $roomLogo[0]; else $roomLogo ='';
$roomNum = get_post_meta($roomType->ID, "eb_roomNum");
if(!empty($roomNum)) $roomNum = $roomNum[0]; else $roomNum ='';					
$peopleNum = get_post_meta($roomType->ID, "eb_peopleNum");
if(!empty($peopleNum)) $peopleNum = $peopleNum[0]; else $peopleNum ='';
$babiesAllowed = get_post_meta($roomType->ID, "eb_babiesAllowed");
if(!empty($babiesAllowed)) $babiesAllowed = $babiesAllowed[0]; else $babiesAllowed = 0;
$childrenAllowed = get_post_meta($roomType->ID, "eb_childrenAllowed");
if(!empty($childrenAllowed)) $childrenAllowed = $childrenAllowed[0]; else $childrenAllowed = 0;
$extraBedsAvailable = get_post_meta($roomType->ID, "eb_extraBedsAvailable");
if(!empty($extraBedsAvailable)) $extraBedsAvailable = $extraBedsAvailable[0]; else $extraBedsAvailable = 0;


$imgPath = ABSPATH.'wp-content/plugins/'.$ebPluginFolderName.'/images/RoomImg';

if(isset($_REQUEST['bs']) && $_REQUEST['bs'] == "sccs" ){
	?>
	<div class="updated"><p>New Booking for <b><?php echo $roomTitle; ?></b> was succesfull!</p></div>	
	<?php
}

?>
<table class="widefat" style="width:99%">
	<thead>
		<tr>
			<th colspan="2">New booking for business  <a href="<?php echo get_admin_url();?>admin.php?page=<?php echo $editBusinessLink; ?>&bID=<?php echo $_REQUEST['bID'];?>" style="font-size:18px;"><em> <?php echo $businessName; ?></em> </a></th>
		</tr>
	</thead> 
	<tbody>
		<tr>
			<td colspan="2">
				<div id="eb_roomDetailsInBookIt">
				
					<table style="border:none" >
						<tr valign="top">
							
								<?php
								if(is_file($imgPath.'/thumbs/'.$roomLogo)){
								?>
									<td style="border:none" valign="top">
									<img src="<?php echo WP_CONTENT_URL?>/plugins/<?php echo $ebPluginFolderName?>/images/RoomImg/thumbs/<?php echo $roomLogo?>" alt="Logo" >
									</td>
								<?php 
								}
								//$edit_link = "<a href='".$eb_adminUrl."?page=".$_REQUEST['page']."&type=Hotel&bID=".$_REQUEST['bID']."&rID=".$rooms->ID."'>Edit</a> |";
								echo '<td style="border:none" valign="top">
								
										<div style="font-size:16px"><strong><a href="'.get_admin_url().'admin.php?page='.$targetPage.'&type=Hotel&bID='.$_REQUEST['bID'].'&rID='.$_REQUEST['rID'].'">'.$roomTitle.'</a></strong></div>
										<div style="padding-left:10px;padding-top:10px;font-style:italic;">
											<div>Up to: '.$peopleNum.' adults</div>
											<div>Up to: '.$childrenAllowed.' children</div>
											<div>Up to: '.$babiesAllowed.' babies</div>
											<div>Up to: '.$extraBedsAvailable.' extra beds	</div>
										</div>
									</td>';
								?>							
							
							<td style="border:none;" align="right">
															
							</td>
						</tr>					
					</table>
				
				</div>			
			</td>	
		</tr>
		<tr>
			<td colspan="2" style="display:none" id="dateErrorMsgArea">
				<div id="dateErrorMsgDiv" class="error" style=""></div>
			</td>
		</tr>
		<tr valign="top">
			<td valign="top">
				<table style="border:none;" width="100%">
					<tr valign="top">
						<td style="border:none;" valign="top" width="55%">			
			
						<div id="eb_setDateRangeInBookIt" class="eb_simpleContainer">
						<h3>Date range</h3>
						<div>Please set the date range you wish to book this room</div>
						<?php
						global $table_prefix;
							include_once(ABSPATH.'wp-content/plugins/'.$ebPluginFolderName.'/classes/booking.class.php');
							$bookVar = new searchServices;
							$startOpPeriod = $bookVar->businessOperatingPeriod($_REQUEST['bID'], $wpdb ,'start', $table_prefix);
							$endOpPeriod = $bookVar->businessOperatingPeriod($_REQUEST['bID'], $wpdb ,'end', $table_prefix);
							
							include_once(ABSPATH.'wp-content/plugins/'.$ebPluginFolderName.'/classes/calendar.class.php');
							$calendar = new bookingCalendar;
							echo '<input type="hidden" id="startOperatingPeriod" value="'.$startOpPeriod.'">';
							echo '<input type="hidden" id="endOperatingPeriod" value="'.$endOpPeriod.'">';
							echo '<input type="hidden" id="abs_path" value="'.ABSPATH.'">';
		
							echo 'From: <input type="text" id="eb_dateRangeStart" name="eb_dateRangeStart" style="width:100px" class="eb_dateRangeArea" onclick="displayCalendar(\'start\');" readonly="readonly" />';
							echo ' To: <input type="text" id="eb_dateRangeEnd" name="eb_dateRangeEnd" style="width:100px" class="eb_dateRangeArea" onclick=displayCalendar(\'end\') readonly="readonly" />';
							echo '<div id="eb_calendarMainContainer" class="eb_calendarMainContainer"></div>';
							echo '<input type="hidden" id="switchStartAndEndDates" value="">';
							echo '<button id="eb_showPricesForRangeBtn">Show prices</button>';
						?>
						<!--Hidden vals needed for calculation-->
						<input type="hidden" id="totalPerRoom" name="totalPerRoom" value="" />
						<input type="hidden" id="babiesAllowed" name="babiesAllowed" value="<?php echo $babiesAllowed; ?>" />
						<input type="hidden" id="extraBedsAvailable" name="extraBedsAvailable" value="<?php echo $extraBedsAvailable; ?>" />
						<input type="hidden" id="checkSeasons" name="checkSeasons" value="" />
						<input type="hidden" id="nightsBookedCount" name="nightsBookedCount" value="" />
						<input type="hidden" id="dateRangeStartModified" value="" />
						
						<div id="HbRepSrc" style="display:none"></div>
						<div id="nightCountReport"></div>
						</div>
						</td>
						<td style="border:none;" valign="top">
							<div id="eb_setRoomNumInBookIt" style="display:none;" class="eb_simpleContainer">
							<h3>Number of rooms</h3>
								Number of rooms you wish to book : 
								<span id="eb_selectRoomNumArea">
									<select id="roomNumSelect" name="roomNumSelect"></select>								
								</span>
								<div id="selectRoomNumMsgArea"></div>
								
							</div>
						</td>
					</tr>
					<tr>
						<td style="border:none;" colspan="2">
							<div id="eb_setOwnerInBookIt" style="display:none;" class="eb_simpleContainer">
							<h3>Debtor (client) details</h3>
								<table style="border:none;width:100%">
									<tr>
										<td style="border:none;">
										
										
											<div id="eb_ownerContainer" class="eb_simpleContainer">
												<label><strong>Select registered client</strong> </label><br>
											<div align="center">
												<input name="eb_owner" type="button" class="button-primary" id="eb_ownerBtn" tabindex="5" value="Registered clients" style="width:90%" onclick="showUserList()" />
												<input type="hidden" id="eb_ownersId" name="eb_ownersId" value=""/>
											</div>
											
											<div id="eb_userlist" style="display:none;padding:5px;border:1px solid #a4cdd7;border-radius: 1em;box-shadow: rgba(0,0,0,0.4) 1px 1px 1px 1px;position:absolute;zindex:2;background-color:#fff; opacity: 0.9;">
												<table class="widefat" style="width:99%">
													<thead>
													<tr>
														<th>
															Select a registered client for this booking<span style="float:right"><a class="littleCloseBtns" onclick="hideUserList();" title="Close users list">X</a></span>
														</th>	
													</tr>
													</thead>
													<tr>
														<td><div style="background: #23769d;height:200px;overflow:scroll;"><div style="line-height:5px">&nbsp;</div>
														<?php
											 				$author_ids = get_users('orderby=nicename&role!=administrator');
															foreach($author_ids as $author){
																$lastName = get_user_meta( $author->ID, 'last_name', 'true' );
																$firstName = get_user_meta( $author->ID, 'first_name', 'true' );
																echo '<div style="padding-right:5px;padding-left:5px;" id="selectOwnerBtn_'.$author->ID.'" onclick="setOwner('.$author->ID.', \''.$lastName.'\', \''.$firstName.'\',\''.$author->user_email.'\' )">
																	<div class="userLine" style="padding:5px;color: #fff;background: #23769d;border: 1px solid #0b3a50; border-right: 1px solid #8bb7cb;border-bottom: 1px solid #8bb7cb;-webkit-border-radius: 5px;-moz-border-radius: 5px;border-radius: 5px;"><strong>
																		<span>'.$lastName.'</span>
																		<span>'.$firstName.'</span></strong>
																		<span>(<i>'.$author->user_nicename.' - '.$author->user_email.'</i>)</span>
																	</div>												
																</div>';
																}										
														?>
															<div style="line-height:5px">&nbsp;</div>
															</div>
														</td>
													</tr>
												</table>
											</div>
										</div>										
										
											
										</td>
										<td style="border:none;">
											<div id="eb_userDetailsArea" class="eb_simpleContainer">
												<table style="border:none;width:100%">
													<tr>
														<td style="border:none;">
															<span>
																<label><strong>First Name:</strong></label><br />
																<input type="text" id="debtor_fName" name="debtor_fName" style="width:250px" onkeyup="setDebtorInfo(this.id, 'BR_clientFname')" />
															</span>
														</td>	
														<td style="border:none;">
														<span>
																<label><strong>Last Name:</strong></label><br />
																<input type="text" id="debtor_lName" name="debtor_lName" style="width:250px" onkeyup="setDebtorInfo(this.id, 'BR_clientLname')" />
															</span>
														</td>												
													</tr>
													<tr>
														<td style="border:none;">
															<span>
																<label><strong>Email:</strong></label><br />
																<input type="email" id="debtor_email" name="debtor_email" style="width:240px" onkeyup="setDebtorInfo(this.id, 'BR_clientEmail')" />
															</span>
														</td>	
														<td style="border:none;">
															<span>
																<label><strong>Telephone:</strong></label><br />
																<input type="tel" id="debtor_tel" name="debtor_tel" onkeyup="setDebtorInfo(this.id, 'BR_clientTel')" />
															</span>
														</td>												
													</tr>
													<tr>
														<td style="border:none;" colspan="2">
															<select name="debtor_country" id="debtor_country" style="color:#666;font-weight:bold;" onchange="setDebtorInfo(this.id, 'BR_clientCountry')">
														
																<?php
																$countries = $wpdb->get_results('select CountryId, Country from '.$countriesTable);
																echo '<option style="font-style:italic" value="">Select country</option>';
																foreach($countries as $country){
																	_e('<option value="'.$country->Country.'">'.$country->Country.'</option>');
																}
															?>
															</select>
														</td>																											
													</tr>
												</table>

											</div>										
										</td>
									</tr>								
								</table>			
							</div>
						</td>
					</tr>
					
					<tr>
						<td style="border:none;" colspan="2">
							<div id="eb_eachRoomDetails" style="display:none;" class="eb_simpleContainer">
							<h3>Rooms details</h3>
								Data for each room that will be booked

							</div>
						</td>
					</tr>
				</table>
			</td>		
			<td width="350px" style="padding-top:10px">
				<div id="bookingReportArea" class="eb_simpleContainer" style="display:none">
					<h3>Booking Report</h3>
					This Reservation was made by <b><?php echo $current_user->user_lastname .' '.$current_user->user_firstname;?></b>
					<div id="clientSelectedData" class="eb_simpleContainer">
						<strong>Client information</strong><br /><br />
						<p><label style="color:#666">Last Name: </label><span id="BR_clientLname" style="font-weight:bold;color:#666"></span></p>
						<p><label style="color:#666">First Name: </label><span id="BR_clientFname" style="font-weight:bold;color:#666"></span></p>
						<p><label style="color:#666">Email: </label><span id="BR_clientEmail" style="font-weight:bold;color:#666"></span></p>
						<p><label style="color:#666">Tel: </label><span id="BR_clientTel" style="font-weight:bold;color:#666"></span></p>	
						<p><label style="color:#666">Country: </label><span id="BR_clientCountry" style="font-weight:bold;color:#666"></span></p>						
					</div>
					<div style="line-height:10px">&nbsp;</div>
					
					<div class="eb_simpleContainer">
						<strong>Pricing information</strong><br /><br />
						<div id="bookPricingDetailsArea" style="display:none;"></div>
						<div id="bookPricingDetailsAnalyzedArea" style="display:none;"></div>
										
						<p>
							<div>
								Price for <span id="roomsSelectedCounter"></span> rooms: <b><span id="priceForSelRoomsArea"></span> <?php echo $businessCurrency; ?></b>
							</div>
						</p>
						<p>
							<div id="extraBedsSelectedArea" style="display:none;">
								Extra beds price: <b><span id="extraBedPriceRecord"></span> <?php echo $businessCurrency; ?></b>
							</div>
						</p>
						<p>
							<div id="HBselectedArea" style="display:none;">
								Half Board Meals price: <b><span id="HBpriceRecord"></span> <?php echo $businessCurrency; ?></b>
							</div>
						</p>
					</div>
					<p>
						<div id="totalBookingPriceArea" class="plainBox" align="right">
							Total price: <span id="totalBookingPrice" style="font-size:16px;font-weight:bold;"></span> <font style="font-size:16px;font-weight:bold;"><?php echo $businessCurrency; ?></font>
						</div>
					</p>	
					<div align="center">
					<form name="goBook" method="post" action="admin.php?page=<?php echo $targetPage; ?>&type=Hotel&rID=<?php echo $rID; ?>&bID=<?php echo $bID; ?>&action=bookit&bs=cnfbook" name="ebBookFrm" onsubmit="validatebookingdata(this);return false;" >
						<input type="hidden" id="cnf_dateRangeStart" name="cnf_dateRangeStart" value="" />
						<input type="hidden" id="cnf_dateRangeEnd" name="cnf_dateRangeEnd" value="" />
						<input type="hidden" id="cnf_numberOfRooms" name="cnf_numberOfRooms" value="" />
						<input type="hidden" id="cnf_numOfNights" name="cnf_numOfNights" value="" />
						<input type="hidden" id="cnf_clientID" name="cnf_clientID" value="" />
						<input type="hidden" id="cnf_clientFname" name="cnf_clientFname" value="" />
						<input type="hidden" id="cnf_clientLname" name="cnf_clientLname" value="" />
						<input type="hidden" id="cnf_clientEmail" name="cnf_clientEmail" value="" />
						<input type="hidden" id="cnf_clientTel" name="cnf_clientTel" value="" />
						<input type="hidden" id="cnf_clientCountry" name="cnf_clientCountry" value="" />
						<input type="hidden" id="cnf_roomsOptions" name="cnf_roomsOptions" value="" />
						<input type="hidden" id="cnf_bookingCur" name="cnf_bookingCur" value="" />
						<input type="hidden" id="cnf_bookingTotal" name="cnf_bookingTotal" value="" />
						<input type="submit" id="cnf_completeBookBtn" value="Complete" class="button-primary" />
					</form>
					</div>				
				</div>
			</td>
		</tr>
	</tbody>	
</table>
<script type="text/javascript" >

	function validatebookingdata(f){
		var hasError = false;
		var errorMsg = "<h3>Booking Error</h3><em>";
		jQuery("#cnf_clientFname").val(jQuery("#debtor_fName").val());
		jQuery("#cnf_clientLname").val(jQuery("#debtor_lName").val());
		
		jQuery("#cnf_clientEmail").val(jQuery("#debtor_email").val());
		jQuery("#cnf_dateRangeStart").val(jQuery("#eb_dateRangeStart").val());
		jQuery("#cnf_dateRangeEnd").val(jQuery("#eb_dateRangeEnd").val());
		jQuery("#cnf_bookingTotal").val(jQuery("#totalBookingPrice").html());
		jQuery("#cnf_bookingCur").val("<?php echo $businessCurrency ;?>");
		jQuery("#cnf_clientCountry").val(jQuery("#debtor_country").val());
		jQuery("#cnf_clientTel").val(jQuery("#debtor_tel").val());					
		jQuery("#cnf_numberOfRooms").val(jQuery("#roomsSelectedCounter").html());
		var roomCostForRange = jQuery("#totalPerRoom").val();
		var nights = jQuery("#nightsBookedCount").val();
		jQuery("#cnf_numOfNights").val(nights);
		
		var roomIDstr = '';
		for(r = 1; r <= parseInt(jQuery("#cnf_numberOfRooms").val()); r++){
			var GuestName = jQuery("#roomItemGuestName_"+r).val();
			var BabiesNum = jQuery("#roomItemBabies_"+r).val();
			var ExtraBedNum = jQuery("#roomItemExtraBeds_"+r).val();
			var ExtraBedPrice = jQuery("#roomExtraBedPrice_"+r).html();
			roomIDstr += "<?php echo $bID; ?>|<?php echo $rID; ?>|"+roomCostForRange+"|"+GuestName+"|"+BabiesNum+"|"+ExtraBedNum+"|"+ExtraBedPrice;
			for(n=0; n< nights; n++){
				if( jQuery("#HBchx<?php echo $rID;?>_"+r+"_"+n).attr('checked') ){
					roomIDstr += "|"+n+":"+jQuery("#HBchx<?php echo $rID;?>_"+r+"_"+n).val();
				}
			}
			roomIDstr += "[-]";
		}
		jQuery("#cnf_roomsOptions").val(roomIDstr);
			
		if (jQuery("#cnf_clientFname").val() == ""){
			hasError = true;
			errorMsg += 'Please enter clients first name<br /> ';	
		}
		if (jQuery("#cnf_clientLname").val() == ""){
			hasError = true;
			errorMsg += 'Please enter clients last name<br /> ';	
		}
		if (jQuery("#cnf_clientEmail").val() == ""){
			hasError = true;
			errorMsg += 'Please enter clients email<br /> ';	
		}
		var email = document.getElementById('cnf_clientEmail');
		var filter = /^([a-zA-Z0-9_\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/;
		if (!filter.test(email.value)) {
			hasError = true;
			errorMsg += 'Clients email seems to be wrong<br /> ';
     }
		
		errorMsg += "</em>";
		if(hasError){
			jQuery("#dateErrorMsgDiv").html(errorMsg);    				
    		jQuery("#dateErrorMsgArea").show();
			return false;	
		}
		else {
			f.submit();
			return false;
		}
	}

	jQuery("#eb_showPricesForRangeBtn").click(function(){
		if(jQuery("#eb_dateRangeStart").val() != "" && jQuery("#eb_dateRangeEnd").val() != ""){
			jQuery("#dateErrorMsgArea").hide();
			jQuery("#extraBedsSelectedArea").hide();
			jQuery("#extraBedPriceRecord").val(0);
			jQuery("#HBselectedArea").hide();
			jQuery("#HBpriceRecord").val(0);
			jQuery("#roomNumSelect").val(1);
			var roomNumSelect = document.getElementById('roomNumSelect') ;
    		roomNumSelect.options.length = 0;
			jQuery("#priceForSelRoomsArea").html('');
			jQuery("#roomNumSelect").trigger('change');

			var totalPerRoom = 0;
			
			jQuery("#cnf_dateRangeStart").val(jQuery("#eb_dateRangeStart").val());
			jQuery("#cnf_dateRangeEnd").val(jQuery("#eb_dateRangeEnd").val());			
						
			var date1 = makeDate(jQuery("#eb_dateRangeStart").val());
			var date2 = makeDate(jQuery("#eb_dateRangeEnd").val());
			
			if(beforeTodayDate(date1) == true) {
				jQuery("#dateErrorMsgDiv").html('<p>You have selected a wrong date.</p> <p>Please make sure that your booking date starts at least from today!</p>');    				
    			jQuery("#dateErrorMsgArea").show();
				return;				
			}

			if(beforeTodayDate(date2) == true){
				jQuery("#dateErrorMsgDiv").html('<p>You have selected a wrong date.</p> <p>Please make sure that your booking date starts at least from today!</p>');    				
    			jQuery("#dateErrorMsgArea").show();
				return;	
			}

			jQuery("#dateRangeStartModified").val(date1);			
			
			var absPath = jQuery("#abs_path").val();
			var bID = '<?php echo $_REQUEST['bID']; ?>';
			var rID = '<?php echo $_REQUEST['rID']; ?>';
			//vdata = "sID=<?php echo $_REQUEST['rID']; ?>&bID="+bID+"&startRange="+jQuery('#eb_dateRangeStart').val()+"&endRange="+jQuery('#eb_dateRangeEnd').val()+"&getServicePrice=WITH_AJAX&abPath="+absPath+"";
			//alert(vdata);
			var daysBooked = calculateDaysBetweenDates(date1, date2);
			jQuery("#nightCountReport").html('').hide();
			jQuery("#nightsBookedCount").html('');
			jQuery("#totalPerRoom").html('');
			jQuery("#nightCountReport").html("You have selected "+daysBooked+ " nights.").show();
			jQuery("#nightsBookedCount").val(daysBooked);
			var total = 0;
			jQuery.ajax({
			type: "POST",
  			url: "../wp-content/plugins/wp-easybooking/classes/booking.class.php",
  			data: "sID="+rID+"&bID="+bID+"&startRange="+jQuery('#eb_dateRangeStart').val()+"&endRange="+jQuery('#eb_dateRangeEnd').val()+"&getServicePrice=WITH_AJAX&abPath="+absPath,
  			success: function(resp){  
    			var res = resp.split("[-]");
    			var roomsAvailable = res[4];
    			var totalPerRoom = res[1];    			
				jQuery("#HbRepSrc").html(res[3]);//apo8hkeyoume tis HB times se hidden pedia sto div HbRepSrc 
				
    			if(roomsAvailable > 0 && roomsAvailable != ''){
    				jQuery("#totalPerRoom").val(totalPerRoom);
    				jQuery("#totalBookingPrice").html(totalPerRoom);
    				
    				jQuery("#bookPricingDetailsArea").html('Price for '+daysBooked+' nights : <strong>'+totalPerRoom+' <?php echo $businessCurrency; ?></strong>&nbsp;<a id="bookPricingDetailsAnalyzedAreaBtn" onclick="showPriceDetails()" class="littleEditBtns">Show details</a>').show();
    				jQuery("#bookPricingDetailsAreaContainer").show();
    				if(res[0] == "hasSeasons"){
    					jQuery("#checkSeasons").val('YES');
    					priceDetails = res[2].split("|");
    					priceDetailsStr = '';
    					for(i=1; i < priceDetails.length; i++ ){
	    					priceDetailsStr += priceDetails[i] + "<br />";
   	 				}
   	 			}
   	 			else{
   	 				priceDetailsStr = res[2];
   	 				jQuery("#checkSeasons").val('NO');
   	 			}
    				
					jQuery("#bookPricingDetailsAnalyzedArea").html(priceDetailsStr);
    				
    				var roomsAvailableSelect = document.getElementById('roomNumSelect');
    				
    				if ( roomsAvailableSelect.hasChildNodes() ){
  	  					while ( roomsAvailableSelect.childNodes.length >= 1 ){
        					roomsAvailableSelect.removeChild( cell.firstChild );       
    					} 
					}

    				for(i=1; i<=roomsAvailable; i++){
    					var roomNumOption = document.createElement("option");
						roomNumOption.text = i;
						roomNumOption.value = i;
						roomsAvailableSelect.appendChild(roomNumOption);
    				}
    				jQuery("#eb_setRoomNumInBookIt").show();
    				jQuery("#roomsSelectedCounter").html("1");
    				jQuery("#priceForSelRoomsArea").html(totalPerRoom);
    				jQuery("#eb_eachRoomDetails").show();
    				jQuery("#bookingReportArea").show();   			    			
    				jQuery("#eb_setOwnerInBookIt").show();
    				createRoomDetailArea(1);
    				
    			}//end of if there are available rooms
    			else{
					jQuery("#dateErrorMsgDiv").html('<p>There are no rooms available for this period for this room.</p> <p>Please change dates and try again!</p>');    				
    				jQuery("#dateErrorMsgArea").show();
    			}
  			}//end of ajax success
		});
		
			
		}
		else {
			jQuery("#dateErrorMsgDiv").html('<p>You have to select a date range first from the "Date range" field.</p> <p>Please make sure that your booking date does not start before today!</p>');    				
    		jQuery("#dateErrorMsgArea").show();
			return;	
		}
			
	});

	jQuery("#roomNumSelect").change(function(){
		jQuery("#roomsSelectedCounter").html('1');
		jQuery('#extraBedPriceRecord').html('');
		jQuery('#extraBedsSelectedArea').hide();
		jQuery('#HBpriceRecord').html('');
		jQuery('#HBselectedArea').hide();
		jQuery("#selectRoomNumMsgArea").html("You have selected "+ jQuery("#roomNumSelect").attr("value")+ " rooms");	
		jQuery("#roomsSelectedCounter").html(jQuery("#roomNumSelect").attr("value"));
		var roomNum = jQuery("#roomNumSelect").attr("value")
		var totalPerRoom = jQuery("#totalPerRoom").val();
		var totalForRooms = totalPerRoom * roomNum;
		jQuery("#priceForSelRoomsArea").html(totalForRooms);
		jQuery("#totalBookingPrice").html(totalForRooms);
		//for(i=1; i<=roomNum; i++){
		createRoomDetailArea(roomNum);
		//}
		//calculateTotal();

	});

function createRoomDetailArea(roomNum){
	jQuery("#eb_eachRoomDetails").html();
	var babiesNum = jQuery("#babiesAllowed").val();
	var extraBedsNum = jQuery("#extraBedsAvailable").val();
	var divName = "roomItem_";
	var textboxName = "roomItemGuestName_";
	var selBabiesName = "roomItemBabies_";
	var selBedName = "roomItemExtraBeds_";
	var elementStr = '<h3>Rooms details</h3>';
	
	for(rcount=1; rcount <= roomNum; rcount++){
		elementStr += '<p>';
		elementStr += '<div id="'+divName+rcount+'" name = "'+divName+rcount+'" class="plainBox">';
		elementStr += '<h3><i><?php echo $roomTitle; ?></i></h3>';
		
		elementStr += '<label>Guest full name: </label>';
		elementStr += '<input type="text" id="'+textboxName+rcount+'" style="width:300px" />';				

		if(babiesNum > 0){			
			elementStr += '<label> Babies: </label>';
			elementStr += '<select id="'+selBabiesName+rcount+'">';
			for(bcount = 0; bcount <= babiesNum; bcount++){
				elementStr += '<option value="'+bcount+'"> '+bcount+' </option>';
			}					
			elementStr += '</select>';
		}
		
		if(extraBedsNum > 0){
			elementStr += '<label> Extra beds: </label>';
			elementStr += '<select name="'+selBedName+rcount+'" id="'+selBedName+rcount+'" onChange="calcExtraBeds(\''+selBedName+rcount+'\', '+rcount+');">';
			for(bcount = 0; bcount <= extraBedsNum; bcount++){
				elementStr += '<option value="'+bcount+'"> '+bcount+' </option>';
			}					
			elementStr += '</select>&#42;';
			
		}
		elementStr += '<br /><label style="font-weight:normal;"><i>&#42;Extra bed price: <span id="roomExtraBedPrice_'+rcount+'"><?php echo $extraBedPrice; ?></span> <?php echo $businessCurrency; ?></i></label>';
		
		//for Half Board selection
		var hasSeasons = jQuery("#checkSeasons").val();
		var dayCount = jQuery("#nightsBookedCount").val();

		if(hasSeasons == "YES"){
			elementStr += '<br /><div class = "plainBox"><label>You can select which dates you desire Half Board meals&#42;&#42;</label><p>';
			for(night = 0; night < dayCount; night++){
				var HbDate = jQuery("#HBDateforNihgt_"+night).val();
				var HbPrice = jQuery("#HBPriceforNihgt_"+night).val();
				if(jQuery("#HBPriceforNihgt_"+night).val()){
					elementStr += '<input type="checkbox" id="HBchx<?php echo $rID;?>_'+rcount+'_'+night+'" name="HBchx_<?php echo $rID;?>_'+rcount+'" value="'+HbPrice+'" onclick = "calcExtraHBs(this.id, '+rcount+');"><label for="HBchx<?php echo $rID;?>_'+rcount+'_'+night+'">Half Board for '+HbDate+' <i style="font-weight: normal;">(Price: '+HbPrice+' <?php echo $businessCurrency; ?>)</i></label> <br />';	
				}
			}
			elementStr += '</p><label style="font-weight:normal;"><i>&#42;&#42;Half Board extra cost will be added only for the selected dates</i></label></div>';
			
		}
		else{
			if(jQuery("#HBPriceforNihgt").val()){
				elementStr += '<br /><div class = "plainBox"><label>You can select which dates you desire Half Board meals&#42;&#42;</label><p>';
				var startDate = jQuery("#dateRangeStartModified").val();

				for(night = 0; night < dayCount; night++){						
					var HbDate = new Date(startDate);
					HbDate.setDate(HbDate.getDate()+night);
								
					var HbDateStr = HbDate.toYYYYMMDDString();		
								
					var HbPrice = jQuery("#HBPriceforNihgt").val();
					//elementStr += '<input type="checkbox" id="HBchx<?php echo $rID;?>_'+rcount+'_'+HbDateStr+'" name="HBchx_<?php echo $rID;?>_'+rcount+'"><label for="HBchx<?php echo $rID;?>_'+rcount+'_'+HbDateStr+'">Half Board for '+HbDateStr+' <i style="font-weight: normal;">(Price: '+HbPrice+' <?php echo $businessCurrency; ?>)</i></label> <br />';	
					elementStr += '<input type="checkbox" id="HBchx<?php echo $rID;?>_'+rcount+'_'+night+'" name="HBchx_<?php echo $rID;?>_'+rcount+'" value="'+HbPrice+'" onclick = "calcExtraHBs(this.id, '+rcount+');"><label for="HBchx<?php echo $rID;?>_'+rcount+'_'+night+'">Half Board for '+HbDateStr+' <i style="font-weight: normal;">(Price: '+HbPrice+' <?php echo $businessCurrency; ?>)</i></label> <br />';
				}
				elementStr += '</p><label style="font-weight:normal;"><i>&#42;&#42;Half Board extra cost will be added only for the selected dates</i></label></div>';
			}	
		}				
		
		elementStr += '</div>';
		elementStr += '</p>';
		elementStr += '<input type="hidden" id="currentExtraBedsFor_'+rcount+'" value="">';
		//alert(elementStr+ "\n options:: "+optionsStr);
	}
	jQuery("#eb_eachRoomDetails").html(elementStr);
  
}

Date.prototype.toYYYYMMDDString = function () {return isNaN (this) ? 'NaN' : [this.getFullYear(), this.getMonth() > 8 ? this.getMonth() + 1 : '0' + (this.getMonth() + 1), this.getDate() > 9 ? this.getDate() : '0' + this.getDate()].join('-')}

function calcExtraHBs(selID, rcount){
	var HBprice = jQuery("#"+selID).val();
	var HBpriceRecord = jQuery("#HBpriceRecord").html();
	if (HBpriceRecord == '') HBpriceRecord = 0;
	var totalPrice = jQuery("#totalBookingPrice").html();
	if(jQuery("#"+selID).attr("checked")){		
		HBpriceRecord = parseFloat(HBpriceRecord) + parseFloat(HBprice);
		HBpriceRecord = Math.round(HBpriceRecord*100)/100;//gia na strogylopoihtai sta 2 pshfia
		totalPrice = parseFloat(totalPrice) + parseFloat(HBprice);
		totalPrice = Math.round(totalPrice*100)/100;//gia na strogylopoihtai sta 2 pshfia
	}
	else{
		HBpriceRecord = parseFloat(HBpriceRecord) - parseFloat(HBprice);
		HBpriceRecord = Math.round(HBpriceRecord*100)/100;//gia na strogylopoihtai sta 2 pshfia
		totalPrice = parseFloat(totalPrice) - parseFloat(HBprice);
		totalPrice = Math.round(totalPrice*100)/100;//gia na strogylopoihtai sta 2 pshfia
	}
	jQuery("#totalBookingPrice").html(totalPrice);
	jQuery("#HBpriceRecord").html(HBpriceRecord);	
	
	if(HBpriceRecord > 0) jQuery("#HBselectedArea").show('slow');
	else jQuery("#HBselectedArea").hide('slow');
}



function calcExtraBeds(selID, rcount){
	var extraBedPrice = <?php echo $extraBedPrice; ?>;
	var extraBedPriceRecord = jQuery("#extraBedPriceRecord").html();
	
	var totalPerRoom = jQuery("#totalPerRoom").val();
	
	var curExtraBeds = jQuery("#currentExtraBedsFor_"+rcount).val();
	var newExtraBeds = jQuery("#"+selID).val();
	var dayCount = jQuery("#nightsBookedCount").val();
	
	var subAmount = 0;
	var addAmount = 0;
	
	if(curExtraBeds == "") curExtraBeds = 0;
	if(curExtraBeds > 0){
		subAmount = (extraBedPrice * curExtraBeds) * dayCount;
	}
	addAmount = (extraBedPrice * newExtraBeds) * dayCount;
	
	extraBedPriceRecord = extraBedPriceRecord - subAmount + addAmount;
	jQuery("#extraBedPriceRecord").html(extraBedPriceRecord);
	if(extraBedPriceRecord > 0){	
		jQuery("#extraBedsSelectedArea").show('slow');
	}
	else{
		jQuery("#extraBedsSelectedArea").hide('slow');
	}
	
	var HBpriceRecord = jQuery("#HBpriceRecord").html();
	if(HBpriceRecord == '') HBpriceRecord = 0;
	HBpriceRecord = parseFloat(HBpriceRecord);
	var oldRoomsTotalPrice = jQuery('#priceForSelRoomsArea').html();
	var newRoomsTotalPrice = parseFloat(oldRoomsTotalPrice) + extraBedPriceRecord + HBpriceRecord;
	newRoomsTotalPrice = Math.round(newRoomsTotalPrice*100)/100;//gia na strogylopoihtai sta 2 pshfia
	jQuery("#totalBookingPrice").html(newRoomsTotalPrice);
	
	jQuery("#currentExtraBedsFor_"+rcount).val(newExtraBeds);
	
}

function showPriceDetails(){
	if(jQuery('#bookPricingDetailsAnalyzedArea').css("display") == "block"){
		jQuery("#bookPricingDetailsAnalyzedArea").hide();
		jQuery("#bookPricingDetailsAnalyzedAreaBtn").html('Show details');
	}
	else{
		jQuery("#bookPricingDetailsAnalyzedArea").show();
		jQuery("#bookPricingDetailsAnalyzedAreaBtn").html('Hide details');
	}
}
function makeDate(dateStr){
	dateStr = dateStr.split('/');
	var newDate = new Date(dateStr[2], dateStr[1] - 1, dateStr[0]); //Month is 0-11 in JavaScript
	return newDate;
}
function beforeTodayDate(dateStr){
	var today = new Date();
	var checkDate = new Date(dateStr);
	if(today.getMonth() == checkDate.getMonth() && today.getDate() == checkDate.getDate()) return false;//gia na mporoume na bookaroume thn shmerinh hmera
	if(today > checkDate) return true;
	else return false;
}
function calculateDaysBetweenDates(date1, date2){
	var one_day=1000*60*60*24
	//Calculate difference btw the two dates, and convert to days
	var days = Math.ceil((date2.getTime()-date1.getTime())/(one_day));
	return days;
}

function showUserList(){
	jQuery('#eb_userlist').show("fast");	
}
function hideUserList(){
	jQuery('#eb_userlist').hide("fast");	
}
function setOwner(owners_id, lname, fname, email){
	jQuery("#eb_ownerBtn").val(lname+ ' '+fname);
	jQuery("#ebOwnerID").val(owners_id);
	jQuery("#cnf_clientID").val(owners_id);
	jQuery("#BR_clientLname").html(lname);
	jQuery("#BR_clientFname").html(fname);
	jQuery("#BR_clientEmail").html(email);
	jQuery("#debtor_lName").val(lname);
	jQuery("#debtor_fName").val(fname);
	jQuery("#debtor_email").val(email);


	hideUserList();	
}

function setDebtorInfo(id, bookReportIDinvolved){
	jQuery("#"+bookReportIDinvolved).html(jQuery("#"+id).val());
	jQuery("#eb_ownerBtn").val("Registered clients");
}
</script>

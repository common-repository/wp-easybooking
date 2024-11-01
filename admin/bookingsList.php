<?php
//=============================================
//==========BOOKINGS LIST START================

global $wpdb;
global $ebPluginFolderName;
//global $current_user;
global $eb_adminUrl;
global $table_prefix;
$user_id = get_current_user_id();
$user_info = get_userdata( $user_id );
$bID='';
$rID='';
$userType = '';
?>

<h2>Bookings</h2>
<div class="information">
	<a id="infoDisp" onclick="jQuery('.infoContent').toggle('slow');" style="color:#f19c06;">
		<img  src="<?php echo WP_CONTENT_URL; ?>/plugins/<?php echo $ebPluginFolderName; ?>/images/infoImg.png"> So what do I do here?
	</a>
	<div class="infoContent" style="display:none;color:#666;font-size:14px;">
		<u>Here you can see all bookings of a business.</u><br />
		All bookings have been categorized by their status as pending, confirmed, completed, canceled or expired. By clicking on each of these categories the corresponding bookings will be displayed.<br />
		<br /><b>Pending</b> are all bookings the have been made and nothing happened to change their status. In other words a user is interested in a room and the owner has to respond.
		<br /><b>Confirmed</b> are the bookings that were Pending and the owner approved them. So the owner has to change the status from Pending to Confirmed.
		<br /><b>Completed</b> are the bookings that have arrived and departed. The status in this case changes automatically from Confirmed to Completed.
		<br /><b>Canceled</b> are the bookings that are not approved from the owner for two reasons, either the user has not payed for the booking or the user has informed about not arriving. The owner has to set the booking status to Canceled manually.
		<br /><b>Expired</b> are the bookings that were Pending and the departure date has passed without being confirmed. The status is set automatically to Expired.<br />
		<br />You have the ability to select bookings that arrive, depart or were made in a certain date, from the dates drop down area. <br /><br />
		By clicking on the ID of a booking you can view its details. <br />
		<br />
		The status of each booking is automatically set to "Pending", which means that a user has made a booking and perhaps has payed the whole ammount of the booking.<br />
		When you are informed that you have a new booking pending, first check if it has been payed. If so, you can then set it as confirmed from the details area of the booking.<br />
		<br />You can also cancel a booking if the user does not pay the amount of the booking.<br />
		It is important to set bookings as canceled when you know that they will not arrive. 
		When a user is searching for a room at a specific date range, all pending bookings are <u>not considered as available</u>.  
		<br /><br />One last thing you need to be cautious is when you have already set a booking as canceled and you need to change it to confirmed. Though you do have the ability to do that, first you need to check if you still have the appropriate rooms which were booked. The best solution, especially when a canceled booking has more than one rooms, is to prompt users to make the booking again. 	
		 
		 
		 <br /><div align="center" style="width:100%"><a style="color:#f19c06;" onclick="jQuery('.infoContent').hide('slow');">OK! Got it, hide info</a></div>
		<br /><br />
	</div>
</div>

<?php
$editBusinessLink = '';
if($user_info->user_level == 0) {
	$bListPage = 'bookings_menu';
	$userType= 'Businessman';
	$editBusinessLink = 'business_list';
}
else {
	$bListPage = 'bookings_menu';
	$userType =  'Administrator';
	$editBusinessLink = 'busines_menu';
}

if(!isset($_REQUEST['bID']) || $_REQUEST['bID'] == ''){
?>
<script type="text/javascript" >
document.location = "admin.php?page=<?php echo $bListPage; ?>";
</script>
<?php
}

if(isset($_REQUEST['bID']) && $_REQUEST['bID'] !=''){
//================================================================
//==========================bID IS SET============================
	$bID = check_input ($_REQUEST["bID"]);	
	if($userType == 'Businessman'){
		$isTrueOwnerResult = $wpdb->get_var('select COUNT(ID) from '.$table_prefix.'posts where ID = '.$bID.' and post_author = '.$user_info->ID);
		if($isTrueOwnerResult == 0) die('<div class="error">Permission error</div>');
	}
	$eb_BusinessOwner = '';
	$bookDataStr = '';
	$bookActionTitle = '';
	$pendingBookingsStr = '';
	$confBookingsStr = '';
	$cancBookingsStr = '';
	$expBookingsStr = '';
	$compBookingsStr = '';
	$bookingCounter = 0;
	$bookingsForMonth = '';

	$minBookMonth = $wpdb->get_row('select booking_date from eb_bookingdata where businessID = '.$bID.' order by 1 limit 1');

	if ($minBookMonth->booking_date != '') $minBookMonth = $minBookMonth->booking_date;
	$fetchBookingSwitch = 'dateRange_start';
	$fetchBookingSwitchExplanation = '';
	$reqDay= '';$reqmonth = '';$reqYear = '';$reqswitch = 'start';
	if(isset($_REQUEST['mnthbk']) && $_REQUEST['mnthbk'] != ''){
		if(isset($_REQUEST['stch']) && $_REQUEST['stch'] != ''){
			$fetchBookingSwitch = addslashes($_REQUEST['stch']);
			$reqswitch = $fetchBookingSwitch;
			if($fetchBookingSwitch == "start") $fetchBookingSwitch = 'dateRange_start'; 
			if($fetchBookingSwitch == "end") $fetchBookingSwitch = 'dateRange_end';
			if($fetchBookingSwitch == "made") $fetchBookingSwitch = 'booking_date';
			$fetchBookingSwitchArr = explode('-',$_REQUEST['mnthbk']); 
			if($fetchBookingSwitchArr[0] != '' && $fetchBookingSwitchArr[0] != '%') $reqYear = $fetchBookingSwitchArr[0];
			if($fetchBookingSwitchArr[1] != '' && $fetchBookingSwitchArr[1] != '%') $reqmonth = $fetchBookingSwitchArr[1];
			if($fetchBookingSwitchArr[2] != '' && $fetchBookingSwitchArr[2] != '%') $reqDay = $fetchBookingSwitchArr[2];
			
		}
		$bookingsForMonth = addslashes($_REQUEST['mnthbk']);
		$bookingsForMonth = ' '.$fetchBookingSwitch.' LIKE "'.$bookingsForMonth.'%" AND ';
	}
	$bBookSQL = 'select * from eb_bookingdata where '.$bookingsForMonth.' businessID ='. $bID;

	$businessData = $wpdb->get_row('select post_author, post_title from '.$table_prefix.'posts where ID = '.$bID. ' and post_parent=0');
	if(!empty($businessData)){
		$eb_BusinessTitle = __( $businessData->post_title );
		
		$businessCurrency =  get_post_meta($bID, "eb_currency");
		if(!empty($businessCurrency)) $businessCurrency = $businessCurrency[0]; else $businessCurrency ='';
		
		$eb_BusinessStars =  get_post_meta($bID, "eb_stars");
		if(!empty($eb_BusinessStars)) $eb_BusinessStars = ' <i style="font-size:10px;">('.$eb_BusinessStars[0].' stars)</i>'; else $eb_BusinessStars ='';		
		
		$eb_BusinessEmail = get_post_meta($bID, "eb_email");
		if(!empty($eb_BusinessEmail)) $eb_BusinessEmail = $eb_BusinessEmail[0]; else $eb_BusinessEmail ='NOT SET';
		
		$eb_BusinessAddress =  get_post_meta($bID, "eb_address");		
		if(!empty($eb_BusinessAddress)) $eb_BusinessAddress = $eb_BusinessAddress[0]; else $eb_BusinessAddress ='NOT SET';		
		
		$eb_BusinessAddressNumber =  get_post_meta($bID, "eb_addressNum");
		if(!empty($eb_BusinessAddressNumber)) $eb_BusinessAddressNumber = $eb_BusinessAddressNumber[0]; else $eb_BusinessAddressNumber ='';
		
		$eb_BusinessTel1 = get_post_meta($bID, "eb_tel1");
		if(!empty($eb_BusinessTel1)) $eb_BusinessTel1 = $eb_BusinessTel1[0]; else $eb_BusinessTel1 ='NOT SET';
		
		$eb_BusinessTel2 = get_post_meta($bID, "eb_tel2");
		if(!empty($eb_BusinessTel2)) $eb_BusinessTel2 = ' , '.$eb_BusinessTel2[0]; else $eb_BusinessTel2 ='';
		
		$eb_BusinessFax = get_post_meta($bID, "eb_fax");
		if(!empty($eb_BusinessFax)) $eb_BusinessFax = $eb_BusinessFax[0]; else $eb_BusinessFax ='NOT SET';
		
		$eb_BusinessZip = get_post_meta($bID, "eb_zip");
		if(!empty($eb_BusinessZip)) $eb_BusinessZip = $eb_BusinessZip[0]; else $eb_BusinessZip ='NOT SET';
		
		$eb_BusinessCountry = 'NOT SET';
		$eb_BusinessRegion = 'NOT SET';
		$cityRes = 'NOT SET';
		$eb_BusinessCityID = get_post_meta($bID, "eb_cityID");
		if(!empty($eb_BusinessCityID)) {
			
			$eb_BusinessCityID = $eb_BusinessCityID[0]; 
			global $countriesTable, $regionsTable, $citiesTable;
			$cityRes = $wpdb->get_row('select CountryID, RegionID, City from '.$citiesTable.' where CityId = '.$eb_BusinessCityID);
			$eb_BusinessRegionID = $cityRes->RegionID;
			
			$eb_BusinessCountryAr = $wpdb->get_row('select Country from '.$countriesTable.' where CountryId = '.$cityRes->CountryID);
			$eb_BusinessCountry = $eb_BusinessCountryAr->Country;
			$eb_BusinessRegionAr = $wpdb->get_row('select Region from '.$regionsTable.' where RegionID = '.$cityRes->RegionID);
			$eb_BusinessRegion = $eb_BusinessRegionAr->Region;
		}	
		else $eb_BusinessCityID ='';				
		 
		$ownerData = get_userdata( $businessData->post_author );	
		$bookActionTitle = 'All bookings for <font style="font-size:14px"><i>'.$eb_BusinessTitle.'</i></font>';
	}
	else {
		die('<div class="error">No such business. Please go back or contact your system administrator for further details</div>');	
	}
	
	?>
	<table class="widefat">
		<thead>
			<tr>
				<th>
					<a href="<?php echo $eb_adminUrl; ?>?page=<?php echo $editBusinessLink; ?>&bID=<?php echo $bID; ?>">
						<em style="font-size:18px"><?php echo $eb_BusinessTitle.$eb_BusinessStars; ?></em></a> Bookings
				</th>
			</tr>
			<?php if($userType == 'Administrator'){?>
			<tr>
				<td>
					<table style="border:none"> 
						<tr>
							<td style="border:none;color:#666;padding-left:10px">
								<b><u style="color:#666;">Contact business</u></b><br />
								Email: <b style="color:#666;"><?php echo $eb_BusinessEmail; ?></b><br />
								Tel: <b style="color:#666;"><?php echo $eb_BusinessTel1.$eb_BusinessTel2 ?></b><br />
								Fax: <b style="color:#666;"><?php echo $eb_BusinessFax; ?></b>
								
							</td>
							<td style="border:none;padding-left:10px;">
								<b><u style="color:#666;">Business Location</u></b><br />
								Country: <b style="color:#666;"><?php _e( $eb_BusinessCountry); ?></b><br />
								Region: <b style="color:#666;"><?php _e( $eb_BusinessRegion); ?></b><br />
								City: <b style="color:#666;"><?php _e( $cityRes->City); ?></b><br />
							</td>
							<td style="border:none;padding-left:10px;">
								<b><u style="color:#666;">Business Address</u></b><br />
								Address: <b style="color:#666;"><?php _e( $eb_BusinessAddress.' '.$eb_BusinessAddressNumber); ?></b><br />
								ZIP: <b style="color:#666;"><?php _e( $eb_BusinessZip); ?></b><br />								
							</td>
							<td style="border:none;padding-left:10px;">
								<b style="color:#666;"><u>Contact Owner</u></b><br />
								Name: <b style="color:#666;"><?php echo $ownerData->last_name .' '.$ownerData->first_name; ?></b><br />
								Email: <b style="color:#666;"><?php echo $ownerData->user_email; ?></b>
							</td>
						</tr>
					</table>
				</td>
			</tr>
			<?php } ?>
		</thead>
	</table>	
	<?php
	//========================================================================================================
	//=============================PENDING AND REST STATUS SELECTED FROM QSTRING==============================
	$allBookingsStr = '';
	
	$bBookingsPending = $wpdb->get_var("SELECT COUNT(bookingID) from eb_bookingdata where businessID =". $bID.' and booking_status = "Pending"');
	if($bBookingsPending > 0) $pendingBookingsStr = '<a href="'.$eb_adminUrl.'?page=bookings_menu&bID='.$bID.'&stat=p" class="littleEditBtns" style="font-size:11px"><b>Pending <i>('.$bBookingsPending.')</i></b></a>';
	if(isset($_REQUEST['stat']) && $_REQUEST['stat'] == 'p'){
		$bBookSQL .= ' AND booking_status = "Pending"';
		$bookActionTitle = 'Pending bookings for <font style="font-size:14px"><i>'.$eb_BusinessTitle.'</i></font>';
		$allBookingsStr = '<a href="'.$eb_adminUrl.'?page=bookings_menu&bID='.$bID.'" class="littleEditBtns" style="font-size:11px"><b>All bookings</b></a>';
		$pendingBookingsStr = '';		
		$bookingCounter = $bBookingsPending;		 	 	
	}

	$bBookingsConf = $wpdb->get_var("SELECT COUNT(bookingID) from eb_bookingdata where businessID =". $bID.' and booking_status = "Confirmed"');
	if($bBookingsConf > 0) $confBookingsStr = '<a href="'.$eb_adminUrl.'?page=bookings_menu&bID='.$bID.'&stat=conf" class="littleEditBtns" style="font-size:11px"><b>Confirmed <i>('.$bBookingsConf.')</i></b></a>';
	if(isset($_REQUEST['stat']) && $_REQUEST['stat'] == 'conf'){
		$bBookSQL .= ' AND booking_status = "Confirmed"';
		$bookActionTitle = 'There are '.$bBookingsConf.' confirmed bookings for <font style="font-size:14px"><i>'.$eb_BusinessTitle.'</i></font>';
		$allBookingsStr = '<a href="'.$eb_adminUrl.'?page=bookings_menu&bID='.$bID.'" class="littleEditBtns" style="font-size:11px"><b>All bookings</b></a>';
		$confBookingsStr = '';
		$bookingCounter = $bBookingsConf;
	}
	
	$bBookingsComp = $wpdb->get_var("SELECT COUNT(bookingID) from eb_bookingdata where businessID =". $bID.' and booking_status = "Completed"');
	if($bBookingsComp > 0) $compBookingsStr = '<a href="'.$eb_adminUrl.'?page=bookings_menu&bID='.$bID.'&stat=comp" class="littleEditBtns" style="font-size:11px"><b>Completed <i>('.$bBookingsComp.')</i></b></a>';
	if(isset($_REQUEST['stat']) && $_REQUEST['stat'] == 'comp'){
		$bBookSQL .= ' AND booking_status = "Completed"';
		$bookActionTitle = 'There are '.$bBookingsComp.' completed bookings for <font style="font-size:14px"><i>'.$eb_BusinessTitle.'</i></font>';
		$allBookingsStr = '<a href="'.$eb_adminUrl.'?page=bookings_menu&bID='.$bID.'" class="littleEditBtns" style="font-size:11px"><b>All bookings</b></a>';
		$compBookingsStr = '';
		$bookingCounter = $bBookingsComp;
	}
	
	$bBookingsCanc = $wpdb->get_var("SELECT COUNT(bookingID) from eb_bookingdata where businessID =". $bID.' and booking_status = "Canceled"');
	if($bBookingsCanc > 0) $cancBookingsStr = '<a href="'.$eb_adminUrl.'?page=bookings_menu&bID='.$bID.'&stat=canc" class="littleEditBtns" style="font-size:11px"><b>Canceled <i>('.$bBookingsCanc.')</i></b></a>';
	if(isset($_REQUEST['stat']) && $_REQUEST['stat'] == 'canc'){
		$bBookSQL .= ' AND booking_status = "Canceled"';
		$bookActionTitle = 'There are '.$bBookingsCanc.' canceled bookings for <font style="font-size:14px"><i>'.$eb_BusinessTitle.'</i></font>';
		$allBookingsStr = '<a href="'.$eb_adminUrl.'?page=bookings_menu&bID='.$bID.'" class="littleEditBtns" style="font-size:11px"><b>All bookings</b></a>';
		$cancBookingsStr = '';
		$bookingCounter = $bBookingsCanc;
	}
	
	$bBookingsExp = $wpdb->get_var("SELECT COUNT(bookingID) from eb_bookingdata where businessID =". $bID.' and booking_status = "Expired"');
	if($bBookingsExp > 0) $expBookingsStr = '<a href="'.$eb_adminUrl.'?page=bookings_menu&bID='.$bID.'&stat=exp" class="littleEditBtns" style="font-size:11px"><b style="color:darkred">Expired <i>('.$bBookingsExp.')</i></b></a>';
	if(isset($_REQUEST['stat']) && $_REQUEST['stat'] == 'exp'){
		$bBookSQL .= ' AND booking_status = "Expired"';
		$bookActionTitle = 'There are '.$bBookingsExp.' expired bookings for <font style="font-size:14px"><i>'.$eb_BusinessTitle.'</i></font>';
		$allBookingsStr = '<a href="'.$eb_adminUrl.'?page=bookings_menu&bID='.$bID.'" class="littleEditBtns" style="font-size:11px"><b>All bookings</b></a>';
		$expBookingsStr = '';
		$bookingCounter = $bBookingsExp;
	}
	//==================================================================
	//======================== END OF PENDING===========================
	
	//==================================================================
	//============================ PAGING =============================
	$today = mktime(0,0,0,date("m"),date("d"),date("Y"));
	$rows_per_page = 8;
	$current = 1;
	if(isset($_REQUEST['paged']))
		$limit = " LIMIT " . ($_REQUEST['paged'] - 1) * $rows_per_page  . ", " . $rows_per_page;
	else $limit = " LIMIT 0" * $rows_per_page  . ", " . $rows_per_page;
	 if(!isset($_REQUEST['paged'])) {
	 	$limit = " LIMIT ".$rows_per_page;
	 	$current = 1;
	 }
	 else $current = $_REQUEST['paged'];

	$allBookings = $wpdb->get_results($bBookSQL.' order by dateRange_start desc ' . $limit);	
	$allBookingsLim = $wpdb->get_results($bBookSQL);
	 
	global $wp_rewrite;
 
	$pagination_args = array(
 		'base' => @add_query_arg('paged','%#%'),
 		'format' => '',
 		'total' => ceil(sizeof($allBookingsLim)/$rows_per_page),
 		'current' => $current,
 		'show_all' => false,
 		'type' => 'plain',
	);
 
		//if( $wp_rewrite->using_permalinks() )
 	//$pagination_args['base'] = user_trailingslashit( trailingslashit( remove_query_arg('s',get_pagenum_link(1) ) ) . 'page/%#%/', 'paged');
 
	if( !empty($wp_query->query_vars['s']) )
 		$pagination_args['add_args'] = array('s'=>get_query_var('s'));

 
	$start = ($current - 1) * $rows_per_page;
	//$end = $start + $rows_per_page;
	//$end = (sizeof($rows) < $end) ? sizeof($rows) : $end;

	//==================================================================
	//======================== END OF PAGING===========================

	if(!empty($allBookings) && !isset($_REQUEST['book'])){
		$bookActionTitle .= ' ('.sizeof($allBookingsLim).' bookings)';
		$bookingCounter = sizeof($allBookings);
		$bookDataStr = '<table class="widefat" id="dataTableTrHov">';
		$bookDataStr .= '<thead>';
		$bookDataStr .= '<tr>';
			$bookDataStr .= '<th></th><th>Book ID</th><th>Arrival</th><th>Departure</th><th>Nights</th><th>Number of rooms</th><th>Cost</th><th>Status</th><th>Client name</th><th>Date of Booking</th>';
		$bookDataStr .= '</thead>';
		$bookArivalD='';
		$bookDepartureD='';
		if(!isset($_REQUEST['paged'])) $pstart = 0;
		else $pstart = (( int)$_REQUEST['paged'] - 1) * $rows_per_page;
		$bookingsCounter = $pstart + 1;   
 		foreach($allBookings as $booking){
			$bookArival = explode('00:00:00',$booking->dateRange_start);
			$bookArivalE = explode('-', $bookArival[0]);	
			$bookArivalT = mktime(0,0,0, (int)$bookArivalE[1], (int)$bookArivalE[2], (int)$bookArivalE[0]);
			$bookArivalD = date('d M Y', $bookArivalT);
			
			$bookDeparture = explode('00:00:00',$booking->dateRange_end);
			$bookDepartureE = explode('-', $bookDeparture[0]);	
			$bookDepartureT = mktime(0,0,0, (int)$bookDepartureE[1], (int)$bookDepartureE[2], (int)$bookDepartureE[0]);
			$bookDepartureD = date('d M Y', $bookDepartureT);
			$bookDataStr .= '<tr>';
			$bookDataStr .= '<td>'.$bookingsCounter.'</td>';
			$bookDataStr .= '<td><a href="'.$eb_adminUrl.'?page=bookings_menu&bID='.$bID.'&book='.$booking->bookingID.'"><b>'.$booking->bookingID.'</b><div class="row-actions"><span class="edit">Details</span></div></a></td>';
			$bookDataStr .= '<td>'.$bookArivalD.'</td>';
			$bookDataStr .= '<td>'.$bookDepartureD.'</td>';
			$bookDataStr .= '<td>'.$booking->bookedNights.' nights</td>';
			$bookDataStr .= '<td>'.$booking->numberOfRooms.' rooms</td>';
			$bookDataStr .= '<td>'.$booking->booking_totalBCUR.' '.$businessCurrency.'</td>';
			$bStatus = $booking->booking_status;
			if($booking->booking_status == "Confirmed" && $today > $bookDepartureT){
				$wpdb->query('update eb_bookingdata set booking_status = "Completed" where bookingID = '.$booking->bookingID);
				$wpdb->query('update eb_bookingroomdata set canceled = "NO" where bookingID = '.$booking->bookingID);	
				$bStatus = 'Completed';		
			}
			if($booking->booking_status == "Pending" && $today > $bookArivalT){
				$wpdb->query('update eb_bookingdata set booking_status = "Expired" where bookingID = '.$booking->bookingID);
				$wpdb->query('update eb_bookingroomdata set canceled = "NO" where bookingID = '.$booking->bookingID);	
				$bStatus = 'Expired';		
			}
			$bookDataStr .= '<td>'.$bStatus.'</td>';
			$bookDataStr .= '<td>'.$booking->customer_lname.' '.$booking->customer_fname.' <br>(email:'.$booking->customer_email.')</td>';
			$bookDataStr .= '<td>'.$booking->booking_date.'</td>';
			
			$bookDataStr .= '</tr>';
			$bookingsCounter++;
		}
		$bookDataStr .= '</table>';
	}
	else $bookDataStr = '<div style= "color: #666;border:1px solid #ff9494; width: 100%; background: #ffa8a8; font-size: 12px;text-align:center;">There might be something missing from the data you entered, or there are no bookings yet. Please try again</div>'; 
	
	//==================================================================
	//==========================bookID IS SET===========================
	
	//leptomeries krathshs
	if(isset($_REQUEST['book']) && $_REQUEST['book'] !=''){				
		$book = check_input ($_REQUEST["book"]);
		$bookBelongsToBusiness = $wpdb->get_row('select businessID from eb_bookingdata where bookingID = '.$book);
		
		if( $bookBelongsToBusiness->businessID != $bID) die('<div class="error">You have no permission to view this booking.Sorry!</div>'); 
		
		$allBookingsStr = '<a href="'.$eb_adminUrl.'?page=bookings_menu&bID='.$bID.'" class="littleEditBtns"><b>Back to bookings</b></a>';
		
		$bBookSQL .= ' AND bookingID = '.$book;
		
		//update deposit payed 
		if( isset($_POST['updateDeposit']) && $_POST['updateDeposit'] == "YES"){
			$bookingDataForStatus = $wpdb->get_row($bBookSQL);
			$addDepositAmount = addslashes( $_POST['booking-deposit-amount'] );
			
			add_filter('wp_mail_content_type',create_function('', 'return "text/html"; '));
	   	add_filter('wp_mail_from', create_function('', 'return "'.get_bloginfo('admin_email').'"; '));
			add_filter('wp_mail_from_name', create_function('', 'return "'.get_bloginfo('name').'"; '));
			
			$bookingPaymentAddedMailSubject = '['.$eb_BusinessTitle.'@'.get_bloginfo('name').'] Payment received';
			 
							
			if( $addDepositAmount != '' && $addDepositAmount > 0 ){
				$newDeposit = $wpdb->get_var('select booking_deposit from eb_bookingdata where bookingID = '.$book);
				if( $newDeposit == '' ) $newDeposit = 0;
				$newDeposit += $addDepositAmount; 
				if($wpdb->query('update eb_bookingdata set booking_deposit = "'.$newDeposit.'" where bookingID = '.$book)){
					$bookingPaymentAddedMailContent = 'Hello Mr/Mrs <b>'.$bookingDataForStatus->customer_lname.' '.$bookingDataForStatus->customer_fname.'</b>,<br/><br />';
					$bookingPaymentAddedMailContent .= $eb_BusinessTitle.' received a payment of <b>'.$addDepositAmount.' '.$_POST['depositCur'].'</b> for booking <b>#'.$book.'</b> <br /><br />This amount has been added to your booking deposit.<br /><br />Please go to your booking details and check for any balance remaining.';
					
					
					$bookingPaymentAddedMailContent .='<table style="border:1px solid #ccc" cellpading="5" cellspacing="5">
					<tr>
						<td colspan="2" style="background-color:#528ae7;color:#fff;font-size:14px"><b>Hotel information</b></td>
					</tr>				
					<tr>
						<td>Hotel name: </td>
						<td><b>'.$eb_BusinessTitle.'</b></td>
					</tr>	
					<tr valign="top">
						<td>Hotel location: </td>
						<td>Country:<b>';
								$bookingPaymentAddedMailContent .= __( $eb_BusinessCountry);
								$bookingPaymentAddedMailContent .= '</b><br />Region <b>';
								$bookingPaymentAddedMailContent .= __($eb_BusinessRegion);
								$bookingPaymentAddedMailContent .= '</b><br />City: <b>';
								$bookingPaymentAddedMailContent .= __($cityRes->City);								
						$bookingPaymentAddedMailContent .= '</td>
					</tr>						
					</table>
					<br />
					<table style="border:1px solid #ccc" cellpading="5" cellspacing="5">
					<tr>
						<td colspan="2" style="background-color:#528ae7;color:#fff;font-size:14px"><b>Booking ID: #'.$book.'</b></td>
					</tr>	
					<tr valign="top">
						<td>Number of rooms: </td>
						<td><b>'.$bookingDataForStatus->numberOfRooms.'</b></td>
					</tr>
					<tr>
						<td>Arriving at: </td>
						<td><b>'.$bookArivalD.'</b></td>
					</tr>	
					<tr>
						<td>Departing at: </td>
						<td><b>'.$bookDepartureD.'</b></td>
					</tr>	
					<tr>
						<td>Number of nights: </td>
						<td><b>'.$bookingDataForStatus->bookedNights.'</b></td>
					</tr>
					<tr>
						<td>Price: </td>
						<td><b>'.$bookingDataForStatus->booking_total.' '.$bookingDataForStatus->booking_currency.' </b><i>('.$bookingDataForStatus->booking_totalBCUR.' '.$businessCurrency.')</i></td>
					</tr>						
					<tr>
						<td>Amount payed: </td>
						<td><b>'.$newDeposit.' '.$bookingDataForStatus->booking_currency.' </b></td>
					</tr>';
					$balanceRemained = number_format($bookingDataForStatus->booking_total, 2) - number_format($newDeposit, 2);
					$bookingPaymentAddedMailContent .= '<tr>
						<td>Balance remaining: </td>
						<td><b>'.$balanceRemained.' '.$bookingDataForStatus->booking_currency.' </b></td>
					</tr>
					</table>';
					
					$bookingPaymentAddedMailContent .= '<br/><br />Thank you for using <a href="'.get_bloginfo('url').'">'.get_bloginfo('name').'</a> for your bookings!';
					
					
					wp_mail($bookingDataForStatus->customer_email,$bookingPaymentAddedMailSubject , $bookingPaymentAddedMailContent);
					echo '<div style="background-color: #ebf8a4;color: #999; -moz-border-radius:5px;-webkit-border-radius:5px;border-radius:5px;-moz-box-shadow: 0 1px 1px #fff inset;-webkit-box-shadow: 0 1px 1px #fff inset;box-shadow:  0 1px 1px #fff inset;border:1px solid;background-position: 99% 50%;padding:10px 10px 10px 10px;">
						Payment of <b>'.$addDepositAmount.' '.$_POST['depositCur'].'</b> was added succesfully. <em>(New deposit set to '.$newDeposit.' '.$_POST['depositCur'].')</em>
					</div>';
				}
				else echo '<div style= "color: #666;border:1px solid #ff9494; width: 100%; background: #ffa8a8; padding-left: 5px; font-size: 12px;">The amount you entered could not be added. Please try again or contact system administrator</div>';
			}
			else echo '<div style= "color: #666;border:1px solid #ff9494; width: 100%; background: #ffa8a8; padding-left: 5px; font-size: 12px;"><b>Error adding deposit amount: </b>The amount to be added must be greater than 0 '.$_POST['depositCur'].'</div>';
		}
		//=====FIX PAYMENT====
		if( isset( $_POST["new-payed-amount"] ) && $_POST["new-payed-amount"] != '' ){
			$newDeposit = number_format( addslashes( $_POST["new-payed-amount"] ),2 );
			if( $newDeposit != '' ){
				$wpdb->query('update eb_bookingdata set booking_deposit = "'.$newDeposit.'" where bookingID = '.$book);
				echo '<div style="background-color: #ebf8a4;color: #999; -moz-border-radius:5px;-webkit-border-radius:5px;border-radius:5px;-moz-box-shadow: 0 1px 1px #fff inset;-webkit-box-shadow: 0 1px 1px #fff inset;box-shadow:  0 1px 1px #fff inset;border:1px solid;background-position: 99% 50%;padding:10px 10px 10px 10px; text-align:center;">
				The new balance has been set successfully to <b>'.$newDeposit.' '.$_POST['depositCur'].'</b>. !
				</div>
				';
			}
			else echo '<div style= "color: #666;border:1px solid #ff9494; width: 100%; background: #ffa8a8; padding-left: 5px; font-size: 12px;"><b>Error correcting deposit amount </b>Please check the value you enter. Must be a valid number.</div>';
		}
		//update status
		if(isset($_REQUEST['statset'])){
			$newStat = check_input ($_REQUEST["statset"]);
			$bookingDataForStatus = $wpdb->get_row($bBookSQL);
			$hasUpdateError = false;
			//echo '<br />Change book stat: '.$bookingDataForStatus->customer_email.' - '.$eb_BusinessEmail;
			if($newStat == "confirm"){
				if($wpdb->query('update eb_bookingdata set booking_status = "Confirmed", booking_canceled_by_user = "", booking_cancelation_cost ="", booking_cancelation_date = "" where bookingID = '.$book)){
 
				}else $hasUpdateError = true;
				if($wpdb->query('update eb_bookingroomdata set canceled = "NO" where bookingID = '.$book)){

				}else $hasUpdateError = true;
				if(!$hasUpdateError){
					$bookArival = explode('00:00:00',$bookingDataForStatus->dateRange_start);
					$bookArivalE = explode('-', $bookArival[0]);	
					$bookArivalT = mktime(0,0,0, (int)$bookArivalE[1], (int)$bookArivalE[2], (int)$bookArivalE[0]);
					$bookArivalD = date('d M Y', $bookArivalT);
			
					$bookDeparture = explode('00:00:00',$bookingDataForStatus->dateRange_end);
					$bookDepartureE = explode('-', $bookDeparture[0]);	
					$bookDepartureT = mktime(0,0,0, (int)$bookDepartureE[1], (int)$bookDepartureE[2], (int)$bookDepartureE[0]);
					$bookDepartureD = date('d M Y', $bookDepartureT);
			
					//$bookingStatusMailSubject = $eb_BusinessTitle.' has confirmed your booking';
					$bookingStatusMailSubject = '['.$eb_BusinessTitle.'@'.get_bloginfo('name').'] - Your booking has been confirmed';	
					$bookingStatusMailMsg = 'Hello Mr/Mrs <b>'.$bookingDataForStatus->customer_lname.' '.$bookingDataForStatus->customer_fname.'</b>
					<br /><br />Your booking has been <b>confirmed</b>!
					<br /><br />
					<table style="border:1px solid #ccc" cellpading="5" cellspacing="5">
					<tr>
						<td colspan="2" style="background-color:#528ae7;color:#fff;font-size:14px"><b>Hotel information</b></td>
					</tr>				
					<tr>
						<td>Hotel name: </td>
						<td><b>'.$eb_BusinessTitle.'</b></td>
					</tr>	
					<tr valign="top">
						<td>Hotel location: </td>
						<td>Country:<b>';
								$bookingStatusMailMsg .= __( $eb_BusinessCountry);
								$bookingStatusMailMsg .= '</b><br />Region <b>';
								$bookingStatusMailMsg .= __($eb_BusinessRegion);
								$bookingStatusMailMsg .= '</b><br />City: <b>';
								$bookingStatusMailMsg .= __($cityRes->City);
								//$bookingStatusMailMsg .= '</b><br /><br />Address: <b>';
								//$bookingStatusMailMsg .= __($eb_BusinessAddress).' '.$eb_BusinessAddressNumber;						
								//$bookingStatusMailMsg .= '</b><br />Zip: <b>'.$eb_BusinessZip.'</b>';
						$bookingStatusMailMsg .= '</td>
					</tr>';
					
					/*$bookingStatusMailMsg .= '<tr>
						<td>Hotel email: </td>
						<td><b>'.$eb_BusinessEmail.'</b></td>
					</tr>	';*/
					
					$bookingStatusMailMsg .= '</table>
					<br />
					<table style="border:1px solid #ccc" cellpading="5" cellspacing="5">
					<tr>
						<td colspan="2" style="background-color:#528ae7;color:#fff;font-size:14px"><b>Booking ID: #'.$book.'</b></td>
					</tr>	
					<tr valign="top">
						<td>Booking PIN: </td>
						<td><b>'.$bookingDataForStatus->pin.'</b></td>
					</tr>
					<tr valign="top">
						<td>Number of rooms: </td>
						<td><b>'.$bookingDataForStatus->numberOfRooms.'</b></td>
					</tr>
					<tr>
						<td>Arriving at: </td>
						<td><b>'.$bookArivalD.'</b></td>
					</tr>	
					<tr>
						<td>Departing at: </td>
						<td><b>'.$bookDepartureD.'</b></td>
					</tr>	
					<tr>
						<td>Number of nights: </td>
						<td><b>'.$bookingDataForStatus->bookedNights.'</b></td>
					</tr>
					<tr>
						<td>Price: </td>
						<td><b>'.$bookingDataForStatus->booking_total.' '.$bookingDataForStatus->booking_currency.' </b><i>('.$bookingDataForStatus->booking_totalBCUR.' '.$businessCurrency.')</i></td>
					</tr>	
					<tr>
						<td colspan="2"><em style="color:#999;font-size:11px;">This price is without payment expenses</em></td>
					</tr>
					</table>';
					$pageID = get_option('eb-view-bookings');
					$permalink = '';
					if( get_option('permalink_structure')  == "") $permalink = get_site_url().'?page_id='.$pageID;
					else $permalink = get_permalink( $pageID );
					$bookingStatusMailMsg .= '
					<form action = "'.$permalink.'" method="post">
					<input type="hidden" name="eb" value="bookings" />
					<input type="hidden" name="bookID" value="'.$book.'" />
					<input type="hidden" name="pin" value="'.$bookingDataForStatus->pin.'" />	
					<input type="hidden" name="bID" value="10" />
					<em style="color:#999;font-size:11px;">To view your booking online please press the "View booking" button</em>
					<input type="submit" value="View booking" class="eb-search-button" />
					</form>
					';
					$bookingStatusMailMsg .= '<br /><br />For further information please contact us at <b>'.get_bloginfo('admin_email').'</b>';
					$bookingStatusMailMsg .= '<br /><br />Thank you for using <a href="'.get_bloginfo('url').'">'.get_bloginfo('name').'</a> for your bookings!';
					$bookingStatusMailMsg .= '<br /><br /><em style="color:#999;font-size:11px;">This email has been generated by '.get_bloginfo('name').' to inform it\'s customers. If it was not intended for you please ignore it or help us out by informing us at '.get_bloginfo('admin_email').'</em>';
						add_filter('wp_mail_content_type',create_function('', 'return "text/html"; '));
				   	add_filter('wp_mail_from', create_function('', 'return "'.get_bloginfo('admin_email').'"; '));
						add_filter('wp_mail_from_name', create_function('', 'return "'.$eb_BusinessTitle.'"; '));
	
						wp_mail($bookingDataForStatus->customer_email, $bookingStatusMailSubject, $bookingStatusMailMsg);
				}
			}	
			if($newStat == "cancel"){
				if($wpdb->query('update eb_bookingdata set booking_status = "Canceled" where bookingID = '.$book)){
					
				}else $hasUpdateError = true;
				if($wpdb->query('update eb_bookingroomdata set canceled = "YES" where bookingID = '.$book)){
					
				}else $hasUpdateError = true;
				if(!$hasUpdateError){
					$bookingStatusMailSubject = '['.$eb_BusinessTitle.'@'.get_bloginfo('name').'] - Your booking has been canceled';
					$bookingStatusMailMsg = 'For some reason <b>'.$eb_BusinessTitle.'</b> has canceled your booking with ID: <b>'.$book.'</b>.<br />';
					$pageID = get_option('eb-view-bookings');
					$permalink = '';
					if( get_option('permalink_structure')  == "") $permalink = get_site_url().'?page_id='.$pageID;
					else $permalink = get_permalink( $pageID );
					$bookingStatusMailMsg .= '
					<form action = "'.$permalink.'" method="post">
					<input type="hidden" name="eb" value="bookings" />
					<input type="hidden" name="bookID" value="'.$book.'" />
					<input type="hidden" name="pin" value="'.$bookingDataForStatus->pin.'" />	
					<input type="hidden" name="bID" value="10" />
					<em style="color:#999;font-size:11px;">To view your booking online please press the "View booking" button</em>
					<input type="submit" value="View booking" class="eb-search-button" />
					</form>
					';
					$bookingStatusMailMsg .= 'For further information about the reasons of this cancellation please contact us at <b>'.get_bloginfo('admin_email').'</b>';
					$bookingStatusMailMsg .= '<br/><br />Thank you for using <a href="'.get_bloginfo('url').'">'.get_bloginfo('name').'</a> for your bookings!';
					$bookingStatusMailMsg .= '<br /><br /><em style="color:#999;font-size:11px;">This email has been generated by '.get_bloginfo('name').' to inform it\'s customers. If it was not intended for you please ignore it or help us out by informing us at '.get_bloginfo('admin_email').'</em>';
					
					add_filter('wp_mail_content_type',create_function('', 'return "text/html"; '));
				   add_filter('wp_mail_from', create_function('', 'return "'.get_bloginfo('admin_email').'"; '));
					add_filter('wp_mail_from_name', create_function('', 'return "'.$eb_BusinessTitle.'"; '));
	
					wp_mail($bookingDataForStatus->customer_email, $bookingStatusMailSubject, $bookingStatusMailMsg);
				}
			}
		
			
		}
		
		$bookActionTitle = 'Book ID '.$book.' details';		
		$numOfBabies = 0;
		$totalExtraBedsNum = 0;
		$totalExtraBedsPrice = 0.0;
		$totalHBNum = 0;
		$totalHBPrice = 0.0;
		$totalRoomsPriceStr ='';
		
		$bookingData = $wpdb->get_row($bBookSQL);
		$bookingMeta = $wpdb->get_results('select * from eb_bookingroomdata where bookingID = '.$book);
		

		//=======SET VARS//
		$bookArival = explode('00:00:00',$bookingData->dateRange_start);
		$bookArivalE = explode('-', $bookArival[0]);	
		$bookArivalT = mktime(0,0,0, (int)$bookArivalE[1], (int)$bookArivalE[2], (int)$bookArivalE[0]);
		$bookArivalD = date('l d M Y', $bookArivalT);
		$bookDeparture = explode('00:00:00',$bookingData->dateRange_end);
		$bookDepartureE = explode('-', $bookDeparture[0]);	
		$bookDepartureT = mktime(0,0,0, (int)$bookDepartureE[1], (int)$bookDepartureE[2], (int)$bookDepartureE[0]);
		$bookDepartureD = date('l d M Y', $bookDepartureT);
		
		$bookedAt = explode('00:00:00',$bookingData->booking_date);
		$bookedAtE = explode('-', $bookedAt[0]);	
		$bookedAtT = mktime(0,0,0, (int)$bookedAtE[1], (int)$bookedAtE[2], (int)$bookedAtE[0]);
		$bookedAtD = date('l d M Y', $bookedAtT);
		//========CORRECT STATUS IF EXPIIRED=========

		if( ( $bookingData->booking_status == "Pending" || $bookingData->booking_status == "Expired" ) && ( $bookingData->booking_deposit == "0" || $bookingData->booking_deposit == "" ) && $today > $bookArivalT){
			$wpdb->query('update eb_bookingdata set booking_status = "Expired" where bookingID = '.$book);
			$wpdb->query('update eb_bookingroomdata set canceled = "YES" where bookingID = '.$book);
		}
		if($bookingData->booking_status == "Expired"){
			echo '<div class="error" align="center"><b>This booking has been defined as Expired since it has not been confirmed until the arrival date</b></div>';	
			//echo '<div style="position:absolute;margin-top:300px;z-index:2;width:100%" align="center"><div style="font-size:440px;color:#cc0000;opacity: 0.3;">X X X</div></div>';
			echo '
			<script type="text/javascript" >
				jQuery(document).ready(function() {
					jQuery("#targetTD").css("background-color", "#feeae7");
					jQuery("#targetTD").css("border", "solid 2px #cc0000");
				});
			</script>			
			';
		}
		if($bookingData->booking_status == "Confirmed" && $today >= $bookDepartureT){
			$wpdb->query('update eb_bookingdata set booking_status = "Completed" where bookingID = '.$book);
		}
		//=======GET ROOM DETAILS FOR LATER USE======
		$roomCounter = 1;
		$roomDataStr = '';
		$extraBedsStr = '<div><b>Number of extra beds:</b>';
		$totalRoomsPriceStr = '<div>Rooms price:';
		$numOfBabiesStr = '<div>Number of babies:';
		$totalHBStr = '<div>Number of Half Board Meals: '; 
			foreach($bookingMeta as $room){				
				$roomData = get_post($room->roomID);
				$totalRoomsPriceStr .= '<div style="padding-left:20px">#'.$roomCounter.' '.__( $roomData->post_title ).' : <b>'.$room->roomCost.' '.$businessCurrency.'</b> </div>';
				
				$roomDataStr .= '<div style="padding-bottom:20px;width:500px">';					
					$roomDataStr .= '#'.$roomCounter. ' <a href="'.$eb_adminUrl.'?page=easy_booking_menu&type=Hotel&bID='.$bID.'&rID='.$room->roomID.'"><b>'.__( $roomData->post_title ).'</b></a>';
					$roomDataStr .= '<div style="padding-left:20px;">Guest name : <b>'.$room->guestFullName.'</b></div>';
					$roomDataStr .= '<div style="padding-left:20px;">Room price for '.$bookingData->bookedNights.' nights: <b>'.$room->roomCost.' '.$businessCurrency.'</b></div>';

					if((int)$room->noOfBabies > 0){
						$roomDataStr .= '<div style="padding-left:20px;">Babies: <b>'.$room->noOfBabies.' babies</b></div>';
						$numOfBabies += (int)$room->noOfBabies;
					}

					if((int)$room->extraBedNum > 0){
						$roomDataStr .= '<div style="padding-left:20px;">Extra Beds: <b>'.$room->extraBedNum.' extra beds</b></div>';
						$roomDataStr .= '<div style="padding-left:20px;">Extra Bed costs: <b>'.$room->extraBedNum.' * '.$room->extraBedPrice.' '.$businessCurrency.'</b></div>';
						$totalExtraBedsNum += (int)$room->extraBedNum;
						$totalExtraBedsPrice += $room->extraBedPrice;
					}
					if($room->HBoptions != ''){
						$roomDataStr .= '<div style="padding-left:20px;"><b>Half Board Meals</b></div>';
						$HBArr = explode('|',$room->HBoptions);
						
		
							for($o=1; $o < sizeof($HBArr) ; $o++){
								$totalHBNum++;
								$HBdayCount = explode(':',$HBArr[$o]);
								$arDateHB = $bookArivalT;
								$arDateHB += ((int)$HBdayCount[0] * 86400);
								$roomDataStr .= '<div style="padding-left:40px;"> '.$o.' Half Board Meal for '.date('l d F Y',$arDateHB).' : <b>'.$HBdayCount[1].' '.$businessCurrency.'</b></div>';
								$totalHBPrice += $HBdayCount[1] ;
							}
						
					}
					
				$roomDataStr .= '</div>';
				
				$roomCounter++;
			}
			$extraBedsStr .= '<b>'.$totalExtraBedsNum.'</b> which cost <b>'.$totalExtraBedsPrice.' '.$businessCurrency.'</b></div>';
			$totalHBStr .= '<b>'.$totalHBNum.'</b> which cost: <b>'.$totalHBPrice.' '.$businessCurrency.'</b></div>'; 
			$numOfBabiesStr .= ' <b>'.$numOfBabies.'</b></div>';
			$totalRoomsPriceStr .='</div>';
		
		//========BOOKING DATA STRING
		$bookDataStr = '<table style="border:none"><tr><td style="border:none">';		
		$bookDataStr .= '<div class="eb_simpleContainer">';		
		$bookDataStr .= '<h3>Customer data</h3>';
			$bookDataStr .= '<label>Name: </label><b>'. $bookingData->customer_lname.' '.$bookingData->customer_fname.'</b><br />';	
			$bookDataStr .= '<label>Email: </label><b>'. $bookingData->customer_email.'</b> <br /><label>Tel: </label><b>'.$bookingData->customer_tel.'</b><br />';
			$bookDataStr .= '<br /><label>Country: </label><b>'.$bookingData->customer_country.'</b>';
		//$bookDataStr .='</div>';
		
		//$bookDataStr .= '<div class="eb_simpleContainer">';		
		$bookDataStr .= '<h3>Booking data</h3>';
			$bookDataStr .= '<label>Booking ID: </label><b>'.$book.'</b><br />';
			$bookDataStr .= '<label>Status: </label><b>'.$bookingData->booking_status.'</b><br />';
			if( $bookingData->booking_canceled_by_user == "YES"){
				$oldDate = date($bookingData->booking_cancelation_date.' 00:00:00');
				$middleDate = strtotime( $oldDate );
				$newDate = date( 'l, d M Y', $middleDate);
				$bookDataStr .= '<em>This booking was cancelled by user on <b>'.$newDate.'</b>. Cancellation charges are calculated to <b>'.$bookingData->booking_cancelation_cost.' '.$businessCurrency.'</b> according to your cancellation policies. </em><br /><br />';
			}
			$bookDataStr .= '<label>Booked at: </label><b>'.$bookedAtD.'</b><br /><br />';
			$bookDataStr .= '<label>Arrival at: </label><b>'.$bookArivalD.'</b><br />';	
			$bookDataStr .= '<label>Departure at: </label><b>'.$bookDepartureD.'</b><br /><br />';
			$bookDataStr .= '<label>Nights: </label><b>'.$bookingData->bookedNights.'</b><br /><br />';
			$bookDataStr .= '<label>Number of rooms: </label><b>'.$bookingData->numberOfRooms.'</b><br /><br />';
			
			$bookDataStr .= $numOfBabiesStr.'<br />';
			$bookDataStr .= $totalRoomsPriceStr.'<br />';
			if($totalExtraBedsNum>0)
				$bookDataStr .= $extraBedsStr.'<br />';
			if($totalHBNum>0)
				$bookDataStr .= $totalHBStr.'<br />';
			$bookDataStr .= '<hr style="border: 0;color: #9E9E9E;background-color: #9E9E9E;height: 1px;width: 90%;text-align: l" />';
			$bookDataStr .= '<label>Booking subtotal: </label><b>'.$bookingData->booking_totalBCUR.' '.$businessCurrency.'</b>';
			$bTotalBookingCur = $bookingData->booking_total;
			if( $businessCurrency != $bookingData->booking_currency ){
				$bookDataStr .= ' <em>(Hotel currency)</em>';
				$bookDataStr .= '<br /><label>Booking subtotal in <b>'.$bookingData->booking_currency.'</b>: </label><b>'.$bTotalBookingCur.' '.$bookingData->booking_currency.'</b>';
				$bookDataStr .= ' <em>(Booking currency)</em>';
			}
			$bookDataStr .= '<hr style="border: 0;color: #9E9E9E;background-color: #9E9E9E;height: 1px;width: 90%;text-align: l" />';
			$bookDataStr .= '<label>Payment method: </label><b>'.$bookingData->booking_paymentMethod.'</b>';
			
			$bookingPaymentCharge = $bookingData->booking_paymentCharge;
			if( $bookingPaymentCharge == '' ) $bookingPaymentCharge = 0;
			$bookingTotal = $bTotalBookingCur + $bookingPaymentCharge;
			
			$bookDataStr .= '<br /><label>Payment expenses: </label><b>'.$bookingPaymentCharge.' '.$bookingData->booking_currency.' </b>';
			$bookDataStr .= '<div style="border:1px solid #9E9E9E;margin-top:10px;text-align:center;font-size:14px;">';
			$bookDataStr .= '<label>Total: </label><b>'.$bookingTotal.' '.$bookingData->booking_currency.' </b>';
			$bookDataStr .= '</div>';				
			
			$bookDataStr .= "<br />";
			$bookingDeposit = $bookingData->booking_deposit;
			if( $bookingDeposit == '' ) $bookingDeposit = 0;
			$bookDataStr .= '<label>Amount that has been already paid: </label><b>'.$bookingDeposit.' '.$bookingData->booking_currency.' </b> <a class="littleEditBtns" onclick="jQuery(\'#fix-payed-amount\').show();" title="Is the paid amount wrong? Click here to fix it!">Correct this amount</a>';
			$bookDataStr .= '<div id="fix-payed-amount" style="text-align:center;padding-top:5px;display:none;">
				<form action = "" method="post">
					<input type="text" name = "new-payed-amount" style="width:80px" /> '.$bookingData->booking_currency.'
					<input type="hidden" name="depositCur" value="'.$bookingData->booking_currency.'" />
					<input type="submit" value="Correct payment amount" class="eb-search-button" style="color:#666;font-weight:bold;" />
					<a class="littleEditBtns" onclick="jQuery(\'#fix-payed-amount\').hide();" title="Hide payment correction area">&nbsp; x &nbsp;</a>
					<br /><em>The new amount must be at the currency the booking was made <b>('.$bookingData->booking_currency.')</b></em>
				</form>
			</div>';
			
			$bookDataStr .= "<br /><br />";
			
			$bookingBalnce = number_format($bookingTotal, 2) - number_format($bookingDeposit, 2);			
			
			if( $bookingBalnce > 0 ){
				$bookDataStr .= '<div style="border:1px solid #9E9E9E;margin-top:10px;text-align:center;font-size:14px; color:#f34246;">';
					$bookDataStr .= 'The balance that remains is: <b>'.$bookingBalnce.' '.$bookingData->booking_currency.'</b>';
				$bookDataStr .= '</div>';
								
				$bookDataStr .= '<div style="text-align:center;">Please enter the amount paid to be added to the deposit of this booking<br />
					<form action="admin.php?page=bookings_menu&bID='.$bID.'&book='.$book.'" method="post">
					<input type="hidden" name="updateDeposit" value="YES" />
					<input type="hidden" name="depositCur" value="'.$bookingData->booking_currency.'" />
					<input type="text" style="width:150px;" id="booking-deposit-amount" name="booking-deposit-amount" value="" /> <b>'.$bookingData->booking_currency.'</b>
					<input type="submit" style="color:#2ba22b;font-weight:bold;" value="Add Payment">
					</form>
					<br /><em>The amount must be at the currency the booking was made <b>('.$bookingData->booking_currency.')</b></em><br /><br />
					</div>';
			}
			else{				
				$bookDataStr .= '<div style="background-color: #ebf8a4;color: #999; -moz-border-radius:5px;-webkit-border-radius:5px;border-radius:5px;-moz-box-shadow: 0 1px 1px #fff inset;-webkit-box-shadow: 0 1px 1px #fff inset;box-shadow:  0 1px 1px #fff inset;border:1px solid;background-position: 99% 50%;padding:10px 10px 10px 10px;">';
					$bookDataStr .= '<b>No balance.</b> <em>It seems that the booking has been payed</em>';
				$bookDataStr .= '</div>';

			}
		$bookDataStr .='</div>';
		$bookDataStr .= '<div style="text-align:center;">';
		$mayCancel = true;
		if($today >= $bookArivalT && $user_info->user_level == 0 ){
			$mayCancel = false;
		}			
			if($bookingData->booking_status != 'Confirmed' && $bookingData->booking_status != 'Completed')
				$bookDataStr .= '<input type="submit" style="color:#2ba22b;font-weight:bold;" value="Confirm Booking" onclick="updateBookingStatus(\'confirm\', '.$book.');">	';
					
			if($bookingData->booking_status != 'Canceled' && $bookingData->booking_status != 'Completed' && $mayCancel) 
				$bookDataStr .= '<input type="submit" style="color:#f34246;font-weight:bold;" value="Cancel Booking" onclick="updateBookingStatus(\'cancel\', '.$book.');"></div>';
		
		$bookDataStr .= '</td><td style="border:none">';
		$bookDataStr .= '<div class="eb_simpleContainer">';
			$bookDataStr .= '<h3>Rooms details</h3>';			
			/**===========Ta ROOM DETAILS=============*/
			$bookDataStr .= $roomDataStr;
			
		$bookDataStr .= '</div>';
		
		$bookDataStr .= '</td></tr></table>';
		
	}
	//==================================================================
	//==========================END OF bookID IS SET====================
	

	?>
	<table class="widefat">
		<thead>
			<tr>
				<th><?php echo $bookActionTitle.' '.$allBookingsStr .' '.$pendingBookingsStr. ' '.$confBookingsStr. ' '.$compBookingsStr.' '.$cancBookingsStr.' '.$expBookingsStr; ?>
				<?php
				/*if($minBookMonth != ''){
					$curDate = time();
					$startMonth = date('m', $curDate);
					$startYear = date('Y', $curDate);
					$endDateArr = explode('-',$minBookMonth);
					$endMonth = $endDateArr[1];
					$endYear = $endDateArr[0];
					$startDate = $startYear.'-'.$startMonth;
					$endDate = $endYear.'-'.$endMonth;*/
				?>
				<p>
				<!--<span class="eb_simpleContainerNoBack" style="position:relative;top:5px;">Bookings made at:
					
				<select name="mnthbk" id="mnthbk" onchange="getBookingsForMonth()">
					<option>Select month</option>
					<?php
					/*$curSearchDate = date('Y-m-d', mktime(0, 0, 0, $startMonth, 1, $startYear));
					$endDateFull = date('Y-m-d', mktime(0, 0, 0, $endMonth, 31, $endYear));
					$addMonths = 0;
					echo $curSearchDate .'>='. $endDateFull;
					while($curSearchDate >= $endDateFull){
						$curSearchDate = date('Y-m-d', mktime(0, 0, 0, $startMonth - $addMonths, 1, $startYear));		
						list($syear, $smonth, $sday) = explode('-', $curSearchDate);
						echo '<option value="'.$syear.'-'.$smonth.'" ';
						if($_REQUEST['mnthbk'] == $syear.'-'.$smonth) $selected = ' selected ';
						else  $selected = '';
						echo $selected.'>'.$smonth.'/'.$syear.'</option>';
						$addMonths++;
					}		*/
					?>
				</select>
				</span>-->
				<?php //} ?>
				<table style="border:none;width:100%;height:50px;" >
				<td style="border:none;">
				<span class="eb_simpleContainerNoBack" style="position:relative;top:5px;padding:5px;">
				Bookings 
				<select id="selectBookingRange">
					<option value="start" <?php if($reqswitch == "start") echo 'selected'; ?>>arriving</option>
					<option value="end" <?php if($reqswitch == "end") echo 'selected'; ?>>departing</option>
					<option value="made" <?php if($reqswitch == "made") echo 'selected'; ?>>made</option>
				</select>
				 at:
				<select id="selectBookingDay">
					<option value="%">Day</option>
					<?php
						for($i=1;$i<=31;$i++){
							$dayNum = $i;
							if($i <= 9) $dayNum = '0'.$i;
							$selected = '';
							if($dayNum == $reqDay) $selected = 'selected'; 
							echo '<option value = "'.$dayNum.'" '.$selected.' >'.$dayNum.'</option>';											
						}
					?>					
				</select>
				<select id="selectBookingMonth">
					<option value="%">Month</option>
					<option value="01" <?php if($reqmonth == "01") echo 'selected'; ?>>1 (Jan)</option>
					<option value="02" <?php if($reqmonth == "02") echo 'selected'; ?>>2 (Feb)</option>
					<option value="03" <?php if($reqmonth == "03") echo 'selected'; ?>>3 (Mar)</option>
					<option value="04" <?php if($reqmonth == "04") echo 'selected'; ?>>4 (Apr)</option>
					<option value="05" <?php if($reqmonth == "05") echo 'selected'; ?>>5 (May)</option>
					<option value="06" <?php if($reqmonth == "06") echo 'selected'; ?>>6 (Jun)</option>
					<option value="07" <?php if($reqmonth == "07") echo 'selected'; ?>>7 (Jul)</option>
					<option value="08" <?php if($reqmonth == "08") echo 'selected'; ?>>8 (Aug)</option>
					<option value="09" <?php if($reqmonth == "09") echo 'selected'; ?>>9 (Sep)</option>
					<option value="10" <?php if($reqmonth == "10") echo 'selected'; ?>>10 (Oct)</option>
					<option value="11" <?php if($reqmonth == "11") echo 'selected'; ?>>11 (Nov)</option>
					<option value="12" <?php if($reqmonth == "12") echo 'selected'; ?>>12 (Dec)</option>
				</select>
				<select id="selectBookingYear">
					<option value="%">Year</option>
					<?php
					/*echo '<option  value="2012" ';
					if($reqYear == "2012") echo "selected";
					echo ' >2012</option>';
					echo '<option  value="2013" ';
					if($reqYear == "2013") echo "selected";
					echo ' >2013</option>';*/
					for( $cy = date("Y")+2; $cy >= 2011; $cy-- ){
						echo '<option  value="'.$cy.'" ';
						if($reqYear == $cy) echo "selected";
						echo ' >'.$cy.'</option>';
					}
					?>
				</select>
				<a onclick="fetchBookingsForDay();" class="littleEditBtns" style="font-size:12px;">go</a>
				</span>
				</td>
				
				<td style="border:none;">
				<form id="fetch-booking-frm" action="admin.php?page=bookings_menu&bID='.$bID.'" method="request">
				<span class="eb_simpleContainerNoBack" style="position:relative;top:5px; left:10px;padding:5px;">
					Enter the number of the booking 
					<input type="hidden" name="page" value="bookings_menu" />
					<input type="hidden" name="bID" value="<?php echo $bID; ?>" />
					<input type="text" style="width:100px;margin-bottom:2px;" name="book" value="" />
					<a onclick="javascript: jQuery('#fetch-booking-frm').submit();" class="littleEditBtns" style="font-size:12px;">go</a>
				</span>
				</form>
				</td>
				</tr>
				</table>
				</p>
				</th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td id="targetTD">
					<?php echo $bookDataStr;?>	
					<div id="paging_container" style="width:100%" align="center">
						<div class="wrap" align="center" style="text-align:center;float:left;">
							<div class="tablenav">
    							<div class='tablenav-pages'>
        						<?php   
        							if(!isset($_REQUEST['book']))        
		  							echo paginate_links($pagination_args);
        						?>
    							</div>
							</div>
						</div>
					</div><!--end of paging_container-->				
				</td>
			</tr>
		</tbody>
	</table>
	

	<?php

//================================================================
//==========================END OF bID IS SET=====================
}//end of if bID

?>
<script type="text/javascript" >
function fetchBookingsForDay(){
	var day = jQuery("#selectBookingDay").val()
	var month = jQuery("#selectBookingMonth").val()
	var year = jQuery("#selectBookingYear").val()
	if(month != ''){
		var urlStr = "admin.php?page=bookings_menu&bID=<?php echo $_REQUEST['bID']?>";
		<?php if (isset($_REQUEST['paged']) && $_REQUEST['paged'] != '') echo 'urlStr += "&paged='.$_REQUEST['paged'].'";';?>
		<?php if (isset($_REQUEST['stat']) && $_REQUEST['stat'] != '') echo 'urlStr += "&stat='.$_REQUEST['stat'].'";';?>
		//mArr = month.split('/');
		//month = mArr[1]+'-'+mArr[0];
		urlStr += '&mnthbk='+year+'-'+month+'-'+day+'&stch='+jQuery("#selectBookingRange").val();
		document.location = urlStr;
	}
}
function getBookingsForMonth(){
	var month = jQuery("#mnthbk").val();
	if(month != ''){
		var urlStr = "admin.php?page=bookings_menu&bID=<?php echo $_REQUEST['bID']?>";
		<?php if (isset($_REQUEST['paged']) && $_REQUEST['paged'] != '') echo 'urlStr += "&paged='.$_REQUEST['paged'].'";';?>
		<?php if (isset($_REQUEST['stat']) && $_REQUEST['stat'] != '') echo 'urlStr += "&stat='.$_REQUEST['stat'].'";';?>
		//mArr = month.split('/');
		//month = mArr[1]+'-'+mArr[0];
		urlStr += '&mnthbk='+month;
		document.location = urlStr;
	}
}
 function updateBookingStatus(action, bookID){
 	document.location = "admin.php?page=bookings_menu&bID=<?php echo $bID; ?>&book="+bookID+"&statset="+action;
 }

</script>
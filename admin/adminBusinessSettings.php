<p>
From here you can take a quick look for any notifications such as today's arrivals or departures.<br />
For a more detailed view you can go to the <a href="admin.php?page=business_list">businesses menu</a>!
</p>
<?php
global $wpdb;
global $eb_path;
global $eb_adminUrl;
global $table_prefix;
global $current_user;

$businessCounter = 0;
$businesessArr = array();
$user_id = get_current_user_id();
$businesses = $wpdb->get_results('select ID, post_title, post_type from '.$table_prefix.'posts where post_author = '.$user_id.' AND ( post_type = "Hotel" OR post_type="Apartments" )');
if(!empty($businesses)){	
	foreach($businesses as $business){
		$businesessArr[$businessCounter][0] = $business->ID;
		$businesessArr[$businessCounter][1] = $business->post_title;
		$businesessArr[$businessCounter][2] = $business->post_type; 
		$businessCounter++;
		
		//echo '<br>'.$business->post_title;	
	}
}
?>

<div>
<?php
	if(!empty($businesessArr))
	for($bC=0;$bC<sizeof($businesessArr);$bC++){
		$display = "display:none";
		//if($bC == 0 ) $display="display:block";
		?>
		<div class="eb_simpleContainer" id="busData_<?php echo $businesessArr[$bC][0];?>">
		<h3>
			<a onclick="busDetailsDisp(<?php echo $businesessArr[$bC][0]; ?>)"><?php echo $businesessArr[$bC][1];?></a>
			<span style="font-style:none;">
				<a class="littleEditBtns" onclick="busTabDetailsDisp('today', <?php echo $businesessArr[$bC][0]; ?>)" id="todaysArrivalsBtn_<?php echo $businesessArr[$bC][0]; ?>" style="display:none"></a>
				<a class="littleEditBtns" onclick="busTabDetailsDisp('tomorrow', <?php echo $businesessArr[$bC][0]; ?>)" id="tomorowArrivalsBtn_<?php echo $businesessArr[$bC][0]; ?>" style="display:none"></a>
				<a class="littleEditBtns" onclick="busTabDetailsDisp('departure', <?php echo $businesessArr[$bC][0]; ?>)" id="todaysDeparturesBtn_<?php echo $businesessArr[$bC][0]; ?>" style="display:none"></a>
			</span>
		</h3>
			<div class="innerBusinessDetailsDiv" id="innerBusinessDetailsDiv_<?php echo $businesessArr[$bC][0];?>" style="<?php echo $display; ?>">			
			Business type: <b><?php echo $businesessArr[$bC][2];?></b><br />
			Package Deal: <?php packDealDetails($businesessArr[$bC][0], $table_prefix);?><br />
			 <b><?php getBusDateArivals($businesessArr[$bC][0], $table_prefix, 0, 'today')?></b>
			<b><?php getBusDateArivals($businesessArr[$bC][0], $table_prefix, 1, 'tomorrow')?></b>
			<b><?php getBusDateArivals($businesessArr[$bC][0], $table_prefix, 0, 'departure')?></b>
			</div>
		</div>
	<div style="width:100%" align="center">
		<a class="show_down_area" id="innerBusinessDetailsDivBtn_<?php echo $businesessArr[$bC][0]; ?>" onclick="busDetailsDisp(<?php echo $businesessArr[$bC][0]; ?>)"> Show </a>
	</div>
		<?php
	}

?>
</div>
<?php
function getBusDateArivals($bID, $table_prefix, $plusDays, $switch){
	global $wpdb;
	$date = time();
	$today = date('d', $date);
	$thismonth = date('m', $date);
	$thisyear = date('Y', $date);
	$isLeaving = '';
	if($switch == 'today'){
		$dateRangeStr =  "dateRange_start";
		$switchBtnID = 'todaysArrivalsBtn_'.$bID;
		$btnStr = 'arrivals for today';
		$curStat = 'Today\'s arrivals';
		$nothingStr = 'No arrivals for today';
		$ocStr = 'occupied by';
	}
	if($switch == 'tomorrow'){
		$dateRangeStr =  "dateRange_start";
		$switchBtnID = 'tomorowArrivalsBtn_'.$bID;
		$btnStr = 'arrivals for tomorrow';
		$curStat = 'Tomorrow arrivals';
		$nothingStr = 'No arrivals for tomorrow';
		$ocStr = 'occupied by';
	}
	if($switch == 'departure'){
		$dateRangeStr =  "dateRange_end";
		$switchBtnID = 'todaysDeparturesBtn_'.$bID;
		$btnStr = 'departures for today';
		$curStat = 'Today\'s departures';
		$nothingStr = 'No departures for today';
		$ocStr = 'emptied today. Guest';
		$isLeaving = 'is leaving';
	}
	$next = date('Y-m-d', mktime(0, 0, 0, $thismonth, $today + $plusDays, $thisyear));
	list($syear, $smonth, $sday) = explode('-', $next);
	$searchDate = $syear.'-'.$smonth.'-'.$sday.' 00:00:00';

	$dateArrivals = $wpdb->get_results('select bookingID, customer_fname, customer_lname from eb_bookingdata where '.$dateRangeStr.' = "'.$searchDate.'" and booking_status != "Canceled" and businessID ='.$bID);
	$dateArrivalCount = count($dateArrivals);

	if($dateArrivalCount > 0)	{
		//echo '<a onclick = "displayDateBookingDetails('.$bID.', '.$plusDays.')">'.$dateArrivalCount.' arrivals </a>';
		?>
		<script type="text/javascript" >
			jQuery(document).ready( function(){
				jQuery("#<?php echo $switchBtnID?>").html("<?php echo $dateArrivalCount.' '.$btnStr; ?>").show();
			});
		</script>
		<div id="<?php echo $switch.'_'.$bID; ?>" class="switchDiv" style="display:none;">
		<?php
		echo '<br />'.$curStat.' '.$dateArrivalCount;
		foreach($dateArrivals as $dateArrival){
			$serviceBookingDataArr = $wpdb->get_results('select roomID, guestFullName, noOfBabies, extraBedNum, HBoptions from eb_bookingroomdata where dateRange_start = "'.$searchDate.'" and canceled = "NO" and bookingID = '.$dateArrival->bookingID);
			
			?>
			<div class="eb_simpleContainer" style="background:none;padding-left:20px;font-weight:normal;">
			<strong><a href="admin.php?page=bookings_menu&bID=<?php echo $bID; ?>&book=<?php echo $dateArrival->bookingID; ?>" target="_blank">Booking Id: <?php echo $dateArrival->bookingID; ?></a></strong><br />

				<?php	
				foreach($serviceBookingDataArr as $serviceBook){
					$roomT = $wpdb->get_row('select post_title from '.$table_prefix.'posts where ID = '.$serviceBook->roomID);
					$guestName = $serviceBook->guestFullName;
					if ($guestName == '') $guestName = $dateArrival->customer_lname.' '.$dateArrival->customer_fname;
					echo '<br><span>A room type <b>'.$roomT->post_title.'</b> will be '.$ocStr.' <b>'.$guestName.' </b>'.$isLeaving.'</span>';
					if($serviceBook->noOfBabies != '' && $serviceBook->noOfBabies > 0)
					echo '<br><span style="padding-left:45px">Number of babies: '.$serviceBook->noOfBabies.'</span>';
					if($serviceBook->extraBedNum != '' && $serviceBook->extraBedNum > 0)
					echo '<br><span style="padding-left:45px">Number of extra beds: '.$serviceBook->extraBedNum.'</span>';
					if($serviceBook->HBoptions != '')
					echo '<br><span style="padding-left:45px">There are some Half Board requests. Check at bookings details</span>';
				}
				?>
			</div>
		<?php
		}
		?>
		</div>
		<?php
	}
	else {
		echo '<br>'.$nothingStr;
	}	
	
	
}
function packDealDetails($bID, $table_prefix){
	global $wpdb;
	$packID = $wpdb->get_row('select meta_value from '.$table_prefix.'postmeta where post_id = '.$bID.' and meta_key ="eb_packDeal" ');

	if($packID){
		$siteCur = $wpdb->get_row('select option_value from '.$table_prefix.'options where option_name = "eb_siteCurrency"');
		if($siteCur) $siteCur = $siteCur->option_value; else $siteCur = '';
		
		$packDealT = $wpdb->get_row('select post_title from '.$table_prefix.'posts where ID = '.$packID->meta_value);
		
		$persStr = '';
		$durStr = '';
		
		$packDealPers = $wpdb->get_row('select meta_value from '.$table_prefix.'postmeta where post_id = '.$packID->meta_value.' and meta_key ="eb_dealPersentCost" ');		
		$packDealDur = $wpdb->get_row('select meta_value from '.$table_prefix.'postmeta where post_id = '.$packID->meta_value.' and meta_key ="eb_dealPeriodDuration" ');
		$packDealPeriodCost = $wpdb->get_row('select meta_value from '.$table_prefix.'postmeta where post_id = '.$packID->meta_value.' and meta_key ="eb_dealPeriodCost" ');
		
		if($packDealDur && $packDealPeriodCost){
			if($packDealDur->meta_value > 0 && $packDealPeriodCost->meta_value > 0){
				$durStr = $packDealPeriodCost->meta_value.' '.$siteCur.' per '. $packDealDur->meta_value.' months';
			}	
		}
		if($packDealPers && $packDealPers->meta_value > 0){
			if($durStr != '') $persStr = ' and ';
			$persStr .= $packDealPers->meta_value.'% of each booking';
		}
		echo '<b>'.$packDealT->post_title.'</b> <i>('.$durStr.$persStr.')</i>';
				
	}
	else echo 'NO PACKAGE DEAL';
}


?>
<script type="text/javascript" >
function busTabDetailsDisp(tSwitch ,bID){

	if(jQuery("#"+tSwitch+"_"+bID).css("display") == "none"){
			jQuery(".switchDiv").hide("slow");
			jQuery("#"+tSwitch+"_"+bID).show("slow");
			//jQuery("#innerBusinessDetailsDivBtn_"+bID).html("Hide");
			
			}
			
		else{
			jQuery("#"+tSwitch+"_"+bID).hide("slow");
			//jQuery("#innerBusinessDetailsDivBtn_"+bID).html("Show");
		
		}
		if(jQuery("#innerBusinessDetailsDiv_"+bID).css("display") == "none") busDetailsDisp(bID);
}
function busDetailsDisp(bID){
	
		if(jQuery("#innerBusinessDetailsDiv_"+bID).css("display") == "none"){
			jQuery(".innerBusinessDetailsDiv").hide("slow");
			jQuery(".show_down_area").html("Show");
			jQuery("#innerBusinessDetailsDiv_"+bID).show("slow");
			jQuery("#innerBusinessDetailsDivBtn_"+bID).html("Hide");
			
			}
			
		else{
			jQuery("#innerBusinessDetailsDiv_"+bID).hide("slow");
			jQuery("#innerBusinessDetailsDivBtn_"+bID).html("Show");
		
		}

}
</script>
<?php
if(isset($_POST['bID']) && $_POST['bID'] != ''){
	if(!isset($_POST['sMonth']) || !isset($_POST['aPath']) || !isset($_POST['pref'])) die('Something is not set man...');

	$bID = addslashes($_POST['bID']);
	$sMonth = (int)addslashes($_POST['sMonth']);

	$aPath = addslashes($_POST['aPath']);
	$pref = addslashes($_POST['pref']);
	$bTitle = addslashes($_POST['bTitle']);
	
	$date = time();
	$thismonth = date('m', $date);
	$thisyear = date('Y', $date);
	
	$curDate = date('Y-m-d', mktime(0, 0, 0, $thismonth + $sMonth, 1, $thisyear));
	list($yearAv, $monthAv, $dayAv) = explode('-', $curDate);
	$pervMonth = (int)$sMonth - 1;
	$nextMonth = (int)$sMonth + 1;
	$nextDate = date('Y-m-d', mktime(0, 0, 0, $thismonth + $sMonth + 1, 1, $thisyear));
	list($nextYearAv, $nextMonthAv, $nextDayAv) = explode('-', $nextDate);
	
	
	include_once($aPath.'wp-config.php');		 
	include_once($aPath.'wp-load.php');
	include_once($aPath.'wp-includes/wp-db.php');
	global $wpdb;
	$businessServices = $wpdb->get_results('select ID from '.$pref.'posts where post_parent = '.$bID);
	if(!empty($businessServices)){
		$firstDay = mktime(0,0,0, $monthAv, 1, $yearAv);
		$monthName = date('F', $firstDay);
		echo '<span style="color:#666;font-size:14px"><strong> <i style="font-size:16px;"> '.$monthName.' '.$yearAv.'</strong></span><br /><br />';
		echo '<a class="littleCloseBtns" style= "float:left;margin-bottom:5px;" onclick="getBAvailability('.$bID.', \''.$bTitle.'\', '.$pervMonth.')">« Previous</a>';		
		echo '<a class="littleCloseBtns" style= "float:right;margin-bottom:5px;" onclick="getBAvailability('.$bID.', \''.$bTitle.'\','.$nextMonth.')">Next »</a>';
		foreach($businessServices as $service){
			$sID = $service->ID;	
			
			$availability = new serviceAvailability($sID, $monthAv, $yearAv, $pref);
			
			if($monthAv > $thismonth && $yearAv >= $thisyear)

			echo '<table class="widefat"><tr><td>';
				echo $availability->displayMonthsPalette();
			echo '</td>';
		
			if($sMonth == 12) $sYear += 1;
	
			echo '</tr></table>';

		}
	}else echo '<div class="updated" align="center" style="font-size:14px;color:#666"><b>There are no rooms for <i>'.$bTitle.'</i> </b></div>';	
	
}

class serviceAvailability{
	var $serviceID = '';
	var $itemNumber = '';
	var $startMonth = '';
	var $startYear = '';
	var $serviceTitle = '';
	var $tablePrefix = '';
	
	function __construct($sID, $sMonth, $sYear, $pref){
		global $wpdb;
		$this->serviceID = $sID;
		$this->startMonth = $sMonth;
		$this->startYear = $sYear;
		$this->tablePrefix = $pref;	
		$sTitle = $wpdb->get_row('select post_title from '.$pref.'posts where ID ='.$sID);
		$this->serviceTitle = $sTitle->post_title;
		$itemNum = $wpdb->get_row('select meta_value from '.$pref.'postmeta where post_id = '.$sID.' and meta_key = "eb_roomNum"');
		$this->itemNumber = $itemNum->meta_value;
	}
	
	
	function displayMonthsPalette(){
		global $wpdb;
		$month = $this->startMonth;
		$year = $this->startYear;
		$firstDay = mktime(0,0,0, $month, 1, $year);
		$title = date('F', $firstDay);
		$dayOfWeek = date('D', $firstDay);
		
		switch($dayOfWeek){
			case 'Sun': $blank = 0; break;
			case 'Mon': $blank = 1; break;
			case 'Tue': $blank = 2; break;
			case 'Wed': $blank = 3; break;
			case 'Thu': $blank = 4; break;
			case 'Fri': $blank = 5; break;
			case 'Sat': $blank = 6; break;  			
		}
		
		$daysInMonth = cal_days_in_month(0, $month, $year);
		$title = date('F', $firstDay);
		$palette = '<div><b style="font-size:14px;color:#666">'.__($this->serviceTitle).' availability for '.$title.' '.$year.'</b></div>';
		$palette .= '<div style="width:900px;overflow:auto;overflow-y: hidden;">';
		$palette .= '<table width="394" class="widefat">';

		$dayCount = 1;
				//$palette .= '<tr>';				
				while($blank > 0){
					//$palette .= '<td></td>';
					$blank = $blank - 1;
					$dayCount++;	
				}
				
				$dayNum = 1;

				while($dayNum <= $daysInMonth){	
					$dayNameD = mktime(0,0,0, $this->startMonth, $dayNum, $this->startYear);
					$dayName = date('D', $dayNameD);
					$dayDate = 	$this->startYear.'-'.$this->startMonth.'-'.$dayNum.' 00:00:00';
					$sPrevBookings = $wpdb->get_var('select COUNT(*) from eb_bookingroomdata where roomID = '.$this->serviceID.' and (dateRange_start <= "'.$dayDate.'" AND dateRange_end > "'.$dayDate.'") AND canceled = "NO" ');
					$availabilityNum = (int)$this->itemNumber - (int)$sPrevBookings;
					$roomsTotalNum = $this->itemNumber;
					$sColor = "green";
					$backColor = "#86d086";
					$sMessage = '<b>'.$availabilityNum.' / '.$roomsTotalNum.' available</b>';
					if($availabilityNum < $roomsTotalNum){
						$sColor = "#00618c";
						$backColor = "#76b6ec";
					}
					if($availabilityNum <= 0 ) {
						$sColor = "red";
						$sMessage = '<b><i>'.$availabilityNum.' / '.$roomsTotalNum.' available</b>';
						$backColor = "#f68888";
					}
					if($availabilityNum == 1 ) {
						$sColor = "#e06c1f";
						$backColor = "#edba97";
						$sMessage = '<b>'.$availabilityNum.' / '.$roomsTotalNum.' available</b>';					
					}
					$palette .= '<td align center style="background-color:'.$backColor.'">';					
					$palette .= '<b>'.$dayName.' '.$dayNum.'</b>';
					$palette .= '<div style="color:'.$sColor.'"> '.$sMessage.'</div>';
					$palette .= '</td>';
					$dayNum++;
					$dayCount++;
					if($dayCount > 7){
						//$palette .= '</tr><tr>';
						$dayCount = 1;	
					}
				}
				
				$palette .= '</table>';
			$palette .= '</div>';
		return $palette;
	}
}
?>
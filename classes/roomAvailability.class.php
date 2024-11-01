<?php
if(isset($_POST['sID']) && $_POST['sID'] != ''){
	if(!isset($_POST['sMonth']) || !isset($_POST['aPath']) || !isset($_POST['pref'])) die('Something is not set man...');
	$sID = addslashes($_POST['sID']);
	$sMonth = (int)addslashes($_POST['sMonth']);

	$aPath = addslashes($_POST['aPath']);
	$aPath = str_replace('|','/', $aPath);
	$pref = addslashes($_POST['pref']);
	
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
	
	$availability = new serviceAvailability($sID, $monthAv, $yearAv, $pref);
	echo '<span style="color:#666;font-size:14px"><strong>Availability for <i style="font-size:16px;">'.getLanguageTitle( $availability->serviceTitle, '', '').'</i></strong></span><a class="littleCloseBtns" style= "float:right;margin-bottom:5px;" onclick="hideAvailabilityPalette()">&nbsp;&nbsp;X&nbsp;&nbsp;</a><br /><br />';
	if($monthAv > $thismonth && $yearAv >= $thisyear)
	echo '<a class="littleCloseBtns" style= "float:left;margin-bottom:5px;" onclick="getAvailability('.$pervMonth.')">« Previous</a>';
	echo '<a class="littleCloseBtns" style= "float:right;margin-bottom:5px;" onclick="getAvailability('.$nextMonth.')">Next »</a>';
	echo '<table class="widefat"><tr><td>';
		echo $availability->displayMonthsPalette();
	echo '</td>';
	
	if($sMonth == 12) $sYear += 1;
	
	$availabilityNext = new serviceAvailability($sID, $nextMonthAv , $nextYearAv, $pref);
	echo '<td>'.$availabilityNext->displayMonthsPalette().'</td>';
	echo '</tr></table>';
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
		$palette = '<table width="394" class="widefat">
			<tr>
				<td colspan ="7" style="width:100%">
					<b style="font-size:14px;"> '.$title.' '.$year.'</b>
				</td>
			</tr>
		';
		$palette .= '<tr>
						<td width="62"><strong>Sun</strong></td>
						<td width="62"><strong>Mon</strong></td>
						<td width="62"><strong>Tue</strong></td>
						<td width="62"><strong>Wed</strong></td>
						<td width="62"><strong>Thu</strong></td>
						<td width="62"><strong>Fri</strong></td>
						<td width="62"><strong>Sat</strong></td>
					</tr>';
		
		$dayCount = 1;
				$palette .= '<tr>';				
				while($blank > 0){
					$palette .= '<td></td>';
					$blank = $blank - 1;
					$dayCount++;	
				}
				
				$dayNum = 1;

				while($dayNum <= $daysInMonth){	
				//2012-02-13 00:00:00
					$dayDate = 	$this->startYear.'-'.$this->startMonth.'-'.$dayNum.' 00:00:00';
					$sPrevBookings = $wpdb->get_var('select COUNT(*) from eb_bookingroomdata where roomID = '.$this->serviceID.' and (dateRange_start <= "'.$dayDate.'" AND dateRange_end > "'.$dayDate.'") AND canceled = "NO"');
					$availabilityNum = (int)$this->itemNumber - (int)$sPrevBookings;
					$sColor = "green";
					$backColor = "#86d086";
					$sMessage = '<b>'.$availabilityNum.' available</b>';
					if($availabilityNum <= 0 ) {
						$sColor = "red";
						$sMessage = '<b><i>No rooms available</i></b>';
						$backColor = "#f68888";
					}
					if($availabilityNum == 1 ) {
						$sColor = "#e06c1f";
						$backColor = "#edba97";
						$sMessage = '<b>Only one left</b>';					
					}
					$palette .= '<td align center style="background-color:'.$backColor.'">';					
					$palette .= '<b>'.$dayNum.'</b>';
					$palette .= '<div style="color:'.$sColor.'"> '.$sMessage.'</div>';
					$palette .= '</td>';
					$dayNum++;
					$dayCount++;
					if($dayCount > 7){
						$palette .= '</tr><tr>';
						$dayCount = 1;	
					}
				}
				
				$palette .= '</table>';
		
		return $palette;
	}
	
	
}

function getLanguageTitle($location, $lang, $defaultLang){
		if($defaultLang == "") $defaultLang = "en";
		$locationName = explode('<!--:'.$lang.'-->', $location);
   	$locationName = explode('<!--:-->', $locationName[1]);
   	$locationName = $locationName[0];
   	if( $locationName == '' && $defaultLang != '' ){
   		$locationName = explode('<!--:'.$defaultLang.'-->', $location);
   		$locationName = explode('<!--:-->', $locationName[1]);
   		$locationName = $locationName[0];
   	}
   	if( $locationName == '') $locationName = $location;
		return $locationName;
	}
?>
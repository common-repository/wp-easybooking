
<?php
if(isset($_POST['getServicePrice']) && $_POST['getServicePrice'] == 'WITH_AJAX'){
	$sID = addslashes($_POST["sID"]);
	 $bID = addslashes($_POST["bID"]);
	 $sRange = addslashes($_POST["startRange"]);
	 $eRange = addslashes($_POST["endRange"]);
	 $aPath = addslashes($_POST["abPath"]);
	 include_once($aPath.'wp-config.php');		 
	 include_once($aPath.'wp-load.php');
	 include_once($aPath.'wp-includes/wp-db.php');
	 global $wpdb;
	// echo 'From include Pref: '.$table_prefix. ' -- u:'.DB_USER;
	 echo ' >>Eftase: sID'.$sID.'  range start: '. $sRange.' end: '. $eRange.' path: '. $aPath;
	 $serviceAvailableNum = sAvailabilityByDateRange($sID, $sRange, $eRange, $aPath, $wpdb);

}

function dateToYMDfn($datestr){
		$dateArray = explode('/',$datestr);
		$newDate = $dateArray[2].'-'.$dateArray[1].'-'.$dateArray[0];
		echo 'new Date: '.$newDate;
		return $newDate;	
}

function sAvailabilityByDateRange($serviceID, $dateRangeStart, $dateRangeEnd, $absolutePath, $wpdb){
		//echo '--sAvailabilityByDateRange '.$absolutePath.'wp-config.php ===sid: '.$serviceID;
		//if(!include_once($absolutePath.'wp-config.php') ) echo 'Include error'; 
		//include('../../../wp-config.php');
		//include_once('../../../wp-load.php');
		//include_once('../../../wp-includes/wp-db.php');
		
		//include_once($absolutePath.'wp-load.php');
		//include_once($absolutePath.'wp-includes/wp-db.php');
		//global $wpdb;
		//echo 'From FUNCTION pref: '.$table_prefix. ' -- u: '.DB_USER;
		$roomNum = $wpdb->get_row('select meta_value from wp_postmeta where post_id = '.$serviceID.' and meta_key = "eb_roomNum"');
		$dateYMDStart = dateToYMD($dateRangeStart);
		$dateYMDEnd = dateToYMD($dateRangeEnd);
		$dateYMDStart = gmdate($dateYMDStart." 00:00:00");
		$dateYMDEnd = gmdate($dateYMDEnd." 00:00:00");
		//$prevBookedNumForDate = 0;
		//echo '+++$dateRangeStart: '.$dateYMDStart.' to '.$dateYMDEnd.' +++';
		//AND (( dateRange_start <= "'.$dateYMDStart.'" and dateRange_end >= "'.$dateYMDStart.'") OR ( dateRange_start <= "'.$dateYMDEnd.'" and dateRange_end >= "'.$dateYMDEnd.'"))
		$getPrevBookedNum = $wpdb->get_var('select COUNT(*) from eb_bookingroomdata where roomID = '.$serviceID.' and canceled = "NO" 
		 AND ((( dateRange_start <= "'.$dateYMDStart.'" and dateRange_end > "'.$dateYMDStart.'") OR ( dateRange_start < "'.$dateYMDEnd.'" and dateRange_end > "'.$dateYMDEnd.'"))
		OR ((dateRange_start >= "'.$dateYMDStart.'" and dateRange_start < "'.$dateYMDEnd.'") OR (dateRange_end > "'.$dateYMDStart.'" and dateRange_end < "'.$dateYMDEnd.'")))');
		
		echo '+++ $getPrevBookedNum: '.$getPrevBookedNum. '+++';
		$roomNumber = $roomNum->meta_value;
		$roomNumber = $roomNumber -  $getPrevBookedNum;
		echo '+++ $roomNumber: '.$roomNumber. '+++';
		return $roomNumber;
		
	}
?>
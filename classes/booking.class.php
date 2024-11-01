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

	$serviceData = new serviceDetails;
	$servicePriceRes = $serviceData->servicePriceByDateRange($sID, $bID, $sRange, $eRange, $aPath, $wpdb, $table_prefix);
	$serviceAvailableNum = $serviceData->serviceAvailabilityByDateRange($sID, $sRange, $eRange, $aPath, $wpdb, $table_prefix);
	$responce = $servicePriceRes.'[-]'.$serviceAvailableNum;
	echo $responce;

}

	
class serviceDetails{
	public function dateToYMD($datestr){
		$dateArray = explode('/',$datestr);
		$newDate = $dateArray[2].'-'.$dateArray[1].'-'.$dateArray[0];
		return $newDate;	
	}
	
	public function serviceAvailabilityByDateRange($serviceID, $dateRangeStart, $dateRangeEnd, $absolutePath, $wpdb, $table_prefix){
		$roomNum = $wpdb->get_row('select meta_value from '.$table_prefix.'postmeta where post_id = '.$serviceID.' and meta_key = "eb_roomNum"');
		$dateYMDStart = $this->dateToYMD($dateRangeStart);
		$dateYMDEnd = $this->dateToYMD($dateRangeEnd);
		$dateYMDStart = gmdate($dateYMDStart." 00:00:00");
		$dateYMDEnd = gmdate($dateYMDEnd." 00:00:00");
		
		$getPrevBookedNum = $wpdb->get_var('select COUNT(*) from eb_bookingroomdata where roomID = '.$serviceID.' and canceled = "NO" 
		 AND ((( dateRange_start <= "'.$dateYMDStart.'" and dateRange_end > "'.$dateYMDStart.'") OR ( dateRange_start < "'.$dateYMDEnd.'" and dateRange_end > "'.$dateYMDEnd.'"))
		OR ((dateRange_start >= "'.$dateYMDStart.'" and dateRange_start < "'.$dateYMDEnd.'") OR (dateRange_end > "'.$dateYMDStart.'" and dateRange_end < "'.$dateYMDEnd.'")))');
		

		$roomNumber = $roomNum->meta_value;
		$roomNumber = $roomNumber -  $getPrevBookedNum;
		return $roomNumber;
	}
	
	public function servicePriceByDateRange($serviceID, $businessID, $dateRangeStart, $dateRangeEnd, $absolutePath, $wpdb, $table_prefix){
		$resStr = '';
		$date1Arr = explode('/',$dateRangeStart);
		$date1 = $date1Arr[2].'-'.$date1Arr[1].'-'.$date1Arr[0];
		$date2Arr = explode('/',$dateRangeEnd);
		$date2 = $date2Arr[2].'-'.$date2Arr[1].'-'.$date2Arr[0];
		$datetime1 = date_create($date1);
		$datetime2 = date_create($date2);
		$interval = date_diff($datetime1, $datetime2);
		$daysNum = (int)$interval->format('%a');
		
		$bCurrency = $wpdb->get_row('select meta_value from '.$table_prefix.'postmeta where post_id = '.$businessID.' and meta_key = "eb_currency"');
		$bCurrency = $bCurrency->meta_value;
		
		$checkIfHasSeasons = $wpdb->get_row('select meta_value from '.$table_prefix.'postmeta where post_id = '.$businessID.' and meta_key = "eb_hasSeasons"');
		
		$hasHB = $wpdb->get_row('select meta_value from '.$table_prefix.'postmeta where post_id = '.$serviceID.' and meta_key = "eb_hasBBandHB"');
		$hasHB = $hasHB->meta_value;

		if($checkIfHasSeasons->meta_value == "YES"){	
			$lowSeason = $wpdb->get_row('select meta_value from '.$table_prefix.'postmeta where post_id = '.$businessID.' and meta_key = "eb_lowSeason"');	
			$lowSeasonStart = explode('[-]',$lowSeason->meta_value);
			$lowSeasonEnd = $lowSeasonStart[1];			
			$lowSeasonStart = $lowSeasonStart[0];
			$lowSeasonStart = str_replace("2011",$date1Arr[2],$lowSeasonStart);
			$lowSeasonEnd = str_replace("2011",$date2Arr[2],$lowSeasonEnd);

			$midSeason = $wpdb->get_row('select meta_value from '.$table_prefix.'postmeta where post_id = '.$businessID.' and meta_key = "eb_midSeason"');	
			$midSeasonStart = explode('[-]',$midSeason->meta_value);
			$midSeasonEnd = $midSeasonStart[1];			
			$midSeasonStart = $midSeasonStart[0];
			$midSeasonStart = str_replace("2011",$date1Arr[2],$midSeasonStart);
			$midSeasonEnd = str_replace("2011",$date2Arr[2],$midSeasonEnd);

			$highSeason = $wpdb->get_row('select meta_value from '.$table_prefix.'postmeta where post_id = '.$businessID.' and meta_key = "eb_highSeason"');	
			$highSeasonStart = explode('[-]',$highSeason->meta_value);
			$highSeasonEnd = $highSeasonStart[1];			
			$highSeasonStart = $highSeasonStart[0];
			$highSeasonStart = str_replace("2011",$date1Arr[2],$highSeasonStart);
			$highSeasonEnd = str_replace("2011",$date2Arr[2],$highSeasonEnd);

			$midSeason2 = $wpdb->get_row('select meta_value from '.$table_prefix.'postmeta where post_id = '.$businessID.' and meta_key = "eb_midSeason2"');	
			$midSeasonStart2 = explode('[-]',$midSeason2->meta_value);
			$midSeasonEnd2 = $midSeasonStart2[1];			
			$midSeasonStart2 = $midSeasonStart2[0];
			$midSeasonStart2 = str_replace("2011",$date1Arr[2],$midSeasonStart2);
			$midSeasonEnd2 = str_replace("2011",$date2Arr[2],$midSeasonEnd2);

			$lowSeason2 = $wpdb->get_row('select meta_value from '.$table_prefix.'postmeta where post_id = '.$businessID.' and meta_key = "eb_lowSeason2"');	
			$lowSeasonStart2 = explode('[-]',$lowSeason2->meta_value);
			$lowSeasonEnd2 = $lowSeasonStart2[1];			
			$lowSeasonStart2 = $lowSeasonStart2[0];
			$lowSeasonStart2 = str_replace("2011",$date1Arr[2],$lowSeasonStart2);
			$lowSeasonEnd2 = str_replace("2011",$date2Arr[2],$lowSeasonEnd2);

			$lowSeasonPrice = $wpdb->get_row('select meta_value from '.$table_prefix.'postmeta where post_id = '.$serviceID.' and meta_key = "eb_lprice"');
			$lowSeasonPrice = $lowSeasonPrice->meta_value;
			$midSeasonPrice = $wpdb->get_row('select meta_value from '.$table_prefix.'postmeta where post_id = '.$serviceID.' and meta_key = "eb_mprice"');
			$midSeasonPrice = $midSeasonPrice->meta_value;
			$highSeasonPrice = $wpdb->get_row('select meta_value from '.$table_prefix.'postmeta where post_id = '.$serviceID.' and meta_key = "eb_hprice"');
			$highSeasonPrice = $highSeasonPrice->meta_value;
						
			$HBstr = '';
			$HBlsPrice = '';
			$HBmsPrice = '';
			$HBhsPrice = '';
			if($hasHB == "YES"){
				$HBlsPrice = $wpdb->get_row('select meta_value from '.$table_prefix.'postmeta where post_id = '.$serviceID.' and meta_key = "HB_eb_lprice"');
				$HBlsPrice = $HBlsPrice->meta_value;
				$HBmsPrice = $wpdb->get_row('select meta_value from '.$table_prefix.'postmeta where post_id = '.$serviceID.' and meta_key = "HB_eb_mprice"');
				$HBmsPrice = $HBmsPrice->meta_value;
				$HBhsPrice = $wpdb->get_row('select meta_value from '.$table_prefix.'postmeta where post_id = '.$serviceID.' and meta_key = "HB_eb_hprice"');
				$HBhsPrice = $HBhsPrice->meta_value;	
			}
			$totalPrice = 0;
			$resStr = '';
			for($dayCount = 0; $dayCount < $daysNum ; $dayCount++){
				$next_date = date('Y-m-d', strtotime($date1 .' +'.$dayCount.' day'));
				if($next_date >= $lowSeasonStart && $next_date < $lowSeasonEnd){
					$totalPrice += $lowSeasonPrice;
					$resStr.= '|'.$next_date.': <i>'. $lowSeasonPrice.' '.$bCurrency.' per night </i>';
					$HBstr .= '|<input type="hidden" id="HBPriceforNihgt_'.$dayCount.'" value= "'.$HBlsPrice.'">';
					$HBstr .= '<input type="hidden" id="HBDateforNihgt_'.$dayCount.'" value= "'.$next_date.'">';
				}
				if($next_date >= $midSeasonStart && $next_date < $midSeasonEnd){
					$totalPrice += $midSeasonPrice;
					$resStr.= '|'.$next_date.': <i>'. $midSeasonPrice.' '.$bCurrency.' per night </i>';
					$HBstr .= '|<input type="hidden" id="HBPriceforNihgt_'.$dayCount.'" value= "'.$HBmsPrice.'">';
					$HBstr .= '<input type="hidden" id="HBDateforNihgt_'.$dayCount.'" value= "'.$next_date.'">';
				}
				if($next_date >= $highSeasonStart && $next_date < $highSeasonEnd){
					$totalPrice += $highSeasonPrice;
					$resStr.= '|'.$next_date.': <i>'. $highSeasonPrice.' '.$bCurrency.' per night </i>';
					$HBstr .= '|<input type="hidden" id="HBPriceforNihgt_'.$dayCount.'" value= "'.$HBhsPrice.'">';
					$HBstr .= '<input type="hidden" id="HBDateforNihgt_'.$dayCount.'" value= "'.$next_date.'">';
				}
				if($next_date >= $midSeasonStart2 && $next_date < $midSeasonEnd2){
					$totalPrice += $midSeasonPrice;
					$resStr.= '|'.$next_date.': <i>'. $midSeasonPrice.' '.$bCurrency.' per night </i>';
					$HBstr .= '|<input type="hidden" id="HBPriceforNihgt_'.$dayCount.'" value= "'.$HBmsPrice.'">';
					$HBstr .= '<input type="hidden" id="HBDateforNihgt_'.$dayCount.'" value= "'.$next_date.'">';
				}
				if($next_date >= $lowSeasonStart2 && $next_date <= $lowSeasonEnd2){
					$totalPrice += $lowSeasonPrice;
					$resStr.= '|'.$next_date.': <i>'. $lowSeasonPrice.' '.$bCurrency.' per night </i>';
					$HBstr .= '|<input type="hidden" id="HBPriceforNihgt_'.$dayCount.'" value= "'.$HBlsPrice.'">';
					$HBstr .= '<input type="hidden" id="HBDateforNihgt_'.$dayCount.'" value= "'.$next_date.'">';
				}
			}

			$resStr = 'hasSeasons[-]'.$totalPrice.'[-]'.$resStr.'[-]'.$HBstr;
			return $resStr;
		} 
		else {		
			$SeasonPrice = $wpdb->get_row('select meta_value from '.$table_prefix.'postmeta where post_id = '.$serviceID.' and meta_key = "eb_fprice"');
			$SeasonPrice = $SeasonPrice->meta_value;					
			
			$totalPrice = $daysNum * $SeasonPrice;
			
			$HBstr = '';
			$HBPrice = '';

			if($hasHB == "YES"){
				$HBPrice = $wpdb->get_row('select meta_value from '.$table_prefix.'postmeta where post_id = '.$serviceID.' and meta_key = "HB_eb_fprice"');
				$HBPrice = $HBPrice->meta_value;
				$HBstr .= '<input type="hidden" id="HBPriceforNihgt" value= "'.$HBPrice.'">';
				$HBstr .= '<input type="text" id="HBPriceforNihgtTxt" value= ">> '.$HBPrice.'">';
				//$HBstr .= '<input type="hidden" id="HBDateforNihgt_'.$daysNum.'" value= "'.$next_date.'">';
			}
			$resStr = $daysNum.' nights * '.$SeasonPrice. ' '.$bCurrency;
			$resStr = 'hasNoSeasons[-]'.$totalPrice.'[-]'.$resStr.'[-]'.$HBstr;
			return $resStr;
		}
	}
	
}

class searchServices extends businessData{

	public function checkServicesAvailability($serviceID, $businessID, $wpdb,$table_prefix){
 		//return 'Testing for room ID: '. $serviceID;
 		$businesses = $wpdb->get_results('select post_title from '.$table_prefix.'posts where ID ='.$serviceID);
 		//$checkIfHasSeasons = $this->serviceOperatingPeriod($serviceID, $businessID, $wpdb, $table_prefix);
 		
		foreach ($businesses as $business){
			return $business->post_title . ' ==== '.$checkIfHasSeasons;
		}//end of foreach
	}
	
	/*public function serviceOperatingPeriod($serviceID, $businessID, $wpdb){
		//Elegxoume an to parent business exei seasons h' oxi
		$checkIfHasSeasons = $wpdb->get_row('select meta_value from wp_postmeta where post_id = '.$businessID.' and meta_key = "eb_hasSeasons"');
		
		return $checkIfHasSeasons->meta_value; 
	}*/
	
	public function businessOperatingPeriod($businessID, $wpdb, $startORend, $table_prefix){
		$checkIfHasSeasons = $wpdb->get_row('select meta_value from '.$table_prefix.'postmeta where post_id = '.$businessID.' and meta_key = "eb_hasSeasons"');
		if($checkIfHasSeasons->meta_value == "YES"){
			$startPeriod = '';
			$endPeriod = '';
			$startPeriod = parent::businessSeason($businessID, $wpdb, 'eb_lowSeason', 'START',$table_prefix);
			if ($startPeriod == '') $startPeriod = parent::businessSeason($businessID, $wpdb, 'eb_midSeason', 'START', $table_prefix);
			
			$endPeriod = parent::businessSeason($businessID, $wpdb, 'eb_lowSeason2', 'END', $table_prefix);
			if ($endPeriod == '') $endPeriod = parent::businessSeason($businessID, $wpdb, 'eb_midSeason2', 'END', $table_prefix);
			
			if($startORend == "start") return $startPeriod;
			elseif($startORend == "end") return $endPeriod;
			else return $startPeriod . ' [-] '.$endPeriod;			
		}
		if($checkIfHasSeasons->meta_value == "NO"){
			$startPeriod = $wpdb->get_row('select meta_value from '.$table_prefix.'postmeta where post_id = '.$businessID.' and meta_key = "eb_operatingPeriodStart"');	
			$startPeriod = $startPeriod->meta_value;
			$endPeriod = $wpdb->get_row('select meta_value from '.$table_prefix.'postmeta where post_id = '.$businessID.' and meta_key = "eb_operatingPeriodEnd"');	
			$endPeriod = $endPeriod->meta_value;
			
			if($startORend == "start") return $startPeriod;
			elseif($startORend == "end") return $endPeriod;
			else return $startPeriod . ' [-] '.$endPeriod;
		}
	}
	
	


	
}	


class businessData{
	public function businessSeason($businessID, $wpdb, $season, $switch, $table_prefix){
		if($switch == "START") $switch = 0; else $switch = 1;
		$startPeriod = $wpdb->get_row('select meta_value from '.$table_prefix.'postmeta where post_id = '.$businessID.' and meta_key = "'.$season.'"');
		if(!empty($startPeriod))
		if($startPeriod->meta_value != ""){
			$startPeriod = explode('[-]', $startPeriod->meta_value);
			if($startPeriod[$switch] != '') return $startPeriod[$switch];
			else return 'NO_VALID_DATE';	
		}
	}
}

?>
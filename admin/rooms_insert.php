<?php
global $table_prefix;
$hasErrors = false;
$errorMessage = '';
global $q_config;

 $titleStr = addslashes( $_POST['roomTitle'] );
		if(isset($_POST['eb_isMultyLang']) && $_POST['eb_isMultyLang'] == "true"){
			$titleStr = '';
			foreach($q_config['enabled_languages'] as $language) {
				if(isset($_POST['roomTitle_'.$language])){
					$titleStr .= '<!--:'.$language.'-->'.$_POST['roomTitle_'.$language].'<!--:-->';  	
				}
				
			}
		}
		
		
$newRoomData = array(
  'post_author' =>  $_POST['ebOwnerID'],
  'post_date' => $_POST['curTime'],
  'post_date_gmt' => gmdate("Y-m-d H:i:s"),
  'post_name' => $titleStr,
  'post_title' => $titleStr,
  'post_type' => "rooms",
  'post_content' => $_POST['content'],
  'post_parent' => $_POST['bID']

);  
$facilitiesStr = '';
		$facilities = $_POST['facilitiesChBox'];
		for($i=0; $i < sizeof($facilities); $i++){
			$facilitiesStr .= $facilities[$i].'|';
		}

echo '<script type="text/javascript" >jQuery("#message").html();</script>';
$roomId = '';
if($_POST['roomTitle'] ==''){
	$hasErrors = true;
	$errorMessage .= '<p><em>You have to enter a name to the room</em></p>';
}
//*****Check if room name exist already*****
//Edw prepei na elegxei to select kai an to onoma to dwmatiou anhkei sthn idia epixeirhsh k oxi ston idio idiokthth
$nameExistRes = $wpdb->get_row('select post_title from wp_posts where post_title = "'.$_POST['roomTitle'].'" and post_author="'.$_POST['ebOwnerID'].'"');
if(!empty($nameExistRes)){
	$hasErrors = true;
	$errorMessage .= '<p><em>The name of the room you entered <font color="red">('.$_POST['roomTitle'].')</font> already exist. Please try using a different one</em></p>';
}
if($_POST['ebOwnerID'] ==''){
	$hasErrors = true;
	$errorMessage .= '<p><em>You have to enter an owner to the business. Please Go Back to edit business preferences.</em></p>';
}


if(!$hasErrors){
	if ($roomId = wp_insert_post( $newRoomData, $wp_error )){
		$newRoomID = $roomId;
		if($_POST['roomNum']!= '') add_post_meta($newRoomID, 'eb_roomNum', $_POST['roomNum'], true);				
		if(isset($_POST['peopleNum'])) add_post_meta($newRoomID, 'eb_peopleNum', $_POST['peopleNum'], true);
		
		if($_POST['babiesAllowed']!= '') add_post_meta($newRoomID, 'eb_babiesAllowed', $_POST['babiesAllowed'], true);
		if($_POST['childrenAllowed']!= '') add_post_meta($newRoomID, 'eb_childrenAllowed', $_POST['childrenAllowed'], true);
		if($_POST['extraBedsAvailable']!= '') add_post_meta($newRoomID, 'eb_extraBedsAvailable', $_POST['extraBedsAvailable'], true);
		$roomPriceVar = '';	
		if(isset($_POST['lprice'])){
			$roomPriceVar = correctPriceNum($_POST['lprice']);
			add_post_meta($newRoomID, 'eb_lprice', $roomPriceVar, true);
		}
		//if($_POST['mprice']!= '') add_post_meta($newRoomID, 'eb_mprice', $_POST['mprice'], true);
		if(isset($_POST['mprice'])){
			$roomPriceVar = correctPriceNum($_POST['mprice']);
			add_post_meta($newRoomID, 'eb_mprice', $roomPriceVar, true);
		}
		//if($_POST['hprice']!= '') add_post_meta($newRoomID, 'eb_hprice', $_POST['hprice'], true);
		if(isset($_POST['hprice'])){
			$roomPriceVar = correctPriceNum($_POST['hprice']);
			add_post_meta($newRoomID, 'eb_hprice', $roomPriceVar, true);
		}
		//if($_POST['fprice']!= '') add_post_meta($newRoomID, 'eb_fprice', $_POST['fprice'], true);
		if(isset($_POST['fprice'])){
			$roomPriceVar = correctPriceNum($_POST['fprice']);
			add_post_meta($newRoomID, 'eb_fprice', $roomPriceVar, true);
			
		}
		//============================================
		//Fill the busHelpVal table with lower and higher prices of the business
		//============================================
		$maxPrice = 0;
		$lowPrice = 0;
		$mainCur = get_option('eb_siteCurrency');
		$bCur = get_post_meta($_POST['bID'], "eb_currency");
		$bCur = $bCur[0]; 
		//fetch all rooms
		$roomsList = $wpdb->get_results('select ID from '.$table_prefix.'posts where post_parent = '.$_POST['bID']);
		foreach($roomsList as $r){
			
				$minPriceR = $wpdb->get_row('select MIN(meta_value) as mm, MAX(meta_value) as mx from '.$table_prefix.'postmeta where post_id = '.$r->ID.' AND ( meta_key = "eb_fprice" OR meta_key = "eb_lprice" OR meta_key = "eb_hprice")');
				if( $lowPrice == 0 ) $lowPrice = $minPriceR->mm;
				if( $lowPrice > $minPriceR->mm) $lowPrice = $minPriceR->mm;
				if( $maxPrice == 0 ) $maxPrice = $minPriceR->mx;
				if( $maxPrice < $minPriceR->mx) $maxPrice = $minPriceR->mx;	

			
		}
		if($mainCur != $bCur){
			$lowPrice = convert($lowPrice, $bCur,$mainCur );
			$maxPrice = convert($maxPrice, $bCur,$mainCur );
		}
		$wpdb->query('update eb_bushelpvals set min_price = '.$lowPrice.', max_price = '.$maxPrice.' where bID = '.$_POST['bID']);
		
		//============================================
		//    IF IT HAS BB AND HB
		//============================================
		if($_POST['BBorHB_check'] == 'YES') {
			add_post_meta($newRoomID, 'eb_hasBBandHB', "YES", true);
			//if($_POST['HB_lprice']!= '') add_post_meta($newRoomID, 'HB_eb_lprice', $_POST['HB_lprice'], true);
			if(isset($_POST['HB_lprice'])){
				$roomPriceVar = correctPriceNum($_POST['HB_lprice']);
				add_post_meta($newRoomID, 'HB_eb_lprice', $roomPriceVar, true);
			}
			//if($_POST['HB_mprice']!= '') add_post_meta($newRoomID, 'HB_eb_mprice', $_POST['HB_mprice'], true);
			if(isset($_POST['HB_mprice'])){
				$roomPriceVar = correctPriceNum($_POST['HB_mprice']);
				add_post_meta($newRoomID, 'HB_eb_mprice', $roomPriceVar, true);
			}
			//if($_POST['HB_hprice']!= '') add_post_meta($newRoomID, 'HB_eb_hprice', $_POST['HB_hprice'], true);
			if(isset($_POST['HB_hprice'])){
				$roomPriceVar = correctPriceNum($_POST['HB_hprice']);
				add_post_meta($newRoomID, 'HB_eb_hprice', $roomPriceVar, true);
			}
			//if($_POST['HB_fprice']!= '') add_post_meta($newRoomID, 'HB_eb_fprice', $_POST['HB_fprice'], true);
			if(isset($_POST['eb_lateCheckoutPrice'])){
				$roomPriceVar = correctPriceNum($_POST['eb_lateCheckoutPrice']);
				add_post_meta($newRoomID, 'eb_lateCheckoutPrice', $roomPriceVar, true);
			}	
		}
		
		if(isset($_POST['mprice'])){
			$roomPriceVar = correctPriceNum($_POST['mprice']);
			add_post_meta($newRoomID, 'eb_mprice', $roomPriceVar, true);
		}
		
		
		if($facilitiesStr != '')
			add_post_meta($newRoomID, 'eb_room_facilities', $facilitiesStr, true);
	
	$roomTitle = $_POST['roomTitle'];
	$roomNum = $_POST['roomNum'];
	$peopleNum = $_POST['peopleNum'];
	$lprice = $_POST['lprice'];
	$mprice = $_POST['mprice'];
	$hprice = $_POST['hprice'];
	$fprice = $_POST['fprice'];
	$RoomDesc = $_POST['content'];
	$eb_BusinessFacilities = $facilitiesStr;	
	}
	else {	
	$roomTitle = $_POST['roomTitle'];
	$roomNum = $_POST['roomNum'];
	$peopleNum = $_POST['peopleNum'];
	$lprice = $_POST['lprice'];
	$mprice = $_POST['mprice'];
	$hprice = $_POST['hprice'];
	$fprice = $_POST['fprice'];
	$RoomDesc = $_POST['content'];
	$eb_BusinessFacilities = $facilitiesStr;	
	
	}
	//}
echo '<div align="center"><div id="message" class="updated" style="width:700px" align="center"><strong>Room Added Successfully!</strong></div></div>';
}
else{?>
	<div align="center">
	<div id="message" class="updated" style="width:700px" align="center">
		<strong><?php echo $errorMessage;?></strong>
	</div>
	</div>
	<?php
	$form_action = "add";
	
	$roomTitle = $_POST['roomTitle'];
	$roomNum = $_POST['roomNum'];
	$peopleNum = $_POST['peopleNum'];
	$lprice = $_POST['lprice'];
	$mprice = $_POST['mprice'];
	$hprice = $_POST['hprice'];
	$fprice = $_POST['fprice'];
	$RoomDesc = $_POST['content'];
	$eb_BusinessFacilities = $facilitiesStr;	

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
?>

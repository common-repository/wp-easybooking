<?php
global $table_prefix;
global $q_config;
 $hasErrors = false;
 $errorMessage = '';
 $existRoomID = $_POST['rID'];
 if($_POST['rID'] == '' || !isset($_POST['rID'])) $existRoomID = $roomId;
 if($existRoomID == '') $existRoomID = $_REQUEST['rID'];
 if($existRoomID == '') $existRoomID = $_GET['rID'];
 if($existRoomID == '') $existRoomID = $roomId;

 $roomId = addslashes( $existRoomID );
 
 $titleStr = addslashes( $_POST['roomTitle'] );
		if(isset($_POST['eb_isMultyLang']) && $_POST['eb_isMultyLang'] == "true"){
			$titleStr = '';
			foreach($q_config['enabled_languages'] as $language) {
				if(isset($_POST['roomTitle_'.$language])){
					$titleStr .= '<!--:'.$language.'-->'.$_POST['roomTitle_'.$language].'<!--:-->';  	
				}
				
			}
		}


$existRoomData = array(
  'ID' => $existRoomID,
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
if($_POST['roomTitle'] ==''){
	$hasErrors = true;
	$errorMessage .= '<p><em>You have to enter a name to the room</em></p>';
}
//*****Check if room name exist already*****
/*$nameExistRes = $wpdb->get_row('select post_title from wp_posts where post_title = "'.$_POST['roomTitle'].'" and ID !="'.$existRoomID.'"');
if(!empty($nameExistRes)){
	$hasErrors = true;
	$errorMessage .= '<p><em>The name of the room you entered <font color="red">('.$_POST['roomTitle'].')</font> already exist. Please try using a different one</em></p>';
}*/
if($_POST['ebOwnerID'] ==''){
	$hasErrors = true;
	$errorMessage .= '<p><em>You have to enter an owner to the business. Please Go Back to edit business preferences.</em></p>';
}


if(!$hasErrors){
		wp_update_post( $existRoomData, $wp_error );
		$roomId = $existRoomID;
		
		if($_POST['roomNum']!= '') update_post_meta($existRoomID, 'eb_roomNum', $_POST['roomNum']);				
		if(isset($_POST['peopleNum'])) update_post_meta($existRoomID, 'eb_peopleNum', $_POST['peopleNum']);
		
		if(isset($_POST['babiesAllowed'])) update_post_meta($existRoomID, 'eb_babiesAllowed', $_POST['babiesAllowed']);
		if(isset($_POST['childrenAllowed'])) update_post_meta($existRoomID, 'eb_childrenAllowed', $_POST['childrenAllowed']);
		if(isset($_POST['extraBedsAvailable'])) update_post_meta($existRoomID, 'eb_extraBedsAvailable', $_POST['extraBedsAvailable']);		
				
		//if($_POST['lprice']!= '') update_post_meta($existRoomID, 'eb_lprice', $_POST['lprice']);
		if(isset($_POST['lprice'])){
			$roomPriceVar = correctPriceNum($_POST['lprice']);
			update_post_meta($existRoomID, 'eb_lprice', $roomPriceVar);
			delete_post_meta($existRoomID, 'eb_fprice');
		}
		//if($_POST['mprice']!= '') update_post_meta($existRoomID, 'eb_mprice', $_POST['mprice']);
		if(isset($_POST['mprice'])){
			$roomPriceVar = correctPriceNum($_POST['mprice']);
			update_post_meta($existRoomID, 'eb_mprice', $roomPriceVar);
		}
		//if($_POST['hprice']!= '') update_post_meta($existRoomID, 'eb_hprice', $_POST['hprice']);
		if(isset($_POST['hprice'])){
			$roomPriceVar = correctPriceNum($_POST['hprice']);
			update_post_meta($existRoomID, 'eb_hprice', $roomPriceVar);
		}
		//if($_POST['fprice']!= '') update_post_meta($existRoomID, 'eb_fprice', $_POST['fprice']);
		if(isset($_POST['fprice'])){
			$roomPriceVar = correctPriceNum($_POST['fprice']);
			update_post_meta($existRoomID, 'eb_fprice', $roomPriceVar);
			delete_post_meta($existRoomID, 'eb_lprice');
			delete_post_meta($existRoomID, 'eb_mprice');
			delete_post_meta($existRoomID, 'eb_hprice');
		}
		
		//============================================
		//Fill the busHelpVal table with lower and higher prices of the business
		//============================================
		$mainCur = get_option('eb_siteCurrency');
		$bCur = get_post_meta($_POST['bID'], "eb_currency");
		$bCur = $bCur[0];
		//fetch all rooms
		$roomsList = $wpdb->get_results('select ID from '.$table_prefix.'posts where post_parent = '.$_POST['bID']);
		$maxPrice = 0;
		$lowPrice = 0;
		foreach($roomsList as $r){
			$minPriceR = $wpdb->get_row('select MIN(meta_value) as mm, MAX(meta_value) as mx from '.$table_prefix.'postmeta where post_id = '.$r->ID.' AND ( meta_key = "eb_fprice" OR meta_key = "eb_lprice" OR meta_key = "eb_hprice")');			

			if( $lowPrice == 0 ) $lowPrice = $minPriceR->mm;
			if( $lowPrice > $minPriceR->mm) $lowPrice = $minPriceR->mm;
			if( $maxPrice == 0 ) $maxPrice = $minPriceR->mx;
			if( $maxPrice < $minPriceR->mx) $maxPrice = $minPriceR->mx;									
		}
		if($mainCur != $bCur){
		$lowPrice = number_format( convert($lowPrice, $bCur,$mainCur ), 2);
		$maxPrice = number_format( convert($maxPrice, $bCur,$mainCur ), 2);
		}
		//An den exei ginei to insert
		//$prevHV = $wpdb->get_row('select * from eb_bushelpvals where bID = '.$_POST['bID']);
		//if( empty($prevHV) ) $wpdb->query('insert into eb_bushelpvals(bID) values('.$_POST['bID'].')');
		$wpdb->query('update eb_bushelpvals set min_price = '.$lowPrice.', max_price = '.$maxPrice.' where bID = '.$_POST['bID']);
		
		//============================================
		//    IF IT HAS BB AND HB
		//============================================
		if($_POST['BBorHB_check'] == 'YES') {
			update_post_meta($existRoomID, 'eb_hasBBandHB', "YES");
			//if($_POST['HB_lprice']!= '') update_post_meta($existRoomID, 'HB_eb_lprice', $_POST['HB_lprice']);
			if(isset($_POST['HB_lprice'])){
				$roomPriceVar = correctPriceNum($_POST['HB_lprice']);
				update_post_meta($existRoomID, 'HB_eb_lprice', $roomPriceVar);
			}
			//if($_POST['HB_mprice']!= '') update_post_meta($existRoomID, 'HB_eb_mprice', $_POST['HB_mprice']);
			if(isset($_POST['HB_mprice'])){
				$roomPriceVar = correctPriceNum($_POST['HB_mprice']);
				update_post_meta($existRoomID, 'HB_eb_mprice', $roomPriceVar);
			}
			//if($_POST['HB_hprice']!= '') update_post_meta($existRoomID, 'HB_eb_hprice', $_POST['HB_hprice']);
			if(isset($_POST['HB_hprice'])){
				$roomPriceVar = correctPriceNum($_POST['HB_hprice']);
				update_post_meta($existRoomID, 'HB_eb_hprice', $roomPriceVar);
			}
			//if($_POST['HB_fprice']!= '') update_post_meta($existRoomID, 'HB_eb_fprice', $_POST['HB_fprice']);
			if(isset($_POST['HB_fprice'])){
				$roomPriceVar = correctPriceNum($_POST['HB_fprice']);
				update_post_meta($existRoomID, 'HB_eb_fprice', $roomPriceVar);
			}	
		}else {
			 delete_post_meta($existRoomID, 'eb_hasBBandHB');
			 delete_post_meta($existRoomID, 'HB_eb_lprice');
			 delete_post_meta($existRoomID, 'HB_eb_mprice');
			 delete_post_meta($existRoomID, 'HB_eb_hprice');
			 delete_post_meta($existRoomID, 'HB_eb_fprice');
		}
		
		if(isset($_POST['eb_lateCheckoutPrice'])){
				$roomPriceVar = correctPriceNum($_POST['eb_lateCheckoutPrice']);
				update_post_meta($existRoomID, 'eb_lateCheckoutPrice', $roomPriceVar);
		}
		else delete_post_meta($existRoomID, 'eb_lateCheckoutPrice');
		
		//if($facilitiesStr != '')
			update_post_meta($existRoomID, 'eb_room_facilities', $facilitiesStr);
	
	
		$roomTitle = $_POST['roomTitle'];
		$roomNum = $_POST['roomNum'];
		$peopleNum = $_POST['peopleNum'];
		$lprice = $_POST['lprice'];
		$mprice = $_POST['mprice'];
		$hprice = $_POST['hprice'];
		$fprice = $_POST['fprice'];
		$RoomDesc = $_POST['content'];
		$eb_BusinessFacilities = $facilitiesStr;	
		
		echo '<div align="center"><div id="message" class="updated" style="width:700px" align="center"><strong>Changes Saved Successfully!</strong></div></div>';
		
}
else{?>
        <div align="center">
        <div id="message" class="updated" style="width:700px" align="center">
            <strong><?php echo $errorMessage;?></strong>
        </div>
        </div>
        <?php
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

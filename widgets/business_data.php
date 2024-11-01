<?php
global $wpdb;

class resort {
	var $bID;
	var $table_prefix;
	var $from;
	var $to;
	var $ccur;
	var $lang;
	var $openDate;
	var $closeDate;
	var $datesAreInOperatingPeriod;

	function __construct($bId, $cur, $from, $to, $tblPref, $clang){
		global $wpdb;
		$this->bID = $bId;
		$this->table_prefix = $tblPref;
		$this->ccur = $cur;
		$this->lang = $clang;
		
		$this->from = $from;
		$this->to = $to;
		
		
		
		//===== OPERATING PERIOD =====
		$this->openDate = get_post_meta($this->bID, "eb_operatingPeriodStart");
		if(!empty($this->openDate)) $this->openDate = $this->openDate[0]; else $this->openDate ='';
		
		if( $this->openDate == "NOT_SET" ){
			$eb_getseason = get_post_meta($this->bID, "eb_lowSeason");
			if(!empty($eb_getseason)) {
				$eb_getseason = $eb_getseason[0]; 
				$eb_getseason = explode("[-]", $eb_getseason);	
				$this->openDate = $eb_getseason[0];
			}
			else $this->openDate ='';			
		}
		
		$this->closeDate = get_post_meta($this->bID, "eb_operatingPeriodEnd");
		if(!empty($this->closeDate)) $this->closeDate = $this->closeDate[0]; else $this->closeDate ='';
				
		if( $this->closeDate == "NOT_SET" ){
			$eb_getseason = get_post_meta($this->bID, "eb_lowSeason2");
			if(!empty($eb_getseason)) {
				$eb_getseason = $eb_getseason[0]; 
				$eb_getseason = explode("[-]", $eb_getseason);	
				$this->closeDate = $eb_getseason[1];
			}
			else {
				$eb_getseason = get_post_meta($this->bID, "eb_midSeason2");
				if(!empty($eb_getseason)) {
					$eb_getseason = $eb_getseason[0]; 
					$eb_getseason = explode("[-]", $eb_getseason);	
					$this->closeDate = $eb_getseason[1];
				}
				else{
					$eb_getseason = get_post_meta($this->bID, "eb_highSeason");
					if(!empty($eb_getseason)) {
						$eb_getseason = $eb_getseason[0]; 
						$eb_getseason = explode("[-]", $eb_getseason);	
						$this->closeDate = $eb_getseason[1];
					}
				}
			}		
		}
		//=========================================
		$this->datesAreInOperatingPeriod = $this->checkIfDatesInOperatingPeriod();

		$roomsList = $this->rooms();
				
		echo $roomsList;
	}
	
	function checkIfDatesInOperatingPeriod(){
		$sFrom = $this->from;
		$sTo = $this->to;
		$sOpen = $this->openDate;
		$sClose = $this->closeDate;

		$sFrom = explode('-', $sFrom);
		$sFrom = $sFrom[1].'-'.$sFrom[0];
		$sTo = explode('-', $sTo);
		$sTo = $sTo[1].'-'.$sTo[0];		
		$sOpen = explode('-', $sOpen);		
		$sOpen = $sOpen[1].'-'.$sOpen[2];			
		$sClose = explode('-', $sClose);
		$sClose = $sClose[1].'-'.$sClose[2];
		
		if( $sFrom < $sOpen ) return false;
		if( $sFrom > $sClose ) return false;
		if( $sTo < $sOpen ) return false;
		if( $sTo > $sClose ) return false;
		
		return true;
		
	}
	
	function rooms(){
		global $wpdb;
		include(ABSPATH.'wp-content/plugins/wp-easybooking/widgets/trans-vars/resort.trans.php');
		$resortPageID = get_option('eb-view-resort');
		
		$page_id = get_option('eb-booking-review');
		$permalink = '';
		if( get_option('permalink_structure')  == "") $permalink = get_site_url().'?page_id='.$page_id;
		else $permalink = get_permalink( $page_id );
	
		$resp = '';
		$rooms = $wpdb->get_results('select ID, post_title, post_content from '.$this->table_prefix.'posts where post_parent = '.$this->bID.' AND post_type = "rooms"');
		$resp .= '<table style="border:none;text-align:center; border-collapse:separate;border-spacing:0 5px;" cellspacing="0px" cellpadding="0px">';
		$location = addslashes( $_POST['location'] );
		$locationType = addslashes( $_POST['type'] );
		$locationID = addslashes( $_POST['lid'] );
		$resp .= '<thead>
			<tr>
			<td style="text-align:center;">'.__($eb_lang_ResortRoomType).'</td>
			<td style="text-align:center;">'.__($eb_lang_RoomsAdults).'</td>
			<td style="text-align:center;">'.__($eb_lang_RoomsChildren).'</td>
			<td style="text-align:center;">'.__($eb_lang_RoomsBabies).'</td>';
			
			if( $this->from != '' && $this->to!= '' && $this->datesAreInOperatingPeriod ){
			$resp .= '<td style="text-align:center;">'.__($eb_lang_RoomsPrice).'</td>
			<td style="text-align:center;">'.__($eb_lang_Rooms_Available).'</td>';
			}
			
			
			$resp .= '</tr>
		</thead>
		<tfoot>
			<tr>
				<td colspan="7" align="right" style="text-align:right;">
					<form action="'.$permalink.'" method="post">
						<input type="hidden" name = "eb" value="booking" />
						<input type="hidden" id="booking-data" name = "booking-data" value="" />
						<input type="hidden" name="bID" value="'.$this->bID.'" />
						<input type="hidden" name="from" value="'.$this->from.'" />
						<input type="hidden" name="to" value="'.$this->to.'" />
						<input type="hidden" name="ccur" value="'.$this->ccur.'" />
						<input type="hidden" name="cur" value="'.$this->ccur.'" />
						
						<input type="hidden" name="location" value="'.$location.'" />
						<input type="hidden" id="eb-location-type" name="type" value="'.$locationType.'" />
						<input type="hidden" id="eb-location-id" name="lid" value="'.$locationID.'" />
						<input type="hidden" id = "bID" name="b" value="'.$bID.'" />';
						if( $this->from != '' && $this->to!= '' && $this->datesAreInOperatingPeriod )
						$resp .= '<input id="booking-btn" type = "submit" value = "'.__($eb_lang_MakeYourBooking).'" class="eb-search-button" disabled="disabled" />';						
					$resp .= '</form>
				</td>
			</tr>
		</tfoot>
		<tbody>';
		foreach ( $rooms as $room ){
			$adultsInRoom = get_post_meta($room->ID, "eb_peopleNum");
			$adultsInRoom = $adultsInRoom[0];
			$childrenInRoom = get_post_meta($room->ID, "eb_childrenAllowed");
			$childrenInRoom = $childrenInRoom[0];
			$babiesInRoom = get_post_meta($room->ID, "eb_babiesAllowed");			
			$babiesInRoom = $babiesInRoom[0];
			
			$roomLogo = get_post_meta($room->ID, "eb_defaultLogo");
			
			$roomImagesAr = get_post_meta($room->ID, "eb_logo");
			if( !empty($roomImagesAr) ) $roomImagesAr = explode('|', $roomImagesAr[0]); 
			$roomImages = '';
			for( $ic = 0;$ic <= sizeof( $roomImagesAr ); $ic++){
				if( $roomImagesAr[$ic] != ''){
					$roomImages .= '<span style="padding:2px;">
										<a class="thickbox" href="'.WP_CONTENT_URL.'/plugins/wp-easybooking/images/RoomImg/'.$roomImagesAr[$ic].'">
											<img class="room-img-item" width="100px;" style="overflow: auto;" src = "'.WP_CONTENT_URL.'/plugins/wp-easybooking/images/RoomImg/thumbs/'.$roomImagesAr[$ic].'">
										</a>
									</span>';
				}
			}
			
			$roomFacilitiesAr = get_post_meta($room->ID, "eb_room_facilities");

			$roomFacilities = '';
			if( !empty( $roomFacilitiesAr ) ) {
				$roomFacilitiesAr = explode( '|', $roomFacilitiesAr[0] );
				$roomFacilitiesAr = implode( ',', $roomFacilitiesAr );
				$roomFacilitiesAr = substr_replace($roomFacilitiesAr ,"",-1);
				
				$f = $wpdb->get_results('select * from '.$this->table_prefix.'eb_facilities where facility_id IN ('.$roomFacilitiesAr.')');
				foreach( $f as $facility ){
					$roomFacilities .= '<a style="padding: 5px;" title ="'.$facility->facility_description.'">';
						if( $facility->image!= '' ) $roomFacilities .= '<img src = "'.WP_CONTENT_URL.'/plugins/wp-easybooking/images/icons/'.$facility->image.'" />'; 
					$roomFacilities .= $facility->facility_name.'</a>';
				}
			}
			
			$price = $this->roomPrice( $this->bID, $room->ID, $this->from, $this->to );
			$rooms = $this->room_count( $room->ID );
			if( $rooms != null ){				
				$resp.= '<tr>';
					$resp .= '<td  valign="top" style="border:none;padding-left:10px;">
								<a class="room-details-btn" onclick = "showRoomDetails('.$room->ID.')" title="'.__($room->post_title).'">
									<div class="room-title-area" style="background-image:url('.WP_CONTENT_URL.'/plugins/wp-easybooking/images/RoomImg/thumbs/'.$roomLogo[0].')">
										<strong style="color:#fff">'.__($room->post_title).'</strong> <span class="room-details-label">'.__( $eb_lang_Details ).'</span>
									</div>
								</a>
						</td>';
					$resp .= '<td style="border:none;text-align:center;">'.$adultsInRoom.'</td>';
					$resp .= '<td style="border:none;text-align:center;">'.$childrenInRoom.'</td>';
					$resp .= '<td style="border:none;text-align:center;">'.$babiesInRoom.'</td>';
					if( $this->from != '' && $this->to!= '' && $this->datesAreInOperatingPeriod ){
					$resp .= '<td style="border:none;text-align:center;">'.$price.'</td>';
					$resp .= '<td style="border:none;text-align:center;">'.$rooms.'</td>';
					}				
				$resp.= "</tr>";
				
				$resp.= '<tr id="room-details-'.$room->ID.'" class="room-details-area" style="display:none">';
					$resp.= '<td colspan="7" style="border:none;"><div class="room-details-container">
								<div class="general-title" style="width:auto;"><strong>'.__($room->post_title).'</strong></div>
								<div>
									<strong>'.__( $eb_lang_Images ).'</strong>
									<div class="sub-container">
										'.$roomImages.'
									</div>
								</div>
								<div>								
									<strong>'.__( $eb_lang_Description ).'</strong>
									<div class="sub-container">
										'.__( $room->post_content ).'
									</div>
								</div>
								<div>								
									<strong>'.__( $eb_lang_RoomFacilities ).'</strong>
									<div class="sub-container">
										'.__( $roomFacilities ).'
									</div>
								</div>
							</div></td>';
				$resp.= '</tr>';				
			}
		}
		$resp.= '</tbody>';
		$resp.= "</table>";
		return $resp;
	}
	

	function room_count( $rID ){
		global $wpdb;
		$roomCounter = 0;
		$rooms = get_post_meta($rID, "eb_roomNum");
		if( count( $rooms) > 0 ) $roomCounter = (int)$rooms[0];
		
		$fromDate = explode('-', $this->from);
		$this->fromYear = $fromDate[2];
		$fromDate = $fromDate[2].'-'.$fromDate[1].'-'.$fromDate[0].' 00:00:00';				
				
		$toDate = explode('-', $this->to);
		$this->toYear = $toDate[2];
		$toDate = $toDate[2].'-'.$toDate[1].'-'.$toDate[0].' 00:00:00';
		
		$roomSubQ = 'select * from  eb_bookingroomdata where roomID = '.$rID.' AND canceled = "NO"
					AND ((( dateRange_start <= "'.$fromDate.'" and dateRange_end > "'.$fromDate.'") OR ( dateRange_start < "'.$toDate.'" and dateRange_end > "'.$toDate.'"))
					OR ((dateRange_start >= "'.$fromDate.'" and dateRange_start < "'.$toDate.'") OR (dateRange_end > "'.$fromDate.'" and dateRange_end < "'.$toDate.'")))';
		
		$roomsPrevBooked = $wpdb->get_results( $roomSubQ );

		$numToSubFromRooms = 0;
		$subRoomsRes = sizeof($roomsPrevBooked);

		$roomCounter -= $subRoomsRes;
		$roomOptions='';
		for( $rc = 0;$rc <= $roomCounter;$rc++){
			$roomOptions .= '<option value="'.$rc.'" ';
			if( $rc == 0) $roomOptions .= 'selected';
			$roomOptions .= '>'.$rc.'</option>';
		}
		$roomSelect = '
			<select id="roomsFor_'.$rID.'" name="roomsFor_'.$rID.'" onchange = "selectRoomsForBooking('.$rID.')">
				'.$roomOptions.'
			</select>
		';
		
		if( $roomCounter > 0 ) return $roomSelect;
		else return null;
	}
	

	function roomPrice( $bID, $rID, $fromDate, $toDate ){
		global $wpdb;
		$interval = date_diff(date_create( $fromDate ), date_create( $toDate ) );
		$daysNum = (int)$interval->format('%a');
		
		$bcur = $wpdb->get_row('select meta_value from '.$this->table_prefix.'postmeta where post_id = '.$bID.' and meta_key = "eb_currency"');
		$bcur = $bcur->meta_value;
		
		$roomPrice = 0;
		
		$checkIfHasSeasons = $wpdb->get_row('select meta_value from '.$this->table_prefix.'postmeta where post_id = '.$bID.' and meta_key = "eb_hasSeasons"');
		if($checkIfHasSeasons->meta_value == "YES"){
			$fDate = explode('-', $this->from);
			$fromYear = $fDate[2];
			$tDate = explode('-', $this->to);
			$toYear = $tDate[2];
			
			$lowSeason = $wpdb->get_row('select meta_value from '.$this->table_prefix.'postmeta where post_id = '.$bID.' and meta_key = "eb_lowSeason"');	
			$lowSeasonStart = explode('[-]',$lowSeason->meta_value);
			$lowSeasonEnd = $lowSeasonStart[1];			
			$lowSeasonStart = $lowSeasonStart[0];
			$lowSeasonStart = str_replace("2011",$fromYear,$lowSeasonStart);
			$lowSeasonEnd = str_replace("2011",$toYear,$lowSeasonEnd);
			
			$midSeason = $wpdb->get_row('select meta_value from '.$this->table_prefix.'postmeta where post_id = '.$bID.' and meta_key = "eb_midSeason"');	
			$midSeasonStart = explode('[-]',$midSeason->meta_value);
			$midSeasonEnd = $midSeasonStart[1];			
			$midSeasonStart = $midSeasonStart[0];
			$midSeasonStart = str_replace("2011",$fromYear,$midSeasonStart);
			$midSeasonEnd = str_replace("2011",$toYear,$midSeasonEnd);
			
			$highSeason = $wpdb->get_row('select meta_value from '.$this->table_prefix.'postmeta where post_id = '.$bID.' and meta_key = "eb_highSeason"');	
			$highSeasonStart = explode('[-]',$highSeason->meta_value);
			$highSeasonEnd = $highSeasonStart[1];			
			$highSeasonStart = $highSeasonStart[0];
			$highSeasonStart = str_replace("2011",$fromYear,$highSeasonStart);
			$highSeasonEnd = str_replace("2011",$toYear,$highSeasonEnd);
			
			$midSeason2 = $wpdb->get_row('select meta_value from '.$this->table_prefix.'postmeta where post_id = '.$bID.' and meta_key = "eb_midSeason2"');	
			$midSeasonStart2 = explode('[-]',$midSeason2->meta_value);
			$midSeasonEnd2 = $midSeasonStart2[1];			
			$midSeasonStart2 = $midSeasonStart2[0];
			$midSeasonStart2 = str_replace("2011",$fromYear,$midSeasonStart2);
			$midSeasonEnd2 = str_replace("2011",$toYear,$midSeasonEnd2);
			
			$lowSeason2 = $wpdb->get_row('select meta_value from '.$this->table_prefix.'postmeta where post_id = '.$bID.' and meta_key = "eb_lowSeason2"');	
			$lowSeasonStart2 = explode('[-]',$lowSeason2->meta_value);
			$lowSeasonEnd2 = $lowSeasonStart2[1];			
			$lowSeasonStart2 = $lowSeasonStart2[0];
			$lowSeasonStart2 = str_replace("2011",$fromYear,$lowSeasonStart2);
			$lowSeasonEnd2 = str_replace("2011",$toYear,$lowSeasonEnd2);
			
			$lowSeasonPrice = $wpdb->get_row('select meta_value from '.$this->table_prefix.'postmeta where post_id = '.$rID.' and meta_key = "eb_lprice"');
			$lowSeasonPrice = $lowSeasonPrice->meta_value;
			$midSeasonPrice = $wpdb->get_row('select meta_value from '.$this->table_prefix.'postmeta where post_id = '.$rID.' and meta_key = "eb_mprice"');
			$midSeasonPrice = $midSeasonPrice->meta_value;
			$highSeasonPrice = $wpdb->get_row('select meta_value from '.$this->table_prefix.'postmeta where post_id = '.$rID.' and meta_key = "eb_hprice"');
			$highSeasonPrice = $highSeasonPrice->meta_value;
						
			for($dayCount = 0; $dayCount < $daysNum ; $dayCount++){
				$next_date = date('Y-m-d', strtotime($fromDate .' +'.$dayCount.' day'));
				if($next_date >= $lowSeasonStart && $next_date < $lowSeasonEnd){
					$roomPrice += $lowSeasonPrice;
				}
				if($next_date >= $midSeasonStart && $next_date < $midSeasonEnd){
					$roomPrice += $midSeasonPrice;										
				}
				if($next_date >= $highSeasonStart && $next_date < $highSeasonEnd){
					$roomPrice += $highSeasonPrice;
				}
				if($next_date >= $midSeasonStart2 && $next_date < $midSeasonEnd2){
					$roomPrice += $midSeasonPrice;
				}
				if($next_date >= $lowSeasonStart2 && $next_date <= $lowSeasonEnd2){
					$roomPrice += $lowSeasonPrice;
				}
			}
			
			
		}	
		else{
			$roomPrice = $wpdb->get_row('select meta_value from '.$this->table_prefix.'postmeta where post_id = '.$rID.' and meta_key = "eb_fprice"');
			$roomPrice = $roomPrice->meta_value;
		 	$roomPrice *= $daysNum;
			$roomPrice = round( $roomPrice );
		}
		if($this->ccur != "htlcur"){
			if( $bcur != $this->ccur) $roomPrice = $this->convert($roomPrice,$bcur,$this->ccur);
			return $roomPrice. ' '.$this->ccur;
		}
		else return $roomPrice. ' '.$bcur;

		
	}
	
	function convert($amount,$from,$to,$decimals=2) {
		global $wpdb;
		$xRatesQ = $wpdb->get_results('select * from currencies where currency = "'.$from.'" OR currency = "'.$to.'"');  
		foreach($xRatesQ as $xRate){
			$this->exchange_rates[$xRate->currency] = $xRate->rate;
		}
		return(number_format(($amount/$this->exchange_rates[$from])*$this->exchange_rates[$to],$decimals));
	}
	
	
}
?>
<script type="text/javascript" >
jQuery(document).ready( function(){
	jQuery("#booking-data").val('');
	jQuery("select").val(0).attr('selected',true);;
});

 function showRoomDetails( roomID ){
 	if( jQuery("#room-details-"+roomID).is(":visible") ) jQuery("#room-details-"+roomID).hide('fast');
 	else{
 		jQuery(".room-details-area").hide('fast');
 		jQuery("#room-details-"+roomID).show('fast');
 	}
 }
 
 function selectRoomsForBooking( room ){
 	var prevBookingDataStr = jQuery("#booking-data").val();
 	jQuery("#booking-data").val('');
 	var newBookingData = '';
 	if( prevBookingDataStr != "" ){
 		var prevBookingData = prevBookingDataStr.split("|");
 		
 		if( prevBookingData.length > 0 ){
		 	for( r = 0; r < ( prevBookingData.length -1 ); r++ ){
 				var curRoomData = prevBookingData[r].split("::");
 				if( curRoomData[0] != room ){
	 				newBookingData += curRoomData[0]+"::"+curRoomData[1]+"|";
 				}
 			}
 		}
 	}
 	if( jQuery("#roomsFor_"+room).val() > 0 )
 		newBookingData += room+"::"+jQuery("#roomsFor_"+room).val()+"|";
 	
 	jQuery("#booking-data").val(newBookingData);
 	<?php if( $from != '' && $to != ''){ ?>		
 	if( jQuery("#booking-data").val() != '' )	 jQuery('#booking-btn').removeAttr('disabled').addClass("eb-search-button");
 	else jQuery('#booking-btn').attr('disabled','disabled').removeClass("eb-search-button");
 	<?php }
 	else { ?>
 		jQuery('#booking-btn').attr('disabled','disabled').removeClass("eb-search-button");
 		<?php }?>
 }
 

</script>
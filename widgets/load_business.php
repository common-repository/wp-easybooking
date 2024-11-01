<?php
if( ( isset( $_REQUEST['b'] ) && $_REQUEST['b'] != '') || ( isset( $_REQUEST['resort'] ) && $_REQUEST['resort'] != '') ){
	global $wpdb;
	global $table_prefix;
	global $ebPluginFolderName;
	
	include_once(ABSPATH.'wp-content/plugins/wp-easybooking/widgets/trans-vars/resort.trans.php');
	$location = addslashes( $_POST['location'] );
	$locationType = addslashes( $_POST['type'] );
	$locationID = addslashes( $_POST['lid'] );
	$bID = '';
	$bTitle = '';
	$from = addslashes( $_POST['from'] );
	$to = addslashes( $_POST['to'] );
	$cur ='EUR';
	if(!isset( $_REQUEST['resort'] )){
		$bID = addslashes( $_REQUEST['b'] );
		$bTitle = addslashes( $_REQUEST['t'] );		
		$cur = addslashes( $_POST['cur'] );
	}
	else{
		$seoTitle = addslashes( $_REQUEST['resort'] );
		$resort = $wpdb->get_row('select * from '.$table_prefix.'posts where post_name = "'.$seoTitle.'"');
		$bID = $resort->ID;
		$bTitle = $resort->post_title;
		$cur =  get_post_meta($bID, "eb_currency");
		if(!empty($cur)) $cur = $cur[0];
	}
	
		
	$lang = 'en';
	$defaultLang = 'en';
	if( function_exists(qtrans_getLanguage)){
		$lang = qtrans_getLanguage();
		$defaultLang = $q_config["default_language"];
	} 
	
	$address = get_post_meta($bID, "eb_address");
	if(!empty($address)) $address = $address[0]; else $address ='';
	
	$addressNumber =  get_post_meta($bID, "eb_addressNum");
		if(!empty($addressNumber)) $addressNumber = $addressNumber[0]; else $addressNumber ='';
		
	$coordinates = get_post_meta($bID, "eb_coordinates");
		if(!empty($coordinates)) $coordinates = $coordinates[0]; else $coordinates ='';
	
	$eb_BusinessCityID = get_post_meta($bID, "eb_cityID");
	
	$city = ''; $country = '';
	if(!empty($eb_BusinessCityID)) {
		$eb_BusinessCityID = $eb_BusinessCityID[0]; 
		$locationID = addslashes( $_POST['lid'] );
		global $countriesTable, $regionsTable, $citiesTable;
		$cityRes = $wpdb->get_row('select CountryID, RegionID, City from '.$citiesTable.' where CityId = '.$eb_BusinessCityID);
		$eb_BusinessCountryID = $cityRes->CountryID;
		$country = $wpdb->get_row('select Country from '.$countriesTable.' where CountryID = '.$cityRes->CountryID);
		$country = $country->Country;
		$city = $cityRes->City;
	}
	
	$businessLogo = get_post_meta($bID, "eb_defaultLogo");
	
	$businessImages = get_post_meta($bID, "eb_logo");
	if(!empty($businessImages)) $businessImages = $businessImages[0]; else $businessImages ='';	
	$businessImages = explode("|", $businessImages);
	
	$business = $wpdb->get_row('select post_author, post_title ,post_content, post_status from '.$table_prefix.'posts where ID = '.$bID. ' and post_parent=0');
	
	$businessFacilities =  get_post_meta($bID, "eb_facilities");
	if(!empty($businessFacilities)) $businessFacilities = $businessFacilities[0]; else $businessFacilities ='';
		
	$stars = get_post_meta($bID, "eb_stars");
		
	echo '<h1 class="resort-title"><a>'.$business->post_title.'</a> <img src = "'.WP_CONTENT_URL.'/plugins/wp-easybooking/images/stars/'.$stars[0].'star-small.png" height="12px" title="'.$stars[0].' stars" /></h1>';
	_e( '<div class="resort-location-at-results-page">'.$address.' '.$addressNumber.'<br />'.$city.', '.$country.'</div>');
	?>
	<table style="border:none">
		<tr>
			<td style="border:none;"> 
				<div class="resort-view-main">
		
					<div class="resort-view-details" style="width:40%">
					<?php
					if($businessLogo[0] != ''){
					?>
						<div class="mainImageContainer">
							<a class="thickbox" title="<?php echo $business->post_title; ?>" style="padding-right:10px;" href="<?php echo WP_CONTENT_URL; ?>/plugins/wp-easybooking/images/businessImg/<?php echo $businessLogo[0]; ?>">
								<div class="business-mains-logo-area" style="background-image:url('<?php echo WP_CONTENT_URL; ?>/plugins/wp-easybooking/images/businessImg/thumbs/<?php echo $businessLogo[0]; ?>')">
								</div>
							</a>
						</div>
						<?php if( !empty( $businessImages ) ){?>
						<p style="margin-top:-20px;">
						<?php
						for($i=0; $i < count($businessImages); $i++){
							if( $businessImages[$i]!="" && $businessImages[$i] != $businessLogo[0] ){
							?>
							<div style="float:left;height:40px;border:1px solid #fff;">
								<a class="thickbox" title="<?php echo $business->post_title; ?>" style="" href="<?php echo WP_CONTENT_URL; ?>/plugins/wp-easybooking/images/businessImg/<?php echo $businessImages[$i]; ?>">
									<img width="70px" src="<?php echo WP_CONTENT_URL; ?>/plugins/wp-easybooking/images/businessImg/thumbs/<?php echo $businessImages[$i]; ?>" />
								</a>
							</div>
							<?php
							}
						}
						?>		
						</p>
			<?php }?>
			<?php
				}
			?>
					</div>
					<div class="resort-view-details" style="width:60%;">
						<p style="position:relative; top:-6px;">
			 				<?php			 				
			 					_e( $business->post_content);
			 				?>
						</p>
					</div>
		
		
	
				</div>
			</td>
		</tr>
		<tr>
			<td style="border:none">
				<div id="change-booking-dates-area">
				<?php if( $from == '' || $to == ''){
					$changeBtnStr = __( $eb_lang_setDates );
				?>
				<span class="change-dates-title"><?php _e( $eb_lang_SelectDates );?></span><em> <?php _e( $eb_lang_SelectDatesExpl );?></em>
				<?php } else {
					$changeBtnStr = __( $eb_lang_change );
				?>
				<span class="change-dates-title"><?php _e( $eb_lang_ChangeDates );?></span>
				<?php
				}
				$page_id = get_option('eb-view-resort');
				$permalink = '';
				if( get_option('permalink_structure')  == "") $permalink = get_permalink( $page_id ).'&lang='.$lang;
				else $permalink = get_permalink( $page_id ).'?lang='.$lang;
				?>
					<table style="border:none">	
						<tr>							
							<td style="border:none;"><label for="change-from"><?php _e($eb_lang_checkIn); ?></label></td>
							<td style="border:none;width:160px;"><input type="text" id="change-from" class="widefat" name="change-from" value="<?php echo $from; ?>" /></td>
							<td style="border:none;"><label for="change-to"><?php _e($eb_lang_checkOut); ?></label></span>
							<td style="border:none;width:160px;"><input type="text" id="change-to" class="widefat" name="change-to" value="<?php echo $to; ?>" /></td>
							<td style="border:none;width:70px;"><input type="submit" class="eb-search-button" onclick="changeBookingDates()" value="<?php echo $changeBtnStr; ?>" /><!--&nbsp;<?php echo $changeBtnStr; ?>&nbsp;</a>--></td>
						</tr>
					</table>
					<div id="change-date-error-field" style="display:none;width:100%;margin-left:-3px;text-align:center;" class="input-error"></div>
				</div>
				
			</td>
		</tr>
		<tr>
			<td style="border:none">
				<div class="resort-tab-options">
					<ol class="resort-tab-container">
						<li id="tab1" class="active" onclick="displayResortData(1)"><?php _e($eb_lang_ResortRooms);?></li>
						<?php
						if( $coordinates != '' ){
						?>
						<li id="tab2" onclick="displayResortData(2)"><?php _e($eb_lang_ResortMap);?></li>
						<?php } ?>
						<li id="tab3" onclick="displayResortData(3)"><?php _e($eb_lang_ResortFacilities);?></li>
						<li id="tab4" onclick="displayResortData(4)"><?php _e($eb_lang_ResortPolicies);?></li>
					</ol>
				</div>
				<?php
				$eb_folder = PLUGINDIR . '/' . dirname(plugin_basename(__FILE__));

				?>
				<div class="resort-data-area" id="resort-data-area-1" style="display:block;">
				<div class="general-title" style="width:auto;font-size:12px;"><strong><img src = "<?php echo WP_CONTENT_URL; ?>/plugins/wp-easybooking/images/arrow-front-small.png" style="margin-bottom:1px;" height="7px" /> <?php _e($eb_lang_ResortRooms);?></strong></div>
				<?php
				include($eb_folder.'/business_data.php');
				$r = new resort($bID, $cur, $from, $to, $table_prefix, $lang);
				?>
				</div>
				<div class="resort-data-area" id="resort-data-area-2" style="display:none;">		
				<div class="general-title" style="width:auto;font-size:12px;"><strong><img src = "<?php echo WP_CONTENT_URL; ?>/plugins/wp-easybooking/images/arrow-front-small.png" style="margin-bottom:1px;" height="7px" /> <?php _e($eb_lang_ResortMap);?></strong></div>	
					<iframe width="100%" height="300px" frameborder="0" scrolling="no" marginheight="0" marginwidth="0" src="https://maps.google.com/maps?q=<?php echo $coordinates; ?>(<?php echo $business->post_title; ?>)&amp;ll=<?php echo $coordinates; ?>&amp;num=1&amp;t=h&amp;ie=UTF8&amp;source=embed&amp;z=15&amp;output=embed"></iframe><br /><small><a target="_blank" style="color:#48f;" href="https://maps.google.com/maps?q=<?php echo $coordinates; ?>&amp;num=1&amp;t=h&amp;ie=UTF8&amp;source=embed&amp;z=14" style="color:#0000FF;text-align:left"><div class="general-title" style="width:auto;font-size:12px;"><?php _e( $eb_lang_ViewLargeMap ); ?></div></a></small>

				</div>
				<div class="resort-data-area" id="resort-data-area-3" style="display:none;">
					<div class="general-title" style="width:auto;font-size:12px;"><strong><img src = "<?php echo WP_CONTENT_URL; ?>/plugins/wp-easybooking/images/arrow-front-small.png" style="margin-bottom:1px;" height="7px" /> <?php _e( $eb_lang_ResortFacilities ); ?></strong></div>
					<div style="font-size:12px;padding:10px;">
					<?php
					if( $businessFacilities != '' ) {
						$facilitiesIds = str_replace("|", ",", $businessFacilities);
						if( substr($facilitiesIds, -1) == "," ) $facilitiesIds = substr_replace($facilitiesIds, "", -1);
						
						$facilities = $wpdb->get_results('select * from '.$table_prefix.'eb_facilities where facility_id IN ('.$facilitiesIds.')');
						?>
						<div class="sub-container">
						<table style="border:none;">	
						<?php
						foreach ($facilities as $facility){
							echo '<tr>';
							
							echo '<td>';
							if($facility->image!='')
								_e( '<img src="'.WP_CONTENT_URL.'/plugins/'.$ebPluginFolderName.'/images/icons/'.$facility->image.'" title="'.$facility->facility_name.'">');							
							echo '</td>'; 
							
							echo '<td>';
							_e( $facility->facility_name );
							echo '</td>'; 

							echo '<td>';
							_e( $facility->facility_description );
							echo '</td>';
							
							echo '</tr>';
						}
						?>
						</table>
						</div>
						</div>
						<?php
						
					}
					else echo '<div class="sub-container">'.__( $eb_lang_NoResortFacilitiesDeclared ).'</div>';
					?>
					</div>
				</div>
				
				<div class="resort-data-area" id="resort-data-area-4" style="display:none;width:100%;">
					<div class="general-title" style="width:auto;font-size:12px;"><strong><img src = "<?php echo WP_CONTENT_URL; ?>/plugins/wp-easybooking/images/arrow-front-small.png" style="margin-bottom:1px;" height="7px" /> <?php _e( $eb_lang_ResortPolicies ); ?></strong></div>

					<div style="font-size:12px;padding:10px;">
					<strong><?php _e( $eb_lang_ResortPoliciesCheckInOut ); ?></strong><br />					<div class="sub-container">
					<?php
					$checkIn = get_post_meta($bID, "eb_checkInTime");
					if(!empty($checkIn)) $checkIn = $checkIn[0]; else $checkIn ='';
					$checkIn = explode("[-]", $checkIn);
					
					if(!empty($checkIn)){
						_e( $eb_lang_checkIn );	
						_e( ' '.$eb_lang_from.': <strong>'.$checkIn[0].'</strong>' );
						
						if(isset($checkIn[1])){							
							_e( ' '.$eb_lang_to.' <strong>'.$checkIn[1].'</strong>' );
						}						
					}
					
					$checkOut = get_post_meta($bID, "eb_checkOutTime");
					if(!empty($checkOut)) $checkOut = $checkOut[0]; else $checkOut ='';
					$checkOut = explode("[-]", $checkOut);
					
					if(!empty($checkOut)){
						_e( "<br />".$eb_lang_checkOut );	
						_e( ' '.$eb_lang_from.': <strong>'.$checkOut[0].'</strong>' );
						
						if(isset($checkOut[1])){							
							_e( ' '.$eb_lang_from.' <strong>'.$checkOut[1].'</strong>' );
						}						
					}
					
					?>
					</div>
					<br />
					<br />
					<strong><?php _e( $eb_lang_Cancellation ); ?></strong>
					<br />
					<div class="sub-container">
					<?php
					$businessCurrency = get_post_meta($bID, "eb_currency");
					if(!empty($businessCurrency)) $businessCurrency = $businessCurrency[0]; else $businessCurrency ='';
									
					$cancellation = cancellationInfo( $bID, "eb_cancellationCharge", $businessCurrency, $cur );
					$earlyCancellation = cancellationInfo( $bID, "eb_earlyCancellationCharge", $businessCurrency, $cur );
					$freeCancellation = cancellationInfo( $bID, "eb_freeCancellationCharge", $businessCurrency, $cur );
					
					$hasCancellationPolicy = false;
					if( $cancellation[0] != ''){
						$hasCancellationPolicy = true;
						_e('<div><strong>'.$cancellation[0].' '.$cancellation[1].'</strong> '.$eb_lang_CancellationsMadeAtLeast.' <strong>'.$cancellation[2]. ' '.$eb_lang_days.'</strong> '.$eb_lang_beforeCheckIn.'</div>');
					}
					if( $earlyCancellation[0] != ''){
						$hasCancellationPolicy = true;
						_e('<div><strong>'.$earlyCancellation[0].' '.$earlyCancellation[1].'</strong> '.$eb_lang_CancellationsMadeAtLeast.' <strong>'.$earlyCancellation[2]. ' '.$eb_lang_days.'</strong> '.$eb_lang_beforeCheckIn.'</div>');
					}
					if( $freeCancellation[0] != ''){
						$hasCancellationPolicy = true;
						_e('<div><strong>'.$eb_lang_NoCharge.'</strong> '.$eb_lang_CancellationsMadeAtLeast.' <strong>'.$freeCancellation[2]. ' '.$eb_lang_days.'</strong> '.$eb_lang_beforeCheckIn.'</div>');
					}

					if( !$hasCancellationPolicy )	_e( $eb_lang_NoCancellationPolicies );

					?>
					</div><!--end of cancellation sub-container-->
					</div>
				</div>
			</td>
		</tr>
	</table>
	<!--form for submiting when dates change-->

	<form method="post" id="bListFrm" action="#">
	<input type="hidden" name="eb" value="resort" />
	<input type="hidden" id="cur" name="cur" value="<?php echo $cur; ?>" />
	<input type="hidden" name="from" id="change-from-value" value="<?php echo $from; ?>" />
	<input type="hidden" name="to" id="change-to-value" value="<?php echo $to; ?>" />
	
	<input type="hidden" name="location" value="<?php echo $location; ?>" />
	<input type="hidden" id="eb-location-type" name="type" value="<?php echo $locationType; ?>" />
	<input type="hidden" id="eb-location-id" name="lid" value="<?php echo $locationID; ?>" />
	<input type="hidden" id = "bID" name="b" value="<?php echo $bID; ?>" />
	<?php if( isset( $_POST["rooms"] ) ){ ?>
	<?php //$roomsC = int()addslashes($_POST["rooms"]); ?>
	<input type="hidden" name="rooms" value="<?php echo addslashes($_POST['rooms']); ?>" />
		<?php for($r = 1; $r <= $_POST["rooms"]; $r++) {?>
			<?php if(isset(  $_POST['adultsForRoom'.$r] )){?>
			<input type="hidden" name="<?php echo 'adultsForRoom'.$r; ?>" value="<?php echo $_POST['adultsForRoom'.$r]; ?>" />			
			<?php } ?>
			<?php if(isset(  $_POST['childrenForRoom'.$r] )){?>
			<input type="hidden" name="<?php echo 'childrenForRoom'.$r; ?>" value="<?php echo $_POST['childrenForRoom'.$r]; ?>" />
			<?php } ?>
			<?php if(isset(  $_POST['babiesForRoom'.$r] )){?>
			<input type="hidden" name="<?php echo 'babiesForRoom'.$r; ?>" value="<?php echo $_POST['babiesForRoom'.$r]; ?>" />
			<?php } ?>
		<?php } ?>
	<?php }
	else{ ?>
	<input type="hidden" name="rooms" value="x" />
	<?php } ?>
</form>

	<?php
}
else echo '<div id="content" class="narrowcolumn">
     <h2 class="center">Error 404 - Not Found</h2>
   </div>';
   
function cancellationInfo( $bID, $field, $bcur, $ccur ){
	global $wpdb;
	$info = array();
	
	$eb_cancellationStr = get_post_meta($bID, $field);		
				
	$cancellationCostMode = '';										
					
	if( !empty($eb_cancellationStr) ){
		$cancellationCostMode = '';
						// echo '<br />'.$eb_cancellationStr[0].'<br />';
		$eb_cancellationStr = explode( "::", $eb_cancellationStr[0] );
		$cancellationCost = $eb_cancellationStr[1];
						
		if( $eb_cancellationStr[0] == "PERSENTAGE" ){														
			$cancellationCostMode = '%';
							
		}
		else{
			$cancellationCostMode = $ccur;
			if( $ccur != $bcur ) $cancellationCost = convert($cancellationCost, $ccur, $bcur );							
		}
		
		$info[0] = $cancellationCost;
		$info[1] = $cancellationCostMode;
		if( $field == "eb_freeCancellationCharge" ){
			$info[0] = "0.00"; 
			$info[2] = $eb_cancellationStr[0];
		}
		else $info[2] = $eb_cancellationStr[2];
		
	}
	else {
		$info[0] = "";
		$info[1] = "";
		$info[2] = "";
	}
	 	
	return $info;
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
<script type="text/javascript" >
	function displayResortData( disp ){
		if( jQuery("#resort-data-area-"+disp).is(':visible') ){
		} else{	
			jQuery(".resort-data-area").hide("fast");
			jQuery("#resort-data-area-"+disp).show("slow");
		}
		jQuery(".active").removeClass("active");
		jQuery("#tab"+disp).addClass("active");
	}
	
	function changeBookingDates(){
		var fromDate = regularDate( jQuery('#change-from').val() );
		var toDate = regularDate( jQuery('#change-to').val() ) ;
		jQuery('#change-date-error-field').html('').hide('fast');
		
		if( jQuery('#change-from').val() != '' && jQuery('#change-to').val() != '' ){
			if(  fromDate >=  toDate ) jQuery('#change-date-error-field').html('<?php _e( $eb_lang_invalidDates );?>').show('slow');
			else{
				//var changeURL = '<?php echo $permalink."&t=".$bTitle."&eb=resort&b=".$bID;?>&from='+jQuery('#change-from').val()+'&to='+jQuery('#change-to').val()+'&cur=<?php echo $cur; ?>';
				//document.location = changeURL;
				jQuery("#change-from-value").val( jQuery('#change-from').val() );
				jQuery("#change-to-value").val( jQuery('#change-to').val() );
				jQuery("#bListFrm").submit();
			}
		}
		else{ 
			jQuery('#change-date-error-field').html('Please set the dates to view prices').show('slow');
		}
	}
	
	function regularDate( dateStr ){
		dateStr = dateStr.split('-');
		var newDate = new Date(dateStr[2], dateStr[1] - 1, dateStr[0]); //Month is 0-11 in JavaScript
		return newDate;
	}
	
	jQuery(function() {
		var dates = jQuery( "#change-from, #change-to" ).datepicker({
			defaultDate: "+1w",
			dateFormat : 'dd-mm-yy',
			changeMonth: true,
			numberOfMonths: 1,
			minDate: 0,
			onSelect: function( selectedDate ) {
				var option = this.id == "change-from" ? "minDate" : "maxDate",
					instance = jQuery( this ).data( "datepicker" ),
					date = jQuery.datepicker.parseDate(
						instance.settings.dateFormat ||
						jQuery.datepicker._defaults.dateFormat,
						selectedDate, instance.settings );
				dates.not( this ).datepicker( "option", option, date );
			}
		});
	});
</script>
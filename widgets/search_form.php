<?php global $table_prefix;?>
<?php $result_page_ID = get_option('eb-location-result-page');?>
<?php $dateRangeStyle = 'style = "color: #47acdf;cursor: pointer;display: block;text-shadow: 0 0 2px 999;font-size: 11px;font-weight:bold;width:60px;background-color:#fff";margin-top:0px;position:relative;top:0px;'; ?>
<?php global $eb_path;?>
<?php include_once($eb_path.'/widgets/trans-vars/search_form.trans.php')?>
<?php
global $q_config;
$siteCurrency = get_option('eb_siteCurrency');
$location = '';$from = '';$to='';$lid= '';$locationType = '';$rooms = 0;$eb_currency = $siteCurrency;
if( isset( $_POST['location'] ) && $_POST['location'] != '' ) $location = addslashes( $_POST['location'] );
if( isset( $_POST['from'] ) && $_POST['from'] != '' ) $from = addslashes( $_POST['from'] );
if( isset( $_POST['to'] ) && $_POST['to'] != '' ) $to = addslashes( $_POST['to'] );
if( isset( $_POST['lid'] ) && $_POST['lid'] != '' ) $lid = addslashes( $_POST['lid'] );
if( isset( $_POST['type'] ) && $_POST['type'] != '' ) $locationType = addslashes( $_POST['type'] );
if( isset( $_POST['rooms'] ) && $_POST['rooms'] != '' ) $rooms = (int)addslashes( $_POST['rooms'] );
if( isset( $_POST['eb_currency'] ) && $_POST['eb_currency'] != '' ) $eb_currency = addslashes( $_POST['eb_currency'] );
if( isset( $_POST['cur'] ) && $_POST['cur'] != '' ) $eb_currency = addslashes( $_POST['cur'] );


//$permalink = "resort-search-results/?";
//if( get_option('permalink_structure')  == "") $permalink = '?page_id='.get_option('eb-location-result-page');
$page_id = get_option('eb-location-result-page');
$permalink = '';

$lang = '';
if( function_exists(qtrans_getLanguage) ){
	if( isset($_REQUEST['lang']) && $_REQUEST['lang'] != '' )
		$lang = 'lang='.addslashes($_REQUEST['lang']);
	else 
		$lang = 'lang='.$q_config["default_language"];		
}

if( get_option('permalink_structure')  == "") $permalink = get_site_url().'?page_id='.$page_id.'&'.$lang;
else {
	$permalink = get_permalink( $page_id );
	if( !isset( $_REQUEST['lang'] ) ) $permalink .= '?'.$lang;
}

?>
<div class="easy-booking-search-form-container">
<form class="easy-booking-search-form" autocomplete="off" id="eb-search-location-form" style="width:100%;" action="<?php echo $permalink ?>" method="post" onsubmit="return searchlocation();">
	
	<div id="search-box-title" class="easy-booking-form-title"><?php _e($eb_lang_location_or_hotel_name)?></div>
	<div class="easy-booking-search-items-area">
		<input placeholder="<?php _e($eb_lang_location_or_hotel_name)?>" autocomplete="off" type="text" onkeyup="proposeLocationWithAjax()" id="location" name="location" class="eb-frm-search-text-box" value="<?php echo $location; ?>" />
		<div id="ajax_location_picker" class="navigation" style="display:none;border:1px solid #ccc;background-color:#fefefe;height:250px;width:auto;position:absolute;top:50px;left:50px;z-index:20;"></div>
		<div style="display:none;margin-top:0px;" id="eb-empty-location"></div>
		<p class="fromDate-par"  align="center">
		<table align="center" style="border:none;width:100%;padding-top:5px;"> 
			<tr valign="top">
				<td align="center" valign="top" >
					<label for="from"><?php _e( $eb_lang_checkIn ); ?></label>		
				</td>
				<td align="center" valign="top" style="margin-top:0px;<?php //if($_REQUEST['eb']=='rs') echo 'padding-left:30px;'?>">
					<label for="to"><?php _e($eb_lang_checkOut ); ?></label>		
				</td>
			</tr>
			<tr valign="top">
				<td align="center" style="border:none;width:50%;padding-top:0px;<?php //if($_REQUEST['eb']=='rs') echo 'padding-left:30px;'?>" valign="top">
					<input type="text" id="from" class="widefat" name="from" value="<?php echo $from; ?>" <?php echo $dateRangeStyle;?> />
				</td>
				<td align="center" style="border:none;width:50%;padding-top:0px;<?php //if($_REQUEST['eb']=='rs') echo 'padding-left:30px;'?>" valign="top">
					<input type="text" id="to" class="widefat" name="to" value="<?php echo $to; ?>" <?php echo $dateRangeStyle;?> />
				</td>
			</tr>
			<tr>
				<td>
					<div class="error" style="display:none" id="eb-empty-from"></div>
				</td>
				<td>
					<div class="error" style="display:none" id="eb-empty-to"></div>
				</td>
			</tr>
		</table>
		<input type="checkbox" name="dates" id="no-dates-is-set" value="no" /> <label for="no-dates-is-set"><?php _e( $eb_lang_noDatesYet )?></label>
		<hr>	
		
		<p>
		<label><?php _e( $eb_lang_numberOfRooms )?></label>
		<select name="rooms" id="roomsNum" onchange="roomNumToBook()">
			<option value="x"><?php _e( $eb_lang_AnyNumberOfRooms )?></option>
			<?php tenOptions( $rooms );?>
		</select> 
		</p>
		<div id="roomsElementsArea" style="display:none;">
		<?php
		if( $rooms > 0 ){
			?>
			<table style="border:none;width:100%;text-align:center;" cellspacing="10" cellpadding = "25px" align="center" class="noClass">
				<tr><td></td><td align="center"> Adults </td><td align="center"> Children </td><td align="center"> Babies </td></tr>
				<?php
				for($r = 1; $r <= $rooms; $r++){
					?>
					<tr>
						<td align="center" style="width:20px;">#<?php echo $r; ?></td>
						<td align="center" style="padding:5px;">
							<select name = "adultsForRoom<?php echo $r; ?>" id = "adultsForRoom<?php echo $r; ?>">
								<option value="0">0</option>
								<?php tenOptions( $_POST['adultsForRoom'.$r] );?>
							</select>
						</td>
						<td align="center" style="padding:5px;">
							<select name = "childrenForRoom<?php echo $r; ?>" id = "childrenForRoom<?php echo $r; ?>">
								<option value="0">0</option>
								<?php tenOptions( $_POST['childrenForRoom'.$r] );?>
							</select>
						</td>
						<td align="center" style="padding:5px;">
							<select name = "babiesForRoom<?php echo $r; ?>" id = "babiesForRoom<?php echo $r; ?>">
								<option value="0">0</option>
								<?php tenOptions( $_POST['babiesForRoom'.$r] );?>
							</select>
						</td>
			
					</tr>
					<?php
				}
				?>
			</table>
			<?php
		}
		?>
		</div>
		
	</p>
	<input type="hidden" id="eb-location-type" class="eb-location-type" name="type" value="<?php echo $locationType; ?>" />
	<input type="hidden" id="eb-location-id" class="eb-location-id" name="lid" value="<?php echo $lid; ?>" />
	<input type="hidden" id="eb_currency" name="eb_currency" value="<?php echo $eb_currency; ?>" />
	<input type="hidden" id="eb_switch" name="eb" value="rs" />
	<input type="hidden" id="eb_bus_switch" name="b" value="" />
	<input type="hidden" id="eb_cur_switch" name="cur" value="<?php echo $eb_currency; ?>" />
	<p>
		<input type="submit" value="<?php _e( $eb_lang_Search )?>" onclick="" class="eb-search-button">
	</p>
	</div>
	
</form>
	<div class="eb-form-sep">&nbsp;</div>
	<div style="width:100%;text-align:center;">
	<span class="go-to-booking-title" onclick="toggleShowBookingFrm();">
		<img id="show-hide-arrow" src = "<?php echo WP_CONTENT_URL; ?>/plugins/wp-easybooking/images/dropdown.png" height="12px" /> <?php _e( $eb_lang_ViewYourBooking ); ?>
	</span>
	</div>
	<div id="go-to-booking" class="go-to-booking">
	<?php
	$page_id = get_option('eb-view-bookings');
	$permalink = '';
	if( get_option('permalink_structure')  == "") $permalink = get_site_url().'?page_id='.$page_id;
	else $permalink = get_permalink( $page_id ); 

	?>
	<form action="<?php echo $permalink; ?>" method="post">
		<input type="hidden" name="eb" value="bookings" />
		<span><?php _e( $eb_lang_BookingNumber ); ?></span><br /><input type="text" name="bookID" value=""  /><br />
		<span style="margin-top:20px;"><?php _e( $eb_lang_BookingPin ); ?></span><br /><input type="password" name="pin" value="" maxlength="4" size="4" /><br />	
		<input type="hidden" name="bID" value="" style="font-size:12px;" />
		<br />
		<div style="width:100%;" align="center"><input type="submit" value="<?php _e( $eb_lang_ViewBooking ); ?>" class="eb-search-button" /></div>
	</form>
	<!--<div style="width:100%;padding-top:10px;text-align:center;">
	<span class="go-to-booking-title" style="padding-left:0px;" onclick="jQuery('#go-to-booking').hide('slow');"><?php _e( $eb_lang_hide ); ?></span>
	</div>-->
</div>
<script type="text/javascript" >
function toggleShowBookingFrm(){
	if( jQuery('#go-to-booking').is(':visible') ) {
		jQuery('#go-to-booking').hide('slow');
		jQuery('#show-hide-arrow').attr('src','<?php echo WP_CONTENT_URL; ?>/plugins/wp-easybooking/images/dropdown.png');
	}
	else{
		jQuery('#go-to-booking').show('slow');
		jQuery('#show-hide-arrow').attr('src','<?php echo WP_CONTENT_URL; ?>/plugins/wp-easybooking/images/dropup.png');
	}
}
</script>
<div class="eb-form-sep">&nbsp;</div>
</div>

<?php

function tenOptions( $selected ) {
	for($o=1;$o<=10;$o++){
		echo '<option value= "'.$o.'"';
		if( $o == $selected ) echo ' selected ';
		echo '>'.$o.'</option>';	
	}	
}


function my_admin_footer() {
	global $eb_path;
	include($eb_path.'/widgets/trans-vars/search_form.trans.php');
	
	$currentLanguage = '';
	$defaultLang = '';
	if( function_exists(qtrans_getLanguage)){
		$currentLanguage = qtrans_getLanguage();
		$defaultLang = $q_config["default_language"];
	}
	else {
		$defaultLang = '';
		$currentLanguage = 'NO_LANGS';
	} 
	if( $defaultLang == '' ){		
		$defaultLang = get_option('qtranslate_default_language');
	}
	global $table_prefix;
	$pluginfolder = get_bloginfo('url') . '/' . PLUGINDIR . '/wp-easybooking/';
	$result_page_ID = get_option('eb-location-result-page');
	$aPath = str_replace('\\', '/', ABSPATH);

	$rooms = 'x';
	if( isset( $_POST['rooms'] ) && $_POST['rooms'] != '' ) $rooms = (int)addslashes( $_POST['rooms'] );
	//$permalink = "resort-search-results/?";
	//if( get_option('permalink_structure')  == "") $permalink = '?page_id='.get_option('eb-location-result-page');
	$page_id = get_option('eb-location-result-page');
	$permalink = '';
	/*if( get_option('permalink_structure')  == "") $permalink = get_site_url().'?page_id='.$page_id.'&';
	else $permalink = get_permalink( $page_id ).'?';*/
	$clang = $currentLanguage;

	if( get_option('permalink_structure')  == "") $permalink = get_site_url().'?page_id='.$page_id;
	else $permalink = get_permalink( $page_id );


	?>
	
	<script type="text/javascript">
		
		jQuery('document').ready(function(){
			jQuery("#roomsNum").val('<?php echo $rooms; ?>');
			<?php if( $rooms != 'x' ){ ?>
			jQuery("#roomsElementsArea").show('slow');
			<?php } ?>
			
			//======LANGUAGE CHOOSER FIX======
			
			//==================================================================================
			//                            FOR SEARCH RESULTS PAGE
			//==================================================================================
			<?php if( isset($_POST['eb']) && $_POST['eb'] == 'rs' && function_exists(qtrans_getLanguage) ){ ?>
				jQuery(".qtrans_language_chooser a").each(function() { 					
 					var lang = jQuery(this).attr("hreflang");
 					jQuery(this).removeAttr("href").hover(function(){
 						jQuery(this).css("cursor", "pointer");
 					});
 					
 					var permalink = '<?php echo $permalink; ?>';
 					var permArr = permalink.split('lang=');
 					permalink = permArr[0];
 					<?php if( get_option('permalink_structure')  == ""){?>
 					permalink += '&';
 					<?php }
 					else { ?>
 					permalink += '?';
 					<?php } ?>
 					permalink = permalink.replace('??', '?');
 					permalink += 'lang='+lang;
 					jQuery(this).click(function (){ 						
 						jQuery("#eb-search-location-form").attr("action", permalink).submit(); 						
 					});
				});
			<?php } ?>

			//==================================================================================
			//                               FOR RESORT PAGE
			//==================================================================================
			
			<?php if( isset($_POST['eb']) && $_POST['eb'] == 'resort' && function_exists(qtrans_getLanguage) ){ 
			$page_id = get_option('eb-view-resort');
			$permalink = '';	
			$clang = $currentLanguage;

			if( get_option('permalink_structure')  == "") $permalink = get_site_url().'?page_id='.$page_id;
			else $permalink = get_permalink( $page_id );
			?>
				jQuery(".qtrans_language_chooser a").each(function() { 					
 					//var action = jQuery("#eb-search-location-form").attr("action");
 					var lang = jQuery(this).attr("hreflang");
 					jQuery(this).removeAttr("href").hover(function(){
 						jQuery(this).css("cursor", "pointer");
 					});
 					
 					var permalink = '<?php echo $permalink; ?>';
 					var permArr = permalink.split('lang=');

 					permalink = permArr[0];
 					<?php if( get_option('permalink_structure')  == ""){?>
 					permalink += '&';
 					<?php }
 					else{ ?> 					
 					permalink += '?';
 					permalink = permalink.replace('??', '?');
 					<?php } ?>
 					permalink += 'lang='+lang;
 					var hTitle = getUrlVarsF()['t'];
 					permalink += '&t='+hTitle; 
 					jQuery(this).click(function (){
 						jQuery('#eb_switch').val('resort');
 						
 						<?php if( isset( $_POST['eb_currency'] ) && $_POST['eb_currency'] != '' ){?>
 						jQuery('#eb_cur_switch').val('<?php echo $_POST["eb_currency"]; ?>');
 						<?php } ?>
 						
 						<?php if( isset( $_POST['cur'] ) && $_POST['cur'] != '' ){?>
 						jQuery('#eb_cur_switch').val('<?php echo $_POST["cur"]; ?>');
 						<?php } ?>
 						
 						<?php if( $_POST['from'] == '' || $_POST['to'] == '' ){ ?>
 						jQuery('#no-dates-is-set').attr('checked', 'checked');
 						<?php } ?>
 						jQuery('#eb_bus_switch').val('<?php echo addslashes( $_POST["b"] ); ?>');
 						jQuery("#eb-search-location-form").attr("action", permalink).submit(); 						
 					});
				});
			<?php } ?>
			
			//==================================================================================
			//                              FOR LOAD BOOKING PAGE
			//==================================================================================
			
			<?php if( isset($_POST['eb']) && $_POST['eb'] == 'bookings' && function_exists(qtrans_getLanguage) ){ ?>				
				jQuery(".qtrans_language_chooser a").each(function() { 					
				 	<?php
				 	$page_id = get_option('eb-view-bookings');
					$permalink = '';
					if( get_option('permalink_structure')  == "") $permalink = get_site_url().'?page_id='.$page_id;
					else $permalink = get_permalink( $page_id );
				 	?>
				 	var permalink = "<?php echo $permalink; ?>";
 					var lang = jQuery(this).attr("hreflang");
 					jQuery(this).removeAttr("href").hover(function(){
 						jQuery(this).css("cursor", "pointer");
 					});
 					<?php if( get_option('permalink_structure')  == ""){?>
 					permalink = permalink.split('&lang=');
 					permalink = permalink[0];
 					permalink += '&';
 					<?php }
 					else{ ?>
 					permalink = permalink.split('?lang=');
 					permalink = permalink[0];
 					permalink += '?';
 					<?php } ?>
 					permalink += 'lang='+lang;
 					jQuery(this).click(function (){
 						jQuery("#load-booking-trans-fix-frm").attr("action", permalink).submit(); 						
 					});
				});
			<?php } ?>
			
			
			//==================================================================================
			//                              FOR BOOKING PAGE (booking-review)
			//==================================================================================
			
<?php if( isset($_POST['eb']) && $_POST['eb'] == 'booking' && function_exists(qtrans_getLanguage) ){ ?>				
				jQuery(".qtrans_language_chooser a").each(function() { 					
				 	<?php
				 	$page_id = get_option('eb-booking-review');
					$permalink = '';
					if( get_option('permalink_structure')  == "") $permalink = get_site_url().'?page_id='.$page_id;
					else $permalink = get_permalink( $page_id );
				 	?>
				 	var permalink = "<?php echo $permalink; ?>";
 					var lang = jQuery(this).attr("hreflang");
 					jQuery(this).removeAttr("href").hover(function(){
 						jQuery(this).css("cursor", "pointer");
 					});
 					<?php if( get_option('permalink_structure')  == ""){?>
 					permalink = permalink.split('&lang=');
 					permalink = permalink[0];
 					permalink += '&';
 					<?php }
 					else{ ?>
 					permalink = permalink.split('?lang=');
 					permalink = permalink[0];
 					permalink += '?';
 					<?php } ?>
 					permalink += 'lang='+lang;
 					jQuery(this).click(function (){
 						jQuery("#booking-review-trans-fix-frm").attr("action", permalink).submit(); 						
 					});
				});
			<?php } ?>

			
			//====END LANGUAGE CHOOSER FIX=====
			
		});
		
	
	 	jQuery("#from").click( function (){
	 		jQuery("#eb-empty-from").hide("fast");
	 		}	 	
	 	);
	 	jQuery("#to").click( function (){
	 		jQuery("#eb-empty-to").hide("fast");
	 		}	 	
	 	);
	 	
	 	function getUrlVarsF() {
    		var vars = {};
    		var parts = window.location.href.replace(/[?&]+([^=&]+)=([^&]*)/gi, function(m,key,value) {
      	  vars[key] = value;
   	 	});
	    	return vars;
		}
		
	 	function roomNumToBook(){
	 		var roomsNum = jQuery("#roomsNum").val();
	 		var roomElementsStr = '';
	 		if( roomsNum != 'x' ){
	 			var tenOptions = JtenOptions(0); 
	 			var tenOptionsForAdults = JtenOptions(2);
	 			var childrenOptions = JzeroToLimitOptions(5);
	 			var babiesOptions = JzeroToLimitOptions(2);
	 			roomElementsStr += '<table style="border:none;width:100%;text-align:center;" cellspacing="10" cellpadding = "25px" align="center" class="noClass">';
	 				roomElementsStr += '<tr><td></td><td align="center"> Adults </td><td align="center"> Children </td><td align="center"> Babies </td></tr>';
	 			for( r = 1; r <= roomsNum; r++ ){
	 				//roomElementsStr += '<p style="padding-top:10px;border:1px solid #fefefe;">';
	 				roomElementsStr += '<tr>';
	 					roomElementsStr += '<td align="center" style="width:20px;">';
	 						roomElementsStr += '#'+r;
	 					roomElementsStr += '</td>';
	 					roomElementsStr += '<td align="center" style="padding:5px;">';
	 						roomElementsStr += '<select name = "adultsForRoom'+r+'">';
	 						roomElementsStr += tenOptionsForAdults;
	 						roomElementsStr += '</select>';
	 					roomElementsStr += '</td>';
	 					roomElementsStr += '<td align="center" style="padding:5px;">';
	 						roomElementsStr += '<select name = "childrenForRoom'+r+'">';
	 						roomElementsStr += childrenOptions;
	 						roomElementsStr += '</select>';
	 					roomElementsStr += '</td>';
	 					roomElementsStr += '<td align="center" style="padding:5px;">';
	 						roomElementsStr += '<select name = "babiesForRoom'+r+'">';
	 						roomElementsStr += babiesOptions;
	 						roomElementsStr += '</select>';
	 					roomElementsStr += '</td>';	 				
	 				roomElementsStr += '</tr>';
	 			}
	 			roomElementsStr += '</table>';
	 			
	 			jQuery('#roomsElementsArea').html(roomElementsStr).show('slow');
	 		}
	 		else jQuery('#roomsElementsArea').html('').hide('fast');
	 		
	 	}
	 	
	 	function JtenOptions( selected ) {
	 		var optionsStr = '';
			for(o=1;o<=10;o++){
				optionsStr += '<option value= "'+o+'"';
				if (o == selected) optionsStr += ' selected '; 
				optionsStr += '>'+o+'</option>';	
			}	
			return optionsStr;
		}
		function JzeroToLimitOptions(limit) {
	 		var optionsStr = '';
			for(o=0;o<=limit;o++){
				optionsStr += '<option value= "'+o+'">'+o+'</option>';	
			}	
			return optionsStr;
		}
		

		function proposeLocationWithAjax(){	
			jQuery("#eb-empty-location").hide('fast');
			jQuery('#eb-location-id').val('');			
			jQuery('#eb-location-type').val('');	
			jQuery('#eb-location-id-spcl').val('');
			jQuery('#eb-location-type-spcl').val('');
			var parOffset = jQuery("#location").position();
			var searchBoxHeight = jQuery("#location").height();
			var topPos = parOffset.top + searchBoxHeight + 10;
			jQuery("#ajax_location_picker").css({"top": topPos+"px", "left": parOffset.left+"px"}); 
			 
			var locationSubStr = jQuery("#location").val();
			locationSubStr = jQuery.trim(locationSubStr);//to remove all blank spaces before and after text
			if(locationSubStr == '' || locationSubStr.length < 3) hideLocationarea();
			else {
				jQuery.ajax({
					type: "POST",
  					url: "<?php echo $pluginfolder; ?>widgets/ajaxProposeLocation.php",  			
  					data: "aPath=<?php echo $aPath; ?>&pref=<?php echo $table_prefix; ?>&locStr="+locationSubStr+"&lang=<?php echo $currentLanguage; ?>&defaultLang=<?php echo $defaultLang; ?>",
  					success: function(resp){
  						jQuery("#ajax_location_picker").html(resp).show('slow');  						
					}
				});
				//jQuery(document).click(function(e) { jQuery("#eb-empty-location").hide('fast'); });
			}
			
		}
		
		function selectThisLocation( selectedLocation, locationType, locationID ){			
			jQuery("#location").val(selectedLocation);
			jQuery("#eb-location-type").val(locationType);
			jQuery("#eb-location-id").val(locationID);
			jQuery(".eb-location-id").val(locationID);
			jQuery(".eb-location-type").val(locationType);
			hideLocationarea();
		}
		function hideLocationarea(){
			jQuery("#ajax_location_picker").slideUp("fast", function(){
				jQuery("#ajax_location_picker").html('').hide('fast');
			});
		}
		
		function searchlocation(){		
			var eb_location = jQuery("#location").val();
			var eb_from = jQuery("#from").val();
			var eb_to = jQuery("#to").val();
			var has_empty_fields = false;
			var hasDates = jQuery("#no-dates-is-set").is(':checked');
			//if( jQuery("#no-dates-is-set").is(':checked') ) hasDates = false;
			
			if ( eb_location == '') {
				jQuery("#eb-empty-location").html('<br /><div style="color: #666;border:1px solid #ff9494; background: #ffa8a8;font-size: 12px;"><?php _e( $eb_lang_Enter_location_or_hotel_name_error );?></div>').show("slow");
				has_empty_fields = true;
			} 
			if ( eb_from == '' && !hasDates) {
				jQuery("#eb-empty-from").html("<div style='color: #666;border:1px solid #ff9494; background: #ffa8a8;font-size: 12px;'><?php _e( $eb_lang_enterStart ); ?></div>").show("slow");
				has_empty_fields = true;
			} 
			if ( eb_to == ''  && !hasDates) {
				jQuery("#eb-empty-to").html("<div style='color: #666;border:1px solid #ff9494; background: #ffa8a8;font-size: 12px;'><?php _e( $eb_lang_enterEnd ); ?></div>").show("slow");
				has_empty_fields = true;
			} 
			if( !has_empty_fields ){
 				<?php
				if($currentLanguage != '') echo 'var curLang = "lang='.$currentLanguage.'&";';
				else echo 'var curLang = "";';
				?>
				//var results_page = "<?php echo $_SERVER['HTTP_HOST'] ?>?"+curLang+"page_id=<?php echo $result_page_ID; ?>&eb=rs&"+jQuery("#eb-search-location-form").serialize();
				//alert(results_page);
				//var results_page = "<?php echo $_SERVER['HTTP_HOST'] ?>?"+curLang+"page_id=2";
				//document.location = results_page;
				//jQuery('#eb-search-location-form').submit();
			}
			else {
				return false;		
			}
		}/*END OF searclocation function*/
		


		
		jQuery(function() {
		var dates = jQuery( "#from, #to" ).datepicker({
			defaultDate: "+1w",
			dateFormat : 'dd-mm-yy',
			changeMonth: true,
			numberOfMonths: 1,
			minDate: 0,
			/*showOn: "button",
			buttonImage: "<?php echo $pluginfolder;?>images/calendar.gif",
			buttonImageOnly: true,*/
			onSelect: function( selectedDate ) {
				var option = this.id == "from" ? "minDate" : "maxDate",
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
	<?php

}
add_action('wp_footer', 'my_admin_footer');

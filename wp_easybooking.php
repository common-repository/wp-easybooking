<?php
/*
Plugin Name: Easy Booking
Version: 1.0.3
Plugin URI: http://wp-easybooking.com
Description: Multi hotel booking plugin for wordpress. Transforms your website into a complete booking engine.
Author: Panos Lyrakis
Author URI: http://wp-easybooking.com
WDP ID: 220

Copyright 2011-2012 wp-easybooking (http://wp-easybooking.com)

*/


if (is_admin()) {
    // If the user is currently in the admin panel, load the admin content.
	add_action('admin_menu', 'ebooking_admin_actions');
	
} else { 
    // Else, load the front-end content (no need for admin content here!)
	if( isset( $_POST['eb'] )  && $_POST['eb'] == 'rs')
		add_filter('the_content','load_search_results');
	if( isset( $_POST['eb'] )  && $_POST['eb'] == 'resort')
		add_filter('the_content','load_business');
	if( isset( $_REQUEST['resort'] ) && $_REQUEST['resort'] != '' )
		add_filter('the_content','load_business');
	if( isset( $_POST['eb'] )  && $_POST['eb'] == 'booking')
		add_filter('the_content','load_booking_page');
		if( isset( $_POST['eb'] )  && $_POST['eb'] == 'booking-completed')
		add_filter('the_content','booking_completed_page');
	if( isset( $_POST['eb'] )  && $_POST['eb'] == 'report')
		add_filter('the_content','load_booking_report');
	if( isset( $_REQUEST['eb'] )  && $_REQUEST['eb'] == 'report')
		add_filter('the_content','load_bookings');
	if( isset( $_POST['eb'] )  && $_POST['eb'] == 'bookings')
		add_filter('the_content','load_bookings');
	if( isset( $_REQUEST['eb'] )  && $_REQUEST['eb'] == 'pp_IPN'){
		//add_filter('wp_head','load_ppipn');
		add_filter('the_content','load_bookings');
	}

}


function load_ppipn($content = ''){
	$tempContent = get_the_content();
	
	$tempVerify = strpos($tempContent,$tempCheck);
	if($tempVerify !== true) {
		$eb_folder = PLUGINDIR . '/' . dirname(plugin_basename(__FILE__)).'/widgets';
		include($eb_folder.'/paypal_functions.php');		 
	}
}


function load_bookings($content = ''){
	$tempContent = get_the_content();
	
	$tempVerify = strpos($tempContent,$tempCheck);
	if($tempVerify !== true) {

		$eb_folder = PLUGINDIR . '/' . dirname(plugin_basename(__FILE__)).'/widgets';

		include($eb_folder.'/load_booking.php'); 
	}
}

function booking_completed_page($content = ''){
	$tempContent = get_the_content();
	
	$tempVerify = strpos($tempContent,$tempCheck);
	if($tempVerify !== true) {

		$eb_folder = PLUGINDIR . '/' . dirname(plugin_basename(__FILE__)).'/widgets';

		include($eb_folder.'/booking_report.php'); 
	}
}

function load_booking_report($content = ''){
	$tempContent = get_the_content();
	
	$tempVerify = strpos($tempContent,$tempCheck);
	if($tempVerify !== true) {

		$eb_folder = PLUGINDIR . '/' . dirname(plugin_basename(__FILE__)).'/widgets';

		include($eb_folder.'/booking_report.php'); 
	}
}


function load_booking_page($content = ''){
	$tempContent = get_the_content();
	//$tempCheck = '[easy-booking-view-resort';
	
	$tempVerify = strpos($tempContent,$tempCheck);
	if($tempVerify !== true) {

		$eb_folder = PLUGINDIR . '/' . dirname(plugin_basename(__FILE__)).'/widgets';

		include($eb_folder.'/booking_page.php'); 
	}
}

function load_business($content = ''){
	$tempContent = get_the_content();
	$tempCheck = '[easy-booking-view-resort';
	
	$tempVerify = strpos($tempContent,$tempCheck);
	if($tempVerify !== true) {

		$eb_folder = PLUGINDIR . '/' . dirname(plugin_basename(__FILE__)).'/widgets';

		include($eb_folder.'/load_business.php'); 
	}
}
function load_search_results($content = '') {
	//first we check for shortcode in the content
	$tempContent = get_the_content();
	$tempCheck = '[easy-booking-resort-search-results';
	
	$tempVerify = strpos($tempContent,$tempCheck);
	if($tempVerify !== true) {

		$eb_folder = PLUGINDIR . '/' . dirname(plugin_basename(__FILE__)).'/widgets';

		include($eb_folder.'/businesses_list_results.php'); 
	}

       
}



/*======THE WIDGET FOR SIDEBAR=======*/
include('wp_easybookingWidget.php');

//Pros8hkh neou user role. An to role yparxei tote den ekteleitai :http://core.trac.wordpress.org/browser/tags/3.0.5/wp-includes/capabilities.php line 136

add_role('eb_businessman', 'Businessman', array(
    'read' => true // True allows that capability
));

$ebPluginFolderName = 'wp-easybooking';
$eb_listLimit = 10;
$eb_adminUrl = 'http://'.$_SERVER['SERVER_NAME'].$_SERVER['PHP_SELF'];
$eb_path = dirname(__FILE__);
$facilitiesTable_name = 'eb_facilities';
$countriesTable = 'eb_countries';
$regionsTable = 'eb_regions';
$citiesTable = 'eb_cities';

global $current_user;
global $wpdb;

define('LOGGED_BUSINESSMAN_ID', '$current_user->ID');

include('admin/eb_install.php');

//***********************CHECK IF THERE IS A BOOKINGS TABLE WITH ROOM DATA*****************************
if($wpdb->get_var("SHOW TABLES LIKE 'eb_bookingroomdata'") == "eb_bookingroomdata") {
}
else{
	$file_content = file($eb_path.'/admin/eb_bookingroomdata.sql');
	$query = "";
	foreach($file_content as $sql_line){
		if(trim($sql_line) != "" && strpos($sql_line, "--") === false){
			$query .= $sql_line;
			if (substr(rtrim($query), -1) == ';'){//an teleiwnei h grammh me ; einai query
  				$result = mysql_query($query)or die(mysql_error());
  				$query = "";
			}
		}
	}
}
//***********************CHECK IF THERE IS A BOOKINGS TABLE*****************************
if($wpdb->get_var("SHOW TABLES LIKE 'eb_bookingdata'") == "eb_bookingdata") {
}
else{
	$file_content = file($eb_path.'/admin/eb_bookingdata.sql');
	$query = "";
	foreach($file_content as $sql_line){
		if(trim($sql_line) != "" && strpos($sql_line, "--") === false){
			$query .= $sql_line;
			if (substr(rtrim($query), -1) == ';'){//an teleiwnei h grammh me ; einai query
  				//echo $query;
  				$result = mysql_query($query)or die(mysql_error());
  				$query = "";
			}
		}
	}
}

//***********************CHECK IF THERE IS A FACILITIES TABLE*****************************

$facilitiesTable_name = $table_prefix.$facilitiesTable_name;

if($wpdb->get_var("SHOW TABLES LIKE '$facilitiesTable_name'") == $facilitiesTable_name) {

}
else{	
	$query = "";
echo '
	<div style="width:99%">
	<div><strong>Creating Facilities Table</strong></div>
	<div style="height:100px;overflow:scroll;border:1px solid #ccc">
';
	$createSql = 'CREATE TABLE IF NOT EXISTS eb_facilities (
  facility_id int(11) NOT NULL AUTO_INCREMENT,
  facility_name text DEFAULT NULL,
  facility_description text,
  facility_for varchar(20) NOT NULL,
  image text,
  PRIMARY KEY (facility_id)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=83
';
$result = mysql_query($createSql)or die(mysql_error());
	mysql_query('rename table eb_facilities to '.$facilitiesTable_name);
	
	?>
	</div>
	</div><br>
	<?php
}

//*****************************************************************************************											

 function call_mySettings_page(){
	include('adminSettings.php');
}

 
function easybooking_menu_render() {
	global $title;
	?>
        <?php
        if (!isset($_REQUEST['type']) || $_REQUEST['type']==""){
        		?> <h2><?php echo $title;?></h2>
        		<?php
        		include('admin/adminSettings.php');
        }  
        if(isset($_REQUEST['type']))      
        if($_REQUEST['type'] == "Hotel" || $_REQUEST['type'] == "Apartments" ){ 
				if (isset($_REQUEST['action']) && $_REQUEST['action'] == "bookit"){
					include('admin/bookItNow.php');					}	
				else include('admin/rooms.php');        		
        }
}

function businesDeals(){
	include('admin/businessDeals.php');	
}

function main_busines() {
	global $title;
   echo '<h2>'.  $title.'</h2>';
   if(!isset($_REQUEST['bID']) && !isset($_REQUEST['action']) )	
   	include('admin/businesses_list.php');
   else
   	include('admin/businessCreator.php');
        
}

function addBusinessMenu() {
	if(!isset($_REQUEST['bID']))
		include('admin/businessCreator.php');
}

function main_bookings() {
	if(!isset($_REQUEST['bID']))		
		include('admin/businesses_bookings.php');
	else
		include('admin/bookingsList.php');
}

function main_facilities() {
	global $title;
	?>
        <h2><?php echo $title;?></h2>
        <?php
        include('admin/businessFacilities.php');
}

function translate_front(){
	include('admin/translate.php');
}

function personal_section_fn() {
	global $title;
	?>
        <h2><?php echo $title;?></h2>
        <?php
        include('admin/adminBusinessSettings.php');

}
function businessman_businessControl(){
	include('admin/businessCreator.php');
}
function businessman_businessList(){	
	if ((isset($_REQUEST['action']) && $_REQUEST['action'] == "view") ||(isset($_REQUEST['type']) && $_REQUEST['type'] == "Hotel")) {
		if ($_REQUEST['action'] == "bookit"){
			include('admin/bookItNow.php');			}	
		else include('admin/rooms.php');
	}
	else{
		if( !isset( $_REQUEST['bID'] ) ) 
			include('admin/businesses_list.php');
		else
			include('admin/businessCreator.php');
	}
}
function dateToYMD($datestr){
	$dateArray = explode('/',$datestr);
	if( sizeof($dateArray) < 2) $dateArray = explode('-',$datestr);
	$newDate = $dateArray[2].'-'.$dateArray[1].'-'.$dateArray[0];
	return $newDate;	
}
function getTextBetweenTags($string, $lang) {
   $start = explode('<!--:'.$lang.'-->', $string);
   $start = explode('<!--:-->', $start[1]);
   return stripslashes($start[0]);
   
}
function check_input($value)
{
// Stripslashes
if (get_magic_quotes_gpc())
  {
  $value = stripslashes($value);
  }
// Quote if not a number
if (!is_numeric($value))
  {
  $value = mysql_real_escape_string($value) ;
  }
return $value;
}
function correctPriceNum($price){	
	$startsWithDelimiter = false;
	$fCharOfPrice = substr($price, 0, 1);
	
	if($fCharOfPrice !== false && ($fCharOfPrice === '.' || $fCharOfPrice === ',')){
		$startsWithDelimiter = true;
	}
	if($startsWithDelimiter){
		$price = substr($price, 1);
		$price = correctPriceNum($price);
	}
	else{
		$firstComma = stripos($price, ',');
		$firstDot  = stripos($price, '.');
		$delimiter = '';
		$delimiterPosition = '';
		if($firstComma !== false){
			$delimiter = ',';
			$delimiterPosition = $firstComma;
		}
		if($firstDot !== false){
			$delimiter = '.';
			$delimiterPosition = $firstDot;
		}
		if($firstDot !== false && $firstComma !== false){
			if($firstDot < $firstComma) {
				$delimiter = '.';
				$delimiterPosition = $firstDot;
			}
			else {
				$delimiter = ',';
				$delimiterPosition = $firstComma;
			} 
		}		
		if($delimiterPosition == '') $delimiterPosition = strlen($price);//gia na mpainei sto telos k na mhn xalaei ari8mo pou den exei komma
		$priceIntegral  = substr($price, 0, $delimiterPosition);
		$priceFractional = substr($price, $delimiterPosition + 1);
		
		$priceFractional = str_replace(',', '', $priceFractional);
		$priceFractional = str_replace('.', '', $priceFractional);
		if($priceFractional == '' || $priceFractional == '0') $priceFractional = '00';
		$price = $priceIntegral.'.'.$priceFractional;
		$price = round($price, 2);
	}
	return $price;
}

function ebooking_admin_actions() {  
		add_menu_page(__('Easy Booking Settings'), __('Easy Booking'), 'edit_themes', 'easy_booking_menu', 'easybooking_menu_render', '', 7);
		add_submenu_page('easy_booking_menu', __('Package Deals'), __('Package Deals'), 'edit_themes', 'busines_deals', 'businesDeals');
		add_submenu_page('easy_booking_menu', __('Businesses list'), __('Businesses'), 'edit_themes', 'busines_menu', 'main_busines');
		//anti gia business_control paliotera eixame to add_business_menu 
		add_submenu_page('easy_booking_menu', __('Business control'), __('Add Business'), 'edit_themes', 'business_control', 'addBusinessMenu');
		add_submenu_page('easy_booking_menu', __('Control Bookings'), __('Bookings'), 'edit_themes', 'bookings_menu', 'main_bookings');		
		add_submenu_page('easy_booking_menu', __('Control Facilities'), __('Facilities'), 'edit_themes', 'facilities_menu', 'main_facilities');
		add_submenu_page('easy_booking_menu', __('Control Translations'), __('Translations'), 'edit_themes', 'translate', 'translate_front');
		
		//gia subscribers pou exoun businessman role
		add_object_page('Notification area', 'Easy Booking', 'eb_businessman', 'eb_businessman_menu', 'personal_section_fn');
		add_submenu_page('eb_businessman_menu', __('Control Business'), __('Businesses'), 'eb_businessman', 'business_list', 'businessman_businessList');
		add_submenu_page('eb_businessman_menu', __('Control Business'), __('Add business'), 'eb_businessman', 'business_control', 'businessman_businessControl');
		add_submenu_page('eb_businessman_menu', __('Control Bookings'), __('Bookings'), 'eb_businessman', 'bookings_menu', 'main_bookings');
		add_submenu_page('eb_businessman_menu', __('Control Facilities'), __('Facilities'), 'eb_businessman', 'facilities_menu', 'main_facilities');
}  



//==================================================================================================
//                       ADD CSS FILE
//==================================================================================================

function admin_register_CSSinHead() {
    $siteurl = get_option('siteurl');
    $url = $siteurl . '/wp-content/plugins/' . basename(dirname(__FILE__)) . '/eb_adminStyle.css';
    echo "<link rel='stylesheet' type='text/css' href='$url' />\n";
}
add_action('admin_head', 'admin_register_CSSinHead');


//==================================================================================================
//                        USE AJAX
//==================================================================================================
if(isset($_REQUEST['page']) && ( $_REQUEST['page']=="busines_menu" || $_REQUEST['page'] = "business_list" ) ){//<--olo ayto 8a mporouse na mpei k sto menu function tou business control
//==========*********Gia AJAX***********==========
add_action('admin_head', 'ajax_fetchRegionsByCountry');
}
function ajax_fetchRegionsByCountry() {
?>
<script type="text/javascript" >
jQuery(document).ready(function($) {
	jQuery('#eb_countries_select').change(function() {
		jQuery(".editLocationArea").hide("slow");
		if(jQuery('#eb_countries_select').val() != '') {
			fetchCountrysRegions();
			jQuery('#checkCountryTranslationBtnArea').show();
		}
		else{
			jQuery('#checkCountryTranslationBtnArea').hide();
			jQuery('#checkRegionTranslationBtnArea').hide();
			jQuery('#checkCityTranslationBtnArea').hide();
			jQuery(".editLocationArea").hide("slow");
			jQuery("#addCityArea").hide();
			jQuery("#regionContainer").hide().html('');
			jQuery("#cityContainer").hide().html('');
			jQuery("#addCityArea").hide();
		}
	});

});

function priceNumbersOnly(evt){
 	var charCode = (evt.which) ? evt.which : event.keyCode;
 	if (charCode == 46 ) return true;//teleia
 	if (charCode == 44 ) return true;//comma
   if (charCode > 31 && (charCode < 48 || charCode > 57))
   	return false;

   return true;
}
function numbersOnly(evt){
 var charCode = (evt.which) ? evt.which : event.keyCode;
 	if (charCode == 32 ) return true;//keno
   if (charCode > 31 && (charCode < 48 || charCode > 57))
   	return false;

   return true;
}

function fetchCountrysRegions(regionID, cityID){
	jQuery("#regionContainer").show();
	jQuery(".editLocationArea").hide("slow");
	jQuery("#regionContainer").html("<img src='<?php echo WP_CONTENT_URL;?>/plugins/wp-easybooking/images/ajax-loader.gif'>");
	jQuery("#cityContainer").html("<img src='<?php echo WP_CONTENT_URL;?>/plugins/wp-easybooking/images/ajax-loader.gif'>");
	var eb_country = jQuery("#eb_countries_select option:selected").val();
	var data = {
		action: 'my_action',//<-AYTO MALLON DEN PREPEI NA ALLAKSEI
		situation: 'OC_FETCH_REGIONS_BY_COUNTRY',
		cID: eb_country
	};
	jQuery.post(ajaxurl, data, function(response) {
		jQuery("#regionContainer").html(response);
		fetchRegionCities(regionID, cityID);
		if(regionID){
			jQuery('#eb_regions_select').val(regionID);
			
		}
		jQuery('#checkRegionTranslationBtnArea').show();
	});	
}
function fetchRegionCities(regionID, cityID){
	jQuery("#cityContainer").show();
	jQuery("#addCityArea").show();
	jQuery(".editLocationArea").hide("slow");
	jQuery("#cityContainer").html("<img src='<?php echo WP_CONTENT_URL;?>/plugins/wpeasybooking/images/ajax-loader.gif'>");
	var eb_region = jQuery("#eb_regions_select option:selected").val();
	if(regionID) eb_region = regionID;
	var data = {
		action: 'my_action',//<-AYTO MALLON DEN PREPEI NA ALLAKSEI
		situation: 'OC_FETCH_CITIES_BY_REGION',
		rID: eb_region
	};
	jQuery.post(ajaxurl, data, function(response) {
		if(response != ''){
			jQuery("#cityContainer").html(response);
			jQuery("#checkCityTranslationBtnArea").show();
			if(cityID){jQuery('#eb_cities_select').val(cityID);}
		}
		else jQuery("#cityContainer").html('<em>No cities found for this region</em>');
	});	
}

function showTranslateLocationForm(locationType, locationSelect, editForm, langs, langsFlagPath, langsFlag, defaultLang){
	jQuery(".editLocationArea").hide().html('');
	jQuery("#addNewCityContainer").hide("slow");
	jQuery("#"+editForm).html("<img src='<?php echo WP_CONTENT_URL;?>/plugins/wp-easybooking/images/ajax-loader.gif'>");
	jQuery("#"+editForm).show();
	var locationID = jQuery("#"+locationSelect).val();
	var data = {
		action: 'my_action',
		situation: 'OC_FETCH_LOCATION_TRANSLATION',
		locationType: locationType,
		locationID: locationID,
		langs: langs,
		langsFlagPath: langsFlagPath,
		langsFlag: langsFlag,
		defaultLang: defaultLang
	};
	jQuery.post(ajaxurl, data, function(response) {
		jQuery("#"+editForm).html(response);
	});
}
function closeTranslateLocationForm(){
	jQuery(".editLocationArea").hide("slow").html('');
	jQuery("#addNewCityContainer").hide("slow");
}

function updateLocationTranslations(locationType, locationID, langs, defaultLang){
	jQuery("#locationMsgArea").hide().addClass('updated');
	jQuery("#locationMsgArea").html("Please wait...<img src='<?php echo WP_CONTENT_URL;?>/plugins/wp-easybooking/images/ajax-loader.gif'>").show();
	var translateFormSerialized = '';
	var languages = langs.split('|');
	for(i = 0; i < languages.length; i++){
		translateFormSerialized += languages[i]+"_translation="+jQuery("#"+languages[i]+"_translation").val()+"&";
	}
	var translationData ="action=my_action&"+translateFormSerialized+"&situation=OC_UPDATE_LOCATION_TRANSLATION&langs="+langs+"&locationType="+locationType+"&locationID="+locationID+"&defaultLang="+defaultLang;
	jQuery.post(ajaxurl, translationData, function(response) {
		if(response == "DEFAULT_NOT_SET"){ 
			jQuery("#locationMsgArea").removeClass('updated').addClass('error').html("<strong>You have to set a value for the default language...</strong>").show("slow");
		}
		else{
			jQuery("#locationMsgArea").html("<strong>Translation completed succesfully!</strong>").show("slow");
		}
	});
}

function addCity(){
	jQuery(".editLocationArea").hide().html('');

	jQuery("#addCitiesMsgArea").hide().html('');
	jQuery("#addNewCityContainer").show();	
}
function insertNewCity(langs, defaultLang, locationType){
	jQuery("#addCitiesMsgArea").hide().html('');
	jQuery("#addCitiesMsgArea").html("<img src='<?php echo WP_CONTENT_URL;?>/plugins/wp-easybooking/images/ajax-loader.gif'>").show();
	var locationParentID = jQuery("#eb_regions_select").val();
	var locationCountryID = jQuery("#eb_countries_select").val();
	var translationData = '';
	var languages = langs.split('|');
	for(i = 0; i < languages.length; i++){
		translationData += "&eb_newcity_"+languages[i]+"="+jQuery("#eb_newcity_"+languages[i]).val();
	}
	var data ="action=my_action&"+ translationData +"&situation=OC_INSERT_LOCATION&langs="+langs+"&locationType="+locationType+"&defaultLang="+defaultLang+"&locationParentID="+locationParentID+"&locationCountryID="+locationCountryID;
	
	jQuery.post(ajaxurl, data, function(response) {
		//alert(response);
		
		if(response == "DEFAULT_NOT_SET"){
			jQuery("#addCitiesMsgArea").removeClass('updated').addClass('error').html("<strong>You have to set a value for the default language...</strong>").show("slow");
		}
		else{
			var sRes = response.split('|');
			if(sRes[0] == "CITY_INSERT_OK"){
				fetchRegionCities(locationParentID, sRes[1]);
				for(i = 0; i < languages.length; i++){
					jQuery("#eb_newcity_"+languages[i]).val('');
				}
				jQuery("#addCitiesMsgArea").html("<strong>New city has been inserted succesfully!</strong><br />The new city now appears in the select area above!").show("slow");		
			}
			else{
				jQuery("#addCitiesMsgArea").removeClass('updated').addClass('error').html("<strong>You have to set a value for the default language...</strong>").show("slow");	
			}
		}
	});

}
</script>
<?php
}//end function 
//}//end of if page = add_busines_menu 
//==================================================================================================

//******************* The PHP for AJAX *************************
add_action('wp_ajax_my_action', 'eb_action_callback');
//==================================================================================================
function eb_action_callback() {
	global $wpdb;
	if(isset($_POST['situation'])){
		if($_POST['situation'] == 'OC_FETCH_REGIONS_BY_COUNTRY'){
			echo '<select name="eb_regions" id="eb_regions_select"  onchange="fetchRegionCities()" style="color:#666;font-weight:bold;">';
			$query = "select RegionID, Region from eb_regions where CountryID = ".$_POST['cID'];
			$regions = $wpdb->get_results($query);
			foreach($regions as $region){
				_e('<option value="'.$region->RegionID.'">'.$region->Region.'</option>');
			}//end of foreach
			echo '</select>';
		}//end of if situation = fetch regions
		//========================================
		if($_POST['situation'] == 'OC_FETCH_CITIES_BY_REGION'){
			$query = "select CityId, City from eb_cities where RegionID = ".$_POST['rID'];
			$cities = $wpdb->get_results($query);
			if(!empty($cities)){
			echo '<select name="eb_cities" id="eb_cities_select" style="color:#666;font-weight:bold;">';
			foreach($cities as $citie){
				_e('<option value="'.$citie->CityId.'">'.$citie->City.'</option>');
			}//end of foreach
			echo '</select>';
			}
		}//end of if situation = fetch cities
		if($_POST['situation'] == 'OC_FETCH_LOCATION_TRANSLATION'){
			$locationTable = '';
			$nameField = '';
			$idField = '';
			if($_POST['locationType'] == 'countries'){
				$locationTable = 'eb_countries';
				$nameField = 'Country';
				$idField = 'CountryId';
			} 
			if($_POST['locationType'] == 'regions'){
				$locationTable = 'eb_regions';
				$nameField = 'Region';
				$idField = 'RegionID';
			}
			if($_POST['locationType'] == 'cities'){
				$locationTable = 'eb_cities';
				$nameField = 'City';
				$idField = 'CityId';
			}
			$locationQ = 'select '.$nameField.' from '.$locationTable. ' where '.$idField.' = '.$_POST['locationID'];
			$locationStr = $wpdb->get_row($locationQ);

			$langsArray = explode('|',$_POST['langs']);
			$langsFlag = explode('|',$_POST['langsFlag']);
			
			echo '<div id="titlewrap">
			<div class="updated" id="locationMsgArea" style="display:none"></div>
				<form id="'.$_POST['locationType'].'_TranlsateForm">
					<table class="widefat">
						<thead>
						<tr>
							<th colspan="2">Translate '.$_POST['locationType'].'<span style="float:right"><a class="littleCloseBtns" onclick="closeTranslateLocationForm();" title="Close translation area">X</a></span></th>
						</tr>
						</thead>
						<tbody>
					';
			$defaultLang = $_POST['defaultLang'];
			$defaultTrans = explode('<!--:'.$defaultLang.'-->',$locationStr->$nameField);
			$defaultTrans = explode('<!--:-->',$defaultTrans[1]);
			if($defaultTrans[0] != '') $defaultTrans = $defaultTrans[0];
			else $defaultTrans = $locationStr->$nameField;
			
			$langCounter = 0;
			foreach($langsArray as $language) { 
				$locationSplit = explode('<!--:'.$language.'-->',$locationStr->$nameField);
				$locationTrans = explode('<!--:-->',$locationSplit[1]);
				if($locationTrans[0] != '') $locationTrans = $locationTrans[0];
				else $locationTrans = $defaultTrans;			
				echo "<tr><td><img alt=\"".$language."\" src=\"".$_POST['langsFlagPath']."/".$langsFlag[$langCounter]."\" /></td>
				<td><input type='text' id='".$language."_translation' name='".$language."_translation' value='".$locationTrans."'></td>
				";
				$langCounter++;
			}	
			echo '</tbody></table>';
			echo '</form>';
			echo '<div style="padding:5px;" align="right"><input type="button" class="button-primary" value="Save translation" onclick="updateLocationTranslations(\''.$_POST['locationType'].'\', \''.$_POST['locationID'].'\', \''.$_POST['langs'].'\', \''.$_POST['defaultLang'].'\')"></div>';
			echo '</div>';
		}
		if($_POST['situation'] == 'OC_UPDATE_LOCATION_TRANSLATION'){
			$defaultLang = $_POST['defaultLang'];
			if(!isset($_POST[$defaultLang.'_translation']) || $_POST[$defaultLang.'_translation'] == '') {
				echo 'DEFAULT_NOT_SET';
				die();
			}
			$langsArray = explode('|',$_POST['langs']);
			$translatedNameStr = '';
			foreach($langsArray as $language) {
				$translatedNameStr .= '<!--:'.$language.'-->'.$_POST[$language.'_translation'].'<!--:-->';
			}
			$nameField = '';
			$idField = '';
			if($_POST['locationType'] == 'countries'){
				$locationTable = 'eb_countries';
				$nameField = 'Country';
				$idField = 'CountryId';
			} 
			if($_POST['locationType'] == 'regions'){
				$locationTable = 'eb_regions';
				$nameField = 'Region';
				$idField = 'RegionID';
			}
			if($_POST['locationType'] == 'cities'){
				$locationTable = 'eb_cities';
				$nameField = 'City';
				$idField = 'CityId';
			}
			$locationQ = 'update '.$locationTable.' set '.$nameField. '= "'.$translatedNameStr.'" where '.$idField .' = '.$_POST['locationID'];
			$wpdb->query($locationQ);
		}
		if($_POST['situation'] == "OC_INSERT_LOCATION"){
			$defaultLang = $_POST['defaultLang'];
			if(!isset($_POST['eb_newcity_'.$defaultLang]) || $_POST['eb_newcity_'.$defaultLang] == '') {
				echo 'DEFAULT_NOT_SET';
				die();
			}
			$langsArray = explode('|',$_POST['langs']);
			$translatedNameStr = '';
			foreach($langsArray as $language) {
				$translatedNameStr .= '<!--:'.$language.'-->'.$_POST['eb_newcity_'.$language].'<!--:-->';
			}
			$newCityID = '';
			$insertQ = 'insert into eb_cities(CountryID, RegionID, City) values ("'.$_POST['locationCountryID'].'", "'.$_POST['locationParentID'].'", "'.$translatedNameStr.'")';
			if($wpdb->query($insertQ)){
				$newCityID = $wpdb->get_var('select CityId from eb_cities where City = "'.$translatedNameStr.'" and RegionID = "'.$_POST['locationParentID'].'" limit 1');	
				echo 'CITY_INSERT_OK|'.$newCityID;
			}
			else 	echo 'INSERT_ERROR';
		}
	}//end of if situation is set	

	die(); // this is required to return a proper result
}//end of if page = business_control

//==================================================================================================
//==================================================================================================

?>
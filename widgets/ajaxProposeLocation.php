<?php
if( isset( $_POST['aPath'] ) && isset( $_POST['pref'] ) && isset( $_POST['locStr'] ) && $_POST['aPath'] != '' && $_POST['pref'] != '' && $_POST['locStr'] != ''){
	$aPath = addslashes($_POST['aPath']);
	$table_prefix  = addslashes($_POST['pref']);
	$locStr = addslashes($_POST['locStr']);
	$hasLang = false;
	$has_no_results = true;
	if( isset( $_POST['lang'] ) && $_POST['lang'] != 'NO_LANGS' ) {
		$hasLang = true;
		$lang = addslashes($_POST['lang']);
		$defaultLang = addslashes($_POST['defaultLang']);
	}
	
	include_once($aPath.'wp-config.php');		 
	include_once($aPath.'wp-load.php');
	include_once($aPath.'wp-includes/wp-db.php');
	if( !include_once($aPath.'wp-content/plugins/wp-easybooking/widgets/trans-vars/search_form.trans.php') ) echo '';
	?>
	<div class="general-title"><span class="location-suggestions"><strong><?php echo getLanguageTitle( $eb_lang_Suggestions, $lang, $defaultLang);?></strong></span>
		<div style="float:right" onclick="hideLocationarea()"><a class="littleCloseBtns" title="Hide">X</a></div>
	</div>
	<table class="widefat" cellpadding="2" cellspacing="2" style="width:330px"> 
	<tr>
	<?php
	$countriesQ = $wpdb->get_results('select CountryId, Country from eb_countries where Country LIKE "%'.$locStr.'%" limit 6');
	if(!empty($countriesQ)){
		$has_no_results = false;
		?>
		<td style="padding-left:2px;width:110px;">
		<h3 class="search-type-title"><?php echo getLanguageTitle( $eb_lang_Countries, $lang, $defaultLang);?></h3>
		<?php
		foreach($countriesQ as $country){
			if ( $hasLang ){
				$countryName = getLanguageTitle( $country->Country, $lang, $defaultLang);
			}
			else $countryName = $country->Country;
			?>
			<p><a class="hoveredLink"  onclick = "selectThisLocation('<?php echo $countryName; ?>', 'Country', <?php echo $country->CountryId; ?>)"><?php echo $countryName; ?></a></p>
			<?php
		}
		?>
		</td>
		<?php
	
	}//IF HAS COUNTRIES
	
	$citiesQ = $wpdb->get_results('select * from eb_cities where City LIKE "%'.$locStr.'%" limit 6');
	if(!empty($citiesQ)){
		$has_no_results = false;
		?>
		<td style="padding-left:2px;width:110px;">
		<h3 class="search-type-title"><?php echo getLanguageTitle( $eb_lang_Cities, $lang, $defaultLang); ?></h3>
		
		<?php
		foreach($citiesQ as $city){
			$citys_region = $wpdb->get_row('select Region from eb_regions where RegionId ='.$city->RegionID);
			$citys_region_name = $citys_region->Region;
			$citys_country = $wpdb->get_row('select Country from eb_countries where CountryId ='.$city->CountryID);
			$citys_country_name = $citys_country->Country;
			$cityName = $city->City;
			if ( $hasLang ){				
   			$cityName = getLanguageTitle( $city->City, $lang, $defaultLang);
   			$citys_region_name =   getLanguageTitle( $citys_region_name, $lang, $defaultLang);
   			$citys_country_name = getLanguageTitle( $citys_country_name, $lang, $defaultLang);
			}
			
			
			?>
			<p>
			<a class="hoveredLink" onclick = "selectThisLocation('<?php echo $cityName; ?>', 'City', <?php echo $city->CityId; ?>)"><?php echo $cityName; ?></a><br />
			<em>(<?php echo $citys_region_name.', '.$citys_country_name; ?>)</em>
			</p>
			<?php
		}
		?>
		</td>
		<?php
	}//IF HAS CITIES
	
	$hotelsQ = $wpdb->get_results('select ID, post_title from '.$table_prefix.'posts where post_title LIKE "%'.$locStr.'%" AND (post_type = "Hotel" OR post_type = "Apartments") AND post_status = "publish" limit 6');
	if(!empty($hotelsQ)){
		$has_no_results = false;
		?>
		<td style="padding-left:2px;width:110px;">
		<h3 class="search-type-title"><?php echo getLanguageTitle( $eb_lang_Resorts, $lang, $defaultLang); ?></h3>		
		<?php
		foreach($hotelsQ as $hotel){
			?>
			<p>
				<a class="hoveredLink" onclick = "selectThisLocation('<?php echo $hotel->post_title; ?>', 'Resort', <?php echo $hotel->ID ?>)"><?php echo $hotel->post_title; ?></a><br />		
				<?php 
				$eb_BusinessCityID = get_post_meta($hotel->ID, "eb_cityID");
				if(!empty($eb_BusinessCityID)) {
					$eb_BusinessCityID = $eb_BusinessCityID[0];
					$hotels_city = $wpdb->get_row('select City, CountryID from eb_cities where CityId ='.$eb_BusinessCityID);
					$citys_country = $wpdb->get_row('select Country from eb_countries where CountryId ='.$hotels_city->CountryID);
					$hotels_city_name = getLanguageTitle( $hotels_city->City, $lang, $defaultLang);
					$citys_country_name = getLanguageTitle( $citys_country->Country, $lang, $defaultLang);
					?>
					<em>(<?php echo $hotels_city_name .' '. $citys_country_name ; ?>)</em>
					<?php
				}
			?>
			</p>
			<?php	

		}		
		?>
		</td>
		<?php
	}//END IF HAS HOTELS
	?>
	</tr></table>	
	<?php
	if($has_no_results){
		$no_results_msg = getLanguageTitle( $eb_lang_NoResultsStr, $lang, $defaultLang);
		echo '<p></p><div class="simple-warning">'.$no_results_msg.'</div>';
	} 	

}
/*
function getTextBetweenTags($string, $lang) {
   $start = explode('<!--:'.$lang.'-->', $string);
   $start = explode('<!--:-->', $start[1]);
   //return stripslashes($start[0]);
   return $start[0];
   
}*/
function getLanguageTitle($location, $lang, $defaultLang){
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
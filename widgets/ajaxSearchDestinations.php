<?php
if(isset($_POST['aPath']) && $_POST['aPath'] != ''){
$aPath = addslashes( $_POST['aPath'] );

	include_once($aPath.'wp-config.php');		 
	include_once($aPath.'wp-load.php');
	include_once($aPath.'wp-includes/wp-db.php');
	global $wpdb;
	
	
	$location = addslashes( $_POST['location'] );
	$hasDates = addslashes( $_POST['dates'] );
	$from = addslashes( $_POST['from'] );
	$to = addslashes( $_POST['to'] );
	//weird date fix this is...
	if( $hasDates == 'no' && $from == '' && $to == '' ){ $from = '01-11-1974'; $to = '03-11-2001';}

	if( isset( $_POST['rooms'] ) && $_POST['rooms'] != '' ){ 
		$rooms = addslashes( $_POST['rooms'] );
		$adults = array();
		$children = array();
		$babies = array();
		for($r = 1; $r <= $rooms; $r++){
			$adults[] = addslashes( $_POST['adultsForRoom'.$r] );
			$children[] = addslashes( $_POST['childrenForRoom'.$r] );
			$babies[] = addslashes( $_POST['babiesForRoom'.$r] );
		}
	}

	$locationType = addslashes( $_POST['type'] );
	$locationID = addslashes( $_POST['lid'] );

	$table_prefix = addslashes( $_POST['pref'] );
	
	$lang = addslashes($_POST['lang']);
	$defaultLang = addslashes($_POST['defaultLang']);
	
	$cur = addslashes( $_POST['ccur'] );
	
	$rpage = addslashes( $_POST['rpage'] );

	if( isset( $_POST['oby'] ) && $_POST['oby'] !='' ){
		$oby = addslashes($_POST['oby']);
		$otype = addslashes($_POST['otype']);
	}	
	$resultObject = new searchDestinations($locationType, $locationID, $location, $hasDates, $from, $to, $adults, $children, $babies, $table_prefix, $lang, $defaultLang, $cur, $aPath, $rpage, $oby, $otype);
	
}
elseif( isset( $_REQUEST['eb'] ) && $_REQUEST['eb'] == "resort" ){
	
}
else die('System Error :: CODE 1');

class searchDestinations{
	
	var $hasErrors = false;
	var $errorMessage = '';
	
	var $table_prefix = '';
	
	var $lang = '';
	var $defaultLang = '';
	
	var $locationQuery = '';
	var $location ='';
	var $locationID = '';
	
	var $fromYear;
	var $toYear;
	var $dateTimeFrom;
	var $dateTimeTo;
	
	var $ccur;
	var $exchange_rates = array();
	
	var $limit; 
	var $limitStart;
	/*Declare the lang vars of the page*/
	var $availableStr = '';
	var $adultsLangStr;
	var $childrenLangStr;
	var $babiesLangStr;
	var $priceStr;
	var $lang_Room;
	var $lang_More;
	var $lang_bookNow;
	var $eb_lang_NoResortsFoundStr;
	
	var $curPage;
	
	var $adults = array();
	var $children = array();
	var $babies = array();
	
	var $oby;
	var $otype;
	
	var $resultsLinkList;
	var $getSimpleResortList = false;
		
	function __construct($locationTypeVal, $locationIDVal, $locationVal, $hasDatesVal, $fromVal, $toVal, $adultsNumVal, $childrenNumVal, $babiesNumVal, $table_prefixVal, $langVal, $defaultLangVal, $cur, $aPath, $rpage, $orderby, $ordertype){
		$this->limit = 10;
		include_once($aPath.'wp-content/plugins/wp-easybooking/widgets/trans-vars/search_results.trans.php');
		
		$this->curPage = $rpage;		
		$this->limitStart = ( $this->curPage - 1 ) * $this->limit;
		/*Some lang vars*/
		$this->availableStr = $eb_lang_Rooms_Available;
		$this->priceStr = $eb_lang_Rooms_Price;
		$this->adultsLangStr = $eb_lang_Adults;
		$this->childrenLangStr = $eb_lang_Children;
		$this->babiesLangStr =  $eb_lang_Babies;
		$this->lang_Room = $eb_lang_Room;
		$this->lang_More = $eb_lang_More;
		$this->lang_bookNow = $eb_lang_BookNow;
		$this->eb_lang_NoResortsFoundStr = $eb_lang_NoResortsFoundStr;
		
		$locationType = $locationTypeVal;
		$this->locationID = $locationIDVal;
		$this->location = $locationVal;
		
		$this->hasDates = $hasDatesVal;
		if( $fromVal != '' ) $this->from = $fromVal; else $this->from = ''; 
		if($toVal != '' ) $this->to = $toVal; else $this->to = '';
		
		$this->table_prefix = $table_prefixVal;
		
		$this->lang = $langVal;
		$this->defaultLang = $defaultLangVal;
			
		$this->ccur = $cur;
		
		$this->adults = $adultsNumVal;	
		$this->children = $childrenNumVal;
		$this->babies = $babiesNumVal; 
		
		$this->oby = $orderby;
		$this->otype = $ordertype;

		global $wpdb;
		

		if($this->location == '') {
			$this->hasErrors = true;
			$this->errorMessage .= '<p>The location parameter is empty</p>';	
		}

		if($this->hasDates != 'no') {
			//$curDate = date();
			$date_from_str = explode('-', $this->from);
			$date_to_str = explode('-', $this->to);
			$this->dateTimeFrom = strtotime( $date_from_str[2].'-'.$date_from_str[1].'-'.$date_from_str[0] ); 
			$this->dateTimeTo = strtotime( $date_to_str[2].'-'.$date_to_str[1].'-'.$date_to_str[0] );
			$dateTimeNow = strtotime( date( "Y-m-d" ) );
			
			if( $this->dateTimeTo < $this->dateTimeFrom ){
				$this->hasErrors = true;
				$this->errorMessage .= '<p>Check in date has to be earlier than the checkout date</p>';	
			}
	
			if( is_numeric($date_from_str[1]) && is_numeric($date_from_str[0]) && is_numeric($date_from_str[2]) && is_numeric($date_to_str[0]) && is_numeric($date_to_str[1]) && is_numeric($date_to_str[0]) ){
				//if( $date_from_str[2] < date('Y') || $date_to_str[2] < date('Y') ){
				if( $this->dateTimeFrom < $dateTimeNow || $this->dateTimeTo < $dateTimeNow ){
					$this->hasErrors = true;
					$this->errorMessage .= '<p>Some date parameter has been set to the past. </p>';
				}
				if( !checkdate( $date_from_str[1], $date_from_str[0], $date_from_str[2] ) || !checkdate( $date_to_str[1], $date_to_str[0], $date_to_str[2] ) ) {
					$this->hasErrors = true;
					$this->errorMessage .= '<p>The date parameters are not valid</p>';
				}
			}
			else{
				$this->hasErrors = true;
				$this->errorMessage .= '<p>The date parameters contain characters</p>';	
			}
		}	

		if($this->hasErrors){
			echo $this->errorMessage;	
		}
		else{
			if( $locationType == "Resort" ){
				//$this->businessTitles_Plain();
				$this->locationQuery = ' b.post_title LIKE "%'.$this->location.'%"';
				$resortsCount = $this->resortsList('Simp', true);				
				$resp = $this->resortsList('Simp');
				if( $resortsCount > $this->limit )
					$resp .= $this->resortPagination( $resortsCount );
				echo '<p><strong class="organizing-element-title">'.$resortsCount.' '.$this->getLanguageTitle( $eb_lang_ResortsFoundStr, $this->lang, $this->defaultLang).'</strong></p>';
				echo $resp;
			}
			

			elseif( $locationType == "Country" ){ 
				if($this->locationID != ''){ 
					$cityList = '';
					$cities = $wpdb->get_results('select CityId from eb_cities where CountryID = '.$this->locationID);
					if(!empty($cities)) {
						foreach($cities as $city){
							$cityList .= $city->CityId.', ';
						}
						if( $cityList != '' ) $cityList = substr($cityList,0,-2);
					}
					$this->locationQuery = ' m.meta_value IN ('.$cityList.') AND m.meta_key="eb_cityID" ';
					
					$resortsCount = $this->resortsList('Comp', true);					

					$resp = $this->resortsList();
					if( $resortsCount > $this->limit )
					$resp .= $this->resortPagination( $resortsCount );
					echo '<p class="organizing-element-title"><strong>'.$resortsCount.' '.$this->getLanguageTitle( $eb_lang_ResortsFoundStr, $this->lang, $this->defaultLang).'</strong></p>';
					echo $resp;
					
				}
				else echo 'LOCATION_ID_MISSING';
			}
			
			
			elseif( $locationType == "City" ){ 
				if($this->locationID != ''){
					$this->locationQuery = 'm.meta_value = "'.$this->locationID.'" AND m.meta_key="eb_cityID" ';
					$resortsCount = $this->resortsList('Comp', true);					

					$resp = $this->resortsList();
					if( $resortsCount > $this->limit )
					$resp .= $this->resortPagination( $resortsCount );
					echo '<p class="resort-counter-area"><strong>'.$resortsCount.' '.$this->getLanguageTitle( $eb_lang_ResortsFoundStr, $this->lang, $this->defaultLang).'</strong></p>';
					echo $resp;
				}
				else echo 'LOCATION_ID_MISSING';
			}
			//If no location search type, display number of businesses for every type (country, city, business name)
			else{ 
				/*business title*/
				echo '<div class = "eb_search-location-in-main-page" style="padding:10px;margin:10px;">';
				echo '<div class="general-title"><strong>'.$this->getLanguageTitle( $eb_lang_Resorts, $this->lang, $this->defaultLang) .'</strong></div>';
				//echo '<hr style="width:100%">';
				$this->locationQuery = ' b.post_title LIKE "%'.$this->location.'%"';
				$this->getSimpleResortList = true;
				$resorts = $this->resortsList('Simp', true);
				echo '<p class="first-letter-capital">'.$this->getLanguageTitle( $eb_lang_ThereWere, $this->lang, $this->defaultLang).' '.$resorts.' '. $this->getLanguageTitle( $eb_lang_ResortsFound, $this->lang, $this->defaultLang);
				if( $resorts > 0 ) echo ': ';
				echo $this->resultsLinkList;
				echo '</p>';
				$this->getSimpleResortList = false;
				echo '</div>';
								
				$citiesNames = $wpdb->get_results('select CityId, City from eb_cities where City LIKE "%'.$this->location.'%"');
					/*city*/
					echo '<div class = "eb_search-location-in-main-page" style="padding:10px;margin:10px;">';
					echo '<div class="general-title"><strong>'.$this->getLanguageTitle( $eb_lang_Cities, $this->lang, $this->defaultLang).'</strong></div>';
					//echo '<hr style="width:100%">';
					
					$page_id = get_option('eb-location-result-page');
					$permalink = '';
					if( get_option('permalink_structure')  == "") $permalink = get_site_url().'?page_id='.$page_id.'&'.$this->lang;
					else $permalink = get_permalink( $page_id );
					if( $this->lang != '' ) $permalink .= '?lang='.$this->lang; 
					$resortsCounterInLocation = 0;
					foreach($citiesNames as $city){
						$this->locationQuery = 'm.meta_value = "'.$city->CityId.'" AND m.meta_key="eb_cityID" ';											
						
						
						$resortsInLocation = $this->resortsList('Comp', true);
						
						if( $resortsInLocation != "NO_RESULTS" ){
							$resortsCounterInLocation++;
							$form = '
							<form action="'.$permalink.'" method="post" id="cityResortsForm_'.$city->CityId.'">
							<input type="hidden" name="eb" value="rs" />
							<input type="hidden" id="cur" name="cur" value="'.$this->ccur.'" />
							<input type="hidden" name="from" value="'.$this->from.'" />
							<input type="hidden" name="to" value="'.$this->to.'" />
							<input type="hidden" name="dates" value="'.$this->hasDates.'" />
							<input type="hidden" name="rooms" value="x" />
							<input type="hidden" name="location" value="'.$this->getLanguageTitle( $city->City, $this->lang, $this->defaultLang).'" />
							<input type="hidden" id="eb-location-type" name="type" value="City" />
							<input type="hidden" id="eb-location-id" name="lid" value="'.$city->CityId.'" />
							
							</form>
							';
							echo $form.$this->getLanguageTitle( $eb_lang_InCity, $this->lang, $this->defaultLang).' <strong><a onclick="javascript:jQuery(\'#cityResortsForm_'.$city->CityId.'\').submit()">'.$this->getLanguageTitle( $city->City, $this->lang, $this->defaultLang).'</a></strong> '.$this->getLanguageTitle( $eb_lang_ThereWere, $this->lang, $this->defaultLang).' <strong>'.$resortsInLocation.' '.$this->getLanguageTitle( $eb_lang_ResortsFound, $this->lang, $this->defaultLang).'</strong><br />';
						}
					}
					if( $resortsCounterInLocation == 0) echo $this->getLanguageTitle( $eb_lang_NoSuchCity, $this->lang, $this->defaultLang);
					echo '</div>';
					/*country*/
				$countriesNames = $wpdb->get_results('select CountryId, Country from eb_countries where Country LIKE "%'.$this->location.'%"');
				echo '<div class = "eb_search-location-in-main-page" style="padding:10px;margin:10px;">';
				echo '<div class="general-title"><strong>'.$this->getLanguageTitle( $eb_lang_Countries, $this->lang, $this->defaultLang).'</strong></div>';
				//echo '<hr style="width:100%">';
				$resortsCounterInLocation = 0;
				foreach( $countriesNames as $countries ){
					$citiesIds = $wpdb->get_results('select CityId from eb_cities where CountryID = '.$countries->CountryId);
					$cityList = '';
					foreach( $citiesIds as $cityId ){
						$cityList .= $cityId->CityId.', ';
					}
					if( $cityList != '' ) $cityList = substr($cityList,0,-2);
					$this->locationQuery = ' m.meta_value IN ('.$cityList.') AND m.meta_key="eb_cityID" ';
					$resortsInLocation = $this->resortsList('Comp', true);
					if( $resortsInLocation != "NO_RESULTS" ){
						$resortsCounterInLocation += (int)$resortsInLocation;
					}
					if( $resortsCounterInLocation > 0 && $resortsInLocation != "NO_RESULTS" ){
						$form = '
							<form action="'.$permalink.'" method="post" id="countryResortsForm_'.$countries->CountryId.'">
							<input type="hidden" name="eb" value="rs" />
							<input type="hidden" id="cur" name="cur" value="'.$this->ccur.'" />
							<input type="hidden" name="from" value="'.$this->from.'" />
							<input type="hidden" name="to" value="'.$this->to.'" />
							<input type="hidden" name="dates" value="'.$this->hasDates.'" />
							<input type="hidden" name="rooms" value="x" />
							<input type="hidden" name="location" value="'.$this->getLanguageTitle( $countries->Country, $this->lang, $this->defaultLang).'" />
							<input type="hidden" id="eb-location-type" name="type" value="Country" />
							<input type="hidden" id="eb-location-id" name="lid" value="'.$countries->CountryId.'" />
							
							</form>
							';
						echo $form.$this->getLanguageTitle( $eb_lang_InCountry, $this->lang, $this->defaultLang).' <a onclick="javascript:jQuery(\'#countryResortsForm_'.$countries->CountryId.'\').submit();"><strong>'.$this->getLanguageTitle( $countries->Country, $this->lang, $this->defaultLang).'</strong></a> '.$this->getLanguageTitle( $eb_lang_ThereWere, $this->lang, $this->defaultLang).' <strong>'.$resortsInLocation.' '.$this->getLanguageTitle( $eb_lang_ResortsFound, $this->lang, $this->defaultLang).'</strong>';
					}
					//else echo '<em>No results</em>';
					
				}
				if( $resortsCounterInLocation == 0) echo $this->getLanguageTitle( $eb_lang_NoSuchCountry, $this->lang, $this->defaultLang);
				echo '</div>';

			}							
			
		}//END OF NO ERRORS
	}	//END OF CONSTRUCTOR
	
	/*===================================================================*/
	//function to get the resorts
	/*===================================================================*/
		function resortsList( $selType = "Comp", $countResults = false ){
			global $wpdb;
			$resortCounter = 0;
			$resortReport = '';
			$selectItems = ' b.ID, b.post_title, b.post_content ';
			$dateQuery = '';
			$dateQueryJoin = '';
			$roomNumJoinQuery = '';
			$roomNumQuery = '';
			if($this->hasDates != 'no'){
					$fromDate = explode('-', $this->from);
					$this->fromYear = $fromDate[2];
					$fromDate = $fromDate[2].'-'.$fromDate[1].'-'.$fromDate[0].' 00:00:00';				
				
					$toDate = explode('-', $this->to);
					$this->toYear = $toDate[2];
					$toDate = $toDate[2].'-'.$toDate[1].'-'.$toDate[0].' 00:00:00';
					
					$dateQueryJoin = 'LEFT JOIN eb_bookingroomdata as rb on r.ID = rb.roomID';
					$dateQuery = ' AND rb.canceled = "NO" AND ((( rb.dateRange_start <= "'.$fromDate.'" AND rb.dateRange_end > "'.$fromDate.'") OR ( rb.dateRange_start < "'.$toDate.'" AND rb.dateRange_end > "'.$toDate.'"))
					OR ((rb.dateRange_start >= "'.$fromDate.'" AND rb.dateRange_start < "'.$toDate.'") OR (rb.dateRange_end > "'.$fromDate.'" AND rb.dateRange_end < "'.$toDate.'")))';
								
			}
		
			$limitstr = 'LIMIT '.$this->limitStart.', '.$this->limit;

			if( $countResults ) $limitstr = '';
			$orderby = '';
			$helpVal = '';
			$helpJoin = '';
			if( $this->oby != '' ){
				if( $this->oby == "name") $orderby = 'order by b.post_title '.$this->otype; 
				if( $this->oby == "stars"){ 
					$orderby = 'order by h.stars '.$this->otype;
					$helpVal = ', h.stars';
					$helpJoin = ' INNER JOIN eb_bushelpvals as h on b.ID = h.bID ';
				}
				if( $this->oby == "price" ){
					$helpJoin = ' INNER JOIN eb_bushelpvals as h on b.ID = h.bID ';
					if( $this->otype == "desc" ){
						$helpVal = ', h.max_price as mp';
						$orderby = 'order by h.max_price desc';
					}
					if( $this->otype == "asc" ){
						$helpVal = ', h.min_price as mp';
						$orderby = 'order by h.min_price '.$this->otype;
					}
				}
				
			}

			if( $selType == "Comp" )
			$results = $wpdb->get_results('select b.ID, b.post_title, b.post_content '.$helpVal.' from '.$this->table_prefix.'posts as b
			INNER JOIN '.$this->table_prefix.'postmeta as m on b.ID = m.post_id 
			'.$helpJoin.' 
			where '.$this->locationQuery.'
			AND (b.post_type = "Hotel" OR b.post_type = "Apartments") AND b.post_status = "publish" 			
			AND b.ID IN (SELECT r.post_parent FROM '.$this->table_prefix.'posts as r
			where r.post_type = "rooms") '.$orderby.' '.$limitstr);

			if( $selType == "Simp" )
				$results = $wpdb->get_results('select b.ID, b.post_title, b.post_name, b.post_content from '.$this->table_prefix.'posts as b where post_title LIKE "%'.$this->location.'%" AND (post_type = "Hotel" OR post_type = "Apartments") AND post_status = "publish" '.$limitstr);

			$foundResults = false;
			
			foreach($results as $result){
				$openingDate = get_post_meta($result->ID, "eb_operatingPeriodStart");
				if(!empty($openingDate)) $openingDate = $openingDate[0]; else $openingDate ='';
				
				$closingDate = get_post_meta($result->ID, "eb_operatingPeriodEnd");
				if(!empty($closingDate)) $closingDate = $closingDate[0]; else $closingDate ='';
				
				$datesAreInOperatingPeriod = $this->checkIfDatesInOperatingPeriod( $this->from, $this->to, $openingDate, $closingDate);
				$roomCountOnly = '';
				if( $countResults ) $roomCountOnly = 'ROOMCOUNTONLY';
				$business_rooms_list = $this->getBusinessRooms( $result->ID , $roomCountOnly);
				if( $datesAreInOperatingPeriod && $business_rooms_list != 'NO_ROOMS_AVAILABLE' && $business_rooms_list != 'NO_ROOMS' && $business_rooms_list != '' ){
					$foundResults = true;
					$resortCounter += 1;	

					$businessLogo = get_post_meta($result->ID, "eb_defaultLogo");
					$bCurrency = $wpdb->get_row('select meta_value from '.$this->table_prefix.'postmeta where post_id = '.$result->ID.' and meta_key = "eb_currency"');
					$bCurrency = $bCurrency->meta_value;
					$resortReport .= '
					<p>
					<div>';
					$seoTitle = str_replace(" ", "-", strtolower( $result->post_title ) );
					$resortPageID = get_option('eb-view-resort');
					if($businessLogo[0] != ''){

					} 

					$page_id = get_option('eb-view-resort');
					$permalink = '';
					if( get_option('permalink_structure')  == "") $permalink = get_permalink( $page_id ).'&lang='.$this->lang;
					else $permalink = get_permalink( $page_id ).'?lang='.$this->lang;										
				
							$resortReport .= '
							<div class="eb_column">
							';
								$stars = get_post_meta($result->ID, "eb_stars");
								$extendedURL = '';
								//if( $this->hasDates != 'no') $extendedURL = '&from='.$this->from.'&to='.$this->to.'&cur='.$this->ccur;//<-den xreiazetai  
								$resortReport .= '<h1 class="resort-title-at-results-page">';
									//$resortReport .= '<a href="'.$permalink.'&t='.$seoTitle.'&eb=resort&b='.$result->ID.$extendedURL.'" title="'.__($result->post_title).'">'.__($result->post_title).'</a>';
									$resortReport .= '<a class="link-button" onclick="goToResortPage(\''.$permalink.'&t='.$seoTitle.'&b='.$result->ID.$extendedURL.'\')" title="'.__($result->post_title).'">'.__($result->post_title).'</a>';
									$resortReport .= ' <img src = "'.WP_CONTENT_URL.'/plugins/wp-easybooking/images/stars/'.$stars[0].'star-small.png" height="12px" title="'.$stars[0].' stars" />';
								$resortReport .= '</h1>';
							
								$eb_BusinessCityID = get_post_meta($result->ID, "eb_cityID");
							
								if(!empty($eb_BusinessCityID)) {
									$eb_BusinessCityID = $eb_BusinessCityID[0];
									$hotels_city = $wpdb->get_row('select City, CountryID from eb_cities where CityId ='.$eb_BusinessCityID);
									$citys_country = $wpdb->get_row('select Country from eb_countries where CountryId ='.$hotels_city->CountryID);
									$hotels_city_name = $this->getLanguageTitle( $hotels_city->City, $this->lang, $this->defaultLang);
									$citys_country_name = $this->getLanguageTitle( $citys_country->Country, $this->lang, $this->defaultLang);
								
									$resortReport .= '<div class="resort-location-at-results-page">'.$hotels_city_name. ' , '.$citys_country_name.' <span style="float:right; margin-right:20px;"><a title="'.$this->getLanguageTitle( $this->lang_bookNow, $this->lang, $this->defaultLang).'" onclick="goToResortPage(\''.$permalink.'&t='.$seoTitle.'&b='.$result->ID.$extendedURL.'\')" class="eb-search-button little">'.$this->getLanguageTitle( $this->lang_bookNow, $this->lang, $this->defaultLang) .'</a></span></div>';
								}
								
								if($this->getSimpleResortList){
									if($this->resultsLinkList != '' ) $this->resultsLinkList .= ', ';
									$this->resultsLinkList .= ' <a href="'.$permalink.'&resort='.$result->post_name.'">'.$result->post_title.'<em style="font-size:11px">('.$hotels_city_name.', '.$citys_country_name.')</em></a>';
								}
								
								$resortReport .= '<div style="width:600px;margin-top:0px;"> ';	
								$resortReport .= '<a style="float:left;padding-right:10px;" class="link-button" title="'.__( $result->post_title ).'" onclick="goToResortPage(\''.$permalink.'&t='.$seoTitle.'&b='.$result->ID.$extendedURL.'\')"><img src = "'.WP_CONTENT_URL.'/plugins/wp-easybooking/images/businessImg/thumbs/'.$businessLogo[0].'" /></a>';
								$resortReport .= '<span class="resort-description-at-results-page">'.substr( strip_tags( $this->getLanguageTitle( $result->post_content, $this->lang, $this->defaultLang) ), 0, 200).' <a class="more-link" onclick="goToResortPage(\''.$permalink.'&t='.$seoTitle.'&b='.$result->ID.$extendedURL.'\')"><em style="font-size:10px">'.$this->getLanguageTitle( $this->lang_More, $this->lang, $this->defaultLang).'</em></a></span>';
								//$resortReport .= '<p><a onclick="goToResortPage(\''.$permalink.'&t='.$seoTitle.'&b='.$result->ID.$extendedURL.'\')" class="eb-search-button little">'.__( 'Book now' ) .'</a></p>';
								$resortReport .= '<div class="eb_clear"></div>';
								$resortReport .= '<div id="scrollbar1" class="scrollDataDiv" >
											<div class="scrollbar"><div class="track"><div class="thumb"><div class="end"></div></div></div></div>
												<div class="viewport">
													<div class="overview">';														
														$resortReport .= $business_rooms_list;							
													$resortReport .= '</div>
											</div></div></div>'; 		
							$resortReport .= '
							</div>
					
							<div class="eb_clear"></div>
						</div>
				
					</p>
					<hr />
					<script type="text/javascript" > jQuery(".scrollDataDiv").tinyscrollbar();</script>
				';
			}
		}//end foreach
		if( $countResults ) return $resortCounter; 
		else {
			if( $foundResults ) return $resortReport;
			else return '<div class="simple-warning">'.$this->getLanguageTitle( $this->eb_lang_NoResortsFoundStr, $this->lang, $this->defaultLang).'</div>';
			
		}
	}
		
	function getBusinessRooms( $bID, $switch = "ROOMCOUNTONLY" ){
		global $wpdb;		
		$roomsRequested = count( $this->adults );//the number of rooms requested
		$adultsArray = $this->adults;
		$childrenArray = $this->children;
		$babiesArray = $this->babies;
		$roomTypesAvailable = 0;
		$roomTypes = $wpdb->get_results('select ID, post_title from '.$this->table_prefix.'posts where post_parent = '.$bID);
		
		$returnRooms = '';
		$returnRooms .= '<table style="width:585px;border:none;" cellpadding="5" cellspacing="5" style="border:none;">';

		if( count( $roomTypes ) > 0){
			
			$returnRooms .= '<tr>';
				$returnRooms .= '<td style="border:none;" class="resort-availableTitles-at-results-page">'.$this->getLanguageTitle( $this->lang_Room, $this->lang, $this->defaultLang).'</td>';
				//$returnRooms .= '<td align="center" style="border:none;">'.$this->getLanguageTitle( $this->availableStr, $this->lang, $this->defaultLang).'</td>';
								
				$returnRooms .= '<td align="center" style="border:none;" class="resort-availableTitles-at-results-page">'.$this->getLanguageTitle( $this->adultsLangStr, $this->lang, $this->defaultLang).'</td>';
				$returnRooms .= '<td align="center" style="border:none;" class="resort-availableTitles-at-results-page">'.$this->getLanguageTitle( $this->childrenLangStr, $this->lang, $this->defaultLang).'</td>';
				$returnRooms .= '<td align="center" style="border:none;" class="resort-availableTitles-at-results-page">'.$this->getLanguageTitle( $this->babiesLangStr, $this->lang, $this->defaultLang).'</td>';
				if($this->hasDates != 'no')
				$returnRooms .= '<td align="center" style="border:none;" class="resort-availableTitles-at-results-page">'.$this->getLanguageTitle( $this->priceStr, $this->lang, $this->defaultLang).'</td>';
			$returnRooms .= '</tr>';
			$unsetList = array();//Aytos o pinakas einai aparaithtos gia thn apo8hkeush twn index tou pinaka twn adults gia ta dwmatia...gsai ta mperdema to kserw
			foreach( $roomTypes as $roomType){				
			
				$roomtTypeRoomCount = 0;
				$roomCounter = 0;
		
				$rooms = get_post_meta($roomType->ID, "eb_roomNum");
				if( count( $rooms) > 0 ) $roomCounter = (int)$rooms[0];
				
				$adultsInRoom = get_post_meta($roomType->ID, "eb_peopleNum");
				$adultsInRoom = $adultsInRoom[0];
				$childrenInRoom = get_post_meta($roomType->ID, "eb_childrenAllowed");
				$childrenInRoom = $childrenInRoom[0];
				$babiesInRoom = get_post_meta($roomType->ID, "eb_babiesAllowed");
				$babiesInRoom = $babiesInRoom[0];
					
				if($this->hasDates != 'no'){
					
					$fromDate = explode('-', $this->from);
					$this->fromYear = $fromDate[2];
					$fromDate = $fromDate[2].'-'.$fromDate[1].'-'.$fromDate[0].' 00:00:00';				
				
					$toDate = explode('-', $this->to);
					$this->toYear = $toDate[2];
					$toDate = $toDate[2].'-'.$toDate[1].'-'.$toDate[0].' 00:00:00';
					
					/*$roomSubQuery = 'select COUNT(id) from eb_bookingroomdata where roomID = '.$roomType->ID.' AND canceled = "NO"
					AND ((( dateRange_start <= "'.$fromDate.'" and dateRange_end > "'.$fromDate.'") OR ( dateRange_start < "'.$toDate.'" and dateRange_end > "'.$toDate.'"))
					OR ((dateRange_start >= "'.$fromDate.'" and dateRange_start < "'.$toDate.'") OR (dateRange_end > "'.$fromDate.'" and dateRange_end < "'.$toDate.'")))';*/
					
					$numToSubFromRooms = 0;					
					
					$roomSubQueryQ = 'select * from eb_bookingroomdata where roomID = '.$roomType->ID.' AND canceled = "NO"
					AND ((( dateRange_start <= "'.$fromDate.'" and dateRange_end > "'.$fromDate.'") OR ( dateRange_start < "'.$toDate.'" and dateRange_end > "'.$toDate.'"))
					OR ((dateRange_start >= "'.$fromDate.'" and dateRange_start < "'.$toDate.'") OR (dateRange_end > "'.$fromDate.'" and dateRange_end < "'.$toDate.'")))';
					$roomsBookingsRes = $wpdb->get_results($roomSubQueryQ);
					foreach($roomsBookingsRes as $book){
						$numToSubFromRooms++; 
					}
					
					$roomCounter = $roomCounter - $numToSubFromRooms;
	
				}
				//When a specific room number is requested	
				if( $roomCounter > 0 ){
					$roomSpec = $roomCounter;	
					$roomTypesAvailable++;	
										
					for( $rc = 0; $rc < $roomsRequested; $rc++ ){

						if( !in_array($rc, $unsetList) ){
							if( $adultsInRoom >= $adultsArray[$rc] && $childrenInRoom >= $childrenArray[$rc] && $babiesInRoom >= $babiesArray[$rc] && $roomSpec > 0){
								$roomSpec--;									
								unset($adultsArray[$rc]);
								array_values($adultsArray);
								unset($childrenArray[$rc]);
								unset($babiesArray[$rc]);
								$unsetList[] = $rc;														
							}						
						}
					}
					$localMinAdults = 1;
					$localMinChildren = 0;
					$localMinBabies = 0;
					if( $this->adults ) $localMinAdults = min($this->adults);
					if( $this->children ) $localMinChildren = min($this->children);
					if( $this->babies ) $localMinBabies = min($this->babies);
					
					if( $adultsInRoom >= $localMinAdults && $childrenInRoom >= $localMinChildren && $childrenInRoom >= $localMinBabies){
					$roomPrice = '-';
					if( $this->hasDates != 'no' && $switch != 'ROOMCOUNTONLY' ) $roomPrice = $this->roomPrice( $bID, $roomType->ID, $fromDate, $toDate );
					else $roomPrice = '';
						$returnRooms .= '<tr>';
							$returnRooms .= '<td style="border-color:#fff0c4;" class="resort-roomTitles-at-results-page" >';
								$returnRooms .= $this->getLanguageTitle( $roomType->post_title, $this->lang, $this->defaultLang).' '.$this->defaultLang;
							$returnRooms .= '</td>';
							/* AVAILABLE ROOMS
							$returnRooms .= '<td align="center">';
								$returnRooms .= $roomCounter;
							$returnRooms .= '</td>';*/
							$returnRooms .= '<td style="border-color:#fff0c4;" class="resort-roomOptions-at-results-page" align="center">';
								$returnRooms .= $adultsInRoom;
							$returnRooms .= '</td>';
							$returnRooms .= '<td style="border-color:#fff0c4;" class="resort-roomOptions-at-results-page" align="center">';
								$returnRooms .= $childrenInRoom;
							$returnRooms .= '</td>';
							$returnRooms .= '<td style="border-color:#fff0c4;" class="resort-roomOptions-at-results-page" align="center">';
								$returnRooms .= $babiesInRoom;
							$returnRooms .= '</td>';
							if($this->hasDates != 'no'){
							$returnRooms .= '<td style="border-color:#fff0c4;" class="resort-roomsPrices-at-results-page" align="center">';
								$returnRooms .= $roomPrice;
							$returnRooms .= '</td>';
							}
						$returnRooms .= '</tr>';
						}//end if( count( $adultsArray ) > 0 ) {return 'NO_ROOMS_AVAILABLE';}
				
			}
			//else return 'NO_ROOMS_AVAILABLE';
			else $returnRooms .= '';
		}
		
		}
		else {
			return 'NO_ROOMS';
		}
				
		$returnRooms .= '</table>';		
		
		


			if( count( $adultsArray ) > 0 ) {return 'NO_ROOMS_AVAILABLE';}	
			
			if( $roomTypesAvailable == 0 ) return 'NO_ROOMS_AVAILABLE';
			return $returnRooms;

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
			$lowSeason = $wpdb->get_row('select meta_value from '.$this->table_prefix.'postmeta where post_id = '.$bID.' and meta_key = "eb_lowSeason"');	
			$lowSeasonStart = explode('[-]',$lowSeason->meta_value);
			$lowSeasonEnd = $lowSeasonStart[1];			
			$lowSeasonStart = $lowSeasonStart[0];
			$lowSeasonStart = str_replace("2011",$this->fromYear,$lowSeasonStart);
			$lowSeasonEnd = str_replace("2011",$this->toYear,$lowSeasonEnd);
			
			$midSeason = $wpdb->get_row('select meta_value from '.$this->table_prefix.'postmeta where post_id = '.$bID.' and meta_key = "eb_midSeason"');	
			$midSeasonStart = explode('[-]',$midSeason->meta_value);
			$midSeasonEnd = $midSeasonStart[1];			
			$midSeasonStart = $midSeasonStart[0];
			$midSeasonStart = str_replace("2011",$this->fromYear,$midSeasonStart);
			$midSeasonEnd = str_replace("2011",$this->toYear,$midSeasonEnd);
			
			$highSeason = $wpdb->get_row('select meta_value from '.$this->table_prefix.'postmeta where post_id = '.$bID.' and meta_key = "eb_highSeason"');	
			$highSeasonStart = explode('[-]',$highSeason->meta_value);
			$highSeasonEnd = $highSeasonStart[1];			
			$highSeasonStart = $highSeasonStart[0];
			$highSeasonStart = str_replace("2011",$this->fromYear,$highSeasonStart);
			$highSeasonEnd = str_replace("2011",$this->toYear,$highSeasonEnd);
			
			$midSeason2 = $wpdb->get_row('select meta_value from '.$this->table_prefix.'postmeta where post_id = '.$bID.' and meta_key = "eb_midSeason2"');	
			$midSeasonStart2 = explode('[-]',$midSeason2->meta_value);
			$midSeasonEnd2 = $midSeasonStart2[1];			
			$midSeasonStart2 = $midSeasonStart2[0];
			$midSeasonStart2 = str_replace("2011",$this->fromYear,$midSeasonStart2);
			$midSeasonEnd2 = str_replace("2011",$this->toYear,$midSeasonEnd2);
			
			$lowSeason2 = $wpdb->get_row('select meta_value from '.$this->table_prefix.'postmeta where post_id = '.$bID.' and meta_key = "eb_lowSeason2"');	
			$lowSeasonStart2 = explode('[-]',$lowSeason2->meta_value);
			$lowSeasonEnd2 = $lowSeasonStart2[1];			
			$lowSeasonStart2 = $lowSeasonStart2[0];
			$lowSeasonStart2 = str_replace("2011",$this->fromYear,$lowSeasonStart2);
			$lowSeasonEnd2 = str_replace("2011",$this->toYear,$lowSeasonEnd2);
			
			$lowSeasonPrice = $wpdb->get_row('select meta_value from '.$this->table_prefix.'postmeta where post_id = '.$rID.' and meta_key = "eb_lprice"');
			$lowSeasonPrice = $lowSeasonPrice->meta_value;
			$midSeasonPrice = $wpdb->get_row('select meta_value from '.$this->table_prefix.'postmeta where post_id = '.$rID.' and meta_key = "eb_mprice"');
			$midSeasonPrice = $midSeasonPrice->meta_value;
			$highSeasonPrice = $wpdb->get_row('select meta_value from '.$this->table_prefix.'postmeta where post_id = '.$rID.' and meta_key = "eb_hprice"');
			$highSeasonPrice = $highSeasonPrice->meta_value;
						
			$pos = '0';
			for($dayCount = 0; $dayCount < $daysNum ; $dayCount++){
				$next_date = date('Y-m-d', strtotime($fromDate .' +'.$dayCount.' day'));
				if($next_date >= $lowSeasonStart && $next_date < $lowSeasonEnd){
					$roomPrice += $lowSeasonPrice;
					$pos = '1';					
				}
				if($next_date >= $midSeasonStart && $next_date < $midSeasonEnd){
					$roomPrice += $midSeasonPrice;
					$pos = '2: '.$midSeasonStart.' '.$midSeasonEnd.' from:'.$fromDate;					
				}
				if($next_date >= $highSeasonStart && $next_date < $highSeasonEnd){
					$roomPrice += $highSeasonPrice;
					$pos = '3';				
				}
				if($next_date >= $midSeasonStart2 && $next_date < $midSeasonEnd2){
					$roomPrice += $midSeasonPrice;
					$pos = '4';					
				}
				if($next_date >= $lowSeasonStart2 && $next_date <= $lowSeasonEnd2){
					$roomPrice += $lowSeasonPrice;
					$pos = '5';					
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

function resortPagination( $resortNum ){
	$paginationString = '';
	if($this->curPage > 1){
		$prevPage = $this->curPage - 1;
		$paginationString = '<a onclick="navResults('.$prevPage.');"  class="optionBtn"> Previous </a>';
	}
	$pages = ceil($resortNum / $this->limit);
	for( $i = 1; $i <= $pages; $i++) {
		$class = 'optionBtn';
		$onclick = 'onclick="navResults('.$i.');"';
		if( $this->curPage == $i ) {
			$class = 'optionBtnSelected';
			$onclick = '';
		}
		$paginationString .= '<a '.$onclick.' class="'.$class.'" title = "page '.$i.'"> '.$i.' </a>';
	}
	if($this->curPage < $pages){
		$nextPage = $this->curPage + 1;
		$paginationString .= '<a onclick="navResults('.$nextPage.');" class="optionBtn"> Next </a>';
	}
	return '<div id="business-results-pagination" align="center" class="eb-business-results-pagination">'.$paginationString.'</div>';
}

	function checkIfDatesInOperatingPeriod( $sFrom, $sTo, $sOpen, $sClose){
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
}
?>
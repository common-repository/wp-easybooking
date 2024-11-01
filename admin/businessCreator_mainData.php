
<?php
global $wpdb;
global $eb_path;
global $q_config;
global $countriesTable;
global $current_user;
$user_id = get_current_user_id();
$user_info = get_userdata( $user_id );
//*****************Check if countries exist ::>> if not neither regions nor cities exist 
									if($wpdb->get_var("SHOW TABLES LIKE '$countriesTable'") == $countriesTable) {

									}else{
										//***eb_cities is over 1 MB so it is compressed. Need to uncompress
										$zip = new ZipArchive;
     									$res = $zip->open($eb_path.'/admin/eb_cities.sql.zip');
     									if ($res === TRUE) {
         								$zip->extractTo($eb_path.'/admin/eb_cities.sql');
         								$zip->close();
         								//echo 'UNZIPPED';
         								//***Since uncompressed execute the sql queries
         								$file_content = file($eb_path.'/admin/eb_cities.sql/eb_cities.sql');
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
											$file_content = file($eb_path.'/admin/eb_countries.sql');
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
											$file_content = file($eb_path.'/admin/eb_regions.sql');
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
											//***Once new tables entered remove all unzipped folders and files created
											/*$d = dir($eb_path.'/admin/eb_cities.sql/'); 
											while($entry = $d->read()) { 
 												if ($entry!= "." && $entry!= "..") { 
 													unlink($entry); 
 												} 
											} 
											$d->close();*/ 
											unlink($eb_path.'/admin/eb_cities.sql/eb_cities.sql'); 
											rmdir($eb_path.'/admin/eb_cities.sql/'); 
     									} else {
         								echo 'NO ZIP';
     									}
									}

//=========================================================
//           CHECK USER LEVEL
//=========================================================
//global $current_user;
$noAdmin_whereStr = '';
if($user_info->user_level == 0 && $user_info->user_level != 10) {
	$noAdmin_whereStr = 'and post_author = "'.$user_info->ID.'"';
}
//=========================================================
//=========================================================	

$langsStr = '';
$langsFlagPath = '';
$langsFlag = '';									
if(!empty($q_config['enabled_languages'])) {
	foreach($q_config['enabled_languages'] as $language) {
		$langsStr .= '|'.$language;
		$langsFlag .= '|'.$q_config['flag'][$language];
	}
	$langsStr = substr($langsStr, 1);//afairw to prwto |
	$langsFlag = substr($langsFlag, 1);
	$langsFlagPath = WP_CONTENT_URL.'/'.$q_config['flag_location'];
}
?>
<tbody style="border:none">
<tr>
	<td style="width:100%;border:none;"><br><br>
		<a name="pagingTarget"></a>
		<div style="margin-top:3px;top:3px;left:10px;position:relative;width:100%;z-index:2;">
		<ul id="sidemenu" style="display:none;width:100%;">
			<li><a href="#" onclick="showInfo('basicInfoDiv');" id="basicInfoDiv_tab" class="current">Basic information</a></li>			
			
			<?php if(isset($_REQUEST['bID']) || $businessId != ''){?>
			<li><a href="#" onclick="showInfo('contactInfoDiv');" id="contactInfoDiv_tab">Contact information</a></li>
			<li><a href="#" onclick="showInfo('facilitiesInfoDiv');" id="facilitiesInfoDiv_tab">Facilities</a></li>
			<li><a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</a></li>
			<li><a href="#" onclick="showInfo('seasonsInfoDiv');" id="seasonsInfoDiv_tab">Seasons/Operating Period</a></li>
			<li><a href="#" onclick="showInfo('policiesInfoDiv');" id="policiesInfoDiv_tab">Policies</a></li>
			<li><a href="#" onclick="showInfo('paymentInfoDiv');" id="paymentInfoDiv_tab">Payment Accounts</a></li>
			<?php //if($current_user->wp_user_level == 10){?>
			<li><a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</a></li>
			
			<?php //}?>
			<!--<li><a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</a></li>
			<li><a href="#" onclick="showInfo('imagesInfoDiv');" id="imagesInfoDiv_tab">Images</a></li>-->
			<?php }
			else{?>
			<li><a style="color:#ccc" title="This option will be enabled after inserting the new business">Contact information</a></li>
			<li><a style="color:#ccc" title="This option will be enabled after inserting the new business">Facilities</a></li>
			<li><a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</a></li>
			<li><a style="color:#ccc" title="This option will be enabled after inserting the new business">Seasons/Operating Period</a></li>
			<li><a style="color:#ccc" title="This option will be enabled after inserting the new business">Policies</a></li>
			<li><a style="color:#ccc" title="This option will be enabled after inserting the new business">Payment Accounts</a></li>			
			<!--<li><a style="color:#ccc" title="This option will be enabled after inserting the new business">Images</a></li>-->	
			<?php }
			?>	
		</ul>
		</div>

<script type="text/javascript" >	
	function showInfo(infoDivID){
		if(jQuery('#'+infoDivID).css("display") == "none"){
			jQuery(".eb_simpleContainer").hide();
			jQuery('#'+infoDivID).show();			
		}
		//if(infoDivID  == "imagesInfoDiv") jQuery("#imagesInfoDiv").html(jQuery("#copyThisToMainData").html()+jQuery("#imagesInfoDiv").html());
		
		jQuery(".current").removeClass("current");
		jQuery("#"+infoDivID+"_tab").addClass("current");
	}
</script>
	</td>
</tr>
<tr>
	<td style="border:none;width:100%;background-color:#fff;" align="center">
		<table cellpadding="0" cellspacing="2" style="border:none;" style="width:100%">
			<tr class="form-field form-required">
				<td width="100%" style="width:100%;border:none" align="left">
					<div class="eb_simpleContainer" id="basicInfoDiv" style="width:900px">
						<h3>Basic information</h3>
						<div style="border:1px solid #dfdfdf;padding:2px;background-color:#f8f8f8;-webkit-border-radius: 5px;-moz-border-radius: 5px;border-radius: 5px;">
							<label><strong>Business name</strong> <span class="description">(required)</span></label><br>
							<input type="text" style="width:99%;" name="ebTitle" value="<?php echo $eb_BusinessTitle;?>"/>
						</div>
							
						<?php if(!isset($_REQUEST['bID'])){?>
							<br>
						<span id="selectBusinessType">
							<label>Define the type of your business</label>
							<select id="select_busType" name="eb_type">
								<option value="Hotel"  <?php if($eb_BusinessType == "Hotel") echo 'selected';?> >Hotel</option>
								<option value="Apartments" <?php if($eb_BusinessType == "Apartments") echo 'selected';?> >Apartments</option>
								<!--<option value="Car rental" <?php if($eb_BusinessType == "Car rental") echo 'selected';?> >Car rental</option>
								<option value="Shipping cruises" <?php if($eb_BusinessType == "Shipping cruises") echo 'selected';?> >Shipping cruises</option>-->
							</select>
						</span>
						<?php }?>
						<?php //if ($eb_BusinessType == "Hotel" || $eb_BusinessType == ""){?>
						<div id="buStarsContainer">
							<label><strong>Hotel Stars</strong></label>	
							<select id="select_busStars" name="eb_stars">
									<option value="1" <?php if($eb_BusinessStars == "1") echo 'selected';?> >1</option>
									<option value="2" <?php if($eb_BusinessStars == "2") echo 'selected';?> >2</option>
									<option value="3" <?php if($eb_BusinessStars == "3") echo 'selected';?> >3</option>
									<option value="4" <?php if($eb_BusinessStars == "4") echo 'selected';?> >4</option>
									<option value="5" <?php if($eb_BusinessStars == "5") echo 'selected';?> >5</option>
							</select>						
						</div>						
						<?php //} ?>
							
						<table style="border:none;width:100%">
							<tr>
								<td style="width:50%;background:transparent;" class="eb_simpleContainerV">
								<span id="eb_ownerContainer">
									<label style="padding-left:15px;"><strong>Business owner</strong> <span class="description">(required)</span></label><br>
									<div align="center">
									<?php
									$eb_BusinessOwner = 'Select Owner';
									if($eb_BusinessOwnerID != ''){
										$ownerData = get_userdata( $eb_BusinessOwnerID );
										$eb_BusinessOwner = $ownerData->last_name.' '. $ownerData->first_name;
									}
									if($user_info->user_level != 0) {
									?>
									<input name="eb_owner" type="button" class="button-primary" id="eb_ownerBtn" tabindex="5" value="<?php echo $eb_BusinessOwner; ?>" style="width:90%" onclick="showUserList()" />
								
									<?php 
									}
									else {
										$eb_BusinessOwner = $user_info->user_lastname.' '. $user_info->user_firstname;
										$eb_BusinessOwnerID = $user_info->ID;
										echo '<input name="eb_owner" type="button" class="button-primary" id="eb_ownerBtn" tabindex="5" value="'.$eb_BusinessOwner.'" style="width:90%"/>';
									}
									?>
									<input type="hidden" id="eb_ownersId" name="eb_ownersId" value="<?php echo $eb_BusinessOwnerID; ?>"/>
									</div>
									</td>
									<td style="border:none">
									<div id="eb_userlist" style="display:none;padding:5px;border:1px solid #a4cdd7;border-radius: 1em;box-shadow: rgba(0,0,0,0.4) 1px 1px 1px 1px;position:absolute;z-index:2;background-color:#fff; opacity: 0.9;">
									<table class="widefat" style="width:100%">
										<thead>
										<tr>
											<th>
												Please select the business owner<span style="float:right"><a class="littleCloseBtns" onclick="hideUserList();" title="Close users list">X</a></span>
											</th>	
										</tr>
										</thead>
										<tr>
										<td><div style="background: #23769d;height:200px;overflow:scroll;"><div style="line-height:5px">&nbsp;</div>
										<?php
											//gia na epilegei mono tous users me rolo businessman
											 $author_ids = get_users('orderby=nicename&role=eb_businessman');
											foreach($author_ids as $author){
												$lastName = get_user_meta( $author->ID, 'last_name', 'true' );
												$firstName = get_user_meta( $author->ID, 'first_name', 'true' );
												echo '<div style="padding-right:5px;padding-left:5px;" id="selectOwnerBtn_'.$author->ID.'" onclick="setOwner('.$author->ID.', \''.$lastName.' '.$firstName.'\')">
													<div class="userLine" style="padding:5px;color: #fff;background: #23769d;border: 1px solid #0b3a50; border-right: 1px solid #8bb7cb;border-bottom: 1px solid #8bb7cb;-webkit-border-radius: 5px;-moz-border-radius: 5px;border-radius: 5px;"><strong>
														<span>'.$lastName.'</span>
														<span>'.$firstName.'</span></strong>
														<span>(<i>'.$author->user_nicename.' - '.$author->user_email.'</i>)</span>
													</div>												
												</div>';
											}										
										?>
										<div style="line-height:5px">&nbsp;</div>
									</div></td></tr>
									</table>
								</div>
							</span>
							</td>
							<td style="width:50%;background:transparent;" class="eb_simpleContainerV">
							<span>
							<label><strong>Business Currency</strong></label>
							<?php

							include($eb_path.'/currencyConverter.php');
							$x = new CurrencyConverter(DB_HOST,DB_USER,DB_PASSWORD,DB_NAME,'currencies');
							$currencies = $wpdb->get_results('select currency from currencies');
							?>
							<select name="eb_currency" id="eb_currency">
								<?php
								if($currencies){
									foreach($currencies as $currency){
										echo '<option value="'.$currency->currency.'" '; 
										if($eb_BusinessCurrency == $currency->currency) echo ' selected';										
										echo'> '.$currency->currency. ' </option>';										
									}
								}
								?>
							</select>
							</span>
							</td>
							</tr>
							</table>
							<br>

							<h3>Description</h3>
							<div style="padding:2px">
							<div style="display:none;">
								<div id="titlediv" >
									<div id="titlewrap">
										<input type="text" name="invisible" size="30" tabindex="1" value="" id="title" autocomplete="on" />
									</div>	
								</div>
							</div>
									<div style="border:1px solid #dfdfdf;padding:2px;background-color:#f8f8f8;-webkit-border-radius: 5px;-moz-border-radius: 5px;border-radius: 5px;">
										<div id="poststuff">
										<div id="<?php echo user_can_richedit() ? 'postdivrich' : 'postdiv'; ?>" class="postarea" style="background-color:#fff;">
										<?php 
										//the_editor($eb_BusinessDescription,$id = 'content', $prev_id = 'title', $media_buttons = true, $tab_index = 1); 
										global $wp_version; 
													if( $wp_version >= 3.4)
														wp_editor($eb_BusinessDescription,$id = 'content', $prev_id = 'title', $media_buttons = true, $tab_index = 1);
													else 
														the_editor($eb_BusinessDescription,$id = 'content', $prev_id = 'title', $media_buttons = true, $tab_index = 1);
										?>

										<?php wp_nonce_field( 'closedpostboxes', 'closedpostboxesnonce', false ); ?>
										<?php wp_nonce_field( 'meta-box-order', 'meta-box-order-nonce', false ); ?>
										</div>
										</div>
									</div>
								</div>
							</div>							
							
						</div><!--Basic info END-->
						<div class="eb_simpleContainer" id="facilitiesInfoDiv" style="display:none;width:900px">
							<h3>Facilities</h3>
								
							<div id="facilitiesContainer" style="height:300px;overflow:scroll" align="center">
							<table style="border:1px solid #dfdfdf;padding:2px;background-color:#f8f8f8;-webkit-border-radius: 5px;-moz-border-radius: 5px;border-radius: 5px;padding:2px;width:100%;" align="center" cellspacing="0">
							<?php 
							global $facilitiesTable_name;
							$facility_counter = 1;
							$facilities = $wpdb->get_results('select * from '.$facilitiesTable_name.' where facility_for = "Hotel"');
							foreach($facilities as $facility){
								if ($facility_counter == 1) echo '<tr>';
								echo '<td style="border:none">';
								_e('<div class="facility-ico-selector" style="" align="center">
								<input type="checkbox" style="border:none" name="facilitiesChBox[]" id="fclty_'.$facility->facility_id.'" value="'.$facility->facility_id.'"/>');
								echo '<label for = "fclty_'.$facility->facility_id.'">';
								if($facility->image!='')
								_e('<img src="'.WP_CONTENT_URL.'/plugins/'.$ebPluginFolderName.'/images/icons/'.$facility->image.'" title="'.$facility->facility_description.'">');
								else
								_e('<img src="'.WP_CONTENT_URL.'/plugins/'.$ebPluginFolderName.'/images/no_img_small.png" title="'.$facility->facility_description.'">');
								_e('<br>'.$facility->facility_name.'<label></div>');
								echo '</td>';
								//if ($facility_counter == 1) 
								if ($facility_counter == 7) {echo '</tr>';$facility_counter = 0;}
								$facility_counter++;
							}
							$defaultLang = $q_config["default_language"];
							?>
							</table>
							</div>						
							</div>
						<div class="eb_simpleContainer" id="contactInfoDiv" style="display:none;width:900px">
							<div style="padding:2px">
								<h3>Contact information</h3>
								<div style="border:1px solid #dfdfdf;padding:2px;background-color:#f8f8f8;-webkit-border-radius: 5px;-moz-border-radius: 5px;border-radius: 5px;">
								<div style="padding:2px">
									<strong>Business Email</strong>
									<div><input type="text" name="eb_email" id="eb_email" value="<?php echo $eb_BusinessEmail; ?>" /></div>
								</div>
								
								<div style="padding:2px">
									<strong>Business Telephone</strong>
									<div><input type="text" name="eb_tel1" id="eb_tel1" onkeypress="return numbersOnly(event)" value="<?php echo $eb_BusinessTel1; ?>" /></div>
								</div>
								
								<div style="padding:2px">
									<strong>Business Telephone (2)</strong>
									<div><input type="text" name="eb_tel2" id="eb_tel2" onkeypress="return numbersOnly(event)" value="<?php echo $eb_BusinessTel2; ?>" /></div>
								</div>
								
								<div style="padding:2px">
									<strong>Business fax</strong>
									<div><input type="text" name="eb_fax" id="eb_fax" onkeypress="return numbersOnly(event)" value="<?php echo $eb_BusinessFax; ?>" /></div>
								</div>
								</div>
							</div>
							
							<p>
							<div style="padding:2px">
								<div><h3>Location information</h3></div>
								
								<div style="border:1px solid #dfdfdf;padding:2px;background-color:#f8f8f8;-webkit-border-radius: 5px;-moz-border-radius: 5px;border-radius: 5px;">
									<p>
									<span><strong>Country</strong> 
										<?php if(!empty($q_config['enabled_languages']) && $user_info->user_level != 0) {?><span id="checkCountryTranslationBtnArea" <?php if ($eb_BusinessCountryID == '') echo 'style="display:none"';?> ><a class="littleEditBtns" onclick="showTranslateLocationForm('countries', 'eb_countries_select', 'editCountryArea', '<?php echo $langsStr; ?>', '<?php echo $langsFlagPath ?>', '<?php echo $langsFlag ?>', '<?php echo $defaultLang;?>');">Check Translation</a></span><?php }?>		
									</span>
									<div>
									<select name="eb_countries" id="eb_countries_select" style="color:#666;font-weight:bold;">
									<?php
									$countries = $wpdb->get_results('select CountryId, Country from '.$countriesTable);
									echo '<option style="font-style:italic" value="">Select country</option>';
									foreach($countries as $country){
										_e('<option value="'.$country->CountryId.'">'.$country->Country.'</option>');
									}
									?>
									</select>
									</div>
									<div id="editCountryArea" class="editLocationArea" style="display:none;"></div>								
									</p>
									<p>
									<span><strong>Region</strong>
										<?php if(!empty($q_config['enabled_languages']) && $user_info->user_level != 0) {?><span id="checkRegionTranslationBtnArea" <?php if ($eb_BusinessRegionID == '') echo 'style="display:none"';?> ><a class="littleEditBtns" onclick="showTranslateLocationForm('regions', 'eb_regions_select', 'editRegionArea', '<?php echo $langsStr; ?>', '<?php echo $langsFlagPath ?>', '<?php echo $langsFlag ?>', '<?php echo $defaultLang;?>');">Check Translation</a></span><?php }?>
									</span>
									<div id="regionContainer">
										
									</div><!--end of region container div-->
									<div id="editRegionArea" class="editLocationArea" style="display:none;"></div>
									</p>
									<p>
									<span><strong>City</strong>
										<?php if(!empty($q_config['enabled_languages']) && $user_info->user_level != 0) {?><span id="checkCityTranslationBtnArea" <?php if ($eb_BusinessCityID == '') echo 'style="display:none"';?> ><a class="littleEditBtns" onclick="showTranslateLocationForm('cities', 'eb_cities_select', 'editCityArea', '<?php echo $langsStr; ?>', '<?php echo $langsFlagPath ?>', '<?php echo $langsFlag ?>', '<?php echo $defaultLang;?>');">Check Translation</a></span><?php }?>
									</span>  <span id="addCityArea" style="display:none"><a class="littleEditBtns" onclick="addCity()">+Add a City</a></span>
									<div id="cityContainer">
										
									</div><!--end of region container div-->
									<div id="addNewCityContainer" style="display:none">
										<?php if(!empty($q_config['enabled_languages'])) {?>
										<div id="titlewrap">
										<div id="addCitiesMsgArea" class="updated" style="display:none"></div>
										<div id="addNewCityForm" class="addNewCityFormClass">
											<table class="widefat">
												<thead>
													<tr>
														<th colspan="2">Add a new City<span style="float:right"><a class="littleCloseBtns" onclick="closeTranslateLocationForm();" title="Close translation area">X</a></span></th>
													</tr>
												</thead>
												<tbody>
										<?php
											foreach($q_config['enabled_languages'] as $language) {
												echo "<tr><td><img alt=\"".$language."\" title=\"".$q_config['language_name'][$language]."\" src=\"".WP_CONTENT_URL.'/'.$q_config['flag_location'].$q_config['flag'][$language]."\" /></td>";
												_e('<td><input type="text" name="eb_newcity_'.$language.'" size="30" tabindex="1" id="eb_newcity_'.$language.'" /></td></tr>');
											}	//end foreach
											?>
												</tbody></table>
											</div>
											
											
											<?php echo '<div style="padding:5px;" align="right"><input type="button" class="button-primary" value="Insert City" onclick="insertNewCity(\''.$langsStr.'\', \''.$defaultLang.'\', \'city\')"></div>';
											}?>	
											</div>								
									</div>
									<div id="editCityArea" class="editLocationArea" style="display:none;">
									</div>
									</p>				
									
									
									<?php
										
										if(empty($q_config['enabled_languages'])) {
											?>
											<strong>Address</strong>
											<div id="eb_Address" style="border:1px solid #dfdfdf;background-color:#e9eced;-webkit-border-radius: 5px;-moz-border-radius: 5px;border-radius: 5px;">
											<div id="titlewrap">
											<input type="text" name="eb_address" size="30" tabindex="1" value="<?php _e( $eb_BusinessAddress ); ?>" id="eb_address" autocomplete="off" />
											</div>
											</div>		
											<?php	
										}
										else {
											echo '<input type="hidden" name="eb_isMultyLang" value = "true">';
											
											_e('<strong>Address</strong>');
											echo '<div id="eb_Address" style="border:1px solid #dfdfdf;background-color:#e9eced;-webkit-border-radius: 5px;-moz-border-radius: 5px;border-radius: 5px;">
													<div id="titlewrap">
													<table style="border:none">';
											foreach($q_config['enabled_languages'] as $language) {
												echo "<tr><td style='border:none'><img alt=\"".$language."\" title=\"".$q_config['language_name'][$language]."\" src=\"".WP_CONTENT_URL.'/'.$q_config['flag_location'].$q_config['flag'][$language]."\" /></td>";
												$eb_BusinessAddressTrans = getTextBetweenTags($eb_BusinessAddress, $language);
												if($eb_BusinessAddressTrans != '') _e('<td style="border:none"><input type="text" name="eb_address_'.$language.'" size="30" tabindex="1" value="'.$eb_BusinessAddressTrans.'" id="eb_address_'.$language.'" autocomplete="off" /></td></tr>');
												else _e('<td style="border:none"><input type="text" name="eb_address_'.$language.'" size="30" tabindex="1" value="'.$eb_BusinessAddress.'" id="eb_address_'.$language.'" autocomplete="off" /></td></tr>');
											}	//end foreach
											echo '</table></div>
											</div>';//end Address
										}//end else
									?>
									<!---->
											<br>
											<div>
											<div id="titlewrap">
											<strong>Address Number :  </strong><input type="text" name="eb_addressNum" size="30" tabindex="1" value="<?php echo $eb_BusinessAddressNumber; ?>" id="eb_addressNum"  style="width:70px;" />
											</div>
											</div>
											<br>
											<div>
											<div id="titlewrap">
											<strong>ZIP code :  </strong><input type="text" name="eb_zip" size="30" tabindex="1" value="<?php echo $eb_BusinessZip; ?>" id="eb_zip" autocomplete="off" style="width:70px;" />
											</div>											
											</div>
											<div id="titlewrap">
												<div>
													<strong>Google maps coordinates : </strong> <input type="text" name="eb_coordinates" size="30" tabindex="1" value="<?php echo $eb_BusinessCoordinates; ?>" id="eb_coordinates" autocomplete="off" style="width:200px;" />
												</div>
											</div>			
								</div>
							</div>
							</p>							
						</div>
							<div class="eb_simpleContainer" id="seasonsInfoDiv" style="display:none;width:900px">
							
							<h3>Set seasons for rates</h3>
							<div style="border:1px solid #dfdfdf;padding:2px;background-color:#f8f8f8;-webkit-border-radius: 5px;-moz-border-radius: 5px;border-radius: 5px;">
								<p>
									<table style="background-color:#e9eced;border:none;-webkit-border-radius: 5px;-moz-border-radius: 5px;border-radius: 5px;"><tr><td style="border:none">
										<table>
											<tr>
												<td style="border:none">
													<input type="radio" style="border:none;" name="eb_hasSeasons" onclick="hideSeasons()" id="eb_hasSeasonsN" value="NO" <?php if($eb_BusinessHasSeasons == "NO" || $eb_BusinessHasSeasons =='') echo 'checked'; ?>>
												</td>
												<td style="border:none"> 
													<label for="eb_hasSeasonsN">Same prices for all <br>operating period</label>
												</td>
											</tr>
										</table>
									</td>
									<td style="border:none">
										<table>  
											<tr>
												<td style="border:none">
													<input type="radio" style="border:none;" name="eb_hasSeasons" onclick="showSeasons()"  id="eb_hasSeasonsY" value="YES" <?php if($eb_BusinessHasSeasons == "YES") echo 'checked'; ?>>
												</td>
												<td style="border:none">									
									  				<label for="eb_hasSeasonsY">Set seasons for prices</label>
									  			</td>
									  		</tr>
									  	</table>
									</td></tr></table>
								</p>
								
								<div id="eb_operatingPeriodArea" style="<?php if($eb_BusinessHasSeasons == '' || $eb_BusinessHasSeasons =='NO' ) echo 'display:block;'; if($eb_BusinessHasSeasons =='YES' ) echo 'display:none;';?>">
								<p>
									<strong>Operating period</strong>
									<div style="border:1px solid #dfdfdf;padding:2px;background-color:#f8f8f8;-webkit-border-radius: 5px;-moz-border-radius: 5px;border-radius: 5px;">
										<p>
											<div id="titlewrap">
												<div> 
													Open : <select name="eb_operatingPeriodStart" id="eb_operatingPeriodStart"><option value="NOT_SET">Set date</option><?php eb_daysList($eb_BusinessOperatingPeriodStart);?></select>
													Close : <select name="eb_operatingPeriodEnd" id="eb_operatingPeriodEnd"><option value="NOT_SET">Set date</option><?php eb_daysList($eb_BusinessOperatingPeriodEnd);?></select>
												</div>
											</div>
										</p>
									</div>
								</p>
								</div>								
								<div id="seasonLoadingImg" style="width:99%;display:none" align="center"><img src='<?php echo WP_CONTENT_URL;?>/plugins/wp-easybooking/images/ajax-loader.gif'></div>
								<div id="eb_seasonsArea" style="<?php if($eb_BusinessHasSeasons == '' || $eb_BusinessHasSeasons =='NO' ) echo 'display:none;'; if($eb_BusinessHasSeasons =='YES' ) echo 'display:block;';?>">
								<p>
									<div id="titlewrap">
										<strong>Low season  </strong>
											<div style="border:1px solid #dfdfdf;padding:2px;background-color:#f8f8f8;-webkit-border-radius: 5px;-moz-border-radius: 5px;border-radius: 5px;" align="center"> 
												Start : <span id="eb_lowSeasonStartArea"><select name="eb_lowSeasonStart" id="eb_lowSeasonStart"><option value="NOT_SET">Set date</option><?php eb_daysList($eb_BusinessLowSeasonStart);?></select></span>
												End : <span id="eb_lowSeasonEndArea"><select name="eb_lowSeasonEnd" id="eb_lowSeasonEnd"><option value="NOT_SET">Set date</option><?php eb_daysList($eb_BusinessLowSeasonEnd);?></select></span>
											</div>
											<img src='<?php echo WP_CONTENT_URL;?>/plugins/wp-easybooking/images/ajax-loader.gif' style="display:none">
									</div>
								</p> 
								<p>
									<div id="titlewrap">
										<strong>Mid season  </strong>
											<div style="border:1px solid #dfdfdf;padding:2px;background-color:#f8f8f8;-webkit-border-radius: 5px;-moz-border-radius: 5px;border-radius: 5px;" align="center"> 
												Start : <span id="eb_midSeasonStartArea"><select name="eb_midSeasonStart" id="eb_midSeasonStart"><option value="NOT_SET">Set date</option><?php eb_daysList($eb_BusinessMidSeasonStart);?></select></span>
												End : <span id="eb_midSeasonEndArea"><select name="eb_midSeasonEnd" id="eb_midSeasonEnd"><option value="NOT_SET">Set date</option><?php eb_daysList($eb_BusinessMidSeasonEnd);?></select></span>
											</div>
											
									</div>
								</p> 
								<p>
									<div id="titlewrap">
										<strong>High season  </strong>
											<div style="border:1px solid #dfdfdf;padding:2px;background-color:#f8f8f8;-webkit-border-radius: 5px;-moz-border-radius: 5px;border-radius: 5px;" align="center"> 
												Start : <span id="eb_highSeasonStartArea"><select name="eb_highSeasonStart" id="eb_highSeasonStart"><option value="NOT_SET">Set date</option><?php eb_daysList($eb_BusinessHighSeasonStart);?></select></span>
												End : <span id="eb_highSeasonEndArea"><select name="eb_highSeasonEnd" id="eb_highSeasonEnd"><option value="NOT_SET">Set date</option><?php eb_daysList($eb_BusinessHighSeasonEnd);?></select></span>
											</div>
									</div>
								</p> 
								<p>
									<div id="titlewrap">
										<strong>Mid season following high</strong>
											<div style="border:1px solid #dfdfdf;padding:2px;background-color:#f8f8f8;-webkit-border-radius: 5px;-moz-border-radius: 5px;border-radius: 5px;" align="center"> 
												Start : <span id="eb_midSeasonStart2Area"><select name="eb_midSeasonStart2" id="eb_midSeasonStart2"><option value="NOT_SET">Set date</option><?php eb_daysList($eb_BusinessMidSeasonStart2);?></select></span>
												End : <span id="eb_midSeasonEnd2Area"><select name="eb_midSeasonEnd2" id="eb_midSeasonEnd2"><option value="NOT_SET">Set date</option><?php eb_daysList($eb_BusinessMidSeasonEnd2);?></select></span>
											</div>
									</div>
								</p>
								<p>
									<div id="titlewrap">
										<strong>Low season following high</strong>
											<div style="border:1px solid #dfdfdf;padding:2px;background-color:#f8f8f8;-webkit-border-radius: 5px;-moz-border-radius: 5px;border-radius: 5px;" align="center"> 
												Start : <span id="eb_lowSeasonStart2Area"><select name="eb_lowSeasonStart2" id="eb_lowSeasonStart2"><option value="NOT_SET">Set date</option><?php eb_daysList($eb_BusinessLowSeasonStart2);?></select></span>
												End : <span id="eb_lowSeasonEnd2Area"><select name="eb_lowSeasonEnd2" id="eb_lowSeasonEnd2"><option value="NOT_SET">Set date</option><?php eb_daysList($eb_BusinessLowSeasonEnd2);?></select></span>
											</div>
									</div>
								</p>
								</div>
							</div>
							<?php 
							function eb_daysList($selectedDate){ 
								$allDaysStr = '';
								$secondsperday=86400;
								
								$firstdayofyear=mktime(12,0,0,1,1,"2011");//<--To 2011 den exei 29 februariou ;)
								$lastdayofyear=mktime(12,0,0,12,31,"2011");

								$theday = $firstdayofyear;

								for($theday=$firstdayofyear; $theday<=$lastdayofyear; $theday+=$secondsperday) {
							   	$dayinfo=getdate($theday);
							   	echo '<option value= "'. date('Y-m-d',$theday).'" ';
							   	if(isset($selectedDate) && $selectedDate!='' && $selectedDate == date('Y-m-d',$theday)) echo 'selected';
							   	echo '>'. date('d M',$theday).'</option>';
								}
							}//end of function daysList
							?>
							
							</div>	
							<?php
							if(strtolower( $eb_BusinessType ) == 'hotel' || strtolower( $eb_BusinessType ) == 'apartments'){?>
							<div class="eb_simpleContainer" id="policiesInfoDiv" style="display:none;width:900px;">
							<h3>Check in policy</h3>
							<div style="border:1px solid #dfdfdf;padding:2px;background-color:#f8f8f8;-webkit-border-radius: 5px;-moz-border-radius: 5px;border-radius: 5px;">
								<p>
									<div id="titlewrap">
										<strong>Check in time  </strong>
											<div> 
												From : <select name="eb_checkInTimeFrom" id="eb_checkInTimeFrom"><option value="NOT_SET">Set time</option>
												<?php
												for($i = 0; $i <= 23; $i++){
													$s='';
													if($i<10) $s='0'.$i; else $s=$i;
													echo '<option value="'.$s.':00" ';
													if($s.':00' == $eb_BusinessCheckInFrom) echo 'selected';
													echo '>'.$s.':00</option>';	
												}
												?>
												</select>												

												To : <select name="eb_checkInTimeTo" id="eb_checkInTimeTo"><option value="NOT_SET">Set time</option>
												<?php
												for($i = 0; $i <= 23; $i++){
													$s='';
													if($i<10) $s='0'.$i; else $s=$i;
													echo '<option value="'.$s.':00" ';
													if($s.':00' == $eb_BusinessCheckInTo) echo 'selected';
													echo '>'.$s.':00</option>';	
												}
												?>
												</select>
											</div>
									</div>
								</p> 
								<p>
									<div id="titlewrap">
										<strong>Check out time  </strong>
											<div> 
												From : <select name="eb_checkOutTimeFrom" id="eb_checkInTimeFrom"><option value="NOT_SET">Set time</option>
												<?php
												for($i = 0; $i <= 23; $i++){
													$s='';
													if($i<10) $s='0'.$i; else $s=$i;
													echo '<option value="'.$s.':00" ';
													if($s.':00' == $eb_BusinessCheckOutFrom) echo 'selected';
													echo '>'.$s.':00</option>';	
												}
												?>
												</select>
												To : <select name="eb_checkOutTimeTo" id="eb_checkInTimeTo"><option value="NOT_SET">Set time</option>
												<?php
												for($i = 0; $i <= 23; $i++){
													$s='';
													if($i<10) $s='0'.$i; else $s=$i;
													echo '<option value="'.$s.':00" ';
													if($s.':00' == $eb_BusinessCheckOutTo) echo 'selected';
													echo '>'.$s.':00</option>';	
												}
												?>
												</select>
											</div>
									</div>
								</p> 
								<p>
									<div id="titlewrap">
										<strong>Late check out time  </strong> <i>(If available)</i>
											<div> 
												Time : <select name="eb_lateCheckoutTime" id="eb_lateCheckoutTime"><option value="NOT_SET">Set time</option>
												<?php
												for($i = 0; $i <= 23; $i++){
													$s='';
													if($i<10) $s='0'.$i; else $s=$i;
													echo '<option value="'.$s.':00" ';
													if($s.':00' == $eb_lateCheckoutTime) echo 'selected';
													echo '>'.$s.':00</option>';	
												}
												?>
												</select>
												
												Time room will be ready: <select name="eb_lateCheckoutReadyTime" id="eb_lateCheckoutReadyTime"><option value="NOT_SET">Set time</option>
												<?php
												for($i = 0; $i <= 23; $i++){
													$s='';
													if($i<10) $s='0'.$i; else $s=$i;
													echo '<option value="'.$s.':00" ';
													if($s.':00' == $eb_lateCheckoutReadyTime) echo 'selected';
													echo '>'.$s.':00</option>';	
												}
												?>
												</select>
																							
											</div>
									</div>
								</p> 
								<i>Please select by local time</i>
							</div>
							<!--<h3>Extra bed policy</h3>
							<div style="border:1px solid #dfdfdf;padding:2px;background-color:#f8f8f8;-webkit-border-radius: 5px;-moz-border-radius: 5px;border-radius: 5px;">
								<p>
									<div id="titlewrap">
										<strong>Price of each extra bed</strong><br />
										<input type="text" id="eb_extraBedPrice" name="eb_extraBedPrice" value="<?php //echo $eb_extraBedPrice; ?>" onkeypress="return priceNumbersOnly(event)" style="width:150px;text-align:right;" /> <b><?php //echo $eb_BusinessCurrency; ?></b>
										<br /><em>You can use the comma "," or dot "." as a decimal delimiter. No need for a thousands delimiter.</em>
									</div>
								</p>
							</div>-->
							<h3>Cancellation policy</h3>
							<div style="border:1px solid #dfdfdf;padding:2px;background-color:#f8f8f8;-webkit-border-radius: 5px;-moz-border-radius: 5px;border-radius: 5px;">
								
								<p>
								<?php
									if( !empty($eb_cancellationStr) ) $eb_cancellationStr = $eb_cancellationStr[0]; else $eb_cancellationStr = '';
									$mode = '';
									$charge = '';
									$daysLimit = '';
									$checked = false;
									if( $eb_cancellationStr != '' ){
										$checked = true; 
										$cancellationData = explode('::', $eb_cancellationStr);
										$mode = $cancellationData[0];
										$charge = $cancellationData[1];
										$daysLimit = $cancellationData[2];//016046518
									}
								?>
									<em>Please set the charges according to your cancellation policy</em><br /><br />
									<strong>Cancellation charge</strong><br />
									<input type="checkbox" name="cancellationCharge" value="YES" <?php if($checked) echo 'checked';?> style="margin-left:-400px;margin-right:-390px;" /> Cancelation charge : 
									<input type="text" name="cancellationChargePrice" id="cancellationChargePrice" onchange="jQuery('#cancellationPercentageCharge').val('');" <?php if($mode == "CASH") echo 'value="'.$charge.'"';?> onkeypress="return priceNumbersOnly(event)" style="width:80px;text-align:right;" /> <b><?php echo $eb_BusinessCurrency; ?></b>&nbsp;&nbsp; or&nbsp;&nbsp; 
									<input type="text" name="cancellationPercentageCharge" id="cancellationPercentageCharge" onchange="jQuery('#cancellationChargePrice').val('');" <?php if($mode == "PERSENTAGE") echo 'value="'.$charge.'"';?> onkeypress="return priceNumbersOnly(event)" style="width:50px;text-align:right;" /> <b>%</b> of total booking cost &nbsp;&nbsp;&nbsp;&nbsp; 
									<select name="chargedCancellationDaysLimit">
										<option value="5" <?php if ($daysLimit == 5) echo 'selected'?> >5 days</option>
										<option value="7" <?php if ($daysLimit == 7) echo 'selected'?> >7 days</option>
										<option value="10" <?php if ($daysLimit == 10) echo 'selected'?> >10 days</option>
										<option value="15" <?php if ($daysLimit == 15) echo 'selected'?> >15 days</option>
										<option value="20" <?php if ($daysLimit == 20) echo 'selected'?> >20 days</option>
										<option value="25" <?php if ($daysLimit == 25) echo 'selected'?> >25 days</option>
										<option value="30" <?php if ($daysLimit == 30) echo 'selected'?> >30 days</option>
										<option value="35" <?php if ($daysLimit == 35) echo 'selected'?> >35 days</option>
										<option value="40" <?php if ($daysLimit == 40) echo 'selected'?> >40 days</option>
										<option value="45" <?php if ($daysLimit == 45) echo 'selected'?> >45 days</option>
										<option value="50" <?php if ($daysLimit == 50) echo 'selected'?> >50 days</option>
										<option value="55" <?php if ($daysLimit == 55) echo 'selected'?> >55 days</option>
										<option value="60" <?php if ($daysLimit == 60) echo 'selected'?> >60 days</option>
										<option value="100" <?php if ($daysLimit == 100) echo 'selected'?> >any day</option>
									</select>
									before check in date
								</p>
								
								<p>
								<?php 
									if( !empty($eb_earlyCancellationStr) ) $eb_earlyCancellationStr = $eb_earlyCancellationStr[0]; else $eb_earlyCancellationStr = '';
									$mode = '';
									$charge = '';
									$daysLimit = '';
									$checked = false;
									if( $eb_earlyCancellationStr != '' ){
										$checked = true; 
										$earlyCancelationData = explode('::', $eb_earlyCancellationStr);
										$mode = $earlyCancelationData[0];
										$charge = $earlyCancelationData[1];
										$daysLimit = $earlyCancelationData[2];
									}

								?>
									<em>If you offer a lower charge for early cancellations</em><br /><br />
									<strong>Early cancellation charge</strong><br />
									<input type="checkbox" name="earlyCancellationCharge" value="YES" <?php if($checked) echo 'checked';?> style="margin-left:-400px;margin-right:-390px;" /> Cancelation charge : 
									<input type="text" name="earlyCancellationChargePrice" id="earlyCancellationChargePrice" onchange="jQuery('#earlyCancellationPercentageCharge').val('');" <?php if($mode == "CASH") echo 'value="'.$charge.'"';?> onkeypress="return priceNumbersOnly(event)" style="width:80px;text-align:right;" /> <b><?php echo $eb_BusinessCurrency; ?></b>&nbsp;&nbsp; or&nbsp;&nbsp; 
									<input type="text" name="earlyCancellationPercentageCharge" id="earlyCancellationPercentageCharge" onchange="jQuery('#earlyCancellationChargePrice').val('');" <?php if($mode == "PERSENTAGE") echo 'value="'.$charge.'"'?> onkeypress="return priceNumbersOnly(event)" style="width:50px;text-align:right;" /> <b>%</b> of total booking cost &nbsp;&nbsp;&nbsp;&nbsp; 
									<select name="chargedEarlyCancellationDaysLimit">										
										<option value="7" <?php if ($daysLimit == 7) echo 'selected'?> >7 days</option>
										<option value="10" <?php if ($daysLimit == 10) echo 'selected'?> >10 days</option>
										<option value="15" <?php if ($daysLimit == 15) echo 'selected'?> >15 days</option>
										<option value="20" <?php if ($daysLimit == 20) echo 'selected'?> >20 days</option>
										<option value="25" <?php if ($daysLimit == 25) echo 'selected'?> >25 days</option>
										<option value="30" <?php if ($daysLimit == 30) echo 'selected'?> >30 days</option>
										<option value="35" <?php if ($daysLimit == 35) echo 'selected'?> >35 days</option>
										<option value="40" <?php if ($daysLimit == 40) echo 'selected'?> >40 days</option>
										<option value="45" <?php if ($daysLimit == 45) echo 'selected'?> >45 days</option>
										<option value="50" <?php if ($daysLimit == 50) echo 'selected'?> >50 days</option>
										<option value="55" <?php if ($daysLimit == 55) echo 'selected'?> >55 days</option>
										<option value="60" <?php if ($daysLimit == 60) echo 'selected'?> >60 days</option>
										<option value="100" <?php if ($daysLimit == 100) echo 'selected'?> >any day</option>
									</select>
									before check in date
								</p>
								
								<p>
								<?php 
									if( !empty($eb_freeCancellationStr) ) $eb_freeCancellationStr = $eb_freeCancellationStr[0]; else $eb_freeCancellationStr = '';								
								?>
									<em>If you offer free cancellation please select it at the following options</em><br /><br />
									<strong>Free cancellation</strong><br />
									<input type="checkbox" name="freeCancellation" value="YES" style="margin-left:-400px;margin-right:-390px;" <?php if ($eb_freeCancellationStr != '') echo 'checked'?> /> Free cancellation 
									<select name="freeCancellationDaysLimit">										
										<option value="5" <?php if ($eb_freeCancellationStr == 5) echo 'selected'?> >5 days</option>
										<option value="7" <?php if ($eb_freeCancellationStr == 7) echo 'selected'?> >7 days</option>
										<option value="10" <?php if ($eb_freeCancellationStr == 10) echo 'selected'?> >10 days</option>
										<option value="15" <?php if ($eb_freeCancellationStr == 15) echo 'selected'?> >15 days</option>
										<option value="20" <?php if ($eb_freeCancellationStr == 20) echo 'selected'?> >20 days</option>
										<option value="25" <?php if ($eb_freeCancellationStr == 25) echo 'selected'?> >25 days</option>
										<option value="30" <?php if ($eb_freeCancellationStr == 30) echo 'selected'?> >30 days</option>
										<option value="35" <?php if ($eb_freeCancellationStr == 35) echo 'selected'?> >35 days</option>
										<option value="40" <?php if ($eb_freeCancellationStr == 40) echo 'selected'?> >40 days</option>
										<option value="45" <?php if ($eb_freeCancellationStr == 45) echo 'selected'?> >45 days</option>
										<option value="50" <?php if ($eb_freeCancellationStr == 50) echo 'selected'?> >50 days</option>
										<option value="55" <?php if ($eb_freeCancellationStr == 55) echo 'selected'?> >55 days</option>
										<option value="60" <?php if ($eb_freeCancellationStr == 60) echo 'selected'?> >60 days</option>
										<option value="100" <?php if ($eb_freeCancellationStr == 100) echo 'selected'?> >any day</option>
									</select>
									before check in date
								</p>
							</div>	
							
							<!--<h3>Allow users to edit their bookings</h3>
							<div style="border:1px solid #dfdfdf;padding:2px;background-color:#f8f8f8;-webkit-border-radius: 5px;-moz-border-radius: 5px;border-radius: 5px;">
								<strong>Date limit</strong> <em>Until how many days before the arrival will a guest be allowed to edit a booking</em>
								<p>
									Set the date limit: <select name="editBookingAllowedDatePeriod" id="editBookingAllowedDatePeriod">
										<option value="0" <?php if ($eb_editBookingAllowedDatePeriod == 0) echo 'selected'?> >Not allowed</option>										
										<option value="5" <?php if ($eb_editBookingAllowedDatePeriod == 5) echo 'selected'?> >5 days</option>
										<option value="7" <?php if ($eb_editBookingAllowedDatePeriod == 7) echo 'selected'?> >7 days</option>
										<option value="10" <?php if ($eb_editBookingAllowedDatePeriod == 10) echo 'selected'?> >10 days</option>
										<option value="15" <?php if ($eb_editBookingAllowedDatePeriod == 15) echo 'selected'?> >15 days</option>
										<option value="20" <?php if ($eb_editBookingAllowedDatePeriod == 20) echo 'selected'?> >20 days</option>
										<option value="30" <?php if ($eb_editBookingAllowedDatePeriod == 30) echo 'selected'?> >30 days</option>
										<option value="50" <?php if ($eb_editBookingAllowedDatePeriod == 50) echo 'selected'?> >50 days</option>
										<option value="60" <?php if ($eb_editBookingAllowedDatePeriod == 60) echo 'selected'?> >60 days</option>				
										<option value="1000" <?php if ($eb_editBookingAllowedDatePeriod == 1000) echo 'selected'?> >any day</option>																
									</select>
								</p>
								<!--START OF ALLOW EDIT BOOKING SUB AREA-->
								<!--<div id="displayEditBookingOptions" style="display:none;">
								<strong>Arrival and departure dates</strong> <em>Allow guests to change their arrival and departure dates</em>
								<p align="left">
									<table style="border:none;">
										<tr>
											<td style="border:none;">
												<span style="float:left;"><input type="radio" name="editBookingDates" id="disallow-editBookingDates" onclick="showRemoveDaysCost('hide')" <?php if ($eb_editBookingDates == "NO") echo 'checked'?> value="NO" /></span> 
												<span style="float:left;"> <label for="disallow-editBookingDates">No</label></span>
												<span style="clear:both;"></span>
											</td>
											<td style=" border:none;padding-left:10px;">
												<span style="float:left;"><input type="radio" name="editBookingDates" id="allow-editBookingDates" onclick="showRemoveDaysCost('show');" <?php if ($eb_editBookingDates == "YES") echo 'checked'?> value="YES" /></span> 
												<span style="float:left;"> <label for="allow-editBookingDates">Yes</label></span>
												<span style="clear:both;"></span>
											</td>
										</tr>
									</table>
									<?php $displayRemoveDayCost = "none"; if( $eb_editBookingDates == "YES" ) $displayRemoveDayCost = "block"; ?>
									<div id="removeDaysCost" style="padding-left:20px;display:<?php echo $displayRemoveDayCost; ?>">
										<strong>Cost of each day being removed from date range</strong>
										<p>
											<input type="text" name="editBookingRemoveDaysCostValue" style="width:80px;text-align:right;" value="<?php echo $eb_editBookingRemoveDaysCost; ?>" /> % of removed days cost.
										</p>
									</div>									
								</p>				
								
								<strong>Add rooms</strong> <em>Allow guests to add rooms to their booking</em>
								<p>
									<table style="border:none;">
										<tr>
											<td style="border:none;">
												<span style="float:left;"><input type="radio" name="editBookingAddRooms" id="disallow-editBookingAddRooms" <?php if ($eb_editBookingAddRooms == "NO") echo 'checked'?> value="NO" /></span> 
												<span style="float:left;"> <label for="disallow-editBookingAddRooms">No</label></span>
												<span style="clear:both;"></span>
											</td>
											<td style=" border:none;padding-left:10px;">
												<span style="float:left;"><input type="radio" name="editBookingAddRooms" id="allow-editBookingAddRooms" <?php if ($eb_editBookingAddRooms == "YES") echo 'checked'?> value="YES" /></span> 
												<span style="float:left;"> <label for="allow-editBookingAddRooms">Yes</label></span>
												<span style="clear:both;"></span>
											</td>
										</tr>
									</table>
								</p>
								
								<strong>Remove rooms</strong> <em>Allow guests to remove rooms from their booking with a cancellation charge </em>
								<p>
									<table style="border:none;">
										<tr>
											<td style="border:none;">
												<span style="float:left;"><input type="radio" name="editBookingRemoveRooms" onclick="showRemoveRoomsCost('hide')" id="disallow-editBookingRemoveRooms" <?php if ($eb_editBookingRemoveRooms == "NO") echo 'checked'?> value="NO" /></span> 
												<span style="float:left;"> <label for="disallow-editBookingRemoveRooms">No</label></span>
												<span style="clear:both;"></span>
											</td>
											<td style=" border:none;padding-left:10px;">
												<span style="float:left;"><input type="radio" name="editBookingRemoveRooms" onclick="showRemoveRoomsCost('show')" id="allow-editBookingRemoveRooms" <?php if ($eb_editBookingRemoveRooms == "YES") echo 'checked'?> value="YES" /></span> 
												<span style="float:left;"> <label for="allow-editBookingRemoveRooms">Yes</label></span>
												<span style="clear:both;"></span>
											</td>
										</tr>
									</table>
									<?php $displayRemoveRoomCost = "none"; if( $eb_editBookingRemoveRooms == "YES" ) $displayRemoveRoomCost = "block"; ?>
									<div id="removeRoomsCost" style="padding-left:20px;display:<?php echo $displayRemoveRoomCost; ?>">
										<strong>Cost of removing room</strong>
										<p>
											<input type="text" name="editBookingRemoveRoomsCostValue" style="width:80px;text-align:right;" value="<?php echo $eb_editBookingRemoveRoomsCost; ?>" /> % of removed rooms cost.
										</p>
									</div>
								</p>
								</div><!--END OF ALLOW EDIT BOOKING SUB AREA-->
							<!--</div>-->

							
							
							</div>
							<?php }?>
							<div class="eb_simpleContainer" id="paymentInfoDiv" style="display:none;width:900px;">
								<div><h3>Payment information</h3></div>
									<div style="border:1px solid #dfdfdf;padding:2px;background-color:#f8f8f8;-webkit-border-radius: 5px;-moz-border-radius: 5px;border-radius: 5px;">
										<p>
											<div id="titlewrap">
											<strong>Bank name  </strong><br><input type="text" name="eb_BankName" size="30" value="<?php echo $eb_BusinessBankName; ?>" id="eb_BankName" autocomplete="off" />
											</div>
											</p>
											<p>
											<div id="titlewrap">
											<strong>IBAN  </strong><br><input type="text" name="eb_IBAN" size="30" value="<?php echo $eb_BusinessIBAN; ?>" id="eb_IBAN" autocomplete="off" />
											</div>					
										</p>
										<p>
											<div id="titlewrap">
											<strong>Bank account  </strong><br><input type="text" name="eb_bankAccount" value="<?php echo $eb_BusinessBankAccount; ?>" id="eb_bankAccount" autocomplete="off" />
											</div>					
										</p>
										<p>
											<div id="titlewrap">
											<strong>Bank SWIFT  </strong><em>or BIC</em><br><input type="text" name="eb_bankSWIFT" value="<?php echo $eb_BusinessSWIFT; ?>" id="eb_bankSwift" autocomplete="off" />
											</div>					
										</p>
										
											<div style="background-color: #ddd;border-bottom:solid 0.5px #fff;line-height:1px;">&nbsp;</div>										
										
										<p>
											<div id="titlewrap">
											<strong>Paypal account  </strong><br><input type="text" name="eb_paypalAccount" value="<?php echo $eb_BusinessPaypalAccount; ?>" id="eb_paypalAccount" autocomplete="off" />
											</div>					
										</p>
										<!--<p>
											<div id="titlewrap" align="left">
											<span><strong><label for="eb_payAtReception">Allow payment at reception</label>  </strong></span>
											<span style="width:10px;"><input style="width:10px;border:none;" type="checkbox" name="eb_payAtReception" value="YES" id="eb_payAtReception" <?php if( $eb_payAtReception == "YES") echo "checked" ;?>  /></span>
											<em>Not recommended. Hotels will be charged even if guests do not arrive.</em>
											</div>					
										</p>-->
									</div>
									
								</div>
								
								<div id="debtInfoDiv" class="eb_simpleContainer" style="width:900px;display:none;">									
									<em>No report available.</em>
								</div>
							</td>
						</tr>					
					</table>
												
				</td>
			</tr>
		</tbody>
	
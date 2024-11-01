<?php
global $wpdb;
global $q_config;
global $eb_path;
global $eb_adminUrl;
global $ebPluginFolderName;
global $facilitiesTable_name;
global $table_prefix;

//********************************Delete Facility******************************************
if(isset($_REQUEST['rmFac']) && $_REQUEST['rmFac'] == "true" && isset($_REQUEST['fID']) && $_REQUEST['fID'] != ""){
	$facility = $wpdb->get_row('select * from '.$facilitiesTable_name .' where facility_id = '.$_REQUEST['fID']);
	$wpdb->query('delete from '.$facilitiesTable_name .' where facility_id = '.$_REQUEST['fID']);

	$imgPath = ABSPATH.'wp-content/plugins/'.$ebPluginFolderName.'/images';
	if(is_file($imgPath.'/icons/'.$facility->image))
		unlink($imgPath.'/icons/'.$facility->image);


}
					
//*****************************************************************************************
//********************************Update Facility******************************************

if(isset($_REQUEST['action']) && $_REQUEST['action'] == 'edit' && isset($_REQUEST['fID'])){
	//update only data content not image
	if(isset($_REQUEST['type']) && $_REQUEST['type'] == 'data'){

		if(empty($q_config['enabled_languages'])) {
			
			$wpdb->update( 
							$facilitiesTable_name, 
							array( 
								'facility_name' => $_POST['eb_facility_name'], 
								'facility_description' => $_POST['eb_facility_desc']
							), 
							array('facility_id' => $_REQUEST['fID']),
							array( 
								'%s', 
								'%s',
								'%s' 
							),
							array('%d') 
			);	
		}//end not multilingual
		else{
			$eb_newFcltVal = '';
			$eb_newFcltDesc = '';
			foreach($q_config['enabled_languages'] as $language) {
				if( $_POST['eb_facility_name_'.$language] != '' ){
					$eb_newFcltVal .= '<!--:'.$language.'-->'.$_POST['eb_facility_name_'.$language].'<!--:-->';
					$eb_newFcltDesc .= '<!--:'.$language.'-->'.$_POST['eb_facility_desc_'.$language].'<!--:-->';
				}
			}//end foreach

			if( $eb_newFcltVal != '' ){
			$wpdb->update( 
							$facilitiesTable_name, 
							array( 
								'facility_name' => $eb_newFcltVal, 
								'facility_description' => $eb_newFcltDesc
							), 
							array('facility_id' => $_REQUEST['fID']),
							array( 
								'%s', 
								'%s',
								'%s' 
							),
							array('%d') 
			);	
			}//end $eb_newFcltVal not empty
		}//end multilingual
	}
	$facility = $wpdb->get_row('select * from '.$facilitiesTable_name .' where facility_id = '.$_REQUEST['fID']);
	?>

	<div>
		
			<table class="widefat" style="width:99%">
				<thead>
					<tr>
						<th>Edit <i><a><?php _e($facility->facility_name); ?></a></i> facility</th>
					</tr>
					<tr>
						<td><i>The <a><?php _e($facility->facility_name); ?></a> facility can be used for <b><?php _e($facility->facility_for); ?></b> only and <u>can not be changed</u></i></td>
					</tr>
				</thead>
				<tbody>
				<tr><td style="border:none">
				<form id="editFacilitiesData" method="post" action="admin.php?page=<?php echo $_REQUEST['page'];?>&action=edit&type=data&fID=<?php echo $facility->facility_id; ?>">
				<table>
				<?php
					if(empty($q_config['enabled_languages'])) {
						echo '<tr>';	
							_e('<td style="border:none"><label>Change Name</label><br><input type= "text" name="facilityname" value="'.$facility->facility_name.'"></td>');
						echo '</tr>';
						
						echo '<tr>';	
							_e('<td style="border:none"><label>Change Description</label><br><textarea name="facilitydesc">'.__( $facility->facility_description ).'</textarea></td>');
						echo '</tr>';
					}
					else{
						echo '<tr>';
						$langCount = 0;
						foreach($q_config['enabled_languages'] as $language) {
							$string = getTextBetweenTags($facility->facility_name, $language);
							/*if($string != '')*/ echo '<td style="border:none"><img alt="'.$language.'" title="'.$q_config['language_name'][$language].'" src="'.WP_CONTENT_URL.'/'.$q_config['flag_location'].$q_config['flag'][$language].'" />&nbsp;Change Name<br><input type = "text" name="eb_facility_name_'.$language.'" value = "'.$string.'">';
							//else echo '<td style="border:none"><img alt="'.$language.'" title="'.$q_config['language_name'][$language].'" src="'.WP_CONTENT_URL.'/'.$q_config['flag_location'].$q_config['flag'][$language].'" />&nbsp;Change Name<br><input type = "text" name="eb_facility_name_'.$language.'" value = "'.$facility->facility_name.'">';
							echo '<br>';
							$stringd = getTextBetweenTags($facility->facility_description, $language);
							/*if($stringd != '') */echo '<img alt="'.$language.'" title="'.$q_config['language_name'][$language].'" src="'.WP_CONTENT_URL.'/'.$q_config['flag_location'].$q_config['flag'][$language].'" />&nbsp;Change Description<br><textarea name="eb_facility_desc_'.$language.'">'.$stringd.'</textarea></td>';
							//else echo '<br><img alt="'.$language.'" title="'.$q_config['language_name'][$language].'" src="'.WP_CONTENT_URL.'/'.$q_config['flag_location'].$q_config['flag'][$language].'" />&nbsp;Change Description<br><textarea name="eb_facility_desc_'.$language.'">'.$facility->facility_description.'</textarea></td>';

							$langCount ++;						
						}//end foreach lang	
						
					}
					echo '</tr>';
					echo '<tr><td colspan="'.$langCount.'" style="border:none"><input type="submit" value="Update facility\'s content"></td></tr>';
				?>
				</form>
				</table></td></tr>
				</tbody>
		</table>
		
		<!-----image table-->
		<table class="widefat" style="width:99%">
				<thead>
					<tr>
						<th colspan="2"><?php if($facility->image != '') echo 'Change'; else echo 'Add';?> the image of <i><a><?php _e($facility->facility_name); ?></a></i> facility</th>
					</tr>
					<tr><td colspan="2"><p id="result"></p></td></tr>
					<tr>
						<td id="f_imgHolder" align="center">
						<?php
							if($facility->image != ''){
								echo '<img src = "'.WP_CONTENT_URL.'/plugins/'.$ebPluginFolderName.'/images/icons/'.$facility->image.'" title="'.$facility->image.'">';
							}
						?>
						</td>
						<td align="left">
						<p id="f1_upload_process" style="display:none">Loading...<br/><img src="loader.gif" /></p>

						<form action="<?php echo WP_CONTENT_URL.'/plugins/'.$ebPluginFolderName; ?>/upload.php?target=facility&fID=<?php echo $facility->facility_id ?>&tablePref=<?php echo $table_prefix; ?>" method="post" enctype="multipart/form-data" target="upload_target" onsubmit="startUpload();" >
    						Select image: <input name="Filedata" type="file" />
          			<input type="submit" name="submitBtn" value="Upload" />
						</form>
 
						<iframe id="upload_target" name="upload_target" src="#" style="width:0;height:0;border:0px solid #fff;"></iframe>                 


						<script type="text/javascript" >
						function startUpload(){
    						document.getElementById('f1_upload_process').style.visibility = 'visible';
    						return true;
							}


						function stopUpload(resultStr){
							//alert (resultStr);
							var result = resultStr.split("|");
      					//alert (result[0]);
      					if (result[0] == 1){
         					document.getElementById('result').innerHTML =
          					'<span class="msg">The image was uploaded successfully!<\/span><br/><br/>';
          					jQuery("#f_imgHolder").html("<img src = '<?php echo WP_CONTENT_URL; ?>/plugins/<?php echo $ebPluginFolderName; ?>/images/icons/"+result[1]+"'>");
      					}
      					else if (result[0] == 1.1){
         					document.getElementById('result').innerHTML =
          					'<div id="message" class="updated"><p><strong>The image type is GIF. These image types may not be properly resized for your needs and may create errors in the layout.Please check the dimensions of the image you uploaded!</strong></p></div>';
          					jQuery("#f_imgHolder").html("<img src = '<?php echo WP_CONTENT_URL; ?>/plugins/<?php echo $ebPluginFolderName; ?>/images/icons/"+result[1]+"'>");
      					}
      					else if (result[0] == 2){
      						document.getElementById('result').innerHTML = '<div id="message" class="updated"><p><strong>This image already exists!<br>You can rename it or select a different image</strong></p></div>';
      					}
      					else if (result[0] == 3){
      						document.getElementById('result').innerHTML = '<div id="message" class="updated"><p><strong>This image type is not supported</strong></p></div>';
      					}
      					else if (result[0] == 4){
      						document.getElementById('result').innerHTML = '<div id="message" class="updated"><p><strong>The image size is to big.<br>Try resizing it or select a different image for this facility</strong></p></div>';
      					}
      					// if (result[0] != 1 && result[0] != 1.1 && result[0] != 2 && result[0] != 3 && result[0] != 4) {
      					else{
				         document.getElementById('result').innerHTML = 
           				'<span class="emsg"><div id="message" class="updated"><p><strong>There was an error during file upload!</strong></p></div><\/span><br/>';
      					}
      					document.getElementById('f1_upload_process').style.visibility = 'hidden';
      					return true;   
						}

				</script>
						</td>
					</tr>
					
				</thead>
				<tbody>
				
				</tbody>
		</table>	
	</div>
	<script type="text/javascript" >
		jQuery(document).ready(function () {
			jQuery("#facilitiesMainTable").hide('fast');
		});

		
	</script>
	<?php
}
else{
//*****************************Insert New Facility*****************************************					
if(isset($_REQUEST['action']) && $_REQUEST['action'] == 'add'){
	//first get business type
	$addFeature_ebType = '';
	$addFeature_hasErrors = true;
	if(isset($_POST['hotel_checkbox']) && isset($_POST['room_checkbox'])){
		//$addFeature_ebType = 'eb_facility_hna';
		$addFeature_ebType = 'Hotels and Rooms';
		$addFeature_hasErrors = false;
	}
	elseif(isset($_POST['hotel_checkbox'])){

		$addFeature_ebType = 'Hotel';
		$addFeature_hasErrors = false;	
	}
	elseif(isset($_POST['room_checkbox'])){
		$addFeature_ebType = 'Room';
		$addFeature_hasErrors = false;	
	}

	else{
		$addFeature_hasErrors = true;
	}
	if(!$addFeature_hasErrors){
		if(empty($q_config['enabled_languages'])) {
			
			$wpdb->insert( 
							$facilitiesTable_name, 
							array( 
								'facility_name' => $_POST['eb_facility_name'], 
								'facility_description' => $_POST['eb_facility_desc'], 
								'facility_for' => $addFeature_ebType
							), 
							array( 
								'%s', 
								'%s',
								'%s' 
							) 
			);	
		}//end not multilingual
		else{
			$eb_newFcltVal = '';
			$eb_newFcltDesc = '';
			foreach($q_config['enabled_languages'] as $language) {
				$eb_newFcltVal .= '<!--:'.$language.'-->'.$_POST['eb_facility_name_'.$language].'<!--:-->';
				$eb_newFcltDesc .= '<!--:'.$language.'-->'.$_POST['eb_facility_desc_'.$language].'<!--:-->';
			}//end foreach

			$wpdb->insert( 
							$facilitiesTable_name, 
							array( 
								'facility_name' => $eb_newFcltVal, 
								'facility_description' => $eb_newFcltDesc, 
								'facility_for' => $addFeature_ebType
							), 
							array( 
								'%s', 
								'%s',
								'%s' 
							) 
			);
		}//end multilingual
	}//end if has no errors
	
}//end of if action is add
//*****************************************************************************************

//*****************************************************************************************
?>
<table id="facilitiesMainTable" style="width:100%">
	<tr>
		<td>
		<form name="eb_newFclt_form" id="eb_newFclt_form" method="post" action="admin.php?page=facilities_menu&action=add">
			<table id="facilitiesAddTbl" class="widefat" style="width:99%">
				<thead>
					<tr>
						<th><a onclick="jQuery('#add-new-facility-body').toggle();"><font size="3"><strong>+</strong></font> Add new facility</a></th>
					</tr>
				</thead>
				<tbody id="add-new-facility-body" style="display:none;">
					<tr>
						<td>
						<!--<i>Select one of the folowing business types this facility refers to (you can select of course <u>hotels and appartments</u> if you wish):<br> </i>
							<input type="checkbox" name="hotel_checkbox" onclick="switchChecboxes('hotel_checkbox')"  id="hotel_checkbox" value="Hotel"><label for="hotel_checkbox"> Hotel</label> &nbsp;&nbsp;
 							<input type="checkbox" name="apts_checkbox"  onclick="switchChecboxes('apts_checkbox')" id="apts_checkbox" value="Appartments"> <label for="apts_checkbox">Appartments</label> &nbsp;&nbsp;
 							<input type="checkbox" name="car_checkbox"  onclick="switchChecboxes('car_checkbox')" id="car_checkbox" value="Car rental"> <label for="car_checkbox">Car rental </label>&nbsp;&nbsp;
 							<input type="checkbox" name="ship_checkbox"  onclick="switchChecboxes('ship_checkbox')" id="ship_checkbox" value="Shipping cruise"> <label for="ship_checkbox">Shipping cruise</label> &nbsp;&nbsp;
 							--> 			
 							<em>Please define if it is a new Hotel or Room facility: </em>				
 							<input type="checkbox" name="hotel_checkbox" onclick="switchChecboxes('hotel_checkbox')"  id="hotel_checkbox" value="Hotel"><label for="hotel_checkbox"> Hotel facility</label> &nbsp;&nbsp;
 							<input type="checkbox" name="room_checkbox"  onclick="switchChecboxes('room_checkbox')" id="room_checkbox" value="Room"> <label for="room_checkbox">Room facility</label> &nbsp;&nbsp;
 							<div id="fac-for-hotel-txt" style="display:none">Add a new <b>Hotel</b> facility</div>
 							<div id="fac-for-room-txt" style="display:none">Add a new <b>Room</b> facility</div>
						</td>
					</tr>
					<tr class="addFclty_tr" style="display:none">
						<td><table style="border:none"><tr>
						<?php
						
						if(empty($q_config['enabled_languages'])) {
							echo '<td style="border:none"><p><label>Name</label><br><input type="text" name="eb_facility_name"></p>
							<p><label>Description</label><br><textarea name="eb_facility_desc"></textarea></p>
							</td>';	
						}
						else{
							echo '<td style="border:1px solid #dfdfdf;background-color:#f0f0f0"><table style="border:none"><tr>';
							foreach($q_config['enabled_languages'] as $language) {
								echo '<td style="border:1px solid #dfdfdf;background-color:#f8f8f8;"><table style="border:none">
								<tr><td style="border:none"><img alt="'.$language.'" title="'.$q_config['language_name'][$language].'" src="'.WP_CONTENT_URL.'/'.$q_config['flag_location'].$q_config['flag'][$language].'" /> Name<br>
								<input type="text" name="eb_facility_name_'.$language.'"></td></tr>';
								
								echo '</tr><td style="border:none"><img alt="'.$language.'" title="'.$q_config['language_name'][$language].'" src="'.WP_CONTENT_URL.'/'.$q_config['flag_location'].$q_config['flag'][$language].'" /> Description<br>
								<textarea name="eb_facility_desc_'.$language.'"></textarea></td></tr></table></td>';
							}
							echo '</tr></table></td>';	
						}					
						?>
						</tr></table>
						</td>	
					</tr>
					<tr class="addFclty_tr" style="display:none">
						<td><input type="submit" value="Add new facility"></td>
					</tr>
					<tr  class="addFclty_tr" style="display:none">
						<td>
							<i style="font-size:10px;">At the moment you can not add an image for the new facility, but you will be able after creating it.</i>
						</td>
					</tr>
				</tbody>
			</table>
		</form>			
		</td>
	</tr>
	<tr>
		<td>
		<?php
	
				
				?>
				<table id="facilitiesListTbl" class="widefat" style="width:99%">
				<thead>
					<tr>
						<th></th>
						<th>Facility name</th>
						<th>Facility Description</th>
						<th>Business type</th>
						<th>Image</th>
					</tr>
				</thead>
				<tbody>
					<?php 
					//$facilities = $wpdb->get_results('select * from '.$facilitiesTable_name.' '.$getBusinessType. $orderBy.' '.$asc.' '.$limit);
					$whereType = '';
					if( isset( $_POST['ft'] ) && $_POST['ft'] != '' ){ 
						$whereType = addslashes( $_POST['ft'] );
						$whereType = ' where type='.addslashes($_POST['ft']);
					}
					$facilities = $wpdb->get_results('select * from '.$facilitiesTable_name.$whereType);
					$facilityCounter = 1;
					foreach($facilities as $facility){
						_e('<tr><td>'.$facilityCounter.'</td>');
							_e('<td><strong>');
								 _e(stripslashes($facility->facility_name));
								 //echo '';
								 ?>
								 </strong><div class="row-actions"><span class="edit"><a href="?page=<?php echo $_REQUEST['page']?>&action=edit&fID=<?php echo $facility->facility_id ?>" >Edit</a></span> | </span><span class="remove"><a class="submitdelete" href="admin.php?page=facilities_menu&rmFac=true&fID=<?php echo $facility->facility_id ?>">Remove</a></span></div>
								 <?php
							_e('</td>');
							_e('<td>');
								_e(stripslashes($facility->facility_description));
							_e('</td>');
							_e('<td>');
							if($facility->facility_for == "Hotels_Apartments") echo 'Hotels & Apartments';
							else	_e($facility->facility_for);
							_e('</td>');
							_e('<td align="center">');
								if($facility->image != '') echo '<img src = "'.WP_CONTENT_URL.'/plugins/'.$ebPluginFolderName.'/images/icons/'.$facility->image.'" title="'.$facility->image.'">';
								else echo '<img src = "'.WP_CONTENT_URL.'/plugins/'.$ebPluginFolderName.'/images/no_img_small.png" title="No image set for facility">';
								
							_e('</td>');
						_e('</tr>');
						//echo '<tr><td colspan="4"></td>
						//<td id="eb_facilities_'.$facility->facility_id.'" style="border:1px solid red;"></td>
						//</tr>';
						$facilityCounter++;
					}					
					?>
				</tbody>
				</table>
		</td>
	</tr>
</table>


<script type="text/javascript" >
function countChecked() {
  var n = jQuery("input:checked").length;
  if(n>0) jQuery(".addFclty_tr").show("fast");
  else {
  	jQuery('#fac-for-room-txt').hide('fast');
	jQuery('#fac-for-hotel-txt').hide('fast');
  	jQuery(".addFclty_tr").hide("fast");
  }
}
function switchChecboxes(cBoxId){
	if(cBoxId == "hotel_checkbox"){
  			jQuery('#room_checkbox').attr('checked', false);
  			jQuery('#fac-for-room-txt').hide('fast');
  			jQuery('#fac-for-hotel-txt').show('slow');
  	}
  	if(cBoxId == "room_checkbox"){
  			jQuery('#hotel_checkbox').attr('checked', false);  
  			jQuery('#fac-for-hotel-txt').hide('fast');
  			jQuery('#fac-for-room-txt').show('slow');			
  	}
  		/*if(cBoxId == "hotel_checkbox" || cBoxId == "room_checkbox"){
  				jQuery('#car_checkbox').attr('checked', false);
  				jQuery('#ship_checkbox').attr('checked', false);
  		}*/
  		/*if(cBoxId == "car_checkbox"){
  			jQuery('#hotel_checkbox').attr('checked', false);
  			jQuery('#apts_checkbox').attr('checked', false);
  			jQuery('#ship_checkbox').attr('checked', false);
  		}
  		if(cBoxId == "ship_checkbox"){
  			jQuery('#hotel_checkbox').attr('checked', false);
  			jQuery('#apts_checkbox').attr('checked', false);
  			jQuery('#car_checkbox').attr('checked', false);
  		}*/
} 

countChecked();
jQuery(":checkbox").click(countChecked);
</script>
<?php }//end of else action not edit?>
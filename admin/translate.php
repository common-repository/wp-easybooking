<h2>Translate booking messages</h2>
<em>Select which page you want to translate</em>
<?php
global $ebPluginFolderName;// <-- apaiteitai sta ajax calls sta paths
global $q_config;
			
	if( isset($_POST['filename']) && $_POST['filename'] != '' ){
		$fname = $_POST['filename'];
		$transData = '<?php
';	
		$transData .= str_replace( "\'", "'", $_POST['translationData']);
		$transData .= '
		?>';
		
		$transFile = ABSPATH.'wp-content/plugins/wp-easybooking/widgets/trans-vars/'.$fname.'.trans.php';
		if (file_exists($transFile)) {
			global $q_config;
    		$handle = fopen($transFile, "w");
			if ($handle) {
				 if( fwrite($handle, $transData) ) echo '<div class = "updated">Translation saved successfully </div>';
				 else echo '<div class = "error">So sorry!!<br />It seems there was an error while saving a translation file... <br />Please try again or contact your system administrator</div>';
			}
			fclose($handle);
		}
	}						
?>
<div>
<span><a onclick="displayArea('search_form_trans_area');" style="font-size:14px; font-weight:bold;">Search Form</a></span>
<span style="padding-left:10px;padding-right:10px;color:#ddd;">|</span>
<span><a onclick="displayArea('search_results_trans_area');" style="font-size:14px; font-weight:bold;">Search Results Page</a></span>
<span style="padding-left:10px;padding-right:10px;color:#ddd;">|</span>
<span><a onclick="displayArea('resort_trans_area');" style="font-size:14px; font-weight:bold;">Resort Page</a></span>
<span style="padding-left:10px;padding-right:10px;color:#ddd;">|</span>
<span><a onclick="displayArea('booking_trans_area');" style="font-size:14px; font-weight:bold;">Booking Page</a></span>
<span style="padding-left:10px;padding-right:10px;color:#ddd;">|</span>
<span><a onclick="displayArea('view_booking_trans_area');" style="font-size:14px; font-weight:bold;">View Booking Page</a></span>
</div>

<div id="search_form_trans_area" class="trans_area" style="display:none">
<div class="eb_simpleContainer" style="width:auto;margin-top:10px;">
<h2>Translate Search Form</h2><em>The search form widget</em>
<?php transFrontEnd('search_form', 'Search Form'); ?>
</div>
</div>

<div id="search_results_trans_area" class="trans_area" style="display:none">
<div class="eb_simpleContainer" style="width:auto;margin-top:10px;">
<h2>Translate Search Results page</h2><em>The page the user sees after pressing the search button of the search form and displays the resorts found</em>
<?php transFrontEnd('search_results', 'Search Results'); ?>
</div>
</div>

<div id="resort_trans_area" class="trans_area" style="display:none">
<div class="eb_simpleContainer" style="width:auto;margin-top:10px;">
<h2>Translate Resort Page</h2><em>The page where the user can view the resort info like rooms, policies etc. </em>
<?php transFrontEnd('resort', 'Resort'); ?>
</div>
</div>


<div id="booking_trans_area" class="trans_area" style="display:none">
<div class="eb_simpleContainer" style="width:auto;margin-top:10px;">
<h2>Translate Booking Page</h2><em>The final step of booking procedure</em>
<?php transFrontEnd('booking', 'Booking'); ?>
</div>
</div>


<div id="view_booking_trans_area" class="trans_area" style="display:none">
<div class="eb_simpleContainer" style="width:auto;margin-top:10px;">
<h2>Translate View Booking Page</h2><em>The page where the user can view his booking info at any time.</em>
<?php transFrontEnd('view_booking', 'View Booking'); ?>
</div>
</div>

<?php

function transFrontEnd($filename, $title){
	$transFile = ABSPATH.'wp-content/plugins/wp-easybooking/widgets/trans-vars/'.$filename.'.trans.php';
	if (file_exists($transFile)) {
		global $q_config;
    	$handle = fopen($transFile, "r");
		if ($handle) {
			$lineCounter = 0;
			?>
			<div style="height:400px;overflow-y: scroll;overflow-x: hidden;border:1px solid #ddd;">
			<?php
    		while (($buffer = fgets($handle, 4096)) !== false) {
    			if( strpos($buffer, '<?') === false && strpos($buffer, '?>') === false ){
    	    		$transVar = explode('=', $buffer);

					if( preg_match('#([a-zA-Z0-9]+)#is', $transVar[0]) ){
					echo '<input type= "hidden" id="vname_'.$filename.'_'.$lineCounter.'" value="'.$transVar[0].'">';
        			if( !empty($q_config['enabled_languages']) ) {
						foreach($q_config['enabled_languages'] as $language) {											
							echo '<div style="width:100%">';
							echo '<span style="padding:2px;"><img src = "'.WP_CONTENT_URL.'/'.$q_config['flag_location'].$q_config['flag'][$language].'" /></span>';
							echo '<span>'.getLanguageTitle($filename, $lineCounter, $buffer, $language).'</span>';
							echo '</div>';
						}
					}
					$lineCounter ++;	
					echo '<br><br>'; 
					}
					
				}				      
    		}
    		?>
    		</div>
    		<?php
    		if (!feof($handle)) {
      	  	echo "Error: unexpected fgets() fail\n";
    		}
    		fclose($handle);
    		if( $lineCounter > 0 ) {
    			?>
    			<input type="submit" onclick="translateFile('<?php echo $filename; ?>', <?php echo $lineCounter; ?>);" value="Save <?php echo $title; ?> Translations" style="margin-top:10px;"> 
    			<?php
    		}
		}
	} 
	else {
   	echo "The file $filename does not exist";
	}
}




	function getLanguageTitle($filename, $lineCounter, $str, $lang, $defaultLang = 'en'){
		$transStr = explode('<!--:'.$lang.'-->', $str);
   	$transStr = explode('<!--:-->', $transStr[1]);
   	$transStr = $transStr[0];
   	if( $transStr == '' && $defaultLang != '' ){
   		$transStr = explode('<!--:'.$defaultLang.'-->', $str);
   		$transStr = explode('<!--:-->', $transStr[1]);
   		$transStr = $transStr[0];
   	}
   	if( $transStr == '') $transStr = $str;
   	
		return '<input type="text" id="'.$filename.'_'.$lang.'_'.$lineCounter.'" value="'.$transStr.'" style="width:90%" />';
	}	
?>
<form action="admin.php?page=translate" method="post" id="submitTranslationForm">
	<input type="hidden" id="filename" name="filename" value="" />
	<input type="hidden" id="translationData" name="translationData" value="" />
</form>
<script type="text/javascript" >
function translateFile( filename, lineCounter ){
	var jTransStr = '';
	for(line = 0; line < lineCounter; line++){
		var varname = jQuery('#vname_'+filename+'_'+line).val();
		jTransStr += varname + " = '";
	<?php	

	if(!empty($q_config['enabled_languages'])) {
		foreach($q_config['enabled_languages'] as $language) {
			//$jTransVar += '<!--:'.$language.'-->';
			?>
			var langContent = jQuery('#'+filename+'_<?php echo $language; ?>_'+line).val();
			//langContent = escape(langContent);
			langContent = langContent.replace("'","&apos;","g");
			langContent = langContent.replace('"','&quot;',"g");
			jTransStr += '<!--:<?php echo $language; ?>-->'+langContent+'<!--:-->';
			<?php
		}
	}
	
	?>
	jTransStr += "';\n";
	}
	jQuery("#filename").val(filename);
	jQuery("#translationData").val(jTransStr);
	jQuery("#submitTranslationForm").submit();
}

function displayArea(area){
	jQuery('.trans_area').fadeOut('fast');
	jQuery('#'+area).fadeIn('slow');
}


</script>
<?php
global $current_user;
global $wpdb;
global $ebPluginFolderName;
$siteCurrency = get_option('eb_siteCurrency');
//===========================================================================
//=======================GET ALL DEALS=======================================
global $table_prefix;
$allDeals = $wpdb->get_results('select ID, post_title from '.$table_prefix.'posts where post_type = "eb_chargingDeal"');

if(sizeof($allDeals) == 0){
	$newDealData = array(
  	'post_author' =>  $current_user->ID,
  	'post_date' => gmdate("Y-m-d H:i:s"),
  	'post_date_gmt' => gmdate("Y-m-d H:i:s"),
  	'post_name' => 'DEFAULT',
  	'post_title' => 'DEFAULT',
  	'post_type' => "eb_chargingDeal"
	); 
	
	if ($newDealID = wp_insert_post( $newDealData, $wp_error )){	 				
		update_post_meta($newDealID,'eb_dealPersentCost', '0');
		update_post_meta($newDealID,'eb_defaultPackDeal', $newDealID);
	}

	$newDealData = array(
  	'post_author' =>  $current_user->ID,
  	'post_date' => gmdate("Y-m-d H:i:s"),
  	'post_date_gmt' => gmdate("Y-m-d H:i:s"),
  	'post_name' => 'BUSINESS-ONE',
  	'post_title' => 'BUSINESS-ONE',
  	'post_type' => "eb_chargingDeal"
	); 
	
	if ($newDealID = wp_insert_post( $newDealData, $wp_error )){	 				
		update_post_meta($newDealID,'eb_dealPersentCost', '0');		
	}
}

if(isset($_REQUEST['action'])){
	if($_REQUEST['action']== "updated") echo '<div class="updated">Deal updated successfully</div>';
	if($_REQUEST['action']== "added") echo '<div class="updated">New deal added successfully</div>';
}

if(isset($_REQUEST['sdef']) && $_REQUEST['sdef'] != ''){
	$setDefDeal = addslashes($_REQUEST['sdef']);
	if(is_numeric($setDefDeal)){
		$wpdb->query('delete from '.$table_prefix.'postmeta where meta_key = "eb_defaultPackDeal"');
		update_post_meta($setDefDeal,'eb_defaultPackDeal', $setDefDeal);	
	}	
}
//===========================================================================
//=======================END GET ALL DEALS===================================


//===========================================================================
//=============================EDIT DEAL=====================================
$dealNameExists = false;
if(isset($_REQUEST['action']) && $_REQUEST['action'] == 'edit' && $_REQUEST['did'] != ''){
	$did = $_REQUEST['did'];
	$editDealName = addslashes($_POST['editDealTitle']);
	$newDealPeriodCost = addslashes($_POST['editDealPeriodCost']);
	$newDealPeriodDuration = addslashes($_POST['editDealDuration']);
	$newDealPersentCost = addslashes($_POST['editDealPercentage']);
	$hasErrors = false;
	$errorMessage = '';
	$warningMessage ='';
	
	if($editDealName != ''){
		foreach($allDeals as $deal){
			
			if($editDealName == $deal->post_title && $did != $deal->ID)
				$dealNameExists = true;
		}
		if(!$dealNameExists){
			$dealValuesUpdates = array();
			$dealValuesUpdates['ID'] = $did;
			$dealValuesUpdates['post_author'] = $current_user->ID;
  			$dealValuesUpdates['post_name'] = $editDealName;
  			$dealValuesUpdates['post_title'] = $editDealName;
  			$dealValuesUpdates['post_modified_gmt'] = gmdate("Y-m-d H:i:s");
  	
			if (wp_update_post( $dealValuesUpdates )){
				//if($newDealPeriodDuration == 0 && $newDealPeriodCost == '' && $newDealPersentCost == 0) $warningMessage .= '<p><em>The new deal has been added successfully but with no charges. If you want to set charges to this deal press the edit button next to it</em></p>'; 
				if($newDealPeriodDuration!= '') update_post_meta($did,'eb_dealPeriodDuration', $newDealPeriodDuration);
				if($newDealPeriodCost!= '') update_post_meta($did,'eb_dealPeriodCost', $newDealPeriodCost);
				if($newDealPersentCost!= '') update_post_meta($did,'eb_dealPersentCost', $newDealPersentCost);
			}
			else {
				$hasErrors = true;
				$errorMessage .= '<p><em>This deal could not be updated. Please try again or contact your system developer</em></p>';
			}			
		}
		else{
			$hasErrors = true;
			$errorMessage .= '<p><em>The name you entered for this deal already exists. Please try again using a different name</em></p>';
		}
	}//end if !=''
	if($hasErrors) {
		echo '<div class="error">'.$errorMessage.'</div>';
	}else{
	?>
		<script type="text/javascript" >
			var url = "<?php echo get_admin_url() .'admin.php?page='.$_REQUEST['page'].'&action=updated';?>"; 
			window.location = url;
		</script>
	<?php	
	}	

}

//===========================================================================
//===========================END EDIT DEAL===================================


?>
<h2>Package Deals</h2>
<table class="widefat">
	<thead>
		<tr>
			<th colspan="7">
				<strong>Package Deals</strong> <i>(<?php echo sizeof($allDeals); ?> Packages)</i>
			</th>
		</tr>
		<tr>
			<th></th><th>Deal Title</th><th>Period Cost</th><th>Period Duration</th><th>Percentage per Booking </th><th title="Make it your default package deal">Default</th><th>Edit</th>
				
		</tr>
	</thead>
	<tbody>
		<?php
		$dealCounter = 1;
		foreach($allDeals as $deal){
						
			$periodDuration = get_post_meta($deal->ID,'eb_dealPeriodDuration');
			if(empty($periodDuration)) $periodDuration = 0; else $periodDuration = $periodDuration[0];
			$periodCost = get_post_meta($deal->ID,'eb_dealPeriodCost');	
			if(empty($periodCost)) $periodCost = 0; else $periodCost = $periodCost[0];
			$percent = get_post_meta($deal->ID,'eb_dealPersentCost');
			if(empty($percent)) $percent = 0; else $percent = $percent[0];
			echo '<form method="post" action="admin.php?page='.$_REQUEST['page'].'&action=edit&did='.$deal->ID.'">';
			echo '<tr>';
			echo '<td>'.$dealCounter.'</td>';
			echo '<td><input type="text" name="editDealTitle" value="'.$deal->post_title.'"></td>';
			//echo '<td><input type="text" name="editDealPeriodCost" value="'.$periodCost.'" style="width:50px;" onkeypress="return priceNumbersOnlyForDeals(event)"> '.$siteCurrency.'</td>';
			echo '<td><b>'.$periodCost.' '.$siteCurrency.'</b></td>';
			echo '<td>';			
				echo '<b>'.$periodDuration.'</b> months';
			echo '</td>';
			echo '<td>';
				echo '<b>'.$percent.'%</b>';
			echo '</td>';
			$defaultPackDeal = get_post_meta($deal->ID,'eb_defaultPackDeal');
			if(empty($defaultPackDeal)) $defaultPackDeal = ''; else $defaultPackDeal = $defaultPackDeal[0];
			$checkDefault = '';
			if($defaultPackDeal != '' && $defaultPackDeal == $deal->ID) $checkDefault = 'checked';
			echo '<td title="Make '.$deal->post_title.' your default deal. It will be used when a new business is created."><input type="checkbox" onclick="setDefaultDeal('.$deal->ID.')" '.$checkDefault.' /></td>';
			echo '<td><input type="submit" value="Save changes"></td>';
			echo '</tr>';
			echo '</form>';
			$dealCounter++;
		}
			
		?>
	</tbody>
</table>
<script type="text/javascript" >
function setDefaultDeal(dealID){
	document.location= "admin.php?page=busines_deals&sdef="+dealID;
};

function priceNumbersOnlyForDeals(evt){
 	var charCode = (evt.which) ? evt.which : event.keyCode;
 	if (charCode == 46 ) return true;//teleia
 	if (charCode == 44 ) return true;//comma
   if (charCode > 31 && (charCode < 48 || charCode > 57))
   	return false;

   return true;
}

</script>
<?php

 $hasErrors = false;
 $errorMessage = '';
$newBusinessData = array(
  'post_author' =>  $_POST['ebOwnerID'],
  'post_date' => $_POST['curTime'],
  'post_date_gmt' => gmdate("Y-m-d H:i:s"),
  'post_name' => $_POST['ebTitle'],
  'post_title' => $_POST['ebTitle'],
  'post_type' => $_POST['eb_type'],
  'post_content' => $_POST['content'],
  'post_status' => 'draft'
);  
echo '<script type="text/javascript" >jQuery("#message").html();</script>';
$newBusinessID = '';
if($_POST['ebTitle'] ==''){
	$hasErrors = true;
	$hasErrors = true;
	$errorMessage .= '<p><em>You have to enter a name to the business</em></p>';
}
//*****Check if business name exist already*****
$nameExistRes = $wpdb->get_row('select post_title from wp_posts where post_title = "'.$_POST['ebTitle'].'"');
if(!empty($nameExistRes)){
	$hasErrors = true;
	$errorMessage .= '<p><em>The name of the business you entered <font color="red">('.$_POST['ebTitle'].')</font> already exist. Please try using a different one</em></p>';
}
if($_POST['ebOwnerID'] ==''){
	$hasErrors = true;
	$errorMessage .= '<p><em>You have to enter an owner to the business</em></p>';
}
if($_POST['eb_email'] !=''){
	 if ( !is_email( $_POST['eb_email'] ) ){
		$hasErrors = true;
		$errorMessage .= '<p><em>The email you entered is not valid</em></p>';
	}
}

if(!$hasErrors){
	if ($newBusinessID = wp_insert_post( $newBusinessData, $wp_error )){
		$businessId = $newBusinessID;
		
		$defaultPackDeal = $wpdb->get_row('select meta_value from '.$table_prefix.'postmeta where meta_key = "eb_defaultPackDeal"');
		if($defaultPackDeal->meta_value!='') add_post_meta($newBusinessID, 'eb_packDeal', $defaultPackDeal->meta_value, true);
		
		$defaultPackDealTitle = $wpdb->get_row('select post_title from '.$table_prefix.'posts where id = '.$defaultPackDeal->meta_value);
		$dealDuration = $wpdb->get_row('select meta_value from '.$table_prefix.'postmeta where post_id = '.$defaultPackDeal->meta_value.' and meta_key = "eb_dealPeriodDuration"');
		
//PACK DEAL EXPLANATION FOR EMAIL	
		$packDealExplanationStr = '';
		if(!empty($dealDuration)){
			$dealDuration = $dealDuration->meta_value;
		}
		else $dealDuration = 0;
		$dealPeriodCost = $wpdb->get_row('select meta_value from '.$table_prefix.'postmeta where post_id = '.$defaultPackDeal->meta_value.' and meta_key = "eb_dealPeriodCost"');
		if(!empty($dealPeriodCost)){
			$dealPeriodCost = $dealPeriodCost->meta_value;
			if($dealDuration !='' && $dealDuration > 0 && $dealPeriodCost>0 )
				$packDealExplanationStr = $dealPeriodCost.' '.get_option('eb_siteCurrency').' each '.$dealDuration.' months';
		}
		else $dealPeriodCost = 0;
		$dealPercentage = $wpdb->get_row('select meta_value from '.$table_prefix.'postmeta where post_id = '.$defaultPackDeal->meta_value.' and meta_key = "eb_dealPersentCost"');
		if(!empty($dealPercentage)){
			$dealPercentage = $dealPercentage->meta_value;
			if($packDealExplanationStr != '') $packDealExplanationStr .= ' and ';
				$packDealExplanationStr .= $dealPercentage.'% per booking';		
		}
		else $dealPercentage = 0;
//PACK DEAL EXPLANATION END

		if($_POST['eb_currency']!= '') add_post_meta($newBusinessID, 'eb_currency', $_POST['eb_currency'], true);
				
		if(isset($_POST['eb_stars'])){
			add_post_meta($newBusinessID, 'eb_stars', $_POST['eb_stars'], true);
			$wpdb->query('insert into eb_bushelpvals(bID, stars) values('.$newBusinessID.', '.$_POST['eb_stars'].')');
		}
		
		if($_POST['eb_cities']!= '') add_post_meta($newBusinessID, 'eb_cityID', $_POST['eb_cities'], true);
		
		if(isset($_POST['eb_isMultyLang']) && $_POST['eb_isMultyLang'] == "true"){		
			$ebaddressStr = '';
			global $q_config;
			foreach($q_config['enabled_languages'] as $language) {
				if(isset($_POST['eb_address_'.$language])){
					$ebaddressStr .= '<!--:'.$language.'-->'.$_POST['eb_address_'.$language].'<!--:-->';  	
				}
				
			}
			add_post_meta($newBusinessID, 'eb_address', $ebaddressStr, true);
		}
		else{
			add_post_meta($newBusinessID, 'eb_address', $_POST['eb_address'], true);	
		}
		/*Mono sto update pleon ...
		$facilitiesStr = '';
		$facilities = $_POST['facilitiesChBox'];
		for($i=0; $i < sizeof($facilities); $i++){
			$facilitiesStr .= $facilities[$i].'|';
		}
		if($facilitiesStr != '')
			add_post_meta($newBusinessID, 'eb_facilities', $facilitiesStr, true);*/
	
		if($_POST['eb_email']!= '') add_post_meta($newBusinessID, 'eb_email', $_POST['eb_email'], true);
		if($_POST['eb_tel1']!= '') add_post_meta($newBusinessID, 'eb_tel1', $_POST['eb_tel1'], true);
			
		if($_POST['eb_tel2']!= ''){
			add_post_meta($newBusinessID, 'eb_tel2', $_POST['eb_tel2'], true);
		}
		if($_POST['eb_fax']!= ''){
			add_post_meta($newBusinessID, 'eb_fax', $_POST['eb_fax'], true);
		}

		if($_POST['eb_addressNum']!= ''){
			add_post_meta($newBusinessID, 'eb_addressNum', $_POST['eb_addressNum'], true);
		}
		
		if($_POST['eb_zip']!= ''){
			add_post_meta($newBusinessID, 'eb_zip', $_POST['eb_zip'], true);
		}
		
		if($_POST['eb_coordinates']!= ''){
			add_post_meta($newBusinessID, 'eb_coordinates', $_POST['eb_coordinates'], true);
		}
		/*Mono sto update pleon
		if($_POST['eb_IBAN']!= ''){
			add_post_meta($newBusinessID, 'eb_IBAN', $_POST['eb_IBAN'], true);
		}
		
		if($_POST['eb_bankAccount']!= ''){
			add_post_meta($newBusinessID, 'eb_bankAccount', $_POST['eb_bankAccount'], true);
		}
		
		if($_POST['eb_BankName']!= ''){
			add_post_meta($newBusinessID, 'eb_BankName', $_POST['eb_BankName'], true);
		}
		
		if($_POST['eb_bankAccount']!= ''){
			add_post_meta($newBusinessID, 'eb_paypalAccount', $_POST['eb_paypalAccount'], true);
		}*/
		$ownerData = get_userdata( $_POST['ebOwnerID'] );
		$newBusinessAddedSub = $_POST['ebTitle'].' has been added as a new business partner of '.get_bloginfo('name');
		$newBusinessAddedMsg = 'Congratulations Mr/Mrs '.$ownerData->last_name.' '. $ownerData->first_name.',<br /><br />';
		$newBusinessAddedMsg .= 'you are the owner of <b>'.$_POST['ebTitle'].'</b>, a business partner of <b>'.get_bloginfo('name').'</b><br />';
		$newBusinessAddedMsg .= '<br />Currently <i>'.$_POST['ebTitle'].'</i> is inactive, that means that it (its content and rooms) will not be accessible from any user at <i>'.get_bloginfo('name').'</i><br />You will be informed as soon as it becomes enabled!!<br />';
		$newBusinessAddedMsg .= '<br />For starters your package deal with <i>'.get_bloginfo('name').'</i> is <b>'.$defaultPackDealTitle->post_title.'</b>.<br />';
		$newBusinessAddedMsg .= 'According to that package deal you will be charged as follows: <br/>';
		$newBusinessAddedMsg .= '<b>'.$packDealExplanationStr.'</b>. <em>(This might be temporary if you have already agreed to another package deal)</em>.<br /><br />';
		
		$newBusinessAddedMsg .= ' <a href ="'.get_admin_url().'admin.php?page=business_control&bID='.$businessId.'">You can access your business by pressing on this link</a>';
		$newBusinessAddedMsg .= '<br /><br />Thank you for using <a href="'.get_bloginfo('url').'">'.get_bloginfo('name').'</a> as your bookings partner!';
		$newBusinessAddedMsg .= '<br />For further information don\'t hesitate to contact us at '.get_bloginfo('admin_email');
		
		add_filter('wp_mail_content_type',create_function('', 'return "text/html"; '));
		add_filter('wp_mail_from', create_function('', 'return "'.get_bloginfo('admin_email').'"; '));
		add_filter('wp_mail_from_name', create_function('', 'return "'.get_bloginfo('name').'"; '));
	
		wp_mail($ownerData->user_email, $newBusinessAddedSub, $newBusinessAddedMsg);
	}
	else echo 'NO_ID_ERROR';
	//}
	//else echo 'You have to inser the name of the business';
}
else{?>
	<div align="center">
	<div id="message" class="updated" style="width:700px" align="center">
		<strong><?php echo $errorMessage;?></strong>
	</div>
	</div>
	<?php
}


 
/*_e("type: [".$_POST['eb_type']."]");


global $wpdb;
$wpdb->insert( 
	'e_bkn_businesses', 
	array( 
		'business_name' => $_POST["eb_name"], 
		'business_owner_id' => 1,
		'business_type' => $_POST['eb_type'],
		'business_stars' => 4,
		'city' => 'Agios Nikolaos',
		'region' => 'Lasi8i' 
	), 
	array( 
		'%s', 
		'%d' 
	) 
);

echo $wpdb->insert_id;*/
?>

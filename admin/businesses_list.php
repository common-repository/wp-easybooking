<?php
global $eb_adminUrl;
$eb_page = addslashes($_REQUEST['page']);
$targetPage = '';
$editBusinessLink = '';
//=========================================================
//           CHECK USER LEVEL
//=========================================================
$noAdmin_whereStr = '';
$user_id = get_current_user_id();
$user_info = get_userdata( $user_id );

if($user_info->user_level == 0 && $user_info->user_level != 10) {
	$noAdmin_whereStr = 'and post_author = "'.$user_info->ID.'"';
	$targetPage = 'busines_menu';
	$editBusinessLink = 'business_list';
}
if($user_info->user_level == 10) {
	$targetPage = 'easy_booking_menu';
	$editBusinessLink = 'busines_menu';
}
//=========================================================
//=========================================================	
	
global $wpdb;
global $ebPluginFolderName;
global $table_prefix;

?>
<div class="information">
	<a id="infoDisp" onclick="jQuery('.infoContent').toggle('slow');" style="color:#f19c06;">
		<img  src="<?php echo WP_CONTENT_URL; ?>/plugins/<?php echo $ebPluginFolderName; ?>/images/infoImg.png"> So what do I do here?
	</a>
	<div class="infoContent" style="display:none;color:#666;font-size:14px;">
		<u>From here you can view basic info about your businesses and you also have access to every detail that has to do with them.</u>
		<p>At the top of the list you can see how many business are registered.</p>
		<p>First in the list you can see the name of each business, and by clicking on it you will be redirected to it's edit page, where you can see all the details and edit them if you need. Also by clicking on the link "Edit" you will be redirected at the same page. By clicking the "Remove" link you can remove this business from your link</p>
		<p>After that you can see the type of the business : Hotel, Apartments, Rental or Shipping Cruise</p>
		<p>The name of the owner is next. In this column you can also see the user name of the owner, and the email of the business.</p>
		<p>You can see the date the business was registered in the next column.</p>
		<p>The Package deal column shows the name of the package deal that is the deal that the business owner has made with the website owner. It can be changed by pressing on the name of the business</p>
		<p>In the content column you can see if the business has any Room Types, in case of hotel or apartments, vehicles in case of Rentals or Cruises in case of Shipping Cruises.<br />
		If there is any content in that business you can view it by pressing on the "View" button at this column. You can also insert some content by pressing on the "Add" button.</p>
		<p>If there is any booking Pending you can see it in the next column of the list, which also shows the total number of bookings made from this website.</p>
		<p>The last column shows the balance of the business. If the business owes to the website, the amount will be coloured with red, and by pressing the button "Balance details" you will be redirected to the "Billing history" tab of the business.</p>
		<p></p>
		<p>If the list is to long, you can select to view business by their type (eg. Hotels only) or if you have the Privileges by owner, from the selection boxes on top of the list.</p>
		<br /><div align="center" style="width:100%"><a style="color:#f19c06;" onclick="jQuery('.infoContent').hide('slow');">OK! Got it, hide info</a></div>
		<br /><br />
	</div>
</div>
<?php

if(isset($_REQUEST['setStat']) && $_REQUEST['setStat'] != "" && $_REQUEST['bID'] != ""){
	if($user_info->user_level == 0 && $user_info->user_level != 10) {
		echo '<div class="updated">You do not have Permissions to Publish your business. Please contact your administrator to complete this action</div>';
	}
	else{
			$businessId = $_REQUEST['bID'];
			if($_REQUEST['setStat'] == "Publish") $staAct = 'publish';
  			else $staAct = 'draft';
  			
			$eb_SetBusinessStatus = array();
  			$eb_SetBusinessStatus['ID'] = $businessId;  			
  			$eb_SetBusinessStatus['post_status'] = $staAct;
  			
  			if(wp_update_post( $eb_SetBusinessStatus )){
  				$businessStatusData = $wpdb->get_row('select post_author, post_title , post_status from '.$table_prefix.'posts where ID = '.$businessId. ' and post_parent=0');
				if(!empty($businessStatusData)){
						$ownerData = get_userdata( $businessStatusData->post_author );
						$eb_BusinessOwner = $ownerData->last_name.' '. $ownerData->first_name;

					if($_REQUEST['setStat'] == 'Publish'){
	  					$statusInformationMailSub = $businessStatusData->post_title.' is now Active for bookings at '.get_bloginfo('name');
	  					$statusInformationMailMsg = 'Hello Mr/Mrs '.$eb_BusinessOwner.',<br />';
	  					$statusInformationMailMsg .= '<br /><b>'.$businessStatusData->post_title.'</b> is now available for bookings!<br />
	  					You can start editing your business, adding rooms and controlling your bookings at <a href ="'.get_admin_url().'admin.php?page='.$editBusinessLink.'&bID='.$businessId.'">'.get_bloginfo('name').' <i>(Please press here to enter)</i></a>';
	  					$statusInformationMailMsg .= '<br /><br />Thank you for choosing <b>'.get_bloginfo('name').'</b><br />For any questions or instructions please contact us at '.get_bloginfo('admin_email');
  					}
					else{
	  					$statusInformationMailSub = $businessStatusData->post_title.' is not Active any more for bookings at '.get_bloginfo('name');
	  					$statusInformationMailMsg = 'Hello Mr/Mrs '.$eb_BusinessOwner.',<br />';
	  					$statusInformationMailMsg .= '<br /><b>'.$businessStatusData->post_title.'</b> is not available for bookings any more!<br />
	  					You can still enter at your administration area and edit your business and add rooms at <a href ="'.get_admin_url().'admin.php?page='.$editBusinessLink.'&bID='.$businessId.'">'.get_bloginfo('name').'</a> but they will not show up in room search from users';
	  					$statusInformationMailMsg .= '<br /><br />For any questions or instructions please contact us at '.get_bloginfo('admin_email');
  					}	
  					if($statusInformationMailSub != '' && $statusInformationMailMsg != ''){
  						add_filter('wp_mail_content_type',create_function('', 'return "text/html"; '));
				   	add_filter('wp_mail_from', create_function('', 'return "'.get_bloginfo('admin_email').'"; '));
						add_filter('wp_mail_from_name', create_function('', 'return "'.get_bloginfo('name').'"; '));
	
						wp_mail($ownerData->user_email, $statusInformationMailSub, $statusInformationMailMsg);	
  					}
  					echo '<div class = "updated"><b>'.$businessStatusData->post_title.'</b> is now set as <b>'.$businessStatusData->post_status.'</b><br />
  					An email has been sent to the owner <em>('.$eb_BusinessOwner.' - '.$ownerData->user_email.')</em> to inform about the new status of his business.</div>';  					
				}  				
		}
	}//END OF STATUS UPDATE
}//END OF IF ISSET STATUS UPDATE

if(isset($_REQUEST['del']) && $_REQUEST['del'] == "true" && $_REQUEST['bID'] != ""){
	
	
	//get all rooms of business
	$rooms_list = $wpdb->get_results("select ID, post_title from ".$table_prefix."posts where post_type='rooms' and post_parent =". $_REQUEST['bID']);
	foreach($rooms_list as $room){
		$delroomImages = get_post_meta($room->ID, "eb_logo");	
		if(!empty($delroomImages)) $delroomImages = $delroomImages[0]; else $delroomImages ='';							
		$delroomImages = explode("|", $delroomImages);		
		if(!empty($delroomImages)){
			for($i=0; $i < count($delroomImages); $i++){
				if ($delroomImages[$i]!=""){
					$imgPath = ABSPATH.'wp-content/plugins/'.$ebPluginFolderName.'/images/RoomImg';
					if(is_file($imgPath.'/thumbs/'.$delroomImages[$i])){
						unlink($imgPath.'/thumbs/'.$delroomImages[$i]);
					}
					else echo 'No such file '.$delroomImages[$i]. 'in thumbs<br>';
					
					if(is_file($imgPath.'/'.$delroomImages[$i])){
						unlink($imgPath.'/'.$delroomImages[$i]);	
					}
					else echo 'No such file '.$delroomImages[$i]. '<br>';	
				}
			}
		} 	
		$delroomImages = get_post_meta($room->ID, "eb_defaultLogo");
		if(!empty($delroomImages)){
			$imgPath = ABSPATH.'wp-content/plugins/'.$ebPluginFolderName.'/images/RoomImg';
			if(is_file($imgPath.'/thumbs/'.$delroomImages[0])){
				unlink($imgPath.'/thumbs/'.$delroomImages[0]);
				//echo 'Image '.$delroomImages[0].' deleted succesfully from thumbs<br>';
			}
			else echo 'No such file '.$delroomImages[0]. 'in thumbs<br>';
					
			if(is_file($imgPath.'/'.$delroomImages[0])){
				unlink($imgPath.'/'.$delroomImages[0]);	
				//echo 'Image '.$delroomImages[0].' deleted succesfully<br>';
			}
			else echo 'No such file '.$delroomImages[0]. '<br>';
		}
		wp_delete_post($room->ID);	
	}
	
	$blogo = get_post_meta($_REQUEST['bID'], "eb_logo");
	if(!empty($blogo))  $blogo = $blogo[0]; else $blogo ='';
		$blogo = explode("|", $blogo);
		for($i=0; $i < count($blogo); $i++){
			if ($blogo[$i]!=""){
				if(is_file(ABSPATH.'wp-content/plugins/'.$ebPluginFolderName.'/images/businessImg/'.$blogo[$i])){
					unlink(ABSPATH.'wp-content/plugins/'.$ebPluginFolderName.'/images/businessImg/'.$blogo[$i]);
				}
				if(is_file(ABSPATH.'wp-content/plugins/'.$ebPluginFolderName.'/images/businessImg/thumbs/'.$blogo[$i])){
					unlink(ABSPATH.'wp-content/plugins/'.$ebPluginFolderName.'/images/businessImg/thumbs/'.$blogo[$i]);
				}
			}
		}
	
	$blogo = get_post_meta($_REQUEST['bID'], "eb_defaultLogo");
	if(!empty($blogo)){
		if(is_file(ABSPATH.'wp-content/plugins/'.$ebPluginFolderName.'/images/businessImg/'.$blogo[0])){
			unlink(ABSPATH.'wp-content/plugins/'.$ebPluginFolderName.'/images/businessImg/'.$blogo[0]);
		}
		if(is_file(ABSPATH.'wp-content/plugins/'.$ebPluginFolderName.'/images/businessImg/thumbs/'.$blogo[0])){
			unlink(ABSPATH.'wp-content/plugins/'.$ebPluginFolderName.'/images/businessImg/thumbs/'.$blogo[0]);
		}
	}
		
	if(wp_delete_post($_REQUEST['bID']))
		echo '<div id="message" class="success"><p><strong>Business deleted successfully</strong></p></div>';
	else echo '<div id="message" class="error"><p><strong>The business you are trying to delete does not exist...</strong></p></div>';
}

$getBusinessType = '';
$type = '';
$orderBy = '';
$asc = 'desc';
$businessService = '';
$businessServicePlural = '';
//list specific types
if(!isset($_REQUEST['type']) || $_REQUEST['type'] == "" || $_REQUEST['type'] == "all") {
	//$getBusinessType = "";
	$getBusinessType = 'where (post_type = "Hotel" or post_type = "Apartments" or post_type = "Car rental" or post_type = "Shipping cruises")';
}
elseif($_REQUEST['type'] == 'Hotel'){
	$getBusinessType = 'where post_type = "Hotel"';
	$type = '&type=Hotel';
}
elseif($_REQUEST['type'] == 'Apartments'){
	$getBusinessType = 'where post_type = "Apartments"';
	$type = '&type=Apartments';
}
elseif($_REQUEST['type'] == 'Car rental'){
	$getBusinessType = 'where post_type = "Car rental"';
	$type = '&type=Car rentals';
}
elseif($_REQUEST['type'] == 'Shipping cruises'){
	$getBusinessType = 'where post_type = "Shipping cruises"';
	$type = '&type=Shipping cruises';
}
else{
	echo 'OK dude. It\'s about time to stop messing around with the code';
}
$own = '';
$whereOwner = '';
if(isset($_REQUEST['own']) && $_REQUEST['own']>= 1 && $user_info->user_level == 10){
	$own = '&own='.$_REQUEST['own'];
	$oID = addslashes($_REQUEST['own']);
	$whereOwner = ' AND post_author = '.$oID;
}

//check for selection options
if(!isset($_REQUEST['orderBy']) || $_REQUEST['orderBy'] == "" || $_REQUEST['orderBy'] == "n") {
	$orderBy = "post_title";
}
elseif($_REQUEST['orderBy'] == 't'){
	$orderBy = "post_type";
}
elseif($_REQUEST['orderBy'] == 'o'){
	$orderBy = "post_title";//a bit complicated for now so leaving it name...
}
elseif($_REQUEST['orderBy'] == 'd'){
	$orderBy = "post_date";//a bit complicated for now so leaving it name...
}
else{
	$orderBy = "post_title";
}

//Ascending or Descending
if(!isset($_REQUEST['desc']) || $_REQUEST['desc'] == "" || $_REQUEST['desc'] == "desc") {
	$asc = "desc";
}
else{
	$asc = "asc";
}


?>
<?php
$businesses_count = $wpdb->get_var("SELECT COUNT(*) FROM ".$table_prefix."posts ". $getBusinessType . ' '.$whereOwner.' ' .$noAdmin_whereStr);
$b_typeStr='';
if(isset($_REQUEST['type']) && $_REQUEST['type'] != "all") $b_typeStr=$_REQUEST['type'];
echo '<table class="widefat" style="width:99%"><thead><tr><th><span>There are <font size="4"><b><i>'.$businesses_count.'</i></b></font> '.$b_typeStr.' businesses</span>';
?>
<span> <font color="#666"><b>|</b></font> </span>
<span id="selectBusinessType">View
<select id="select_busType" onchange="fetchType()">
<option value="all" <?php if(!isset($_REQUEST['type']) || $_REQUEST['type'] == "all") echo 'selected';?> >All Businesses</option>
<option value="Hotel" <?php if($_REQUEST['type'] == "Hotel") echo 'selected';?> >Hotels only</option>
<option value="Apartments" <?php if($_REQUEST['type'] == "Apartments") echo 'selected';?> >Apartments only</option>
<!--<option value="Car rental" <?php if($_REQUEST['type'] == "Car rental") echo 'selected';?> >Car rentals only</option>
<option value="Shipping cruises" <?php if($_REQUEST['type'] == "Shipping cruises") echo 'selected';?> >Shipping cruises only</option>-->
</select>
</span>
<?php
if($user_info->user_level == 10) {
?>
<span style="padding-left:5px">
	that belong to 
	<select id="select_busOwner" onchange="fetchOwner()">
		<option value="">any owner</option>
		<?php
		$owner_ids = get_users('orderby=nicename&role=eb_businessman');
		foreach($owner_ids as $owner){
			$lastName = get_user_meta( $owner->ID, 'last_name', 'true' );
			$firstName = get_user_meta( $owner->ID, 'first_name', 'true' );
			echo '<option value="'.$owner->ID.'" ';
			if(isset($_REQUEST['own']) && $_REQUEST['own'] == $owner->ID) echo 'selected';
			echo '>'.$lastName.' '.$firstName.'</option>';
		}
		?>
	</select>
</span>
<?php	
}
?>
</th>
</tr></thead></table>

<div id="businessContainer">
<table class="widefat" style="width:99%">
<thead>
    <tr>
    	<th></th>
       <?php 
       	if(($orderBy == "post_title" && $asc == "desc") || $orderBy != "post_title") {
       		echo' <th class="manage-column column-username sortable desc"><a href="'.$eb_adminUrl.'?page='.$eb_page.$type.'&orderBy=n&desc=asc" title="Order businesses by their name" style="width:120px;"><span>Business name</span> <span class="sorting-indicator" style=""></span></a></th>';
       	}
       	else {
       		echo' <th class="manage-column column-username sorted asc"><a href="'.$eb_adminUrl.'?page='.$eb_page.$type.'&orderBy=n&desc=desc" title="Order businesses by their name in descending order"><span>Business name</span><span class="sorting-indicator"></span></a></th>';
       	}
       	
       	if(($orderBy == "post_type" && $asc == "desc") || $orderBy != "post_type") {
       		echo' <th class="manage-column column-username sortable desc"><a href="'.$eb_adminUrl.'?page='.$eb_page.$type.'&orderBy=t&desc=asc" title="Order businesses by their type" style="width:70px;"><span>Type</span> <span class="sorting-indicator"></span></a></th>';
       	}
       	else {
       		echo' <th class="manage-column column-username sorted asc"><a href="'.$eb_adminUrl.'?page='.$eb_page.$type.'&orderBy=t&desc=desc" title="Order businesses by their type in descending order"><span>Type</span> <span class="sorting-indicator"></span></a></th>';
       	}
       	?>

        <th>Owner</th>
        <?php
		   if(($orderBy == "post_date" && $asc == "desc") || $orderBy != "post_date") {
       		echo' <th class="manage-column column-username sortable desc"><a href="'.$eb_adminUrl.'?page='.$eb_page.'&orderBy=d&desc=asc" title="Order businesses by their registration date" style="width:90px;"><span>Reg. date</span> <span class="sorting-indicator"></span></a></th>';
       	}
       	else {
       		echo' <th class="manage-column column-username sorted asc"><a href="'.$eb_adminUrl.'?page='.$eb_page.'&orderBy=d&desc=desc" title="Order businesses by their registation date in descending order"><span>Reg. date</span> <span class="sorting-indicator"></span></a></th>';
       	}
		?>
        <th>Package Deal</th>
        <th>Content</th>
        <th>Bookings</th>
        <th>Balance</th>
    </tr>
</thead>
<tfoot>
	<tr>
		<th></th>
		 <?php 
       	if(($orderBy == "business_name" && $asc == "desc") || $orderBy != "business_name") {
       		echo' <th class="manage-column column-username sortable desc"><a href="'.$eb_adminUrl.'?page='.$eb_page.'&orderBy=n&desc=asc" title="Order businesses by their name" style="width:120px;"><span>Business name</span> <span class="sorting-indicator"></span></a></th>';
       	}
       	else {
       		echo' <th class="manage-column column-username sorted asc"><a href="'.$eb_adminUrl.'?page='.$eb_page.'&orderBy=n&desc=desc" title="Order businesses by their name in descending order"><span>Business name</span> <span class="sorting-indicator"></span></a></th>';
       	}
       	
       	if(($orderBy == "business_type" && $asc == "desc") || $orderBy != "business_type") {
       		echo' <th class="manage-column column-username sortable desc"><a href="'.$eb_adminUrl.'?page='.$eb_page.'&orderBy=t&desc=asc" title="Order businesses by their type"><span>Type</span> <span class="sorting-indicator"></span></a></th>';
       	}
       	else {
       		echo' <th class="manage-column column-username sorted asc"><a href="'.$eb_adminUrl.'?page='.$eb_page.'&orderBy=t&desc=desc" title="Order businesses by their type in descending order"><span>Type</span> <span class="sorting-indicator"></span></a></th>';
       	}
       	?>
		<th>Owner</th>
		<?php
		   if(($orderBy == "post_date" && $asc == "desc") || $orderBy != "post_date") {
       		echo' <th class="manage-column column-username sortable desc"><a href="'.$eb_adminUrl.'?page='.$eb_page.'&orderBy=d&desc=asc" title="Order businesses by their registration date"><span>Reg. date</span> <span class="sorting-indicator"></span></a></th>';
       	}
       	else {
       		echo' <th class="manage-column column-username sorted asc"><a href="'.$eb_adminUrl.'?page='.$eb_page.'&orderBy=d&desc=desc" title="Order businesses by their registation date in descending order"><span>Reg. date</span> <span class="sorting-indicator"></span></a></th>';
       	}
		?>
		<th>Package Deal</th>
		<th>Content</th>
		<th>Bookings</th>
		 <th>Balance</th>
	</tr>
</tfoot>
<tbody>
<?php
 include('pagination.class.php');
 global $eb_listLimit;
$orderByLink = '';
if($orderBy != "") {
	$orderByLink = '&orderBy='.$orderBy;
	$orderBy = ' order by '.$orderBy;
} 
$ascStr='';
if($asc != "") $ascStr = '&desc='.$asc;
$pagingUrl = $eb_adminUrl.'?page='.$eb_page.$type.$orderByLink.$ascStr.$own;
$items = $businesses_count;
if($items > 0) {
        $p = new pagination;
        $p->items($items);
        $p->limit($eb_listLimit); // Limit entries per page
        //$p->target("admin.php?page=list_record");
        $p->target($pagingUrl);
        //$p->currentPage($_GET[$p->paging]); // Gets and validates the current page
        $p->calculate(); // Calculates what to show
        $p->parameterName('paging');
        $p->adjacents(1); //No. of page away from the current page
 
        if(!isset($_GET['paging'])) {
            $p->page = 1;
        } else {
            $p->page = $_GET['paging'];
        }
 
        //Query for limit paging
        $limit = "LIMIT " . ($p->page - 1) * $p->limit  . ", " . $p->limit;
} else {
    //echo "No Record Found";
}

$businesses = $wpdb->get_results('select ID, post_author, post_title, post_type, post_date, post_status from '.$table_prefix.'posts '.$getBusinessType.' '.$whereOwner.' '.$noAdmin_whereStr. $orderBy.' '.$asc.' '.$limit);
if(!empty($businesses)){
	$busCount = $eb_listLimit*($p->page - 1) + 1;
	$businessIDsForBalanceReporter = '';
	foreach($businesses as $business){
		$owner = $wpdb->get_row('select ID, user_login, user_email from '.$table_prefix.'users where ID = '.$business->post_author);
		if(!empty($owner)){
			$bBookings = $wpdb->get_var("SELECT COUNT(bookingID) from eb_bookingdata where businessID =". $business->ID); 
			$businessIDsForBalanceReporter .= "|".$business->ID;
			$bBookingsPending = $wpdb->get_var("SELECT COUNT(bookingID) from eb_bookingdata where businessID =". $business->ID.' and booking_status = "Pending"');
						
			if(!empty($bBookings)){
				if($user_info->user_level == 0) $targetPage = 'business_list';
				else $targetPage = 'easy_booking_menu';
				$bookStr = '<strong style="color:#6fbf4d">'.$bBookingsPending.' bookings PENDING</strong><br /><strong>'.$bBookings.' bookings in total</strong><br />
					 <a class="littleEditBtns" href="'.$eb_adminUrl.'?page=bookings_menu&bID='.$business->ID.'" title = "View booking details ">Details</a>';

			}else{
				$bookStr = '<i>There are no bookings</i><br />';
				$scount = 0;
			}			
			
			$bEmail = get_post_meta($business->ID, "eb_email");
			if(!empty($bEmail))
			if($bEmail[0] != '') $bEmail = '('.$bEmail[0].')'; else $bEmail = '';
			else $bEmail = '';
			$eb_BusinessPackDealID = get_post_meta($business->ID, "eb_packDeal");
			$eb_BusinessPackDeal = get_post($eb_BusinessPackDealID[0]);
			$owner_lastName = get_user_meta( $owner->ID, 'last_name', 'true' );
			$owner_firstName = get_user_meta( $owner->ID, 'first_name', 'true' );
			$postDate = explode(' ', $business->post_date);
			
			if(!empty($eb_BusinessPackDeal->post_title)) $eb_BusinessPackDeal = $eb_BusinessPackDeal->post_title; else $eb_BusinessPackDeal ='';
			
			$publishMsg = '<font style="color:green">Publish</font>';
			$publishMsgLink = 'admin.php?page=busines_menu&bID='.$business->ID.'&statusAction=updateBusinessStatus&setStatus=publish';
			$publishMsgLinkTitle = $business->post_title.' is not Published and its content can not be viewed by users. Press here to make it visible';
			
			if($business->post_status == "publish") {
				$publishMsg = '<font style="color:red">Unpublish</font>';
				$publishMsgLink = 'admin.php?page=busines_menu&bID='.$business->ID.'&statusAction=updateBusinessStatus&setStatus=draft';
				$publishMsgLinkTitle = $business->post_title.' is Published and its contents can be viewed by users. Press here to hide it.';
			}
			 
			echo '<tr><td>'.$busCount.'</td>
			<td><a href="'.get_admin_url().'admin.php?page='.$editBusinessLink.'&bID='.$business->ID.'"><strong>'.$business->post_title.'</strong></a>
				<div class="row-actions" align="left">
				<span class="edit"><a href="'.get_admin_url().'admin.php?page='.$editBusinessLink.'&bID='.$business->ID.'">Edit</a> | </span>
				<span class="remove"><a class="submitdelete" onclick="removeBusiness('.$business->ID.', \''.$business->post_title.'\')">Remove</a></span>';
				if($user_info->user_level == 10) 
					echo '<br /><span class="remove"><a href="'.$publishMsgLink.'" class="littleEditBtns" title = "'.$publishMsgLinkTitle.'">'.$publishMsg.'</a></span>';
			
			echo '</div>
			</td>
			
			<td>'.$business->post_type.'</td>
			<td>'.$owner_lastName.' '.$owner_firstName.'<br />[<em>'.$owner->user_login.'</em>]<br />'.$bEmail.'</td>
			<td>'.$postDate[0].'</td>
			<td>'. $eb_BusinessPackDeal .'</td>';
			
			if( strtolower( $business->post_type ) == "hotel"){
				$businessService = 'Room type';
				$businessServicePlural = 'Room types';		
			}
			if( strtolower( $business->post_type ) == "apartments") {
				$businessService = 'Room';
				$businessServicePlural = 'Rooms';		
			}
			if( strtolower( $business->post_type ) == "car rental") {
				$businessService = 'Vehicle';
				$businessServicePlural = 'Vehicles';		
			}
			if(strtolower( $business->post_type ) == "shipping cruises") {
				$businessService = 'Shipping cruise';
				$businessServicePlural = 'Shipping cruises';		
			}
			$serviceStr = '';
			$service_count = $wpdb->get_var("SELECT COUNT(*) from ".$table_prefix."posts where post_type='rooms' and post_parent =". $business->ID);
			$targetPage = 'easy_booking_menu';
			if($user_info->user_level == 0) $targetPage = 'business_list';
			if(!empty($service_count)){
				$scount = $service_count;
				//if($current_user->wp_user_level == 0) $targetPage = 'business_list';
				//else $targetPage = 'easy_booking_menu';
				$serviceStr = '<strong>'.$scount.' '.$businessServicePlural.'</strong><br />
					<a class="littleEditBtns" href="'.$eb_adminUrl.'?page='.$targetPage.'&type=Hotel&bID='.$business->ID.'" title = "Add a '.$businessService.' to '.$business->post_title.'">Add a '.$businessService.'</a><span style="color:#ccc"> |</span> <a class="littleEditBtns" href="'.$eb_adminUrl.'?page='.$targetPage.'&type=Hotel&action=view&bID='.$business->ID.'" title = "View all '.$businessServicePlural.' of '.$business->post_title.'">View '.$businessServicePlural.'</a>';

			}else{
			$serviceStr = '<i>There are no '.$businessServicePlural.'</i><br />
			<a class="littleEditBtns" href="'.$eb_adminUrl.'?page='.$targetPage.'&type=Hotel&bID='.$business->ID.'" title = "Add a '.$businessService.' to '.$business->post_title.'">Add a '.$businessService.'</a>';
			$scount = 0;
			
			}
			//echo '<td><strong>'.$serviceStr.'</strong><br /><a href="'.$eb_adminUrl.'?page=easy_booking_menu&type=Hotel&bID='.$business->ID.'">Add</a> | <a href="'.$eb_adminUrl.'?page=easy_booking_menu&type=Hotel&action=view&bID='.$business->ID.'">View</a> Services ('.$scount.' exist)</td>
			echo '<td>'.$serviceStr.'</td>';
			echo '<td>'.$bookStr.'</td>';
			echo '<td><em>No report available</em></td>';
			echo '</tr>';
			$busCount++;
		}else echo 'There seems that business '.$business->post_title. ' exists with no owner. You might need to check it out';
	}
	echo '<input type= "hidden" id="businessIDsForBalanceReporter" value="'.$businessIDsForBalanceReporter.'">';
}

?>

</tbody>
</table>
<div id="paging_container">

<div class="wrap" align="center" style="text-align:center;float:left">
<div class="tablenav">
    <div class='tablenav-pages'>
        <?php if($businesses_count > 0) echo $p->show();  // Echo out the list of paging. ?>
    </div>
</div>

</div><!--end of paging_container-->

</div><!--end of business container-->







<script type="text/javascript" >
jQuery(".balanceReporterDiv").html('<img src="<?php echo WP_CONTENT_URL;?>/plugins/wp-easybooking/images/ajax-loader.gif" />');
jQuery(document).ready(function(){
	
});

function fetchType(){
	var b_type = jQuery("#select_busType").val();
	var url = "<?php echo $eb_adminUrl .'?page='.$eb_page?>&type="+b_type; 
	window.location = url;
}
function fetchOwner(){
	var b_owner = jQuery("#select_busOwner").val();
	var url = "<?php echo $eb_adminUrl .'?page='.$eb_page?>&type=<?php echo $_REQUEST['type']?>&own="+b_owner; 
	window.location = url;
}
function removeBusiness(bID, bName){
	var deleteConf = confirm('Are you sure you want to delete business "'+bName+'"');
	if (deleteConf){
		var urlQstr= document.URL.split( '?' );
		//var urldel = "<?php echo $eb_adminUrl .'?page='.$eb_page?>&action=delete&bID="+bID;
		var urldel = "<?php echo $eb_adminUrl .'?'?>"+urlQstr[1]+"&del=true&bID="+bID; 
		window.location = urldel;
	}
	
}
</script>

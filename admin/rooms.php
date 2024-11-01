<?php
global $ebPluginFolderName;// <-- apaiteitai sta ajax calls sta paths
global $eb_adminUrl;//<-importand to globalize it so you can use it
$eb_page = $_REQUEST['page'];
$eb_type = $_REQUEST['type'];
$eb_action = '';
if(isset($_REQUEST['action']))
	$eb_action = addslashes( $_REQUEST['action'] );
else $eb_action = '';

global $wpdb;
global $table_prefix;

	$roomId = '';
	
	$roomTitle = "";
	$roomNum = "";
	$peopleNum = 1;
	$babiesAllowed = 0;
	$childrenAllowed = 0;
	$extraBedsAvailable = 0;
	
	$lprice = "";
	$mprice = "";
	$hprice = "";
	$fprice = "";
	$HB_lprice = '';
	$HB_mprice = '';
	$HB_hprice = '';
	$HB_fprice = '';
	
	$RoomDesc = "";
	$eb_BusinessFacilities = "";
	
	$roomDefaultLogo = '';
	
	$form_action = "add";

//}	

	//=========================================================
	//           CHECK USER LEVEL
	//=========================================================
//global $current_user;
$user_id = get_current_user_id();
$user_info = get_userdata( $user_id );
$targetPage = 'easy_booking_menu';
$editBusinessLink = 'busines_menu';
$businessInBusinessmanList = false;
$noAdmin_whereStr = '';
if($user_info->user_level == 0) {
	$targetPage = 'business_list';
	$editBusinessLink = 'business_list';
	$noAdmin_whereStr = 'and post_author = "'.$user_info->ID.'"';
	if(isset($_REQUEST['rID']))
	$rID = $_REQUEST['rID'];
	if($rID == '') $rID = $_POST['rID'];
	if($rID == '') $rID = $_GET['rID'];
	if(isset($_REQUEST['rmroom']) && ($_REQUEST['rmroom'] != '' && $_REQUEST['rmroom'] != 'conf')) $rID = $_REQUEST['rmroom'];  
	if(isset($_REQUEST['bID'])){
		$businesses = $wpdb->get_results('select ID from '.$table_prefix.'posts where post_author ='.$user_info->ID);
		foreach ($businesses as $business){
			if($rID == $business->ID) {
				$businessInBusinessmanList = true;
			}
		}//end of foreach
		if(!$businessInBusinessmanList && $rID != '') die('<div class="error"><b>Not a valid room.</b> <br><i>Please go back and try again. For further instructions please contact your business administrator</i></div>');
	}
}
	//=========================================================
	//=========================================================	


if (isset($_REQUEST['action']) && $_REQUEST['action'] == "add"){
	include('rooms_insert.php');	
}
if($eb_action == "edit"){
	include('rooms_update.php');	
	$form_action = "edit&rID=".$roomId;
}


if((isset($_REQUEST['rID']) && $_REQUEST['rID'] != '') || $roomId !=''){
	$form_action = "edit";
if($roomId == '')	$roomId = $_REQUEST['rID'];
	$sroom = $wpdb->get_row('select ID, post_author, post_title, post_type, post_content from '.$table_prefix.'posts where ID ='.$roomId);

	if(!empty($sroom)){
		
		
		//================================================================
		//		LOGO THE IMAGE 	
		//================================================================
		if (isset($_REQUEST['logoimg'])){
			update_post_meta($roomId, 'eb_defaultLogo', $_REQUEST['logoimg']);
			echo '<div style="width:100%" align = "center"><div class="updated" align = "center"><strong>A new logo image has been set succesfully for room <em>'.__($sroom->post_title).'</em></strong></div></div>';			
		}			
		
		//================================================================
		//		DELETE IMAGE 	
		//================================================================
		if (isset($_REQUEST['delimg'])){
		$imgPath = ABSPATH.'wp-content/plugins/'.$ebPluginFolderName.'/images/RoomImg';
		if(is_file($imgPath.'/thumbs/'.$_REQUEST['delimg']))
		unlink($imgPath.'/thumbs/'.$_REQUEST['delimg']);
		if(is_file($imgPath.'/'.$_REQUEST['delimg']))
		unlink($imgPath.'/'.$_REQUEST['delimg']);

		$roomImages = get_post_meta($roomId, "eb_logo");	
		if(!empty($roomImages)) $roomImages = $roomImages[0]; else $roomImages ='';							
		$roomLogo = explode("|", $roomImages);
		
		$deletedImgStr = '';
		for($i=0; $i < count($roomLogo); $i++){
			if($roomLogo[$i] != $_REQUEST['delimg'])
				$deletedImgStr .= '|'.$roomLogo[$i];
		}		
		update_post_meta($roomId, 'eb_logo', $deletedImgStr);
		
		$businessDefLogo = get_post_meta($roomId, "eb_defaultLogo");
		if(!empty($businessDefLogo) && $businessDefLogo[0] == $_REQUEST['delimg']) update_post_meta($roomId, 'eb_defaultLogo', '');
	}
		
		
		$roomTitle = $sroom->post_title;
		$RoomDesc = $sroom->post_content;
		
		$roomNum = get_post_meta($sroom->ID, "eb_roomNum");
		if(!empty($roomNum)) $roomNum = $roomNum[0]; else $roomNum ='';					
		$peopleNum = get_post_meta($sroom->ID, "eb_peopleNum");
		if(!empty($peopleNum)) $peopleNum = $peopleNum[0]; else $peopleNum ='';
		$babiesAllowed = get_post_meta($sroom->ID, "eb_babiesAllowed");
		if(!empty($babiesAllowed)) $babiesAllowed = $babiesAllowed[0]; else $babiesAllowed = 0;
		$childrenAllowed = get_post_meta($sroom->ID, "eb_childrenAllowed");
		if(!empty($childrenAllowed)) $childrenAllowed = $childrenAllowed[0]; else $childrenAllowed = 0;
		$extraBedsAvailable = get_post_meta($sroom->ID, "eb_extraBedsAvailable");
		if(!empty($extraBedsAvailable)) $extraBedsAvailable = $extraBedsAvailable[0]; else $extraBedsAvailable = 0;

		
		$hasBBandHB = get_post_meta($sroom->ID, "eb_hasBBandHB");
		if(!empty($hasBBandHB)) $hasBBandHB = $hasBBandHB[0]; else $hasBBandHB ='NO';
		if($hasBBandHB == "YES"){		
			$HB_lprice = get_post_meta($sroom->ID, "HB_eb_lprice");
			if(!empty($HB_lprice)) $HB_lprice = $HB_lprice[0]; else $HB_lprice ='';
			$HB_mprice = get_post_meta($sroom->ID, "HB_eb_mprice");
			if(!empty($HB_mprice)) $HB_mprice = $HB_mprice[0]; else $HB_mprice ='';
			$HB_hprice = get_post_meta($sroom->ID, "HB_eb_hprice");
			if(!empty($HB_hprice)) $HB_hprice = $HB_hprice[0]; else $HB_hprice ='';
			$HB_fprice = get_post_meta($sroom->ID, "HB_eb_fprice");
			if(!empty($HB_fprice)) $HB_fprice = $HB_fprice[0]; else $HB_fprice ='';
		}				
		$lprice = get_post_meta($sroom->ID, "eb_lprice");
		if(!empty($lprice)) $lprice = $lprice[0]; else $lprice ='';					
		$mprice = get_post_meta($sroom->ID, "eb_mprice");
		if(!empty($mprice)) $mprice = $mprice[0]; else $mprice ='';					
		$hprice = get_post_meta($sroom->ID, "eb_hprice");
		if(!empty($hprice)) $hprice = $hprice[0]; else $hprice ='';					
		$fprice = get_post_meta($sroom->ID, "eb_fprice");
		if(!empty($fprice)) $fprice = $fprice[0]; else $fprice ='';	
						
		$eb_BusinessFacilities = get_post_meta($sroom->ID, "eb_room_facilities");
		if(!empty($eb_BusinessFacilities)) $eb_BusinessFacilities = $eb_BusinessFacilities[0]; else $eb_BusinessFacilities ='';				
		$facilities = explode("|",$eb_BusinessFacilities);
		
		$roomImages = get_post_meta($sroom->ID, "eb_logo");	
		if(!empty($roomImages)) $roomImages = $roomImages[0]; else $roomImages ='';							
		$roomLogo = explode("|", $roomImages);
		
		$roomDefaultLogo  = get_post_meta($sroom->ID, "eb_defaultLogo");	
		if(!empty($roomDefaultLogo)) $roomDefaultLogo = $roomDefaultLogo[0]; else $roomDefaultLogo ='';	
	}

}

if (isset($_REQUEST['rmroom']) && $_REQUEST['rmroom'] == "conf"){
	?>
	<div align="center" id="rmRoom_confMsg">
	<div class="error" align="center" style="width:70%">
		<p><i style="color:#999;">You have been redirected here to view the content of room <b><?php _e($roomTitle);?></b> and confirm deletion</i></p>
		<p style="font-size:14px;font-weight:bolder;color:#666;">Are you sure you want to delete room <em style="font-size:16px"><?php _e($roomTitle);?></em> and it's images?</p> 
		<p>Since deleting this room there is no way of getting it's content or images back.</p>
		<p><a href="<?php echo $eb_adminUrl; ?>?page=<?php echo $targetPage; ?>&type=Hotel&action=view&bID=<?php echo $_REQUEST['bID'];?>&rmroom=<?php _e($roomId);?>" class="littleRoomDelBtnsTrans" title="Delete room <?php _e($roomTitle);?>"> &nbsp;Delete&nbsp;</a> &nbsp; <a onclick="cancel_rmRoom()" class="littleRoomDelBtnsTrans" title="Cancel deletion">&nbsp;Cancel&nbsp;</a></p>
	</div>
	</div>
	<script type="text/javascript" >
		function cancel_rmRoom(){
			jQuery("#rmRoom_confMsg").hide("slow");
		}
	</script>
<?php
}
	$business = $wpdb->get_row('select ID, post_author, post_title, post_type from '.$table_prefix.'posts where ID=' . $_REQUEST['bID']);
	
	if(!empty($business)){	
	$currency = get_post_meta( $business->ID, 'eb_currency', 'true' );
	$eb_BusinessHasSeasons = get_post_meta($business->ID, "eb_hasSeasons");
	$eb_BusinessHasSeasons = $eb_BusinessHasSeasons[0];
	$eb_BusinessOwnerID = $business->post_author;
	
	?>
	<div id="availabilitySect" class="monthsAvailPalette" style="left:160px;"></div>
	<div class="information">
	<a id="infoDisp" onclick="jQuery('.infoContent').toggle('slow');" style="color:#f19c06;">
		<img  src="<?php echo WP_CONTENT_URL; ?>/plugins/<?php echo $ebPluginFolderName; ?>/images/infoImg.png"> So what do I do here?
	</a>
	<div class="infoContent" style="display:none;color:#666;font-size:14px;">
		<u>Here you can edit and create your package deals.</u><br />
		A <b>Package Deal</b> is the deal you can make with a business so it can use your website. In other words it is the method you charge the business.<br />
		<br />There are two ways of charging a business<br />
		<div style="padding-left:25px">1) By a certain amount of money in a certain amount of months/ period (i.e 100 USD per 12 months), or </div>   
		<div style="padding-left:25px">2) By a certain percentage of each booking made (i.e 15% of each booking has to be paid to you each month)</div>
		You can also use a combination of both charging methods mentioned above.<br />
		<br />
		<hr style="color:#f19c06;background-color:#f19c06;border-color:#f19c06;border:none;height:1px;">
		<b>Creating a Package Deal</b><br />
		If you want to create a new Package deal press on the "Create new Package Deal" link (or the "Show" button that is beneath it).<br />
		First you have to enter a title for the package deal (i.e Hotel One Pack).<br />
		If you want a periodical charge fill in the amount and the number of months. If you prefer a percentage per booking, just select a percentage. If you want you can do both.
		<br />
		<hr style="color:#f19c06;background-color:#f19c06;border-color:#f19c06;border:none;height:1px;">
		<b>Edit a Package Deal</b><br />
		Since you create a new package deal there is no ability to change anything but the Title. This is for ensuring that there would be no errors when calculating the balance of a business.
		<br>So if you need to change the charging method all you have to do is to create a new Package Deal, with the necessary charging amounts, and associate it with the business. 
		<br>We recommend that each time you need to change a package deal of a business, to inform the owner of the business to pay the balance. If there is a balance when changing the package deal, the old balance will be added to the new history, so it is not a necessity. This is mentioned mostly because you will no more be able to view the charging history of an older package deal, only its balance will be available.
		<br>
		<hr style="color:#f19c06;background-color:#f19c06;border-color:#f19c06;border:none;height:1px;">
		<b>Associating a Package Deal to a business</b><br />
		From the controls of each business you can associate the appropriate package deal to it. Just select the name of the package deal you wish, from the drop down list.
		<br>
		<hr style="color:#f19c06;background-color:#f19c06;border-color:#f19c06;border:none;height:1px;">
		<b>The Default Package Deal</b><br />
		Each time you add a new business, automatically the default package deal is associated to it. You can change it from the controls of the business as described above.<br />
		To change the default package deal just check the checkbox in the column that says "Default" and is in the same row as the package deal in the following list.
		  	
		<br /><div align="center" style="width:100%"><a style="color:#f19c06;" onclick="jQuery('.infoContent').hide('slow');">OK! Got it, hide info</a></div>
		<br /><br />
	</div>
</div>
     <div><table class="widefat"><thead><tr><th style="font-size:11px">Business Name: <a href="<?php echo get_admin_url();?>admin.php?page=<?php echo $editBusinessLink; ?>&bID=<?php echo $business->ID;?>" style=""><?php _e( $business->post_title ); ?></a> | Type: <em><?php echo $business->post_type; ?></em> 
     <?php if( isset( $_REQUEST['rID'] ) ){ ?>
     | <a href="admin.php?page=<?php echo $_REQUEST['page'];?>&type=<?php echo $_REQUEST['type'];?>&action=view&bID=<?php echo $_REQUEST['bID'];?>" class="littleEditBtns" style="color:green;">Add new room</a>
     <?php } ?>
     </th></tr></thead></table></div>
     <br>
		<div id="post-body" <? if ($eb_action == "view") {?> style="display:none" <? }?> >
			<div id="post-body-content">
            <?php 	            	            	            	
					if((isset($_REQUEST['rID']) && $_REQUEST['rID'] != '') || $roomId != ''){
						include_once(ABSPATH.'wp-content/plugins/'.$ebPluginFolderName.'/classes/booking.class.php');
						$bookVar = new searchServices;
						$startOpPeriod = $bookVar->businessOperatingPeriod($_REQUEST['bID'], $wpdb ,'start', $table_prefix);
						$endOpPeriod = $bookVar->businessOperatingPeriod($_REQUEST['bID'], $wpdb ,'end', $table_prefix	);									
            ?>
							<div class="eb_simpleContainer">
								<a name="imagesList"></a>
								<div class="imgHolderTitleDiv"><strong><?php _e( $roomTitle );?> images list</strong> <a href="#imagesList" class="littleEditBtns" onclick="showHideImageArea()"><span id="showHideImgLinkTitle">Show</span> <?php _e( $roomTitle );?> images</a></div>
                          <div style="line-height:5px;">&nbsp;</div>
                          <div id="b_logoHolder" class="imageAreaDiv" style="border:1px solid #dfdfdf;padding:2px;margin-top:5px;background-color:#f8f8f8;-webkit-border-radius: 5px;-moz-border-radius: 5px;border-radius: 5px;margin-left:1px;">
                            <?php
                             if($roomDefaultLogo!=''){?>
                            <div class="imgHolderTitleDiv"><strong>Room logo</strong></div>
                            <div style="line-height:5px;">&nbsp;</div>
                            	<span>
										<span style="position:absolute; padding-top:5px; padding-left:128px;"> 
											<a onclick="deleteIamges('<?php echo $roomDefaultLogo;?>')"  class="littleDelBtnsTrans" title="Delete your room logo <?php echo $roomDefaultLogo;?>. Please replace it later on...">X</a>
										</span>
										<a target="_blank" href="<?php echo WP_CONTENT_URL?>/plugins/<?php echo $ebPluginFolderName?>/images/RoomImg/<?php echo $roomDefaultLogo?>" title="Click to view fool size of your room logo <?php echo $roomDefaultLogo;?>"><img id="eb_defaultImg" width="150" src="<?php echo WP_CONTENT_URL?>/plugins/<?php echo $ebPluginFolderName?>/images/RoomImg/thumbs/<?php echo $roomDefaultLogo?>" ></a>
									</span>
                            <?php }
                            else {
                            ?>
                            	<div class="error" style="color:#666"><strong>You have not set a default image logo for room <em><?php _e( $roomTitle );?></em>  yet.</strong><br />After uploading your images for this room, you can set one of them as your room logo by pressing the "logo it" button which appears on every image.<br /><em>You can change it in the future with the same way.</em></div>
                            <?php }?>
                            </div>
                         <div style="line-height:5px;">&nbsp;</div>
                         <div id="b_imgHolder" class="imageAreaDiv">
                         <div class="imgHolderTitleDiv"><strong>Room <em><?php _e( $roomTitle );?></em> images list</strong></div>
                            <div style="line-height:5px;">&nbsp;</div>
							<?php 
							if(!empty($roomLogo)){
								for($i=0; $i < count($roomLogo); $i++){
									 if ($roomLogo[$i]!="" && $roomLogo[$i] != $roomDefaultLogo){
									?>							
									<span>
										
										<span style="position:absolute; padding-top:5px; padding-left:78px;"> 
											<a onclick="deleteIamges('<?php echo $roomLogo[$i];?>')"  class="littleDelBtnsTrans" title="Delete <?php echo $roomLogo[$i];?> image">X</a>
										</span>
										<span style="position:absolute; padding-top:5px; padding-left:28px;">
											<a onclick="logoIt('<?php echo $roomLogo[$i];?>')" class="littleDelBtnsTrans" title="Make this a default logo image for room <?php _e( $roomTitle );?>"><strong>Logo it</strong></a> 
										</span>
										<a target="_blank" href="<?php echo WP_CONTENT_URL?>/plugins/<?php echo $ebPluginFolderName?>/images/RoomImg/<?php echo $roomLogo[$i]?>" title="Click to view fool size of image <?php echo $roomLogo[$i];?>"><img id="eb_img" width="100" src="<?php echo WP_CONTENT_URL?>/plugins/<?php echo $ebPluginFolderName?>/images/RoomImg/thumbs/<?php echo $roomLogo[$i]?>" ></a>
									</span>&nbsp;&nbsp;
									<?php 
									}
								}
							} ?>
                                </div>
								<span>
								<div id="b_imgUploadArea" class="imageAreaDiv" style="border:1px solid #dfdfdf;padding:2px;margin-top:5px;background-color:#f8f8f8;-webkit-border-radius: 5px;-moz-border-radius: 5px;border-radius: 5px;width:98.3%;margin-left:1px;">
                         <div class="imgHolderTitleDiv"><strong>Upload new images</strong></div>
								<div id="logoUploadArea">
								<p id="result"></p>
								<p id="f1_upload_process" style="display:none">Loading...<br/><img src="<?php echo WP_CONTENT_URL;?>/plugins/wp-easybooking/images/ajax-loader.gif" /></p>
<!-- bID is in this page the room id. we use the same function thats why we use the same variables-->
								<?php
								$rIDforImg = '';
								if(isset($_REQUEST['rID']) && $_REQUEST['rID'] !='') $rIDforImg = $_REQUEST['rID'];
								else $rIDforImg = $roomId;
								?>
								<form action="<?php echo WP_CONTENT_URL.'/plugins/'.$ebPluginFolderName; ?>/uploadLogo.php?target=roomLogo&bID=<?php echo $rIDforImg; ?>" method="post" enctype="multipart/form-data" target="upload_target" onsubmit="startUpload();" >
								
    								<strong>Image</strong> <input name="Filedata" type="file" id="fileUploadArea" />
          					<input type="submit" name="submitBtn" value="Upload image" />
								</form>
 								
								<iframe id="upload_target" name="upload_target" src="#" style="width:0;height:0;border:0px solid #fff;"></iframe>
								</div>
								<script type="text/javascript" >
								function showHideImageArea(){
									if(jQuery(".imageAreaDiv").is(":visible")){
										jQuery(".imageAreaDiv").hide("slow");
										jQuery("#showHideImgLinkTitle").html("Show");
									}
									else{
										jQuery(".imageAreaDiv").show("slow");
										jQuery("#showHideImgLinkTitle").html("Hide");
									}
								}
								function logoIt(imgName){
									var confDel = confirm('If you continue you will set this image ("'+imgName+'") as your room logo! You can change it later if you change your mind...');
									if (confDel){
										var delUrl = "admin.php?page=<?php echo $_REQUEST['page']?>&type=<?php echo $_REQUEST['type']?>&bID=<?php echo $_REQUEST['bID']?>&rID=<?php echo $roomId?>&logoimg="+imgName;
										window.location = delUrl;
									}
								}
								function deleteIamges(imgName){
									var confDel = confirm('Are you sure you want to delete image "'+imgName+'"?');
									if (confDel){
										var delUrl = "admin.php?page=<?php echo $_REQUEST['page']?>&type=<?php echo $_REQUEST['type']?>&bID=<?php echo $_REQUEST['bID']?>&rID=<?php echo $roomId?>&delimg="+imgName;
										window.location = delUrl;
									}	
								}
								function startUpload(){
    								document.getElementById('f1_upload_process').style.visibility = 'visible';
    								return true;
								}
								function stopUpload(resultStr){
							var result = resultStr.split("|");

      					if (result[0] == 1){
      						jQuery('#fileUploadArea').val('');
         					document.getElementById('result').innerHTML =
          					'<span class="msg">The image was uploaded successfully!<\/span><br/><br/>';
							var imgHolderCont= jQuery("#b_imgHolder").html();
							var newHolderStr = '<span><span style="position:absolute; padding-top:5px; padding-left:75px;"> <a  onclick="deleteIamges(\''+result[1]+'\')" class="littleDelBtnsTrans" title = "Delete '+result[1]+' image">X</a></span><span style="position:absolute; padding-top:5px; padding-left:28px;"><a onclick="logoIt(\''+result[1]+'\')" class="littleDelBtnsTrans" title="Make this your default logo image"><strong>Logo it</strong></a> </span><a target="_blank" href="<?php echo WP_CONTENT_URL?>/plugins/<?php echo $ebPluginFolderName?>/images/RoomImg/'+result[1]+'"  title="Click to view fool size of image '+result[1]+'"><img id="eb_img" width="100" src="<?php echo WP_CONTENT_URL?>/plugins/<?php echo $ebPluginFolderName?>/images/RoomImg/thumbs/'+result[1]+'" ></a></span>&nbsp;&nbsp;';
							jQuery("#b_imgHolder").html(imgHolderCont+newHolderStr);
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
								</div>
								</span>
							</div>

                                      <?php
									  }
									  ?>
									 
			<form name="ebrooms_form" id="ebrooms_form" method="post" action="admin.php?page=<?php echo $eb_page . "&type=" . $eb_type . "&bID=".$_REQUEST['bID']."&action=".$form_action; if ($_REQUEST['action'] == 'add' || $_REQUEST['action'] == 'edit') echo '&rID='.$roomId;  ?>">
			<table width="100%" style="width:100%" class="noclasspls">
					<tr valign="top">
						<td width="70%" style="width:70%" valign="top">				
							<table class="widefat" id="roomContainerTbl">
								<thead>
								<tr>
								<th><span>
								<?php
									if($roomTitle != '') {
										//echo '<span>Edit details of room <em style="font-size:18px">'.__($roomTitle).'</em></span><span style="float:right;"><a class="littleEditBtns" style="font-size:12px;padding:5px" onclick="getAvailability()">Room availability</a>&nbsp;<a class="littleEditBtns" style="font-size:12px;padding:5px" href="'.$eb_adminUrl.'?page='.$targetPage.'&type=Hotel&action=bookit&bID='.$_REQUEST['bID'].'&rID='.$roomId.'">Book it now</a></span>';
										echo '<span>Edit details of room <em style="font-size:18px">'.__($roomTitle).'</em></span><span style="float:right;"><a class="littleEditBtns" style="font-size:12px;padding:5px" onclick="getAvailability()">Room availability</a></span>';
									}
									else echo 'Enter the details of the new Room';
									
								?>									
								</span></th>
								</tr>
								</thead>
								<tbody style="border:none">    
									<tr>
									<td style="border:none">
										<table style="border:none">
											<tr valign="top">
												<td valign="top" width="48%" class="eb_simpleContainer">
													<h3>Basic info</h3>
													<table style="border:none;"><tr><td style="border:none;">
													<div class="eb_inputArea" style="float:left">
														<label><strong>Room Type</strong> <span class="description">(required)</span></label><br>
														<?php
														global $q_config;
														if(empty($q_config['enabled_languages'])) {
														?>
														<input type="text" style="width:200px;font-size:12px" name="roomTitle" id="roomTitle" class="multilanguage-input" value="<? _e( $roomTitle ); ?>"/>
														<?php
														}
														else{
															echo '<input type="hidden" name="eb_isMultyLang" value = "true">';
															echo '<input type="hidden" name="roomTitle" value = "TRANSLATABLE">';
															echo '<div id="eb_Address" style="border:1px solid #dfdfdf;background-color:#e9eced;-webkit-border-radius: 5px;-moz-border-radius: 5px;border-radius: 5px;">
															<div id="titlewrap">
															<table style="border:none">';
															foreach($q_config['enabled_languages'] as $language) {
															echo "<tr><td style='border:none'><img alt=\"".$language."\" title=\"".$q_config['language_name'][$language]."\" src=\"".WP_CONTENT_URL.'/'.$q_config['flag_location'].$q_config['flag'][$language]."\" /></td>";
															$roomTitleTrans = getTextBetweenTags($roomTitle, $language);
															if($roomTitleTrans != '') _e('<td style="border:none"><input type="text" name="roomTitle_'.$language.'" size="30" tabindex="1" value="'.$roomTitleTrans.'" id="roomTitle_'.$language.'" autocomplete="off" /></td></tr>');
																else _e('<td style="border:none"><input type="text" name="roomTitle_'.$language.'" size="30" tabindex="1" value="'.$roomTitle.'" id="roomTitle_'.$language.'" autocomplete="off" /></td></tr>');
															}	//end foreach
															echo '</table></div>
															</div>';//end Address
														}//end else
														?>
													</div>
													<div style="margin-top:10px; padding:4px;float:left"></div>
													<div class="eb_inputArea" style="float:left;width:100px;;">
														<label><strong>No of rooms</strong></label><br>
														<input type="text" style="width:50px;font-size:12px" name="roomNum" id="roomNum" value="<? echo $roomNum ?>"/>
													</div>
													<div style="margin-top:10px; padding:4px;float:left"></div>
													<div class="eb_inputArea" style="float:left;width:100px;margin-left:8px;">
														<label><strong>Max Adults</strong></label><br>
														<select style="width:50px;font-size:12px" name="peopleNum" id="peopleNum">
														<?php 
															for( $i = 1; $i <= 10; $i++ ){
																echo '<option value = "'.$i.'" ';
																if( $peopleNum == $i ) echo 'selected';
																echo ' >'.$i.'</option>';	
															}
														?>

														</select>
													</div>
													</td></tr>
													<tr><td style="border:none;">
													<div class="eb_inputArea" style="float:left">
														<label><strong>Babies allowed</strong></label><br>
														<select style="width:50px;font-size:12px" name="babiesAllowed" id="babiesAllowed">
														<?php 
															for( $i = 0; $i <= 10; $i++ ){
																echo '<option value = "'.$i.'" ';
																if( $babiesAllowed == $i ) echo 'selected';
																echo ' >'.$i.'</option>';	
															}
														?>
														</select>
													</div>
													<div style="margin-top:10px; padding:4px;float:left"></div>
													<div class="eb_inputArea" style="float:left">
														<label><strong>Children</strong></label><br>
														<select style="width:50px;font-size:12px" name="childrenAllowed" id="childrenAllowed">
														<?php 
															for( $i = 0; $i <= 10; $i++ ){
																echo '<option value = "'.$i.'" ';
																if( $childrenAllowed == $i ) echo 'selected';
																echo ' >'.$i.'</option>';	
															}
														?>															
														</select>
													</div>
													<div style="margin-top:10px; padding:4px;float:left"></div>
													<!--<div class="eb_inputArea" style="float:left">
														<label><strong>Extra bed</strong></label><br>
														<select style="width:50px;font-size:12px" name="extraBedsAvailable" id="extraBedsAvailable">
														<?php 
															for( $i = 0; $i <= 10; $i++ ){
																echo '<option value = "'.$i.'" ';
																if( $extraBedsAvailable == $i ) echo 'selected';
																echo ' >'.$i.'</option>';	
															}
														?>
														</select>
													</div>-->
													</td></tr></table>
												</td>
												<td valign="top" class="eb_simpleContainer">
												<table style="border:none"><tr valign="top"><td style="border:none" valign="top">
												<h3>Pricing Info</h3>
												<?php
												//echo 'BB and HB = '.$hasBBandHB;
												//$hasBBandHB = 'YES';
												?>
												<!--<input onclick="switchBBnHB()" type="checkbox" id="BBorHB_check" name="BBorHB_check" value="YES" <?php if($hasBBandHB == "YES") echo 'checked="yes"';?> ><label for="BBorHB_check"> Set seperate prices for <em><strong>Bed and Breakfast (BB)</strong></em> and <em><strong>Half Board (HB)</strong></em></label>-->
												<script type="text/javascript" >
												function switchBBnHB(){
													var checked = '';
													if(jQuery("#BBorHB_check").attr("checked")){
														jQuery(".BBorHB_span").show('slow');
													}
													else jQuery(".BBorHB_span").hide('slow');
													
												}
												</script>
												<br /> 
												<?php 
												if ($eb_BusinessHasSeasons == "YES" ){
												?>
												<div class="eb_inputArea" style="float:left">
													<label><strong>Low season Price</strong> <span class="description"></span></label><br>
													<span class="BBorHB_span"  <?php if($hasBBandHB != "YES") echo 'style="display:none"';?>><label title="Set Bed and Breakfast price for low season">BB</label></span>
													<input type="text" onkeypress="return priceNumbersOnly(event)" style="width:50px;font-size:12px" name="lprice"  value="<? echo $lprice ?>" />&nbsp;<?php echo $currency ?> / night<br />
													<span class="BBorHB_span"  <?php if($hasBBandHB != "YES") echo 'style="display:none"';?>><label title="Set Half Board price for low season">HB</label>
													<input type="text" onkeypress="return priceNumbersOnly(event)" style="width:50px;font-size:12px" name="HB_lprice"  value="<? echo $HB_lprice ?>" />&nbsp;<?php echo $currency ?> / night</span>
												</div> 
												<div style="padding:4px;float:left"></div>
												<div class="eb_inputArea" style="float:left">
													<label><strong>Middle season Price</strong> <span class="description"></span></label><br>
													<span class="BBorHB_span"  <?php if($hasBBandHB != "YES") echo 'style="display:none"';?>><label title="Set Bed and Breakfast price for mid season">BB</label></span>
													<input type="text" onkeypress="return priceNumbersOnly(event)" style="width:50px;font-size:12px" name="mprice" value="<? echo $mprice ?>" />&nbsp;<?php echo $currency ?> / night<br />
													<span class="BBorHB_span"  <?php if($hasBBandHB != "YES") echo 'style="display:none"';?>><label title="Set Half Board price for mid season">HB</label>
													<input type="text" onkeypress="return priceNumbersOnly(event)" style="width:50px;font-size:12px" name="HB_mprice"  value="<? echo $HB_mprice ?>" />&nbsp;<?php echo $currency ?> / night</span>
												</div> 
												<div style="padding:4px;float:left"></div>                            
												<div class="eb_inputArea" style="float:left">
													<label><strong>High season Price</strong> <span class="description">(required)</span></label><br>
													<span class="BBorHB_span"  <?php if($hasBBandHB != "YES") echo 'style="display:none"';?>><label title="Set Bed and Breakfast price for high season">BB</label></span>
													<input type="text" onkeypress="return priceNumbersOnly(event)" style="width:50px;font-size:12px" name="hprice" value="<? echo $hprice ?>" />&nbsp;<?php echo $currency ?> / night<br />
													<span class="BBorHB_span"  <?php if($hasBBandHB != "YES") echo 'style="display:none"';?>><label title="Set Half Board price for high season">HB</label>
													<input type="text" onkeypress="return priceNumbersOnly(event)" style="width:50px;font-size:12px" name="HB_hprice"  value="<? echo $HB_hprice ?>" />&nbsp;<?php echo $currency ?> / night</span>
												</div> 
												<div style="<?php if($hasBBandHB != 'YES') echo 'display:none;';?>width:200px;height:80px;"></div>
												<span class="BBorHB_span" style="<?php if($hasBBandHB != 'YES') echo 'display:none;';?>"><em>*Half Board prices will be <b>added</b> to the room price, to calculate the final room price during booking.</em></span>
												<?php 
												}
												if ($eb_BusinessHasSeasons == "NO" || empty($eb_BusinessHasSeasons) ){
												?>
												<div class="eb_inputArea" style="float:left">
													<label><strong>Flat Price for all operating period</strong> <span class="description">(required)</span></label><br>
													<span class="BBorHB_span"  <?php if($hasBBandHB != "YES") echo 'style="display:none"';?>><label title="Set Bed and Breakfast flat price (for all operating period)">BB</label></span>
													<input type="text" onkeypress="return priceNumbersOnly(event)" style="width:50px;font-size:12px" name="fprice" value="<? echo $fprice ?>" />&nbsp;<?php echo $currency ?> / night<br />
													<span class="BBorHB_span"  <?php if($hasBBandHB != "YES") echo 'style="display:none"';?>><label title="Set Half Board flat price (for all operating period)">HB</label>
													<input type="text" onkeypress="return priceNumbersOnly(event)" style="width:50px;font-size:12px" name="HB_fprice"  value="<? echo $HB_fprice ?>" />&nbsp;<?php echo $currency ?> / night</span>
												</div> 
												<?php										
												}
												?>
												</td></tr>
												<?php
												$eb_lateCheckoutTime = get_post_meta($_REQUEST['bID'], "eb_lateCheckoutTime");
												if( !empty($eb_lateCheckoutTime) ) {												
													$eb_lateCheckoutTime = $eb_lateCheckoutTime[0];
													$eb_lateCheckoutPrice = get_post_meta($roomId, "eb_lateCheckoutPrice");
													if(!empty($eb_lateCheckoutPrice)) $eb_lateCheckoutPrice = $eb_lateCheckoutPrice[0]; else $eb_lateCheckoutPrice ='0';
												?>
												<tr>
													<td style="border:none">
														<div class="eb_inputArea" style="float:left">
														<b>Additional charge for late checkout:</b> <input type="text" value="<?php echo $eb_lateCheckoutPrice;?>" name="eb_lateCheckoutPrice" onkeypress="return priceNumbersOnly(event)" style="width:50px;font-size:12px" /> &nbsp;<?php echo $currency ?>
														</div>
													</td>
												</tr>
												<?php
												}
												?>
												<tr>
													<td style="border:none">
														<em>You can use the comma "," or dot "." as a decimal delimiter. No need for a thousands delimiter.</em>
													</td>
												</tr>
												
												</table>
												</td>
											</tr>
											<tr>
												<td colspan="2" width="100%" class="eb_simpleContainer">
													<h3>Room Facilities</h3>
													<div id="facilitiesContainer" style="width:99%;height:300px;overflow:scroll" align="center">
													<table style="border:1px solid #dfdfdf;padding:2px;background-color:#f8f8f8;-webkit-border-radius: 5px;-moz-border-radius: 5px;border-radius: 5px;padding:2px;width:100%;" align="center" cellspacing="0">
													<?php 
													global $facilitiesTable_name;
													$facility_counter = 1;
													$facilities = $wpdb->get_results('select * from '.$facilitiesTable_name.' where facility_for = "Room"');
													foreach($facilities as $facility){
														$ischecked = "";
														for($i=0; $i < sizeof($facilities); $i++){
															if ($facility->facility_id == $facilities[$i]){
																$ischecked = "checked";
															}											
														}
														if ($facility_counter == 1) echo '<tr>';
														echo '<td style="border:none">';
														_e('<div  class="facility-ico-selector" align="center">
														<input type="checkbox" '.$ischecked.' style="border:none" name="facilitiesChBox[]" id="fclty_'.$facility->facility_id.'" value="'.$facility->facility_id.'"/>');
														echo '<br /><label for = "fclty_'.$facility->facility_id.'">';
														if($facility->image!='')
															_e('<img src="'.WP_CONTENT_URL.'/plugins/'.$ebPluginFolderName.'/images/icons/'.$facility->image.'" title="'.$facility->facility_name.'">');
														else
															_e('<img src="'.WP_CONTENT_URL.'/plugins/'.$ebPluginFolderName.'/images/no_img_small.png" title="'.$facility->facility_name.'">');
														_e('<br />'.$facility->facility_name.'<label></div>');
														echo '</td>'; 
														if ($facility_counter == 9) {echo '</tr>';$facility_counter = 0;}
														$facility_counter++;
													}
													?>
												</table>
												</div>
														
												</td>
											</tr>
											<tr>
											<td colspan="2" width="100%" class="eb_simpleContainer">
												<h3>Room Description</h3>
												<div id="poststuff">
												<div style="display:none;">
													<div id="titlediv" >
														<div id="titlewrap">
															<input type="text" name="invisible" size="30" tabindex="1" value="" id="title" autocomplete="on" />
														</div>	
													</div>
												</div>
												<div id="<?php echo user_can_richedit() ? 'postdivrich' : 'postdiv'; ?>" class="postarea" style="background-color:#fff;">
													<?php
													global $wp_version; 
													if( $wp_version >= 3.4)
														wp_editor($RoomDesc,$id = 'content', $prev_id = 'title', $media_buttons = true, $tab_index = 1);
													else 
														the_editor($RoomDesc,$id = 'content', $prev_id = 'title', $media_buttons = true, $tab_index = 1);
													?>
												</div>
												</div>
											</td>
											</tr>
										</table>
										                            
										<div style="margin-top:10px; padding:15px;float:left"></div>
											
										<!--<div onclick="showFacilities()" style="border:1px solid #dfdfdf;padding:2px;background-color:#f8f8f8">
										<strong>Facilities</strong>
										</div>-->
										</td>
									</tr>
								</tbody>				
							</table>
								
						</td>
						
					</tr>
					<tr>
						<td>
						
						</td>
						
					</tr>
				</table>
				<p class="submit">
					<input type="hidden" name="curTime" id="curTime" value="" />
					<input type="hidden" name="ebOwnerID" id="ebOwnerID" value="<?php echo $eb_BusinessOwnerID; ?>" />        
					<input type="hidden" name="bID" id="bID" value="<?php echo $_REQUEST['bID']; ?>" />
                    <?php if( isset($_REQUEST['rID'])) ?>
					<input type="hidden" name="rID" id="rID" value="<?php echo $_REQUEST['rID']; ?>" />
                    
					<input type="submit" name="Submit" value="<?php _e('Save') ?>" />
				</p>
			</form>
				<script>
				 var currentTime = new Date();
				var month = currentTime.getMonth() + 1;
				var day = currentTime.getDate();
				var year = currentTime.getFullYear();
				var hours = currentTime.getHours()
				var minutes = currentTime.getMinutes()
				var seconds = currentTime.getSeconds()
				
				var curDate = year+"-"+month+"-"+day+" "+hours+":"+minutes+":"+seconds;
				jQuery("#curTime").val(curDate);
				
				<?php
				if($eb_BusinessFacilities != ''){
					$facilities = explode("|", $eb_BusinessFacilities);
					for($i=0; $i < count($facilities)-1; $i++){
						echo 'jQuery("#fclty_'.$facilities[$i].'").attr("checked","checked");';
					}
				}
				?>
				</script>
			
			</div>
		</div>
	
	<?php
	}


// ROOMS LIST

$getBusinessType = '';
$type = '';
$orderBy = '';
$asc = 'desc';
//list specific types



//$eb_url = 
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

<?php if ($eb_action == "view") {
	//====================================================================
	//       DELETE ROOM
	//====================================================================
	if(isset($_REQUEST['rmroom']) && $_REQUEST['rmroom'] != ''){
		$delroom = $wpdb->get_row('select post_title, post_type from '.$table_prefix.'posts where ID ='.$_REQUEST['rmroom']);		
		
		$delroomImages = get_post_meta($_REQUEST['rmroom'], "eb_logo");	
		if(!empty($delroomImages)) $delroomImages = $delroomImages[0]; else $delroomImages ='';							
		$delroomImages = explode("|", $delroomImages);
		
		if(!empty($delroomImages)){
			for($i=0; $i < count($delroomImages); $i++){
				if ($delroomImages[$i]!=""){
					$imgPath = ABSPATH.'wp-content/plugins/'.$ebPluginFolderName.'/images/RoomImg';
					if(is_file($imgPath.'/thumbs/'.$delroomImages[$i])){
						unlink($imgPath.'/thumbs/'.$delroomImages[$i]);
						//echo 'Image '.$delroomImages[$i].' deleted succesfully from thumbs<br>';
					}
					else echo 'No such file '.$delroomImages[$i]. 'in thumbs<br>';
					
					if(is_file($imgPath.'/'.$delroomImages[$i])){
						unlink($imgPath.'/'.$delroomImages[$i]);	
						echo 'Image '.$delroomImages[$i].' deleted succesfully<br>';
					}
					else echo 'No such file '.$delroomImages[$i]. '<br>';	
				}
			}
		} 	
		wp_delete_post($_REQUEST['rmroom']);	
		//delete_post_meta($_REQUEST['rmroom']);
		 
		echo '<div class="updated" style="padding:10px;">Room type <em style="font-size:16px">'.__( $delroom->post_title).'</em> was successfully deleted </div><br />';	
	}
	//====================================================================
	//====================================================================
	
$room_count = $wpdb->get_var("SELECT COUNT(*) FROM ".$table_prefix."posts where post_type='rooms' and post_parent =".$_REQUEST['bID']);
$b_typeStr='';
echo '<table class="widefat" style="width:99%"><thead><tr><th><span>There are <font size="4"><b><i>'.$room_count.'</i></b></font> room types</span>';

	?> <img src="<? echo WP_CONTENT_URL.'/plugins/'.$ebPluginFolderName.'/admin/add.png' ?>" style="cursor:pointer" onclick="this.style.display='none'; document.getElementById('post-body').style.display='block';" align="absmiddle" /> <? }?> 
</th>
</tr></thead></table>

<div id="businessContainer">
<table class="widefat" style="width:99%">
<thead>
    <tr>
    	<th></th>
       <?php 
       	if(($orderBy == "post_title" && $asc == "desc") || $orderBy != "post_title") {
       		echo' <th class="manage-column column-username sortable desc"><a href="'.$eb_adminUrl.'?page='.$eb_page.$type.'&orderBy=n&desc=asc" title="Order businesses by their name"><span>Room Name/Type</span> <span class="sorting-indicator"></span></a></th>';
       	}
       	else {
       		echo' <th class="manage-column column-username sorted asc"><a href="'.$eb_adminUrl.'?page='.$eb_page.$type.'&orderBy=n&desc=desc" title="Order businesses by their name in descending order"><span>Room Name/Type</span> <span class="sorting-indicator"></span></a></th>';
       	}
       	?>

        <th>Rooms Num</th>
        <th>Max Person</th>
        <th></th>
    </tr>
</thead>
<tfoot>
	<tr>
		<th></th>
		 <?php 
       	if(($orderBy == "business_name" && $asc == "desc") || $orderBy != "business_name") {
       		echo' <th class="manage-column column-username sortable desc"><a href="'.$eb_adminUrl.'?page='.$eb_page.'&orderBy=n&desc=asc" title="Order businesses by their name"><span>Room Name/Type</span> <span class="sorting-indicator"></span></a></th>';
       	}
       	else {
       		echo' <th class="manage-column column-username sorted asc"><a href="'.$eb_adminUrl.'?page='.$eb_page.'&orderBy=n&desc=desc" title="Order businesses by their name in descending order"><span>Room Name/Type</span> <span class="sorting-indicator"></span></a></th>';
       	}

       	?>
        <th>Rooms Num</th>
        <th>Max Person</th>
        <th></th>
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
$pagingUrl = $eb_adminUrl.'?page='.$eb_page.$type.$orderByLink.$ascStr;
$room_count = $wpdb->get_var("SELECT COUNT(*) FROM ".$table_prefix."posts where post_type='rooms' and post_parent =".$_REQUEST['bID']);
$items = $room_count;

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

$rooms = $wpdb->get_results('select ID, post_author, post_title, post_type, post_parent from '.$table_prefix.'posts where post_type="rooms" and post_parent ='.$_REQUEST['bID']);

if(!empty($rooms)){
	$busCount = $eb_listLimit*($p->page - 1) + 1;
	
	foreach($rooms as $rooms){

			$eb_RNum = get_post_meta($rooms->ID, "eb_roomNum");
				if(!empty($eb_RNum)) $eb_RNum = $eb_RNum[0]; else $eb_RNum ='';			
			$eb_pNum = get_post_meta($rooms->ID, "eb_peopleNum");
				if(!empty($eb_pNum)) $eb_pNum = $eb_pNum[0]; else $eb_pNum ='';			
			
			if ($rooms->ID != $roomId) $edit_link = "<a href='".$eb_adminUrl."?page=".$_REQUEST['page']."&type=Hotel&bID=".$_REQUEST['bID']."&rID=".$rooms->ID."'>Edit</a> |"; else $edit_link="";
			$rmRoom_link = $eb_adminUrl."?page=".$_REQUEST['page']."&type=Hotel&bID=".$_REQUEST['bID']."&rID=".$rooms->ID."&rmroom=conf";		
			echo '<tr><td>'.$busCount.'</td><td><a href="'.$eb_adminUrl.'?page='.$_REQUEST['page'].'&type=Hotel&bID='.$_REQUEST['bID'].'&rID='.$rooms->ID.'">'.__( $rooms->post_title ).'</a></td><td>'.$eb_RNum.'</td><td>'.$eb_pNum.'</td><td>'.$edit_link.'  <a href="'.$rmRoom_link.'">Delete</a></td></tr>';
			$busCount++;
		
	}
}

?>

</tbody>
</table>
<div id="paging_container">

<div class="wrap" align="center" style="text-align:center;float:left">
<div class="tablenav">
    <div class='tablenav-pages'>
        <?php if($room_count > 0) echo $p->show();  // Echo out the list of paging. ?>
    </div>
</div>

</div><!--end of paging_container-->

</div><!--end of business container-->

</div>


<script type="text/javascript" >
	function getAvailability(addMonths){
				
		jQuery("#availabilitySect").html("<div style='width:100%' align='center'><img src='<?php echo WP_CONTENT_URL;?>/plugins/wp-easybooking/images/loaderMedium.gif'></div>").show();
		if(!addMonths) addMonths = 0;
		<?php 
		$absPathNoSlashes = str_replace('/','|', ABSPATH);
		$absPathNoSlashes = str_replace('\\','|', $absPathNoSlashes);
		?>
		var abspath = "<?php echo $absPathNoSlashes; ?>";		

		jQuery.ajax({
			type: "POST",
  			url: "../wp-content/plugins/wp-easybooking/classes/roomAvailability.class.php",  			
  			data: "sID=<?php echo $_REQUEST['rID']; ?>&sMonth="+addMonths+"&aPath="+abspath+"&pref=<?php echo $table_prefix; ?>",
  			success: function(resp){
  				jQuery("#availabilitySect").html(resp);
			},
			error: function(ermsg){
				alert('Error: '+ermsg);
			}
			});			
			
		if(jQuery("#availabilitySect").css("display") == "none")
			jQuery("#availabilitySect").fadeIn("slow");
		
	}
	
	function hideAvailabilityPalette(){
		jQuery("#availabilitySect").fadeOut("slow");
	}

 function priceNumbersOnly(evt){
 var charCode = (evt.which) ? evt.which : event.keyCode;
 	if (charCode == 46 ) return true;//teleia
 	if (charCode == 44 ) return true;//comma
   if (charCode > 31 && (charCode < 48 || charCode > 57))
   	return false;

   return true;
}
function fetchType(){
	var b_type = jQuery("#select_busType").val();
	var url = "<?php echo $eb_adminUrl .'?page='.$eb_page?>&type="+b_type; 
	window.location = url;
}
</script>

<h2>Bookings</h2>

<?php
global $eb_adminUrl;//<-importand to globalize it so you can use it
$eb_page = addslashes($_REQUEST['page']);
$targetPage = '';
global $ebPluginFolderName;
global $table_prefix;

?>
<div class="information">
	<a id="infoDisp" onclick="jQuery('.infoContent').toggle('slow');" style="color:#f19c06;">
		<img  src="<?php echo WP_CONTENT_URL; ?>/plugins/<?php echo $ebPluginFolderName; ?>/images/infoImg.png"> So what do I do here?
	</a>
	<div class="infoContent" style="display:none;color:#666;font-size:14px;">
		<u>From here you can select a business to view it's bookings or availability.</u>
		<p>By clicking on the button "View bookings" on each business area, you will be immediately redirected at it's bookings page.<br/>
		 If there is any booking "PENDING" you will be notified here, and by clicking on the notification button you will be redirected again to view the Pending bookings of the business.</p>
		<p>There is a little coloured button at the bottom-right of each business area. <br />By clicking on it a new window will appear which informs you with a small calender about the business availability.<br />
		<ol>
			<li style="padding-left:10px">There is a different line for each room type of the business.<br /></li>
			<li style="padding-left:10px">Green colour means that the room type at these dates is fully available, no room has been booked.<br /></li>
			<li style="padding-left:10px">The blue colour means that at least one room has been booked of the room type.<br /></li>
			<li style="padding-left:10px">When you see an orange colour it means that there is only one room left of this room type.<br /></li>
			<li style="padding-left:10px">And the red colour means that there is no room available for these dates of this room type.<br /></li>
			<li style="padding-left:10px">In each day of the calender you will also be informed with text about the availability of each room type.</li>
		</ol>
		</p>
		<br /><div align="center" style="width:100%"><a style="color:#f19c06;" onclick="jQuery('.infoContent').hide('slow');">OK! Got it, hide info</a></div>
		<br /><br />
	</div>
</div>
<?php
//=========================================================
//           CHECK USER LEVEL
//=========================================================

$user_id = get_current_user_id();
$user_info = get_userdata( $user_id );

$noAdmin_whereStr = '';
$editBusinessLink = '';

if($user_info->user_level == 0) {
	$noAdmin_whereStr = 'and post_author = "'.$current_user->ID.'"';
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
if(isset($_REQUEST['own']) && $_REQUEST['own']>= 1 && $current_user->wp_user_level == 10){
	$own = '&own='.$_REQUEST['own'];
	$oID = addslashes($_REQUEST['own']);
	$whereOwner = ' AND post_author = '.$oID;
}

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

include('pagination.class.php');
 global $eb_listLimit;
$orderByLink = '';
if($orderBy != "") {
	$orderByLink = '&orderBy='.$orderBy;
	$orderBy = ' order by '.$orderBy;
} 
$ascStr='';
if($asc != "") $ascStr = '&desc='.$asc;
$pagingUrl = $eb_adminUrl.'?page=bookings_menu'.$type.$orderByLink.$ascStr.$own;
$items = $businesses_count;
if($items > 0) {
        $p = new pagination;
        $p->items($items);
        $p->limit($eb_listLimit); // Limit entries per page
        $p->target($pagingUrl);
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
 
$businesses = $wpdb->get_results('select ID, post_author, post_title, post_type from '.$table_prefix.'posts '.$getBusinessType.' '.$whereOwner.' '.$noAdmin_whereStr. $orderBy.' '.$asc.' '.$limit);
if(!empty($businesses)){
	$busCount = $eb_listLimit*($p->page - 1) + 1;
	$availabilityCalendarImg = get_bloginfo('url').'/wp-content/plugins/'.$ebPluginFolderName.'/images/availabilityImg.png';
echo '<div>';
	foreach($businesses as $business){
		$bBookingsPending = $wpdb->get_var("SELECT COUNT(bookingID) from eb_bookingdata where businessID =". $business->ID.' and booking_status = "Pending"');		
		?>
		<div class="eb_simpleContainer" style="width:350px;float:left;margin:2px;">
		<a href="admin.php?page=<?php echo $editBusinessLink; ?>&bID=<?php echo $business->ID; ?>"><font style="font-size:16px;font-weight:bold"><?php echo $business->post_title ?></font></a>	
		<a class="littleEditBtns" href="admin.php?page=bookings_menu&bID=<?php echo $business->ID; ?>">View bookings</a>		
		<?php if($bBookingsPending > 0)
			echo '<a class="littleEditBtns" href="admin.php?page=bookings_menu&bID='.$business->ID.'&stat=p" style="color:#6fbf4d;"> <b>'.$bBookingsPending.' PENDING</b></a>';
		?>		
		<a onclick="getBAvailability(<?php echo $business->ID; ?>, '<?php echo $business->post_title; ?>', 0)" title="View <?php echo $business->post_title ?> rooms availability" style="position:relative;top:10px;float:right;"><img src="<?php echo $availabilityCalendarImg; ?>" style="border:1px solid #dfdfdf;-webkit-border-radius: 5px;-moz-border-radius: 5px;border-radius: 5px;padding:2px;-moz-box-shadow: 0 1px 1px #fff inset;-webkit-box-shadow: 0 1px 1px #fff inset;box-shadow:  0 1px 1px #fff inset;"></a>
		</div>
		<?php
	}
echo '</div>';
}

init_plugin_name();
function init_plugin_name(){
  wp_enqueue_script('flotscript', WP_PLUGIN_URL . '/wp_easybooking/js/jquery.flot.min.js', array('jquery'));
  wp_enqueue_script('excanvasscript', WP_PLUGIN_URL . '/wp_easybooking/js/excanvas.min.js', array('jquery'));

}
?>
<input type="hidden" id="businessIDForChartNav" value="" />
<input type="hidden" id="businessTitleForChartNav" value="" />
<div class="monthsAvailPalette" id="ebChartHolder" style="left:160px;height:400px;overflow:auto;" align="center">	
	Availability for <font id="chartBusinessTitle" style="font-size:14px;font-weight:bold;"></font><a class="littleCloseBtns" style="float:right;" onclick="closeAvailabilityWindow()">&nbsp;X&nbsp;</a> 
	<div><em style="color:#999;">Canceled bookings are not taken under consideration</em></div>
		<div id="businessAvailabilityDiv" style="width:900px;height:350px;"></div>		
</div>
<?php $absPath = str_replace('\\', '/', ABSPATH);?>
    
<script type="text/javascript">
function closeAvailabilityWindow(){
	jQuery("#ebChartHolder").hide('slow');
	jQuery("#businessAvailabilityDiv").html('');
	
}

function getBAvailability(bID, bTitle, addMonths){	
		jQuery("#chartBusinessTitle").html(bTitle);
		jQuery("#businessAvailabilityDiv").html("<div style='width:100%' align='center'><img src='<?php echo WP_CONTENT_URL;?>/plugins/wp-easybooking/images/loaderMedium.gif'></div>");
		jQuery.ajax({
			type: "POST",
  			url: "../wp-content/plugins/wp-easybooking/classes/businessAvailability.class.php",  			
  			data: "usrLevel=<?php echo $current_user->wp_user_level; ?>&bID="+bID+"&bTitle="+bTitle+"&aPath=<?php echo $absPath; ?>&pref=<?php echo $table_prefix; ?>&sMonth="+addMonths,  		
  			success: function(resp){
				jQuery('#businessAvailabilityDiv').html(resp);
  				jQuery('#ebChartHolder').show();
			},
			error: function (xhr, ajaxOptions, thrownError) {
        alert(xhr.status + ' '+ thrownError);
      }
			});
}
</script>
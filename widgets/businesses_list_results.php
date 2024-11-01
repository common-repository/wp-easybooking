<a id="eb-results-top-point"></a>
<?php
include_once( ABSPATH.'wp-content/plugins/wp-easybooking/widgets/trans-vars/search_results.trans.php' );
global $table_prefix;
global $eb_path;
global $wpdb;

$location = addslashes( $_POST['location'] );
$hasDates = addslashes( $_POST['dates'] );
$from = addslashes( $_POST['from'] );
$to = addslashes( $_POST['to'] );
$adultsNum = addslashes( $_POST['adults'] );
$childrenNum = addslashes( $_POST['children'] );
$babiesNum = addslashes( $_POST['babies'] );

$locationType = addslashes( $_POST['type'] );
$locationID = addslashes( $_POST['lid'] );

$pluginfolder = get_bloginfo('url') . '/' . PLUGINDIR . '/wp-easybooking/';
$result_page_ID = get_option('eb-location-result-page');

$siteCur = $wpdb->get_row('select option_value from '.$table_prefix.'options where option_name = "eb_siteCurrency"');
if( count( $siteCur ) > 0 ) $siteCur = $siteCur->option_value; else $siteCur = '';

//if( isset( $_REQUEST['ccur'] ) && $_REQUEST['ccur'] != '' ) $siteCur = $_POST['ccur'];
if( isset( $_POST['eb_currency'] ) && $_POST['eb_currency'] != '' ) $siteCur = addslashes( $_POST['eb_currency'] );

include($eb_path.'/currencyConverter.php');
$x = new CurrencyConverter(DB_HOST,DB_USER,DB_PASSWORD,DB_NAME,'currencies');

$currencies = $wpdb->get_results('select currency from currencies where currency NOT IN ("EUR", "USD", "GBP")');

if( function_exists(qtrans_getLanguage)){
		$currentLanguage = qtrans_getLanguage();
		$defaultLang = $q_config["default_language"];
	}
	else {
		$defaultLang = 'en';
		$currentLanguage = 'NO_LANGS';
	} 
$term = __( $eb_lang_SearhedByTerm );
$datesMsg = '';
if( $locationType == "Resort") $term = __( $eb_lang_SearhedByResort );
if( $locationType == "Country") $term = __( $eb_lang_SearhedByCountry );
if( $locationType == "City") $term = __( $eb_lang_SearhedByCity );
if( $hasDates != "no" ){
	$datesMsg = ', '.__( $eb_lang_from ).' <em><strong>'.$from.'</strong></em> '.__( $eb_lang_to ).' <em><strong>'.$to.'</strong></em>';
}
?>
<div class="results-searchdata-details-metatitle"><?php echo __( $eb_lang_YouSearhedStr ).' '. $term . ' <em><strong>'.__( $location ).'</strong></em>'.$datesMsg ; ?> </div>
<?php include_once($eb_path.'/widgets/trans-vars/search_results.trans.php')?>
<?php if( isset($_POST['lid']) && $_POST['lid'] != ''){ ?>

<div class="resultDisplayOptionsDiv" align="center">
<span class="organizing-element-title"><?php _e($eb_lang_Sort_By);?> : </span>
<?php
$sortByUrl = $_SERVER["REQUEST_URI"];
$orderBy = '';
$orderType = '';

$orderByName = '&oby=name&otype=asc';
$orderByPrice = '&oby=price&otype=asc';
$orderByStars = '&oby=stars&otype=asc';
if( isset( $_POST['oby'] ) ){
	$sortByUrl = explode('&oby=', $sortByUrl);
	$sortByUrl = $sortByUrl[0];
	$orderBy = '&oby='.$_POST['oby'];
	$orderType = '&otype=desc';
	if( $_POST['otype'] == "asc" ) $orderType = '&otype=asc'; 
	if( $_POST['oby'] == "name" && $_POST['otype'] == "asc" ){ 
		$orderByName = '&oby=name&otype=desc';
	}
	if( $_POST['oby'] == "price" && $_POST['otype'] == "asc" ){ 
		$orderByPrice = '&oby=price&otype=desc';
	}
	if( $_POST['oby'] == "stars" && $_POST['otype'] == "asc" ){ 
		$orderByStars = '&oby=stars&otype=desc';
	}
}

if ( $currentLanguage != 'NO_LANGS' && strpos($sortByUrl, "lang=") === false ) $sortByUrl .= '&lang='.$currentLanguage;
?>
<a class="optionBtn" onclick="setOrderBy('name')" title="<?php _e($eb_lang_Sort_By_Name); ?>"><?php _e($eb_lang_Name_SortBy) ?></a>
<?php
if(isset( $_REQUEST['from'] ) && $_REQUEST['from'] != ''){ 
?>
<a class="optionBtn" onclick="setOrderBy('price')" title="<?php _e($eb_lang_Sort_By_Price); ?>"><?php _e($eb_lang_Price_SortBy) ?></a>
<?php }?>
<a class="optionBtn" onclick="setOrderBy('stars')" title="<?php _e($eb_lang_Sort_By_Stars); ?>"><?php _e($eb_lang_Stars_SortBy) ?></a>
<input type="hidden" id="obyVars" value="" />
<select name="eb_currencySwitch" id="eb_currencySwitch">
	<option value="htlcur" <?php if( $siteCur == "htlcur" )echo 'selected="selected"';?> ><?php _e($eb_lang_Hotel_Currency)?></option>
	<option disabled="disabled" style="height:1px;border-bottom:1px solid #3d81ef;font-size:1px;margin-top:5px;margin-bottom:5px;"></option>
	
	<option value="USD" <?php if( $siteCur == "USD" )echo 'selected="selected"';?> >USD</option>
	<option value="EUR" <?php if( $siteCur == "EUR" )echo 'selected="selected"';?> >EUR</option>
	<option value="GBP" <?php if( $siteCur == "GBP" )echo 'selected="selected"';?> >GBP</option>
	<option disabled="disabled" style="height:1px;border-bottom:1px solid #3d81ef;font-size:1px;margin-top:5px;margin-bottom:5px;"></option>
	
	<?php
	if($currencies){
		foreach($currencies as $currency){
			echo '<option value="'.$currency->currency.'" '; 
				if( $currency->currency == $siteCur ) echo 'selected="selected"';										
			echo'> '.$currency->currency. '</option>';										
		}
	}
	?>
</select>

</div>
<?php
}//end of if isset lid so order by options will be visible
if( $location == '' || ( $hasDates != "no" && ($from == '' || $to == ''))){
	?>
	<table class="" style="width:100%">
	<tr>
	<?php
		echo '<td style="width:50%">
			<div class="eb_search-location-in-main-page" align="center">';
			$eb_folder = PLUGINDIR . '/' . dirname(plugin_basename(__FILE__));
			include($eb_folder.'/search_form.php');
			echo '</div>';
		echo '</td>';
		
		echo '<td style="width:50%">
			<div class = "updated">';
				_e('So sorry but it seems that there were some empty or invalid fields in your search. Please give it another shot', 'easybooking');
			echo '</div>';
		echo '</div>';	
	
	?>
	</tr>
	</table>
	<?php
	
}else{
?>
<div id="loading_while_searcing" style="width:100%" align="center">
	<img src="<?php echo WP_CONTENT_URL . '/plugins/wp-easybooking/images/widget-loader.gif'; ?>" alt="Loading" >
	<br />
	<?php _e('Please wait while searching', 'easybooking')?>
</div>

<div id="business-results-area" class="eb-business-resorts-area"></div>

<form method="post" id="bListFrm">
	<input type="hidden" name="eb" value="resort" />
	<input type="hidden" id="cur" name="cur" value="<?php echo $siteCur; ?>" />
	<input type="hidden" name="from" value="<?php echo $from; ?>" />
	<input type="hidden" name="to" value="<?php echo $to; ?>" />
	
	<input type="hidden" name="location" value="<?php echo $location; ?>" />
	<input type="hidden" id="eb-location-type-spcl" name="type" value="<?php echo $locationType; ?>" />
	<input type="hidden" id="eb-location-id-spcl" name="lid" value="<?php echo $locationID; ?>" />
	<input type="hidden" id = "bID" name="b" value="" />
	<?php if( isset( $_POST["rooms"] ) ){ ?>

	<input type="hidden" name="rooms" value="<?php echo addslashes($_POST['rooms']); ?>" />
		<?php for($r = 1; $r <= $_POST["rooms"]; $r++) {?>
			<?php if(isset(  $_POST['adultsForRoom'.$r] )){?>
			<input type="hidden" name="<?php echo 'adultsForRoom'.$r; ?>" value="<?php echo $_POST['adultsForRoom'.$r]; ?>" />			
			<?php } ?>
			<?php if(isset(  $_POST['childrenForRoom'.$r] )){?>
			<input type="hidden" name="<?php echo 'childrenForRoom'.$r; ?>" value="<?php echo $_POST['childrenForRoom'.$r]; ?>" />
			<?php } ?>
			<?php if(isset(  $_POST['babiesForRoom'.$r] )){?>
			<input type="hidden" name="<?php echo 'babiesForRoom'.$r; ?>" value="<?php echo $_POST['babiesForRoom'.$r]; ?>" />
			<?php } ?>
		<?php } ?>
	<?php } ?>
</form>
<?php
} //end of else not empty fields in search 


	
	$aPath = str_replace('\\', '/', ABSPATH);
	
	add_action('init', 'myplugin_thickbox');
function myplugin_thickbox() {
		$pluginfolder = get_bloginfo('url') . '/' . PLUGINDIR . '/' . dirname(plugin_basename(__FILE__)).'/js';
		wp_enqueue_script('jquery');
		wp_enqueue_script('jquery-ui-core');
		
		wp_enqueue_script('jquery-tinyscrollbar', $pluginfolder . '/jquery.tinyscrollbar.min.js', array('jquery', 'jquery-ui-core') );
		wp_enqueue_script('thickbox', null,  array('jquery'));
		wp_enqueue_style('thickbox.css', '/'.WPINC.'/js/thickbox/thickbox.css', null, '1.0');

}
?>
<script type="text/javascript" >

jQuery(document).ready(function() {
	searchBusinessesWithAjax( "<?php echo $siteCur; ?>", '1' );
	jQuery("#from").css("margin-left","30px;");
});

function setOrderBy( oby ){
	var isSame = false;
	
	if( jQuery("#obyVars").val().indexOf(oby) >= 0 ) isSame = true;
	
	if( isSame ){
		otypeArr = jQuery("#obyVars").val().split('otype');
		if( otypeArr[1] == '=asc') jQuery("#obyVars").val( otypeArr[0]+'otype=desc');
		else jQuery("#obyVars").val(otypeArr[0]+'otype=asc');
	}
	else jQuery("#obyVars").val('&oby='+oby+'&otype=desc');
	
	searchBusinessesWithAjax( "<?php echo $siteCur; ?>", "1" );
	
}

function searchBusinessesWithAjax( currency, rpage ){		

	var urlParams = '&location=<?php echo $location;?>&dates=<?php echo $hasDates;?>&from=<?php echo $from;?>&to=<?php echo $to;?>';
	
	<?php if( isset($_POST['lid'])){ ?>	
	urlParams += '&lid=<?php echo addslashes($_POST["lid"]);?>';
	<?php } ?>
	
	<?php if( isset($_POST['type'])){ ?>	
	urlParams += '&type=<?php echo addslashes($_POST["type"]);?>';
	<?php } ?>
	
	<?php if( isset($_POST['rooms'])){ ?>	
	urlParams += '&rooms=<?php echo addslashes($_POST["rooms"]);?>';
		<?php for($r=1;$r<=(int)$_POST["rooms"];$r++){?>
		urlParams +='&adultsForRoom<?php echo $r; ?>=<?php echo $_POST["adultsForRoom".$r]; ?>';
		urlParams +='&childrenForRoom<?php echo $r; ?>=<?php echo $_POST["childrenForRoom".$r]; ?>';
		urlParams +='&babiesForRoom<?php echo $r; ?>=<?php echo $_POST["babiesForRoom".$r]; ?>'		
		<?php } ?>
	<?php } ?>
	
	<?php
	if( isset( $_REQUEST['rpage'] ) && $_REQUEST['rpage'] != '' ){
	?>
	//var rpage = <?php echo $_REQUEST['rpage']; ?>;
	<?php	
	}
	else{
	?>
	//var rpage =1;
	<?php	
	}
	?>
	urlParams += jQuery("#obyVars").val();

		jQuery.ajax({
					type: "POST",
  					url: "<?php echo $pluginfolder; ?>widgets/ajaxSearchDestinations.php",  			
  					data: "aPath=<?php echo $aPath; ?>&rpage="+rpage+"&pref=<?php echo $table_prefix; ?>&lang=<?php echo $currentLanguage; ?>&defaultLang=<?php echo $defaultLang; ?>&ccur="+currency+"&"+urlParams+"<?php echo $orderBy.$orderType?>",
  					success: function(resp){
  						jQuery("#loading_while_searcing").fadeOut('slow');
  						jQuery("#business-results-area").html(resp).show('slow');  						
					}
				});
}

jQuery('#eb_currencySwitch').change( function() {
	var newCur = jQuery('#eb_currencySwitch').val();
	jQuery("#eb_currency").val( newCur );
	jQuery("#cur").val( newCur );
	//jQuery("#eb-search-location-form").submit();
	searchBusinessesWithAjax( newCur, "1" );
});

function goToResortPage( frmAction ){	
	var frmActionArr = frmAction.split('&b=');
	jQuery('#bID').val( frmActionArr[1] );
	jQuery('#bListFrm').attr("action",frmActionArr[0]).submit();
}


function navResults( pageNum ){
	var newCur = jQuery('#eb_currencySwitch').val();
	searchBusinessesWithAjax( newCur, pageNum );

	var topPos = jQuery("#business-results-area").position().top; 
	jQuery('html, body').animate({ scrollTop: topPos+300 }, 'slow'); 
}

</script>
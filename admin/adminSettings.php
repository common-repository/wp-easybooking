<?php
global $wpdb;
global $eb_path;
global $eb_adminUrl;
global $table_prefix;

if(isset($_POST['ucur']) && $_POST['ucur'] != ''){	
	$uCur = addslashes($_POST['ucur']);	
	update_option('eb_siteCurrency', $uCur);
}
if(isset($_POST['eb_siteBankName']) && $_POST['eb_siteBankName'] != ''){	
	$uCur = addslashes($_POST['eb_siteBankName']);	
	update_option('eb_siteBankName', $uCur);
}

if(isset($_POST['eb_sitePPacount']) && $_POST['eb_sitePPacount'] != ''){	
	$uCur = addslashes($_POST['eb_sitePPacount']);	
	if ( !is_email( $uCur ) ) echo '<div class="error">The paypal account you entered is not a valid email address. Please enter a correct email and try again!</div>';
	else
		update_option('eb_sitePPacount', $uCur);
}

$businessCounter = 0;
$businesses = $wpdb->get_results('select ID, post_author, post_title, post_type from '.$table_prefix.'posts where post_type = "Hotel" OR post_type="Apartments"');
if(!empty($businesses)){	
	$businessList = '';
	foreach($businesses as $business){
		$bEmail = get_post_meta($business->ID, "eb_email");
		if(!empty($bEmail))
			if($bEmail[0] != '') $bEmail = '('.$bEmail[0].')'; else $bEmail = '';
			else $bEmail = '';		
		$businessList .= $businessCounter.':'.$business->ID.':'.$business->post_title.':'.$bEmail.'|';
		$businessCounter++;
	}
}

?>
<input type="hidden" id="businessIDsForBalanceReporter" value="<?php echo $businessList; ?>" />
<?php

$siteCurrency = get_option('eb_siteCurrency');
$siteBankName = get_option('eb_siteBankName');
$sitePPacount = get_option('eb_sitePPacount');
?>
<div id="primary" class="eb_simpleContainer">
<h3 class="widgettitle"><a onclick="dispSettings()">Settings</a></h3>
		<div id="settingsContDiv" style="display:none" class="sickAndHide">
			<span>			
				<form id="chnageSiteCurFrm" name="chnageSiteCurFrm" method="post" action="<?php echo $eb_adminUrl .'?page='.$_REQUEST['page']?>">
				<table style= "border:none" cellpadding="5px">
				<tr valign="top">
					<td style="border:none;" colspan="2">
					<!--These settings can be moderated by any user with <a href="users.php?role=administrator" target="_blank">administrative properties</a> (whose role is Administrator).<br>
					Every time a business owner sends a request or a payment transaction is made, every Administrator will receive a notification email.<br>
					If a payment transaction is made and concerns a business (ie booking payment) only the appropriate business owner will be informed.-->
					</td>
				</tr>
				<tr valign="top">
					<td style="border:none;">
						<label style="padding-top:15px;"><strong>Business Currency</strong></label>	
					</td>				
					<?php
					include_once($eb_path.'/currencyConverter.php');
					$x = new CurrencyConverter(DB_HOST,DB_USER,DB_PASSWORD,DB_NAME,'currencies');
					$currencies = $wpdb->get_results('select currency from currencies');
					?>
					<td style="border:none;">
					<select name="ucur" id="ucur">
						<?php
						if($currencies){
							foreach($currencies as $currency){
								echo '<option value="'.$currency->currency.'" '; 
								if($siteCurrency == $currency->currency) echo ' selected';										
									echo'> '.$currency->currency. ' </option>';										
								}
							}
							?>
					</select>
					<div class="description">Set the main currency of this website. Each debt to you from the businesses will be converted automatically to the currency you set here.
					<br>Conversion is made according to the rates provided by the <a title="ECB" target="_blank" href="http://www.ecb.int/stats/exchange/eurofxref/html/index.en.html">ECB (European Central Bank)</a></div>
					</td>
				</tr>
				<tr valign="top">
					<td style="border:none;">
						<label style="padding-top:15px;"><strong>Bank name:</strong></label>
					</td>
					<td style="border:none">

						<textarea name="eb_siteBankName" style="width:600px;height:180px;color:#333;"><?php echo $siteBankName; ?></textarea>

						<div class="description">Here you can enter the details of the bank you use to get paid. Could be more than one.<br />
						You have to enter all the necessary details of your account in each bank.<br />
						<em>(Usually Bank name, account number, IBAN and perhaps your full name or business name, in general all data needed for a successful payment.)</em><br />
						</div>
					</td>
				</tr>

				<tr valign="top">
					<td style="border:none;">
						<label style="padding-top:15px;"><strong>PayPal email:</strong></label>
					</td>
					<td style="border:none">
						<input type="text" value="<?php echo $sitePPacount; ?>" name="eb_sitePPacount">
						<div class="description">Your paypal email address. The currency of the payment will be the currency you set as your web site's main currency (above).<br><a href="https://cms.paypal.com/us/cgi-bin/?cmd=_render-content&content_ID=developer/e_howto_api_nvp_currency_codes" target="_blank">Please click here to see if paypal supports your currency.</a>
						<br><i>The paypal payment option will be available only if you fill in your paypal email. Else the only payment option will be by bank deposit.</i>
						</div>
					</td>
				</tr>
				
				<!--<tr valign="top">
					<td style="border:none;" valign="top">
						<label style="padding-top:15px;" for="allowOwnersReceptionPayment"><strong>Allow hotel owners to use "Payment at reception" as payment option </strong></label>
					</td>
					<td align="left">
						<em>This is not a recommended payment method. Users may use this method for a booking and never show up.</em><br />
						<input type="checkbox" style="width:10px;border:none" name="allowOwnersReceptionPayment" id="allowOwnersReceptionPayment" value="YES" <?php if( $allowOwnersReceptionPayment == "YES") echo "checked"; ?> />
					</td>
				</tr>-->
				
				</table>
				<input type="submit" value="save" />
				</form>
			</span>			
		</div>
</div>
<div style="width:100%" align="center">
	<a class="show_down_area" id="settingsContDivBtn" onclick="dispSettings()"> Show </a>
</div>
<p>
There is a total of <a href="admin.php?page=busines_menu" target="_blank" title="View all registered businesses"> <b><?php echo $businessCounter; ?> businesses registered</b></a>.
<div class="eb_simpleContainer" id="balanceMainContainer" style="display:none">
<div class="error">
	<h3>Businesses debts <span style="font-style:none;" id="numberOfBusinesessInDebt"></span></h3>
	<div class="description"><i>List of businesses registered at your site that owe you according to their package deal.</i></div>
	<div id="businessesInDebtList" style="display:none;" class="sickAndHide"></div>
</div>
</div>
<div style="width:100%" align="center">
	<a class="show_down_area" id="businessesInDebtListBtn" onclick="dispOwers()" style="display:none"> Show </a>
</div>
</p>
<input type="hidden" value="0" id="busNumOwingHidden">
<script type="text/javascript" >

	jQuery(document).ready(function() {
		jQuery("#numberOfBusinesessInDebt").html('<img src="<?php echo WP_CONTENT_URL;?>/plugins/wp-easybooking/images/ajax-loader.gif" />');
		jQuery("#busNumOwingHidden").val(0);
		var businessIdsStr = jQuery("#businessIDsForBalanceReporter").val();
		var businessIdsStrArr = businessIdsStr.split("|");
		for (i=0;i<businessIdsStrArr.length - 1;i++){
			var businessIdInside = businessIdsStrArr[i].split(":");
			var bID = businessIdInside[1];
			//ajaxBallanceRep(bID, businessIdInside[2]);
		}		
	});
	
	
function dispSettings(){
		if(jQuery("#settingsContDiv").css("display") == "none"){
			//jQuery(".sickAndHide").hide("slow");
			jQuery("#settingsContDiv").show("slow");
			jQuery("#settingsContDivBtn").html("Hide");	
		}	
		else{
			jQuery("#settingsContDiv").hide("slow");
			jQuery("#settingsContDivBtn").html("Show");
		}
	}
	
	
	function ajaxBallanceRep(bID, bName){		
		/*NOT AVAILABLE FOR FREE VERSION*/
	}
	
	function checkHiddenValForNumOfDebtors(){
		jQuery("#numberOfBusinesessInDebt").html('No fees (dept) found');	
	}
	
	
	function dispOwers(){
		if(jQuery("#businessesInDebtList").css("display") == "none"){
			//jQuery(".sickAndHide").hide("slow");
			jQuery("#businessesInDebtList").show("slow");
			jQuery("#businessesInDebtListBtn").html("Hide");	
		}	
		else{
			jQuery("#businessesInDebtList").hide("slow");
			jQuery("#businessesInDebtListBtn").html("Show");
		}
	}
	
</script>
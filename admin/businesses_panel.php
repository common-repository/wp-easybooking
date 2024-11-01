<?php
if(!isset($_REQUEST['action']) || $_REQUEST['action'] == "") include("businesses_list.php");
   elseif(isset($_REQUEST['action']) && $_REQUEST['action'] == "edit") include("edit_business.php"); 
   elseif(isset($_REQUEST['action']) && $_REQUEST['action'] == "new")  echo "three"; 
   else _e("Something seems to be wrong. Please inform supplier for this misbehaviour");
   




?>
<!--
<div id="wpsc_options" class="wrap">
<div id="wpsc_settings_nav_bar" style="width: 100%;"><ul id="sidemenu">
	<li id="tab-general"><a href="/wordpress/wp-admin/options-general.php?page=wpsc-settings&amp;tab=general&amp;_wpnonce=d5c1ac9214" class="current">General</a></li>
	<li id="tab-presentation"><a href="/wordpress/wp-admin/options-general.php?page=wpsc-settings&amp;tab=presentation&amp;_wpnonce=65f84aecb4">Presentation</a></li>
	<li id="tab-admin"><a href="/wordpress/wp-admin/options-general.php?page=wpsc-settings&amp;tab=admin&amp;_wpnonce=00a9e4ddc5">Admin</a></li>

	<li id="tab-taxes"><a href="/wordpress/wp-admin/options-general.php?page=wpsc-settings&amp;tab=taxes&amp;_wpnonce=6448570d51">Taxes</a></li>
	<li id="tab-shipping"><a href="/wordpress/wp-admin/options-general.php?page=wpsc-settings&amp;tab=shipping&amp;_wpnonce=9cbee1eda4">Shipping</a></li>
	<li id="tab-gateway"><a href="/wordpress/wp-admin/options-general.php?page=wpsc-settings&amp;tab=gateway&amp;_wpnonce=d96b2d0649">Payments</a></li>
	<li id="tab-checkout"><a href="/wordpress/wp-admin/options-general.php?page=wpsc-settings&amp;tab=checkout&amp;_wpnonce=529747f6c4">Checkout</a></li>

</ul>
</div>
-->






</div><!--end of wrap-->
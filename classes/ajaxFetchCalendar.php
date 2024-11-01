<?php
if(isset($_POST['addMonths']) && isset($_POST['ABSPATH']) && isset($_POST['pluginFolderName'])){
	//define(ABSPATH, );
	include_once($_POST['ABSPATH'].'wp-content/plugins/'.$_POST['pluginFolderName'].'/classes/calendar.class.php');
	echo 'In calendar: Post addMonths was set';
	$calendar = new bookingCalendar;
	echo $calendar->displayCalendar('','1',$_POST['ABSPATH'], $_POST['pluginFolderName']);	
}
?>
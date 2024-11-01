<?php
if(isset($_POST['addMonths']) && isset($_POST['ABSPATH']) && isset($_POST['pluginFolderName'])){
	include_once($_POST['ABSPATH'].'wp-content/plugins/wp-easybooking/classes/calendar.class.php');
	$calendar = new bookingCalendar;
	$curMonth = 0;
	echo $calendar->displayCalendar($_POST['monthCounter'],$_POST['addMonths'],$_POST['ABSPATH'], $_POST['pluginFolderName'], $_POST['defineRange'], $_POST['startRange'], $_POST['endRange'], $_POST['startOperatingPeriod'], $_POST['endOperatingPeriod']);
	//echo 'Youre ajaxing';	
}

global $ebPluginFolderName;

class bookingCalendar{
	public function displayCalendar($monthCounter, $addMonths, $absolutePath, $ebPluginFolderName, $defineRange, $startRange, $endRange, $startOperatingPeriod, $endOperatingPeriod){		
		$date = time();
		$today = date('d', $date);
		$thismonth = date('m', $date);
		$thisyear = date('Y', $date);
		
		if(!$startOperatingPeriod){
			$startOperatingPeriod = date('Y-m-d', mktime(0, 0, 0, 1, 1, $thisyear));			
		}	
		$explodeStart = explode('-', $startOperatingPeriod);
		$operPerStartMonth = $explodeStart[1];
		$operPerStartDay = $explodeStart[2];
		
		if(!$endOperatingPeriod){
			$endOperatingPeriod = date('Y-m-d', mktime(0, 0, 0, 12, 30, $thisyear));		
		} 
		$explodeEnd = explode('-', $endOperatingPeriod);
		$operPerEndMonth = $explodeEnd[1];
		$operPerEndDay = $explodeEnd[2];

		$JSsetRangeFunc = '';
		$monthAction = 0;
			
		if($monthCounter == '' || !is_numeric($monthCounter)) $monthCounter = 0;
		
		if($addMonths == "add"){
			$monthCounter = $monthCounter+1;
		}
		if($addMonths == "sub"){
			$monthCounter = $monthCounter-1;
			
		}
		
		$startMonth = $thismonth;
		$notValidBeforeMonth = '';
		$notValidBeforeDay = '';
		$notValidAfterMonth = '';
		$notValidAfterDay = '';
		$dateSelectedStart = '';
		$dateSelectedEnd = '';
		
		if($thismonth < $operPerStartMonth) $startMonth = $operPerStartMonth;
		
		if($defineRange == 'start' && $startRange != ''){			
			$startRange = explode('/', $startRange);
			$startMonth = $startRange[1];	
			$dateSelectedStart = mktime(0,0,0, $startRange[1], $startRange[0], $startRange[2]); 
			if( $endRange != '' ){
				$endRange = explode('/', $endRange);
				$notValidAfterMonth = $endRange[1];	
				$notValidAfterDay = $endRange[0];
				$dateSelectedEnd = mktime(0,0,0, $endRange[1], $endRange[0], $endRange[2]);
			}
		}
		if($defineRange == 'end' && $endRange != ''){
			$endRange = explode('/', $endRange);
			$startMonth = $endRange[1];
			$startRange = explode('/', $startRange);	
			$notValidBeforeMonth = $startRange[1];
			$notValidBeforeDay = $startRange[0];
			$dateSelectedStart = mktime(0,0,0, $startRange[1], $startRange[0], $startRange[2]);
			$dateSelectedEnd = mktime(0,0,0, $endRange[1], $endRange[0], $endRange[2]);
		}
		if($defineRange == 'end' && $endRange == '' && $startRange != ''){
			$endRange = explode('/', $startRange);
			$startMonth = $endRange[1];	
			$notValidBeforeMonth = $startMonth;
			$notValidBeforeDay = $endRange[0];
		}		
		//===>Tyxaia vazw 1 pou einai mikrotero apo 29, opou exei provlhma o flevarhs kai mikrotero apo 31 pou exoun oloi oi mhnes oi opoioi den exoun 31 meres
		$next = date('Y-m-d', mktime(0, 0, 0, $startMonth + $monthCounter, 1, $thisyear));
		list($year, $month, $day) = explode('-', $next);
		//Lysh gia to 8ema pou exei o flevarhs gia tis 30 k 31 k kamia fora k 29.
		/*if($month == 03){
			$nextC = date('Y-m-d', mktime(0, 0, 0, $startMonth + $monthCounter + 1, $today, $thisyear));
			list($yearC, $monthC, $dayC) = explode('-', $nextC);
			if($monthC == 03) $month = 02;
		}*/
		
		$firstDay = mktime(0,0,0, $month, 1, $year);
		$title = date('F', $firstDay);
		$dayOfWeek = date('D', $firstDay);

		switch($dayOfWeek){
			case 'Sun': $blank = 0; break;
			case 'Mon': $blank = 1; break;
			case 'Tue': $blank = 2; break;
			case 'Wed': $blank = 3; break;
			case 'Thu': $blank = 4; break;
			case 'Fri': $blank = 5; break;
			case 'Sat': $blank = 6; break;  			
		}
		
		$daysInMonth = cal_days_in_month(0, $month, $year);
		echo '<div id="eb_bookingCalendar" class="eb_bookingCalendar">';
		
		?>
		<?php
		echo '
			<div id="eb_calDatesArea" class="eb_slide">
  				<div class="eb_inner">
				
				<table width="394">
					<tr>
						<td colspan ="7" style="width:100%">
							<table style="width:100%">
								<tr>
									<th class="eb_calendarNav">';
									
									if($year > $thisyear || ($year == $thisyear && $month > $thismonth)) echo '<a onclick="displayPreviousMonth()" class="eb_calendarNavLink" title="Previous month"><b class="eb_calendarNavLink"><</b></a>';
									else echo '<span style="color:#ccc"><</span>';
								
									echo '
									</th>
									<th class="eb_calendarMonthTitle">'.$title.' '. $year.'</th>
									<th class="eb_calendarNav"><a onclick="displayNextMonth()" title="Next month" class="eb_calendarNavLink"><b class="eb_calendarNavLink">></b></a></th>
								</tr>
							</table>
						</td>
					</tr>
					<tr>
						<td width="62"><strong>Sun</strong></td>
						<td width="62"><strong>Mon</strong></td>
						<td width="62"><strong>Tue</strong></td>
						<td width="62"><strong>Wed</strong></td>
						<td width="62"><strong>Thu</strong></td>
						<td width="62"><strong>Fri</strong></td>
						<td width="62"><strong>Sat</strong></td>
					</tr>';
				$dayCount = 1;
				echo '<tr>';				
				while($blank > 0){
					echo '<td></td>';
					$blank = $blank - 1;
					$dayCount++;	
				}
				
				$dayNum = 1;
				$eb_dateItemStyle = '';
				$hasJSFunction = '';

				while($dayNum <= $daysInMonth){
					$currentDateTime = mktime(0,0,0, $month, $dayNum, $year);
					if($month == $operPerStartMonth){
						if($dayNum >= $operPerStartDay){
							$eb_dateItemStyle = 'eb_validDateForItem';
						}else $eb_dateItemStyle = 'eb_invalidDateForItem';
					}
					if($month < $operPerStartMonth) $eb_dateItemStyle = 'eb_invalidDateForItem';
					if($month > $operPerStartMonth && $month < $operPerEndMonth){
						$eb_dateItemStyle = 'eb_validDateForItem';
					}
					if($month == $operPerEndMonth){
						if($dayNum <= $operPerEndDay) $eb_dateItemStyle = 'eb_validDateForItem';
						else $eb_dateItemStyle = 'eb_invalidDateForItem';  	
					}
					if($month == $thismonth && $dayNum == $today && $year == $thisyear) $eb_dateItemStyle = 'eb_calendarIsToday';
					if($month > $operPerEndMonth) $eb_dateItemStyle = 'eb_invalidDateForItem';
					if($notValidBeforeMonth != '' && $notValidBeforeDay != '') if($month < $notValidBeforeMonth || ($month == $notValidBeforeMonth && $dayNum <= $notValidBeforeDay)) $eb_dateItemStyle = 'eb_invalidDateForItem';
					if($notValidAfterMonth != '' && $notValidAfterDay != '') if($month > $notValidAfterMonth || ($month == $notValidAfterMonth && $dayNum >= $notValidAfterDay)) $eb_dateItemStyle = 'eb_invalidDateForItem';
					if($currentDateTime > $dateSelectedStart && $currentDateTime < $dateSelectedEnd) $eb_dateItemStyle = 'eb_selectedDate';
					if($currentDateTime == $dateSelectedStart || $currentDateTime == $dateSelectedEnd) $eb_dateItemStyle = 'eb_selectedDateLimit';
					if($month <= $thismonth && $dayNum < $today && $year <= $thisyear) $eb_dateItemStyle = 'eb_invalidDateForItem';
					
					echo '<td class = "'.$eb_dateItemStyle.'"';
					
					if($eb_dateItemStyle == 'eb_validDateForItem' || $eb_dateItemStyle == 'eb_selectedDate' || $eb_dateItemStyle == 'eb_calendarIsToday'){
						if($defineRange == "start") $JSsetRangeFunc = 'setStartRange';
						if($defineRange == "end") $JSsetRangeFunc = 'setEndRange';
						?>
						onclick = "<?php echo $JSsetRangeFunc; ?>('<?php echo $dayNum.'/'.$month.'/',$year ;?>');"
						<?php
					}
					echo '>'.$dayNum.'</td>';
					$dayNum++;
					$dayCount++;
					if($dayCount > 7){
						echo '</tr><tr>';
						$dayCount = 1;	
					}
				}	
				
				while($dayCount > 1 && $dayCount <= 7){
					echo '<td></td>';
					$dayCount++;	
				}			
				
		echo'	</tr>			
			</table>
			
			
			</div>
			</div>
		</div>';	
			
			?>
			<script type="text/javascript" >	
				function setStartRange(startRange){
					jQuery('#eb_dateRangeStart').val(startRange);
					jQuery('#eb_bookingCalendar').hide();
					
				}
				function setEndRange(startRange){
					jQuery('#eb_dateRangeEnd').val(startRange);
					jQuery('#eb_bookingCalendar').hide();
					
				}
				function displayNextMonth(){
					var absolutePath = jQuery("#abs_path").val();
					jQuery.ajax({
						type: "POST",
  						url: "../wp-content/plugins/<?php echo $ebPluginFolderName; ?>/classes/calendar.class.php",
  						data: "addMonths=add&ABSPATH="+absolutePath+"&pluginFolderName=<?php echo $ebPluginFolderName; ?>&monthCounter=<?php echo $monthCounter;?>&defineRange=<?php echo $defineRange ;?>&startRange="+jQuery("#eb_dateRangeStart").val()+"&endRange="+jQuery("#eb_dateRangeEnd").val()+"&startOperatingPeriod="+jQuery('#startOperatingPeriod').val()+"&endOperatingPeriod="+jQuery('#endOperatingPeriod').val(),
  						success: function(resp){
    						jQuery("#eb_calendarMainContainer").html(resp);
  						}
					});	
				}	
				function displayPreviousMonth(){
					var absolutePath = jQuery("#abs_path").val();
					jQuery.ajax({
						type: "POST",
  						url: "../wp-content/plugins/<?php echo $ebPluginFolderName; ?>/classes/calendar.class.php",
  						data: "addMonths=sub&ABSPATH="+absolutePath+"&pluginFolderName=<?php echo $ebPluginFolderName; ?>&monthCounter=<?php echo $monthCounter;?>&defineRange=<?php echo $defineRange ;?>&startRange="+jQuery("#eb_dateRangeStart").val()+"&endRange="+jQuery("#eb_dateRangeEnd").val()+"&startOperatingPeriod="+jQuery('#startOperatingPeriod').val()+"&endOperatingPeriod="+jQuery('#endOperatingPeriod').val(),
  						success: function(resp){
    						jQuery("#eb_calendarMainContainer").html(resp);
  						}
					});	
				}
				
				
			</script>
			<?php
			
	}	
}
?>

<script type="text/javascript" >
	jQuery(document).ready(function() {
		/*jQuery('#eb_dateRangeStart').click(function(event) {
			event.stopPropagation();
			displayCalendar('start');
		});

		jQuery('#eb_dateRangeEnd').click(function(event) {
			event.stopPropagation();
			displayCalendar('end');	
  		});*/
	

	});//end of document ready
				
function displayCalendar(defineRange){
	jQuery("#switchStartAndEndDates").val(defineRange);
	/*if(defineRange == "end" && jQuery('#eb_dateRangeStart').val() == '') {
		return;
	}*/
	if(defineRange == "start" || defineRange == "end"){
		var absolutePath = jQuery('#abs_path').val();
		jQuery.ajax({
			type: "POST",
  			url: "../wp-content/plugins/wp-easybooking/classes/calendar.class.php",
  			data: "addMonths=&ABSPATH="+absolutePath+"&pluginFolderName=wp-easybooking&monthCounter=<?php echo $monthCounter;?>&defineRange="+defineRange+"&startRange="+jQuery('#eb_dateRangeStart').val()+"&endRange="+jQuery('#eb_dateRangeEnd').val()+"&startOperatingPeriod="+jQuery('#startOperatingPeriod').val()+"&endOperatingPeriod="+jQuery('#endOperatingPeriod').val(),
  			success: function(resp){  
  				//alert('yoo: '+resp);				
    			jQuery("#eb_calendarMainContainer").html(resp);
  			}
  			
		});
			
	}
	if(defineRange == "start"){
		var cal_pos   = jQuery("#eb_dateRangeStart").position();
		var cal_height = jQuery("#eb_dateRangeStart").height();
		var o = document.getElementById('eb_dateRangeStart');
		var t = o.offsetTop;
		cal_height = cal_height + t + 8;
		jQuery('#eb_calendarMainContainer').css({ "left": (cal_pos.left) + "px", "top":(cal_height) + "px"});
		if(jQuery('#eb_calendarMainContainer').css("display") == "block") jQuery('#eb_calendarMainContainer').hide();
		else jQuery('#eb_calendarMainContainer').show();
	}
	if(defineRange == "end"){
		
		if(jQuery('#eb_dateRangeStart').val() != ''){
			var cal_pos   = jQuery("#eb_dateRangeEnd").position();
			var cal_height = jQuery("#eb_dateRangeEnd").height();
			var o = document.getElementById('eb_dateRangeEnd');
			var t = o.offsetTop;
			cal_height = cal_height + t + 8;
			jQuery('#eb_calendarMainContainer').css({ "left": (cal_pos.left) + "px", "top":(cal_height) + "px"});
			if(jQuery('#eb_calendarMainContainer').css("display") == "block") jQuery('#eb_calendarMainContainer').hide();
			else jQuery('#eb_calendarMainContainer').show();
		}	
	}
}
//Gia na kleinie to calendar otan patame eksw
jQuery(document).click(function(e) {
	if(e.target.className !== "eb_dateRangeArea" && e.target.className !== "eb_slide" && e.target.className !== "eb_inner" && e.target.className !== "eb_calendarMonthTitle" && e.target.className !== "eb_calendarNav" && e.target.className !== "eb_calendarNavLink")
    {
      if(jQuery('#eb_calendarMainContainer').css("display") == "block") jQuery('#eb_calendarMainContainer').hide();
    }
});



</script>
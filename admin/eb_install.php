<?php
global $wpdb;
global $table_prefix;
$locationResultPageId = '';

if(get_option('eb-location-result-page') == ''){
	
	global $user_ID;
	$new_post = array(
		'post_title' => 'Resort Search Results',
    	'post_content' => '[easy-booking-resort-search-results]',
    	'post_status' => 'publish',
    	'post_date' => date('Y-m-d H:i:s'),
    	'post_author' => $user_ID,
    	'post_type' => 'post',
    	'comment_status' => 'closed',
    	'ping_status' => 'closed'
	);
	$post_id = wp_insert_post($new_post);
	if($post_id != ''){
		$wpdb->query("update ".$table_prefix."posts set post_type='page' where ID =".$post_id);
		add_option('eb-location-result-page', $post_id);
	}
	

}

if(get_option('eb-view-resort') == ''){
	
	global $user_ID;
	$new_post = array(
		'post_title' => 'View Resort',
    	'post_content' => '[easy-booking-view-resort]',
    	'post_status' => 'publish',
    	'post_date' => date('Y-m-d H:i:s'),
    	'post_author' => $user_ID,
    	'post_type' => 'post',
    	'comment_status' => 'closed',
    	'ping_status' => 'closed'
	);
	$post_id = wp_insert_post($new_post);
	if($post_id != ''){
		$wpdb->query("update ".$table_prefix."posts set post_type='page' where ID =".$post_id);
		add_option('eb-view-resort', $post_id);
	}
	

}

if(get_option('eb-booking-review') == ''){
	global $user_ID;
	$new_post = array(
		'post_title' => 'Booking review',
    	'post_content' => '[easy-booking-review-booking]',
    	'post_status' => 'publish',
    	'post_date' => date('Y-m-d H:i:s'),
    	'post_author' => $user_ID,
    	'post_type' => 'post',
    	'comment_status' => 'closed',
    	'ping_status' => 'closed'
	);
	$post_id = wp_insert_post($new_post);
	if($post_id != ''){
		$wpdb->query("update ".$table_prefix."posts set post_type='page' where ID =".$post_id);
		add_option('eb-booking-review', $post_id);
	}
	

}

if(get_option('eb-booking-success') == ''){
	global $user_ID;
	$new_post = array(
		'post_title' => 'Booking completed',
    	'post_content' => '[easy-booking-success]',
    	'post_status' => 'publish',
    	'post_date' => date('Y-m-d H:i:s'),
    	'post_author' => $user_ID,
    	'post_type' => 'post',
    	'comment_status' => 'closed',
    	'ping_status' => 'closed'
	);
	$post_id = wp_insert_post($new_post);
	if($post_id != ''){
		$wpdb->query("update ".$table_prefix."posts set post_type='page' where ID =".$post_id);
		add_option('eb-booking-success', $post_id);
	}
	

}


if(get_option('eb-view-bookings') == ''){
	global $user_ID;
	$new_post = array(
		'post_title' => 'Bookings',
    	'post_content' => '[easy-booking-view-bookings]',
    	'post_status' => 'publish',
    	'post_date' => date('Y-m-d H:i:s'),
    	'post_author' => $user_ID,
    	'post_type' => 'post',
    	'comment_status' => 'closed',
    	'ping_status' => 'closed'
	);
	$post_id = wp_insert_post($new_post);
	if($post_id != ''){
		$wpdb->query("update ".$table_prefix."posts set post_type='page' where ID =".$post_id);
		add_option('eb-view-bookings', $post_id);
	}
	

}

if(get_option('eb-terms') == ''){
	global $user_ID;
	$new_post = array(
		'post_title' => 'Terms and conditions',
    	'post_content' => 'Please type your terms and conditions here',
    	'post_status' => 'publish',
    	'post_date' => date('Y-m-d H:i:s'),
    	'post_author' => $user_ID,
    	'post_type' => 'post',
    	'comment_status' => 'closed',
    	'ping_status' => 'closed'
	);
	$post_id = wp_insert_post($new_post);
	if($post_id != ''){
		$wpdb->query("update ".$table_prefix."posts set post_type='page' where ID =".$post_id);
		add_option('eb-terms', $post_id);
	}
	

}

if($wpdb->get_var("SHOW TABLES LIKE eb_bushelpvals") == $facilitiesTable_name) {

}
else{
		$createHelpSql = 'CREATE TABLE IF NOT EXISTS eb_bushelpvals (
	  	id int(11) NOT NULL AUTO_INCREMENT,
	  	bID int(11),
  		stars int(11),
  		min_price DOUBLE NOT NULL,
  		max_price DOUBLE NOT NULL,
  		PRIMARY KEY (id)
		) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=83
		';
		$result = mysql_query($createHelpSql)or die(mysql_error());
}
?>
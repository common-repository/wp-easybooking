<?php
/**
 * Plugin Name: Easybooking Widget
 * Plugin URI: http://wp-easybooking.com
 * Description: This widget is used for accommodation search and to perform the actual booking 
 * Version: 1.0.3
 * Author: Panos Lyrakis
 * Author URI: http://wp-easybooking.com
 *
 */

/**
 * Add function to widgets_init that'll load our widget.
 * @since 0.1
 */
add_action( 'widgets_init', 'eb_load_widgets' );

/**
 * Register our widget.
 * 'EasyBooking_Widget' is the widget class used below.
 *
 * @since 0.1
 */
function eb_load_widgets() {
	register_widget( 'EasyBooking_Widget' );
}

class EasyBooking_Widget extends WP_Widget {

	/**
	 * Widget setup.
	 */
	function EasyBooking_Widget() {
		/* Widget settings. */
		$widget_ops = array( 'classname' => 'easybooking', 'description' => __('Booking widget that allows users to search for available rooms in a specific date range..', 'easybooking') );

		/* Widget control settings. */
		$control_ops = array( 'id_base' => 'example-widget' );

		/* Create the widget. */
		$this->WP_Widget( 'example-widget', __('Booking Search', 'easybooking'), $widget_ops, $control_ops );
	}

	/**
	 * How to display the widget on the screen.
	 */
	function widget( $args, $instance ) {
		extract( $args );

		/* Our variables from the widget settings. */
		$title = apply_filters('widget_title', $instance['title'] );
		$name = $instance['name'];
		$sex = $instance['sex'];
		$show_sex = isset( $instance['show_sex'] ) ? $instance['show_sex'] : false;

		/* Before widget (defined by themes). */
		echo $before_widget;

		/* Display the widget title if one was input (before and after defined by themes). */
		if ( $title )
			printf( $before_title . __('%1$s', 'example') . $after_title, $title );
		
		$eb_folder = PLUGINDIR . '/' . dirname(plugin_basename(__FILE__)).'/widgets';

		include($eb_folder.'/search_form.php');
		
		echo $after_widget;
	}

	/**
	 * Update the widget settings.
	 */
	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;

		/* Strip tags for title and name to remove HTML (important for text inputs). */
		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['name'] = strip_tags( $new_instance['name'] );

		return $instance;
	}

	/**
	 * Displays the widget settings controls on the widget panel.
	 * Make use of the get_field_id() and get_field_name() function
	 * when creating form elements. This handles the confusing stuff.
	 */
	function form( $instance ) {

		/* Set up some default widget settings. */
		$defaults = array( 'title' => __('Booking Search', 'example'), 'name' => __('Panos', 'example'), 'sex' => 'male', 'show_sex' => true );
		$instance = wp_parse_args( (array) $instance, $defaults ); ?>

		<!-- Widget Title: Text Input -->
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e('Title:', 'hybrid'); ?></label>
			<input id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo $instance['title']; ?>" style="width:100%;" />
		</p>

		<!-- Your Name: Text Input -->
		<p>
			<label for="<?php echo $this->get_field_id( 'name' ); ?>"><?php _e('Your Name:', 'example'); ?></label>
			<input id="<?php echo $this->get_field_id( 'name' ); ?>" name="<?php echo $this->get_field_name( 'name' ); ?>" value="<?php echo $instance['name']; ?>" style="width:100%;" />
		</p>

	<?php
	}
}

//Enqueue date picker ui
function datepicker_in_init() {
	$pluginfolder = get_bloginfo('url') . '/' . PLUGINDIR . '/' . dirname(plugin_basename(__FILE__)).'/js';
	wp_enqueue_script('jquery');
	wp_enqueue_script('jquery-ui-core');
	wp_enqueue_script('jquery-tinyscrollbar', $pluginfolder . '/jquery.tinyscrollbar.min.js', array('jquery', 'jquery-ui-core') );
	wp_enqueue_script('jquery-ui-datepicker', $pluginfolder . '/jquery.ui.datepicker.min.js', array('jquery', 'jquery-ui-core') );
	wp_enqueue_style('jquery.ui.theme', $pluginfolder . '/smoothness/jquery-ui-1.8.18.custom.css');
}
add_action('widgets_init', 'datepicker_in_init');

function widget_register_CSSinHead() {
	$siteurl = get_option('siteurl');
	$url = $siteurl . '/wp-content/plugins/' . basename(dirname(__FILE__)) . '/eb_widgetStyle.css';
	echo "<link rel='stylesheet' type='text/css' href='$url' />\n";
}
add_action('wp_head', 'widget_register_CSSinHead');

//Enqueue thickbox
add_action('widgets_init', 'myplugin_thickboxX');
function myplugin_thickboxX() {
	if (! is_admin()) {
		wp_enqueue_script('thickbox', null,  array('jquery'));
		wp_enqueue_style('thickbox.css', '/'.WPINC.'/js/thickbox/thickbox.css', null, '1.0');
	}
}
?>

<?php
/**
Plugin Name: Visual Verse of the Day Widget
Description: Plugin for adding a widget for the Visual Verse of the Day from http://visualverse.thecreationspeaks.com/
Version: 1.3
Author: Karl Kranich
Author URI: http://karl.kranich.org/visual-verse-widget
*/
// Creating the widget
class vv_widget extends WP_Widget {
	function __construct() {
		parent::__construct('vv_widget',__('Visual Verse of the Day Widget', 'vv_widget_domain'),
		array( 'description' => __( 'Display the latest image and verse reference from Visual Verse of the Day', 'vv_widget_domain' ), ));
	}
 
	// Creating widget front-end
	// This is where the action happens
	public function widget( $args, $instance ) {
		// Register the styles
    	wp_register_style('vv-widget-styles', plugins_url('/css/vv-widget-styles.css', __FILE__ ), false, null);
    	wp_enqueue_style('vv-widget-styles');
    	// Get the width from saved settings
		$width = apply_filters( 'widget_width', $instance['width'] );
		$width = trim($width);
		if ($width == '') { $width = '100%'; }
		if (substr($width, -1) != ';') { $width .= ';'; }
		// before and after widget arguments are defined by themes
		echo $args['before_widget'];
		// main output here
		$rssfeed = simplexml_load_file('http://visualverse.thecreationspeaks.com/feed/');

		$i = 0;
		while ($i < 5) {
			$vv_title = $rssfeed->channel[0]->item[$i]->title;
			$vv_link = $rssfeed->channel[0]->item[$i]->link;
			if (substr($vv_title,0,4) != 'Blog') break;
			$i++;
		}

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_AUTOREFERER, TRUE);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_URL, $vv_link);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, FALSE);       
		$page_data = curl_exec($ch);
		curl_close($ch);

		preg_match('@<div class="entry-content.*<img .*src="(http://visual\S*\.jpg)"@s', $page_data, $matches);
		$img_link = $matches[1];
		preg_match('/<meta name="description" content="([^&]*)&quot/', $page_data, $matches);
		$verse_ref = $matches[1];
		?>
	  	<div id="vv-widget" style="width: <?php echo $width; ?>">
	  		<h3 class="widget-title" id="vv-title">Visual Verse of the Day</h3>
	  		<a href="http://visualverse.thecreationspeaks.com/" target="_blank"><img src="<?php echo $img_link; ?>"></a>
	      <p id="vv-picTitle"><?php echo $verse_ref; ?></p>
	  	</div>
	  	<?php

		echo $args['after_widget'];
	}
	         
	// Widget Backend
	public function form( $instance ) {
		if ( isset( $instance[ 'width' ] ) ) {
		$width = $instance[ 'width' ];
	} else {
		$width = __( '', 'vv_widget_domain' );
	}

	// Widget admin form
	?>
	<p>
	<label for="<?php echo $this->get_field_id( 'width' ); ?>"><?php _e( 'Width: Use CSS-type syntax, like "100px;" or leave empty for 100%'); ?></label>
	<input class="widefat" id="<?php echo $this->get_field_id( 'width' ); ?>" name="<?php echo $this->get_field_name( 'width' ); ?>" type="text" value="<?php echo esc_attr( $width ); ?>" />
	</p>
	<?php
	}
	     
	// Updating widget replacing old instances with new
	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['width'] = ( ! empty( $new_instance['width'] ) ) ? strip_tags( $new_instance['width'] ) : '';
		return $instance;
	}
} // Class wpb_widget ends here

// Register and load the widget
function vv_load_widget() {
    register_widget( 'vv_widget' );
}
add_action( 'widgets_init', 'vv_load_widget' );

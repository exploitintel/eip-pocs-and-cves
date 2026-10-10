<?php

/* 
 * Customize the list of groups in your widget area
 * 
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'Profilegrid_User_login' ) ) :
    
class Profilegrid_User_login extends WP_Widget {
    
    /**
     * Plugin identifiers used by shared helpers included in the widget view.
     */
    private $profile_magic;
    private $version;
    
    /*
     *  registers basic widget information.
     */
    public function __construct() {
        $this->profile_magic = 'profilegrid-user-profiles-groups-and-communities';
        $this->version       = defined( 'PROGRID_PLUGIN_VERSION' ) ? PROGRID_PLUGIN_VERSION : '1.0.0';
        $widget_options = array(
           'classname' => 'pg_user_login',
          'description' => esc_html__('Widget for Login and Display Short Profile ','profilegrid-user-profiles-groups-and-communities'),
        );
        parent::__construct( 'pg_user_login', esc_html__('ProfileGrid Login','profilegrid-user-profiles-groups-and-communities'), $widget_options );
    }
    
    /*
     * used to add setting fields to the widget which will be displayed in the WordPress admin area.
     */
    public function form($instance)
    {
        $title = ! empty( $instance['title'] ) ? $instance['title'] : '';
        $pg_show_dp = ! empty( $instance['pg_show_dp'] ) ? $instance['pg_show_dp'] : '';
        $pg_show_group_name = ! empty( $instance['pg_show_group_name'] ) ? $instance['pg_show_group_name'] : '';
        $pg_show_group_icon = ! empty( $instance['pg_show_group_icon'] ) ? $instance['pg_show_group_icon'] : '';
        ?>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id( 'title' )); ?>"><?php esc_html_e('Title:','profilegrid-user-profiles-groups-and-communities');?>
                <input type="text" class="widefat" id="<?php echo esc_attr($this->get_field_id( 'title' )); ?>" name="<?php echo esc_attr($this->get_field_name( 'title' )); ?>" value="<?php echo esc_attr( $title ); ?>" />
            </label>
        </p>
        <p>
            <input value="1" <?php checked($pg_show_dp , '1' ); ?> class="checkbox widefat" type="checkbox" id="<?php echo esc_attr($this->get_field_id( 'pg_show_dp' )); ?>" name="<?php echo esc_attr($this->get_field_name( 'pg_show_dp' )); ?>"> <label for="<?php echo esc_attr($this->get_field_id( 'pg_show_dp' )); ?>"><?php esc_html_e('Display Profile Picture','profilegrid-user-profiles-groups-and-communities');?></label>
            <br>
             <input value="1" <?php checked($pg_show_group_name , '1'); ?>class="checkbox widefat" type="checkbox" id="<?php echo esc_attr($this->get_field_id( 'pg_show_group_name' )); ?>" name="<?php echo esc_attr($this->get_field_name( 'pg_show_group_name' )); ?>"> <label for="<?php echo esc_attr($this->get_field_id( 'pg_show_group_name' )); ?>"><?php esc_html_e('Display Group Name','profilegrid-user-profiles-groups-and-communities');?></label>
            <br>
            <input value="1" <?php checked($pg_show_group_icon , '1' ); ?> class="checkbox widefat" type="checkbox" id="<?php echo esc_attr($this->get_field_id( 'pg_show_group_icon' )); ?>" name="<?php echo esc_attr($this->get_field_name( 'pg_show_group_icon' )); ?>"> <label for="<?php echo esc_attr($this->get_field_id( 'pg_show_group_icon' )); ?>"><?php esc_html_e('Display Group Picture','profilegrid-user-profiles-groups-and-communities');?></label>
            <br>
        </p>
        <?php
    }
    
    /*
     * used to view to frontend 
     */
    
    public function widget($args,$instance)
    {
        $title = (isset($instance['title']))?apply_filters( 'widget_title', $instance['title'] ):'';
        $pg_show_dp = (isset($instance['pg_show_dp']))?$instance['pg_show_dp']:'';
        $pg_show_group_name = (isset($instance['pg_show_group_name']))?$instance['pg_show_group_name']:'';
        $pg_show_group_icon = (isset($instance['pg_show_group_icon']))?$instance['pg_show_group_icon']:'';
        $dbhandler = new PM_DBhandler;
        //var_dump($pg_show_dp);
        // before and after widget arguments are defined by themes
        echo wp_kses_post($args['before_widget']);
        if ( ! empty( $title ) )
        echo wp_kses_post($args['before_title'] . $title . $args['after_title']); 
        // This is where you run the code and display the output
        require 'login-form.php';
   
        echo wp_kses_post($args['after_widget']);
    }

    /*
     * Update the information in the WordPress database      * 
     */
    public function update( $new_instance, $old_instance ) {
        $instance = array();
        $instance['title'] = ( ! empty( $new_instance['title'] ) ) ? wp_strip_all_tags( $new_instance['title'] ) : '';
        $instance['pg_show_dp'] = ( ! empty( $new_instance['pg_show_dp'] ) ) ? wp_strip_all_tags( $new_instance['pg_show_dp'] ) : '';
        $instance['pg_show_group_name'] = ( ! empty( $new_instance['pg_show_group_name'] ) ) ? wp_strip_all_tags( $new_instance['pg_show_group_name'] ) : '';
        $instance['pg_show_group_icon'] = ( ! empty( $new_instance['pg_show_group_icon'] ) ) ? wp_strip_all_tags( $new_instance['pg_show_group_icon'] ) : '';
        
        return $instance;
    }
}
endif;

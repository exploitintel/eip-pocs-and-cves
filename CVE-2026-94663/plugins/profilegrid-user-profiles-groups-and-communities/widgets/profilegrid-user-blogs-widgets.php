<?php

/* 
 * Customize the list of groups in your widget area
 * 
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'Profilegrid_User_Blogs' ) ) :
    
class Profilegrid_User_Blogs extends WP_Widget {
    
    /*
     *  registers basic widget information.
     */
    public function __construct() {
        $widget_options = array(
           'classname' => 'pg_user_blogs',
          'description' => esc_html__('The widget will display User Blog posts.','profilegrid-user-profiles-groups-and-communities'),
        );
        parent::__construct( 'pg_user_blogs', esc_html__('Profilegrid User Blogs','profilegrid-user-profiles-groups-and-communities'), $widget_options );
    }
    
    /*
     * used to add setting fields to the widget which will be displayed in the WordPress admin area.
     */
    public function form($instance)
    {
        $dbhandler = new PM_DBhandler;
        $title = ! empty( $instance['title'] ) ? $instance['title'] : '';
        $gid = ! empty( $instance['gid'] ) ? $instance['gid'] : '';
        //print_r($instance);
        $post_counts = ! empty( $instance['post_counts'] ) ? $instance['post_counts'] : '5';
        $groups = $dbhandler->get_all_result('GROUPS');
        ?>
        
   
        <p>
            <label for="<?php echo esc_attr($this->get_field_id( 'title' )); ?>"><?php esc_html_e('Title:','profilegrid-user-profiles-groups-and-communities');?>
                <input type="text" class="widefat" id="<?php echo esc_attr($this->get_field_id( 'title' )); ?>" name="<?php echo esc_attr($this->get_field_name( 'title' )); ?>" value="<?php echo esc_attr( $title ); ?>" />
            </label>
        </p>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id( 'gid' )); ?>"><?php esc_html_e('Display User Blogs From:','profilegrid-user-profiles-groups-and-communities');?></label>
            <select class="widefat" id="<?php echo esc_attr($this->get_field_id( 'gid' )); ?>" name="<?php echo esc_attr($this->get_field_name( 'gid' )); ?>">
               <option value="all" <?php if($gid=='all'){echo 'selected';} ?>><?php esc_html_e('All Group','profilegrid-user-profiles-groups-and-communities'); ?></option>
                <?php foreach($groups as $group):?>
                <option value="<?php echo esc_attr($group->id); ?>" <?php if($gid==$group->id){echo 'selected';} ?>><?php echo esc_html($group->group_name); ?></option> 
                <?php endforeach;?>
            </select>
        </p>
         <p>
             <label for="<?php echo esc_attr($this->get_field_id( 'post_counts' )); ?>"><?php esc_html_e('Number of Posts to Show:','profilegrid-user-profiles-groups-and-communities');?>
                 <input type="number" class="widefat" id="<?php echo esc_attr($this->get_field_id( 'post_counts' )); ?>" name="<?php echo esc_attr($this->get_field_name( 'post_counts' )); ?>" value="<?php echo esc_attr( $post_counts ); ?>" />
            </label>
            
        </p>
      <?php
        
    
    }
    
    /*
     * used to view to frontend 
     */
    
    public function widget($args,$instance)
    {
        $title = (isset($instance['title']))?apply_filters( 'widget_title', $instance['title'] ):'';
        $gid = (isset($instance['gid']))?$instance['gid']:'';
        $post_counts = (isset($instance['post_counts']))?$instance['post_counts']:'';
        
        $dbhandler = new PM_DBhandler;
        if($gid=='all')
        {
            $meta_query = array('relation'=>'AND',array('key' => 'pm_group','value' => '','compare' => '!='));
        }
        else
        {
            $meta_query = array('relation'=>'AND',array('key'=> 'pm_group','value'   => sprintf(':"%s";',$gid),'compare' => 'like'));
        }
        
        $users =  $dbhandler->pm_get_all_users('',$meta_query);
        $author_ids = array('0');
        foreach($users as $user)
        {
           array_push($author_ids, $user->ID);
        }
        
        $arg = array(
        'post_type'        => 'profilegrid_blogs',
        'posts_per_page' => $post_counts,
        'post_status' => 'publish,',
        'author__in' => $author_ids
        );
        
       
        $posts = get_posts( $arg );
        // before and after widget arguments are defined by themes
        echo wp_kses_post($args['before_widget']);
        if ( ! empty( $title ) )
        echo wp_kses_post($args['before_title'] . $title . $args['after_title']); 
        // This is where you run the code and display the output
 
        if (!empty($posts)):
            echo '<ul>';
            foreach ($posts as $post):
                echo '<li><a href="' . esc_url(get_permalink($post->ID)) . '">'.esc_html($post->post_title).'</a> </li> ';
            endforeach;
            echo '</ul>';
        else:
        ?>
            <div class="pm-slab"><?php esc_html_e("Group Members does not create any posts yet",'profilegrid-user-profiles-groups-and-communities'); ?></div>
        <?php
        endif;
        
        echo wp_kses_post($args['after_widget']);
    }

    /*
     * Update the information in the WordPress database      * 
     */
    public function update( $new_instance, $old_instance ) {
        $instance = array();
        $instance['title'] = ( ! empty( $new_instance['title'] ) ) ? wp_strip_all_tags( $new_instance['title'] ) : '';
        $instance['gid'] = ( ! empty( $new_instance['gid'] ) ) ? wp_strip_all_tags( $new_instance['gid'] ) : '';
        $instance['post_counts'] = ( ! empty( $new_instance['post_counts'] ) ) ? wp_strip_all_tags( $new_instance['post_counts'] ) : '5';
        //print_r($new_instance);die;
        return $instance;
    }
}
endif;


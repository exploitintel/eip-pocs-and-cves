<?php

/* 
 * Customize the list of groups in your widget area
 * 
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'Profilegrid_Groups_Menu' ) ) :
    
class Profilegrid_Groups_Menu extends WP_Widget {
    
    /*
     *  registers basic widget information.
     */
    public function __construct() {
        $widget_options = array(
           'classname' => 'pg_groups_menu',
          'description' => esc_html__('List ProfileGrid Groups','profilegrid-user-profiles-groups-and-communities'),
        );
        parent::__construct( 'pg_groups_menu', esc_html__('ProfileGrid Groups Menu','profilegrid-user-profiles-groups-and-communities'), $widget_options );
    }
    
    /*
     * used to add setting fields to the widget which will be displayed in the WordPress admin area.
     */
    public function form($instance)
    {
        $dbhandler = new PM_DBhandler;
        $title = ! empty( $instance['title'] ) ? $instance['title'] : '';
        
        $group_menu = get_option('pg_group_menu');
        $group_menu = is_array( $group_menu ) ? $group_menu : array();
        $group_list = isset( $instance['group_list'] ) ? (array) $instance['group_list'] : maybe_unserialize( get_option( 'pg_group_list' ) );
        $group_list = is_array( $group_list ) ? $group_list : array();
        $group_list = array_filter( array_map( 'strval', $group_list ) );
        $pg_group_icon = isset( $instance['icon'] ) ? $instance['icon'] : get_option( 'pg_group_icon' );
        $all_groups = $dbhandler->get_all_result( 'GROUPS', array( 'id', 'group_name' ), 1, $result_type = 'results', $offset = 0, $limit = false, $sort_by = null, $descending = false, $additional = '', $output = 'ARRAY_A', $distinct = false );
        $groups_by_id = array();
        if ( ! empty( $all_groups ) ) {
            foreach ( $all_groups as $group_row ) {
                $groups_by_id[ (string) $group_row['id'] ] = $group_row;
            }
        }
        
        if($group_menu)
        {
            $changed_list = array();
            foreach($group_menu as $key=>$val)
            {               
                $val = (string) $val;
                if ( isset( $groups_by_id[ $val ] ) ) {
                    $changed_list[ $key ] = array(
                        'id' => $groups_by_id[ $val ]['id'],
                        'group_name' => $groups_by_id[ $val ]['group_name'],
                    );
                }
            }
            $group_menu = $changed_list;
        } elseif ( ! empty( $groups_by_id ) ) {
            $group_menu = array_values( $groups_by_id );
        }
        ?>
        <div class="pg-group-menu-widgets">
            <p>
                <label for="<?php echo esc_attr($this->get_field_id( 'title' )); ?>"><?php esc_html_e('Title:','profilegrid-user-profiles-groups-and-communities');?>
                    <input type="text" class="widefat" id="<?php echo esc_attr($this->get_field_id( 'title' )); ?>" name="<?php echo esc_attr($this->get_field_name( 'title' )); ?>" value="<?php echo esc_attr( $title ); ?>" />
                </label>
            </p>
            <p>
                <label><?php esc_html_e('Display Group Icon/Badge:','profilegrid-user-profiles-groups-and-communities');?></label>
                <input type="checkbox" <?php checked( $pg_group_icon, 'yes'); ?> id="<?php echo esc_attr( $this->get_field_id( 'icon' ) ); ?>" class="pg-group-icon-toggle" data-pg-group-icon="1" name="<?php echo esc_attr( $this->get_field_name( 'icon' ) ); ?>" value="yes" />
            </p>
            <ul class="pm_sortable_groups">
            <?php
            if (!empty($group_menu)):
                foreach ($group_menu as $group):
            ?>
                <li id="<?php echo esc_attr($group['id']); ?>">
                    <div class="pm-custom-field-page-slab-widget pm-dbfl">
                        <div class="pg-widget-drag-handle"><span class="dashicons dashicons-menu"></span></div>
                        <div class="pm-group-buttons"><input type="checkbox" <?php checked( in_array( (string) $group['id'], $group_list, true ), true ); ?> name="<?php echo esc_attr( $this->get_field_name( 'group_list' ) ); ?>[]" value="<?php echo esc_attr($group['id']); ?>"/></div>
                        <div class="pm-group-info"><?php echo esc_html($group['group_name']); ?></div>
                    </div>
                </li>
            <?php
                endforeach;
            else:
            ?>
                <li>
                    <div class="pm-slab"><?php esc_html_e("You haven't created any Profile Groups yet.",'profilegrid-user-profiles-groups-and-communities'); ?></div>
                </li>
            <?php
            endif;
            ?>
            </ul>
        </div>
        <?php 
    
    }
    
    /*
     * used to view to frontend 
     */
    
    public function widget($args,$instance)
    {
        $dbhandler = new PM_DBhandler;
        $pmrequests = new PM_request;   
        if(isset($instance['title']))
        {
            $title = apply_filters( 'widget_title', $instance['title'] );
        }
        else
        {
            $title = '';
        }
        $group_menu = get_option('pg_group_menu');
        $group_menu = is_array( $group_menu ) ? $group_menu : array();
        $group_list = isset( $instance['group_list'] ) ? (array) $instance['group_list'] : maybe_unserialize( get_option( 'pg_group_list' ) );
        $group_list = is_array( $group_list ) ? $group_list : array();
        $group_list = array_filter( array_map( 'strval', $group_list ) );
        $pg_group_icon = isset( $instance['icon'] ) ? $instance['icon'] : get_option( 'pg_group_icon' );
        $path = plugins_url( '/images/widget-default-group.png', __FILE__ );
        $pg_group_list = array();
        $all_groups = $dbhandler->get_all_result( 'GROUPS', array( 'id', 'group_name', 'group_icon' ), 1, $result_type = 'results', $offset = 0, $limit = false, $sort_by = null, $descending = false, $additional = '', $output = 'ARRAY_A', $distinct = false );
        $groups_by_id = array();
        if ( ! empty( $all_groups ) ) {
            foreach ( $all_groups as $group_row ) {
                $groups_by_id[ (string) $group_row['id'] ] = $group_row;
            }
        }

        if ( ! empty( $group_menu ) ) {
            foreach ( $group_menu as $key => $val ) {
                $val = (string) $val;
                if ( isset( $groups_by_id[ $val ] ) ) {
                    $pg_group_list[ $key ] = (object) array(
                        'id' => $groups_by_id[ $val ]['id'],
                        'group_name' => $groups_by_id[ $val ]['group_name'],
                        'group_icon' => $groups_by_id[ $val ]['group_icon'],
                    );
                }
            }
        } elseif ( ! empty( $groups_by_id ) ) {
            foreach ( $groups_by_id as $group_row ) {
                $pg_group_list[] = (object) array(
                    'id' => $group_row['id'],
                    'group_name' => $group_row['group_name'],
                    'group_icon' => $group_row['group_icon'],
                );
            }
        }
        $groups = (object)$pg_group_list;
        $group_url  = $pmrequests->profile_magic_get_frontend_url('pm_group_page','');
        $selected_groups = ! empty( $group_list ) ? array_map( 'strval', (array) $group_list ) : array();
        if ( empty( $selected_groups ) && ! empty( $pg_group_list ) ) {
            foreach ( $pg_group_list as $group_row ) {
                $selected_groups[] = (string) $group_row->id;
            }
        }
        
        // before and after widget arguments are defined by themes
        echo wp_kses_post($args['before_widget']);
        if ( ! empty( $title ) )
        echo wp_kses_post($args['before_title'] . $title . $args['after_title']); 
        // This is where you run the code and display the output
        ?>
        <?php
        if (!empty($groups)):
            foreach ($groups as $group):
            if( in_array( (string) $group->id, $selected_groups, true ) ):
                $group_url  = $pmrequests->profile_magic_get_frontend_url('pm_group_page','',$group->id);
                //$group_url = add_query_arg( 'gid',$group->id, $group_url );
            
        ?>
            <a href="<?php echo esc_url($group_url); ?>" class="pm-dbfl">
                <div class="pg-group-menu-slab pm-dbfl" >
                    <?php if ($pg_group_icon == 'yes'): ?>
                        <div class="pg-group-menu-img pm-difl">
                           <?php echo wp_kses_post($pmrequests->profile_magic_get_group_icon($group,'',$path)); ?>
                        </div>
                    <?php endif; ?>
                    <div class="pg-group-menu-name pm-difl">
                         <?php echo wp_kses_post($group->group_name); ?>
                    </div>
                </div>
            </a>
        <?php
                endif;
            endforeach;
        else:
        ?>
            <div class="pm-slab"><?php esc_html_e("You haven't created any Profile Groups yet.",'profilegrid-user-profiles-groups-and-communities'); ?></div>
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
        $instance['icon'] = ( ! empty( $new_instance['icon'] ) ) ? 'yes' : '';
        $instance['group_list'] = array();
        if ( isset( $new_instance['group_list'] ) && is_array( $new_instance['group_list'] ) ) {
            $instance['group_list'] = array_values( array_filter( array_map( 'strval', $new_instance['group_list'] ) ) );
        }
        
        return $instance;
    }
}
endif;


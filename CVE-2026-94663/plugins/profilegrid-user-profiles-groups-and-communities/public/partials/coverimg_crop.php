<?php
$dbhandler = new PM_DBhandler;
$pmrequests = new PM_request;
$current_user = wp_get_current_user();
$uploads =  wp_upload_dir();
$pm_sanitizer = new PM_sanitizer();
$nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : '';
if ( ! wp_verify_nonce( $nonce, 'ajax-nonce' ) ) {
    wp_send_json_error( array( 'message' => 'Failed security check.' ), 403 );
    exit;
}
$post      = $pm_sanitizer->sanitize( $_POST );
$target_user_id = isset( $post['user_id'] ) ? intval( $post['user_id'] ) : 0;
// Only the profile owner or an admin/super admin can change cover images.
$is_authorized = ( $target_user_id > 0 ) && ( $target_user_id === (int) $current_user->ID || current_user_can( 'manage_options' ) || is_super_admin() );
$allowed_ext ='jpg|jpeg|png|gif|webp|avif';
$targ_w = $targ_h = 150;
$jpeg_quality = intval($dbhandler->get_global_option_value('pg_image_quality','90'));
 switch($post['cover_status']) {
  case 'cancel' :
      if ( ! $is_authorized ) {
        wp_send_json_error( array( 'message' => 'Unauthorized request.' ) );
        exit;
      }
      if ( $is_authorized ) {
        $delete = $pmrequests->pg_delete_attachment( $post['attachment_id'] );
        die;
      }
  break;
  
  case 'save' :
    
    if ( ! $is_authorized ) {
        wp_send_json_error( array( 'message' => 'Unauthorized request.' ) );
        exit;
    }
    $attachment_id = isset( $post['attachment_id'] ) ? absint( $post['attachment_id'] ) : 0;
    $attachment_owner = $attachment_id > 0 ? (int) get_post_field( 'post_author', $attachment_id ) : 0;
    if ( $attachment_id <= 0 || ( $attachment_owner !== (int) $current_user->ID && ! current_user_can( 'manage_options' ) && ! is_super_admin() ) ) {
        wp_send_json_error( array( 'message' => 'Unauthorized attachment.' ), 403 );
        exit;
    }
    if(isset($post['fullpath'])){
        
        $valid_fullpath = $pmrequests->pg_file_fullpath_validation($post['fullpath']);
        if(empty($valid_fullpath)){
           esc_html_e('Something went wrong.', 'profilegrid-user-profiles-groups-and-communities');
           die();
        }else{
            $image = wp_get_image_editor( $post['fullpath'] );
            $image_attribute = wp_get_attachment_image_src($post['attachment_id'],'full');
            $image_url = ( is_array( $image_attribute ) && isset( $image_attribute[0] ) ) ? $image_attribute[0] : wp_get_attachment_url( $post['attachment_id'] );
            $basename = basename($post['fullpath']);
            $can_process_image = ( ! is_wp_error( $image ) && $is_authorized );

            if ( $can_process_image ) {
                $crop_x = isset( $post['x'] ) ? max( 0, (int) round( (float) $post['x'] ) ) : 0;
                $crop_y = isset( $post['y'] ) ? max( 0, (int) round( (float) $post['y'] ) ) : 0;
                $crop_w = isset( $post['w'] ) ? max( 1, (int) round( (float) $post['w'] ) ) : 1;
                $crop_h = isset( $post['h'] ) ? max( 1, (int) round( (float) $post['h'] ) ) : 1;

                $crop_result = $image->crop( $crop_x, $crop_y, $crop_w, $crop_h, $crop_w, $crop_h, false );
                if ( is_wp_error( $crop_result ) ) {
                    $can_process_image = false;
                }
            }

            if ( $can_process_image ) {
                $resize_result = $image->resize( $crop_w, $crop_h, false );
                if ( is_wp_error( $resize_result ) ) {
                    $can_process_image = false;
                }
            }

            if ( $can_process_image ) {
                if (is_numeric($jpeg_quality)) 
                {
                    $image->set_quality(intval($jpeg_quality));
                }

                $image->save( $uploads['path']. '/'.$basename );
            } else {
                // Skip resizing/cropping if editor unavailable (e.g., AVIF/WEBP without GD/Imagick)
                $basename = basename( $image_url ? $image_url : $post['fullpath'] );
            }

            // Keep update_user_meta scoped to authorized requests only.
            update_user_meta($post['user_id'],'pm_cover_image',$post['attachment_id']);
            do_action('pm_update_cover_image',$post['user_id']);
            echo "<img id='coverphotofinal' file-name='".esc_attr($basename)."' src='".esc_url($image_url)."' class='preview'/>";
           die;
        }
    }
  break;
  default:
        
    if ( ! $is_authorized ) {
        wp_send_json_error( array( 'message' => 'Unauthorized request.' ) );
        exit;
    }
    if ( $is_authorized )
    {
        $filefield = $_FILES['coverimg'];
        $minimum_width = trim($dbhandler->get_global_option_value('pg_cover_photo_minimum_width','DEFAULT'));
        $minimum_height = 300;
        $maximum_size = trim($dbhandler->get_global_option_value('pg_cover_image_max_file_size',''));
        $minimum_require = array();
        if($minimum_width=='' || $minimum_width=='DEFAULT')
        {
            $minimum_require[0]=$post['cover_minwidth'];
        }
        else
        {
            $minimum_require[0]=$minimum_width;
        }
        $minimum_require[1] = $minimum_height;
        
        if($maximum_size!='')
        {
            $minimum_require[2] = $maximum_size;
        }
        
        $attachment_id = $pmrequests->make_upload_and_get_attached_id($filefield,$allowed_ext,$minimum_require);
        if(is_numeric($attachment_id))
        {
            $image_attribute = wp_get_attachment_image_src($attachment_id,'full');
            $image_newpath = get_attached_file($attachment_id);

            // Fallback for formats where WP metadata is missing (e.g., AVIF on limited GD/Imagick)
            $image_url = isset($image_attribute[0]) ? $image_attribute[0] : wp_get_attachment_url($attachment_id);
            $image_width = isset($image_attribute[1]) ? $image_attribute[1] : 0;
            $image_height = isset($image_attribute[2]) ? $image_attribute[2] : 0;
            if((!$image_width || !$image_height) && file_exists($image_newpath)){
                $imagesize = @getimagesize($image_newpath);
                if($imagesize){
                    $image_width = $imagesize[0];
                    $image_height = $imagesize[1];
                }
            }

            if ( ! $image_width || ! $image_height ) {
                // Allow processing when server cannot read AVIF/WebP dimensions.
                $image_width  = 1;
                $image_height = 1;
            }

            if($image_url){
                echo "<img id='coverimage' file-name='". esc_attr(basename($image_url))."' src='".esc_url($image_url)."' class='preview'/>";
                echo "<input type='hidden' name='covertruewidth' id='covertruewidth' value='".esc_attr($image_width)."' />";
                echo "<input type='hidden' name='covertrueheight' id='covertrueheight' value='".esc_attr($image_height)."' />";
                echo "<input type='hidden' name='cover_attachment_id' id='cover_attachment_id' value='".esc_attr($attachment_id)."' />";
                echo "<input type='hidden' name='coverfullpath' id='coverfullpath' value='".esc_attr($image_newpath)."' />";
                echo "<input type='hidden' name='pg_cover_image_error' id='pg_cover_image_error' value='0' />";
            }else{
                echo '<p class="pm-popup-error" style="display:block;">'.esc_html__('Could not read image dimensions. Please try another image format.','profilegrid-user-profiles-groups-and-communities').'</p>';
                echo "<input type='hidden' name='pg_cover_image_error' id='pg_cover_image_error' value='1' />";
            }
        }
        else
        {
           echo '<p class="pm-popup-error" style="display:block;">'.esc_html($attachment_id).'</p>'; 
           echo "<input type='hidden' name='pg_cover_image_error' id='pg_cover_image_error' value='1' />";
        }
        

    }
    die;

 }
?>

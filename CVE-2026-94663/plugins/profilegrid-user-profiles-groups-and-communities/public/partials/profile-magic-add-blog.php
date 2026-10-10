<?php
$dbhandler = new PM_DBhandler;
$pmrequests = new PM_request;
$pmemails = new PM_Emails;
$textdomain = $this->profile_magic;
$permalink = get_permalink();
$identifier = 'BLOG';
$themepath = $this->profile_magic_get_pm_theme('add-blog-tpl');
$tiny = ($dbhandler->get_global_option_value('pm_blog_editor','0')==0?false:true);
$poststatus = $dbhandler->get_global_option_value('pm_blog_status','pending');
$pm_blog_notification_admin = $dbhandler->get_global_option_value('pm_blog_notification_admin','0');
$settings = array('wpautop' => false,'media_buttons' => false,'textarea_name' => 'blog_description',
    'textarea_rows' => 10,'tabindex' => '','tabfocus_elements' => ':prev,:next','editor_css' => '',
    'editor_class' => '','teeny' => false,'dfw' => false,'tinymce' => $tiny,'quicktags' => false
);
$current_user = wp_get_current_user();
$uid = $current_user->ID;

$gids = $pmrequests->profile_magic_get_user_field_value($uid,'pm_group');
$gid = $pmrequests->pg_filter_users_group_ids($gids);
if(isset($_POST['blog_title']))
{
    $retrieved_nonce = filter_input(INPUT_POST,'_wpnonce');
    if (!wp_verify_nonce($retrieved_nonce, 'pg_blog_post' ) ) die( esc_html__('Failed security check','profilegrid-user-profiles-groups-and-communities') );
    $exclude = array('_wpnonce','_wp_http_referer','pg_blog_submit');
    $post = $pmrequests->sanitize_request($_POST,$identifier,$exclude);
    if(!isset($post['blog_tags']))$post['blog_tags']='';
    $allowed_ext = 'jpg|jpeg|png|gif|webp|avif';
    $arg = array('post_type' => 'profilegrid_blogs',
        'post_title' =>$post['blog_title'],
        'post_status' => $poststatus,
        'tags_input'	=> $post['blog_tags'],
        'post_content' => wp_rel_nofollow($post['blog_description'])
        );
    $postid = wp_insert_post($arg);
    if($postid && !empty($post['blog_tags']))
    {
        $tags = explode(',', $post['blog_tags']);
        wp_set_object_terms($postid,$tags, 'blog_tag');
    }
    if($postid)
    {
        if(isset($_FILES['blog_image']))
        {
            $attchment_id = $pmrequests->make_upload_and_get_attached_id($_FILES['blog_image'],$allowed_ext,array(),$postid);
            set_post_thumbnail($postid, $attchment_id );
        }
        update_post_meta($postid,'pm_enable_custom_access','1');
        if(isset($post['pm_content_access'])):
            
            if($post['pm_content_access']==2)
            {
                update_post_meta($postid,'pm_content_access_group','all');
            }
            update_post_meta($postid,'pm_content_access',$post['pm_content_access']);
            
        else:
                update_post_meta($postid,'pm_content_access','1');
        endif;
        echo '<div class="pm_blog_post_message">';
        if($poststatus=='publish')
        {
            esc_html_e('Your blog post has been published successfully!','profilegrid-user-profiles-groups-and-communities');
        }
        else
        {
            esc_html_e('Your blog post has been submitted successfully and is pending approval. Once approved, it will start appearing in your profile.','profilegrid-user-profiles-groups-and-communities');
        }
        echo '</div>';
        
        if($pm_blog_notification_admin=="1")
        {
           $subject = esc_html__('New User Blog Post Submitted','profilegrid-user-profiles-groups-and-communities');
           $review_subject = $dbhandler->get_global_option_value('pm_blog_notification_email_subject',__('New User Blog Post Submitted','profilegrid-user-profiles-groups-and-communities'));
           if($poststatus=='publish')
           {
                $review_body = $dbhandler->get_global_option_value('pm_blog_notification_email_body',__("Hello, <br /> {{user_login}} from {{group_name}} has published a new post titled {{post_name}}. You can view the post by {{post_link}}","profilegrid-user-profiles-groups-and-communities")); 
           }
           else
           {
               $review_body = $dbhandler->get_global_option_value('pm_blog_notification_email_body',__("Hello, <br /> {{user_login}} from {{group_name}} has submitted a new post titled {{post_name}} and is pending approval. You can moderate the post by {{edit_post_link}}","profilegrid-user-profiles-groups-and-communities"));
           }
           $review_body = $pmemails->pm_filter_email_content($review_body,$uid,$postid);
           $pmemails->pm_send_admin_notification($review_subject,$review_body);
        }
       
    }	
    else
    {
        echo '<div class="pm_blog_post_message">';
            esc_html_e("Something went wrong. Your blog post wasn't published. Please try again or contact the admin.",'profilegrid-user-profiles-groups-and-communities');
        echo '</div>';        
    }
    $redirect_url = esc_url_raw($pmrequests->profile_magic_get_frontend_url('pm_user_profile_page',site_url('/wp-login.php')));
    
    echo '<div class="pm_blog_post_message">';
           echo '<a href="'.esc_url($redirect_url).'">'. esc_html__("Go to My Profile",'profilegrid-user-profiles-groups-and-communities').'</a>';
        echo '</div>';
     
}
elseif(is_user_logged_in())
{
    include $themepath;
}
else
{
    $redirect_url = $pmrequests->profile_magic_get_frontend_url('pm_user_login_page',site_url('/wp-login.php'));
    $redirect_url = add_query_arg( 'errors','loginrequired', $redirect_url );
    wp_safe_redirect( esc_url_raw( $redirect_url ) );
    exit;	
}
?>

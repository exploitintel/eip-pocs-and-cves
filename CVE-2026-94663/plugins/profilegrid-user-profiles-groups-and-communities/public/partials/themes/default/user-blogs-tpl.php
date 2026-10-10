<?php
$pmhtmlcreator = new PM_HTML_Creator($this->profile_magic,$this->version);
$dbhandler = new PM_DBhandler;
$pmrequests = new PM_request;

if(!empty($content)){extract($content);}
$author = array();
if(isset($user_id))
{
    $users = explode(',',$user_id);
    foreach($users as $uid)
    {
        $author[] = $uid;
    }
}

if(isset($uid))
{
    $users = explode(',',$uid);
    foreach($users as $ui)
    {
        $author[] = $ui;
    }
}

if(isset($username))
{
    $user_names = explode(',',$username);
    foreach($user_names as $user_name)
    {
        $user = get_user_by('login', $user_name);
        $author[] = $user->ID;
    }
}

if(isset($user))
{
    $user_names = explode(',',$user);
    foreach($user_names as $user_name)
    {
        $user = get_user_by('login', $user_name);
        $author[] = $user->ID;
    }
}

if((isset($include_blog) && $include_blog=="true") || (isset($wpblog) && $wpblog=="true"))
{$post_type = array('profilegrid_blogs','post');}else{$post_type = 'profilegrid_blogs';}

?>
<div class="pmagic">
<div id="pg-user-blog" class="pm-difl">
         
    <div id="pg-user-blog-container" class="pm-dbfl">
    <?php
    if($dbhandler->get_global_option_value('pm_enable_blog','0')=='1')
    {
        $pmhtmlcreator->pm_get_user_blogs_shortcode_posts($author,$post_type);
    }
    else
    {
        echo '<div class="pg-alert-warning pg-alert-info">';
        esc_html_e('User Blogs are disabled at the moment. Please contact the site administrator for more information.','profilegrid-user-profiles-groups-and-communities');
        echo '</div>';
        
    }
    ?>
    </div>
</div>
</div>
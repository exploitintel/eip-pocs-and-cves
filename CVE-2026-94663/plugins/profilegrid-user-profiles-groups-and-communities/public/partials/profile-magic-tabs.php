<?php
$dbhandler = new PM_DBhandler;
$pmrequests = new PM_request;
$profile_tabs = $pmrequests->pm_profile_tabs();
?>
<div class="pm-profile-tabs pg-theme-bg pm-dbfl" id="pg-profile-tabs">
     <div class="pm-section-nav-horizental pm-dbfl">
       <ul class="mymenu pm-difl pm-profile-tab-wrap pm-border-bt">	
           <?php
            if (!empty($profile_tabs)):                  
                foreach($profile_tabs as $key=>$tab):
                    $pmrequests->generate_profile_tab_links($tab['id'],$tab,$uid,$gid,$primary_gid);
                endforeach;
            endif;
            ?>
           <?php do_action( 'profile_magic_profile_tab',$uid,$primary_gid);?>
       </ul>
     </div>
    
    <?php
        if (!empty($profile_tabs)):                  
            foreach($profile_tabs as $key=>$tab):
                $pmrequests->generate_profile_tab_content($tab['id'],$tab,$uid,$gid,$primary_gid);
            endforeach;
        endif;
    ?>
    <?php do_action( 'profile_magic_profile_tab_content',$uid,$primary_gid);?>
    
</div>
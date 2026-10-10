<?php
$path = plugin_dir_url( __FILE__ );
?>
<div class="uimagic">
    <form name="pm_content_restriction" id="pm_content_restriction" method="post">
        <div class="content">

            <div class="uimheader">
                    <?php esc_html_e( 'Content Restrictions', 'profilegrid-user-profiles-groups-and-communities' ); ?>
            </div>
            <div class="uimrow">
                <div class="pg-uim-notice">
                 <?php
                        esc_html_e( 'Content Restriction is build into ProfileGrid. To restrict content of a post or a page, scroll down while editing it, and you will find a ProfileGrid options box (see the image below).Tweak settings of this box to restrict the accessibility of post or page you are editing.', 'profilegrid-user-profiles-groups-and-communities' );
					?>
                </div>
            </div>

            <div class="uimrow">
                <img class="pg-content-restriction-img" src="<?php echo esc_url( $path . 'images/pg-content-restrictions.jpg' ); ?>" />
            </div>

            <div class="buttonarea"> 
                <a href="admin.php?page=pm_settings">
                    <div class="cancel">&#8592; &nbsp;
                        <?php esc_html_e( 'Cancel', 'profilegrid-user-profiles-groups-and-communities' ); ?>
                    </div>
                </a>
                <?php wp_nonce_field( 'pg_content_restrictions_settings', 'pg_content_restrictions_nonce' ); ?>
            </div>
        </div>
    </form>
</div>

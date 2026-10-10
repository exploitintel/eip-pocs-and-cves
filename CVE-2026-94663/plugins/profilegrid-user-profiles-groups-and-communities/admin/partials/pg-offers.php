<?php
$path =  plugin_dir_url( __FILE__ );
?>
<div class="uimagic">
    <div class="content">
        <div class="uimheader"><?php esc_html_e( 'Offers', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>

        <div class="uimrow">   
            <div class="pg-offers">
                <div class="pg-offer-results"></div>
                <div class="pg-offer-button"> <button ><?php esc_html_e( 'Fetch Offers', 'profilegrid-user-profiles-groups-and-communities' ); ?></button>  </div>
            </div>
        </div> 
    </div> 
</div>

<div class="pm-fetch-offers-popup pg-modal-box-main" style="display: none;">
    <div class="pg-modal-box-overlay pg-modal-box-overlay-fade-in"></div>
    <div class="pg-modal-box-wrap pg-modal-box-out">
        <div class="pg-modal-box-header">
            <div class="pm-popup-title"><?php esc_html_e( 'Please Confirm', 'profilegrid-user-profiles-groups-and-communities' ); ?>   </div>
           <span class="pg-modal-box-close">×</span>
                     
        </div>

        <div class="pg-extension-modal-des">
           <?php esc_html_e( 'To fetch latest offers, we need to connect to the ProfileGrid server for a moment. No data related to you or your website will be stored or shared. Do you agree to proceed?', 'profilegrid-user-profiles-groups-and-communities' ); ?>
        </div>
        <div class="pg-modal-box-footer">
            <button onclick="pg_fetch_offers()"><i class="fa fa-refresh fa-spin pg-fetch_offers-spiner " style="display: none;"></i><?php esc_html_e( 'I Agree', 'profilegrid-user-profiles-groups-and-communities' ); ?></button>
            <a href="javascript:void(0)" class="pg-modal-box-close"><?php esc_html_e( "I don't Agree", 'profilegrid-user-profiles-groups-and-communities' ); ?></a>
        </div>
    </div>
</div>

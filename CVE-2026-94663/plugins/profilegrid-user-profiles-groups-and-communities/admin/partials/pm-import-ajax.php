<?php
$dbhandler      = new PM_DBhandler();
$pmrequests     = new PM_request();
$current_user   = wp_get_current_user();
$pmexportimport = new PM_Export_Import();
$pm_sanitize = new PM_sanitizer;
$filefield      = isset($_FILES['uploadcsv']) ? $_FILES['uploadcsv'] : array();
$allowed_ext    ='csv';
$pm_import_step = filter_input( INPUT_POST, 'pm_import_step' );
switch ( $pm_import_step ) {
    case 1:
        $retrieved_nonce = filter_input( INPUT_POST, '_wpnonce' );
        if ( !wp_verify_nonce( $retrieved_nonce, 'pm_import_users' ) ) {
                    die( esc_html__( 'Failed security check on step 1', 'profilegrid-user-profiles-groups-and-communities' ) );
        }
        $post           = $pm_sanitize->sanitize($_POST);
        $attachment_id = $pmrequests->make_upload_and_get_attached_id( $filefield, $allowed_ext );

        if ( is_numeric( $attachment_id ) ) {
            echo "<input type='hidden' name='attachment_id' id='attachment_id' value='" . esc_attr( $attachment_id ) . "' />";
        } else {
            echo '<p class="pm-popup-error" style="display:block;">' . esc_html( $attachment_id ) . '</p>';
        }
        break;

    case 2:
        $nonce  = filter_input( INPUT_POST, 'nonce' );
        if ( ! isset( $nonce ) || ! wp_verify_nonce( $nonce, 'ajax-nonce' ) ) {
                die( esc_html__( 'Failed security check step 2', 'profilegrid-user-profiles-groups-and-communities' ) );
        }
        $post           = $pm_sanitize->sanitize($_POST);
        $pmexportimport->pm_generate_mapping_table( $post );
        break;

    case 3:
        $retrieved_nonce = filter_input( INPUT_POST, '_wpnonce' );
        if ( !wp_verify_nonce( $retrieved_nonce, 'pm_import_users' ) ) {
                    die( esc_html__( 'Failed security check on step 3', 'profilegrid-user-profiles-groups-and-communities' ) );
        }
        $post           = $pm_sanitize->sanitize($_POST);
         $pmexportimport->pm_import_users_from_csv( $post );

        break;

    default:
        break;


}

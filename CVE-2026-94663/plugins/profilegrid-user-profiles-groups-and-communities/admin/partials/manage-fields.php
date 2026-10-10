<?php
$dbhandler  = new PM_DBhandler();
$pmrequests = new PM_request();
$textdomain = $this->profile_magic;
$path       = plugin_dir_url( __FILE__ );
$gid        = filter_input( INPUT_GET, 'gid' );
$identifier = 'FIELDS';
$groups     = $dbhandler->get_all_result( 'GROUPS', array( 'id', 'group_name' ) );
if ( !isset( $gid ) ) {
    $gid = $groups[0]->id;
}

$sections         = $dbhandler->get_all_result( 'SECTION', array( 'id', 'section_name' ), array( 'gid' => $gid ), 'results', 0, false, $sort_by = 'ordering' );
$fields           = $dbhandler->get_all_result( $identifier, $column = '*', array( 'associate_group' => $gid ), 'results', 0, false, $sort_by = 'ordering' );
$lastrow          = $dbhandler->get_all_result( 'SECTION', 'id', 1, 'var', 0, 1, 'id', 'DESC' );
$section_ordering = $lastrow + 1;
?>

<div class="pm-group-fields-modal" id="pm-group-fields-popup" style="display: none;">
 <div class="pm-group-fields-popup-overlay pm-group-fields-popup-overlay-fade-in"></div>
 <div class="pm-group-fields-popup-wrap pm-group-fields-popup-out">
    <div class="pm-popup-header">
        <div class="pm-popup-title">
            <?php esc_html_e( 'Choose a field type', 'profilegrid-user-profiles-groups-and-communities' ); ?>
        </div>
        <span class="pm-group-fields-popup-close"><img class="" src="<?php echo esc_url( $path . 'images/close-pm.png' ); ?>"></span>
        <span class="pg-fields-help"><a href="https://profilegrid.co/documentation/new-field/" target="_blank"><?php esc_html_e( 'Help', 'profilegrid-user-profiles-groups-and-communities' ); ?></a></span>

    </div>
   
        <div class="pm-field-selection">
        <div class="pm-popup-field-box" onClick="add_pm_field('first_name', '<?php echo esc_attr( $gid ); ?>')">
            <div class="pm-popup-field-name" >
                <i class="fa fa-user-circle-o" aria-hidden="true"></i>
                <?php esc_html_e( 'First Name', 'profilegrid-user-profiles-groups-and-communities' ); ?>
            </div>
            <div class="pm-popup-field-details"><?php esc_html_e( 'This field is connected directly to WordPress’ User Profile First Name field.', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
        </div>
        <div class="pm-popup-field-box" onClick="add_pm_field('last_name', '<?php echo esc_attr( $gid ); ?>')">
            <div class="pm-popup-field-name" >
                <i class="fa fa-user-circle-o" aria-hidden="true"></i>
                <?php esc_html_e( 'Last Name', 'profilegrid-user-profiles-groups-and-communities' ); ?>
            </div>
            <div class="pm-popup-field-details"><?php esc_html_e( 'This field is connected directly to WordPress’ User Profile Last Name field.', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
        </div>
        <div class="pm-popup-field-box" onClick="add_pm_field('user_name', '<?php echo esc_attr( $gid ); ?>')">
            <div class="pm-popup-field-name" >
                <i class="fa fa-user-circle-o" aria-hidden="true"></i>
                <?php esc_html_e( 'Username', 'profilegrid-user-profiles-groups-and-communities' ); ?>
            </div>
            <div class="pm-popup-field-details"><?php esc_html_e( 'If you want members to login using Username instead of email.', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
        </div>
        <div class="pm-popup-field-box" onClick="add_pm_field('nickname', '<?php echo esc_attr( $gid ); ?>')">
            <div class="pm-popup-field-name" >
                <i class="fa fa-user-circle-o" aria-hidden="true"></i>
                <?php esc_html_e( 'Nickname', 'profilegrid-user-profiles-groups-and-communities' ); ?>
            </div>
            <div class="pm-popup-field-details"><?php esc_html_e( 'This field is connected directly to WordPress’ User Profile Nickname field.', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
        </div>    
            
        <?php
        $check = $pmrequests->pm_check_field_exist( $gid, 'user_email', true );
        if ( $check === false ) :
            ?>
            <div class="pm-popup-field-box" onClick="add_pm_field('user_email', '<?php echo esc_attr( $gid ); ?>')">
                <div class="pm-popup-field-name">
                    <?php esc_html_e( 'User Email', 'profilegrid-user-profiles-groups-and-communities' ); ?>
                </div>
                <div class="pm-popup-field-details"><?php esc_html_e( "Member's email. Only use <u>once</u> for each profile. For secondary email, use <i>Email</i> field type.", 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
            </div>
        <?php endif; ?>
        <div class="pm-popup-field-box" onClick="add_pm_field('user_url', '<?php echo esc_attr( $gid ); ?>')">
            <div class="pm-popup-field-name" >
                <i class="fa fa-globe" aria-hidden="true"></i>
                <?php esc_html_e( 'User URL', 'profilegrid-user-profiles-groups-and-communities' ); ?>
            </div>
            <div class="pm-popup-field-details"><?php esc_html_e( 'Allows members to add website address to their profiles.', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
        </div>
            
        <div class="pm-popup-field-box" onClick="add_pm_field('url', '<?php echo esc_attr( $gid ); ?>')">
            <div class="pm-popup-field-name" >
                <i class="fa fa-globe" aria-hidden="true"></i>
                <?php esc_html_e( 'URL', 'profilegrid-user-profiles-groups-and-communities' ); ?>
            </div>
            <div class="pm-popup-field-details"><?php esc_html_e( 'Allows members to add external links to their profiles with custom anchor text.', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
        </div>    
        <div class="pm-popup-field-box" onClick="add_pm_field('user_pass', '<?php echo esc_attr( $gid ); ?>')">
            <div class="pm-popup-field-name" >
                <i class="fa fa-key" aria-hidden="true"></i>
                <?php esc_html_e( 'Password', 'profilegrid-user-profiles-groups-and-communities' ); ?>
            </div>
            <div class="pm-popup-field-details"><?php esc_html_e( 'Allows members to define their own password during registration.', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
        </div>
        <div class="pm-popup-field-box" onClick="add_pm_field('confirm_pass', '<?php echo esc_attr( $gid ); ?>')">
            <div class="pm-popup-field-name">
                <i class="fa fa-key" aria-hidden="true"></i>
                <?php esc_html_e( 'Confirm Password', 'profilegrid-user-profiles-groups-and-communities' ); ?>
            </div>
            <div class="pm-popup-field-details"><?php esc_html_e( 'To be used in conjunction with <i>Password</i> field.', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
        </div>
        <div class="pm-popup-field-box" onClick="add_pm_field('description', '<?php echo esc_attr( $gid ); ?>')">
            <div class="pm-popup-field-name">
                <i class="fa fa-info-circle" aria-hidden="true"></i>
                <?php esc_html_e( 'Biographical Info', 'profilegrid-user-profiles-groups-and-communities' ); ?>
            </div>
            <div class="pm-popup-field-details"><?php esc_html_e( 'Allows members to enter long text.', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
        </div>
        <div class="pm-popup-field-box" onClick="add_pm_field('user_avatar', '<?php echo esc_attr( $gid ); ?>')">
            <div class="pm-popup-field-name">
                <i class="fa fa-camera-retro" aria-hidden="true"></i>
                <?php esc_html_e( 'Profile Image', 'profilegrid-user-profiles-groups-and-communities' ); ?>
            </div>
            <div class="pm-popup-field-details"><?php esc_html_e( 'Allows members to upload their Profile image during registration. This can be changed or removed later.', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
        </div>

        <div class="pm-popup-field-box" onClick="add_pm_field('text', '<?php echo esc_attr( $gid ); ?>')">
            <div class="pm-popup-field-name">
                <i class="fa fa-text-width" aria-hidden="true"></i>
                <?php esc_html_e( 'Text', 'profilegrid-user-profiles-groups-and-communities' ); ?>
            </div>
            <div class="pm-popup-field-details"><?php esc_html_e( 'The common text field allowing user a single line of text.', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
        </div>
            
         <div class="pm-popup-field-box" onClick="add_pm_field('read_only', '<?php echo esc_attr( $gid ); ?>')">
            <div class="pm-popup-field-name" >
                <i class="fa fa-user-circle-o" aria-hidden="true"></i>
                <?php esc_html_e( 'Read Only', 'profilegrid-user-profiles-groups-and-communities' ); ?>
            </div>
            <div class="pm-popup-field-details"><?php esc_html_e( 'Allows admins to create read-only fields. Only admins can change the value for users. This field will not show in registration forms and edit profile pages.', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
        </div>
            
        <div class="pm-popup-field-box" onClick="add_pm_field('select', '<?php echo esc_attr( $gid ); ?>')">
            <div class="pm-popup-field-name">
                <i class="fa fa-chevron-circle-down" aria-hidden="true"></i>
                <?php esc_html_e( 'DropDown', 'profilegrid-user-profiles-groups-and-communities' ); ?>
            </div>
            <div class="pm-popup-field-details"><?php esc_html_e( 'A dropdown with selection of predefined options. Members can only select one option.', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
        </div>
        <div class="pm-popup-field-box" onClick="add_pm_field('radio', '<?php echo esc_attr( $gid ); ?>')">
            <div class="pm-popup-field-name">
                <i class="fa fa-circle-o" aria-hidden="true"></i>
                <?php esc_html_e( 'Radio', 'profilegrid-user-profiles-groups-and-communities' ); ?>
            </div>
            <div class="pm-popup-field-details"><?php esc_html_e( 'A radiobox selection with predefined options. Members can only select one option.', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
        </div>
        <div class="pm-popup-field-box" onClick="add_pm_field('textarea', '<?php echo esc_attr( $gid ); ?>')">
            <div class="pm-popup-field-name">
                <i class="fa fa-bars" aria-hidden="true"></i>
                <?php esc_html_e( 'Text Area', 'profilegrid-user-profiles-groups-and-communities' ); ?>
            </div>
            <div class="pm-popup-field-details"><?php esc_html_e( 'A text area box with ability to add multiple lines of plain text.', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
        </div>
        <div class="pm-popup-field-box" onClick="add_pm_field('checkbox', '<?php echo esc_attr( $gid ); ?>')">
            <div class="pm-popup-field-name">
                <i class="fa fa-check-square-o" aria-hidden="true"></i>
                <?php esc_html_e( 'Checkbox', 'profilegrid-user-profiles-groups-and-communities' ); ?>
            </div>
            <div class="pm-popup-field-details"><?php esc_html_e( 'A checkbox selection with predefined options. Members can select multiple options.', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
        </div>
        <div class="pm-popup-field-box" onClick="add_pm_field('heading', '<?php echo esc_attr( $gid ); ?>')">
            <div class="pm-popup-field-name">
                <i class="fa fa-header" aria-hidden="true"></i>
                <?php esc_html_e( 'Heading', 'profilegrid-user-profiles-groups-and-communities' ); ?>
            </div>
            <div class="pm-popup-field-details"><?php esc_html_e( 'Large size read only text, useful for creating custom headings.', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
        </div>
        <div class="pm-popup-field-box" onClick="add_pm_field('paragraph', '<?php echo esc_attr( $gid ); ?>')">
            <div class="pm-popup-field-name">
                <i class="fa fa-paragraph" aria-hidden="true"></i>
                <?php esc_html_e( 'Paragraph', 'profilegrid-user-profiles-groups-and-communities' ); ?>
            </div>
            <div class="pm-popup-field-details"><?php esc_html_e( 'This is a read only field which can be used to display formatted content inside the form.', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
        </div>
        <div class="pm-popup-field-box" onClick="add_pm_field('DatePicker', '<?php echo esc_attr( $gid ); ?>')">
            <div class="pm-popup-field-name">
                <i class="fa fa-calendar" aria-hidden="true"></i>
                <?php esc_html_e( 'Date', 'profilegrid-user-profiles-groups-and-communities' ); ?>
            </div>
            <div class="pm-popup-field-details"><?php esc_html_e( 'Allows users to pick a date from dropdown calendar or type it manually.', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
        </div>
        <div class="pm-popup-field-box" onClick="add_pm_field('email', '<?php echo esc_attr( $gid ); ?>')">
            <div class="pm-popup-field-name">
                <i class="fa fa-envelope" aria-hidden="true"></i>
                <?php esc_html_e( 'Email', 'profilegrid-user-profiles-groups-and-communities' ); ?>
            </div>
            <div class="pm-popup-field-details"><?php esc_html_e( 'A secondary email field.', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
        </div>
        <div class="pm-popup-field-box" onClick="add_pm_field('number', '<?php echo esc_attr( $gid ); ?>')">
            <div class="pm-popup-field-name">
                <i class="fa fa-sort-numeric-desc" aria-hidden="true"></i>
                <?php esc_html_e( 'Number', 'profilegrid-user-profiles-groups-and-communities' ); ?>
            </div>
            <div class="pm-popup-field-details"><?php esc_html_e( 'Allows user to input value in numbers.', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
        </div>
        <div class="pm-popup-field-box" onClick="add_pm_field('country', '<?php echo esc_attr( $gid ); ?>')">
            <div class="pm-popup-field-name">
                <i class="fa fa-flag" aria-hidden="true"></i>
                <?php esc_html_e( 'Country', 'profilegrid-user-profiles-groups-and-communities' ); ?>
            </div>
            <div class="pm-popup-field-details"><?php esc_html_e( 'A drop down list of all countries appears to the user for selection.', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
        </div>
        <div class="pm-popup-field-box" onClick="add_pm_field('timezone', '<?php echo esc_attr( $gid ); ?>')">
            <div class="pm-popup-field-name">
                <i class="fa fa-clock-o" aria-hidden="true"></i>
                <?php esc_html_e( 'Timezone', 'profilegrid-user-profiles-groups-and-communities' ); ?>
            </div>
            <div class="pm-popup-field-details"><?php esc_html_e( 'A drop down list of all time-zones appears to the user for selection.', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
        </div>
        <div class="pm-popup-field-box" onClick="add_pm_field('term_checkbox', '<?php echo esc_attr( $gid ); ?>')">
            <div class="pm-popup-field-name">
                <i class="fa fa-check" aria-hidden="true"></i>
                <?php esc_html_e( 'T&C Checkbox', 'profilegrid-user-profiles-groups-and-communities' ); ?>
            </div>
            <div class="pm-popup-field-details"><?php esc_html_e( 'Useful for adding terms and conditions to the form. User must select the check box to continue with submission if you select “Is Required” below.', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
        </div>
        <div class="pm-popup-field-box" onClick="add_pm_field('file', '<?php echo esc_attr( $gid ); ?>')">
            <div class="pm-popup-field-name">
                <i class="fa fa-files-o" aria-hidden="true"></i>
                <?php esc_html_e( 'File', 'profilegrid-user-profiles-groups-and-communities' ); ?>
            </div>
            <div class="pm-popup-field-details"><?php esc_html_e( 'Display a field to the user for attaching files from his/ her computer.', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
        </div>
        <div class="pm-popup-field-box" onClick="add_pm_field('repeatable_text', '<?php echo esc_attr( $gid ); ?>')">
            <div class="pm-popup-field-name">
                <i class="fa fa-repeat" aria-hidden="true"></i>
                <?php esc_html_e( 'Repeatable Text', 'profilegrid-user-profiles-groups-and-communities' ); ?>
            </div>
            <div class="pm-popup-field-details"><?php esc_html_e( 'Allows user to add extra text field boxes to the form for submitting different values. Useful where a field requires multiple user input values.', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
        </div>
        <div class="pm-popup-field-box" onClick="add_pm_field('mobile_number', '<?php echo esc_attr( $gid ); ?>')">
            <div class="pm-popup-field-name">
                <i class="fa fa-mobile" aria-hidden="true"></i>
                <?php esc_html_e( 'Mobile Number', 'profilegrid-user-profiles-groups-and-communities' ); ?>
            </div>
            <div class="pm-popup-field-details"><?php esc_html_e( 'Adds a Mobile number field.', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
        </div>

        <div class="pm-popup-field-box" onClick="add_pm_field('phone_number', '<?php echo esc_attr( $gid ); ?>')">
            <div class="pm-popup-field-name">
                <i class="fa fa-phone" aria-hidden="true"></i>
                <?php esc_html_e( 'Phone Number', 'profilegrid-user-profiles-groups-and-communities' ); ?>
            </div>
            <div class="pm-popup-field-details"><?php esc_html_e( 'Adds a phone number field.', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
        </div>

        <div class="pm-popup-field-box" onClick="add_pm_field('gender', '<?php echo esc_attr( $gid ); ?>')">
            <div class="pm-popup-field-name">
                <i class="fa fa-venus-mars" aria-hidden="true"></i>
                <?php esc_html_e( 'Gender', 'profilegrid-user-profiles-groups-and-communities' ); ?>
            </div>
            <div class="pm-popup-field-details"><?php esc_html_e( 'Gender/ Sex selection radio box.', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
        </div>

        <div class="pm-popup-field-box" onClick="add_pm_field('language', '<?php echo esc_attr( $gid ); ?>')">
            <div class="pm-popup-field-name">
                <i class="fa fa-language" aria-hidden="true"></i>
                <?php esc_html_e( 'Language', 'profilegrid-user-profiles-groups-and-communities' ); ?>
            </div>
            <div class="pm-popup-field-details"><?php esc_html_e( 'Adds a drop down language selection field with common languages as options.', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
        </div>

        <div class="pm-popup-field-box" onClick="add_pm_field('divider', '<?php echo esc_attr( $gid ); ?>')">
            <div class="pm-popup-field-name">
                <i class="fa fa-minus" aria-hidden="true"></i>
                <?php esc_html_e( 'Divider', 'profilegrid-user-profiles-groups-and-communities' ); ?>
            </div>
            <div class="pm-popup-field-details"><?php esc_html_e( 'Divider for separating fields.', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
        </div>

        <div class="pm-popup-field-box" onClick="add_pm_field('spacing', '<?php echo esc_attr( $gid ); ?>')">
            <div class="pm-popup-field-name">
                <i class="fa fa-minus" aria-hidden="true"></i>
                <?php esc_html_e( 'Spacing', 'profilegrid-user-profiles-groups-and-communities' ); ?>
            </div>
            <div class="pm-popup-field-details"><?php esc_html_e( 'Useful for adding space between fields.', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
        </div>

        <div class="pm-popup-field-box" onClick="add_pm_field('multi_dropdown', '<?php echo esc_attr( $gid ); ?>')">
            <div class="pm-popup-field-name">
                <i class="fa fa-angle-double-down" aria-hidden="true"></i>
                <?php esc_html_e( 'Multi Dropdown', 'profilegrid-user-profiles-groups-and-communities' ); ?>
            </div>
            <div class="pm-popup-field-details"><?php esc_html_e( 'A dropdown field with a twist. Users can now select more than one option.', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
        </div>

        <div class="pm-popup-field-box" onClick="add_pm_field('facebook', '<?php echo esc_attr( $gid ); ?>')">
            <div class="pm-popup-field-name">
                <i class="fa fa-facebook" aria-hidden="true"></i>
                <?php esc_html_e( 'Facebook', 'profilegrid-user-profiles-groups-and-communities' ); ?>
            </div>
            <div class="pm-popup-field-details"><?php esc_html_e( 'A speciality URL field for asking Facebook Profile page.', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
        </div>

        <div class="pm-popup-field-box" onClick="add_pm_field('twitter', '<?php echo esc_attr( $gid ); ?>')">
            <div class="pm-popup-field-name">
                <i class="fa" aria-hidden="true"> <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path d="M389.2 48h70.6L305.6 224.2 487 464H345L233.7 318.6 106.5 464H35.8L200.7 275.5 26.8 48H172.4L272.9 180.9 389.2 48zM364.4 421.8h39.1L151.1 88h-42L364.4 421.8z"/></svg></i>
                <?php esc_html_e( 'X/Twitter', 'profilegrid-user-profiles-groups-and-communities' ); ?>
            </div>
            <div class="pm-popup-field-details"><?php esc_html_e( 'A speciality URL field for asking X/Twitter Profile page.', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
        </div>



        <div class="pm-popup-field-box" onClick="add_pm_field('linked_in', '<?php echo esc_attr( $gid ); ?>')">
            <div class="pm-popup-field-name">
                <i class="fa fa-linkedin" aria-hidden="true"></i>
                <?php esc_html_e( 'Linked In', 'profilegrid-user-profiles-groups-and-communities' ); ?>
            </div>
            <div class="pm-popup-field-details"><?php esc_html_e( 'A speciality URL field for asking Linkedin Profile page.', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
        </div>


        <div class="pm-popup-field-box" onClick="add_pm_field('youtube', '<?php echo esc_attr( $gid ); ?>')">
            <div class="pm-popup-field-name">
                <i class="fa fa-youtube" aria-hidden="true"></i>
                <?php esc_html_e( 'Youtube', 'profilegrid-user-profiles-groups-and-communities' ); ?>
            </div>
            <div class="pm-popup-field-details"><?php esc_html_e( 'A speciality URL field for asking Video page.', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
        </div>

         <div class="pm-popup-field-box" onClick="add_pm_field('mixcloud', '<?php echo esc_attr( $gid ); ?>')">
            <div class="pm-popup-field-name">
                <i class="fa fa-mixcloud" aria-hidden="true"></i>
                <?php esc_html_e( 'MixCloud', 'profilegrid-user-profiles-groups-and-communities' ); ?>
            </div>
            <div class="pm-popup-field-details"><?php esc_html_e( 'A speciality URL field for asking MixCloud audio link.', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
        </div>
            
        <div class="pm-popup-field-box" onClick="add_pm_field('soundcloud', '<?php echo esc_attr( $gid ); ?>')">
            <div class="pm-popup-field-name">
             <i class="fa fa-soundcloud" aria-hidden="true"></i>
                <?php esc_html_e( 'SoundCloud', 'profilegrid-user-profiles-groups-and-communities' ); ?>
            </div>
            <div class="pm-popup-field-details"><?php esc_html_e( 'A speciality URL field for asking SoundCloud audio link.', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
        </div>

        <div class="pm-popup-field-box" onClick="add_pm_field('instagram', '<?php echo esc_attr( $gid ); ?>')">
            <div class="pm-popup-field-name">
                <i class="fa fa-instagram   " aria-hidden="true"></i>
                <?php esc_html_e( 'Instagram', 'profilegrid-user-profiles-groups-and-communities' ); ?>
            </div>
            <div class="pm-popup-field-details"><?php esc_html_e( 'A speciality URL field for asking Instagram Profile page.', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
        </div>
            
            <?php do_action( 'pg_add_field_in_popup', $gid ); ?>
            
           <!-- <div class="pg-uim-notice-wrap"> <div class="pg-uim-notice"><?php esc_html_e( 'Note: More fields and registration options available through free RegistrationMagic integration.', 'profilegrid-user-profiles-groups-and-communities' ); ?></div></div> -->
    </div>
    </div>
 </div>


    <div class="pmagic"> 

        <!-----Operationsbar Starts----->

        <div class="operationsbar">
            <div class="pmtitle">
                <?php esc_html_e( 'Profile Fields Manager', 'profilegrid-user-profiles-groups-and-communities' ); ?>
            </div>
            <div class="icons"><a href="admin.php?page=pm_add_group&id=<?php echo esc_attr( $gid ); ?>"><img src="<?php echo esc_url( $path . 'images/pg-general-setting.png' ); ?>"></a></div>
            <div class="nav">
                <ul>
                    
                    <li><a href="#new_section" onclick="pm_open_tab('new_section')">
                            <?php esc_html_e( 'New Section', 'profilegrid-user-profiles-groups-and-communities' ); ?>
                        </a></li>
                        <li><a href="#" id="pg-field-selection-popup" onclick="CallFieldSelectionModal(this)">
                            <?php esc_html_e( 'New Field', 'profilegrid-user-profiles-groups-and-communities' ); ?>
                        </a></li>
                        <li><a href="https://profilegrid.co/documentation/fields-manager/" target="_blank"><?php esc_html_e( 'Help', 'profilegrid-user-profiles-groups-and-communities' ); ?></a></li>
                    <li class="pm-form-toggle pm-fields-form-toggle">
                        <i class="fa fa-filter" aria-hidden="true"></i>
                        <?php esc_html_e( 'Filter by', 'profilegrid-user-profiles-groups-and-communities' ); ?>
                        <select name="associate_group" id="associate_group" onChange="redirectpmform(this.value, 'pm_profile_fields')">
                            <option value="">
                                <?php esc_html_e( 'Select A Group', 'profilegrid-user-profiles-groups-and-communities' ); ?>
                            </option>
                            <?php
                            foreach ( $groups as $group ) {
                                ?>
                                <option value="<?php echo esc_attr( $group->id ); ?>" 
                                                          <?php
															if ( !empty( $gid ) ) {
																selected( $gid, $group->id );}
															?>
                                ><?php echo esc_html( $group->group_name ); ?></option>
								<?php
                            }
                            ?>
                        </select>
                    </li>
                </ul>
            </div>
        </div>
        <!--------Operationsbar Ends-----> 

        <!----Slab View---->

        <div class="pm-field-creator" id="sections">
            <?php wp_nonce_field('pm_section_reorder_nonce', 'pm_section_reorder_nonce'); ?>

            <ul class="pm-page-tabs-sidebar field-tabs pm_sortable_tabs">
                <?php
                if ( isset( $sections ) ) :
                    foreach ( $sections as $section ) :
                        ?>
                        <li class="pm-page-tab field-tabs-row" id="<?php echo esc_attr( $section->id ); ?>">
                            <div class="pm-slab-drag-handle">&nbsp;</div>
                            <a href="#<?php echo sanitize_key( 'profile_section_' . $section->id ); ?>" onclick="pm_open_tab('<?php echo sanitize_key( 'profile_section_' . $section->id ); ?>')">
                        <?php echo esc_html( $section->section_name ); ?>
                            </a> </li>
						<?php
    endforeach;
endif;
				?>
                <li><a href="#new_section"></a></li>
            </ul>
            <div class="pm-custom-fields-page">
                    <?php
                    if ( isset( $sections ) ) :
						foreach ( $sections as $section ) :
							?>
                        <div id="<?php echo sanitize_key( 'profile_section_' . $section->id ); ?>" class="field-selector-pills">
														<?php
														$fields      = $dbhandler->get_all_result(
                                                            $identifier,
															$column  = '*',
															array(
																'associate_group'   => $gid,
																'associate_section' => $section->id,
                                                            ),
															'results',
															0,
															false,
															$sort_by = 'ordering'
                                                        );
														?>
                            <div class="pmrow"> 
                                <a href="admin.php?page=pm_add_section&id=<?php echo esc_attr( $section->id ); ?>#<?php echo sanitize_key( 'profile_section_' . $section->id ); ?>"><?php esc_html_e( 'Edit Section', 'profilegrid-user-profiles-groups-and-communities' ); ?></a> 
                                <!--<a href="admin.php?page=pm_add_section&action=delete&id=<?php echo esc_attr( $section->id ); ?>&gid=<?php echo esc_attr( $gid ); ?>"><?php esc_html_e( 'Delete Section', 'profilegrid-user-profiles-groups-and-communities' ); ?></a> -->
                                <a onclick="pg_delete_form_section('<?php echo esc_attr( $gid ); ?>','<?php echo esc_attr( $section->id ); ?>')" href="javascript:void(0)"><?php esc_html_e( 'Delete Section', 'profilegrid-user-profiles-groups-and-communities' ); ?></a>
                            </div>
                            <ul class="pm_sortable_fields">
                                <?php
                                if ( !empty( $fields ) ) :
                                    foreach ( $fields as $field ) :
                                        ?>
                                <li id="<?php echo esc_attr( $field->field_id ); ?>">
                                            <div class="pm-custom-field-page-slab">
                                                <div class="pm-slab-drag-handle">&nbsp;</div>
                                                <div class="pm-slab-info"><?php echo esc_html( $field->field_name ); ?> <sup><?php echo esc_html( $field->field_type ); ?></sup></div>
                                                <div class="pm-slab-buttons"><a href="admin.php?page=pm_add_field&id=<?php echo esc_attr( $field->field_id ); ?>#<?php echo sanitize_key( $section->section_name ); ?>"><?php esc_html_e( 'Edit', 'profilegrid-user-profiles-groups-and-communities' ); ?></a><a href="admin.php?page=pm_add_field&id=<?php echo esc_attr( $field->field_id ); ?>&action=delete#<?php echo sanitize_key( 'profile_section_' . $section->id ); ?>"><?php esc_html_e( 'Delete', 'profilegrid-user-profiles-groups-and-communities' ); ?></a></div>
                                            </div>
                                        </li>
										<?php
            endforeach;
								else :
									?>
                                    <li>
                                        <div class="pg-uim-notice pg-uim-error"><?php esc_html_e( "You haven't created any Profile Fields for this Section yet.", 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
                                    </li>
									<?php
												endif;
								?>
                            </ul>
                        </div>
													<?php
    endforeach;
endif;
					?>
                <div id="new_section" class="field-selector-pills">
                    <div class="uimagic pm-section-form">
                        <form name="pm_add_section" id="pm_add_section" method="post" action="admin.php?page=pm_add_section">
                            <!-----Dialogue Box Starts----->
                            <div class="content">
                                <div class="uimrow">
                                    <div class="uimfield">
<?php esc_html_e( 'Section Name', 'profilegrid-user-profiles-groups-and-communities' ); ?>
                                        <sup>*</sup></div>
                                    <div class="uiminput pm_required">
                                        <input type="text" name="section_name" id="section_name" value="<?php
                                        if ( !empty( $row ) ) {
											echo esc_attr( $row->section_name );}
										?>" />
                                        <div class="errortext"></div>
                                    </div>
                                    <div class="uimnote"><?php esc_html_e( 'Enter a name for this Profile Section.', 'profilegrid-user-profiles-groups-and-communities' ); ?></div>
                                </div>
                                <div class="uimrow">
                                    <div class="uimfield"> </div>
                                    <div class="uiminput">
                                        <input type="hidden" name="gid" id="gid" value="<?php echo esc_attr( $gid ); ?>" />
                                        <input type="hidden" name="section_id" id="section_id" value="0" />
                                        <input type="hidden" name="ordering" id="ordering" value="<?php echo esc_attr( $section_ordering ); ?>" />
<?php wp_nonce_field( 'save_pm_add_section' ); ?>
                                        <input type="submit" value="<?php esc_attr_e( 'Save', 'profilegrid-user-profiles-groups-and-communities' ); ?>" name="submit_section" id="submit_section" onClick="return add_section_validation()"  />
                                        <div class="all_error_text" style="display:none;"></div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <div class="pm-popup pm-section-delete-popup pm-popup-height-auto" >
            <div class="pm-popup-header">
                <div class="pm-popup-title"><?php esc_html_e('Please Confirm', 'profilegrid-user-profiles-groups-and-communities'); ?>   </div>
                <img class="pm-popup-close" src="<?php echo esc_url($path . '/images/close-pm.png'); ?>">
            </div>

            <div class="pm-popup-body" style="padding:15px;" >
                <p class=""> <?php esc_html_e('Please confirm that you wish to permanently delete this section. This action is irreversible and the data cannot be recovered.', 'profilegrid-user-profiles-groups-and-communities'); ?></p>
            </div>
            <div class="modal-footer" style="padding:15px;">
                
                <input type="button" id="cancel-delete" class="pm-popup-close button" value="&#8592; &nbsp;<?php esc_attr_e('Cancel', 'profilegrid-user-profiles-groups-and-communities'); ?> " />
                <a href="" class='pm-delete-section-confirm-btn button button-primary'><?php esc_attr_e('Confirm', 'profilegrid-user-profiles-groups-and-communities'); ?></a>
            </div>
        </div>
        
          <?php if ( $pmrequests->pm_check_field_exist( $gid, 'user_pass', true ) == false ) : ?>
    <div class="pg-ui-info-notice">
				<?php esc_html_e( 'There’s no password field in your sign up form. You can auto-generated and email password to registering users. To do that please add password field to Account Activation email template associated with this Group.', 'profilegrid-user-profiles-groups-and-communities' ); ?>
    </div>
				<?php
endif;
			?>
       
    </div>

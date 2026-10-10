<?php
/**
 * @link              http://profilegrid.co
 * @since             1.0.0
 * @package           Profile_Grid
 *
 * @wordpress-plugin
 * Plugin Name:       ProfileGrid
 * Plugin URI:        http://profilegrid.co
 * Description:       ProfileGrid adds user groups and user profiles functionality to your site.
 * Version:           6.0.0.2
 * Author:            ProfileGrid User Profiles
 * Author URI:        https://profilegrid.co
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       profilegrid-user-profiles-groups-and-communities
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-profile-magic-activator.php
 */

define('PROGRID_DB_VERSION',4.5);
define('PROGRID_PLUGIN_VERSION','6.0.0.2');
define('PROGRID_MULTI_GROUP_VERSION', 3.0);


function activate_profile_magic() {
        $profile_magic = 'profilegrid-user-profiles-groups-and-communities';
        $version       = PROGRID_PLUGIN_VERSION;
        $public = new Profile_Magic_Public($profile_magic, $version);
        $public->pg_create_post_type();
	flush_rewrite_rules();
        add_option('progrid_db_version',PROGRID_DB_VERSION);
        update_option('progrid_multi_group_version',PROGRID_MULTI_GROUP_VERSION);
	$pm_activator = new Profile_Magic_Activator;
	$pm_activator->activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-profile-magic-deactivator.php
 */
function deactivate_profile_magic() {
        flush_rewrite_rules();
	$pm_deactivator = new Profile_Magic_Deactivator;
	$pm_deactivator->deactivate();
}

register_activation_hook( __FILE__, 'activate_profile_magic' );
register_deactivation_hook( __FILE__, 'deactivate_profile_magic' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-profile-magic.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_profile_magic() {

	$plugin = new Profile_Magic();
	$plugin->run();

}
run_profile_magic();

<?php
/**
 * Plugin Name: FKW Membership
 * Plugin URI: https://developer.finarina.com/services/wordpress-customization/fkw-membership
 * Description: Creates a membership system on your WordPress website. Integrates with WooCommerce.
 * Version: 1.0.0
 * Author: Finarina Development
 * Author URI: https://developer.finarina.com/services/wordpress-customization/
 * License: GPLv3 or later
 * Text Domain: fkwmembership
 * Domain Path: /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

define( 'FKWMEMBERSHIP_NAME', 'FKW Membership' );
define( 'FKWMEMBERSHIP_NAMESPACE', 'fkwmembership' );
define( 'FKWMEMBERSHIP_VERSION', '1.0.0' );
define( 'FKWMEMBERSHIP_PLUGIN_BASENAME', plugin_dir_path( __FILE__ ) );
define( 'FKWMEMBERSHIP_PLUGIN_BASEURL', plugin_dir_url( __FILE__ ) );

/**
 * The child theme class autoloader.
 *
 * @since 1.0.0
 */
$plugin_loader =  plugin_dir_path( __FILE__ ) . '/vendor/autoload.php';

if ( file_exists( $plugin_loader ) ) {
	require_once( $plugin_loader );
} else {
	WP_Error( FKWMEMBERSHIP_NAMESPACE, 'Cannot locate the FKWMembership plugin autoloader', 500 );
}

// Activation and deactivation hooks
register_activation_hook( __FILE__, 'activate_fkwmembership' );
register_deactivation_hook( __FILE__, 'deactivate_fkwmembership' );

// Call the fkwmembership() function to initialize the plugin.
fkwmembership();

/**
 * Get the instance of FKWMembership class and set up the plugin.
 *
 * @return FKWMembership|null
 */
function fkwmembership() {
    return FKW\Membership\FKWMembership::get_instance( FKWMEMBERSHIP_NAMESPACE, FKWMEMBERSHIP_VERSION );
}

/**
 * Activate the plugin.
 */
function activate_fkwmembership() {
    // Perform activation tasks, if any.
    fkwmembership()->activate();
}

/**
 * Deactivate the plugin.
 */
function deactivate_fkwmembership() {
    // Perform deactivation tasks, if any.
    fkwmembership()->deactivate();
}

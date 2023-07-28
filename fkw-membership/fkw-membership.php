<?php
/**
 * Plugin Name: FKW Membership
 * Plugin URI: https://dev.finarina.com/wordpress/fkw-membership
 * Description: Creates a membership system on your WordPress website. Integrates with WooCommerce.
 * Version: 1.0.0
 * Author: Your Name
 * Author URI: https://dev.finarina.com/wordpress
 * License: GPLv2 or later
 * Text Domain: fkwmembership
 * Domain Path: /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

define( 'FKWMEMBERSHIP_NAMESPACE', 'fkwmembership' );
define( 'FKWMEMBERSHIP_VERSION', '1.0.0' );

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
    return Finarina\Membership\FKWMembership::get_instance();
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

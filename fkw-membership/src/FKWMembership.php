<?php
namespace Finarina\Membership;

// Include necessary files or classes here, if needed.

class FKWMembership {

    /**
     * The single instance of the class.
     *
     * @var FKWMembership|null
     */
    private static $instance = null;

    /**
     * Plugin version.
     *
     * @var string
     */
    public $version = '1.0.0';

    /**
     * Get the single instance of the class.
     *
     * @return FKWMembership|null
     */
    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Constructor
     */
    private function __construct() {
        // Initialize the plugin.
        add_action( 'plugins_loaded', array( $this, 'init' ) );
    }

    /**
     * Initialize the plugin.
     */
    public function init() {
        // Load plugin text domain.
        add_action( 'init', array( $this, 'load_textdomain' ) );

        // render admin page settings
        new Admin();

        // Include other necessary files or classes here, if needed.
        
        // Register custom post types, taxonomies, shortcodes, etc.
        // Add plugin hooks and filters.
        // Set up Woocommerce and Woocommerce subscriptions integration.
    }

    /**
     * Load plugin text domain for translations.
     */
    public function load_textdomain() {
        load_plugin_textdomain( 'fkwmembership', false, dirname( plugin_basename( __FILE__ ) ) . '/../languages/' );
    }

    /**
     * Activate the plugin.
     */
    public function activate() {
        // Code to be executed when the plugin is activated.
    }

    /**
     * Deactivate the plugin.
     */
    public function deactivate() {
        // Code to be executed when the plugin is deactivated.
    }
}

// Instantiate the class.
FKWMembership::get_instance();

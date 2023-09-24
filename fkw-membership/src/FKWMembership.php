<?php
namespace FKW\Membership;

// Include necessary files or classes here, if needed.

class FKWMembership extends Base {

    /**
     * Plugin namespace.
     *
     * @var string
     */
    public $plugin_namespace = FKWMEMBERSHIP_NAMESPACE;

    /**
     * Plugin version.
     *
     * @var string
     */
    public $version = FKWMEMBERSHIP_VERSION;

    /**
     * Constructor
     *
     * @since 1.0.0
     * @return void
     */
    public function __construct() {
        // Initialize the plugin.
        add_action( 'plugins_loaded', array( $this, 'init' ) );
    }

    /**
     * Initialize the plugin.
     *
     * @since 1.0.0
     * @return void
     */
    public function init() {
        // Load plugin text domain.
        add_action( 'init', array( $this, 'load_textdomain' ) );

        // render admin page settings
        $admin = new Admin();
        $admin->admin_init();

        $subscription = Subscription::get_instance();
        $subscription->init();

    }

    /**
     * Load plugin text domain for translations.
     *
     * @since 1.0.0
     * @return void
     */
    public function load_textdomain() {
        load_plugin_textdomain( 'fkwmembership', false, dirname( plugin_basename( __FILE__ ) ) . '/../languages/' );
    }

    /**
     * Execute upon plugin activation.
     *
     * @since 1.0.0
     * @return void
     */
    public function activate() {
        // ensure db dependencies are installed
        $this->install_database_tables();
    }

    /**
     * Execute upon plugin deactivation.
     *
     * @since 1.0.0
     * @return void
     */
    public function deactivate() {
        // Code to be executed when the plugin is deactivated.
    }

    /**
     * Installs the database tables.
     *
     * @since 1.0.0
     * @return void
     */
    private function install_database_tables() {
        global $wpdb;

        $levels_table_name      = $wpdb->prefix . 'fkwmembership_levels';
        $points_table_name      = $wpdb->prefix . 'fkwmembership_levels_points';
        $points_logs_table_name = $wpdb->prefix . 'fkwmembership_levels_points_logs';
        $users_table_name       = $wpdb->prefix . 'users';

        /**
         * LEVELS DATABASE TABLE
         */
        if ( $wpdb->get_var( "SHOW TABLES LIKE '$levels_table_name'" ) != $levels_table_name ) {
            $charset_collate = $wpdb->get_charset_collate();

            // SQL query to create the table
            $sql = "CREATE TABLE $levels_table_name (
                id INT NOT NULL AUTO_INCREMENT,
                level_name VARCHAR(255) NOT NULL,
                level_access TEXT,
                free TINYINT(1) DEFAULT 0 NOT NULL,
                created DATETIME NOT NULL,
                modified DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL,
                PRIMARY KEY (id)
            ) $charset_collate;";

            require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
            dbDelta( $sql );

        }


        /**
         * POINTS DATABASE TABLES
         */
        if ( $wpdb->get_var( "SHOW TABLES LIKE '$points_table_name'" ) != $points_table_name ) {
            $charset_collate = $wpdb->get_charset_collate();

            // SQL query to create the table
            $sql = "CREATE TABLE $points_table_name (
                id INT NOT NULL AUTO_INCREMENT,
                level_id INT NOT NULL,
                points_interval INT NOT NULL,
                points_interval_type VARCHAR(255) NOT NULL,
                points_per INT NOT NULL,
                active TINYINT(1) DEFAULT 1 NOT NULL,
                created DATETIME NOT NULL,
                modified DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL,
                PRIMARY KEY (id),
                FOREIGN KEY (level_id) REFERENCES $levels_table_name (id)
            ) $charset_collate;";

            require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
            dbDelta( $sql );

        }

        if ( $wpdb->get_var( "SHOW TABLES LIKE '$points_table_name'" ) != $points_logs_table_name ) {
            $charset_collate = $wpdb->get_charset_collate();

            // SQL query to create the table
            $sql = "CREATE TABLE $points_logs_table_name (
                id INT NOT NULL AUTO_INCREMENT,
                user_id BIGINT(20) NOT NULL,
                point_action ENUM('Added', 'Removed', 'No Change') DEFAULT 'No Change' NOT NULL,
                point_action_by VARCHAR(255) DEFAULT 'System' NOT NULL,
                point_quantity INT DEFAULT 0 NOT NULL,
                modified DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL,
                PRIMARY KEY (id)
            ) $charset_collate;";

            require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
            dbDelta( $sql );

        }


    }
}

// Instantiate the class.
FKWMembership::get_instance();

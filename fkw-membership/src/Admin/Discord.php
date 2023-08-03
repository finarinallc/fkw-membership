<?php
namespace Finarina\Membership\Admin;

class Discord {

    /**
     * Constructor for the class.
     *
     * Hooks into WordPress admin menu to add the custom top-level menu link.
     * Hooks into the custom top-level menu page to handle form submissions.
     * Enqueues CSS stylesheet for the admin pages.
     */
    public function __construct() {
        // Hook into WordPress admin menu to add the custom top-level menu link.
        add_action( 'admin_menu', array( $this, 'add_points_subpage_menu' ) );

        // check if this person should have access to discord
        add_action( 'wp', array( $this, 'check_membership_access' ) );

        // integrate with discord
        add_action( 'init', array( $this, 'init_discord_integration' ) );

        // API integration
        add_action( 'wp_footer', array( $this, 'add_discord_script' ) );
    }

    public function add_points_subpage_menu() {
        // Add a submenu page for managing Discord Integration under Integrations.
        add_submenu_page(
            'fkwmembership',    // Parent menu slug
            'Discord Integration', // Page title
            'Discord',     // Menu title
            'manage_options',   // Capability required to access the submenu page
            'fkwmembership_discord', // Submenu slug
            array( $this, 'render_membership_settings_discord_page' ) // Callback function to render the submenu page
        );
    }

    /**
     * Renders the membership settings points page.
     *
     * @throws Exception If the user does not have permission to access the page.
     * @return void
     */
    public function render_membership_settings_woocommerce_page() {
        // Check if the user has the capability to access the page.
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have permission to access this page.'));
        }
    
        // Get the saved levels from the database
        $levels = get_option('fkwmembership_levels_available', array());
    
        // Code to handle form submissions for adding/editing/deleting points intervals
        if ( isset( $_POST['action'] ) ) {
            $this->handle_form_submitted( $_POST, $levels );
        }
        ?>
    
        <div class="wrap fkw-membership-settings-form">
            <h1>FKW Membership Discord Integration Settings</h1>
    
            
        </div>
        <?php
    }

    /**
     * Handles the form submission for discord integration.
     *
     * @param array $post_data The data submitted from the form.
     * @param object $discord The discord object.
     * @return void
     */
    private function handle_form_submitted( $post_data, $discord ) {
        if ($post_data['action'] === 'save_discord_integration_settings') {
            // Handle form submission for discord integration
            
        } 

        // Save the updated levels array back to the database
        update_option( 'fkwmembership_discord_integration', $discord );

        // Redirect back to the points page to prevent form resubmission
        wp_redirect( esc_url( admin_url( 'admin.php?page=fkwmembership_discord' ) ) );
        exit;
    }
    
    public function check_membership_access() {
        // Check if the user has access to Discord integration based on their membership level.
        if ( ! current_user_can( 'access_discord' ) ) {
            // Redirect user to a restricted page or show an error message.
            wp_die( __( 'You do not have access to Discord integration.' ) );
        }
    }

    public function init_discord_integration() {
        // Add custom capabilities for Discord integration access.
        $capabilities = array(
            'access_discord',
        );
        foreach ( $capabilities as $cap ) {
            $role = get_role( 'subscriber' );
            if ( $role ) {
                $role->add_cap( $cap );
            }
        }
    }

    public function add_discord_script() {
        // Enqueue the custom script
        wp_enqueue_script( 'discord-api', 'https://discord.com/api/gateway', array(), '1.0', true );
    
        // Localize the script with your Discord server details
        $server_details = array(
            'server_id' => 'YOUR_SERVER_ID',
            'api_key' => 'YOUR_API_KEY',
        );
        wp_localize_script( 'discord-api', 'serverDetails', $server_details );
    
        // Load your custom script that interacts with Discord API
        wp_enqueue_script( 'fkw-discord-api', plugin_dir_url( __FILE__ ) . '../public/assets/dist/js/fkw-discord.min.js', array( 'discord-api' ), '1.0', true );
    }
    
}
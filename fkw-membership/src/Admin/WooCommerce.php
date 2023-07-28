<?php
namespace Finarina\Membership\Admin;

class WooCommerce {

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
    }

    public function add_points_subpage_menu() {
        // Add a submenu page for managing WooCommerce Integration under Integrations.
        add_submenu_page(
            'fkwmembership',    // Parent menu slug
            'WooCommerce Integration', // Page title
            'WooCommerce',     // Menu title
            'manage_options',   // Capability required to access the submenu page
            'fkwmembership_woocommerce', // Submenu slug
            array( $this, 'render_membership_settings_woocommerce_page' ) // Callback function to render the submenu page
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
            <h1>FKW Membership WooCommerce Integration Settings</h1>
    
            
        </div>
        <?php
    }

    private function handle_form_submitted( $post_data, $woocommerce ) {
        if ($post_data['action'] === 'save_woocommerce_integration_settings') {
            // Handle form submission for woocommerce integration
            
        } 

        // Save the updated levels array back to the database
        update_option( 'fkwmembership_woocommerce_integration', $woocommerce );

        // Redirect back to the points page to prevent form resubmission
        wp_redirect( esc_url( admin_url( 'admin.php?page=fkwmembership_woocommerce' ) ) );
        exit;
    }

}
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

        // assign a level to a product for subscriptions
        add_action( 'woocommerce_product_options_general_product_data', array( $this, 'add_product_level_field' ) );

        // save product meta data info for the level chosen for this product
        add_action( 'woocommerce_process_product_meta', array( $this, 'save_product_level_field' ) );

        // add a subscription trial length field
        add_filter( 'woocommerce_subscription_product_trial_length', array( $this, 'set_subscription_trial_length' ), 10, 2 );

        // add a subscription price field
        add_filter( 'woocommerce_subscription_price_string', array( $this, 'custom_subscription_price_string' ), 10, 2 );
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
            No extra settings necessary for WooCommerce yet. Additional settings can be found in the product pages.
            
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

    public function add_product_level_field() {

        global $woocommerce, $post;

        echo '<div class="options_group">';

        woocommerce_wp_checkbox( array(
            'id'            => '_product_level',
            'label'         => 'Membership Level',
            'value'         => get_post_meta( $post->ID, '_product_level', true ),
            'desc_tip'      => true,
            'description'   => 'Select the membership level associated with this product.',
        ) );

        echo '</div>';
    }

    public function save_product_level_field( $post_id ) {
        $product_level = isset( $_POST['_product_level'] ) ? 'yes' : 'no';

        update_post_meta( $post_id, '_product_level', $product_level );
    }

    public function set_subscription_trial_length( $trial_length, $product ) {
        $product_level = get_post_meta( $product->get_id(), '_product_level', true );

        if ( 'yes' === $product_level ) {
            // Set trial length based on level, e.g., free level has 7 days trial, others have none.
            $trial_length = ( 'free' === $product->get_slug() ) ? 7 : 0;
        }

        return $trial_length;
    }

    public function custom_subscription_price_string( $subscription_string, $subscription ) {
        $product_id = $subscription->get_parent_id();
        $product_level = get_post_meta( $product_id, '_product_level', true );

        if ( 'yes' === $product_level ) {
            // Modify subscription price string based on level, e.g., show "Free" for free level.
            $subscription_string = ( 'free' === $subscription->get_slug() ) ? 'Free' : $subscription_string;
        }

        return $subscription_string;
    }
    
}
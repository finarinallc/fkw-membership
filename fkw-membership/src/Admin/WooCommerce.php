<?php
namespace FKW\Membership\Admin;

use FKW\Membership\Base;
use FKW\Membership\Admin;
use FKW\Membership\Admin\Levels;

class WooCommerce extends Base {

    /**
     * FKWMembership Admin settings id
     */
    public $settings_id;

    /**
     * Unique module name
     */
    public $module_name;

	/**
	 * Settings ID to associate fields with database
	 */
	public $module_settings_id;

    /**
     * Settings database ID to associate fields with
     */
    public $module_settings_database_id;

    /**
     * Constructor for the class.
     *
     * Hooks into WordPress admin menu to add the custom top-level menu link.
     * Hooks into the custom top-level menu page to handle form submissions.
     * Enqueues CSS stylesheet for the admin pages.
     */
    public function __construct() {
        // initiate the FKWMembership class
        $fkwmembership_admin = Admin::get_instance();
        $this->settings_id = $fkwmembership_admin->settings_id;

        $this->module_name = 'WooCommerce';
		$this->module_settings_id = $this->settings_id . '_woocommerce';
    }

    public function admin_init() {
        // assign a level to a product for subscriptions
        add_action( 'woocommerce_product_options_general_product_data', [ $this, 'add_product_level_field' ] );

        // save product meta data info for the level chosen for this product
        add_action( 'woocommerce_process_product_meta', [ $this, 'save_product_level_field' ] );

        // add a subscription price field
        add_filter( 'woocommerce_subscription_price_string', [ $this, 'custom_subscription_price_string' ], 10, 2 );
    }

    public function add_product_level_field() {
        global $woocommerce, $post;

        $levelClass = Levels::get_instance();
        $membership_levels = $levelClass->get_all_levels();

        echo '<div class="options_group">';

        // Create an array for the dropdown options.
        $dropdown_options = array('' => 'No Membership Level Associated');

        if (!empty($membership_levels) && is_array($membership_levels)) {
            foreach ($membership_levels as $level) {
                $level_id = $level['id'];
                $level_name = $level['level_name'];
                $dropdown_options[$level_id] = 'Product for: ' . $level_name;
            }
        }

        woocommerce_wp_select(array(
            'id'            => '_product_membership_level',
            'label'         => 'Associated Membership Level',
            'value'         => get_post_meta($post->ID, '_product_membership_level', true),
            'options'       => $dropdown_options,
            'desc_tip'      => true,
            'description'   => 'Select the membership level associated with this product, meaning, if someone wants a membership, they need to buy this product to activate the membership.',
        ));

        echo '</div>';
    }

    public function save_product_level_field( $post_id ) {
        $product_level = intval( $_POST['_product_level'] );

        if( !empty( $product_level ) && is_int( $product_level ) ) {
            update_post_meta( $post_id, '_product_membership_level', $product_level );
        }
    }

    public function custom_subscription_price_string( $subscription_string, $subscription ) {
        $product_id = $subscription->get_parent_id();
        $product_level = get_post_meta( $product_id, '_product_membership_level', true );

        if( !empty( $product_level ) && is_int( $product_level ) ) {
            $levelClass = Levels::get_instance();

            if( $levelClass->is_level_free( $product_level ) ) {
                $subscription_string = 'Free';
            }
        }

        return $subscription_string;
    }

}

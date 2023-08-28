<?php
namespace FKW\Membership;

use FKW\Membership\FKWMembership;

use FKW\Membership\Admin\SettingsPage;

use FKW\Membership\Admin\Levels;
use FKW\Membership\Admin\Points;
use FKW\Membership\Admin\WooCommerce;
use FKW\Membership\Admin\Discord;

class Admin {

	/**
     * The single instance of the class.
     *
     * @var Admin|null
     */
    private static $instance = null;

    /**
     * FKWMembership class object
	 *
     * @var FKWMembership|null
     */
    public $fkwmembership;

	/**
	 * FKWMembership SettingsPage object
	 *
     * @var SettingsPage|null
	 */
	public $fkwmembership_settingspage;

	/**
	 * Settings ID to associate fields with database
	 */
	public $settings_id;

    /**
     * Settings database ID to associate fields with
     */
    public $settings_database_id;

	/**
	 * Settings page ID to associate fields with specific page
	 */
	public $settings_page_id;

	/**
	 * General settings object to create page fields
	 */
	public $settings_page;

	/**
     * Get the single instance of the class.
     *
     * @return Admin|null
     */
    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }

        return self::$instance;
    }

	/**
	 * Constructor for the class.
	 *
	 * Initializes the class and sets up the necessary properties.
	 *
	 * @return void
	 */
	public function __construct() {

        $this->fkwmembership = FKWMembership::get_instance();
		$this->fkwmembership_settingspage = SettingsPage::get_instance();

		$this->settings_id = $this->fkwmembership->plugin_namespace . '_settings';
        $this->settings_database_id = $this->settings_id . '_general';
		$this->settings_page_id = $this->settings_id . '_page';


	}

    public function init() {

		// enqueues the styles and scripts for the admin area specificially
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_admin_styles' ] );

		// registers the settings group and fields to that new page
		add_action( 'admin_init', [ $this, 'register_systems_settings_group' ] );
        add_action( 'admin_init', [ $this, 'register_access_settings_group' ] );

		// creates a settings page area
		add_action( 'admin_menu', [ $this, 'add_top_level_menu' ] );

        $admin_levels = new Levels();
		$admin_levels->init();

        $options = get_option( $this->settings_database_id );

        if( !empty( $options ) && is_array( $options ) ) {

            if( !empty( $system = $options['system_features'] ) ) {

                $points = !empty( $system['member_points_system'] ) ? (int)$system['member_points_system'] : 0;
                $woocommerce = !empty( $system['member_woocommerce_integration'] ) ? (int)$system['member_woocommerce_integration'] : 0;
                $discord = !empty( $system['member_discord_integration'] ) ? (int)$system['member_discord_integration'] : 0;

                if ( $points === 1 ) {
                    $admin_points = new Points();
					$admin_points->init();
                }

                if ( $woocommerce === 1 ) {
                    new WooCommerce();
                }

                if ( $discord === 1 ) {
                    new Discord();
                }

            }

        }

	}

    /**
     * Enqueue the admin CSS stylesheet.
     *
     * @return void
     */
    public function enqueue_admin_styles() {
        // Enqueue the admin CSS stylesheet
        wp_enqueue_style( FKWMEMBERSHIP_NAMESPACE . '-admin-style', FKWMEMBERSHIP_PLUGIN_BASEURL . 'assets/dist/css/admin.css' );
    }

    /**
	 * Adds a top-level menu to the WordPress admin menu.
	 *
	 * @since    1.0.0
	 */
	public function add_top_level_menu() {

        add_menu_page(
            __( FKWMEMBERSHIP_NAME, FKWMEMBERSHIP_NAMESPACE ),
            __( FKWMEMBERSHIP_NAME, FKWMEMBERSHIP_NAMESPACE ),
            'manage_options',
            $this->settings_id,
            [ $this, 'render_settings_page' ],
            'dashicons-groups',
            71
        );

	}

    public function register_systems_settings_group() {

        register_setting( $this->settings_page_id, $this->settings_database_id );

		$this->fkwmembership_settingspage->init(
			$this->settings_id,
            $this->settings_database_id,
			$this->settings_page_id,
			$this->settings_id . '_systems',
			'General Settings',
			'Enable or disable system wide settings for your membership configuration. Disabling a feature will turn off the feature on the website, but retain the data in the database. If you need to clear the data from the database, you can do so by clearing the member data here.'
		);

		$this->fkwmembership_settingspage->register_settings_init();

		$fields_to_register = [
			'System Features',
			[
				'id' => $this->settings_id . '_system_features',
				'field' => $this->settings_id . '_system_features_val',
				'name' => 'system_features',
				'type' => 'checkbox',
				'value' => [
					'activate_system' => 'Membership System',
					'member_new_registration' => 'New Member Registration',
					'individual_member_registration' => 'Individual Member Registrations',
					'organization_member_registration' => 'Organization/Business Member Registrations',
					'member_points_system' => 'Member Points System',
					'member_woocommerce_integration' => 'Member WooCommerce Integration',
					'member_discord_integration' => 'Member Discord Integration',
				],
				'placeholder' => '',
				'default_value' => '',
				'class' => 'form-field',
                'style' => ''
			]
		];

		$this->fkwmembership_settingspage->register_settings_field_to_section(
			...$fields_to_register
		);

	}

    public function register_access_settings_group() {

        register_setting( $this->settings_page_id, $this->settings_id . '_general' );

		$this->fkwmembership_settingspage->init(
			$this->settings_id,
            $this->settings_id . '_general',
			$this->settings_page_id,
			$this->settings_id . '_access',
			'Access Settings',
			'Enable or disable accessibility specific allowances for your members. Disabling a feature will turn off the feature on the website, but retain the data in the database. If you need to clear the data from the database, you can do so by clearing the member data here.'
		);

		$this->fkwmembership_settingspage->register_settings_init();

		$fields_to_register = [
			'Access Settings',
			[
				'id' => $this->settings_id . '_access_settings',
				'field' => $this->settings_id . '_access_settings_val',
				'name' => 'access_settings',
				'type' => 'checkbox',
				'value' => [
					'exclusive_content' => 'Exclusive Content Access',
					'early_access' => 'Early Access to Content',
					'live_chat_access' => 'Live Chat Access',
					'event_access' => 'Event Access',
					'discount_code_access' => 'Discount Code Access',
				],
				'placeholder' => '',
				'default_value' => '',
				'class' => 'form-field',
				'style' => ''
			]
		];

		$this->fkwmembership_settingspage->register_settings_field_to_section(
			...$fields_to_register
		);

	}

	public function render_settings_page() {

		if ( !current_user_can( 'manage_options' ) ) {
            return;
        }

		if ( isset( $_GET['settings-updated'] ) ) {
			add_settings_error(
                $this->fkwmembership->plugin_namespace . '-messages',
                $this->fkwmembership->plugin_namespace . '-message',
                __( 'Settings updated', $this->fkwmembership->plugin_namespace ), 'updated' );
		}

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'partials/admin/settings-page.php';

	}

}

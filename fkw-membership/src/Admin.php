<?php
namespace Finarina\Membership;

use Finarina\Membership\Admin\Levels;
use Finarina\Membership\Admin\Points;
use Finarina\Membership\Admin\WooCommerce;
use Finarina\Membership\Admin\Discord;

class Admin {

    private $submission_status;

    private $default_membership_system_settings = [
        'activate_system' => [
            'Membership System',
            1
        ],
        'member_new_registration' => [
            'New Member Registration',
            0
        ],
        'individual_member_registration' => [
            'Individual Member Registrations',
            0
        ],
        'organization_member_registration' => [
            'Organization/Business Member Registrations',
            0
        ],
        'member_points_system' => [
            'Member Points System',
            0
        ],
        'member_woocommerce_integration' => [
            'Member WooCommerce Integration',
            0
        ],
        'member_discord_integration' => [
            'Member Discord Integration',
            0
        ]
    ];

    private $default_access_settings_settings = [
        'exclusive_content' => [
            'Exclusive Content Access',
            0
        ],
        'early_access' => [
            'Early Access to Content',
            0
        ],
        'live_chat_access' => [
            'Live Chat Access',
            0
        ],
        'event_access' => [
            'Event Access',
            0
        ],
        'discount_code_access' => [
            'Discount Code Access',
            0
        ]
    ];

    private $default_all_settings;

    /**
     * Constructor for the class.
     *
     * Hooks into WordPress admin menu to add the custom top-level menu link.
     * Hooks into the custom top-level menu page to handle form submissions.
     * Enqueues CSS stylesheet for the admin pages.
     */
    public function __construct() {
        // Initialize default settings
        $this->default_all_settings = [ 
            'membership_system' => $this->default_membership_system_settings,
            'access_settings' => $this->default_access_settings_settings,
        ];
        // Hook into WordPress admin menu to add the custom top-level menu link.
        add_action( 'admin_menu', array( $this, 'add_top_level_menu' ) );

        // Enqueue CSS stylesheet for the admin pages
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_styles' ) );
    }

    /**
     * Enqueue the admin CSS stylesheet.
     *
     * @return void
     */
    public function enqueue_admin_styles() {
        // Enqueue the admin CSS stylesheet
        wp_enqueue_style( 'fkwmembership-admin-style', plugin_dir_url( __FILE__ ) . '../public/assets/dist/css/admin.css' );
    }

    /**
     * A function to add top-level menu links for managing membership settings.
     *
     * @return void
     */
    public function add_top_level_menu() {
        // Add a top-level menu link for managing member levels.
        add_menu_page(
            'FKW Membership',   // Page title
            'FKW Membership',   // Menu title
            'manage_options',   // Capability required to access the menu page
            'fkwmembership',    // Menu slug
            array( $this, 'render_membership_settings_page' ), // Callback function to render the menu page
            'dashicons-groups', // Icon for the menu link
            30 // Position of the menu link in the admin menu
        );

        register_setting( 'fkwmembership_settings_group', 'fkwmembership_general_membership_system' );

        new Levels();

        $options = get_option( 'fkwmembership_general_membership_system', array() );

        if( !empty( $options ) && is_array( $options ) ) {

            $system = $options['membership_system'];

            if( !empty( $system ) ) {

                $points = !empty( $system['member_points_system'] ) ? $system['member_points_system'] : 0;
                $woocommerce = !empty( $system['member_woocommerce_integration'] ) ? $system['member_woocommerce_integration'] : 0;
                $discord = !empty( $system['member_discord_integration'] ) ? $system['member_discord_integration'] : 0;

                if ( $points === 1 ) {
                    new Points();
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
     * Renders the membership settings page.
     *
     * @return void
     */
    public function render_membership_settings_page(): void{
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have permission to access this page.'));
        }

        // Check if form is submitted and update the settings
        if ( isset( $_POST['submit'] ) ) {
            $this->handle_form_submitted( $_POST );

            // Display a success message
            if( !empty( $this->submission_status ) && is_array( $this->submission_status ) ) {
                echo '<div class="' . $this->submission_status[0] . '"><p>' . $this->submission_status[1] . '</p></div>';
            }
        }

        // reset the submission status so it doesnt keep appearing
        $this->submission_status = NULL;

        if( empty( $fkwmembership_db_data = get_option( 'fkwmembership_general_membership_system' ) ) ) {
            $settings = $this->default_all_settings;
        } else {
            $settings = $fkwmembership_db_data;
        }
        ?>

        <div class="wrap fkw-membership-settings-form">
            <h1>FKW Membership General Settings</h1>

            <form method="post">
                <input type="hidden" name="fkwmembership_settings_type" value="save_fkwmemebership_general_settings">
                <?php settings_fields('fkwmembership_general_membership_system'); ?>
                <?php do_settings_sections('fkwmembership_general_membership_system'); ?>

                <?php var_dump( $settings ); ?>

                <h2>Membership System Settings</h2>
                <table class="form-table">
                    <tr valign="top">
                        <th scope="row">Enable/Disable Settings</th>
                        <td>
                            <div class="fields-group">
                            <?php foreach( $settings['membership_system'] as $key => $setting ) {
                                echo '<input type="hidden" name="membership_system[' . $key . '][0]" value="' . $setting[0] . '" />';
                                $this->render_checkbox_row( 'membership_system[' . $key . '][1]', $setting[1], $setting[0] );
                            }
                            ?>
                            </div>
                        </td>
                    </tr>
                </table>

                <h2>Access Settings</h2>
                <table class="form-table">
                    <tr valign="top">
                        <th scope="row">Enable/Disable Access Features</th>
                        <td>
                            <div class="fields-group">
                            <?php foreach( $settings['access_settings'] as $key => $setting ) {
                                $this->render_checkbox_row( 'access_settings[' . $key . '][1]', (int)$setting[1], $setting[0] );
                            }
                            ?>
                            </div>
                        </td>
                    </tr>
                </table>

                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }
    
    /**
     * Renders a checkbox row in HTML table format.
     *
     * @param string $field_name The name of the checkbox field.
     * @param mixed $field_value The value of the checkbox field.
     * @param string $field_label The label of the checkbox field.
     * @return void
     */
    private function render_checkbox_row($field_name, $field_value, $field_label) {
        ?>
        <label>
            <input type="checkbox" name="<?php echo $field_name; ?>" value="1" <?php checked($field_value, 1); ?>>
            Enable <?php echo $field_label; ?>
        </label>
        <?php
    }

    /**
     * Handles the form submission for discord integration.
     *
     * @param array $post_data The data submitted from the form.
     * @param object $discord The discord object.
     * @return void
     */
    private function handle_form_submitted( $post_data ) {
        if ( !empty( $post_data['fkwmembership_settings_type'] ) && $post_data['fkwmembership_settings_type'] === 'save_fkwmemebership_general_settings' ) {            
            // Save the submitted settings
            $settings = array(
                'membership_system' => isset($_POST['membership_system']) ? $_POST['membership_system'] : $this->default_membership_system_settings,
                'access_settings' => isset($_POST['access_settings']) ? $_POST['access_settings'] : $this->default_access_settings_settings,
            );

            // Save the updated settings to the database
            update_option('fkwmembership_general_membership_system', $settings);

            $this->submission_status = ['updated', 'Settings saved successfully.'];
            
        }

        $this->submission_status = ['error', 'Settings could not save due to an error.'];

        // Redirect back to the points page to prevent form resubmission
        wp_redirect( esc_url( admin_url( 'admin.php?page=fkwmembership' ) ) );
        exit;
    }
    
}

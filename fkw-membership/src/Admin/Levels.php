<?php
namespace FKW\Membership\Admin;

use FKW\Membership\FKWMembership;
use FKW\Membership\Admin;
use FKW\Membership\Admin\SettingsPage;

class Levels {

    /**
     * The single instance of the class.
     *
     * @var Levels|null
     */
    private static $instance = null;

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
	 * Settings page ID to associate fields with specific page
	 */
	public $module_settings_subpage_id;

	/**
	 * General settings object to create page fields
	 */
	public $module_settings_subpage;

    /**
     * Get the single instance of the class.
     *
     * @return Levels|null
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

        $fkwmembership = FKWMembership::get_instance();

        $fkwmembership_admin = Admin::get_instance();
        $this->settings_id = $fkwmembership_admin->settings_id;

        $this->module_name = 'Levels';
		$this->module_settings_id = $this->settings_id . '_levels';
        $this->module_settings_database_id = $fkwmembership->plugin_namespace . '_levels';
		$this->module_settings_subpage_id = $this->module_settings_database_id . '_page';

    }

    /**
     * Initializes the admin page functionality.
     *
     * @return void
     */
    public function init() {
        // Hook into WordPress admin menu to add the custom top-level menu link.
        add_action( 'admin_menu', [ $this, 'add_subpage_menu' ] );
    }

    /**
     * Adds a subpage menu to the parent page.
     *
     * @return void
     */
    public function add_subpage_menu() {

        add_submenu_page(
            $this->settings_id,
            $this->module_name . ' Settings',
            $this->module_name,
            'manage_options',
            $this->module_settings_id,
            [ $this, 'render_settings_subpage' ]
        );

    }

    /**
     * Renders the membership settings levels page.
     *
     * @throws None
     * @return None
     */
    public function render_settings_subpage() {
        // Check if the user has the capability to access the page.
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have permission to access this page.'));
        }

        // Code to handle form submissions for adding/editing/deleting levels
        $levels = $this->check_current_form_submission( $_POST );

        $settings_general = get_option( 'fkwmembership_settings_general', [] );
        ?>

        <div class="wrap fkwmembership-form-fields">
            <h1>FKW Membership Levels Settings</h1>

            <?php if( !empty( $settings_general ) ) {
                if( empty( $settings_general['access_settings'] ) ) {
            ?>
            <p>There are no access options set. Please go back to the Settings page to activate access options before you can make membership levels.</p>
            <?php } else {
                $access_options = $settings_general['access_settings'];

                require_once FKWMEMBERSHIP_PLUGIN_BASENAME . 'partials/admin/settings-levels-page.php';
            }
        }
    }

    /**
	 * Checks the current form submission and handles it accordingly.
	 *
	 * @param array $post_data The form submission data.
	 * @return array The saved levels from the database.
	 */
	public function check_current_form_submission( $post_data )
	{
        $module_data = $this->handle_form_submitted( $post_data );
		// Handle form submissions for adding/editing/deleting levels
		if ( isset( $post_data['action'] ) ) {
			// Display a success message
			if ( !empty( $this->submission_status ) && is_array( $this->submission_status ) ) {
				$message = sprintf(
					'<div class="%s"><p>%s</p></div>',
					$this->submission_status[0], $this->submission_status[1]
				);

				echo $message;
			}

		}

		return $module_data;
	}

    /**
     * Handle the submitted form data.
     *
     * @param array $post_data The data submitted via the form.
     * @return array The results retrieved from the database.
     */
    public function handle_form_submitted( $post_data ) {
        global $wpdb;
        $table_name = $wpdb->prefix . $this->module_settings_database_id;

        if( !empty( $post_data ) ) {

            $action = $post_data['action'];
            $action_formatted = strtolower( $this->module_name );

            if ( $action === 'add_' . $action_formatted ) {

                $post_data['created'] = current_time( 'mysql' );

                // Insert the new level data into the database
                $wpdb->insert(
                    $table_name,
                    [
                        'level_name' => $post_data['level_name'],
                        'level_access' => serialize( $post_data['level_access'] ),
                        'created' => $post_data['created'],
                        'modified' => $post_data['created'], // Initial modification time is the same as creation time
                    ],
                    [ '%s', '%s', '%s', '%s' ]
                );

                $this->submission_status = [ 'updated', ucfirst( $this->module_name ) . ' added successfully.' ];
            } else {
                $level_id = $post_data['level_id'];

                // Retrieve the existing level data from the database
                $existing_level = $wpdb->get_row(
                    $wpdb->prepare( "SELECT * FROM $table_name WHERE id = %d", $level_id ),
                    ARRAY_A
                );

                if ( !empty( $existing_level ) ) {
                    if( $action === 'edit_' . $action_formatted ) {
                        // Update the existing level data in the database
                        $wpdb->update(
                            $table_name,
                            [
                                'level_name' => $post_data['level_name'],
                                'level_access' => serialize( $post_data['level_access'] ),
                                'modified' => current_time( 'mysql' ),
                            ],
                            [ 'id' => $level_id ],
                            [ '%s', '%s', '%s' ],
                            [ '%d' ]
                        );

                        $this->submission_status = [ 'updated', ucfirst( $this->module_name ) . ' modified successfully.' ];
                    } elseif ( $action === 'delete_' . $action_formatted ) {
                        // Delete the existing level data from the database
                        $wpdb->delete(
                            $table_name,
                            [ 'id' => $level_id ],
                            [ '%d' ]
                        );

                        $this->submission_status = [ 'updated', ucfirst( $this->module_name ) . ' deleted successfully.' ];
                    }
                }
            }

            if( empty( $this->submission_status ) ) {
                $this->submission_status = [
                    'error',
                    ucfirst( $this->module_name ) . ' could not be saved due to an unforeseen error.'
                ];
            }
        }

        return $wpdb->get_results( "SELECT * FROM $table_name", ARRAY_A );
    }

}

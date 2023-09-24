<?php
namespace FKW\Membership\Admin;

use FKW\Membership\FKWMembership;
use FKW\Membership\Admin;
use FKW\Membership\Admin\Levels;

class Points {

    /**
     * The single instance of the class.
     *
     * @var Points|null
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
     * @return Points|null
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

        $fkwmembership_levels = Levels::get_instance();
        $this->levels_settings_database_id = $fkwmembership_levels->module_settings_database_id;

        $this->module_name = 'Points';
		$this->module_settings_id = $this->settings_id . '_points';
        $this->module_settings_database_id = $this->levels_settings_database_id . '_points';
		$this->module_settings_subpage_id = $this->module_settings_database_id . '_page';

    }

    /**
     * Retrieves all point intervals from the database.
     *
     * @return array An array of all levels.
     */
    public function get_all_points_intervals() {
        global $wpdb;
        $table_name = $wpdb->prefix . $this->module_settings_database_id;

        return $wpdb->get_results( "SELECT * FROM $table_name", ARRAY_A );
    }

    /**
     * Initializes the admin page functionality.
     *
     * @return void
     */
    public function admin_init() {
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
     * Renders the membership settings points page.
     *
     * @throws Exception If the user does not have permission to access the page.
     * @return void
     */
    public function render_settings_subpage() {
        // Check if the user has the capability to access the page.
        if ( !current_user_can( 'manage_options' ) ) {
            wp_die( __( 'You do not have permission to access this page.' ) );
        }

        global $wpdb;
        $levels_table_name = $wpdb->prefix . $this->levels_settings_database_id;
        $points_logs_table_name = $wpdb->prefix . $this->module_settings_database_id . '_logs';

        // Code to handle form submissions for adding/editing/deleting levels
        $points = $this->check_current_form_submission( $_POST );

        $levels = $wpdb->get_results( "SELECT * FROM $levels_table_name", ARRAY_A );
        ?>

        <div class="wrap fkwmembership-form-fields">
            <h1>FKW Membership Points Settings</h1>

        <?php if( empty( $levels ) ) {
            ?>
            <p>There are no levels set. Please go back to the Levels page and create a level or two before you can assign point intervals to levels.</p>
        <?php } else {
            $points_users_sql = "
                SELECT
                    u.ID AS user_id,
                    u.user_login AS user_name,
                    l.level_name AS level_name,
                    IFNULL(pm2.meta_value, 0) AS total_points
                FROM {$wpdb->users} u
                LEFT JOIN {$wpdb->usermeta} pm1 ON u.ID = pm1.user_id
                    AND pm1.meta_key = 'fkwmembership_level_assignment'
                LEFT JOIN {$levels_table_name} l ON pm1.meta_value = l.id
                LEFT JOIN {$wpdb->usermeta} pm2 ON u.ID = pm2.user_id
                    AND pm2.meta_key = 'fkwmembership_points_total'
                GROUP BY u.ID
                ORDER BY u.user_login
            ";

            $points_users = $wpdb->get_results( $points_users_sql, ARRAY_A );

            $points_logs_table = new Admin\PointsLogs();

            require_once FKWMEMBERSHIP_PLUGIN_BASENAME . 'partials/admin/settings-points-page.php';
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
        $points_table_name = $wpdb->prefix . $this->module_settings_database_id;
        $levels_table_name = $wpdb->prefix . $this->levels_settings_database_id;

        $fkwmembership_member = Admin\Member::get_instance();
        $fkwmembership_pointslogs = Admin\PointsLogs::get_instance();

        if( !empty( $post_data ) ) {

            $action = $post_data['action'];

            if( $action === 'modify_points_users' ) {

                $points_user_id = filter_var( $post_data['points_user_id'], FILTER_VALIDATE_INT );
                $new_points_quantity = filter_var( $post_data['new_points_quantity'], FILTER_VALIDATE_FLOAT );

                if( !empty( $points_user_id && !empty( $new_points_quantity ) ) ) {

                    // set the point action based on the point difference
                    $current_points = $fkwmembership_member->get_points_total( $points_user_id );

                    if( $current_points < $new_points_quantity ) {
                        $point_action = 'Removed';
                    } else if ( $current_points > $new_points_quantity ) {
                        $point_action = 'Added';
                    } else {
                        $point_action = 'No Change';
                    }

                    // grab the current user ID to show whos making this change
                    $point_action_by = get_current_user_id();

                    // update the points
                    $fkwmembership_member->set_points_total( $points_user_id, $new_points_quantity );

                    // log the point change
                    $fkwmembership_pointslogs->add_new_log( $points_user_id, $point_action, $new_points_quantity, $point_action_by );

                    $this->submission_status = [ 'updated', ucfirst( $this->module_name ) . ' quantity modified successfully.' ];

                }

            }

            if ( $action === 'add_points_interval' ) {

                $post_data['created'] = current_time( 'mysql' );

                // Insert the new points data into the database
                $wpdb->insert(
                    $points_table_name,
                    [
                        'level_id' => $post_data['level_id'],
                        'points_interval' => $post_data['points_interval'],
                        'points_interval_type' => $post_data['points_interval_type'],
                        'points_per' => $post_data['points_per'],
                        'active' => $post_data['points_status'],
                        'created' => $post_data['modified'],
                        'modified' => $post_data['modified'], // Initial modification time is the same as creation time
                    ],
                    [ '%d', '%s', '%s', '%d', '%d', '%s', '%s' ]
                );

                $this->submission_status = [ 'updated', ucfirst( $this->module_name ) . ' added successfully.' ];
            }

            if( $action === 'edit_points_interval' || $action === 'delete_points_interval' ) {
                $points_id = $post_data['points_id'];

                // Retrieve the existing points data from the database
                $existing_points = $wpdb->get_row(
                    $wpdb->prepare( "SELECT * FROM $points_table_name WHERE id = %d", $points_id ),
                    ARRAY_A
                );

                if ( !empty( $existing_points ) ) {
                    if( $action === 'edit_points_interval' ) {
                        // Update the existing points data in the database
                        $wpdb->update(
                            $points_table_name,
                            [
                                'level_id' => $post_data['level_id'],
                                'points_interval' => $post_data['points_interval'],
                                'points_interval_type' => $post_data['points_interval_type'],
                                'points_per' => $post_data['points_per'],
                                'active' => $post_data['points_status'],
                                'modified' => current_time( 'mysql' ), // Initial modification time is the same as creation time
                            ],
                            [ 'id' => $points_id ],
                            [ '%d', '%s', '%s', '%d', '%d', '%s' ],
                            [ '%d' ]
                        );

                        $this->submission_status = [ 'updated', ucfirst( $this->module_name ) . ' modified successfully.' ];
                    } elseif ( $action === 'delete_points_interval' ) {
                        // Delete the existing points data from the database
                        $wpdb->delete(
                            $points_table_name,
                            [ 'id' => $points_id ],
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

        return $wpdb->get_results( "SELECT $points_table_name.*, $levels_table_name.level_name
            FROM $points_table_name
            JOIN $levels_table_name ON $points_table_name.level_id = $levels_table_name.id", ARRAY_A );
    }

}

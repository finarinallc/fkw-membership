<?php
namespace FKW\Membership\Admin;

use FKW\Membership\FKWMembership;
use FKW\Membership\Admin;
use FKW\Membership\Admin\Points;

require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';

class PointsLogs extends \WP_List_Table {

	/**
     * The single instance of the class.
     *
     * @var PointsLogs|null
     */
    private static $instance = null;

	/**
     * Get the single instance of the class.
     *
     * @return PointsLogs|null
     */
    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }

        return self::$instance;
    }

	/**
	 * Empty constructor
	 */
	public function __construct() {
		parent::__construct(
			array(
				'singular' => 'Log',
				'plural'   => 'Logs',
			)
		);
	}

	/**
	 * Adds a new log entry to the points logs table.
	 *
	 * @param int $user_id The ID of the user.
	 * @param string $point_action The action performed.
	 * @param int $point_quantity The quantity of points.
	 * @param string $point_action_by The entity that performed the action. Default is 'System'.
	 * @param string|null $modified The modified date. Default is the current time.
	 * @return bool True on success, false on failure.
	 */
	public function add_new_log( $user_id, $point_action, $point_quantity, $point_action_by = 'System', $modified = NULL ) {
		global $wpdb;
        $points_logs_table_name = $wpdb->prefix . 'fkwmembership_levels_points_logs';

		if( empty( $modified ) ) {
			$modified = current_time( 'mysql' );
		}

		$wpdb->insert(
			$points_logs_table_name,
			[
				'user_id' => $user_id,
				'point_action' => $point_action,
				'point_action_by' => $point_action_by,
				'point_quantity' => $point_quantity,
				'modified' => $modified,
			],
			[ '%d', '%s', '%s', '%d', '%s' ]
		);

		return true;
	}

	/**
	 * Retrieves the columns for the specified table.
	 *
	 * @return array The columns for the table.
	 */
	public function get_columns() {
		return array(
			'user_login' => 'User',
			'point_action' => 'Action',
			'action_login' => 'Initiated By',
			'point_quantity' => 'Quantity',
			'modified' => 'Timestamp',
		);
	}

	/**
	 * Prepare the items for display.
	 *
	 * @return void
	 */
	public function prepare_items() {
		global $wpdb;

		$this->_column_headers = $this->get_column_info();

		$points_logs_table_name = $wpdb->prefix . 'fkwmembership_levels_points_logs';
		$levels_table_name      = $wpdb->prefix . 'fkwmembership_levels';
		$users_table_name       = $wpdb->prefix . 'users';

		$sql = "SELECT $points_logs_table_name.*,
					username_table.user_login AS user_login,
					actionname_table.user_login AS action_login
				FROM $points_logs_table_name
		LEFT JOIN $users_table_name AS username_table
				ON $points_logs_table_name.user_id = username_table.ID
		LEFT JOIN $users_table_name AS actionname_table
				ON $points_logs_table_name.point_action_by = actionname_table.ID";


		$points = $wpdb->get_results( $sql, ARRAY_A	);

		$per_page     = 10;
		$current_page = $this->get_pagenum();
		$total_items  = count( $points );

		$this->set_pagination_args(
			array(
				'total_items' => $total_items,
				'per_page'    => $per_page,
			)
		);

		var_dump($points);

		$this->items = array_slice( $points, ( $current_page - 1 ) * $per_page, $per_page );
	}

	/**
	 * Retrieves the value of a specific column for a given item.
	 *
	 * @param mixed $item The item to retrieve the column value from.
	 * @param string $column_name The name of the column to retrieve the value from.
	 * @return mixed The value of the specified column for the given item.
	 */
	public function column_default( $item, $column_name ) {
		return $item[ $column_name ];
	}
}

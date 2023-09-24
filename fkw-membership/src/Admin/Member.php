<?php
namespace FKW\Membership\Admin;

use FKW\Membership\FKWMembership;
use FKW\Membership\Admin;
use FKW\Membership\Admin\Levels;
use FKW\Membership\Admin\Points;


class Member {

    /**
     * The single instance of the class.
     *
     * @var Member|null
     */
    private static $instance = null;

    /**
     * Default points set for a user.
     */
    public $default_points_total = 0;

    /**
     * Default level assignment for a user.
     */
    public $default_level_assignment = NULL;

    /**
     * Get the single instance of the class.
     *
     * @return Member|null
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

    }

    /**
     * Initializes the admin page functionality.
     *
     * @return void
     */
    public function admin_init() {
        // adds the fkw membership related fields to the individual user
        add_action('show_user_profile', [ $this, 'add_membership_level_field' ] );
        add_action('edit_user_profile', [ $this, 'add_membership_level_field' ] );

        // modifies the saving process during the user save to include the fkw membership data
        add_action('personal_options_update', [ $this, 'save_membership_level_field' ] );
        add_action('edit_user_profile_update', [ $this, 'save_membership_level_field' ] );
    }

    /**
     * Gets level for a user.
     *
     * @param int $user_id The ID of the user.
     * @return void
     */
    public static function get_level_user( $user_id ) {
        get_user_meta( $user_id, 'fkwmembership_level_assignment', true );
    }

    /**
     * Assigns a level to a user.
     *
     * @param int $user_id The ID of the user.
     * @param int $level_id The ID of the level to assign.
     * @return void
     */
    public static function set_level_user( $user_id, $level_id ) {
        update_user_meta( $user_id, 'fkwmembership_level_assignment', $level_id );
    }

    /**
     * Gets the total points for a user.
     *
     * @param int $user_id The ID of the user.
     * @param int $points_total The total points to be set for the user.
     */
    public static function get_points_total( $user_id ) {
        get_user_meta( $user_id, 'fkwmembership_points_total', true );
    }

    /**
     * Sets the total points for a user.
     *
     * @param int $user_id The ID of the user.
     * @param int $points_total The total points to be set for the user.
     */
    public static function set_points_total( $user_id, $points_total ) {
        if( $points_total < 0 ) {
            update_user_meta( $user_id, 'fkwmembership_points_total', 0 );
        } else {
            update_user_meta( $user_id, 'fkwmembership_points_total', $points_total );
        }
    }

    /**
     * Adds a membership level field to the user's profile.
     *
     * @param object $user The user object.
     * @return void
     */
    public function add_membership_level_field( $user ) {
        $fkwmembership_levels = Levels::get_instance();

        $selected_level = get_user_meta( $user->ID, 'fkwmembership_level_assignment', true );
        $available_levels = $fkwmembership_levels->get_all_levels();
        $field_options = '';

        foreach ( $available_levels as $levels ) {
            $field_options .= '<option value="' . esc_attr( $levels['id'] ) . '" ' . selected($selected_level, $levels['id'], false) . '>' . esc_html( $levels['level_name'] ) . '</option>';
        }
        ?>

        <div class="fkwmembership-admin-userprofile fkwmembership-form-fields">
            <h3>FKW Membership</h3>
            <table class="form-table">
                <tr>
                    <th><label for="fkwmembership_level_assignment">Membership Level</label></th>
                    <td>
                        <select name="fkwmembership_level_assignment" id="fkwmembership_level_assignment">
                            <option value="">No Level Set</option>';
                            <?php echo $field_options; ?>
                        </select>
                        <p><em>NOTE: If you are using WooCommerce, if this user has an active subscription, this will get overrided by the subscription when it renews. If you don't want that to happen, modify the membership level at the WooCommerce level in the user's subscription.</em></p>
                    </td>
                </tr>
            </table>
        </div>
        <?php
    }

    /**
     * Saves the membership level field for a user.
     *
     * @param int $user_id The ID of the user.
     * @return bool Returns false if the current user doesn't have permission to edit the user, otherwise returns void.
     */
    function save_membership_level_field($user_id) {
        if ( !current_user_can( 'edit_user', $user_id ) ) {
            return false;
        }

        if ( !empty( $_POST['fkwmembership_level_assignment'] ) ) {
            $selected_level = sanitize_text_field( $_POST['fkwmembership_level_assignment'] );

            $this->set_level_user( $user_id, $selected_level );
        }
    }

}

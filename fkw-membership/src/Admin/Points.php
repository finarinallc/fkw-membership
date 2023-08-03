<?php
namespace Finarina\Membership\Admin;

class Points {

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
        // Add a submenu page for managing membership points system.
        add_submenu_page(
            'fkwmembership',    // Parent menu slug
            'Points Settings',  // Page title
            'Points',           // Menu title
            'manage_options',   // Capability required to access the submenu page
            'fkwmembership_points', // Submenu slug
            array( $this, 'render_membership_settings_points_page' ) // Callback function to render the submenu page
        );
    }

    /**
     * Renders the membership settings points page.
     *
     * @throws Exception If the user does not have permission to access the page.
     * @return void
     */
    public function render_membership_settings_points_page() {
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
    
        // Check if any levels have been created before showing the points page
        if ( empty( $levels ) ) {
            ?>
            <div class="wrap fkw-membership-settings-form">
                <h1>FKW Membership Points Settings</h1>
                <p>There are no membership levels set. Please go back to the Levels page to create different levels before you can create point intervals.</p>
            </div>
            <?php
            return;
        }
        ?>
    
        <div class="wrap fkw-membership-settings-form">
            <h1>FKW Membership Points Settings</h1>
    
            <!-- Modal for adding a new points interval -->
            <div id="add-points-modal" class="modal">
                <div class="modal-content">
                    <span class="close">&times;</span>
                    <h2>Add Points Interval</h2>
                    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                        <input type="hidden" name="action" value="add_point_interval">
                        <input type="hidden" name="level_id" id="level_id" value="">
                        <table class="form-table">
                            <tr valign="top">
                                <th scope="row">Add a new points interval for this level</th>
                                <td>
                                    <div class="fields-group">
                                        On every - <br>
                                        <label for="interval">Interval:</label>
                                        <div class="interval-wrapper">
                                            <input type="number" name="interval" id="interval" class="form-control" required>
                                            <select name="interval_type" id="interval_type" class="form-control" required>
                                                <option value="day">Day(s)</option>
                                                <option value="week">Week(s)</option>
                                                <option value="month">Month(s)</option>
                                                <option value="year">Year(s)</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="fields-group">
                                        - add this many points: <br>
                                        <label for="points">Points:</label>
                                        <input type="number" name="points" id="points" class="form-control" required>
                                    </div>
                                    <div class="fields-group">
                                        <?php wp_nonce_field('save_points_interval', 'save_points_interval_nonce'); ?>
                                        <input type="submit" class="button-primary" value="Save Points Interval">
                                    </div>
                                </td>
                            </tr>
                        </table>
                    </form>
                </div>
            </div>

            

            <form method="post" action="options.php">
                <?php settings_fields('fkwmembership_settings_group'); ?>
                <?php do_settings_sections('fkwmembership_settings_group'); ?>

                <div class="fields-group">
                    <label>
                        <input type="checkbox" name="<?php echo $field_name; ?>" value="1" <?php checked($field_value, 1); ?>>
                        Enable <?php echo $field_label; ?>
                    </label>
                </div>

                <?php submit_button(); ?>
            </form>
    
            <!-- Add Points Interval button -->
            <?php echo get_submit_button('Add Points Interval', 'secondary', 'add-points-btn'); ?>
    
            <?php foreach ($levels as $level_id => $level) { ?>
                <?php if (isset($level['points_intervals'])) { ?>
                    <h2><?php echo $level['name']; ?> Points Intervals</h2>
                    <table class="wp-list-table widefat fixed striped">
                        <thead>
                        <tr>
                            <th>Points</th>
                            <th>Interval</th>
                            <th>Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($level['points_intervals'] as $interval_id => $interval) { ?>
                            <tr>
                                <td><?php echo $interval['points']; ?></td>
                                <td><?php echo $interval['interval']; ?></td>
                                <td>
                                    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                                        <input type="hidden" name="action" value="delete_point_intervals">
                                        <input type="hidden" name="level_id" value="<?php echo $level_id; ?>">
                                        <input type="hidden" name="selected_intervals[]" value="<?php echo $interval_id; ?>">
                                        <?php wp_nonce_field('delete_points_interval', 'delete_points_interval_nonce'); ?>
                                        <input type="submit" class="button" value="Delete">
                                    </form>
                                </td>
                            </tr>
                        <?php } ?>
                        </tbody>
                    </table>
                <?php } ?>
            <?php } ?>
        </div>
    
        <script>
            // JavaScript to handle the modal functionality
            var addPointsModal = document.getElementById('add-points-modal');
            var addPointsBtn = document.getElementById('add-points-btn');
            var closeBtn = document.getElementsByClassName('close')[0];
    
            addPointsBtn.onclick = function () {
                addPointsModal.style.display = 'block';
            }
    
            closeBtn.onclick = function () {
                addPointsModal.style.display = 'none';
            }
    
            window.onclick = function (event) {
                if (event.target === addPointsModal) {
                    addPointsModal.style.display = 'none';
                }
            }
        </script>
        <?php
    }

    private function handle_form_submitted( $post_data, $levels ) {
        if ($post_data['action'] === 'add_point_interval') {
            // Handle form submission for adding a new points interval
            $level_id = $post_data['level_id'];
            $points = $post_data['points'];
            $interval = $post_data['interval'];

            // Save the new points interval for the selected level
            if (isset($levels[$level_id])) {
                $levels[$level_id]['points_intervals'][] = array(
                    'points' => $points,
                    'interval' => $interval
                );
            }
        } else if ($post_data['action'] === 'delete_point_intervals') {
            // Handle form submission for deleting points intervals
            $level_id = $post_data['level_id'];
            $selected_intervals = isset($post_data['selected_intervals']) ? $post_data['selected_intervals'] : array();

            // Remove the selected points intervals from the selected level
            if (isset($levels[$level_id])) {
                foreach ($selected_intervals as $interval_id) {
                    if (isset($levels[$level_id]['points_intervals'][$interval_id])) {
                        unset($levels[$level_id]['points_intervals'][$interval_id]);
                    }
                }
            }
        }

        // Save the updated levels array back to the database
        update_option( 'fkwmembership_levels_available', $levels );

        // Redirect back to the points page to prevent form resubmission
        wp_redirect( esc_url( admin_url( 'admin.php?page=fkwmembership_points' ) ) );
        exit;
    }

}
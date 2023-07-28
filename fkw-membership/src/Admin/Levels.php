<?php
namespace Finarina\Membership\Admin;

class Levels {

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
        // Add a submenu page for managing membership levels.
        add_submenu_page(
            'fkwmembership',    // Parent menu slug
            'Levels Settings',    // Page title
            'Levels',    // Menu title
            'manage_options',   // Capability required to access the submenu page
            'fkwmembership_levels', // Submenu slug
            array( $this, 'render_membership_settings_levels_page' ) // Callback function to render the submenu page (same as the top-level menu page)
        );
    }

    /**
     * Renders the membership settings levels page.
     *
     * @throws None
     * @return None
     */
    public function render_membership_settings_levels_page() {
        // Check if the user has the capability to access the page.
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have permission to access this page.'));
        }

        // Get the saved levels from the database
        $levels = get_option('fkwmembership_levels_available', array());

        // Code to handle form submissions for adding/editing/deleting levels
        if ( isset( $_POST['action'] ) ) {
            $this->handle_form_submitted( $_POST, $levels );
        }

        $access_options = get_option( 'fkwmembership_general_access_settings', array() );
        ?>

        <div class="wrap fkw-membership-settings-form">
            <h1>FKW Membership Levels Settings</h1>

            <?php if( empty( $access_options ) ) { ?>

            <p>There are no access options set. Please go back to the Settings page to activate access options before you can make membership levels.</p>

            <?php } else { ?>

            <!-- Add Level button -->
            <?php echo get_submit_button('Add Level', 'secondary', 'add-level-btn'); ?>

            <!-- Modal for adding a new level -->
            <div id="add-level-modal" class="modal">
                <div class="modal-content">
                    <span class="close">&times;</span>
                    <h2>Add Level</h2>
                    <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
                        <input type="hidden" name="action" value="add_level">
                        <table class="form-table">
                            <tr valign="top">
                                <th scope="row">Add a new membership level</th>
                                <td>
                                    <div class="fields-group">
                                        <label for="level_name">Level Name:</label>
                                        <input type="text" name="level_name" id="level_name" class="form-control" required>
                                    </div>
                                    <div class="fields-group">
                                        <label for="level_access">Level Access:</label>
                                        <select name="level_access[]" id="level_access" class="form-control" multiple required>
                                            <?php foreach( $access_options as $option ) { ?>
                                            <option value="<?php echo strtolower( str_replace( ' ', '_', $option ) ); ?>', $option; ?>"><?php echo $option; ?></option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                    <div class="fields-group">
                                        <?php wp_nonce_field( 'save_member_level', 'save_member_level_nonce' ); ?>
                                        <input type="submit" class="button-primary" value="Save Level">
                                    </div>
                                </td>
                            </tr>
                        </table>
                    </form>
                </div>
            </div>

            <?php if ( !empty( $levels ) ) { ?>
            <!-- Display levels table -->
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th>Level Name</th>
                        <th>Level Access</th>
                        <th>Level Created</th>
                        <th>Level Modified</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($levels as $level) { ?>
                        <tr>
                            <td><?php echo $level['name']; ?></td>
                            <td>
                                <?php
                                // Display the access options for the level
                                $access_settings = array(
                                    'exclusive_content' => 'Enable Exclusive Content Access',
                                    'early_access' => 'Enable Early Access to Content',
                                    'live_chat_access' => 'Enable Live Chat Access',
                                    'event_access' => 'Enable Event Access',
                                    'discount_code_access' => 'Enable Discount Code Access',
                                );

                                $access_options = array();
                                foreach ($access_settings as $key => $label) {
                                    if (in_array($key, $level['access'])) {
                                        $access_options[] = $label;
                                    }
                                }

                                echo implode(', ', $access_options);
                                ?>
                            </td>
                            <td><?php echo $level['created']; ?></td>
                            <td><?php echo $level['modified']; ?></td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
            <?php } else { ?>
                <p>No levels created yet.</p>
            <?php }
            } ?>
        </div>

        <script>
            // JavaScript to handle the modal functionality
            var addLevelModal = document.getElementById('add-level-modal');
            var addLevelBtn = document.getElementById('add-level-btn');
            var closeBtn = document.getElementsByClassName('close')[0];

            addLevelBtn.onclick = function () {
                addLevelModal.style.display = 'block';
            }

            closeBtn.onclick = function () {
                addLevelModal.style.display = 'none';
            }

            window.onclick = function (event) {
                if (event.target === addLevelModal) {
                    addLevelModal.style.display = 'none';
                }
            }
        </script>
        <?php
    }

    private function handle_form_submitted( $post_data, $levels ) {
        if( $post_data['action'] === 'add_level' ) {
            // Handle form submission for adding a new level
            $new_level_name = $post_data['level_name'];
            $new_level_access = isset($post_data['level_access']) ? $post_data['level_access'] : array();

            // Create a new level entry
            $new_level = array(
                'name' => $new_level_name,
                'access' => $new_level_access,
                'created' => current_time('mysql'),
                'modified' => current_time('mysql')
            );

            // Add the new level to the existing levels array
            $levels[] = $new_level;
        } else if ( $post_data['action'] === 'delete_levels' ) {
            // Handle form submission for deleting levels
            $selected_levels = isset($post_data['selected_levels']) ? $post_data['selected_levels'] : array();

            // Remove the selected levels from the levels array
            foreach ($selected_levels as $level_id) {
                if (isset($levels[$level_id])) {
                    unset($levels[$level_id]);
                }
            }
        }

        // Save the updated levels array back to the database
        update_option( 'fkwmembership_levels_available', $levels );

        // Redirect back to the levels page to prevent form resubmission
        wp_redirect( esc_url( admin_url( 'admin.php?page=fkwmembership_levels' ) ) );
        exit;
    }

}
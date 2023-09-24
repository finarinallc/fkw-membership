<?php
namespace FKW\Membership\Admin\Access;

use FKW\Membership\Admin;
use FKW\Membership\Admin\Levels;


class ExclusiveContent {

	/**
     * The single instance of the class.
     *
     * @var ExclusiveContent|null
     */
    private static $instance = null;

	/**
     * Get the single instance of the class.
     *
     * @return ExclusiveContent|null
     */
    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }

        return self::$instance;
    }

	public function __construct() {

	}

	/**
	 * Adds a custom meta box to the edit page to determine access to content.
	 *
	 * @return void
	 */
	public function admin_init() {
		// adds a custom meta box to the edit page to determine access to content
		add_action('add_meta_boxes', [ $this, 'add_exclusive_content_meta_box' ] );
		add_action('save_post', [ $this, 'save_exclusive_content_access_levels' ] );

		// adds exclusive access column to the Posts list page
		add_filter( 'manage_posts_columns', [ $this, 'add_exclusive_content_column' ] );
		add_action( 'manage_posts_custom_column', [ $this, 'populate_exclusive_content_column' ], 10, 2 );

		// enforce access permissions to content
		add_action( 'template_redirect', [ $this, 'show_error_if_no_access' ] );
		//add_action( 'pre_get_posts', [ $this, 'filter_posts_by_access_level' ] );

	}

	/**
	 * Add exclusive content meta box to specified post types.
	 *
	 * @return void
	 */
	public function add_exclusive_content_meta_box() {
		$post_types = get_post_types( array( 'public' => true ), 'names' );

		foreach ( $post_types as $post_type ) {
			add_meta_box(
				'exclusive_content_meta_box',
				'Exclusive Content Access',
				[ $this, 'render_exclusive_content_meta_box' ],
				$post_type,
				'side',
				'default'
			);
		}
	}

	/**
	 * Render the exclusive content meta box.
	 *
	 * @param $post The post object.
	 * @return void
	 */
	public function render_exclusive_content_meta_box( $post ) {
		// get the main settings
		$fkwmembership_admin = Admin::get_instance();

		// Check if the exclusive content option is enabled in settings
		$access_settings = $fkwmembership_admin->get_all_access_settings();

		if( !empty ( $access_settings ) ) {
			if( !empty( $access_settings['exclusive_content'] ) ) {
				$exclusive_content_enabled = true;
			} else {
				return false;
			}
		}

		// Get the available levels from the database
		$fkwmembership_levels = Levels::get_instance();
		$levels = $fkwmembership_levels->get_all_levels();

		// Get the post's saved access levels
		$saved_levels = get_post_meta( $post->ID, '_fkwmembership_exclusive_content_access_levels', true );

		// Display checkboxes for each level
		foreach ( $levels as $level ) {

			// if the level access is defined
			// and its not empty when its unserialized
			// and after unserializing, its still an array
			// and the access for exclusive_content is set to true
			if( !empty( $level['level_access'] ) ) {
				$level_access = unserialize( $level['level_access'] );

				if( is_array( $level_access ) && in_array( 'exclusive_content', $level_access, true ) ) {

					if( !empty( $saved_levels ) ) {
						$checked = in_array( $level['id'], $saved_levels ) ? 'checked' : '';
					} else {
						$checked = '';
					}

					echo '<input type="checkbox" class="fkwmembership_exclusive_content_levels_checkbox" name="fkwmembership_exclusive_content_levels[]" value="' . $level['id'] . '" ' . $checked . '> ' . esc_html( $level['level_name'] ) . '<br>';

				}

			}

		}
	}

	/**
	 * Saves the exclusive content access levels for a post.
	 *
	 * @param int $post_id The ID of the post.
	 */
	public function save_exclusive_content_access_levels( $post_id ) {
		if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) return;
		if ( !current_user_can( 'edit_post', $post_id ) ) return;

		if ( isset( $_POST['fkwmembership_exclusive_content_levels'] ) ) {
			$selected_levels = $_POST['fkwmembership_exclusive_content_levels'];

			if( is_array( $selected_levels ) ) {
				$selected_levels = array_map( 'sanitize_text_field', $selected_levels );
			}

			update_post_meta( $post_id, '_fkwmembership_exclusive_content_access_levels', $selected_levels );
		} else {
			delete_post_meta( $post_id, '_fkwmembership_exclusive_content_access_levels' );
		}
	}

	/**
	 * Adds an exclusive content column to the given array of columns.
	 *
	 * This function takes an array of columns and adds an "Exclusive Content" column
	 * after the "Categories" column. It first removes the "Tags" column from the array.
	 *
	 * @param array $columns The array of columns to which the exclusive content column will be added.
	 * @return array The updated array of columns with the exclusive content column.
	 */
	public function add_exclusive_content_column( $columns ) {
		// Remove the "Tags" column
		unset( $columns['tags'] );

		// Add "Exclusive Content" after "Categories"
		$new_columns = array();
		foreach ( $columns as $key => $value ) {
			$new_columns[ $key ] = $value;
			if ( $key === 'categories' ) {
				$new_columns['exclusive_content'] = 'Exclusive Content';
			}
		}

		return $new_columns;
	}

	/**
	 * Populates the exclusive_content column in the specified $column of the post with the given $post_id.
	 *
	 * @param mixed $column The name of the column.
	 * @param int $post_id The ID of the post.
	 * @return void
	 */
	public function populate_exclusive_content_column( $column, $post_id ) {

		$levels = Levels::get_instance();

		if ( $column === 'exclusive_content' ) {
			$post_exclusive_content_access = get_post_meta( $post_id, '_fkwmembership_exclusive_content_access_levels', true );

			if( !empty( $post_exclusive_content_access ) ) {
				$is_exclusive = '';

				foreach( $post_exclusive_content_access as $level_id ) {
					$level_name = $levels->get_level( $level_id )['level_name'];

					if( in_array( $level_id, $post_exclusive_content_access, true ) ) {
						$is_exclusive .= $level_name . ', ';
					}
				}

				$is_exclusive = rtrim( $is_exclusive, ', ' );

			} else {
				$is_exclusive = false;
			}

			// Display a checkmark or X based on the value
			echo $is_exclusive ? '<span style="color: green; font-size: 20px; font-wieght: bold;">&#9679;</span> ' . $is_exclusive : '<span style="color: red; font-size: 20px;">&#10006;</span>';
		}
	}

	/**
	 * Filters posts based on the user's access level.
	 *
	 * This function takes an array of posts and filters out the posts that the current user does not have access to. It retrieves the current user using the `wp_get_current_user()` function and gets the access levels for each post using the `get_post_meta()` function. If a post has an exclusive content access level and the current user does not have access to that level, the post is removed from the array. Finally, the function returns the filtered array of posts.
	 *
	 * @param array $posts An array of posts to filter.
	 * @return array The filtered array of posts.
	 */
	function filter_posts_by_access_level( $posts ) {
		if ( is_home() && $query->is_main_query() ) {
			// Get the current user's ID
			$current_user_id = get_current_user_id();

			// Get the IDs of posts with restricted access
			$restricted_post_ids = get_posts(
				[
					'post_type' => 'post', // Change to your post type if needed
					'meta_query' => [
						[
							'key' => '_fkwmembership_exclusive_content_access_levels',
							'value' => $current_user_id,
							'compare' => 'NOT LIKE'
						]
					]
				]);

			// Exclude restricted posts from the query
			$query->set('post__not_in', wp_list_pluck( $restricted_post_ids, 'ID' ) );
		}
	}

	/**
	 * Checks if the current user has access to a specific post.
	 *
	 * @param int $post_id The ID of the post to check access for.
	 * @return bool Returns true if the user has access, false otherwise.
	 */
	function check_user_access_to_post( $post_id ) {

		// Get the current user.
		$current_user_id = get_current_user_id();

		// Get the post's access levels.
		$post_access_levels = get_post_meta( $post_id, '_fkwmembership_exclusive_content_access_levels', true );

		// If the post has an exclusive content access level and the current user does not have access to the post, return false.
		if ( ! empty( $post_access_levels ) && ! in_array( $current_user_id, $post_access_levels ) ) {
			return false;
		}

		// Otherwise, return true.
		return true;
	}

	/**
	 * Checks if the current user has access to a post and shows an error message if not.
	 *
	 * @return void
	 */
	function show_error_if_no_access() {
		global $post;

		// Get the current user.
		$current_user_id = get_current_user_id();

		// Get the post's access levels.
		$post_access_levels = get_post_meta( $post->ID, '_fkwmembership_exclusive_content_access_levels', true );

		// If the post has an exclusive content access level and the current user does not have access to the post, show an error.
		if ( ! empty( $post_access_levels ) && ! in_array( $current_user_id, $post_access_levels ) ) {
			wp_die( __( 'You do not have permission to access this post.', FKWMEMBERSHIP_NAMESPACE ) );
		}
	}

}

<?php
namespace FKW\Membership\Admin;

use FKW\Membership\FKWMembership;
use FKW\Membership\Admin;

/**
 * Class SettingsPage
 *
 *
 * @link       https://developer.finarina.com/services/wordpress-customization/fkw-membership
 * @since      1.0.0
 *
 * @package    FKW/Membership
 * @subpackage FKW/Membership/Admin
 * @author     Finarina LLC <systems@finarina.com>
 */

class SettingsPage {

    /**
     * The single instance of the class.
     *
     * @var SettingsPage|null
     */
    private static $instance = null;

    /**
	 * Official plugin name
	 */
	public $plugin_namespace;

	/**
	 * Official plugin version
	 */
	public $version;

    /**
	 * Settings page ID to associate fields with database
	 */
	public $settings_id;

    /**
     * Setting page section ID to associate fields to
     */
    public $section_id;

    /**
     * Setting page heading
     */
    public $settings_page_title;

    /**
     * Setting page description
     */
    public $settings_page_description;

    /**
     * Get the single instance of the class.
     *
     * @return SettingsPage|null
     */
    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Constructs a new instance of the class.
     *
     * @param mixed $plugin_namespace The namespace of the plugin.
     * @param mixed $version The version of the plugin.
     * @param mixed $option_name The name of the option.
     * @param mixed $title The title of the settings page.
     * @param mixed $description The description of the settings page.
     */
    public function __construct() {

        $fkwmembership = FKWMembership::get_instance();
        $this->plugin_namespace = $fkwmembership->plugin_namespace;
        $this->plugin_namespace = $fkwmembership->version;

    }

    public function init(
        $settings_id,
        $settings_database_id,
        $settings_page_id,
        $section_id,
        $title,
        $description
    ) {

        $this->settings_id = $settings_id;
        $this->settings_database_id = $settings_database_id;
        $this->settings_page_id = $settings_page_id;
        $this->section_id = $section_id;
        $this->settings_page_title = $title;
        $this->settings_page_description = $description;

    }

    /**
     * Registers the settings initialization.
     *
     * @return void
     */
    public function register_settings_init() {

        $section_setup = [
            $this->section_id,
			__( $this->settings_page_title, $this->plugin_namespace ),
			[ $this, 'render_settings_section_heading' ],
			$this->settings_page_id
        ];

		// Add section heading
		add_settings_section(
			...$section_setup
		);

	}

   /**
    * Appends a settings field to a section.
    *
    * @param $settingsId The ID of the settings section.
    * @param $field_label The label of the field.
    * @param $field_parameters The parameters of the field.
    *
    * @return void
    */
    public function register_settings_field_to_section( $field_label, $field_parameters ) {

		add_settings_field(
			$field_parameters['id'],
			__( $field_label, $this->plugin_namespace ),
            function() use ( $field_label, $field_parameters ) {
                $this->render_settings_field( $field_label, $field_parameters );
            },
			$this->settings_page_id,
			$this->section_id
		);

    }

	/**
	 * Render the settings section heading.
	 *
	 * @return void
	 */
	public function render_settings_section_heading() {
		?>
        <div class="page-description">
            <?php echo esc_html( $this->settings_page_description ); ?>
        </div>
        <?php
	}

	/**
	 * Renders a settings text field.
	 *
	 * @param array $args An associative array of arguments.
	 *     - id (string) The ID attribute of the input field.
	 *     - name (string) The name attribute of the input field.
	 *     - default_value (mixed) The default value of the input field.
	 *     - class (string) The class attribute of the input field.
	 *     - style (string) The style attribute of the input field.
	 *     - placeholder (string) The placeholder attribute of the input field.
	 *
	 * @return void
	 */
	public function render_settings_field( $field_label, $args ) {
        $option_value = get_option( $this->settings_database_id );
        $field_type = $args['type'];

        if( $field_type == 'select' ) {
        ?>
        <select name="<?php echo $this->settings_database_id; ?>[<?php echo esc_attr( $args['name'] ); ?>]"
            id="<?php echo esc_attr( $args['id'] ); ?>"
            class="<?php echo !empty( $args['class'] ) ? $args['class']: '' ?>"
            style="<?php echo !empty( $args['style'] ) ? $args['style']: '' ?>"
            placeholder="<?php echo esc_attr( $args['placeholder'] ); ?>">
            <?php if( !empty( $args['value'] ) && is_array( $args['value'] ) ) {
            foreach( $args['value'] as $name => $value ) {
                if( !empty( $option_value )
                    && !empty( $option_value[ $args['name'] ] )
                    && $option_value[ $args['name'] ] == $value ) {
                    $selected = true;
                }
            ?>
                <option value="<?php echo esc_attr( $name ); ?>" <?php echo $selected ? 'selected' : ''; ?>><?php echo esc_html( $value ); ?></option>
            <?php $selected = false;
                }
            } ?>
        </select>
        <?php
        } else if( $field_type == 'checkbox' || $field_type == 'radio' ) {
            if( !empty( $args['value'] ) && is_array( $args['value'] ) ) {
                $count = 0;

                foreach( $args['value'] as $key => $value ) {
            ?>
            <label for="<?php echo $this->settings_database_id; ?>[<?php echo esc_attr( $args['name'] ); ?>][<?php echo $key; ?>]">
                <input type="<?php echo $field_type; ?>"
                    id="<?php echo esc_attr( $args['id'] ); ?>"
                    name="<?php echo $this->settings_database_id; ?>[<?php echo esc_attr( $args['name'] ); ?>][<?php echo $key; ?>]"
                    value="1"
                    class="<?php echo !empty( $args['class'] ) ? $args['class'] : '' ?>"
                    style="<?php echo !empty( $args['style'] ) ? $args['style'] : '' ?>"
                    placeholder="<?php echo esc_attr( $args['placeholder'] ); ?>"
                    <?php echo !empty( $option_value[ $args['name'] ][ $key ] ) ? ' checked' : ''; ?>
                    />
                <?php echo esc_html( $value ); ?>
            </label>
            <?php
                }
            } else {
            ?>
            <label for="<?php echo $this->settings_database_id; ?>[<?php echo esc_attr( $args['name'] ); ?>]">
                <input type="<?php echo $field_type; ?>"
                    id="<?php echo esc_attr( $args['id'] ); ?>"
                    name="<?php echo $this->settings_database_id; ?>[<?php echo esc_attr( $args['name'] ); ?>]"
                    value="1"
                    class="<?php echo !empty( $args['class'] ) ? $args['class'] : '' ?>"
                    style="<?php echo !empty( $args['style'] ) ? $args['style'] : '' ?>"
                    placeholder="<?php echo esc_attr( $args['placeholder'] ); ?>"
                    <?php echo !empty( $option_value[ $args['name'] ] ) ? ' checked' : ''; ?>
                    />
                <?php echo esc_html( $field_label ); ?>
            </label>
            <?php
            }
        } else if( $field_type == 'textarea' ) {
        ?>
        <textarea
            id="<?php echo esc_attr( $args['id'] ); ?>"
            name="<?php echo $this->settings_database_id; ?>[<?php echo esc_attr( $args['name'] ); ?>]"
            class="<?php echo !empty( $args['class'] ) ? $args['class']: '' ?>"
            style="<?php echo !empty( $args['style'] ) ? $args['style']: '' ?>"
            placeholder="<?php echo esc_attr( $args['placeholder'] ); ?>">
            <?php echo !empty( $option_value[ $args['name'] ] ) ? $option_value[ $args['name'] ] : esc_attr( $args['default_value']); ?>
        </textarea>
        <?php
        } else {
        ?>
        <input type="text"
            id="<?php echo esc_attr( $args['id'] ); ?>"
            name="<?php echo $this->settings_database_id; ?>[<?php echo esc_attr( $args['name'] ); ?>]"
            value="<?php echo !empty( $option_value[ $args['name'] ] ) ? $option_value[ $args['name'] ] : esc_attr( $args['default_value']); ?>"
            class="<?php echo !empty( $args['class'] ) ? $args['class']: '' ?>"
            style="<?php echo !empty( $args['style'] ) ? $args['style']: '' ?>"
            placeholder="<?php echo esc_attr( $args['placeholder'] ); ?>"/>
        <?php
        }

	}

}

<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://thedockline.com
 * @since      1.0.0
 *
 * @package    Dockline_Suite
 * @subpackage Dockline_Suite/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Dockline_Suite
 * @subpackage Dockline_Suite/admin
 * @author     Levi <levi@thedockline.com>
 */
class Dockline_Suite_Admin
{

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct($plugin_name, $version)
	{

		$this->plugin_name = $plugin_name;
		$this->version = $version;
		$this->load_dependencies();
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles()
	{

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Dockline_Suite_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Dockline_Suite_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/dockline-suite-admin.css', array(), $this->version, 'all');
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts()
	{

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Dockline_Suite_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Dockline_Suite_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/dockline-suite-admin.js', array('jquery'), $this->version, false);
	}

	/**
	 * Load the dependencies for the admin area.
	 *
	 * @since    1.0.2
	 */
	public function load_dependencies()
	{
		require_once plugin_dir_path(dirname(__FILE__)) . 'admin/class-dockline-suite-admin-image-compress.php';
		require_once plugin_dir_path(dirname(__FILE__)) . 'admin/class-dockline-suite-admin-betheme-overrides.php';

		$image_compress = new Dockline_Suite_Admin_Image_Compress();
		$betheme_overrides = new Dockline_Suite_Admin_Betheme_Overrides();
	}

	/**
	 * Register the administration menu for this plugin into the WordPress Dashboard menu.
	 *
	 * @since    1.0.2
	 */
	public function add_plugin_admin_menu()
	{

		/*
		 * Add a settings page for this plugin to the Settings menu.
		 *
		 * NOTE:  Alternative menu locations are available via WordPress administration menu functions.
		 *
		 *        Administration Menus: http://codex.wordpress.org/Administration_Menus
		 *
		 */
		add_menu_page(
			'Dockline Suite',
			'Dockline Suite',
			'manage_options',
			'dockline-suite',
			array($this, 'display_plugin_setup_page')
		);

		// Add submenu pages
		add_submenu_page(
			'dockline-suite',
			'General',
			'General',
			'manage_options',
			'dockline-suite'
		);

		add_submenu_page(
			'dockline-suite',
			'CSS',
			'CSS',
			'manage_options',
			'dockline-suite-css',
			array($this, 'display_css_page')
		);

		add_submenu_page(
			'dockline-suite',
			'Image Settings',
			'Image Settings',
			'manage_options',
			'dockline-suite-image-settings',
			array($this, 'display_image_page')
		);
	}
	/**
	 * Register settings for the Image Compression section
	 * 
	 * @since    1.0.2
	 */
	public function register_image_compress_settings()
	{
		// Register a new section
		add_settings_section(
			'dockline_image_compress_section',
			'Image Compression Settings',
			array($this, 'image_compress_section_callback'),
			'dockline-suite-image-settings'
		);

		// Register Enable/Disable setting
		register_setting(
			'dockline_image_compress_settings',
			'dockline_image_compress_enable'
		);

		add_settings_field(
			'dockline_image_compress_enable',
			'Enable Compression',
			array($this, 'image_compress_enable_callback'),
			'dockline-suite-image-settings',
			'dockline_image_compress_section'
		);

		// Register Output Size setting
		register_setting(
			'dockline_image_compress_settings',
			'dockline_image_compress_output_size'
		);

		add_settings_field(
			'dockline_image_compress_output_size',
			'Output Size',
			array($this, 'image_compress_output_size_callback'),
			'dockline-suite-image-settings',
			'dockline_image_compress_section'
		);

		// Register Compression Level setting
		register_setting(
			'dockline_image_compress_settings',
			'dockline_image_compress_level'
		);

		add_settings_field(
			'dockline_image_compress_level',
			'Compression Level',
			array($this, 'image_compress_level_callback'),
			'dockline-suite-image-settings',
			'dockline_image_compress_section'
		);
	}

	/**
	 * Register settings for the Betheme Overrides section
	 * 
	 * @since    1.0.2
	 */
	public function register_betheme_overrides_settings()
	{
		// Register a new section
		add_settings_section(
			'dockline_betheme_overrides_section',
			'Betheme Overrides Settings',
			array($this, 'dockline_betheme_overrides_section_callback'),
			'dockline-suite-betheme-overrides'
		);

		// Register Enable/Disable setting
		register_setting(
			'dockline_betheme_overrides_settings',
			'dockline_betheme_override_license'
		);

		add_settings_field(
			'dockline_betheme_override_license',
			'Override License',
			array($this, 'override_license_callback'),
			'dockline-suite-betheme-overrides',
			'dockline_betheme_overrides_section'
		);
	}

	/**
	 * Section callback
	 *
	 * @since    1.0.2
	 */
	public function image_compress_section_callback()
	{
		echo '<p>Configure your image compression settings below.</p>';
	}

	/**
	 * Enable/Disable radio callback
	 *
	 * @since    1.0.2
	 */
	public function image_compress_enable_callback()
	{
		$enable = get_option('dockline_image_compress_enable', 'disabled');
		// Set default values for compression level and output size
?>
		<label>
			<input type="checkbox" name="dockline_image_compress_enable" value="enabled" <?php checked('enabled', $enable); ?>>
			Enable Image Compression
		</label>
	<?php
	}

	/**
	 * Output Size text field callback
	 *
	 * @since    1.0.2
	 */
	public function image_compress_output_size_callback()
	{
		$size = get_option('dockline_image_compress_output_size', '');
	?>
		<input type="text" name="dockline_image_compress_output_size" value="<?php echo esc_attr($size); ?>">
	<?php
	}

	/**
	 * Compression Level range slider callback
	 *
	 * @since    1.0.2
	 */
	public function image_compress_level_callback()
	{
		$level = get_option('dockline_image_compress_level', '75');
	?>
		<input type="range" name="dockline_image_compress_level" min="0" max="100" value="<?php echo esc_attr($level); ?>">
		<span class="level-value"><?php echo esc_html($level); ?></span>
	<?php
	}

	/**
	 * Betheme Overrides section callback
	 *
	 * @since    1.0.2
	 */
	public function dockline_betheme_overrides_section_callback()
	{
		echo '<p>Configure your Betheme overrides settings below.</p>';
	}

	/**
	 * Override License checkbox callback
	 *
	 * @since    1.0.2
	 */
	public function override_license_callback()
	{
		$override = get_option('dockline_betheme_override_license', 'disabled');
	?>
		<label>
			<input type="checkbox" name="dockline_betheme_override_license" value="enabled" <?php checked('enabled', $override); ?>>
			Enable
		</label>
<?php
	}

	/**
	 * Render the settings page for this plugin.
	 *
	 * @since    1.0.2
	 */
	public function display_plugin_setup_page()
	{
		include_once 'partials/dockline-suite-admin-display.php';
	}

	/**
	 * Render the CSS page for this plugin.
	 *
	 * @since    1.0.2
	 */
	public function display_css_page()
	{
		include_once 'partials/dockline-suite-admin-css-settings.php';
	}

	/**
	 * Render the Image Compress page for this plugin.
	 *
	 * @since    1.0.2
	 */
	public function display_image_page()
	{
		include_once 'partials/dockline-suite-admin-image-settings.php';
	}
}

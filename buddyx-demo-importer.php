<?php
/**
 * Plugin Name: BuddyX Demo Importer
 * Plugin URI: https://wbcomdesigns.com/
 * Description: BuddyX Theme Demo Importer
 * Version: 3.0.0
 * Author: Wbcom Designs
 * Author URI: https://wbcomdesigns.com/
 * Requires at least: 4.0
 * Tested up to: 6.7.1
 *
 * Text Domain: buddyx-demo-Importer
 * Domain Path: /i18n/languages/
 *
 * @package BuddyX_Theme_Demo_Importer
 * @category Core
 * @author Wbcom Designs
 */

// Define plugin constants
define( 'BDI_DIR', dirname( __FILE__ ) );
define( 'BDI_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
define( 'BDI_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'BDI_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

/**
 * Include required files
 *
 * @since 3.0.0
 * @return void
 */
if ( ! function_exists( 'bdi_file_includes' ) ) {

	add_action( 'init', 'bdi_file_includes' );

	function bdi_file_includes() {
		if ( file_exists( BDI_PLUGIN_PATH . 'vendor/autoload.php' ) ) {
			require_once BDI_PLUGIN_PATH . 'vendor/autoload.php';
		}

		if ( file_exists( BDI_PLUGIN_PATH . 'includes/buddyx-demo-functions.php' ) ) {
			require_once BDI_PLUGIN_PATH . 'includes/buddyx-demo-functions.php';
		}
	}
}

/**
 * Load One Click Demo Import if not already loaded
 *
 * @since 3.0.0
 * @return void
 */
if ( is_admin() ) {
	include_once ABSPATH . 'wp-admin/includes/plugin.php';
	if ( ! class_exists( 'OCDI_Plugin' ) && ! is_plugin_active( 'one-click-demo-import/one-click-demo-import.php' ) ) {
		require_once BDI_PLUGIN_PATH . 'includes/one-click-demo-import/one-click-demo-import.php';
	}
}

/**
 * Load demo importer configuration
 *
 * @since 3.0.0
 * @return void
 */
if ( file_exists( BDI_PLUGIN_PATH . 'buddyx-demo-importer-config.php' ) ) {
	require_once BDI_PLUGIN_PATH . 'buddyx-demo-importer-config.php';
}

/**
 * Redirect to demo import page after plugin activation
 *
 * @since 3.0.0
 * @param string $plugin Plugin basename that was activated
 * @return void
 */
add_action( 'activated_plugin', 'bdi_activated_plugin_redirect' );

function bdi_activated_plugin_redirect( $plugin ) {

	$theme_name = wp_get_theme();
	if ( 'buddyx' !== $theme_name->template ) {
		return;
	}
	
	if ( $plugin === BDI_PLUGIN_BASENAME ) {
		if ( isset( $_GET['page'] ) && sanitize_text_field( $_GET['page'] ) === 'tgmpa-install-plugins' ) {
			wp_safe_redirect( admin_url( 'themes.php?page=one-click-demo-import' ) );
			exit;
		} else {
			wp_redirect( esc_url( admin_url( 'themes.php?page=one-click-demo-import' ) ) );
			exit;
		}
	}
}

/**
 * Filter to add demo-specific plugins to the installer
 *
 * @since 3.0.0
 * @param array $plugins Array of plugins to install
 * @return array Modified array of plugins
 */
add_filter( 'buddyx_plugin_install', 'buddyx_demo_plugin_installer' );

function buddyx_demo_plugin_installer( $plugins ) {
	if ( 
		( isset( $_GET['page'] ) && in_array( sanitize_text_field( $_GET['page'] ), ['buddyx-sample-demo-import', 'tgmpa-install-plugins'], true ) ) 
		|| ( defined( 'DOING_AJAX' ) && DOING_AJAX ) 
	) {
		$plugins[] = array(
			'name'     => 'BuddyPress',
			'slug'     => 'buddypress',
			'required' => false,
		);
		$plugins[] = array(
			'name'     => 'WooCommerce',
			'slug'     => 'woocommerce',
			'required' => false,
		);
		$plugins[] = array(
			'name'     => 'BuddyBoss Platform',
			'slug'     => 'buddyboss-platform',
			'source'   => 'https://github.com/buddyboss/buddyboss-platform/releases/download/2.8.80/buddyboss-platform-plugin.zip',
			'required' => false,
		);
		$plugins[] = array(
			'name'     => 'Wbcom Essential',
			'slug'     => 'wbcom-essential',
			'source'   => 'https://demos.wbcomdesigns.com/exporter/plugins/wbcom-essential/3.9.4/wbcom-essential.zip',
			'required' => false,
		);
		$plugins[] = array(
			'name'     => 'LifterLMS',
			'slug'     => 'lifterlms',
			'required' => false,
		);
		$plugins[] = array(
			'name'     => 'ElementsKit Lite',
			'slug'     => 'elementskit-lite',
			'required' => false,
		);
		$plugins[] = array(
			'name'     => 'LearnPress',
			'slug'     => 'learnpress',
			'required' => false,
		);
		$plugins[] = array(
			'name'     => 'Tutor LMS',
			'slug'     => 'tutor',
			'required' => false,
		);
		$plugins[] = array(
			'name'     => 'Dokan',
			'slug'     => 'dokan-lite',
			'required' => false,
		);
		$plugins[] = array(
			'name'     => 'The Events Calendar',
			'slug'     => 'the-events-calendar',
			'required' => false,
		);
	}
	return $plugins;
}

/**
 * Activate default BuddyPress components on new installation
 *
 * @since 3.0.0
 * @param array $components Array of BuddyPress components
 * @return array Modified array of components with groups, friends, and messages activated
 */
add_action( 'bp_new_install_default_components', 'buddyx_demo_bp_default_components', 99 );

function buddyx_demo_bp_default_components( $components ) {
	$components['groups']   = 1;
	$components['friends']  = 1;
	$components['messages'] = 1;
	return $components;
}

/**
 * Initialize plugin update checker
 *
 * @since 3.0.0
 * @return void
 */
require plugin_dir_path( __FILE__ ) . 'plugin-update-checker/plugin-update-checker.php';
$myUpdateChecker = Puc_v4_Factory::buildUpdateChecker(
	esc_url( 'https://demos.wbcomdesigns.com/exporter/free-plugins/buddyx-demo-importer.json' ),
	__FILE__,
	'buddyx-demo-importer'
);

/**
 * Check if BuddyX theme is active and deactivate plugin if not
 *
 * @since 3.0.0
 * @return void
 */
function buddyx_demo_reactions_requires_buddyx() {
	$theme_name = wp_get_theme();
		
	if ( 'buddyx' !== $theme_name->template ) {
		deactivate_plugins( BDI_PLUGIN_BASENAME );
		add_action( 'admin_notices', 'buddyx_demo_reactions_required_theme_admin_notice' );
		if ( isset( $_GET['activate'] ) ) {
			unset( $_GET['activate'] );
		}
	}
}
add_action( 'admin_init', 'buddyx_demo_reactions_requires_buddyx' );

/**
 * Display admin notice when BuddyX theme is not active
 *
 * @since 3.0.0
 * @return void
 */
function buddyx_demo_reactions_required_theme_admin_notice() {
	$bpreaction_plugin = esc_html__( ' BuddyX Demo Importer', 'buddyx-demo-Importer' );
	$bp_theme         = esc_html__( 'BuddyX', 'buddyx-demo-Importer' );
	echo '<div class="error"><p>';
	echo sprintf( esc_html__( '%1$s is ineffective now as it requires %2$s theme to be installed and active.', 'buddyx-demo-Importer' ), '<strong>' . esc_html( $bpreaction_plugin ) . '</strong>', '<strong>' . esc_html( $bp_theme ) . '</strong>' );
	echo '</p></div>';
}

/**
 * Register and enqueue admin styles
 *
 * @since 3.0.0
 * @param string $hook The current admin page hook
 * @return void
 */
function buddyx_demo_admin_enqueue_scripts( $hook ) {
	// Only load on specific pages
	$allowed_pages = array(
		'themes_page_one-click-demo-import',
		'appearance_page_buddyx-demo-delete-data'
	);
	
	if ( ! in_array( $hook, $allowed_pages ) ) {
		return;
	}
	
	wp_enqueue_style(
		'buddyx-demo',
		plugin_dir_url( __FILE__ ) . 'assets/css/buddyx-demo.css',
		array(),
		'3.0.0'
	);
}
add_action( 'admin_enqueue_scripts', 'buddyx_demo_admin_enqueue_scripts' );

/**
 * Add admin menu for deleting demo data
 *
 * @since 3.0.0
 * @return void
 */
add_action( 'admin_menu', 'buddyx_demo_add_admin_menu' );

function buddyx_demo_add_admin_menu() {
	add_submenu_page(
		'themes.php',
		'Delete Demo Data',
		'Delete Demo Data',
		'manage_options',
		'buddyx-demo-delete-data',
		'buddyx_demo_data_delete' 
	);
}

/**
 * Display delete demo data page and handle deletion
 *
 * @since 3.0.0
 * @return void
 */
function buddyx_demo_data_delete() {
	if ( ! empty( $_POST['buddyx-admin-clear'] ) ) {
		// Verify nonce
		check_admin_referer( 'buddyx-admin' );
		
		if ( function_exists( 'buddypress' ) ) {
			buddyx_bp_clear_db();
		}
		buddyx_demo_clear_db();
		echo '<div id="message" class="updated fade"><p>' . esc_html__( 'Everything created by this plugin was successfully deleted.', 'buddyx-demo-Importer' ) . '</p></div>';
	}
	?>
	<div class="wrap" id="buddyx-default-data-page">
		<h1><?php esc_html_e( 'Delete BuddyX Default Data', 'buddyx-demo-Importer' ); ?></h1>
		<form action="" method="post" id="buddyx-admin-form">
			<p class="submit">
				<input class="button" type="submit" name="buddyx-admin-clear" id="buddyx-admin-clear" 
					   value="<?php esc_attr_e( 'Clear BuddyX Default Data', 'buddyx-demo-Importer' ); ?>" 
					   onclick="return confirm('<?php echo esc_js( esc_html__( 'Are you sure you want to delete all *imported* content - users, groups, messages, activities, forum topics etc? Content, that was created by you and others, and not by this plugin, will not be deleted.', 'buddyx-demo-Importer' ) ); ?>');" />
			</p>
			<?php wp_nonce_field( 'buddyx-admin' ); ?>
		</form>
	</div>
	<?php
}
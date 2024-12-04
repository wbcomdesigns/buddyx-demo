<?php
/**
 * Plugin Name: BuddyX Demo Importer
 * Plugin URI: https://wbcomdesigns.com/
 * Description: BuddyX Theme Demo Importer
 * Version: 2.1.0
 * Author: Wbcom Designs
 * Author URI: https://wbcomdesigns.com/
 * Requires at least: 4.0
 * Tested up to: 5.3.2
 *
 * Text Domain: buddyx-demo-Importer
 * Domain Path: /i18n/languages/
 *
 * @package BuddyX_Theme_Demo_Importer
 * @category Core
 * @author Wbcom Designs
 */
define( 'BDI_DIR', dirname( __FILE__ ) );
define( 'BDI_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
define( 'BDI_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

/*
 * Include Merlin related Files.
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
if(is_admin()) {
	include_once ABSPATH . 'wp-admin/includes/plugin.php';
	if ( !class_exists( 'OCDI_Plugin' ) && ! is_plugin_active( 'one-click-demo-import/one-click-demo-import.php' )) {
		require_once BDI_PLUGIN_PATH . 'includes/one-click-demo-import/one-click-demo-import.php';
	}
}


if ( file_exists( BDI_PLUGIN_PATH . 'buddyx-demo-importer-config.php' ) ) {				
	require_once BDI_PLUGIN_PATH . 'buddyx-demo-importer-config.php';			
}
/*
 * redirect Theme Setup Wizard setting page after plugin activated
 */
add_action( 'activated_plugin', 'bdi_activated_plugin_redirect' );

function bdi_activated_plugin_redirect( $plugin ) {

	$theme_name = wp_get_theme();
	if ( 'buddyx' !== $theme_name->template  ) {
		return;
	}
	if ( $plugin === plugin_basename( __FILE__ ) ) {

		if ( isset( $_GET['page'] ) && sanitize_text_field( $_GET['page'] ) === 'tgmpa-install-plugins' ) {
			?>
			<script>
				window.location = "<?php echo esc_url( admin_url( 'themes.php?page=one-click-demo-import' ) ); ?>";
			</script>
			<?php
			wp_die();
		} else {
			wp_redirect( esc_url( admin_url( 'themes.php?page=one-click-demo-import' ) ) );
			exit;
		}
	}
}


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
			'source'   => 'https://github.com/buddyboss/buddyboss-platform/releases/download/2.7.50/buddyboss-platform-plugin.zip',
			'required' => false,
		);
		$plugins[] = array(
			'name'     => 'Wbcom Essential',
			'slug'     => 'wbcom-essential',
			'source'   => 'https://demos.wbcomdesigns.com/exporter/plugins/wbcom-essential/3.9.1/wbcom-essential.zip',
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
 * Activate Groups, Friends, and Messages components when BuddyPress is activated.
 */
add_action( 'bp_new_install_default_components', 'buddyx_demo_bp_default_components', 99 );

function buddyx_demo_bp_default_components( $components ) {
	$components['groups']   = 1;
	$components['friends']  = 1;
	$components['messages'] = 1;
	return $components;
}


require plugin_dir_path( __FILE__ ) . 'plugin-update-checker/plugin-update-checker.php';
$myUpdateChecker = Puc_v4_Factory::buildUpdateChecker(
	esc_url( 'https://demos.wbcomdesigns.com/exporter/free-plugins/buddyx-demo-importer.json' ),
	__FILE__, // Full path to the main plugin file or functions.php.
	'buddyx-demo-importer'
);


/**
 *  Check if buddypress activate.
 */
function buddyx_demo_reactions_requires_buddyx() {
	$theme_name = wp_get_theme();
		
	if (  'buddyx' !== $theme_name->template ) {
		deactivate_plugins( plugin_basename( __FILE__ ) );
		add_action( 'admin_notices', 'buddyx_demo_reactions_required_theme_admin_notice' );
		unset( $_GET['activate'] );
	}
}
add_action( 'admin_init', 'buddyx_demo_reactions_requires_buddyx' );

function buddyx_demo_reactions_required_theme_admin_notice() {
	$bpreaction_plugin 	= esc_html__( ' BuddyX Demo Importer', 'buddyx-demo-Importer' );
	$bp_theme       	= esc_html__( 'BuddyX', 'buddyx-demo-Importer' );
	echo '<div class="error"><p>';
	echo sprintf( esc_html__( '%1$s is ineffective now as it requires %2$s theme to be installed and active.', 'buddyx-demo-Importer' ), '<strong>' . esc_html( $bpreaction_plugin ) . '</strong>', '<strong>' . esc_html( $bp_theme ) . '</strong>' );
	echo '</p></div>';
	if ( isset( $_GET['activate'] ) ) {
		unset( $_GET['activate'] );
	}
}
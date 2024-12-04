<?php
/**
 * Merlin WP configuration file.
 *
 * @package   Merlin WP
 * @version   @@pkg.version
 * @link      https://merlinwp.com/
 * @author    Rich Tabor, from ThemeBeans.com & the team at ProteusThemes.com
 * @copyright Copyright (c) 2018, Merlin WP of Inventionn LLC
 * @license   Licensed GPLv3 for Open Source Use
 */

/* remove Admin init function on Theme Setup wizard start */
add_action( 'admin_init', 'bdi_remove_admin_init', 0 );
function bdi_remove_admin_init() {	

	if( isset( $_REQUEST['action'] ) && $_REQUEST['action'] == 'ocdi_install_plugin' && isset( $_REQUEST['slug'] ) && $_REQUEST['slug'] == 'woocommerce') {
		update_option( 'woocommerce_onboarding_profile', [ 'completed'=> true, 'skipped' => true ]);
		// Remove the redirect transient set by WooCommerce after activation
		if ( get_transient( '_wc_activation_redirect' ) ) {
			delete_transient( '_wc_activation_redirect' );
		}
	}
	/* Pass activate multi plugin in get request*/
	if( isset( $_REQUEST['action'] ) && ( $_REQUEST['action'] == 'ocdi_install_plugin' || $_REQUEST['action'] == 'ocdi_import_demo_data' ) ) {
		$_GET['activate-multi'] = true;
		if( isset( $_REQUEST['slug'] ) && $_REQUEST['slug'] == 'dokan-lite' ) {
			update_option( 'dokan_theme_version', true );
		}
	}
	if ( ( isset( $_GET['page'] ) 
		&& ( 
			$_GET['page'] == 'buddyx-sample-demo-import' 
			|| $_GET['page'] == 'tgmpa-install-plugins' 
			|| $_GET['page'] == 'one-click-demo-import' 
			) )
			
			|| ( isset( $_REQUEST['action'] ) && $_REQUEST['action'] == 'ocdi_install_plugin')
	
		) {
		// Remove the redirect transient set by WooCommerce after activation
		if ( get_transient( '_wc_activation_redirect' ) ) {
			delete_transient( '_wc_activation_redirect' );
		}
		
		remove_action( 'admin_init', 'is_admin_init' );
		add_filter( 'woocommerce_enable_setup_wizard', '__return_false');		
		add_filter( 'woocommerce_prevent_automatic_wizard_redirect', '__return_false' );
		
		update_option( 'wpforms_activation_redirect', true );
		if ( did_action( 'elementor/loaded' ) ) {
			remove_action( 'admin_init', array( \Elementor\Plugin::$instance->admin, 'maybe_redirect_to_getting_started' ) );
		}
		if ( class_exists( 'Tribe__Events__Main' ) ) {
			remove_action( '_admin_menu', 'tribe_remove_activation_page', 20 );
		}
	}
}
/*
 *One Click Demo Import
 */
function bdi_ocdi_import_files( ) {	
  return [
    [
		'import_file_name'             	=> 'BuddyX with BuddyPress',
		'categories'                   	=> [],		
		'local_import_file'            	=> BDI_PLUGIN_PATH . '/demos/buddyx/buddypress/demo-content.xml',
		'local_import_page_file'    	=> BDI_PLUGIN_PATH . '/demos/buddyx/buddypress/demo-page-content.xml',
		'local_import_products_file'	=> BDI_PLUGIN_PATH . '/demos/buddyx/buddypress/demo-products-content.xml',
		'local_import_widget_file'     	=> BDI_PLUGIN_PATH . '/demos/buddyx/buddypress/widgets.json',
		'local_import_customizer_file' 	=> BDI_PLUGIN_PATH . '/demos/buddyx/buddypress/customizer.dat',
		'local_import_redux'            => [],
		'import_preview_image_url'   	=> 'https://buddyxtheme.com/wp-content/uploads/2020/12/buddy-demo-bp.jpg',
		'import_notice'              	=> '',
		'preview_url'                	=> 'https://demo.buddyxtheme.com/',
		'import_plugins'             	=> [ 'elementor', 'kirki', 'buddypress', 'woocommerce', 'wbcom-essential' ],

    ],
    [
		'import_file_name'           	=> 'BuddyX with BB Platform',
		'categories'                   	=> [],
		'local_import_file'            	=> BDI_PLUGIN_PATH . '/demos/buddyx/bb-platform/demo-content.xml',
		'local_import_page_file'    	=> BDI_PLUGIN_PATH . '/demos/buddyx/bb-platform/demo-bb-page-content.xml',
		'local_import_products_file'	=> BDI_PLUGIN_PATH . '/demos/buddyx/bb-platform/demo-products-content.xml',
		'local_import_widget_file'     	=> BDI_PLUGIN_PATH . '/demos/buddyx/bb-platform/widgets.json',
		'local_import_customizer_file' 	=> BDI_PLUGIN_PATH . '/demos/buddyx/bb-platform/customizer.dat',
		'local_import_redux'            => [],
		'import_preview_image_url'   	=> 'https://buddyxtheme.com/wp-content/uploads/2020/12/buddy-demo-bp.jpg',
		'import_notice'              	=> '',
		'preview_url'                	=> 'https://bb-free.buddyxtheme.com/',
		'import_plugins'             	=> [ 'elementor', 'classic-widgets', 'kirki', 'buddyboss-platform', 'woocommerce', 'wbcom-essential' ],
    ],
	
	[
		'import_file_name'           	=> 'BuddyX with LearnDash',
		'categories'                   	=> [],
		'local_import_file'            	=> BDI_PLUGIN_PATH . '/demos/buddyx/learndash/demo-content.xml',		
		'local_import_widget_file'     	=> BDI_PLUGIN_PATH . '/demos/buddyx/learndash/widgets.json',
		'local_import_customizer_file' 	=> BDI_PLUGIN_PATH . '/demos/buddyx/learndash/customizer.dat',
		'local_import_redux'            => [],
		'import_preview_image_url'   	=> 'https://buddyxtheme.com/wp-content/uploads/2020/12/Buddyx-learndash-demo.jpg',
		'import_notice'              	=> __( 'Please install and activate LearnDash LMS and LearnDash LMS - Course Grid plugin before import demo.', 'buddyx-demo-Importer' ),
		'preview_url'                	=> 'https://lms-demos.buddyxtheme.com/learndash',
		'import_plugins'             	=> [ 'elementor', 'classic-widgets', 'kirki', 'buddyboss-platform', 'woocommerce', 'wbcom-essential' ],
		'required_plugins'  			=> [
												'sfwd-lms/sfwd_lms.php' => 'LearnDash LMS',
												'learndash-course-grid/learndash_course_grid.php' => 'LearnDash LMS - Course Grid',
											],
    ],
	
	[
		'import_file_name'           	=> 'BuddyX with LifterLMS',
		'categories'                   	=> [],
		'local_import_file'            	=> BDI_PLUGIN_PATH . '/demos/buddyx/lifterlms/demo-content.xml',		
		'local_import_widget_file'     	=> BDI_PLUGIN_PATH . '/demos/buddyx/lifterlms/widgets.json',
		'local_import_customizer_file' 	=> BDI_PLUGIN_PATH . '/demos/buddyx/lifterlms/customizer.dat',
		'local_import_redux'            => [],
		'import_preview_image_url'   	=> 'https://buddyxtheme.com/wp-content/uploads/2023/01/BuddyX-with-LifterLMS.png',
		'import_notice'              	=> '',
		'preview_url'                	=> 'https://lms-demos.buddyxtheme.com/lifterlms/',
		'import_plugins'             	=> [ 'elementor', 'elementskit-lite', 'classic-widgets', 'kirki', 'lifterlms' ],
    ],
	
	[
		'import_file_name'           	=> 'BuddyX with LearnPress',
		'categories'                   	=> [],
		'local_import_file'            	=> BDI_PLUGIN_PATH . '/demos/buddyx/learnpress/demo-content.xml',		
		'local_import_widget_file'     	=> BDI_PLUGIN_PATH . '/demos/buddyx/learnpress/widgets.json',
		'local_import_customizer_file' 	=> BDI_PLUGIN_PATH . '/demos/buddyx/learnpress/customizer.dat',
		'local_import_redux'            => [],
		'import_preview_image_url'   	=> 'https://buddyxtheme.com/wp-content/uploads/2023/01/BuddyX-With-LearnPress.png',
		'import_notice'              	=> '',
		'preview_url'                	=> 'https://lms-demos.buddyxtheme.com/learnpress/',
		'import_plugins'             	=> [ 'elementor', 'classic-widgets', 'kirki', 'learnpress', 'wbcom-essential' ],
    ],
	
	[
		'import_file_name'           	=> 'BuddyX with TutorLMS',
		'categories'                   	=> [],
		'local_import_file'            	=> BDI_PLUGIN_PATH . '/demos/buddyx/tutorlms/demo-content.xml',		
		'local_import_widget_file'     	=> BDI_PLUGIN_PATH . '/demos/buddyx/tutorlms/widgets.json',
		'local_import_customizer_file' 	=> BDI_PLUGIN_PATH . '/demos/buddyx/tutorlms/customizer.dat',
		'local_import_redux'            => [],
		'import_preview_image_url'   	=> 'https://buddyxtheme.com/wp-content/uploads/2020/12/buddyx-tutor-lms.jpg',
		'import_notice'              	=> '',
		'preview_url'                	=> 'https://lms-demos.buddyxtheme.com/tutorlms/',
		'import_plugins'             	=> [ 'elementor', 'classic-widgets', 'kirki', 'tutor', 'wbcom-essential' ],
    ],
	
	[
		'import_file_name'           	=> 'BuddyX with Dokan',
		'categories'                   	=> [],
		'local_import_file'            	=> BDI_PLUGIN_PATH . '/demos/buddyx/dokan/demo-content.xml',		
		'local_import_widget_file'     	=> BDI_PLUGIN_PATH . '/demos/buddyx/dokan/widgets.json',
		'local_import_customizer_file' 	=> BDI_PLUGIN_PATH . '/demos/buddyx/dokan/customizer.dat',
		'local_import_redux'            => [],
		'import_preview_image_url'   	=> 'https://buddyxtheme.com/wp-content/uploads/2020/12/buddyx-dokan-1.jpg',
		'import_notice'              	=> '',
		'preview_url'                	=> 'https://lms-demos.buddyxtheme.com/dokan/',
		'import_plugins'             	=> [ 'elementor', 'classic-widgets', 'kirki', 'dokan-lite', 'woocommerce' ],
    ],
	
	[
		'import_file_name'           	=> 'BuddyX with Charity',
		'categories'                   	=> [],
		'local_import_file'            	=> BDI_PLUGIN_PATH . '/demos/buddyx/charity/demo-content.xml',		
		'local_import_widget_file'     	=> BDI_PLUGIN_PATH . '/demos/buddyx/charity/widgets.json',
		'local_import_customizer_file' 	=> BDI_PLUGIN_PATH . '/demos/buddyx/charity/customizer.dat',
		'local_import_redux'            => [],
		'import_preview_image_url'   	=> 'https://buddyxtheme.com/wp-content/uploads/2021/05/buddy_X-charity.jpg',
		'import_notice'              	=> '',
		'preview_url'                	=> 'https://lms-demos.buddyxtheme.com/charity/',
		'import_plugins'             	=> [ 'elementor', 'classic-widgets', 'kirki', 'the-events-calendar', 'buddypress', 'woocommerce' ],
    ],
	
  ];
}
add_filter( 'ocdi/import_files', 'bdi_ocdi_import_files' );


function bdi_ocdi_register_plugins( $plugins ) {
 
	// Required: List of plugins used by all theme demos.  
	$theme_plugins[] = array(
			'name'     => 'Elementor',
			'slug'     => 'elementor',
			'required' => true,			
		);
	$theme_plugins[] = array(
			'name'     => 'Kirki',
			'slug'     => 'kirki',
			'required' => true,			
		);
	$theme_plugins[] = array(
		'name'     => 'Classic Widgets',
		'slug'     => 'classic-widgets',
		'required' => true,		
	);
	
	// Check if user is on the theme recommeneded plugins step and a demo was selected.
	if ( ( isset( $_GET['step'] ) && $_GET['step'] === 'import' && isset( $_GET['import'] ) ) || ( isset($_POST['slug']) && $_POST['slug'] != '' ) ) {
 
		// Adding one additional plugin for the first demo import ('import' number = 0).
		if ( isset( $_GET['import'] ) && ( $_GET['import'] === '0' || $_GET['import'] === '7' ) ) {
		  
			$theme_plugins[] = array(
				'name'     => 'BuddyPress',
				'slug'     => 'buddypress',
				'required' => true,
				
			);
		}
	
		if ( ( isset( $_GET['import'] ) && ( $_GET['import'] === '0' || $_GET['import'] === '1' || $_GET['import'] === '2' || $_GET['import'] === '4' || $_GET['import'] === '5' ) )  || ( isset($_POST['slug']) && $_POST['slug'] === 'wbcom-essential' )  ) {
		  
			$theme_plugins[] = array(
				'name'     => 'Wbcom Essential',
				'slug'     => 'wbcom-essential',
				'source'   => 'https://demos.wbcomdesigns.com/exporter/plugins/wbcom-essential/3.9.1/wbcom-essential.zip',
				'required' => true,
			);
		}
		
		if ( ( isset( $_GET['import'] ) &&  $_GET['import'] === '1' ) || ( isset($_POST['slug']) && $_POST['slug'] === 'buddyboss-platform' )  ) {
		  
			$theme_plugins[] = array(
				'name'     => 'BuddyBoss Platform',
				'slug'     => 'buddyboss-platform',
				'source'   => 'https://github.com/buddyboss/buddyboss-platform/releases/download/2.7.50/buddyboss-platform-plugin.zip',
				'required' => true,
			);
		}
		
		if ( isset( $_GET['import'] ) && $_GET['import'] === '3' ) {
		  
			$theme_plugins[] = array(
				'name'     => 'ElementsKit Lite',
				'slug'     => 'elementskit-lite',
				'required' => true,
			);
			$theme_plugins[] = array(
				'name'     => 'LifterLMS',
				'slug'     => 'lifterlms',
				'required' => true,
			);
		}
		
		if ( isset( $_GET['import'] ) && $_GET['import'] === '4' ) {
		  
			$theme_plugins[] = array(
				'name'     => 'LearnPress',
				'slug'     => 'learnpress',
				'required' => true,
			);
		}
		
		if ( isset( $_GET['import'] ) && $_GET['import'] === '5' ) {
		  
			$theme_plugins[] = array(
				'name'     => 'Tutor LMS',
				'slug'     => 'tutor',
				'required' => true,
			);
		}
		
		if ( isset( $_GET['import'] ) && ( $_GET['import'] === '0' || $_GET['import'] === '1' || $_GET['import'] === '6' || $_GET['import'] === '7' ) ) {
		  
			$theme_plugins[] = array(
				'name'     => 'WooCommerce',
				'slug'     => 'woocommerce',
				'required' => true,
			);
		}
		
		if ( isset( $_GET['import'] ) && ( $_GET['import'] === '6' ) ) {
		  
			$theme_plugins[] = array(
				'name'     => 'Dokan',
				'slug'     => 'dokan-lite',
				'required' => true,
			);
		}
		
		if ( isset( $_GET['import'] ) && $_GET['import'] === '7' ) {
		  
			$theme_plugins[] = array(
				'name'     => 'The Events Calendar',
				'slug'     => 'the-events-calendar',
				'required' => true,
			);
		}
		
		
   
	}
 
	return $theme_plugins;
}
add_filter( 'ocdi/register_plugins', 'bdi_ocdi_register_plugins' );

/**
 * After import setup custom code.
 */
function bdi_ocdi_after_import_setup( $import_files ) {
	// Set static homepage.
	global $wpdb;
	
	$homepage = get_page_by_title( apply_filters( 'ocdi_content_home_page_title', 'Homepage' ) );

	if ( $homepage ) {
		update_option( 'page_on_front', $homepage->ID );
		update_option( 'show_on_front', 'page' );
	}

	$home = get_page_by_title( apply_filters( 'ocdi_content_home_page_title', 'Home' ) );

	if ( $home ) {
		update_option( 'page_on_front', $home->ID );
		update_option( 'show_on_front', 'page' );
	}

	// Set static blog page.
	$blogpage = get_page_by_title( apply_filters( 'ocdi_content_home_page_title', 'Blog' ) );

	if ( $blogpage ) {
		update_option( 'page_for_posts', $blogpage->ID );
		update_option( 'show_on_front', 'page' );
	}

	/*
	 * Assign Import Menus
	 */
	// Set imported menus to registered theme locations

	$locations = get_theme_mod( 'nav_menu_locations' ); // registered menu locations in theme
	$registered_menus = wp_get_nav_menus(); // registered menus
	// Assign Menu Name to Registered menus as array keys
	foreach( $registered_menus as $menu ) {

		if ( $menu->slug == 'main-menu' && strtolower($menu->name) == strtolower('Main Menu') ) {
			$locations['primary'] = $menu->term_id;
		}
		if ( $menu->slug == 'primary-menu' && strtolower($menu->name) == strtolower('Primary Menu') ) {
			$locations['primary'] = $menu->term_id;
		}

		if ( $menu->slug == 'user-menu' && strtolower($menu->name) == strtolower('User Menu') ) {
			$locations['user_menu'] = $menu->term_id;
		}
	}
	set_theme_mod( 'nav_menu_locations', $locations ); // set menus to locations
	
	/*
	 * Update Custom URL in menu
	 */
	//update_option('odi_import_files', $import_files);		
	$preview_url = ( isset($import_files['preview_url']) && $import_files['preview_url'] != '' ) ? $import_files['preview_url'] : '';
	
	if( $preview_url != '' ) {
		
		$query 			= "Select * from {$wpdb->prefix}postmeta where meta_key='_menu_item_url'";
		$results 		= $wpdb->get_results( $query );
		$find_string 	= $preview_url;
		$replace_string = trailingslashit( get_site_url() );
		if( !empty( $results )) {
			foreach( $results as $res ) {		
				if( trim( $res->meta_value ) != '' ) {
					$meta_value = str_replace( $find_string, $replace_string, $res->meta_value );					
					$data 			= [ 'meta_value' => $meta_value ];
					$where 			= [ 'meta_id' => $res->meta_id ];
					$format			= [ '%s'];
					$where_format	= [ '%d'];
					$wpdb->update( 
						"{$wpdb->prefix}postmeta",       // Table name
						$data,        // Data array (column => value)
						$where,       // Where clause array (column => value)
						$format = null,  // Optional data format array (e.g., ['%s', '%d'])
						$where_format = null // Optional where format array (e.g., ['%d'])
					);
				}
			}
		}

	}
}
add_action( 'ocdi/after_import', 'bdi_ocdi_after_import_setup' );

function bdi_ocdi_after_content_import_execution( $selected_import_files, $import_files, $selected_index) {
	
	if( $selected_index == 0 || $selected_index == 1 || $selected_index == 7 ) {
		bdi_import_buddypress_demo_data();
	}
}
add_action( 'ocdi/after_content_import_execution', 'bdi_ocdi_after_content_import_execution', 10, 3);

function bdi_import_buddypress_demo_data() {
	// Cound what we have just imported.
	$imported = array();

	// Check nonce before we do anything.
	include_once BDI_PLUGIN_PATH . '/bp-process.php';

	// Import users
	if ( ! buddyx_bp_is_imported( 'users', 'users' ) ) {
		$users             = buddyx_bp_import_users();
		$imported['users'] = sprintf( /* translators: formatted number. */
			esc_html__( '%s new users', 'buddyx-demo-Importer' ),
			number_format_i18n( count( $users ) )
		);
		buddyx_bp_update_import( 'users', 'users' );
	}

	if ( bp_is_active( 'xprofile' ) && ! buddyx_bp_is_imported( 'users', 'xprofile' ) ) {
		$profile             = buddyx_bp_import_users_profile();
		$imported['profile'] = sprintf( /* translators: formatted number. */
			esc_html__( '%s profile entries', 'buddyx-demo-Importer' ),
			number_format_i18n( $profile )
		);
		buddyx_bp_update_import( 'users', 'xprofile' );
	}

	if ( bp_is_active( 'friends' ) && ! buddyx_bp_is_imported( 'users', 'friends' ) ) {
		$friends             = buddyx_bp_import_users_friends();
		$imported['friends'] = sprintf( /* translators: formatted number. */
			esc_html__( '%s friends connections', 'buddyx-demo-Importer' ),
			number_format_i18n( $friends )
		);
		buddyx_bp_update_import( 'users', 'friends' );
	}
	

	if ( bp_is_active( 'activity' ) && ! buddyx_bp_is_imported( 'users', 'activity' ) ) {
		$activity             = buddyx_bp_import_users_activity();
		$imported['activity'] = sprintf( /* translators: formatted number. */
			esc_html__( '%s personal activity items', 'buddyx-demo-Importer' ),
			number_format_i18n( $activity )
		);
		buddyx_bp_update_import( 'users', 'activity' );
	}

	// Import groups
	if ( bp_is_active( 'groups' ) && ! buddyx_bp_is_imported( 'groups', 'groups' ) ) {
		$groups             = buddyx_bp_import_groups();
		$imported['groups'] = sprintf( /* translators: formatted number. */
			esc_html__( '%s new groups', 'buddyx-demo-Importer' ),
			number_format_i18n( count( $groups ) )
		/* translators: formatted number. */ );
		buddyx_bp_update_import( 'groups', 'groups' );
	}
	if ( bp_is_active( 'groups' ) && ! buddyx_bp_is_imported( 'groups', 'members' ) ) {
		$g_members             = buddyx_bp_import_groups_members();
		$imported['g_members'] = sprintf( /* translators: formatted number. */
			esc_html__( '%s groups members (1 user can be in several groups)', 'buddyx-demo-Importer' ),
			number_format_i18n( count( $g_members ) )
		);
		buddyx_bp_update_import( 'groups', 'members' );
	}

	

	if ( bp_is_active( 'activity' ) && bp_is_active( 'groups' ) && ! buddyx_bp_is_imported( 'groups', 'activity' ) ) {
		$g_activity             = buddyx_bp_import_groups_activity();
		$imported['g_activity'] = sprintf( /* translators: formatted number. */
			esc_html__( '%s groups activity items', 'buddyx-demo-Importer' ),
			number_format_i18n( $g_activity )
		);
		buddyx_bp_update_import( 'groups', 'activity' );
	}

	return true;
}
function bdi_ocdi_plugin_page_title() {
	ob_start(); ?>
	<div class="ocdi__title-container">
		<h1 class="ocdi__title-container-title"><?php esc_html_e( 'BuddyX Demo Import', 'one-click-demo-import' ); ?></h1>
		<a href="https://ocdi.com/user-guide/" target="_blank" rel="noopener noreferrer">
			<img class="ocdi__title-container-icon" src="<?php echo esc_url( OCDI_URL . 'assets/images/icons/question-circle.svg' ); ?>" alt="<?php esc_attr_e( 'Questionmark icon', 'one-click-demo-import' ); ?>">
		</a>
	</div>
	<?php
	return $plugin_title = ob_get_clean();
}
add_filter( 'ocdi/plugin_page_title', 'bdi_ocdi_plugin_page_title');
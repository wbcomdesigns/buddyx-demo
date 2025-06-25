<?php
/**
 * BuddyX Demo Importer Functions
 *
 * @package BuddyX_Demo_Importer
 * @since 3.0.0
 */

/**
 * Get plugin admin area root page
 *
 * Returns settings.php for WordPress Multisite and tools.php for single site
 *
 * @since 3.0.0
 * @return string Admin page slug
 */
function buddyx_bp_get_root_admin_page() {
	return is_multisite() ? 'settings.php' : 'tools.php';
}

/**
 * Delete all BuddyPress imported data
 *
 * Removes all users, groups, xProfile fields and import records
 * that were created by the demo importer
 *
 * @since 3.0.0
 * @global object $wpdb WordPress database object
 * @return void
 */
function buddyx_bp_clear_db() {
	global $wpdb;
	$bp = buddypress();

	// Delete Groups
	$groups = bp_get_option( 'buddyx_bp_imported_group_ids' );
	if ( ! empty( $groups ) ) {
		foreach ( (array) $groups as $group_id ) {
			groups_delete_group( intval( $group_id ) );
		}
	}

	// Delete Users and their data
	$users = bp_get_option( 'buddyx_bp_imported_user_ids' );
	if ( ! empty( $users ) ) {
		foreach ( (array) $users as $user_id ) {
			bp_core_delete_account( intval( $user_id ) );
		}
	}

	// Delete xProfile Groups and Fields
	$xprofile_ids = bp_get_option( 'buddyx_bp_imported_user_xprofile_ids' );
	if ( ! empty( $xprofile_ids ) ) {
		foreach ( (array) $xprofile_ids as $xprofile_id ) {
			$group = new BP_XProfile_Group( intval( $xprofile_id ) );
			$group->delete();
		}
	}

	// Delete import records
	buddyx_bp_delete_import_records();
}

/**
 * Delete all WordPress demo content
 *
 * Removes all posts, pages, and navigation items that were
 * marked as demo content during import
 *
 * @since 3.0.0
 * @return void
 */
function buddyx_demo_clear_db() {
	$args = array(
		'post_type'      => 'any',
		'posts_per_page' => -1,
		'post_status'    => 'any',
		'fields'         => 'ids',
		'order'          => 'ASC',
		'meta_key'       => '_demo_data_imported',
		'meta_value'     => 1,
	);

	$buddyx_demo_post = new WP_Query( $args );

	if ( $buddyx_demo_post->have_posts() ) {
		foreach ( $buddyx_demo_post->posts as $post_id ) {
			wp_delete_post( $post_id, true );
		}
	}

	// Delete Nav Menu items	
	$args['post_type'] = array( 'nav_menu_item', 'bp-email', 'wp_navigation', 'wp_global_styles' );
	$buddyx_demo_post  = new WP_Query( $args );
	
	if ( $buddyx_demo_post->have_posts() ) {
		foreach ( $buddyx_demo_post->posts as $post_id ) {
			wp_delete_post( $post_id, true );
		}
	}
}

/**
 * Fix date for group join activities
 *
 * Modifies the recorded time for joined_group activities to use
 * a random date instead of current time
 *
 * @since 3.0.0
 * @param array $args Arguments passed to bp_activity_add()
 * @return array Modified arguments with random date
 */
function buddyx_bp_groups_join_group_date_fix( $args ) {
	if ( isset( $args['type'], $args['component'] ) && 
		$args['type'] === 'joined_group' && 
		$args['component'] === 'groups' 
	) {
		$args['recorded_time'] = buddyx_bp_get_random_date( 25, 1 );
	}

	return $args;
}

/**
 * Fix date for friend connections
 *
 * Returns a random timestamp for friend connections instead of current time
 *
 * @since 3.0.0
 * @param string $current_time Default BuddyPress current timestamp
 * @return int Random timestamp
 */
function buddyx_bp_friends_add_friend_date_fix( $current_time ) {
	return strtotime( buddyx_bp_get_random_date( 43 ) );
}

/**
 * Get random group IDs from imported groups
 *
 * Returns an array or comma-separated string of random group IDs
 * from groups that were imported by the demo importer
 *
 * @since 3.0.0
 * @global object $wpdb WordPress database object
 * @param int    $count  Number of groups to return. Use 0 for all groups
 * @param string $output Return format: 'array' or 'string'
 * @return array|string Array of group IDs or comma-separated string
 */
function buddyx_bp_get_random_groups_ids( $count = 1, $output = 'array' ) {
	$groups_arr = (array) bp_get_option( 'buddyx_bp_imported_group_ids', array() );

	if ( ! empty( $groups_arr ) ) {
		$total_groups = count( $groups_arr );
		$count = ( $count <= 0 || $count > $total_groups ) ? $total_groups : $count;

		$random_keys = (array) array_rand( $groups_arr, $count );
		$groups = array_intersect_key( $groups_arr, array_flip( $random_keys ) );
	} else {
		global $wpdb;
		$bp = buddypress();

		if ( $count > 0 ) {
			$groups = $wpdb->get_col( $wpdb->prepare( 
				"SELECT id FROM {$bp->groups->table_name} ORDER BY rand() LIMIT %d", 
				$count 
			) );
		} else {
			$groups = $wpdb->get_col( "SELECT id FROM {$bp->groups->table_name} ORDER BY rand()" );
		}
	}

	$groups = array_map( 'intval', $groups );

	return $output === 'string' ? implode( ',', $groups ) : $groups;
}

/**
 * Get random user IDs from imported users
 *
 * Returns an array or comma-separated string of random user IDs
 * from users that were imported by the demo importer
 *
 * @since 3.0.0
 * @param int    $count  Number of users to return. Use 0 for all users
 * @param string $output Return format: 'array' or 'string'
 * @return array|string Array of user IDs or comma-separated string
 */
function buddyx_bp_get_random_users_ids( $count = 1, $output = 'array' ) {
	$users_arr = (array) bp_get_option( 'buddyx_bp_imported_user_ids', array() );

	if ( ! empty( $users_arr ) ) {
		$total_members = count( $users_arr );
		$count = ( $count <= 0 || $count > $total_members ) ? $total_members : $count;

		$random_keys = (array) array_rand( $users_arr, $count );
		$users = array_intersect_key( $users_arr, array_flip( $random_keys ) );
	} else {
		$users = get_users( array(
			'fields' => 'ID',
		) );
	}

	$users = array_map( 'intval', $users );

	return $output === 'string' ? implode( ',', $users ) : $users;
}

/**
 * Generate a random date between specified days in the past
 *
 * Creates a random date between $days_from and $days_to days ago.
 * For example, [30, 5] returns a random date between 30 and 5 days ago
 *
 * @since 3.0.0
 * @param int $days_from Maximum days in the past (default 30)
 * @param int $days_to   Minimum days in the past (default 0)
 * @return string Random date in 'Y-m-d H:i:s' format
 */
function buddyx_bp_get_random_date( $days_from = 30, $days_to = 0 ) {
	// Ensure $days_from is always greater than $days_to
	if ( $days_to > $days_from ) {
		$days_to = $days_from - 1;
	}

	try {
		$date_from = new DateTime( 'now - ' . intval( $days_from ) . ' days' );
		$date_to   = new DateTime( 'now - ' . intval( $days_to ) . ' days' );

		$timestamp = wp_rand( $date_from->getTimestamp(), $date_to->getTimestamp() );
		$date = wp_date( 'Y-m-d H:i:s', $timestamp );
	} catch ( Exception $e ) {
		$date = current_time( 'mysql' );
	}

	return $date;
}

/**
 * Get current timestamp using blog timezone settings
 *
 * @since 3.0.0
 * @return int Current timestamp
 */
function buddyx_bp_get_time() {
	return (int) current_time( 'timestamp' );
}

/**
 * Check if specific content has been imported
 *
 * Checks whether a specific type of content (users, groups) has been imported
 *
 * @since 3.0.0
 * @param string $group  Content group: 'users' or 'groups'
 * @param string $import Specific import type within the group
 * @return bool True if imported, false otherwise
 */
function buddyx_bp_is_imported( $group, $import ) {
	$group  = sanitize_key( $group );
	$import = sanitize_key( $import );

	if ( ! in_array( $group, array( 'users', 'groups' ), true ) ) {
		return false;
	}

	return array_key_exists( $import, (array) bp_get_option( 'buddyx_bp_import_' . $group ) );
}

/**
 * Display disabled attribute for already imported items
 *
 * Outputs HTML attributes for form inputs to disable and check
 * items that have already been imported
 *
 * @since 3.0.0
 * @param string $group  Content group
 * @param string $import Import type
 * @return void
 */
function buddyx_bp_imported_disabled( $group, $import ) {
	$group  = sanitize_key( $group );
	$import = sanitize_key( $import );

	echo buddyx_bp_is_imported( $group, $import ) ? 'disabled="disabled" checked="checked"' : 'checked="checked"';
}

/**
 * Save import timestamp
 *
 * Records when a specific type of content was imported
 *
 * @since 3.0.0
 * @param string $group  Content group
 * @param string $import Import type
 * @return bool True on success, false on failure
 */
function buddyx_bp_update_import( $group, $import ) {
	$group  = sanitize_key( $group );
	$import = sanitize_key( $import );

	$values = (array) bp_get_option( 'buddyx_bp_import_' . $group, array() );
	$values[ $import ] = buddyx_bp_get_time();

	return bp_update_option( 'buddyx_bp_import_' . $group, $values );
}

/**
 * Remove all import tracking records
 *
 * Deletes all options that track what content has been imported
 *
 * @since 3.0.0
 * @return void
 */
function buddyx_bp_delete_import_records() {
	bp_delete_option( 'buddyx_bp_import_users' );
	bp_delete_option( 'buddyx_bp_import_groups' );

	bp_delete_option( 'buddyx_bp_imported_user_ids' );
	bp_delete_option( 'buddyx_bp_imported_group_ids' );
	
	bp_delete_option( 'buddyx_bp_imported_user_xprofile_ids' );
}
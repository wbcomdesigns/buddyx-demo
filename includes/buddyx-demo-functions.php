<?php

/**
 * Get plugin admin area root page: settings.php for WPMS and tool.php for WP.
 *
 * @return string
 */
function buddyx_bp_get_root_admin_page() {

	return is_multisite() ? 'settings.php' : 'tools.php';
}

/**
 * Delete all imported information.
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

function buddyx_demo_clear_db() {
	$args = array(
		'post_type'      => 'any',
		'posts_per_page' => -1,
		'post_status'    => 'any',
		'order'          => 'ASC',
		'meta_key'       => '_demo_data_imported',
		'meta_value'     => 1,
	);

	$buddyx_demo_post = new WP_Query( $args );

	if ( $buddyx_demo_post->have_posts() ) {
		while ( $buddyx_demo_post->have_posts() ) {
			$buddyx_demo_post->the_post();
			wp_delete_post( get_the_ID(), true );
		}
		wp_reset_postdata(); // Reset the post data to avoid conflicts
	}

	// Delete Nav Menu items
	$args['post_type'] = ['nav_menu_item','bp-email','elementor_library','wp_navigation', 'wp_global_styles'];
	$buddyx_demo_post  = new WP_Query( $args );	
	if ( $buddyx_demo_post->have_posts() ) {
		while ( $buddyx_demo_post->have_posts() ) {
			$buddyx_demo_post->the_post();
			wp_delete_post( get_the_ID(), true );
		}
		wp_reset_postdata(); // Reset the post data to avoid conflicts
	}
}

/**
 * Fix the date issue, when all joined_group events took place at the same time.
 *
 * @param array $args Arguments that are passed to bp_activity_add().
 *
 * @return array
 * @throws \Exception
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
 * Fix the date issue, when all friends connections are done at the same time.
 *
 * @param string $current_time Default BuddyPress current timestamp.
 *
 * @return string
 * @throws \Exception
 */
function buddyx_bp_friends_add_friend_date_fix( $current_time ) {

	return strtotime( buddyx_bp_get_random_date( 43 ) );
}

/**
 * Get the array (or a string) of group IDs.
 *
 * @param int    $count  If you need all, use 0.
 * @param string $output What to return: 'array' or 'string'. If string - comma separated.
 *
 * @return array|string Default is array.
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

		$limit = $count > 0 ? 'LIMIT ' . intval( $count ) : '';
		$groups = $wpdb->get_col( "SELECT id FROM {$bp->groups->table_name} ORDER BY rand() {$limit}" );
	}

	$groups = array_map( 'intval', $groups );

	return $output === 'string' ? implode( ',', $groups ) : $groups;
}


/**
 * Get the array (or a string) of user IDs.
 *
 * @param int    $count  If you need all, use 0.
 * @param string $output What to return: 'array' or 'string'. If string - comma separated.
 *
 * @return array|string Default is array.
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
 * Get a random date between some days in the past.
 * If [30;5] is specified - that means a random date between 30 and 5 days from now.
 *
 * @param int $days_from
 * @param int $days_to
 *
 * @return string Random time in 'Y-m-d h:i:s' format.
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
		$date = date( 'Y-m-d H:i:s', $timestamp );
	} catch ( Exception $e ) {
		$date = date( 'Y-m-d H:i:s' );
	}

	return $date;
}


/**
 * Get the current timestamp, using current blog time settings.
 *
 * @return int
 */
function buddyx_bp_get_time() {

	return (int) current_time( 'timestamp' );
}


/**
 * Check whether something was imported or not.
 *
 * @param string $group  Possible values: users, groups
 * @param string $import What exactly was imported
 *
 * @return bool
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
 * Display a disabled attribute for inputs of the particular value was already imported.
 *
 * @param string $group
 * @param string $import
 */
function buddyx_bp_imported_disabled( $group, $import ) {
	$group  = sanitize_key( $group );
	$import = sanitize_key( $import );

	echo buddyx_bp_is_imported( $group, $import ) ? 'disabled="disabled" checked="checked"' : 'checked="checked"';
}


/**
 * Save when the importing was done.
 *
 * @param string $group
 * @param string $import
 *
 * @return bool
 */
function buddyx_bp_update_import( $group, $import ) {
	$group  = sanitize_key( $group );
	$import = sanitize_key( $import );

	$values = (array) bp_get_option( 'buddyx_bp_import_' . $group, array() );
	$values[ $import ] = buddyx_bp_get_time();

	return bp_update_option( 'buddyx_bp_import_' . $group, $values );
}


/**
 * Remove all imported ids and the indication, that importing was done.
 */
function buddyx_bp_delete_import_records() {
	bp_delete_option( 'buddyx_bp_import_users' );
	bp_delete_option( 'buddyx_bp_import_groups' );

	bp_delete_option( 'buddyx_bp_imported_user_ids' );
	bp_delete_option( 'buddyx_bp_imported_group_ids' );
	
	bp_delete_option( 'buddyx_bp_imported_user_xprofile_ids' );
}


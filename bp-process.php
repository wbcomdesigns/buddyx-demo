<?php
/**
 * BuddyPress Demo Data Import Processing
 *
 * @package BuddyX_Demo_Importer
 * @since 3.0.0
 */

/**
 * Import demo users into BuddyPress
 *
 * Creates demo users with profiles and sets up their basic information
 *
 * @since 3.0.0
 * @return array Array of created user IDs
 */
function buddyx_bp_import_users() {

	$users = array();

	// Check if users data file exists
	$users_file = __DIR__ . '/demos/demo-bp-data/users.php';
	if ( ! file_exists( $users_file ) ) {
		return array();
	}
	$users_data = require $users_file;

	foreach ( $users_data as $user ) {
		$user_id = wp_insert_user(
			array(
				'user_login'      => sanitize_user( $user['login'] ),
				'user_pass'       => $user['pass'],
				'display_name'    => sanitize_text_field( $user['display_name'] ),
				'user_email'      => sanitize_email( $user['email'] ),
				'user_registered' => buddyx_bp_get_random_date( 45, 1 ),
			)
		);

		if ( is_wp_error( $user_id ) ) {
			continue;
		}

		if ( bp_is_active( 'xprofile' ) ) {
			xprofile_set_field_data( 1, $user_id, $user['display_name'] );
		}
		$name = explode( ' ', $user['display_name'] );
		update_user_meta( $user_id, 'first_name', $name[0] );
		update_user_meta( $user_id, 'last_name', isset( $name[1] ) ? $name[1] : '' );
		update_user_meta( $user_id, 'buddyx_bp_user', 1 );

		bp_update_user_last_activity( $user_id, buddyx_bp_get_random_date( 5 ) );

		bp_update_user_meta( $user_id, 'notification_messages_new_message', 'no' );
		bp_update_user_meta( $user_id, 'notification_friends_friendship_request', 'no' );
		bp_update_user_meta( $user_id, 'notification_friends_friendship_accepted', 'no' );

		$users[] = $user_id;
	}

	if ( ! empty( $users ) ) {
		bp_update_option( 'buddyx_bp_imported_user_ids', $users );
	}

	return $users;
}

/**
 * Import extended profile fields and data
 *
 * Creates xProfile field groups, fields, and populates them with demo data
 *
 * @since 3.0.0
 * @return int Number of profile field entries created
 */
function buddyx_bp_import_users_profile() {

	$count = 0;

	if ( ! bp_is_active( 'xprofile' ) ) {
		return $count;
	}

	$data = array();
	$groups = array();

	// Check if xprofile structure file exists
	$xprofile_file = __DIR__ . '/demos/demo-bp-data/xprofile_structure.php';
	if ( ! file_exists( $xprofile_file ) ) {
		return 0;
	}
	$xprofile_structure = require $xprofile_file;

	// Firstly, import profile groups.
	foreach ( $xprofile_structure as $group_type => $group_data ) {
		$group_id = xprofile_insert_field_group(
			array(
				'name'        => $group_data['name'],
				'description' => $group_data['desc'],
			)
		);
		$groups[] = $group_id;

		// Then import fields.
		foreach ( $group_data['fields'] as $field_type => $field_data ) {
			$field_id = xprofile_insert_field(
				array(
					'field_group_id' => $group_id,
					'parent_id'      => 0,
					'type'           => $field_type,
					'name'           => $field_data['name'],
					'description'    => $field_data['desc'],
					'is_required'    => $field_data['required'],
					'order_by'       => 'custom',
				)
			);

			if ( $field_id ) {
				bp_xprofile_update_field_meta( $field_id, 'default_visibility', $field_data['default-visibility'] );

				bp_xprofile_update_field_meta( $field_id, 'allow_custom_visibility', $field_data['allow-custom-visibility'] );

				$data[ $field_id ]['type'] = $field_type;

				// finally import options
				if ( ! empty( $field_data['options'] ) ) {
					foreach ( $field_data['options'] as $option ) {
						$option_id = xprofile_insert_field(
							array(
								'field_group_id'    => $group_id,
								'parent_id'         => $field_id,
								'type'              => 'option',
								'name'              => $option['name'],
								'can_delete'        => true,
								'is_default_option' => $option['is_default_option'],
								'option_order'      => $option['option_order'],
							)
						);

						$data[ $field_id ]['options'][ $option_id ] = $option['name'];
					}
				} else {
					$data[ $field_id ]['options'] = array();
				}
			}
		}
	}

	// Check if xprofile data file exists
	$xprofile_data_file = __DIR__ . '/demos/demo-bp-data/xprofile_data.php';
	if ( ! file_exists( $xprofile_data_file ) ) {
		return $count;
	}
	$xprofile_data = require $xprofile_data_file;
	
	$users = buddyx_bp_get_random_users_ids( 0 );

	// Now import profile fields data for all fields for each user.
	foreach ( $users as $user_id ) {
		foreach ( $data as $field_id => $field_data ) {
			switch ( $field_data['type'] ) {
				case 'datebox':
				case 'textarea':
				case 'number':
				case 'textbox':
				case 'url':
				case 'selectbox':
				case 'radio':
					if ( xprofile_set_field_data( $field_id, $user_id, $xprofile_data[ $field_data['type'] ][ array_rand( $xprofile_data[ $field_data['type'] ] ) ] ) ) {
						$count ++;
					}
					break;

				case 'checkbox':
				case 'multiselectbox':
					if ( xprofile_set_field_data( $field_id, $user_id, explode( ',', $xprofile_data[ $field_data['type'] ][ array_rand( $xprofile_data[ $field_data['type'] ] ) ] ) ) ) {
						$count ++;
					}
					break;
			}
		}
	}

	if ( ! empty( $groups ) ) {
		bp_update_option( 'buddyx_bp_imported_user_xprofile_ids', $groups );
	}

	return $count;
}

/**
 * Import user activity updates
 *
 * Creates activity stream items for imported users
 *
 * @since 3.0.0
 * @return int Number of activity items created
 */
function buddyx_bp_import_users_activity() {

	$count = 0;

	if ( ! bp_is_active( 'activity' ) ) {
		return $count;
	}

	$users = buddyx_bp_get_random_users_ids( 0 );

	// Check if activity data file exists
	$activity_file = __DIR__ . '/demos/demo-bp-data/activity.php';
	if ( ! file_exists( $activity_file ) ) {
		return $count;
	}
	/** @var $activity array */
	require $activity_file;

	for ( $i = 0; $i < 75; $i ++ ) {
		$user    = $users[ array_rand( $users ) ];
		$content = $activity[ array_rand( $activity ) ];

		if ( $bp_activity_id = bp_activity_post_update( array( 'user_id' => $user, 'content' => $content ) ) ) {
			$bp_activity                = new BP_Activity_Activity( $bp_activity_id );
			$bp_activity->date_recorded = buddyx_bp_get_random_date( 44 );
			if ( $bp_activity->save() ) {
				$count ++;
			}
		}
	}

	return $count;
}

/**
 * Create friend connections between users
 *
 * Randomly connects imported users as friends
 *
 * @since 3.0.0
 * @return int Number of friend connections created
 */
function buddyx_bp_import_users_friends() {

	$count = 0;

	if ( ! bp_is_active( 'friends' ) ) {
		return $count;
	}

	$users = buddyx_bp_get_random_users_ids( 50 );

	add_filter( 'bp_core_current_time', 'buddyx_bp_friends_add_friend_date_fix' );

	for ( $i = 0; $i < 100; $i ++ ) {
		$user_one = $users[ array_rand( $users ) ];
		$user_two = $users[ array_rand( $users ) ];

		// Make them friends if possible.
		if ( friends_add_friend( $user_one, $user_two, true ) ) {
			$count ++;
		}
	}

	remove_filter( 'bp_core_current_time', 'buddyx_bp_friends_add_friend_date_fix' );

	return $count;
}

/**
 * Import BuddyPress groups
 *
 * Creates demo groups with various privacy settings
 *
 * @since 3.0.0
 * @param bool|array $users Optional. Array of users to use as group creators
 * @return array Array of created group IDs
 */
function buddyx_bp_import_groups( $users = false ) {

	$groups    = array();
	$group_ids = array();

	if ( ! bp_is_active( 'groups' ) ) {
		return $group_ids;
	}

	// Use currently available users from DB if no default were specified.
	if ( empty( $users ) ) {
		$users = get_users();
	}

	// Check if groups data file exists
	$groups_file = __DIR__ . '/demos/demo-bp-data/groups.php';
	if ( ! file_exists( $groups_file ) ) {
		return $group_ids;
	}
	require $groups_file;

	foreach ( $groups as $group ) {
		$creator_id = is_object( $users[ array_rand( $users ) ] ) ? $users[ array_rand( $users ) ]->ID : $users[ array_rand( $users ) ];
		$cur        = groups_create_group(
			array(
				'creator_id'   => $creator_id,
				'name'         => $group['name'],
				'description'  => $group['description'],
				'slug'         => groups_check_slug( sanitize_title( esc_attr( $group['name'] ) ) ),
				'status'       => $group['status'],
				'date_created' => buddyx_bp_get_random_date( 30, 5 ),
				'enable_forum' => $group['enable_forum'],
			)
		);

		if ( ! $cur ) {
			continue;
		}

		groups_update_groupmeta( $cur, 'last_activity', buddyx_bp_get_random_date( 10 ) );

		// Create forums if Forum Component is active.
		if (
			bp_is_active( 'forums' ) &&
			function_exists( 'bp_forums_is_installed_correctly' ) && bp_forums_is_installed_correctly() &&
			function_exists( 'groups_new_group_forum' )
		) {
			groups_new_group_forum( $cur, $group['name'], $group['description'] );
		}

		$group_ids[] = $cur;
	}

	if ( ! empty( $group_ids ) ) {
		bp_update_option( 'buddyx_bp_imported_group_ids', $group_ids );
	}

	return $group_ids;
}

/**
 * Import group activity updates
 *
 * Creates activity items within groups
 *
 * @since 3.0.0
 * @return int Number of group activity items created
 */
function buddyx_bp_import_groups_activity() {

	$count = 0;

	if ( ! bp_is_active( 'groups' ) || ! bp_is_active( 'activity' ) ) {
		return $count;
	}

	$users  = buddyx_bp_get_random_users_ids( 0 );
	$groups = buddyx_bp_get_random_groups_ids( 0 );

	// Check if activity data file exists
	$activity_file = __DIR__ . '/demos/demo-bp-data/activity.php';
	if ( ! file_exists( $activity_file ) ) {
		return $count;
	}
	/** @var $activity array */
	require $activity_file;

	for ( $i = 0; $i < 150; $i ++ ) {
		$user_id  = $users[ array_rand( $users ) ];
		$group_id = $groups[ array_rand( $groups ) ];
		$content  = $activity[ array_rand( $activity ) ];

		if ( ! groups_is_user_member( $user_id, $group_id ) ) {
			continue;
		}

		$bp_activity_id = groups_post_update(
			array(
				'user_id'  => $user_id,
				'group_id' => $group_id,
				'content'  => $content,
			)
		);

		if ( $bp_activity_id ) {
			$bp_activity                = new BP_Activity_Activity( $bp_activity_id );
			$bp_activity->date_recorded = buddyx_bp_get_random_date( 29 );
			if ( $bp_activity->save() ) {
				$count ++;
			}
		}
	}

	return $count;
}

/**
 * Add members to groups
 *
 * Randomly assigns users to groups
 *
 * @since 3.0.0
 * @param array $groups Optional. Array of group IDs to add members to
 * @return array Array of group IDs that received new members
 */
function buddyx_bp_import_groups_members( $groups = array() ) {

	$members = array();

	if ( ! bp_is_active( 'groups' ) ) {
		return $members;
	}

	if ( empty( $groups ) ) {
		$groups = buddyx_bp_get_random_groups_ids( 0 );
	}

	add_filter( 'bp_after_activity_add_parse_args', 'buddyx_bp_groups_join_group_date_fix' );

	foreach ( $groups as $group_id ) {
		$user_ids = buddyx_bp_get_random_users_ids( wp_rand( 2, 15 ) );

		foreach ( $user_ids as $user_id ) {
			if ( groups_join_group( $group_id, $user_id ) ) {
				$members[] = $group_id;
			}
		}
	}

	remove_filter( 'bp_after_activity_add_parse_args', 'buddyx_bp_groups_join_group_date_fix' );

	return $members;
}

/**
 * Import forums and topics for groups
 *
 * Placeholder function for future forum import functionality
 *
 * @since 3.0.0
 * @param array $groups Array of group IDs
 * @return bool Always returns true
 */
function buddyx_bp_import_groups_forums( $groups ) {
	return true;
}
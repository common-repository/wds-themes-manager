<?php
function wdstm_create_db_table() {
	global $wpdb;
	$table_name = wdstm_sign_get_table_name();

	$sql = /** @lang text */
		"CREATE TABLE `$table_name` (
        `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
        `title` varchar(255),
        `theme` varchar(255),
        `days` varchar(255),
        `period_days` varchar(255),
        `repeater` varchar (2),
        `br_includ` varchar (2),
        `c_includ` varchar (2),
        `l_includ` varchar (2),
        `p_includ` varchar (2),
        `time` varchar(255),
        `seasons` varchar(255),
        `os` varchar(255),
        `device_type` text,
        `browser` text,
        `phone` text,
        `country` text,
        `language` text,
        `range_ip` text,
        `taxonomy` text,
        `typepage` text,
        `on_off` varchar(3),                
        `notes` text,
        PRIMARY KEY (`id`)
        )";

	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	dbDelta( $sql );

	add_option( "wdstm_sign_db_version", WDSTM_DB_VERSION );
}
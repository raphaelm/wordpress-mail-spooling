<?php // encoding: utf-8
/*
Plugin Name: Mail Spooling
Description: Save outgoing mails into database instead of sending them out so they can be sent out by a daemon. Evil code! Might break attachments! Use carefully! Cronjob setup needed! Linux servers only!
Version: 1.0.0
Author: Raphael Michel
*/

function msp_install () {
	global $wpdb;
	$table_name = $wpdb->prefix . "mailqueue"; 
	$sql = "CREATE TABLE $table_name (
	  `id` mediumint(9) NOT NULL AUTO_INCREMENT,
	  `time` datetime NOT NULL,
	  `phpmailer` LONGTEXT,
	  `tries` mediumint(9),
	  UNIQUE KEY id (id)
	);";
	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	dbDelta( $sql );
}

register_activation_hook( __FILE__, 'msp_install' );
add_action( 'phpmailer_init', 'msp_fake_phpmailer' );

function msp_fake_phpmailer ( $phpmailer ) {
	global $wpdb;
	$table_name = $wpdb->prefix . "mailqueue"; 
	
	$serialized = serialize($phpmailer);
	
	$wpdb->insert( $table_name, array( 'time' => current_time('mysql'), 'tries' => 0, 'phpmailer' => $serialized ) );
	
	$phpmailer->ClearAddresses();
}

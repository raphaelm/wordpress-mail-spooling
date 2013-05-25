<?php
if (php_sapi_name() !== 'cli') {
    trigger_error("You should call this from command line!", E_USER_WARNING);
}
$pidfile = __DIR__ . '/pid';
define( 'SHORTINIT', true );
require_once( __DIR__ . '/../../../wp-load.php' );
require_once ABSPATH . WPINC . '/class-phpmailer.php';
require_once ABSPATH . WPINC . '/class-smtp.php';

if(file_exists($pidfile)) {
	$pid = trim(file_get_contents($pidfile));
	if(file_exists('/proc/'.$pid))
		throw new Exception("Script is still running (pid: ".$pid.")");
}
file_put_contents($pidfile, getmypid());

require_once( __DIR__ . '/../../../wp-load.php' );

$table_name = $wpdb->prefix . "mailqueue"; 

$queued = $wpdb->get_results( 
	"
	SELECT `id`, `time`, `phpmailer`, `tries`
	FROM $table_name
	WHERE `tries` < 15
	ORDER by `time` ASC
	"
);

foreach ( $queued as $mail ) 
{
	$phpmailer = unserialize($mail->phpmailer);
	
	try {
		$phpmailer->Send();
		$wpdb->query( 
			$wpdb->prepare( 
				"
				DELETE FROM $table_name
				WHERE id = %d
				",
					$mail->id
				)
		);
	} catch ( phpmailerException $e ) {
		$wpdb->query( 
			$wpdb->prepare( 
				"
				UPDATE $table_name
				SET `tries` = `tries`+1
				WHERE id = %d
				",
					$mail->id
				)
		);
	}
}
unlink($pidfile);

<?
	/*
	* Könyvtárak
	*/

	error_reporting(E_ALL & ~(E_STRICT|E_NOTICE));
	ini_set('display_errors', 1);
	ini_set("session.gc_maxlifetime", 60*60*24 );

	require "settings/config.php";
	require 'autoload.php';

	if( file_exists(SMARTY_DIR . 'Smarty.class.php')) {
		require_once( SMARTY_DIR . 'Smarty.class.php' );
	}

	$start = new Start();
	
	function __( $t ) {
		return $t;
	}
?>

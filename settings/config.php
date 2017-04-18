<?
	// Domain név
	define('DOMAIN','http://'.$_SERVER['HTTP_HOST'].'/');
	define('MDOMAIN',$_SERVER['HTTP_HOST']);
	define('CLR_DOMAIN',str_replace(array("http://","www."),"",substr('www.'.DOMAIN,0,-1)));
	define('AJAX_GET','/ajax/get/');
	define('AJAX_POST','/ajax/post/');
	define('AJAX_BOX','/ajax/box/');
	define('IS_DEV', true);
	////////////////////////////////////////
	// Ne módosítsa innen a beállításokat //
	date_default_timezone_set('Europe/Berlin');
	// PATH //
		define('APP_ROOT','v1.0');

		define('PATH', realpath($_SERVER['HTTP_HOST']));

		define('APP_PATH','application/' );

		define('LIBS', APP_PATH . 'libs/' );

		define('VIEW', APP_PATH . 'views/'.APP_ROOT.'/' );

		define('CONTROL', APP_PATH . 'controllers/' );

		define('SOURCE_STYLE','/source/css/');

		define('SOURCE_JS','/source/js/');

		define( 'SOURCE_IMG', '/source/images/');

		define( 'UPLOADS', '/source/uploads/');

		// Smarty Template Engine PATH
		define('SMARTY_DIR', LIBS . 'Smarty/');

	// Környezeti beállítások //

		define('CAPTCHA_PUBLIC_KEY','6Lfim_oSAAAAADzkdJjq8LJ0BTT0X4eBLpSy3emZ');

		define('CAPTCHA_PRIVATE_KEY','6Lfim_oSAAAAAABh5QkbpyJaAzVpvsGaC7IcCnG4');

		define('GOOGLE_WEB_API_KEY', 'AIzaSyDQ5EvmhI9YeI0S9X6dyBxqHLvSVoM0gqU');

		define('SKEY','Sgzh343(S6fe3rerfgAS(eu38z848üüÜÚA9sDGFGDSFDS');

		define('PREV_PAGE',$_SERVER['HTTP_REFERER']);

		define('CURRENT_PAGE', 'http://'.$_SERVER['HTTP_HOST'].strtok($_SERVER['REQUEST_URI'],'?'));

		define('NOW', date('Y-m-d H:i:s'));

	require "data.php";
?>

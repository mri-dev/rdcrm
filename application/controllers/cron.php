<?
use AlertsManager\Alerts;

class cron extends Controller  {
	public $alerts;

	function __construct(){
		parent::__construct();
		$title = null;

		$this->alerts = new Alerts(array(
			'db' 		=> $this->db,
			'settings' 	=> $this->settings,
			'smarty' 	=> $this->smarty
		));
	}

	function __destruct(){
		// RENDER OUTPUT
		parent::bodyHead();					# HEADER
		$this->displayView( __CLASS__.'/index', true );		# CONTENT
		parent::__destruct();				# FOOTER
	}
}

?>

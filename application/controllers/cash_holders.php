<?
use CashFlowManager\CashHolders;
class cash_holders extends Controller  {
		private $user = false;
		function __construct(){
			parent::__construct();

      if (isset($_POST['saveCashHolder'])) {
				$ch = (new CashHolders(array('db' => $this->db, 'smarty' => $this->smarty)))->save($_POST, $_POST['saveCashHolder']);
				Helper::reload();
			}

			// SEO Információk
			$SEO = null;
			// Site info
			$SEO .= $this->addMeta('description','');
			$SEO .= $this->addMeta('keywords','');
			$SEO .= $this->addMeta('revisit-after','3 days');

			// FB info
			$SEO .= $this->addOG('type','website');
			$SEO .= $this->addOG('url','');
			$SEO .= $this->addOG('image','');
			$SEO .= $this->addOG('site_name',parent::$pageTitle);

			$this->out( 'SEOSERVICE', $SEO );
		}

		function __destruct(){
			// RENDER OUTPUT
			parent::bodyHead();					# HEADER
			$this->displayView( __CLASS__.'/index', true );		# CONTENT
			parent::__destruct();				# FOOTER
		}
	}

?>

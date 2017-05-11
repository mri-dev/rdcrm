<?
use \ProjectManager\Payments;

class home extends Controller  {
		private $user = false;
		function __construct(){
			parent::__construct();

			$this->out('homepage', true);

			$this->user = $this->getVar('user');
			$this->me = $this->getVar('me');

			$payments = new Payments(null, $this->Projects->arg);

			// 15 napon belül befizetendő díbekérők
			$payments_arg = array();
			if (!$this->me->isAdmin() && !$this->me->isReferer()) {
				$payments_arg['usercan'] = $this->me->getID();
			}
			$payments_arg['onlyactive'] = true;
			$payments_arg['deadlinein'] = 15;
			$payments_arg['order'] = "due_date ASC";
			$this->out('actual_payments', $payments->getList($payments_arg));

			// Befizetett díjbekérők
			$payments_arg = array();
			if (!$this->me->isAdmin() && !$this->me->isReferer()) {
				$payments_arg['usercan'] = $this->me->getID();
			}
			$payments_arg['onlyactive'] = true;
			$payments_arg['onlypaid'] = true;
			$payments_arg['order'] = "paid_date DESC";
			$this->out('paid_payments', $payments->getList($payments_arg));

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

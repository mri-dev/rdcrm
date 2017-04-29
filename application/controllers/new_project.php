<?
use ProjectManager\Project;
use PortalManager\Form;

class new_project extends Controller  {
		private $user = false;
		function __construct(){
			parent::__construct();

			$form = new Form( $_GET['response'] );
			$this->out( 'form', $form );

			$this->user = $this->getVar('user');
			$this->me = $this->getVar('me');

		
			// Subpage
			if ( !empty($this->gets[2]) ) {
				switch ($this->gets[2]) {
					case 'payments':
						if(($_GET['v'] == 'mod' && $_GET['a'] == 'edit') || $_GET['v'] == 'remove') {
							$check = new Payment($_GET['id'], $this->Projects->arg );
							if($check->ID()) {
								$this->out('check', $check);
							}
						}
						$this->out('controlpages', $this->gets[2]);
					break;
				}
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

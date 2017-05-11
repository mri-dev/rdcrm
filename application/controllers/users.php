<?
use ProjectManager\Project;
use PortalManager\User;
use PortalManager\Form;

class users extends Controller  {
		function __construct(){
			parent::__construct();

			$form = new Form( $_GET['response'] );
			$this->out( 'form', $form );

			$this->user = $this->getVar('user');
			$this->me = $this->getVar('me');
      $this->out('userlist', $this->User->getUserList());

			if (!$this->me->isAdmin()) {
				\Helper::reload('/');
				exit;
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

		public function account()
		{
			$this->temp = '/'.__FUNCTION__;

			if( !$this->me->isAdmin() ) {
				\Helper::reload('/');
			}

			if($_GET['action'] == 'edit') {
				$user = new User($_GET['id'], $this->Projects->arg);
				$this->out('edituser', $user);
			}

		}

		function __destruct(){
			// RENDER OUTPUT
			parent::bodyHead();					# HEADER
			$this->displayView( __CLASS__.$this->temp.'/index', true );		# CONTENT
			parent::__destruct();				# FOOTER
		}
	}

?>

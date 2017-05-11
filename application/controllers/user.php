<? 
use PortalManager\Form;
use PortalManager\Categories;
use PortalManager\Ad;

class user extends Controller  {
		private $temp = '';
		function __construct(){	
			parent::__construct();
			parent::$pageTitle = $this->settings['page_slogan'];

			$form = new Form( $_GET['response'] );
			$this->out( 'form', $form );

			if( isset( $this->vars[user][user_group] ) && $this->vars[user][user_group] != $this->vars[settings][USERS_GROUP_USER] ) {
				\Helper::reload( '/employer' );
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

		function register() {
			$this->temp = '/'.__FUNCTION__;
			
			// Területek
			$c = (new Categories( 'teruletek', array( 
				'db' => $this->db,
				'orderby' => 'neve',
				'order' => 'ASC' 
			) ))->getTree( 1 );

			// Terület adatok
			$this->out( 'teruletek', $c );
			// Családi állapotok
			$this->out( 'marital_statuses', $this->User->csaladi_allapotok );
			$this->out( 'budapest_id', 	\PortalManager\Categories::TYPE_TERULETEK_BUDAPEST_ID );
			$this->out( 'districts', 	(new Categories( \PortalManager\Categories::TYPE_TERULETEK, array( 'db' => $this->db ) ))->getChildCategories( \PortalManager\Categories::TYPE_TERULETEK_BUDAPEST_ID ) );
		}

		function dashboard() {
			//$this->temp = '/'.__FUNCTION__;
		}

		function applicant_for_job() {
			$user = $this->getVar('user');

			$this->out( 'applicants', $this->User->getApplicants( $user['data']['ID'] ) );

			// Jelentkezéshez a hirdetmény
			if( $_GET['a'] == 'applicant' && isset($_GET['job']) ) {
				$this->out( 'app', new Ad( $_GET['job'], array( 'admin' => true, 'db' => $this->db, 'settings' => $this->settings ) ) );
			}
		}


		function settings() {
			//$this->temp = '/'.__FUNCTION__;
			$user = $this->getVar('user');

			// Kompetencia adatok
			$c = (new Categories( \PortalManager\Categories::TYPE_KOMPETENCIAK, array( 'db' => $this->db ) ))->getTree();
			$this->out( 'kompetenciak', $c ); 

			// Kompetencia ID-k			
			$this->out( 'kompetencia_id', $user['data']['kompetenciak'] ); 

			// Területek
			$c = (new Categories( 'teruletek', array( 
				'db' => $this->db,
				'orderby' => 'neve',
				'order' => 'ASC' 
			) ))->getTree( 1 );
			
			// Terület adatok
			$this->out( 'teruletek', $c ); 
			// Családi állapotok
			$this->out( 'marital_statuses', $this->User->csaladi_allapotok );
			$this->out( 'budapest_id', 	\PortalManager\Categories::TYPE_TERULETEK_BUDAPEST_ID );
			$this->out( 'districts', 	(new Categories( \PortalManager\Categories::TYPE_TERULETEK, array( 'db' => $this->db ) ))->getChildCategories( \PortalManager\Categories::TYPE_TERULETEK_BUDAPEST_ID ) );
			$this->out( 'tooltip_logo', \PortalManager\Formater::tooltip( $this->User->lang['lng_users_profilimg']));
		}


		function logout()
		{
			$this->User->logout();
            \Helper::reload( '/'.__CLASS__ );
		}
		
		function __destruct(){
			// RENDER OUTPUT
			parent::bodyHead();					# HEADER
			$this->displayView( __CLASS__.$this->temp.'/index', true );		# CONTENT
			parent::__destruct();				# FOOTER
		}
	}

?>
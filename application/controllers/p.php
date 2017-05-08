<?
use ProjectManager\Project;
use ProjectManager\Payments;
use ProjectManager\Documents;
use ProjectManager\Document;
use ProjectManager\Payment;
use PortalManager\Form;

class p extends Controller  {
		private $user = false;
		function __construct(){
			parent::__construct();

			$form = new Form( $_GET['response'] );
			$this->out( 'form', $form );

			$this->user = $this->getVar('user');
			$this->me = $this->getVar('me');

			$editor = false;

			if ($this->me->isAdmin() || $this->me->isReferer()) {
				$editor = true;
			}
			$this->out('editor', $editor);

			$this->out('projects', $this->getVar('projects'));

			$project = new Project($this->gets[1], $this->me, $this->Projects->arg );
			$this->out('p', $project);
			$payments = new Payments($project, $this->Projects->arg );
			$this->out('payments', $payments);
			$this->out('project_payments', $payments->getList());

			$documents = new Documents($project->ID(), $this->Projects->arg );
			$this->out('documents', $documents);
			$this->out('documents_list', $documents->getList());


			if( !$project->ID()) {
				Helper::reload($this->settings['page_url']);
				exit;
			}

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
					case 'documents':
						if(($_GET['v'] == 'mod' && $_GET['a'] == 'edit') || $_GET['v'] == 'remove') {
							$check = new Document($_GET['id'], $this->Projects->arg );

							if($check->ID()) {
								$this->out('check', $check);
							} else {
								Helper::reload('/p/'.$project->ID());
							}
						}
						if($_GET['v'] == 'open' && !empty($_GET['doc'])) {
							$check = new Document($_GET['doc'], $this->Projects->arg );
							$fileurl = $check->PathURL();
							$check->logFileOpen($this->me);
							Helper::reload($fileurl);
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

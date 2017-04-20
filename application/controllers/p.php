<?
use ProjectManager\Project;
use ProjectManager\Payments;

class p extends Controller  {
		private $user = false;
		function __construct(){
			parent::__construct();

			$this->user = $this->getVar('user');
			$this->me = $this->getVar('me');

			$this->projects = $this->Projects->getList($this->me);
			$this->out('projects', $this->projects);

			$project = new Project($this->gets[1], $this->me, $this->Projects->arg );
			$this->out('p', $project);
			$payments = new Payments($project, $this->Projects->arg );
			$this->out('payments', $payments);
			$this->out('project_payments', $payments->getList());

			if( !$project->ID()) {
				Helper::reload($this->settings['page_url']);
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

		function __destruct(){
			// RENDER OUTPUT
			parent::bodyHead();					# HEADER
			$this->displayView( __CLASS__.'/index', true );		# CONTENT
			parent::__destruct();				# FOOTER
		}
	}

?>

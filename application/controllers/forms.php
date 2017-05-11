<?
use PortalManager\Ads;
use PortalManager\Ad;
use PortalManager\User;
use PortalManager\Admins;
use PortalManager\Form;
use TransactionManager\Barion;
use TransactionManager\Transaction;
use ExceptionManager\RedirectException;
use ProjectManager\Payment;
use ProjectManager\Project;
use ProjectManager\Document;

class forms extends Controller {
	function __construct(){
		parent::__construct();

		if( empty($_POST) ) {
			Helper::reload('/');
		}

		$form = new Form( $_GET['response'] );
		$this->out( 'form', $form );

		// SEO Információk
		$SEO = null;
	}

	function payments()
	{
		$this->hidePatern = true;
		$return_url = $_POST['return'];

		$payment = new Payment($_POST['id'], $this->Projects->arg );
		$me = $this->getVar('me');

		switch( $_POST['for'] )
		{
			case 'actionsave':
				$done = false;

				if(isset($_POST['setCompleted'])) {
					$payment->setCompleted();
					$done = sprintf("Sikeresen befizetetté jelölte a(z) %s díjbekérőt.", $payment->Name());
				}

				if(isset($_POST['setUncompleted'])) {
					$payment->setUncompleted();
					$done = sprintf("Sikeresen befizetetlenné jelölte a(z) %s díjbekérőt.", $payment->Name());
				}

				if(isset($_POST['remove'])) {
					$payment->delete();
					$return_url = '/p/'.$payment->ProjectID();
					$done = 'Sikeresen befizetetté jelölte a(z) <strong>'.$payment->Name().'</strong> díjbekérőt.';
				}

				if($done) {
					\PortalManager\Form::formDone( $done, false, $return_url );
				} else {
					Helper::reload($return_url);
					exit;
				}
			break;
			case 'edit': case 'add':
				$return_url = $_POST['return'];

				if (!$payment->canControl($me)) {
					\PortalManager\Form::formError( 'Önnek nincs jogosultsága erre a műveletre.', false, $return_url );
				}

				try {
					$payment->creator( $_POST );
					\PortalManager\Form::formDone( 'Sikeresen elmentette a változásokat.', false, $return_url );
				} catch (RedirectException $e) {
					$e->redirect();
				}

			break;

			case 'remove':
				$return_url = $_POST['return'];

				if (!$payment->canControl($me)) {
					\PortalManager\Form::formError( 'Önnek nincs jogosultsága erre a műveletre.', false, $return_url );
				}

				try {
					$payment->delete();
					\PortalManager\Form::formDone( 'Sikeresen törölte a díjbekérőt.', false, $return_url );
				} catch (RedirectException $e) {
					$e->redirect();
				}
			break;
		}
	}

	function projects()
	{
		$this->hidePatern = true;
		$return_url = $_POST['return'];

		$me = $this->getVar('me');
		$project = new Project($_POST['projectid'], $me, $this->Projects->arg );

		switch( $_POST['for'] )
		{
			case 'settings':
				try {
					$project->save($_POST);
					\PortalManager\Form::formDone( 'Sikeresen elmentette a változásokat.', false, $return_url );
				} catch (RedirectException $e) {
					$e->redirect();
				}
			break;
			case 'create':
				try {
					$id = $project->create($_POST);
					$newproject = new Project($id, $me, $this->Projects->arg );
					\PortalManager\Form::formDone( '<strong>'.$newproject->Name() . '</strong> project sikeresen létrehozva. Tekintse át az adatokat, majd tegye aktívvá a projektet.', false, '/p/'.$id );
				} catch (RedirectException $e) {
					$e->redirect();
				}
			break;
		}
	}

	function documents()
	{
		$this->hidePatern = true;
		$return_url = $_POST['return'];

		$me = $this->getVar('me');
		$document = new Document($_POST['id'], $this->Projects->arg );

		switch( $_POST['for'] )
		{
			case 'add': case 'edit':
				try {
					$id = $document->creator($_POST);
					$newproject = new Document($id, $this->Projects->arg );
					\PortalManager\Form::formDone( '<strong>'.$newproject->Name() . '</strong> project sikeresen létrehozva. Tekintse át az adatokat, majd tegye aktívvá a projektet.', false, $return_url );
				} catch (RedirectException $e) {
					$e->redirect();
				}
			break;
		}
	}


	/**
	 * Regisztrációk
	 * */
	function register() {
		$this->hidePatern = true;

		// Users class
		$users = $this->User;

		$return_url = $_POST['return'];

		try {
			$users->add( $_POST );
			\PortalManager\Form::formDone( 'Sikeres regisztráció! E-mail címére küldünk egy aktiváló e-mailt, amivel aktiválhatja regisztrációját. Bizonyos levelező szolgáltatókhoz később érkezhet meg az e-mail. A biztonság kedvéért ellenőrizze a SPAM mappákat is.', false, $return_url );
		} catch (RedirectException $e) {
			$e->redirect();
		}

	}

	/**
	 * Azonosítás / Belépés
	 * */
	function auth() {
		$this->hidePatern = true;

		// Users class
		$users = $this->User;

		$return_url = $_POST['return'];

		switch( $_POST['for'] ) {
			case 'user':
				try {
					$users->login( $_POST );
					\PortalManager\Form::formDone( 'Sikeresen bejelentkezett!', false, '/' );
				} catch (RedirectException $e) {
					$e->redirect();
				}
			break;
		}
	}

	/**
	 * Munkavállalóval kapcsolatos űrlap feldolgozások
	 * **/
	public function user()
	{
		$this->hidePatern = true;

		$return_url = $_POST['return'];

		switch( $_POST['for'] ) {
			case 'creator':

				try {
					$msg = $this->User->autocreator( $_POST );
					\PortalManager\Form::formDone( $msg, false, $return_url);
				} catch (RedirectException $e) {
					$e->redirect();
				}
			break;
		}
	}

	/**
	 * Jelszó reszetelés
	 * **/
	public function resetpassword()
	{
		$lang = array_merge (
	        $this->lang->loadLangText( 'class/users', true )
	    );

		/* */
		try {
			$this->User->resetPassword( $_POST['data'], $_POST['user_group'] );

			\PortalManager\Form::formDone( $lang['lng_users_form_password_reset_success'], false, '/'.__FUNCTION__ );
		} catch (RedirectException $e) {
			$e->redirect();
		}
		/* */
	}

	function __destruct(){
		// RENDER OUTPUT
		parent::bodyHead();					# HEADER
		$this->displayView( __CLASS__.'/index', true );		# CONTENT
		parent::__destruct();				# FOOTER
	}
}
?>

<?
use PortalManager\Ads;
use PortalManager\Ad;
use PortalManager\User;
use PortalManager\Admins;
use PortalManager\Form;
use TransactionManager\Barion;
use TransactionManager\Transaction;
use ExceptionManager\RedirectException;

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
					\PortalManager\Form::formDone( 'Sikeresen bejelentkezett!', false, $return_url );
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
			// Munkavállaló alapadatok módosítása
			case 'settings_basic':
				// Objects
		        $lang = array_merge (
		            $this->lang->loadLangText( 'class/users', true ),
		            $this->lang->loadLangText( 'mails', true )
		        );

		        $user = $this->getVar('user');

				try {
					$msg = $this->User->change( $user['data']['ID'], $user['data']['user_group'], $_POST['data'], $_POST['details'] );
					\PortalManager\Form::formDone( $msg, false, '/user/settings/', 'basic' );
				} catch (RedirectException $e) {
					$e->redirect();
				}
			break;
			// Munkavállaló jelszó csere
			case 'settings_password':

		        $lang = array_merge (
		            $this->lang->loadLangText( 'class/users', true ),
		            $this->lang->loadLangText( 'mails', true )
		        );

		        // Users class
				$users 	= $this->User;
				$user 	= $this->getVar('user');

		        /* */
				try {
					$users->changePassword( $user['data']['ID'], $_POST['data'] );
					\PortalManager\Form::formDone( $lang['lng_users_form_password_success_change'], false, '/user/settings/', 'password' );
				} catch (RedirectException $e) {
					$e->redirect( 'password' );
				}
				/* */
			break;
			// Munkavállaló Europass önéletrajz
			case 'europass':

		        $lang = array_merge (
		            $this->lang->loadLangText( 'class/users', true ),
		            $this->lang->loadLangText( 'mails', true )
		        );

		        // Users class
				$users 	= $this->User;
				$user 	= $this->getVar('user');

		        /* */
				try {
					$users->changeEuropass( $user['data']['ID'], $_FILES['xml'] );
					\PortalManager\Form::formDone( $lang['lng_users_europass_success_change'], false, '/user/settings/' );
				} catch (RedirectException $e) {
					$e->redirect( );
				}
				/* */
			break;

			// Munkavállaló jelentkezés egy munkára
			case 'app_for_job':

		        $lang = array_merge (
		            $this->lang->loadLangText( 'class/ad', true ),
		            $this->lang->loadLangText( 'mails', true )
		        );

		        // Users class
				$users 	= $this->User;
				$user 	= $this->getVar('user');

		        /* */
				try {
					$ad = new Ad( $_POST['id'], array(
						'db' => $this->db,
						'settings' => $this->settings,
						'admin' => true,
						'lang' => $lang,
						'smarty' => $this->smarty
					));

					$ad->applicantForJob( $user['data']['ID'], $_POST['data']['message'] );

					\PortalManager\Form::formDone( $lang['lng_applicant_form_apped'], false, '/user/applicant_for_job/' );
				} catch (RedirectException $e) {
					$e->redirect();
				}
				/* */
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

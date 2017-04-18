<? 
use PortalManager\Admins;
use PortalManager\Form;
use PortalManager\Categories;
use PortalManager\Category;
use PortalManager\UserList;
use PortalManager\User;
use PortalManager\AdServices;
use PortalManager\Services;
use PortalManager\Ads;
use ExceptionManager\RedirectException;
use PortalManager\Pages;
use TransactionManager\Transactions;
use MailManager\Mailer;

class cp extends Controller {
	private $admin;
	function __construct(){	
		parent::__construct( array(
			'root' => 'cp'
		) );
		parent::$pageTitle = 'ADMIN';

		$form = new Form( $_GET['response'] );
		$this->out( 'form', $form );

		$lang_admin = array_merge (
            $this->lang->loadLangText( 'adminobject', true )
        );
		$this->admins = new Admins( array( 
			'db' => $this->db, 
			'smarty' => $this->smarty, 
			'lang' => $lang_admin,
			'view' => $this->getAllVars()
		));

		$this->out( 'admin', 	$this->admins->get() );
		$this->out( 'root', 	'/'.__CLASS__.'/' );

		// Dashboard
		if ( !isset($this->view->gets[1]) ) 
		{
			// Felhasználók listája
			$filters 				= array();
			$filters['user_group'] 	= $this->settings['USERS_GROUP_USER'];
			$filters['orderby'] 	= 'u.regisztralt DESC';
			$users = new UserList( array(
				'db' 		=> $this->db,
				'settings' 	=> $this->settings,
				'filters' 	=> $filters,
				'limit' 	=> 10
			) );
			$users->getList();
			$user_register_30day = $this->db->query("SELECT count(id) FROM ".\PortalManager\Users::TABLE_NAME." WHERE user_group = ".$this->settings['USERS_GROUP_USER']." and DATEDIFF( NOW(), regisztralt ) <= 30;")->fetchColumn();
			$user_register_90day = $this->db->query("SELECT count(id) FROM ".\PortalManager\Users::TABLE_NAME." WHERE user_group = ".$this->settings['USERS_GROUP_USER']." and DATEDIFF( NOW(), regisztralt ) <= 90;")->fetchColumn();

			// Munkáltatók listája
			$filters 				= array();
			$filters['user_group'] 	= $this->settings['USERS_GROUP_EMPLOYER'];
			$filters['orderby'] 	= 'u.regisztralt DESC';
			$employers = new UserList( array(
				'db' 		=> $this->db,
				'settings' 	=> $this->settings,
				'filters' 	=> $filters,
				'limit' 	=> 10
			) );
			$employers->getList();
			$employers_register_30day = $this->db->query("SELECT count(id) FROM ".\PortalManager\Users::TABLE_NAME." WHERE user_group = ".$this->settings['USERS_GROUP_EMPLOYER']." and DATEDIFF( NOW(), regisztralt ) <= 30;")->fetchColumn();
			$employers_register_90day = $this->db->query("SELECT count(id) FROM ".\PortalManager\Users::TABLE_NAME." WHERE user_group = ".$this->settings['USERS_GROUP_EMPLOYER']." and DATEDIFF( NOW(), regisztralt ) <= 90;")->fetchColumn();

			// Tranzakciók
			$lang = array_merge(
				$this->lang->loadLangText( 'transaction', true )
			);
			$transactions = (new Transactions(array( 
				'db' 		=> $this->db, 
				'settings' 	=> $this->settings,
				'lang' 		=> $lang
			)));

			$trans_total_out 	= $this->db->query("SELECT sum(ertek) FROM ".\TransactionManager\Transactions::TABLE." WHERE aktivalt = 1 and ertek < 0;")->fetchColumn();
			$trans_total_in 	= $this->db->query("SELECT sum(ertek) FROM ".\TransactionManager\Transactions::TABLE." WHERE aktivalt = 1 and ertek > 0;")->fetchColumn();

			$this->out( 'users', 				$users );
			$this->out( 'users_count_30day',	$user_register_30day );
			$this->out( 'users_count_90day',	$user_register_90day );
			$this->out( 'employers', 			$employers );	
			$this->out( 'employers_count_30day',$employers_register_30day );
			$this->out( 'employers_count_90day',$employers_register_90day );
			$this->out( 'transactions',			$transactions->getList( false, array( 'limit' => 50 ) ) );
			$this->out( 'trans_out',			$trans_total_out );
			$this->out( 'trans_in',				$trans_total_in );

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

	function forms() {
		$this->hidePatern = true;

		$return_url = $_POST['return'];

		switch( $_POST['for'] ) {
			case 'employer_balance':	

				$lang = array_merge(
					$this->lang->loadLangText( 'class/users', true ),
					$this->lang->loadLangText( 'mails', true ),
					$this->lang->loadLangText( 'global', true )
				);		

				if( $_POST['type'] == '' ) {
					\PortalManager\Form::formError( 'Egyenlegfeltöltésnél válassza ki a tranzakció típusát!', false, $return_url );
					exit;
				}

				try {
					$user = new User( $_POST['uid'], array(
						'db' => $this->db,
						'settings' => $this->settings,
						'lang' => $lang,
						'smarty' => $this->smarty
					));

					// Egyenleg feltöltés
					$alert = ( isset($_POST['user_alert']) ) ? true : false;

					
					$user->balance( $_POST['amount'], $_POST['type'], $alert );

					\PortalManager\Form::formDone( 'Egyenlegfeltöltés sikeres volt.', false, $return_url );
				} catch (RedirectException $e) {
					$e->redirect();
				}

			break;
			case 'employer_ad_package':	

				$lang = array_merge(
					$this->lang->loadLangText( 'class/users', true ),
					$this->lang->loadLangText( 'mails', true ),
					$this->lang->loadLangText( 'global', true )
				);		

				if( $_POST['package'] == '' ) {
					\PortalManager\Form::formError( 'Kérjük, hogy válassza ki a jóváírandó csomagot!', false, $return_url );
					exit;
				}

				$xpackage = explode("-",$_POST['package']);

				try {
					$user = new User( $_POST['uid'], array(
						'db' => $this->db,
						'settings' => $this->settings,
						'lang' => $lang,
						'smarty' => $this->smarty
					));

					$lang = array_merge( 
						$this->lang->loadLangText( 'services', true )
					);	

					$packages = new AdServices( array(
						'db' 		=> $this->db,
						'settings' 	=> $this->settings,
						'lang' 		=> $lang, 
						'filters' 	=> array(
							'admin' => 1
						)
					) );

					$selected_package = $packages->getAd( $xpackage[0] );

					$this->db->insert(
						\PortalManager\Ad::TABLE_PACKAGES_BUYED,
						array(
							'fiok_id' 			=> $user->getID(),
							'csomag_azonosito' 	=> $xpackage[0],
							'elerheto_napok' 	=> $xpackage[1],
							'kiadott_hirdetes' 	=> $selected_package->getHirdetes(),
							'hirdetes_maradt' 	=> $selected_package->getHirdetes(),
							'vasarolva' 		=> NOW
						)
					);
					
					// Kiértesítés
					if( isset( $_POST['user_alert'] ) )	
					{
						$this->out( 'user', 		$user );
						$this->out( 'csomag', 		$selected_package->getTitle() );
						$this->out( 'hirdetes', 	$selected_package->getHirdetes() );

						$mail = new Mailer( $this->settings['page_title'], $this->settings['email_noreply_address'], $this->settings['mail_sender_mode'] );

						$mail->add( $user->getEmail() );
						$mail->setSubject( 'Csomag jóváírás: '. $selected_package->getTitle() );
						$mail->setMsg( $this->smarty->fetch( 'mails/'.\PortalManager\Lang::getCurrentLang().'/gift_ad_package.tpl' )  );
						$mail->sendMail();
					}			

					\PortalManager\Form::formDone( 'Hirdetői-csomag sikeresen jóváírva '.$user->getName().' részére.', false, $return_url );
				} catch (RedirectException $e) {
					$e->redirect();
				}

			break;
			case 'login_admin':
				try {
					$this->admins->login( $_POST );
					\PortalManager\Form::formDone( 'Sikeresen bejelentkezett!', false, $return_url );
				} catch (RedirectException $e) {
					$e->redirect();
				}
			break;
			case 'settings_basic':
				try {
					$this->admins->saveSettings( $_POST['data'] );
					\PortalManager\Form::formDone( 'Változások mentésre kerültek!', false, $return_url );
				} catch (RedirectException $e) {
					$e->redirect();
				}
			break;	
			case 'munkavallaloi_kompetenciak_add':
			case 'oktatas_kategoriak_add':
			case 'munkakorok_add':
			case 'munkatipusok_add':
			case 'teruletek_add':
				try {

					$editing_cat = new Category(
						$_POST['cat_type'], 
						$_POST['id'], 
						array( 
							'db' => $this->db 
						)
					);

					switch( $_POST['do'] ) {
						// Add
						case 'add':
							$editing_cat->add( array(
								'neve' 		=> $_POST['data']['neve'],
								'szulo_id' 	=> $_POST['data']['szulo_id'],
								'sorrend' 	=> $_POST['data']['sorrend'],
							) );
							\PortalManager\Form::formDone( 'Az elem sikeresen létrehozva!', false, $return_url );
						break;
						// Edit
						case 'edit':
							$editing_cat->edit( array(
								'neve' 		=> $_POST['data']['neve'],
								'szulo_id' 	=> $_POST['data']['szulo_id'],
								'sorrend' 	=> $_POST['data']['sorrend'],
							) );
							\PortalManager\Form::formDone( 'Változások mentésre kerültek!', false, $return_url );
						break;
						// Delete
						case 'delete':
							$editing_cat->delete();
							\PortalManager\Form::formDone( 'Az elem véglegesen törlésre került!', false, $return_url );
						break;
					}					
				} catch (RedirectException $e) {
					$e->redirect( );
				}
				
			break;		
			case 'services_ad':
				try {
					$this->admins->saveAdServices( $_POST['data'] );
					\PortalManager\Form::formDone( 'Változások mentésre kerültek!', false, $return_url );
				} catch (RedirectException $e) {
					$e->redirect( 'ad' );
				}
			break;
			case 'services_basic':
				try {
					$this->admins->saveServiceBasic( $_POST['data'] );
					\PortalManager\Form::formDone( 'Változások mentésre kerültek!', false, $return_url );
				} catch (RedirectException $e) {
					$e->redirect( 'basic' );
				}
			break;
			case 'services_extra':
				try {
					$this->admins->saveServiceExtra( $_POST['data'] );
					\PortalManager\Form::formDone( 'Változások mentésre kerültek!', false, $return_url );
				} catch (RedirectException $e) {
					$e->redirect( 'extra' );
				}
			break;

			case 'services_ad_hossz':
				try {
					$this->admins->saveAdServicesExtensionPrices( $_POST['data'] );
					\PortalManager\Form::formDone( 'Változások mentésre kerültek!', false, $return_url );
				} catch (RedirectException $e) {
					$e->redirect( 'adh' );
				}
			break;
			case 'pages_save':
				$pages = new Pages( $_POST['page_id'], array( 'db' => $this->db )  );
				try{
					$pages->save( $_POST['data'] );	
					\PortalManager\Form::formDone( 'Változások mentésre kerültek!', false, $return_url );
				}catch(RedirectException $e){
					$e->redirect();
				}
			break;
			case 'pages_create':
				$pages = new Pages( $_POST['page_id'], array( 'db' => $this->db )  );
				try{
					$pages->add( $_POST['data'] );
					\PortalManager\Form::formDone( 'Oldal sikeresen létrehozva!', false, $return_url );	
				}catch(RedirectException $e){
					$e->redirect();
				}
			break;
		}
	}

	public function szolgaltatasok()
	{
		
		// Hirdetői szolgáltatások
		$lang = array_merge(
			$this->lang->loadLangText( 'services', true )
		);

		$ad_services = new AdServices( array(
			'db' => $this->db,
			'settings' => $this->settings,
			'lang' => $lang, 
			'filters' => array(
				'admin' => 1,
				'group' => 'job'
			)
		) );
		$napok = (new Ads())->adsRunningDays;

		$edu_services = new AdServices( array(
			'db' => $this->db,
			'settings' => $this->settings,
			'lang' => $lang, 
			'filters' => array(
				'admin' => 1,
				'group' => 'education'
			)
		) );
		$edu_napok = (new Ads())->edusRunningDays;


		$services = new Services( array(
			'db' 		=> $this->db,
			'settings' 	=> $this->settings,
			'lang' 		=> $lang, 
			'filters' 	=> array(
				'admin' => 1
			)
		) );

		$ad_services->getList();
		$edu_services->getList();
		$services->getList();

		$this->out( 'ad_days', 		$napok );
		$this->out( 'ad_services', 	$ad_services );

		$this->out( 'edu_days', 	$edu_napok );
		$this->out( 'edu_services', $edu_services );

		$this->out( 'services', 	$services );

		$this->out( 'tooltip_idotartam', \PortalManager\Formater::tooltip('Választható hirdetés időtartam futamidők. Vesszővel válasszuk el.') );
	}

	public function munkavallaloi_kompetenciak()
	{
		
		$cat = new Categories(
			\PortalManager\Categories::TYPE_KOMPETENCIAK,
			array(
				'db' => $this->db
			)
		);

		$this->out( 'editbox_title', 'Létrehozás' );
		$this->out( 'editbox_btn', 'Létrehozás' );
		$this->out( 'editbox_type', 'warning' );
		$this->out( 'editbox_icon', 'plus-circle' );

		$this->out( 'lista', $cat->getTree() );
		$this->out( 'cat_type', \PortalManager\Categories::TYPE_KOMPETENCIAK );
		$this->out( 'cat_title', 'Munkavállalói kompetenciák' );

		// Szerkesztés & Törlés
		$item = false;
		if( $this->gets[2] == 'edit' || $this->gets[2] == 'delete' ) {
			$item = new Category( 
				\PortalManager\Categories::TYPE_KOMPETENCIAK, 
				$this->gets[3], 
				array( 
					'db' => $this->db 
				)
			);

			$this->out( 'editbox_title', 'Elem szerkesztése' );
			$this->out( 'editbox_btn', 'Módosítás' );
			$this->out( 'editbox_type', 'success' );
			$this->out( 'editbox_icon', 'save' );

			$this->out( 'form_value_neve', $item->getName() );
			$this->out( 'form_value_szulo_id', $item->getParentKey() );
			$this->out( 'form_value_sorrend', $item->getSortNumber() );

			if( $this->gets[2] == 'delete' ) {
				$this->out( 'editbox_title', 'Elem törlése' );
				$this->out( 'editbox_btn', 'Végleges törlés' );
				$this->out( 'editbox_type', 'danger' );
				$this->out( 'editbox_icon', 'times' );
			}
		}
		// Kategória objektum
		$this->out( 'item', $item );

	}

	public function pages()
	{
		$pages = new Pages( $this->gets[2], array( 
			'db' => $this->db )  
		);		
		$pages->setAdmin( true );

		switch( $this->gets[1] ){
			case 'szerkeszt': case 'torles':
				$this->out( 'page', $pages->get( $this->gets[2]) );			
			break;
		}

		if( $this->gets[2] == 'szerkeszt' || $this->gets[2] == 'torol' ) {
			$this->out( 'page', $pages->get( $this->gets[3] ) );
		}

		// Oldal fa betöltés
		$page_tree 	= $pages->getTree();
		$this->out( 'pages', $page_tree );

		$this->out('tooltip_gyujto', \PortalManager\Formater::tooltip('A gyűjtő oldalnak jelölt oldalaknál nincs tartalom megjelenítés. Csak arra szolgál, hogy fa szerkezetbe rendezzük és összefogjunk egy adott témakörrel foglalkozó oldalakat.') );
	}

	public function munkatipusok()
	{
		
		$cat = new Categories(
			\PortalManager\Categories::TYPE_MUNKATIPUS,
			array(
				'db' => $this->db
			)
		);

		$this->out( 'editbox_title', 'Létrehozás' );
		$this->out( 'editbox_btn', 'Létrehozás' );
		$this->out( 'editbox_type', 'warning' );
		$this->out( 'editbox_icon', 'plus-circle' );

		$this->out( 'lista', $cat->getTree() );
		$this->out( 'cat_type', \PortalManager\Categories::TYPE_MUNKATIPUS );
		$this->out( 'cat_title', 'Munkatípusok' );

		// Szerkesztés & Törlés
		$item = false;
		if( $this->gets[2] == 'edit' || $this->gets[2] == 'delete' ) {
			$item = new Category( 
				\PortalManager\Categories::TYPE_MUNKATIPUS, 
				$this->gets[3], 
				array( 
					'db' => $this->db 
				)
			);

			$this->out( 'editbox_title', 'Elem szerkesztése' );
			$this->out( 'editbox_btn', 'Módosítás' );
			$this->out( 'editbox_type', 'success' );
			$this->out( 'editbox_icon', 'save' );

			$this->out( 'form_value_neve', $item->getName() );
			$this->out( 'form_value_szulo_id', $item->getParentKey() );
			$this->out( 'form_value_sorrend', $item->getSortNumber() );

			if( $this->gets[2] == 'delete' ) {
				$this->out( 'editbox_title', 'Elem törlése' );
				$this->out( 'editbox_btn', 'Végleges törlés' );
				$this->out( 'editbox_type', 'danger' );
				$this->out( 'editbox_icon', 'times' );
			}
		}
		// Kategória objektum
		$this->out( 'item', $item );

	}

	public function munkakorok()
	{
		
		$cat = new Categories(
			\PortalManager\Categories::TYPE_MUNKAKOROK,
			array(
				'db' => $this->db
			)
		);

		$this->out( 'editbox_title', 'Létrehozás' );
		$this->out( 'editbox_btn', 'Létrehozás' );
		$this->out( 'editbox_type', 'warning' );
		$this->out( 'editbox_icon', 'plus-circle' );

		$this->out( 'lista', $cat->getTree() );
		$this->out( 'cat_type', \PortalManager\Categories::TYPE_MUNKAKOROK );
		$this->out( 'cat_title', 'Munkakörök' );

		// Szerkesztés & Törlés
		$item = false;
		if( $this->gets[2] == 'edit' || $this->gets[2] == 'delete' ) {			
			$item = new Category( 
				\PortalManager\Categories::TYPE_MUNKAKOROK, 
				$this->gets[3], 
				array( 
					'db' => $this->db 
				)
			);
			
			$this->out( 'editbox_title', 'Elem szerkesztése' );
			$this->out( 'editbox_btn', 'Módosítás' );
			$this->out( 'editbox_type', 'success' );
			$this->out( 'editbox_icon', 'save' );

			$this->out( 'form_value_neve', $item->getName() );
			$this->out( 'form_value_szulo_id', $item->getParentKey() );
			$this->out( 'form_value_sorrend', $item->getSortNumber() );

			if( $this->gets[2] == 'delete' ) {
				$this->out( 'editbox_title', 'Elem törlése' );
				$this->out( 'editbox_btn', 'Végleges törlés' );
				$this->out( 'editbox_type', 'danger' );
				$this->out( 'editbox_icon', 'times' );
			}
		}
		// Kategória objektum
		$this->out( 'item', $item );

	}

	public function oktatas_kategoriak()
	{
		
		$cat = new Categories(
			\PortalManager\Categories::TYPE_STUDIES,
			array(
				'db' => $this->db
			)
		);

		$this->out( 'editbox_title', 'Létrehozás' );
		$this->out( 'editbox_btn', 'Létrehozás' );
		$this->out( 'editbox_type', 'warning' );
		$this->out( 'editbox_icon', 'plus-circle' );

		$this->out( 'lista', $cat->getTree() );
		$this->out( 'cat_type', \PortalManager\Categories::TYPE_STUDIES );
		$this->out( 'cat_title', 'Oktatás kategóriái' );

		// Szerkesztés & Törlés
		$item = false;
		if( $this->gets[2] == 'edit' || $this->gets[2] == 'delete' ) {
			$item = new Category( 
				\PortalManager\Categories::TYPE_STUDIES, 
				$this->gets[3], 
				array( 
					'db' => $this->db 
				)
			);

			$this->out( 'editbox_title', 'Elem szerkesztése' );
			$this->out( 'editbox_btn', 'Módosítás' );
			$this->out( 'editbox_type', 'success' );
			$this->out( 'editbox_icon', 'save' );

			$this->out( 'form_value_neve', $item->getName() );
			$this->out( 'form_value_szulo_id', $item->getParentKey() );
			$this->out( 'form_value_sorrend', $item->getSortNumber() );

			if( $this->gets[2] == 'delete' ) {
				$this->out( 'editbox_title', 'Elem törlése' );
				$this->out( 'editbox_btn', 'Végleges törlés' );
				$this->out( 'editbox_type', 'danger' );
				$this->out( 'editbox_icon', 'times' );
			}
		}
		// Kategória objektum
		$this->out( 'item', $item );

	}

	public function teruletek()
	{
		
		$cat = new Categories(
			\PortalManager\Categories::TYPE_TERULETEK,
			array(
				'db' => $this->db
			)
		);

		$this->out( 'editbox_title', 'Létrehozás' );
		$this->out( 'editbox_btn', 'Létrehozás' );
		$this->out( 'editbox_type', 'warning' );
		$this->out( 'editbox_icon', 'plus-circle' );

		$this->out( 'lista', $cat->getTree() );
		$this->out( 'cat_type', \PortalManager\Categories::TYPE_TERULETEK );
		$this->out( 'cat_title', 'Területek' );

		// Szerkesztés & Törlés
		$item = false;
		if( $this->gets[2] == 'edit' || $this->gets[2] == 'delete' ) {
			$item = new Category( 
				\PortalManager\Categories::TYPE_TERULETEK, 
				$this->gets[3], 
				array( 
					'db' => $this->db 
				)
			);

			$this->out( 'editbox_title', 'Elem szerkesztése' );
			$this->out( 'editbox_btn', 'Módosítás' );
			$this->out( 'editbox_type', 'success' );
			$this->out( 'editbox_icon', 'save' );

			$this->out( 'form_value_neve', $item->getName() );
			$this->out( 'form_value_szulo_id', $item->getParentKey() );
			$this->out( 'form_value_sorrend', $item->getSortNumber() );

			if( $this->gets[2] == 'delete' ) {
				$this->out( 'editbox_title', 'Elem törlése' );
				$this->out( 'editbox_btn', 'Végleges törlés' );
				$this->out( 'editbox_type', 'danger' );
				$this->out( 'editbox_icon', 'times' );
			}
		}
		// Kategória objektum
		$this->out( 'item', $item );

	}

	public function employers()
	{
		$filters = array();
		$filters['user_group'] 	= $this->settings['USERS_GROUP_EMPLOYER'];
		$filters['orderby'] 	= 'u.nev ASC';

		$users = new UserList( array(
			'db' => $this->db,
			'settings' => $this->settings,
			'filters' => $filters
		) );

		$users->getList();

		$this->out( 'lista', $users );
		
	}

	public function users()
	{
		$filters = array();
		$filters['user_group'] 	= $this->settings['USERS_GROUP_USER'];
		$filters['orderby'] 	= 'u.nev ASC';

		$users = new UserList( array(
			'db' => $this->db,
			'settings' => $this->settings,
			'filters' => $filters
		) );

		$users->getList();

		$this->out( 'lista', $users );
		
	}


	public function beallitasok()
	{
		
	}
	
	public function logout()
	{
		$this->hidePatern = true;
		$this->admins->logout();
		Helper::reload( $this->settings['admin_root'] );
	}
	
	function __destruct(){
		// RENDER OUTPUT
		parent::bodyHead();					# HEADER
		$this->displayView( __CLASS__.'/index', true );		# CONTENT
		parent::__destruct();				# FOOTER
	}
}
?>
<?
namespace PortalManager;

use TransactionManager\Transaction;
use MailManager\Mails;
use ExceptionManager\RedirectException;
/**
 * class Users
 *
 */
class User
{
	private $db = null;
	public $smarty = null;
	public $lang = array();

	public $id 	= false;
	public $user = false;

	function __construct( $user_id, $arg = array() ){
		$this->id 			= $user_id;
		$this->db 			= $arg['db'];
		$this->settings 	= $arg[settings];
		$this->smarty 		= $arg[smarty];
		$this->lang 		= $arg[lang];

		$this->user = $this->get();

		return $this;
	}

	private function get( $arg = array() )
	{
		$ret 			= array();

		if(!$this->id) return false;

		$ret[data] 			= $this->getData( $this->id, 'ID' );
		$ret[email] 		= $ret[data][email];

		return $ret;
	}

	private function getData( $account_id, $db_by = 'email' ){
		if($account_id == '') return false;

		$q = "
		SELECT 			u.*
		FROM 			".\PortalManager\Users::TABLE_NAME." as u
		WHERE 			1 = 1 ";

		$q .= " and u.".$db_by." = '$account_id';";

		extract($this->db->q($q));

		// Details
		$det = $this->db->query("SELECT nev, ertek FROM ".\PortalManager\Users::TABLE_DETAILS_NAME." WHERE fiok_id = $account_id;")->fetchAll(\PDO::FETCH_ASSOC);

		foreach ($det as $d ) {
			$data[$d['nev']] = $d['ertek'];
		}

		return $data;
	}

	public function getUserGroup()
	{
		return $this->user['data']['user_group'];
	}

	public function getValue( $key )
	{
		$v = $this->user['data'][$key];

		if( empty($v) && !$v ) return false;

		return $v;
	}

	public function getName()
	{
		return $this->user['data']['nev'];
	}

	public function getID()
	{
		return $this->user['data']['ID'];
	}

	public function getZipCode()
	{
		return $this->user['data']['zip_code'];
	}

	public function getEmail()
	{
		return $this->user['email'];
	}

	public function isAllowed()
	{
		return ($this->user['data']['engedelyezve'] == '1') ? true : false;
	}

	public function getPhone()
	{
		return $this->user['data']['phone'];
	}

	public function getLastloginTime( $formated = false )
	{
		if( $formated ) {
			return \PortalManager\Formater::distanceDate($this->user['data']['utoljara_belepett']);
		} else {
			return $this->user['data']['utoljara_belepett'];
		}

	}

	public function getRegisterTime( $formated = false )
	{
		if( $formated ) {
			return \PortalManager\Formater::distanceDate($this->user['data']['regisztralt']);
		} else {
			return $this->user['data']['regisztralt'];
		}
	}

	public function sendEmail( $message, $email_template, $arg = array(), $from = false )
	{
		$this->checkLanguageFiles();
		$this->checkSmarty();

		if( empty($message) ) {
			$this->error( $this->lang['lng_users_form_sendmessage_miss_message'] );
		}

		$arg['message'] 	= $message;
		$arg['from_name'] 	= $from['name'];
		$arg['from_email']	= $from['email'];
		$arg['infoMsg'] 	= $this->lang['lng_mail_sendth_jobabc'];

		$mail = new Mails( $this, $email_template, $this->getEmail(), $arg );

		$mail->send();
	}

	private function error( $msg )
	{
		throw new RedirectException( $msg, $_POST['form'], $_POST['return'], $_POST['session_path'] );
	}

	public function checkLanguageFiles()
	{
		if( empty($this->lang) ) die(__CLASS__.': '.'Hiányoznak a nyelvi fájlok.');
	}
	public function checkSmarty()
	{
		if( empty($this->smarty) ) die(__CLASS__.': '.'Hiányzik a Smarty controller');
	}

	public function __destruct()
	{
		$this->db = null;
		$this->smarty = null;
		$this->user = false;
	}
}

?>

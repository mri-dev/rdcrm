<?php
namespace ProjectManager;

use \ProjectManager\Project;

class Projects
{
  const DBTABLE = 'projects';
  const DBXREFUSER = 'projects_xref_user';

  private $db = null;
  private $settings = array();
	public $smarty = null;
	public $lang = array();
  public $arg = array();

  public function __construct( $arg = array() )
  {
    $this->arg = $arg;
    $this->db	= $arg['db'];
		$this->settings = $arg[settings];
		$this->smarty	= $arg[smarty];
		$this->lang = $arg[lang];

    return $this;
  }

  public function getList(\PortalManager\User $user = null, $arg = array() )
  {
    $qparam = array();
    $projects = array();

    if (is_null($user)) {
      return $projects;
    }

    $qry = "SELECT p.ID FROM ". self::DBTABLE." as p WHERE 1=1 ";

    $qry .= " and p.active = 1 ";

    if ( $user->isAdmin() === false && $user->isReferer() === false) {
      $qry .= " and (p.user_id = :uid || :uid IN (SELECT pux.userid FROM ".self::DBXREFUSER." as pux WHERE pux.projectid = p.ID)) ";
      $qparam['uid'] = $user->getID();
    }

    $result = $this->db->squery($qry, $qparam)->fetchAll(\PDO::FETCH_ASSOC);

    if($result) {
      foreach ($result as $r) {
        $projects[] = new Project($r['ID'], $user, $this->arg);
      }
    }

    return $projects;
  }

  public function __destruct()
  {
    $this->arg = null;
    $this->db	= null;
		$this->settings = null;
		$this->smarty	= null;
		$this->lang = null;
  }

}
?>

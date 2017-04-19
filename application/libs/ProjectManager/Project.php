<?php
namespace ProjectManager;


class Project
{
  private $db = null;
  private $settings = array();
	public $smarty = null;
	public $lang = array();

  public $project_id = null;
  public $project = null;
  private $user = null;

  public function __construct( $project_id = false, \PortalManager\User $user, $arg = array() )
  {
    if(!$project_id) return false;

    $this->project_id = $project_id;
    $this->arg = $arg;
    $this->db	= $arg['db'];
		$this->settings = $arg[settings];
		$this->smarty	= $arg[smarty];
		$this->lang = $arg[lang];
    $this->user = $user;

    $this->load();

    return $this;
  }

  private function load()
  {
    $qparam = array();
    $qry = "SELECT
      p.*,
      ac.name as author_name
    FROM ". \ProjectManager\Projects::DBTABLE." as p
    LEFT OUTER JOIN ".\PortalManager\Users::TABLE_NAME." as ac ON ac.ID = p.user_id
    WHERE 1=1 and p.ID = :id ";

    $qparam['id'] = $this->project_id;

    if ( $this->user->isAdmin() === false && $this->user->isReferer() === false) {
      $qry .= " and (p.user_id = :uid || :uid IN (SELECT pux.userid FROM ".\ProjectManager\Projects::DBXREFUSER." as pux WHERE pux.projectid = p.ID)) ";
      $qparam['uid'] = $this->user->getID();
    }

    $result = $this->db->squery($qry, $qparam)->fetch(\PDO::FETCH_ASSOC);

    $this->project = $result;

    return $this;
  }

  public function ID()
  {
    return $this->project['ID'];
  }

  public function Name()
  {
    return $this->project['name'];
  }

  public function Author()
  {
    return $this->project['author_name'];
  }

  public function Description()
  {
    return $this->project['description'];
  }

  public function SandboxURL()
  {
    return $this->project['sandbox_url'];
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

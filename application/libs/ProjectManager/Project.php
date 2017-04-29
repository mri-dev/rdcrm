<?php
namespace ProjectManager;

use ExceptionManager\RedirectException;

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

  public function save( $post )
  {
    extract($post);
    $vars = array();

    if (empty($name)) {
      $this->error("Projekt elnevezése kötelező.");
    }

    if (empty($name)) {
      $this->error("Projekt rövid leírását megadni kötelező.");
    }

    $vars['name'] = $name;
    $vars['description'] = $description;
    $vars['trello_id'] = (empty($trello_id)) ? NULL :$trello_id;
    $vars['slack_id'] = (empty($slack_id)) ? NULL : $slack_id;
    $vars['sandbox_url'] = (empty($sandbox_url)) ? NULL : $sandbox_url;

    if (isset($user_id) && !empty($user_id)) {
      $vars['user_id'] = (int)$user_id;
    }

    $vars['active'] = (isset($active)) ? 1 : 0;

    $this->db->update(
      \ProjectManager\Projects::DBTABLE,
      $vars,
      sprintf("ID = %d", $this->ID())
    );
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
    $v = $this->project['sandbox_url'];
    return ($v == '') ? false : $this->project['sandbox_url'];
  }

  public function TrelloBoardID()
  {
    $v = $this->project['trello_id'];
    return ($v == '') ? false : $this->project['trello_id'];
  }

  public function SlackChannelID()
  {
    $v = $this->project['slack_id'];
    return ($v == '') ? false : $this->project['slack_id'];
  }

  public function getTotalPayments()
  {
    return (float)$this->db->query("SELECT SUM(p.amount) FROM ".\ProjectManager\Payments::DBTABLE." as p WHERE p.projectid = ".$this->ID())->fetchColumn();
  }

  public function isActive()
  {
    return ($this->project['active'] == '1') ? true : false;
  }

  public function data($key=false)
  {
    if(!$key) return $this->payment;

    return $this->project[$key];
  }

  private function error( $msg )
  {
    throw new RedirectException( $msg, $_POST['form'], $_POST['return'], $_POST['session_path'] );
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

<?php
namespace ProjectManager;

use \ProjectManager\Project;
use \ProjectManager\Payments;

class Projects
{
  const DBTABLE = 'projects';
  const DBXREFUSER = 'projects_xref_user';

  private $db = null;
  private $settings = array();
	public $smarty = null;
	public $lang = array();
  public $arg = array();

  private $projects = array();
  public $total_payment_amount = 0;
  public $paid_payment_amount = 0;

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

    if (is_null($user)) {
      return $this->projects;
    }

    $qry = "SELECT p.ID FROM ". self::DBTABLE." as p WHERE 1=1 ";

    if ( !$user->isAdmin()) {
      $qry .= " and p.active = 1 ";
    }

    if ( $user->isAdmin() === false && $user->isReferer() === false) {
      $qry .= " and (p.user_id = :uid || :uid IN (SELECT pux.userid FROM ".self::DBXREFUSER." as pux WHERE pux.projectid = p.ID)) ";
      $qparam['uid'] = $user->getID();
    }

    $qry .= " ORDER BY p.active DESC";



    $result = $this->db->squery($qry, $qparam)->fetchAll(\PDO::FETCH_ASSOC);

    if($result) {
      foreach ($result as $r) {
        $project = new Project($r['ID'], $user, $this->arg);

        // In
        $this->projects[] = $project;
      }
    }

    return $this->projects;
  }

  public function getListPayments()
  {
    foreach ((array)$this->projects as $p) {
      $ppayment = new Payments($p, $this->arg);
      $ppayment->getList();

      if(!$p->isActive()) continue;

      $this->total_payment_amount += $ppayment->total_amount;
      $this->paid_payment_amount += $ppayment->paid_amount;
    }

    unset($p);
    unset($ppayment);

    return array(
      'total' => $this->total_payment_amount,
      'paid' => $this->paid_payment_amount
    );
  }

  public function __destruct()
  {
    $this->arg = null;
    $this->db	= null;
		$this->settings = null;
		$this->smarty	= null;
		$this->lang = null;
		$this->projects = null;
  }

}
?>

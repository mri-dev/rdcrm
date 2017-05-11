<?php
namespace ProjectManager;

class Payments
{
  const DBTABLE = 'payments';

  private $db = null;
  private $settings = array();
	private $smarty = null;
	private $lang = array();
  public $arg = array();
  public $project = null;
  public $total_amount = 0;
  public $paid_amount = 0;

  public function __construct(\ProjectManager\Project $project = null, $arg = array() )
  {
    $this->arg = $arg;
    $this->db	= $arg['db'];
		$this->settings = $arg[settings];
		$this->smarty	= $arg[smarty];
		$this->lang = $arg[lang];
		$this->project = $project;

    return $this;
  }

  public function getList( $arg = array() )
  {
    $qparam = array();
    $payments = array();

    $qry = "SELECT
      p.ID,
      p.amount,
      p.completed
    FROM ". self::DBTABLE." as p
    LEFT OUTER JOIN ".\ProjectManager\Projects::DBTABLE." as pr ON pr.ID = p.projectid
    WHERE 1=1 ";

    if (!is_null($this->project)) {
      $qry .= " and p.projectid = :pid";
      $qparam['pid'] = $this->project->ID();
    }

    if (isset($arg['onlyactive']) && $arg['onlyactive'] === true) {
      $qry .= " and pr.active = 1";
    }

    if (isset($arg['onlypaid']) && $arg['onlypaid'] === true) {
      $qry .= " and p.paid_date IS NOT NULL ";
    }

    if (isset($arg['usercan'])) {
      $qry .= " and (pr.user_id = :uid || :uid IN (SELECT pxu.userid FROM ".\ProjectManager\Projects::DBXREFUSER." as pxu WHERE pxu.projectid = p.projectid))";
      $qparam['uid'] = $arg['usercan'];
    }

    if (isset($arg['deadlinein'])) {
      $qry .= " and (datediff(p.due_date, now()) < ".$arg['deadlinein']." and p.paid_date IS NULL)";
    }

    if (isset($arg['order'])) {
      $qry .= " ORDER BY ".$arg['order'];
    } else {
      $qry .= " ORDER BY p.completed ASC, p.due_date ASC ";
    }



    //echo $qry . "<br>";

    $result = $this->db->squery($qry, $qparam)->fetchAll(\PDO::FETCH_ASSOC);

    if($result) {
      foreach ($result as $r) {
        $payments[] = new Payment($r['ID'], $this->arg);

        $this->total_amount += $r['amount'];
        if($r['completed'] == 1) {
          $this->paid_amount += $r['amount'];
        }
      }
    }

    return $payments;
  }
  public function __destruct()
  {
    $this->arg = null;
    $this->db	= null;
		$this->settings = null;
		$this->smarty	= null;
		$this->lang = null;
		$this->project = null;
  }

}
?>

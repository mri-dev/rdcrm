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

    if (is_null($this->project)) {
      return $payments;
    }

    $qry = "SELECT p.ID, p.amount, p.completed FROM ". self::DBTABLE." as p WHERE 1=1 ";

    $qry .= " and p.projectid = :pid";
    $qparam['pid'] = $this->project->ID();

    $qry .= " ORDER BY p.completed ASC, p.due_date ASC ";

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

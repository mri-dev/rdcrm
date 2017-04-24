<?php
namespace ProjectManager;

class Payment
{
  private $db = null;
  private $settings = array();
	private $smarty = null;
	private $lang = array();
  private $arg = array();
  public $payment = null;
  public $payment_id = false;

  public function __construct( $payment_id = null, $arg = array() )
  {
    $this->arg = $arg;
    $this->db	= $arg['db'];
		$this->settings = $arg[settings];
		$this->smarty	= $arg[smarty];
		$this->lang = $arg[lang];
    $this->payment_id = $payment_id;

    $this->load();

    return $this;
  }

  private function load()
  {
    $qparam = array();
    $qry = "SELECT
      p.*
    FROM ". \ProjectManager\Payments::DBTABLE." as p
    WHERE 1=1 and p.ID = :id ";

    $qparam['id'] = $this->payment_id;

    $result = $this->db->squery($qry, $qparam)->fetch(\PDO::FETCH_ASSOC);

    $this->payment = $result;

    return $this;
  }

  public function Amount()
  {
    return $this->payment['amount'];
  }

  public function DueDate()
  {
    return $this->payment['due_date'];
  }

  public function PaidDate()
  {
    return $this->payment['paid_date'];
  }

  public function ID()
  {
    return $this->payment['ID'];
  }

  public function Name()
  {
    return $this->payment['name'];
  }

  public function Status($formated = false)
  {
    $status = $this->payment['completed'];

    if ($status == 0) {
      $status = 'Fizetetlen';
      if($formated) $status = '<span class="label label-danger">'.$status.'</span>';
    } else if ($status == 1){
      $status = 'Teljesítve';
      if($formated) $status = '<span class="label label-success">'.$status.'</span>';
    }

    return $status;
  }

  public function ProjectID()
  {
    return $this->payment['projectid'];
  }

  public function isCompleted()
  {
    return ($this->payment['completed'] == 0) ? false : true;
  }

  public function setCompleted()
  {
    $this->db->update(
      \ProjectManager\Payments::DBTABLE,
      array(
        'completed' => 1,
        'paid_date' => NOW
      ),
      sprintf("ID = %d", $this->ID())
    );
  }

  public function setUncompleted()
  {
    $this->db->update(
      \ProjectManager\Payments::DBTABLE,
      array(
        'completed' => 0,
        'paid_date' => NULL
      ),
      sprintf("ID = %d", $this->ID())
    );
  }


  public function data($key=false)
  {
    if(!$key) return $this->payment;

    return $this->payment[$key];
  }

  public function __destruct()
  {
    $this->arg = null;
    $this->db	= null;
		$this->settings = null;
		$this->smarty	= null;
		$this->lang = null;
		$this->payment = null;
  }

}
?>

<?php
namespace ProjectManager;

use ExceptionManager\RedirectException;

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
      p.*,
      pr.name as project_name
    FROM ". \ProjectManager\Payments::DBTABLE." as p
    LEFT OUTER JOIN ".\ProjectManager\Projects::DBTABLE." as pr ON pr.ID = p.projectid
    WHERE 1=1 and p.ID = :id ";

    $qparam['id'] = $this->payment_id;

    $result = $this->db->squery($qry, $qparam)->fetch(\PDO::FETCH_ASSOC);

    $this->payment = $result;

    return $this;
  }

  public function creator( $post )
  {
    extract($post);
    $return_id = false;

    $vars = array();

    $vars['name'] = $name;
    $vars['projectid'] = (int)$projectid;
    $vars['due_date'] = $due_date." ".$due_date_time;

    if($this->isCompleted()){
      $vars['paid_date'] = $paid_date." ".$paid_date_time;
    }
    $vars['amount'] = (float)$amount;


    // Exceptions
    if (empty($vars['name'])) {
      $this->error( 'A díjbekérőt kötelezően el kell nevezni.' );
    }
    if (empty($post['due_date'])) {
      $this->error( 'A díjbekérő fizetési határidejét meg kell határozni.' );
    }
    if (empty($vars['amount']) || (float)$vars['amount'] <= 0) {
      $this->error( 'A díjbekérő összegének nagyobbnak kell lennie, mint nulla.' );
    }

    if (isset($id) && !empty($id))
    {
      // Edit
      $return_id = $id;

      $this->db->update(
        \ProjectManager\Payments::DBTABLE,
        $vars,
        sprintf("ID = %d", $id)
      );
    } else {
      // Create
      $insert_id = $this->db->insert(
        \ProjectManager\Payments::DBTABLE,
        $vars
      );
      $return_id = $insert_id;
    }

    $return_id;
  }

  public function delete()
  {
    if (!$this->ID()) {
      return false;
    }

    $this->db->delete(\ProjectManager\Payments::DBTABLE, array("ID" => $this->ID()));
  }

  public function canControl( \PortalManager\User $user = null )
  {
    if(!$user->isAdmin() && !$user->isReferer()) {
      return false;
    }

    return true;
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

  public function ProjectName()
  {
    return $this->payment['project_name'];
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
		$this->payment = null;
  }

}
?>

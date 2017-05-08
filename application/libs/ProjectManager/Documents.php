<?php
namespace ProjectManager;

use \ProjectManager\Document;

class Documents
{
  const DBTABLE = 'documents';
  const DBTABLE_LOG_DOC_CLICK = 'documents_click';

  private $db = null;
  private $settings = array();
	private $smarty = null;
	private $lang = array();
  public $arg = array();
  private $project_id = false;

  public function __construct($project_id = false, $arg = array() )
  {
    $this->arg = $arg;
    $this->db	= $arg['db'];
		$this->settings = $arg[settings];
		$this->smarty	= $arg[smarty];
		$this->lang = $arg[lang];
    $this->project_id = $project_id;

    return $this;
  }

  public function getList( $arg = array() )
  {
    $qparam = array();
    $documents = array();

    $qry = "SELECT p.ID FROM ". self::DBTABLE." as p WHERE 1=1 ";

    $qry .= " and p.projectid = :pid";
    $qparam['pid'] = $this->project_id;

    $qry .= " ORDER BY p.uploaded DESC ";

    $result = $this->db->squery($qry, $qparam)->fetchAll(\PDO::FETCH_ASSOC);

    if($result) {
      foreach ($result as $r) {
        $documents[] = new Document($r['ID'], $this->arg);
      }
    }

    return $documents;
  }
  public function __destruct()
  {
    $this->arg = null;
    $this->db	= null;
		$this->settings = null;
		$this->smarty	= null;
		$this->lang = null;
		$this->document = null;
  }

}
?>

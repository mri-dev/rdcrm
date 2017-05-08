<?php
namespace ProjectManager;

use ExceptionManager\RedirectException;

class Document
{
  private $db = null;
  private $settings = array();
	private $smarty = null;
	private $lang = array();
  private $arg = array();
  public $document = null;
  public $document_id = false;

  public function __construct( $document_id = null, $arg = array() )
  {
    $this->arg = $arg;
    $this->db	= $arg['db'];
		$this->settings = $arg[settings];
		$this->smarty	= $arg[smarty];
		$this->lang = $arg[lang];
    $this->document_id = $document_id;

    $this->load();

    return $this;
  }

  private function load()
  {
    $qparam = array();
    $qry = "SELECT
      p.*
    FROM ". \ProjectManager\Documents::DBTABLE." as p
    WHERE 1=1 ";

    if(is_numeric($this->document_id)) {
      $qry .= " and p.ID = :id";
      $qparam['id'] = $this->document_id;
    } else {
      $qry .= " and p.hashkey = :hashkey";
      $qparam['hashkey'] = $this->document_id;
    }


    $result = $this->db->squery($qry, $qparam)->fetch(\PDO::FETCH_ASSOC);

    $this->document = $result;

    return $this;
  }

  public function creator( $post )
  {
    extract($post);
    $return_id = false;

    $vars = array();

    $vars['name'] = $name;
    $vars['file_path'] = $file_path;
    $vars['projectid'] = (int)$projectid;
    $vars['hashkey'] = md5(microtime());


    // Exceptions
    if (empty($vars['name'])) {
      $this->error( 'A dokumentumot kötelezően el kell nevezni.' );
    }
    if (empty($vars['file_path'])) {
      $this->error( 'A dokumentum elérhetőségét válassza ki.' );
    }

    if (isset($id) && !empty($id))
    {
      // Edit
      $return_id = $id;

      $this->db->update(
        \ProjectManager\Documents::DBTABLE,
        $vars,
        sprintf("ID = %d", $id)
      );
    } else {
      // Create
      $insert_id = $this->db->insert(
        \ProjectManager\Documents::DBTABLE,
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

    $this->db->delete(\ProjectManager\Documents::DBTABLE, array("ID" => $this->ID()));
  }

  public function Uploaded()
  {
    return $this->document['uploaded'];
  }

  public function Hashkey()
  {
    return $this->document['hashkey'];
  }

  public function ID()
  {
    return $this->document['ID'];
  }

  public function Name()
  {
    return $this->document['name'];
  }

  public function ProjectID()
  {
    return $this->document['projectid'];
  }

  public function Extension()
  {
    $ext = pathinfo($this->document['file_path'], PATHINFO_EXTENSION);
    return $ext;
  }

  public function Size()
  {
    $abs_path = $_SERVER['DOCUMENT_ROOT'] . $this->document['file_path'] ;
    $size = filesize($abs_path);
    return $this->formatSizeUnits($size);
  }

  public function PathURL()
  {
    return $this->document['file_path'];
  }

  public function logFileOpen( \PortalManager\User $user = null )
  {
    $userid = ($user) ? $user->getID() : NULL;

    $vars = array(
      'docid' => $this->ID(),
      'ip' => $_SERVER['REMOTE_ADDR'],
      'userid' => $userid
    );
    $insert_id = $this->db->insert(
      \ProjectManager\Documents::DBTABLE_LOG_DOC_CLICK,
      $vars
    );
  }

  private function formatSizeUnits($bytes)
  {
     if ($bytes >= 1073741824)
     {
         $bytes = number_format($bytes / 1073741824, 2) . ' GB';
     }
     elseif ($bytes >= 1048576)
     {
         $bytes = number_format($bytes / 1048576, 2) . ' MB';
     }
     elseif ($bytes >= 1024)
     {
         $bytes = number_format($bytes / 1024, 2) . ' KB';
     }
     elseif ($bytes > 1)
     {
         $bytes = $bytes . ' bytes';
     }
     elseif ($bytes == 1)
     {
         $bytes = $bytes . ' byte';
     }
     else
     {
         $bytes = '0 bytes';
     }

     return $bytes;
   }

  public function data($key=false)
  {
    if(!$key) return $this->document;

    return $this->document[$key];
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
		$this->document = null;
  }

}
?>

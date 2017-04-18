<?
namespace PortalManager;

use PortalManager\Ad;
use PortalManager\Categories;
use ExceptionManager\RedirectException;

class Ads
{
	// Hirdetésnél választható futamidő napokban
	public $adsRunningDays = array( 5, 14, 21, 28 );
	public $edusRunningDays = array( 30 );
	public $active_items 	= 0;
	public $inactive_items 	= 0;
	public $total_items 	= 0;	
	public $total_employers = 0;
	public $filters 		= array();

	public $limit 			= array(0, 25);	
	public $total_pages 	= 1;
	public $current_page 	= 1;
	public $row_items 		= 0;

	private $db = null;
	private $lang = array();
	private $smarty = null;
	private $settings = null;
	private $arg = null;
	private $items = false;
	private $current_item = false;
	private $item_steped_item = false;	
	private $walk_step = 0;
	private $employers = array();
	private $results_in_terulet = array();
	private $results_in_terulet_parents = array();
	private $results_in_mode = array();
	private $results_in_mode_parents = array();
	private $load_applicants 		= false;

	function __construct( $arg = array() )
	{
		$this->db 			= $arg[db];
		$this->arg 			= $arg;
		$this->settings 	= $arg[settings];
		$this->lang 		= $arg[lang];
		$this->smarty 		= $arg[smarty];
		$this->filters 		= $arg[filters];

		if( isset($arg['load_applicants']) ) {
			$this->load_applicants = $arg['load_applicants'];
		}

		return $this;
	}

	public function getList( $arg = array() )
	{
		$this->items = false;
		$items = array();
		$megye_id = 0;

		$q = "
		SELECT 
		SQL_CALC_FOUND_ROWS 				
							adv.id
		FROM 				".\PortalManager\Ad::TABLE." as adv
		WHERE 				1 = 1 ";

		// WHERE's
		if( $this->arg['listing'] == '1' ) {
			$q .= " and adv.active = 1 ";
		}
		// Csak bizonyos ID-jú munkatípusok listázása
		if( isset($this->filters['list_type']) && !empty($this->filters['list_type']) ) {
			$q .= " and adv.tipus = '".$this->filters['list_type']."' ";						
		}
		// Running - Külső listzás, hirdetés
		if( $this->arg['listing'] == '1' ) {
			$q .= " and now() >= adv.feladas_ido && now() < adv.lejarat_ido ";
		}
		// Employer
		if( isset($arg['employer']) && !empty( $arg['employer'] ) ) {
			$q .= " and fiok_id = ".$arg['employer'];
			$this->arg['employer'] = $arg['employer'];
		}

		// Csak bizonyos ID-jú elemek listázása
		if( isset($this->filters['in_ids']) && !empty($this->filters['in_ids']) ) {
			if( is_array($this->filters['in_ids']) && count($this->filters['in_ids']) > 0 ) {
				$q .= " and adv.id IN (".implode(",",$this->filters['in_ids']).") ";
			}			
		}

		// Csak bizonyos ID-jú munkatípusok listázása
		if( isset($this->filters['jobtype']) && !empty($this->filters['jobtype']) ) {
			if( is_array($this->filters['jobtype']) && count($this->filters['jobtype']) > 0 ) {
				$q .= " and adv.jobtype_id IN (".implode(",",$this->filters['jobtype']).") ";
			}			
		}

		// Csak bizonyos ID-jú munkakörök listázsa és az alkategóiáji
		if( isset($this->filters['jobmode']) && !empty($this->filters['jobmode']) ) {
			$mode_src_in = array();
			$mode_row 	= array();

			if( !is_array($this->filters['jobmode']) ) {

				$mode_src_in[] = $this->filters['jobmode'];

			} else if( is_array($this->filters['jobmode']) ){

				foreach ( $this->filters['jobmode'] as $t ) {
					$mode_src_in[] = $t;
				}

			}

			foreach ( $mode_src_in as $value ) {

				$mode 		= $value;
				$mode_data 	= $this->db->query("SELECT id, szulo_id, deep FROM ".\PortalManager\Categories::TYPE_MUNKAKOROK." WHERE id = '$mode';")->fetch(\PDO::FETCH_ASSOC);

				
				if( !in_array( $mode_data['szulo_id'], $this->results_in_mode_parents) && !is_null($mode_data['szulo_id']) && $mode_data['deep'] > 0 ) {
					$this->results_in_mode_parents[] = $mode_data['szulo_id'];
				} elseif( $mode_data['deep'] == 0 ) {
					$this->results_in_mode_parents[] = $mode_data['id'];
				}
						
				$mode_children = (new Categories( \PortalManager\Categories::TYPE_MUNKAKOROK, $this->arg ))->getChildCategories( $mode_data['id'] );

				if( $mode_children ) {
					$mode_row[] = $mode_data['id'] ;
					foreach ($mode_children as $cd ) {
						if( !in_array($cd['id'], $mode_row)  && !empty($cd['id']) ) {
							$mode_row[] = $cd['id'];
						}
					}
				} else {
					if( !in_array($mode_data['id'], $mode_row) && !empty($mode_data['id']) ) {
						$mode_row[] = $mode_data['id'];
					}
				}

			}

			if( count($mode_row) == 0 ) return $this;

			unset($mode_children);

			$this->results_in_mode = $mode_row;

			$q .= " and jobmode_id IN (".implode(",",$mode_row).") ";

			unset($mode_row);
		}

		// Terület szerinti szűrés
		if( isset($this->filters['terulet']) && !empty($this->filters['terulet']) ) {
			$terulet_src_in = array();
			$terulet_row 	= array();

			if( !is_array($this->filters['terulet']) ) {

				$terulet_src_in[] = \Helper::makeSafeUrl( $this->filters['terulet'], '', false );

			} else if( is_array($this->filters['terulet']) ){

				foreach ( $this->filters['terulet'] as $t ) {
					$terulet_src_in[] = \Helper::makeSafeUrl( $t, '', false );
				}

			}

			foreach ( $terulet_src_in as $value ) {

				$terulet 		= $value;
				$terulet_data 	= $this->db->query("SELECT id, szulo_id, deep FROM ".\PortalManager\Categories::TYPE_TERULETEK." WHERE slug = '$terulet';")->fetch(\PDO::FETCH_ASSOC);

				if( !in_array( $terulet_data['szulo_id'], $this->results_in_terulet_parents) && !is_null($terulet_data['szulo_id']) && $terulet_data['deep'] > 1 ) {
					$this->results_in_terulet_parents[] = $terulet_data['szulo_id'];
				} elseif( $terulet_data['deep'] == 1 ) {
					$this->results_in_terulet_parents[] = $terulet_data['id'];
				}
								
				$terulet_children = (new Categories( \PortalManager\Categories::TYPE_TERULETEK, $this->arg ))->getChildCategories( $terulet_data['id'] );

				if( $terulet_children ) {
					$terulet_row[] = $terulet_data['id'] ;
					foreach ($terulet_children as $cd ) {
						if( !in_array($cd['id'], $terulet_row)  && !empty($cd['id']) ) {
							$terulet_row[] = $cd['id'];
						}
					}
				} else {
					if( !in_array($terulet_data['id'], $terulet_row) && !empty($terulet_data['id']) ) {
						$terulet_row[] = $terulet_data['id'] ;
					}					
				}

			}

			if( count($terulet_row) == 0 ) return $this;

			unset($terulet_children);

			$this->results_in_terulet = $terulet_row;

			$q .= " and terulet_id IN (".implode(",",$terulet_row).") ";

			unset($terulet_row);
			
		}

		// Searching
		if( is_array($this->filters['search']) && count($this->filters['search']) > 0 ) {
			$searched 	= false;
			$search 	= " MATCH( adv.kulcsszavak, adv.cim ) AGAINST(";
		
			foreach( $this->filters['search'] as $searching ) {
				if( $searching != '' ) {
					$searched = true;
					$search .= " '*".trim($searching)."*' ";
				}
			}
						
			$search .=  " IN BOOLEAN MODE ) != 0 ";

			if( $searched ) {
				$q .= " and ".$search;
			} 
		}

		if( count($this->results_in_terulet_parents) == 1 ) {
			$megye_id = (int)$this->results_in_terulet_parents[0];
		}

		// ORDER
		$order = ' adsPriority(adv.id, adv.fiok_id, '.$megye_id.') DESC';

		if( isset($this->filters['orderby']) ){
			$order .= ", ".$this->filters['orderby'];
		} else {
			$order .= ", adv.feladas_ido DESC";
		}

		$q .= " ORDER BY ".$order;


		// Limit
		$limit = $this->getLimit();
		$q .= " LIMIT ".$limit[0].", ".$limit[1];

		//echo $q;

		$qry 				= $this->db->query($q);
		$this->total_items 	= $this->db->query("SELECT FOUND_ROWS();")->fetchColumn();

		$this->total_pages 	= ceil( $this->total_items / $limit[1] );
		

		$qry_data 			= $qry->fetchAll(\PDO::FETCH_ASSOC); 

		if( $qry->rowCount() == 0 ) return $this; 

		$this->arg['results_in_terulet_parents'] = $this->results_in_terulet_parents;
		
		foreach ( $qry_data as $adv ) {
			$this->row_items++;

			$item = new Ad( $adv['id'], $this->arg );

			// Munkáltatók összegyűjtése a hirdetésekből
			if( !array_key_exists( $item->getEmployerID(), $this->employers ) ) {
				$this->employers[$item->getEmployerID()] = array(
					'id' 	=> $item->getEmployerID(),
					'name' 	=> $item->getEmployerName(),
					'url' 	=> $item->getEmployerURL()
				);
				$this->total_employers++;
			}
			

			if( $item->isOver() || !$item->isActive() ) {
				$this->inactive_items++;
			} else {
				$this->active_items++;
			}

			$items[] = $item;
		}

		$this->items = $items;

		return $this;
	}

	public function walk()
	{			
		$this->current_item = $this->items[$this->walk_step];		

		$this->walk_step++;

		if ( $this->walk_step > $this->row_items ) {
			// Reset Walk
			$this->walk_step = 0;
			$this->current_item = false;

			return false;
		}

		return true;	
	}

	private function getLimit()
	{
		$limit = array( 0, 25 );

		if( isset($this->arg['limit']) ) {
			$limit[1] = $this->arg['limit'];
		}

		$page = $this->arg['page'];

		if( isset($page) && $page > 0 ) {

		} else {
			$page = 1;
		}

		$limit[0] = $limit[1] * $page - $limit[1];

		$this->limit[0] = $limit[0] + 1;
		$this->limit[1] = $limit[0] + $limit[1];
		$this->current_page = $page;

		return $limit;
	}

	public function getCurrentTeruletBySlug( $slug )
	{
		$xs = explode("-",$slug);

		if( count($xs) > 1 ) {
			// Megye - város
			return $this->db->query("SELECT neve FROM ".\PortalManager\Categories::TYPE_TERULETEK." WHERE slug = '{$xs[1]}' LIMIT 0,1;")->fetchColumn();
		} else {
			// Megye
			return $this->db->query("SELECT neve FROM ".\PortalManager\Categories::TYPE_TERULETEK." WHERE slug = '{$xs[0]}' LIMIT 0,1;")->fetchColumn();
		}
	}

	public function adv()
	{
		return $this->current_item;
	}

	public function getTeruletInIDS()
	{
		return $this->results_in_terulet;
	}

	public function getJobmodeInIDS()
	{
		return $this->results_in_mode;
	}

	public function getTeruletInParentIDS()
	{
		return $this->results_in_terulet_parents;
	}

	public function getJobmodeInParentIDS()
	{
		return $this->results_in_mode_parents;
	}

	public function getEmployers()
	{
		return $this->employers;
	}

	private function error( $msg )
	{
		throw new RedirectException( $msg, $_POST['form'], $_POST['return'], $_POST['session_path'] );
	}

	public function __destruct()
	{
		$this->db = null;
		$this->arg = null;
		$this->adv_list = false;
		$this->smarty = null;
		$this->items = false;
		$this->current_item = false;
		$this->item_steped_item = false;
		$this->total_items = 0;
		$this->walk_step = 0;
	}
}
?>
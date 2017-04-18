<?
namespace PortalManager;

use PortalManager\User;

class UserList
{
	public $total_items 	= 0;
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
	private $results_in_terulet = array();
	private $results_in_terulet_parents = array();
	
	function __construct( $arg = array() )
	{
		$this->db 			= $arg[db];
		$this->arg 			= $arg;
		$this->settings 	= $arg[settings];
		$this->lang 		= $arg[lang];
		$this->smarty 		= $arg[smarty];
		$this->filters 		= $arg[filters];

		return $this;
	}

	public function getList( $arg = array() )
	{
		$this->items = false;
		$items = array();

		$q = "
		SELECT SQL_CALC_FOUND_ROWS 
							u.ID,
							(SELECT ertek FROM ".\PortalManager\Users::TABLE_DETAILS_NAME." WHERE fiok_id = u.ID and nev = 'kompetenciak') as komp_set
		FROM 				".\PortalManager\Users::TABLE_NAME." as u
		WHERE 				1 = 1 ";

		// WHERE's
		// Running - Külső listzás, hirdetés
		if( $this->filters['admin'] == '1' ) {
			$q .= " and u.engedelyezve = 1  ";
		}

		if( isset( $this->filters['user_group'] ) ) {
			$q .= " and u.user_group = " . $this->filters['user_group'];
		}

		// Adott ID-jú munkáltatók listázása
		if( isset( $this->filters['in_ids'] ) && !empty($this->filters['in_ids']) && count( $this->filters['in_ids'] ) > 0 ) {
			$q .= " and u.ID IN (".implode(",",$this->filters['in_ids'] ).") ";
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

			$q .= " and (SELECT ertek FROM ".\PortalManager\Users::TABLE_DETAILS_NAME." WHERE fiok_id = u.ID and nev = 'city') IN (".implode(",",$terulet_row).") ";

			unset($terulet_row);
			
		}

		// Kompetencia szerinti szűrés
		if( isset($this->filters['kompetenciak']) && !empty($this->filters['kompetenciak']) && !empty($this->filters['kompetenciak'][0])) { 
			$q .= " and ( ";
				$komp_q = "";
				foreach( $this->filters['kompetenciak'] as $komp ) {
					$komp_q .= $komp. " IN (SELECT komp_id FROM ".\PortalManager\Users::TABLE_COMPETENCE_XREF." WHERE fiok_id = u.ID) and ";
				} 
				$komp_q = rtrim( $komp_q, " and " );
				$q .= $komp_q;
			$q .= " ) ";
		}

		// 25 év alatti szűrés
		if( isset($this->filters['max_age']) && !empty($this->filters['max_age']) ) { 
			$sec_to_ageunder = strtotime( '-'.$this->filters['max_age'].' year', time());
			$sec_to_ageunder = date('Y-m-d', $sec_to_ageunder);

			$q .= " and (SELECT ertek FROM ".\PortalManager\Users::TABLE_DETAILS_NAME." WHERE fiok_id = u.ID and nev = 'born') > '$sec_to_ageunder' ";			
		}

		// Független
		if( isset($this->filters['not_married']) && !empty($this->filters['not_married']) ) { 
			$q .= " and (SELECT ertek FROM ".\PortalManager\Users::TABLE_DETAILS_NAME." WHERE fiok_id = u.ID and nev = 'csaladi_allapot') != 'married' ";			
		}

		// Europass
		if( isset($this->filters['europass']) && !empty($this->filters['europass']) ) { 
			$q .= " and (SELECT count(felh_id) FROM ".\PortalManager\Users::TABLE_EUROPASS_XML." WHERE felh_id = u.ID) = 1 ";			
		}

		// Nem szerinti szűrés
		if( isset($this->filters['gender']) && !empty($this->filters['gender']) ) { 
			$q .= " and (SELECT ertek FROM ".\PortalManager\Users::TABLE_DETAILS_NAME." WHERE fiok_id = u.ID and nev = 'gender') = '".$this->filters['gender']."' ";			
		}

		// Csak kiemelt munkáltatók listázása
		if( isset($this->filters['only_premium_users']) ) {
			$q .= " and (SELECT 1 FROM ".\PortalManager\Users::TABLE_PREMIUM." WHERE fiok_id = u.ID and NOW() > mikortol and NOW() < meddig ) = 1 ";
		}

		// Csak hirdetéssel rendelkező munkáltatók
		if( isset($this->filters['only_with_ads']) ) {
			$q .= " and (SELECT count(id) FROM ".\PortalManager\Ad::TABLE." WHERE fiok_id = u.ID and active = '1' and now() > feladas_ido and now() < lejarat_ido ) != 0 ";
		}

		// Csak gyakornoki munkával rendelkező munkáltatók
		if( isset($this->filters['only_with_trainee']) ) {
			$q .= " and (SELECT count(id) FROM ".\PortalManager\Ad::TABLE." WHERE fiok_id = u.ID and active = '1' and tipus = 'trainee' and now() > feladas_ido and now() < lejarat_ido ) != 0 ";
		}

		// Search
		if( is_array($this->filters['search']) && count($this->filters['search']) > 0 ) {
			$searched 	= false;
			$search 	= " MATCH( u.kulcsszavak, u.nev ) AGAINST(";
		
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
				
		// ORDER
		$order = ' u.utoljara_belepett ASC ';

		if( isset($this->filters['orderby']) ){
			$order = $this->filters['orderby'];
		}

		if( isset($this->filters['order']) ) {
			switch( $this->filters['order']['by'] ) {
				case 'age':
					$order = " (SELECT ertek FROM ".\PortalManager\Users::TABLE_DETAILS_NAME." WHERE fiok_id = u.ID and nev = 'born') ".$this->filters['order']['o']; 
				break;
				case 'name':
					$order = 'u.nev '.$this->filters['order']['o']; 
				break;
				case 'ads':
					$order = " (SELECT count(id) FROM ".\PortalManager\Ad::TABLE." WHERE fiok_id = u.ID and active = '1' and now() > feladas_ido and now() < lejarat_ido) ".$this->filters['order']['o']; 
				break;
			}
		}

		// Kiemelt munkáltatók előre
		if( isset($this->filters['premium_user_top']) ) {
			$order = " (SELECT 1 FROM ".\PortalManager\Users::TABLE_PREMIUM." WHERE fiok_id = u.ID and NOW() > mikortol and NOW() < meddig ) DESC, " . $order;
		}

		$q .= " ORDER BY ".$order;

		// Limit
		$limit = $this->getLimit();
		$q .= " LIMIT ".$limit[0].", ".$limit[1];

		//echo $q;

		$qry 				= $this->db->query($q);
		$this->total_items 	= $this->db->query("SELECT FOUND_ROWS();")->fetchColumn();
		$this->total_pages 	= ceil( $this->total_items / $limit[1] );

		$qry_data 	= $qry->fetchAll(\PDO::FETCH_ASSOC); 

		if( $qry->rowCount() == 0 ) return $this; 
		
		foreach ( $qry_data as $u ) {
			$this->row_items++;
			$item = new User( $u['ID'], $this->arg );

			$items[] = $item;
		}

		$this->items = $items;

		return $this;
	}

	private function getLimit()
	{
		$limit = array( 0, 999999 );

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

	public function getTeruletInParentIDS()
	{
		return $this->results_in_terulet_parents;
	}

	public function getTeruletInIDS()
	{
		return $this->results_in_terulet;
	}

	public function get()
	{
		return $this->current_item;
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
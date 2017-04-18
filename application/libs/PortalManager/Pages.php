<?
namespace PortalManager;

use PortalManager\Formater;
use PortalManager\Template;
use Applications\Tabledata;
use ExceptionManager\RedirectException;

/**
* class Pages
* @package PortalManager
* @version v1.0
*/
class Pages
{
	private $db = null;
	public $tree = false;
	private $current_item = false;
	private $current_get_item = false;
	private $tree_steped_item = false;
	private $tree_items = 0;
	private $walk_step = 0;
	private $selected_page_id = false;
	private $is_admin = false;

	const DB_TABLE = 'oldalak';

	function __construct( $page_id = false, $arg = array() )
	{
		$this->db = $arg[db];
		
		if ( $page_id ) {
			$this->selected_page_id = $page_id;
		}
	}

	public function get( $page_id_or_slug )
	{
		$data = array();
		$qry = "
			SELECT 				*
			FROM 				".self::DB_TABLE."
		";
		
		if (is_numeric($page_id_or_slug)) {
			$qry .= " WHERE ID = ".$page_id_or_slug;
		}else {
			$qry .= " WHERE eleres = '".$page_id_or_slug."'";
		}


		if ( !$this->is_admin ) {
			$qry .= " and lathato = 1 ";
		}

		$qry = $this->db->query($qry);

		$this->current_get_item = $qry->fetch(\PDO::FETCH_ASSOC);

		return $this;
	}

	public function add( $data )
	{
		$deep 		= 0;

		$cim 	= ($data['cim']) ?: false;		
		$parent = ($data['parent']) ?: false;
		$eleres = ($data['eleres']) ?: false;
		$szoveg = ($data['szoveg']) ?: NULL;	
		$keys 	= ($data['kulcsszavak']) ?: NULL;	
		$lathato= ($data['lathato'] == 'on') ? 1 : 0;
		$gyujto	= ($data['gyujto'] == 'on') ? 1 : 0;

		
		if ($parent) {
			$xparent = explode('_',$parent);
			$deep = $xparent[1]+1;
			$parent = $xparent[0];
		} else {
			$parent = NULL;
		}

		if (!$cim) { $this->error("Kérjük, hogy adja meg az Oldal címét!"); } 


		if (!$eleres) {
			$eleres = $this->checkEleres( $cim );
		} else {
			$eleres = \PortalManager\Formater::makeSafeUrl($eleres,'');
		}

		$this->db->insert(
			self::DB_TABLE,
			array(
				'cim' 		=> addslashes($cim),
				'szulo_id' 	=> $parent,
				'eleres' 	=> $eleres,
				'szoveg' 	=> addslashes($szoveg),
				'deep' 		=> $deep,
				'idopont' 	=> NOW,
				'lathato' 	=> $lathato,		
				'gyujto' 	=> $gyujto,
				'kulcsszavak'=> addslashes($keys),
				'sorrend' 	=> $data['sorrend']
			)
		);
	}

	public function save( $data )
	{
		$deep 		= 0;

		$cim 	= ($data['cim']) 	?: false;		
		$parent = ($data['parent']) ?: false;
		$eleres = ($data['eleres']) ?: false;
		$szoveg = ($data['szoveg']) ?: NULL;
		$keys 	= ($data['kulcsszavak']) ?: NULL;
		$lathato= ($data['lathato'])? 1 : 0;
		$gyujto	= ($data['gyujto'] == 'on') ? 1 : 0;
		
		if ($parent) {
			$xparent = explode('_',$parent);
			$deep = $xparent[1]+1;
			$parent = $xparent[0];
		} else {
			$parent = NULL;
		}

		if (!$cim) { $this->error("Kérjük, hogy adja meg az <strong>Oldal címét</strong>!"); } 


		if (!$eleres) {
			$eleres = $this->checkEleres( $cim );
		} else {
			$eleres = \PortalManager\Formater::makeSafeUrl($eleres,'');
		}

		$this->db->update(
			self::DB_TABLE,
			array(
				'cim' 		=> addslashes($cim),
				'szulo_id' 	=> $parent,
				'eleres' 	=> $eleres,
				'szoveg' 	=> addslashes($szoveg),
				'deep' 		=> $deep,
				'idopont' 	=> NOW,
				'lathato' 	=> $lathato,
				'gyujto' 	=> $gyujto,
				'kulcsszavak'=> addslashes($keys),
				'sorrend' 	=> $data['sorrend']
			),
			sprintf("ID = %d", $this->selected_page_id)
		);
	}

	private function checkEleres( $text )
	{
		$text = Formater::makeSafeUrl($text,'');

		$qry = $this->db->query(sprintf("
			SELECT 		eleres 
			FROM 		".self::DB_TABLE." 
			WHERE 		eleres = '%s' or 
						eleres like '%s-_' or 
						eleres like '%s-__' 
			ORDER BY 	eleres DESC 
			LIMIT 		0,1", trim($text), trim($text), trim($text) ));
		$last_text = $qry->fetch(\PDO::FETCH_COLUMN);
		
		if( $qry->rowCount() > 0 ) {

			$last_int = (int)end(explode("-",$last_text));

			if( $last_int != 0 ){
				$last_text = str_replace('-'.$last_int, '-'.($last_int+1) , $last_text);
			} else {
				$last_text .= '-1';
			}			
		} else {
			$last_text = $text;
		}

		return $last_text;
	}

	public function delete( $id = false )
	{
		$del_id = ($id) ?: $this->selected_page_id;

		if ( !$del_id ) return false;

		$this->db->query(sprintf("DELETE FROM ".self::DB_TABLE." WHERE ID = %d", $del_id));
	}

	/**
	 * Oldal fa kilistázása
	 * @param int $top_page_id Felső oldal ID meghatározása, nem kötelező. Ha nincs megadva, akkor
	 * az összes oldal fa listázódik.
	 * @return array Oldalak
	 */
	public function getTree( $top_page_id = false, $arg = array() )
	{
		$tree 		= array();

		// Legfelső színtű oldalak
		$qry = "
			SELECT 			* 
			FROM 			".self::DB_TABLE." 
			WHERE 			ID IS NOT NULL ";

		if ( !$this->is_admin ) {
			$qry .= " and lathato = 1 ";
		}

		
		if ( !$top_page_id ) {
			$qry .= " and szulo_id IS NULL ";
		} else {
			$qry .= " and szulo_id = ".$top_page_id;
		}

		$qry .= "
			ORDER BY 		sorrend ASC;";

		$top_page_qry 	= $this->db->query($qry);
		$top_page_data 	= $top_page_qry->fetchAll(\PDO::FETCH_ASSOC); 

		if( $top_page_qry->rowCount() == 0 ) return $this; 
		
		foreach ( $top_page_data as $top_page ) {
			$this->tree_items++;
			$this->tree_steped_item[] = $top_page;

			// Aloldalak betöltése
			$top_page['child'] = $this->getChildItems($top_page['ID']);
			
			$tree[] = $top_page;
		}

		$this->tree = $tree;

		return $this;
	}

	public function has_page()
	{
		return ($this->tree_items === 0) ? false : true;
	}

	/**
	 * Végigjárja az összes oldalt, amit betöltöttünk a getTree() függvény segítségével. while php függvénnyel
	 * járjuk végig. A while függvényen belül használjuk a the_page() objektum függvényt, ami az aktuális oldal 
	 * adataiat tartalmazza tömbbe sorolva.
	 * @return boolean
	 */
	public function walk()
	{	
		if( !$this->tree_steped_item ) return false;

		$this->current_item = $this->tree_steped_item[$this->walk_step];	

		$this->walk_step++;

		if ( $this->walk_step > $this->tree_items ) {
			// Reset Walk
			$this->walk_step = 0;
			$this->current_item = false;

			return false;
		}

		return true;	
	}

	public function getWalkInfo()
	{
		return array(
			'walk_step' => $this->walk_step,
			'tree_steped_item' => $this->tree_steped_item,
			'tree_items' => $this->tree_items,
			'current_item' => $this->current_item,
		);
	}

	/**
	 * A walk() fgv-en belül visszakaphatjuk az aktuális oldal elem adatait tömbbe tárolva.
	 * @return array 
	 */
	public function the_page()
	{
		return $this->current_item;
	}

	/**
	 * Oldal al-elemeinek listázása
	 * @param  int $parent_id 	Szülő oldal ID
	 * @return array 			Szülő oldal al-elemei
	 */
	private function getChildItems( $parent_id )
	{
		$tree = array();

		// Gyerek oldalak
		$child_page_qry 	= $this->db->query( sprintf("
			SELECT 			* 
			FROM 			".self::DB_TABLE." 
			WHERE 			szulo_id = %d
			ORDER BY 		sorrend ASC;", $parent_id));
		$child_page_data	= $child_page_qry->fetchAll(\PDO::FETCH_ASSOC); 

		if( $child_page_qry->rowCount() == 0 ) return false; 
		foreach ( $child_page_data as $child_page ) {
			$this->tree_items++;

			$this->tree_steped_item[] = $child_page;

			$child_page['child'] = $this->getChildItems($child_page['ID']);
			
			$tree[] = $child_page;
		}

		return $tree;
	}

	public function setAdmin( $flag )
	{
		$this->is_admin = $flag;
	}

	public function getTopParentId( $id )
	{
		$got_top = false;
		$top_id = false;

		if ( !$id ) {
			return false;
		}

		$step_id = $id;

		while ( !$got_top ) {
			$q = $this->db->query("SELECT szulo_id FROM ".self::DB_TABLE." WHERE ID = ".$step_id );
			$qq = $q->fetch(\PDO::FETCH_ASSOC);

			if ( is_null($qq['szulo_id']) ) {
				$got_top = true;
				$top_id = $step_id;
			} else {
				$step_id = $qq['szulo_id'];
			}
		}

		return $top_id;
	}

	/*===============================
	=            GETTERS            =
	===============================*/
	public function getParentId()
	{
		return $this->current_get_item['szulo_id'];
	}
	public function getParentKey()
	{
		return $this->current_get_item['szulo_id'].'_'.($this->current_get_item['deep']-1);
	}
	public function getOrderIndex()
	{
		return $this->current_get_item['sorrend'];
	}
	public function getDeepIndex()
	{
		return $this->current_get_item['deep'];
	}
	public function getId()
	{
		return $this->current_get_item['ID'];
	}

	public function getTitle()
	{
		return $this->current_get_item['cim'];
	}
	public function getKeywords( $arrayed = false )
	{
		if ( !$arrayed ) {
			return $this->current_get_item['kulcsszavak'];
		} else {
			return explode( " ", trim($this->current_get_item['kulcsszavak']) );
		}
		
	}
	public function getUrl()
	{
		return $this->current_get_item['eleres'];
	}
	public function getHtmlContent()
	{
		return $this->current_get_item['szoveg'];
	}
	public function getVisibility()
	{
		return ($this->current_get_item['lathato'] == 1 ? true : false);
	}

	public function isContainer()
	{
		return ($this->current_get_item['gyujto'] == 1 ? true : false);
	}
	/*-----  End of GETTERS  ------*/

	private function error( $msg )
	{
		throw new RedirectException( $msg, $_POST['form'], $_POST['return'], $_POST['session_path'] );
	}

}
?>
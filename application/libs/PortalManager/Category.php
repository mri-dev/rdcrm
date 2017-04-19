<?
namespace PortalManager;

use ExceptionManager\RedirectException;

class Category
{
	private $db = null;
	private $id = false;
	private $cat_data = false;
	private $category_table = false;
	private $settings = null;

	function __construct( $category_table = false, $category_id = false, $arg = array() )
	{
		$this->db = $arg[db];
		$this->id = $category_id;
		$this->settings = $arg[settings];

		$this->category_table = $category_table;

		if( !$this->category_table ) {
			$this->kill( 'ERROR'.__CLASS__.'Hiányzik a kategória táblája. Kérjük adja meg!' );
		}
		$this->get();

		return $this;
	}

	/**
	 * Kategória adatainak lekérése
	 * @return void
	 */
	private function get()
	{
		$cat_qry 	= $this->db->query( sprintf("
			SELECT 			* 
			FROM 			%s
			WHERE 			id = %d;", $this->category_table, $this->id));
		$cat_data = $cat_qry->fetch(\PDO::FETCH_ASSOC); 
		$this->cat_data = $cat_data;
	}

	/**
	 * Kategória létrehzás
	 * @param array $data új kategória létrehozásához szükséges adatok
	 * @return int inserted ID
	 */
	public function add( $data = array() )
	{
		$deep = 0;
		$relations 	= 0;
		
		$name 		= ($data['neve']) 		?: false;
		$sort 		= ($data['sorrend']) 	?: 100;
		$parent 	= ($data['szulo_id']) 	?: NULL;

		if ($parent) {
			$xparent 	= explode('_',$parent);
			$parent 	= (int)$xparent[0];
			$deep 		= (int)$xparent[1] + 1;
		}

		if ( !$name ) {
			$this->error( "Kérjük, hogy adja meg az elem elnevezését!" );			
		}

		$this->db->insert(
			$this->category_table,
			array(
				'neve' 		=> $name,
				'szulo_id' 	=> $parent,
				'sorrend' 	=> $sort,
				'deep' 		=> $deep,
				'slug' 		=> \Helper::makeSafeUrl( $name, '', false)
			)
		);
		
		
		$id = $this->db->lastInsertId();

		return $id;
		/*
		$relations = '';
		
		if( $this->category_table == \PortalManager\Categories::TYPE_TERULETEK ) {
			$relations .= $this->settings['country_id'];
		}

		$relations .= '_'.$parent;
		$relations .= '_'.$id;

		$relations = trim($relations, '_');

		$this->db->update(
			$this->category_table,
			array( 
				'relations' => $relations
			),
			"ID = ".$id
		);*/
	}

	/**
	 * Beszúrandó kategória ellenőrzése, hogy létezik-e már!
	 * @param  array $data( neve, szulo_id) kategória adatok
	 * @return boolean | int - elem id
	 */
	public function checkExists( $data  )
	{
		$deep 		= 0;
		$name 		= ( $data['neve'] ) 		? $data['neve'] 	: false;
		$parent 	= ( $data['szulo_id'] ) 	? $data['szulo_id'] : NULL;

		if ( $parent ) {
			$xparent 	= explode('_',$parent);
			$parent 	= (int)$xparent[0];
			$deep 		= (int)$xparent[1] + 1;
		} else {
			$parent = 'NULL';
		}

		$q = "SELECT id FROM ".$this->category_table." WHERE szulo_id = $parent and deep = $deep and neve = '$name';";

		$check = $this->db->query($q);
		

		if( $check->rowCount() != 0 ){
			$id = $check->fetchColumn();
			return $id;
		} 

		return false;
	}

	/**
	 * Kategória keresés adatok alapján
	 * @param  array $data
	 * @return array|boolean 
	 */
	public function checkData( $data )
	{
		$details = array();

		if( empty( $data ) ) return false;

		$q = "SELECT * FROM ".$this->category_table." WHERE 1 = 1 ";

		foreach ($data as $key => $value) {
			$q .= " and ".$key. " = '".addslashes($value)."' ";
		}

		$qry = $this->db->query( $q );

		if( $qry->rowCount() == 0) return false;

		$details = $qry->fetch(\PDO::FETCH_ASSOC);

		return $details;
	}

	/**
	 * Aktuális kategória adatainak szerkesztése / mentése
	 * @param  array $db_fields új kategória adatok
	 * @return void            
	 */
	public function edit( $db_fields )
	{
		$deep = 0;
		$relations 	= '';

		$parent = ($db_fields['szulo_id']) ?: NULL;

		if ($parent) {
			$xparent 	= explode('_',$parent);
			$parent 	= (int)$xparent[0];
			$deep 		= (int)$xparent[1] + 1;
		}

		if( empty( $db_fields['neve'] ) )  {
			$this->error( "Elem nevének megadása kötelező!" );
		}



		$db_fields['sorrend'] 	= ( !empty($db_fields['sorrend']) ) ? (int)$db_fields['sorrend'] : 100;
		$db_fields['szulo_id'] 	= $parent;
		$db_fields['deep'] 		= $deep;
		$db_fields['slug'] 		= \Helper::makeSafeUrl($db_fields['neve'],'',false);
		//$db_fields['relations']	= $relations;

		$this->db->update(
			$this->category_table,
			$db_fields,
			"id = ".$this->id
		);
	}

	/**
	 * Aktuális kategória törlése
	 * @return void
	 */
	public function delete()
	{
		$this->db->query(sprintf("DELETE FROM %s WHERE ID = %d", $this->category_table, $this->id));
	}

	private function error( $msg )
	{
		throw new RedirectException( $msg, $_POST['form'], $_POST['return'], $_POST['session_path'] );
	}

	private function kill( $msg = '' )
	{
		throw new \Exception( $msg );
	}

	/*===============================
	=            GETTERS            =
	===============================*/
	public function getName()
	{
		return $this->cat_data['neve'];
	}
	
	public function getSortNumber()
	{
		return $this->cat_data['sorrend'];
	}
	public function getParentKey()
	{
		return $this->cat_data['szulo_id'].'_'.($this->cat_data['deep']-1);
	}
	public function getParentId()
	{
		return $this->cat_data['szulo_id'];
	}
	public function getDeep()
	{
		return $this->cat_data['deep'];
	}
	public function getId()
	{
		return $this->cat_data['id'];
	}
	/*-----  End of GETTERS  ------*/

	public function __destruct()
	{
		$this->db = null;
		$this->cat_data = false;
		$this->settings = null;
	}
		
}
?>
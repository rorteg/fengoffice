<?php 

  
  /**
  * ProjectContacts class
  *
  * @author Carlos Palma <chonwil@gmail.com>
  */
  abstract class BaseProjectContacts extends ProjectDataObjects {
  
    /**
    * Column name => Column type map
    *
    * @var array
    * @static
    */
    static private $columns = array(
    'id' => DATA_TYPE_INTEGER, 
    'project_id' => DATA_TYPE_INTEGER, 
    'contact_id' => DATA_TYPE_INTEGER, 
    'role' => DATA_TYPE_STRING, 
);
  
    /**
    * Construct
    *
    * @return BaseProjectContacts 
    */
    function __construct() {
    	Hook::fire('object_definition', 'ProjectContact', self::$columns);
      parent::__construct('ProjectContact', 'project_contacts', true);
    } // __construct
    
    // -------------------------------------------------------
    //  Description methods
    // -------------------------------------------------------
    
    /**
    * Return array of object columns
    *
    * @access public
    * @param void
    * @return array
    */
    function getColumns() {
      return array_keys(self::$columns);
    } // getColumns
    
    /**
    * Return column type
    *
    * @access public
    * @param string $column_name
    * @return string
    */
    function getColumnType($column_name) {
      if(isset(self::$columns[$column_name])) {
        return self::$columns[$column_name];
      } else {
        return DATA_TYPE_STRING;
      } // if
    } // getColumnType
    
    /**
    * Return array of PK columns. If only one column is PK returns its name as string
    *
    * @access public
    * @param void
    * @return array or string
    */
    function getPkColumns() {
      return 'id';
    } // getPkColumns
    
    /**
    * Return name of first auto_incremenent column if it exists
    *
    * @access public
    * @param void
    * @return string
    */
    function getAutoIncrementColumn() {
      return 'id';
    } // getAutoIncrementColumn
    
    // -------------------------------------------------------
    //  Finders
    // -------------------------------------------------------
    
    /**
    * Do a SELECT query over database with specified arguments
    *
    * @access public
    * @param array $arguments Array of query arguments. Fields:
    * 
    *  - one - select first row
    *  - conditions - additional conditions
    *  - order - order by string
    *  - offset - limit offset, valid only if limit is present
    *  - limit
    * 
    * @return one or ProjectContacts objects
    * @throws DBQueryError
    */
    function find($arguments = null) {
      if(isset($this) && instance_of($this, 'ProjectContacts')) {
        return parent::find($arguments);
      } else {
        return ProjectContacts::instance()->find($arguments);
        //$instance =& ProjectContacts::instance();
        //return $instance->find($arguments);
      } // if
    } // find
    
    /**
    * Find all records
    *
    * @access public
    * @param array $arguments
    * @return one or ProjectContacts objects
    */
    function findAll($arguments = null) {
      if(isset($this) && instance_of($this, 'ProjectContacts')) {
        return parent::findAll($arguments);
      } else {
        return ProjectContacts::instance()->findAll($arguments);
        //$instance =& ProjectContacts::instance();
        //return $instance->findAll($arguments);
      } // if
    } // findAll
    
    /**
    * Find one specific record
    *
    * @access public
    * @param array $arguments
    * @return ProjectContact 
    */
    function findOne($arguments = null) {
      if(isset($this) && instance_of($this, 'ProjectContacts')) {
        return parent::findOne($arguments);
      } else {
        return ProjectContacts::instance()->findOne($arguments);
        //$instance =& ProjectContacts::instance();
        //return $instance->findOne($arguments);
      } // if
    } // findOne
    
    /**
    * Return object by its PK value
    *
    * @access public
    * @param mixed $id
    * @param boolean $force_reload If true cache will be skipped and data will be loaded from database
    * @return ProjectContact 
    */
    function findById($id, $force_reload = false) {
      if(isset($this) && instance_of($this, 'ProjectContacts')) {
        return parent::findById($id, $force_reload);
      } else {
        return ProjectContacts::instance()->findById($id, $force_reload);
        //$instance =& ProjectContacts::instance();
        //return $instance->findById($id, $force_reload);
      } // if
    } // findById
    
    /**
    * Return number of rows in this table
    *
    * @access public
    * @param string $conditions Query conditions
    * @return integer
    */
    function count($condition = null) {
      if(isset($this) && instance_of($this, 'ProjectContacts')) {
        return parent::count($condition);
      } else {
        return ProjectContacts::instance()->count($condition);
        //$instance =& ProjectContacts::instance();
        //return $instance->count($condition);
      } // if
    } // count
    
    /**
    * Delete rows that match specific conditions. If $conditions is NULL all rows from table will be deleted
    *
    * @access public
    * @param string $conditions Query conditions
    * @return boolean
    */
    function delete($condition = null) {
      if(isset($this) && instance_of($this, 'ProjectContacts')) {
        return parent::delete($condition);
      } else {
        return ProjectContacts::instance()->delete($condition);
        //$instance =& ProjectContacts::instance();
        //return $instance->delete($condition);
      } // if
    } // delete
    
    /**
    * This function will return paginated result. Result is an array where first element is 
    * array of returned object and second populated pagination object that can be used for 
    * obtaining and rendering pagination data using various helpers.
    * 
    * Items and pagination array vars are indexed with 0 for items and 1 for pagination
    * because you can't use associative indexing with list() construct
    *
    * @access public
    * @param array $arguments Query argumens (@see find()) Limit and offset are ignored!
    * @param integer $items_per_page Number of items per page
    * @param integer $current_page Current page number
    * @return array
    */
    function paginate($arguments = null, $items_per_page = 10, $current_page = 1) {
      if(isset($this) && instance_of($this, 'ProjectContacts')) {
        return parent::paginate($arguments, $items_per_page, $current_page);
      } else {
        return ProjectContacts::instance()->paginate($arguments, $items_per_page, $current_page);
        //$instance =& ProjectContacts::instance();
        //return $instance->paginate($arguments, $items_per_page, $current_page);
      } // if
    } // paginate
    
    function paginateOrderByName($arguments = null, $items_per_page = 10, $current_page = 1)
    {
      if(!is_array($arguments)) $arguments = array();
      $conditions = array_var($arguments, 'conditions');
      $contactsTableName = Contacts::instance()->getTableName(true);
      	  
      $pagination = new DataPagination($this->count($conditions), $items_per_page, $current_page);
      
      if (strlen($conditions) > 0)
          $conditions .= " AND ".$this->getTableName(true).".contact_id = $contactsTableName.id";
      else
      	  $conditions = $this->getTableName(true).".contact_id = $contactsTableName.id";
      
      $offset = $pagination->getLimitStart();
      $limit = $pagination->getItemsPerPage();
      
      $sql = "SELECT ". $this->getTableName(true) .".* FROM " . $this->getTableName(true) . ", $contactsTableName" . 
      " WHERE $conditions ORDER BY UPPER(lastname) ASC, UPPER(firstname) ASC LIMIT $offset, $limit";
      
      // Run!
      $rows = DB::executeAll($sql);
      
      if(!is_array($rows) || (count($rows) < 1)) $items =  null;

      $objects = array();
      foreach($rows as $row) {
      	$object = $this->loadFromRow($row);
      	if(instance_of($object, $this->getItemClass())) $objects[] = $object;
      } // foreach
      $items = count($objects) ? $objects : null;

      return array($items, $pagination);
    }
    
    /**
    * Return manager instance
    *
    * @return ProjectContacts 
    */
    function instance() {
      static $instance;
      if(!instance_of($instance, 'ProjectContacts')) {
        $instance = new ProjectContacts();
      } // if
      return $instance;
    } // instance
  
  } // ProjectContacts 

?>
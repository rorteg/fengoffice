<?php
  /**
  * Contacts class
  *
  * @author Carlos Palma <chonwil@gmail.com>
  */
  abstract class BaseContacts extends ProjectDataObjects {
  
    /**
    * Column name => Column type map
    *
    * @var array
    * @static
    */
    static private $columns = array(
     'id' => DATA_TYPE_INTEGER,
     'firstname' => DATA_TYPE_STRING,
     'lastname' => DATA_TYPE_STRING,
     'middlename' => DATA_TYPE_STRING,
     'department' => DATA_TYPE_STRING,
     'job_title' => DATA_TYPE_STRING,
     'company_id' => DATA_TYPE_INTEGER,
     'email' => DATA_TYPE_STRING,
     'email2' => DATA_TYPE_STRING,
     'email3' => DATA_TYPE_STRING,
    
     'w_web_page' => DATA_TYPE_STRING,
     'w_address' => DATA_TYPE_STRING,
     'w_city' => DATA_TYPE_STRING,
     'w_state' => DATA_TYPE_STRING,
     'w_zipcode' => DATA_TYPE_STRING,
     'w_country' => DATA_TYPE_STRING,
     'w_phone_number' => DATA_TYPE_STRING,
     'w_phone_number2' => DATA_TYPE_STRING,
     'w_fax_number' => DATA_TYPE_STRING,
     'w_assistant_number' => DATA_TYPE_STRING,
     'w_callback_number' => DATA_TYPE_STRING,
    
     'h_web_page' => DATA_TYPE_STRING,
     'h_address' => DATA_TYPE_STRING,
     'h_city' => DATA_TYPE_STRING,
     'h_state' => DATA_TYPE_STRING,
     'h_zipcode' => DATA_TYPE_STRING,
     'h_country' => DATA_TYPE_STRING,
     'h_phone_number' => DATA_TYPE_STRING,
     'h_phone_number2' => DATA_TYPE_STRING,
     'h_fax_number' => DATA_TYPE_STRING,
     'h_mobile_number' => DATA_TYPE_STRING,
     'h_pager_number' => DATA_TYPE_STRING,
    
     'o_web_page' => DATA_TYPE_STRING,
     'o_address' => DATA_TYPE_STRING,
     'o_city' => DATA_TYPE_STRING,
     'o_state' => DATA_TYPE_STRING,
     'o_zipcode' => DATA_TYPE_STRING,
     'o_country' => DATA_TYPE_STRING,
     'o_phone_number' => DATA_TYPE_STRING,
     'o_phone_number2' => DATA_TYPE_STRING,
     'o_fax_number' => DATA_TYPE_STRING,
     'o_birthday' => DATA_TYPE_DATETIME,
     'picture_file' => DATA_TYPE_STRING,
     'timezone' => DATA_TYPE_FLOAT, 
     'notes' => DATA_TYPE_STRING,
     'user_id' => DATA_TYPE_INTEGER,
     'is_private' => DATA_TYPE_BOOLEAN,
     'created_on' => DATA_TYPE_DATETIME,
     'created_by_id' => DATA_TYPE_INTEGER,
     'updated_on' => DATA_TYPE_DATETIME,
     'updated_by_id' => DATA_TYPE_INTEGER,
     'trashed_on' => DATA_TYPE_DATETIME,
     'trashed_by_id' => DATA_TYPE_INTEGER,
     'archived_on' => DATA_TYPE_DATETIME,
     'archived_by_id' => DATA_TYPE_INTEGER,
    );
  
    /**
    * Construct
    *
    * @return BaseContacts 
    */
    function __construct() {
    	Hook::fire('object_definition', 'Contact', self::$columns);
      parent::__construct('Contact', 'contacts', true);
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
    
    /**
    * Return system columns
    *
    * @access public
    * @param void
    * @return array
    */
    function getSystemColumns() {
      return array_merge(parent::getSystemColumns(), array(
      	'company_id', 'picture_file', 'timezone', 'user_id')
      );
    } // getSystemColumns
    
    /**
    * Return external columns
    *
    * @access public
    * @param void
    * @return array
    */
    function getExternalColumns() {
      return array_merge(parent::getExternalColumns(), array('company_id'));
    } // getExternalColumns
    
    /**
    * Return report object title columns
    *
    * @access public
    * @param void
    * @return array
    */
    function getReportObjectTitleColumns() {
      return array('firstname', 'middlename', 'lastname');
    } // getReportObjectTitleColumns
    
    /**
    * Return report object title
    *
    * @access public
    * @param void
    * @return string
    */
    function getReportObjectTitle($values) {
    	$firstname = isset($values['firstname']) ? $values['firstname'] : ''; 
    	$middlename = isset($values['middlename']) ? $values['middlename'] : '';;
    	$lastname = isset($values['lastname']) ? $values['lastname'] : '';;
    	return $firstname.' '.$middlename.' '.$lastname;
    } // getReportObjectTitle
    
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
    * @return one or Contacts objects
    * @throws DBQueryError
    */
    function find($arguments = null) {
      if(isset($this) && instance_of($this, 'Contacts')) {
        return parent::find($arguments);
      } else {
        return Contacts::instance()->find($arguments);
      } // if
    } // find
    
    /**
    * Find all records
    *
    * @access public
    * @param array $arguments
    * @return one or Contacts objects
    */
    function findAll($arguments = null) {
      if(isset($this) && instance_of($this, 'Contacts')) {
        return parent::findAll($arguments);
      } else {
        return Contacts::instance()->findAll($arguments);
      } // if
    } // findAll
    
    /**
    * Find one specific record
    *
    * @access public
    * @param array $arguments
    * @return Contact 
    */
    function findOne($arguments = null) {
      if(isset($this) && instance_of($this, 'Contacts')) {
        return parent::findOne($arguments);
      } else {
        return Contacts::instance()->findOne($arguments);
      } // if
    } // findOne
    
    /**
    * Return object by its PK value
    *
    * @access public
    * @param mixed $id
    * @param boolean $force_reload If true cache will be skipped and data will be loaded from database
    * @return Contact 
    */
    function findById($id, $force_reload = false) {
      if(isset($this) && instance_of($this, 'Contacts')) {
        return parent::findById($id, $force_reload);
      } else {
        return Contacts::instance()->findById($id, $force_reload);
        //$instance =& Contacts::instance();
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
      if(isset($this) && instance_of($this, 'Contacts')) {
        return parent::count($condition);
      } else {
        return Contacts::instance()->count($condition);
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
      if(isset($this) && instance_of($this, 'Contacts')) {
        return parent::delete($condition);
      } else {
        return Contacts::instance()->delete($condition);
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
      if(isset($this) && instance_of($this, 'Contacts')) {
        return parent::paginate($arguments, $items_per_page, $current_page);
      } else {
        return Contacts::instance()->paginate($arguments, $items_per_page, $current_page);
      } // if
    } // paginate
    
    
    /**
    * Return manager instance
    *
    * @return Contacts 
    */
    function instance() {
      static $instance;
      if(!instance_of($instance, 'Contacts')) {
        $instance = new Contacts();
      } // if
      return $instance;
    } // instance
  
  } // Contacts 
?>
<?php

  /**
  * BaseContact class
  *
  * @author Carlos Palma <chonwil@gmail.com>
  */
  abstract class BaseContact extends ProjectDataObject {
  
  	protected $objectTypeIdentifier = 'ct';
  	
  	protected $is_commentable = true;
  	
    // -------------------------------------------------------
    //  Access methods
    // -------------------------------------------------------
  
	
    /**
    * Return value of 'id' field
    *
    * @access public
    * @param void
    * @return integer 
    */
    function getId() {
      return $this->getColumnValue('id');
    } // getId()
    
    /**
    * Set value of 'id' field
    *
    * @access public   
    * @param integer $value
    * @return boolean
    */
    function setId($value) {
      return $this->setColumnValue('id', $value);
    } // setId() 

    /**
    * Return value of 'firstname' field
    *
    * @access public
    * @param void
    * @return string 
    */
    function getFirstname() {
      return $this->getColumnValue('firstname');
    } // getFirstname()
    
    /**
    * Set value of 'firstname' field
    *
    * @access public   
    * @param string $value
    * @return boolean
    */
    function setFirstname($value) {
      return $this->setColumnValue('firstname', $value);
    } // setFirstname() 

    /**
    * Return value of 'lastname' field
    *
    * @access public
    * @param void
    * @return string 
    */
    function getLastname() {
      return $this->getColumnValue('lastname');
    } // getLastname()
    
    /**
    * Set value of 'lastname' field
    *
    * @access public   
    * @param string $value
    * @return boolean
    */
    function setLastname($value) {
      return $this->setColumnValue('lastname', $value);
    } // setLastname() 

    /**
    * Return value of 'middlename' field
    *
    * @access public
    * @param void
    * @return string 
    */
    function getMiddlename() {
      return $this->getColumnValue('middlename');
    } // getMiddlename()
    
    /**
    * Set value of 'middlename' field
    *
    * @access public   
    * @param string $value
    * @return boolean
    */
    function setMiddlename($value) {
      return $this->setColumnValue('middlename', $value);
    } // setMiddlename() 

    /**
    * Return value of 'department' field
    *
    * @access public
    * @param void
    * @return string 
    */
    function getDepartment() {
      return $this->getColumnValue('department');
    } // getDepartment()
    
    /**
    * Set value of 'department' field
    *
    * @access public   
    * @param string $value
    * @return boolean
    */
    function setDepartment($value) {
      return $this->setColumnValue('department', $value);
    } // setDepartment() 

    /**
    * Return value of 'job_title' field
    *
    * @access public
    * @param void
    * @return string 
    */
    function getJobTitle() {
      return $this->getColumnValue('job_title');
    } // getJobTitle()
    
    /**
    * Set value of 'job_title' field
    *
    * @access public   
    * @param string $value
    * @return boolean
    */
    function setJobTitle($value) {
      return $this->setColumnValue('job_title', $value);
    } // setJobTitle() 

    /**
    * Return value of 'company_id' field
    *
    * @access public
    * @param void
    * @return string 
    */
    function getCompanyId() {
      return $this->getColumnValue('company_id');
    } // getCompanyId()
    
    /**
    * Set value of 'company_id' field
    *
    * @access public   
    * @param string $value
    * @return boolean
    */
    function setCompanyId($value) {
      return $this->setColumnValue('company_id', $value);
    } // setCompanyId() 

    /**
    * Return value of 'email' field
    *
    * @access public
    * @param void
    * @return string 
    */
    function getEmail() {
      return $this->getColumnValue('email');
    } // getEmail()
    
    /**
    * Set value of 'email' field
    *
    * @access public   
    * @param string $value
    * @return boolean
    */
    function setEmail($value) {
      return $this->setColumnValue('email', $value);
    } // setEmail() 

    /**
    * Return value of 'email2' field
    *
    * @access public
    * @param void
    * @return string 
    */
    function getEmail2() {
      return $this->getColumnValue('email2');
    } // getEmail2()
    
    /**
    * Set value of 'email2' field
    *
    * @access public   
    * @param string $value
    * @return boolean
    */
    function setEmail2($value) {
      return $this->setColumnValue('email2', $value);
    } // setEmail2() 

    /**
    * Return value of 'email3' field
    *
    * @access public
    * @param void
    * @return string 
    */
    function getEmail3() {
      return $this->getColumnValue('email3');
    } // getEmail3()
    
    /**
    * Set value of 'email3' field
    *
    * @access public   
    * @param string $value
    * @return boolean
    */
    function setEmail3($value) {
      return $this->setColumnValue('email3', $value);
    } // setEmail3() 

    /**
    * Return value of 'w_web_page' field
    *
    * @access public
    * @param void
    * @return string 
    */
    function getWWebPage() {
      return $this->getColumnValue('w_web_page');
    } // getWWebPage()
    
    /**
    * Set value of 'w_web_page' field
    *
    * @access public   
    * @param string $value
    * @return boolean
    */
    function setWWebPage($value) {
      return $this->setColumnValue('w_web_page', $value);
    } // setWWebPage() 

    /**
    * Return value of 'w_address' field
    *
    * @access public
    * @param void
    * @return string 
    */
    function getWAddress() {
      return $this->getColumnValue('w_address');
    } // getWAddress()
    
    /**
    * Set value of 'w_address' field
    *
    * @access public   
    * @param string $value
    * @return boolean
    */
    function setWAddress($value) {
      return $this->setColumnValue('w_address', $value);
    } // setWAddress() 

    /**
    * Return value of 'w_city' field
    *
    * @access public
    * @param void
    * @return string 
    */
    function getWCity() {
      return $this->getColumnValue('w_city');
    } // getWCity()
    
    /**
    * Set value of 'w_city' field
    *
    * @access public   
    * @param string $value
    * @return boolean
    */
    function setWCity($value) {
      return $this->setColumnValue('w_city', $value);
    } // setWCity() 

    /**
    * Return value of 'w_state' field
    *
    * @access public
    * @param void
    * @return string 
    */
    function getWState() {
      return $this->getColumnValue('w_state');
    } // getWState()
    
    /**
    * Set value of 'w_state' field
    *
    * @access public   
    * @param string $value
    * @return boolean
    */
    function setWState($value) {
      return $this->setColumnValue('w_state', $value);
    } // setWState() 

    /**
    * Return value of 'w_zipcode' field
    *
    * @access public
    * @param void
    * @return string 
    */
    function getWZipcode() {
      return $this->getColumnValue('w_zipcode');
    } // getWZipcode()
    
    /**
    * Set value of 'w_zipcode' field
    *
    * @access public   
    * @param string $value
    * @return boolean
    */
    function setWZipcode($value) {
      return $this->setColumnValue('w_zipcode', $value);
    } // setWZipcode() 

    /**
    * Return value of 'w_country' field
    *
    * @access public
    * @param void
    * @return string 
    */
    function getWCountry() {
      return $this->getColumnValue('w_country');
    } // getWCountry()
    
    /**
    * Set value of 'w_country' field
    *
    * @access public   
    * @param string $value
    * @return boolean
    */
    function setWCountry($value) {
      return $this->setColumnValue('w_country', $value);
    } // setWCountry() 

    /**
    * Return value of 'w_phone_number2' field
    *
    * @access public
    * @param void
    * @return string 
    */
    function getWPhoneNumber2() {
      return $this->getColumnValue('w_phone_number2');
    } // getWPhoneNumber2()
    
    /**
    * Set value of 'w_phone_number2' field
    *
    * @access public   
    * @param string $value
    * @return boolean
    */
    function setWPhoneNumber2($value) {
      return $this->setColumnValue('w_phone_number2', $value);
    } // setWPhoneNumber2() 

    /**
    * Return value of 'w_phone_number' field
    *
    * @access public
    * @param void
    * @return string 
    */
    function getWPhoneNumber() {
      return $this->getColumnValue('w_phone_number');
    } // getWPhoneNumber()
    
    /**
    * Set value of 'w_phone_number' field
    *
    * @access public   
    * @param string $value
    * @return boolean
    */
    function setWPhoneNumber($value) {
      return $this->setColumnValue('w_phone_number', $value);
    } // setWPhoneNumber() 

    /**
    * Return value of 'w_fax_number' field
    *
    * @access public
    * @param void
    * @return string 
    */
    function getWFaxNumber() {
      return $this->getColumnValue('w_fax_number');
    } // getWFaxNumber()
    
    /**
    * Set value of 'w_fax_number' field
    *
    * @access public   
    * @param string $value
    * @return boolean
    */
    function setWFaxNumber($value) {
      return $this->setColumnValue('w_fax_number', $value);
    } // setWFaxNumber() 

    /**
    * Return value of 'w_assistant_number' field
    *
    * @access public
    * @param void
    * @return string 
    */
    function getWAssistantNumber() {
      return $this->getColumnValue('w_assistant_number');
    } // getWAssistantNumber()
    
    /**
    * Set value of 'w_assistant_number' field
    *
    * @access public   
    * @param string $value
    * @return boolean
    */
    function setWAssistantNumber($value) {
      return $this->setColumnValue('w_assistant_number', $value);
    } // setWAssistantNumber() 

    
    /**
    * Return value of 'w_callback_number' field
    *
    * @access public
    * @param void
    * @return string 
    */
    function getWCallbackNumber() {
      return $this->getColumnValue('w_callback_number');
    } // getWCallbackNumber()
    
    /**
    * Set value of 'w_callback_number' field
    *
    * @access public   
    * @param string $value
    * @return boolean
    */
    function setWCallbackNumber($value) {
      return $this->setColumnValue('w_callback_number', $value);
    } // setWCallbackNumber() 
    
    
    
    /**
    * Return value of 'h_web_page' field
    *
    * @access public
    * @param void
    * @return string 
    */
    function getHWebPage() {
      return $this->getColumnValue('h_web_page');
    } // getHWebPage()
    
    /**
    * Set value of 'h_web_page' field
    *
    * @access public   
    * @param string $value
    * @return boolean
    */
    function setHWebPage($value) {
      return $this->setColumnValue('h_web_page', $value);
    } // setHWebPage() 

    /**
    * Return value of 'h_address' field
    *
    * @access public
    * @param void
    * @return string 
    */
    function getHAddress() {
      return $this->getColumnValue('h_address');
    } // getHAddress()
    
    /**
    * Set value of 'h_address' field
    *
    * @access public   
    * @param string $value
    * @return boolean
    */
    function setHAddress($value) {
      return $this->setColumnValue('h_address', $value);
    } // setHAddress() 

    /**
    * Return value of 'h_city' field
    *
    * @access public
    * @param void
    * @return string 
    */
    function getHCity() {
      return $this->getColumnValue('h_city');
    } // getHCity()
    
    /**
    * Set value of 'h_city' field
    *
    * @access public   
    * @param string $value
    * @return boolean
    */
    function setHCity($value) {
      return $this->setColumnValue('h_city', $value);
    } // setHCity() 

    /**
    * Return value of 'h_state' field
    *
    * @access public
    * @param void
    * @return string 
    */
    function getHState() {
      return $this->getColumnValue('h_state');
    } // getHState()
    
    /**
    * Set value of 'h_state' field
    *
    * @access public   
    * @param string $value
    * @return boolean
    */
    function setHState($value) {
      return $this->setColumnValue('h_state', $value);
    } // setHState() 

    /**
    * Return value of 'h_zipcode' field
    *
    * @access public
    * @param void
    * @return string 
    */
    function getHZipcode() {
      return $this->getColumnValue('h_zipcode');
    } // getHZipcode()
    
    /**
    * Set value of 'h_zipcode' field
    *
    * @access public   
    * @param string $value
    * @return boolean
    */
    function setHZipcode($value) {
      return $this->setColumnValue('h_zipcode', $value);
    } // setHZipcode() 

    /**
    * Return value of 'h_country' field
    *
    * @access public
    * @param void
    * @return string 
    */
    function getHCountry() {
      return $this->getColumnValue('h_country');
    } // getHCountry()
    
    /**
    * Set value of 'h_country' field
    *
    * @access public   
    * @param string $value
    * @return boolean
    */
    function setHCountry($value) {
      return $this->setColumnValue('h_country', $value);
    } // setHCountry() 

    /**
    * Return value of 'h_phone_number2' field
    *
    * @access public
    * @param void
    * @return string 
    */
    function getHPhoneNumber2() {
      return $this->getColumnValue('h_phone_number2');
    } // getHPhoneNumber2()
    
    /**
    * Set value of 'h_phone_number2' field
    *
    * @access public   
    * @param string $value
    * @return boolean
    */
    function setHPhoneNumber2($value) {
      return $this->setColumnValue('h_phone_number2', $value);
    } // setHPhoneNumber2() 

    /**
    * Return value of 'h_phone_number' field
    *
    * @access public
    * @param void
    * @return string 
    */
    function getHPhoneNumber() {
      return $this->getColumnValue('h_phone_number');
    } // getHPhoneNumber()
    
    /**
    * Set value of 'h_phone_number' field
    *
    * @access public   
    * @param string $value
    * @return boolean
    */
    function setHPhoneNumber($value) {
      return $this->setColumnValue('h_phone_number', $value);
    } // setHPhoneNumber() 

    /**
    * Return value of 'h_fax_number' field
    *
    * @access public
    * @param void
    * @return string 
    */
    function getHFaxNumber() {
      return $this->getColumnValue('h_fax_number');
    } // getHFaxNumber()
    
    /**
    * Set value of 'h_fax_number' field
    *
    * @access public   
    * @param string $value
    * @return boolean
    */
    function setHFaxNumber($value) {
      return $this->setColumnValue('h_fax_number', $value);
    } // setHFaxNumber() 

    /**
    * Return value of 'h_mobile_number' field
    *
    * @access public
    * @param void
    * @return string 
    */
    function getHMobileNumber() {
      return $this->getColumnValue('h_mobile_number');
    } // getHMobileNumber()
    
    /**
    * Set value of 'h_mobile_number' field
    *
    * @access public   
    * @param string $value
    * @return boolean
    */
    function setHMobileNumber($value) {
      return $this->setColumnValue('h_mobile_number', $value);
    } // setHMobileNumber() 

    /**
    * Return value of 'h_pager_number' field
    *
    * @access public
    * @param void
    * @return string 
    */
    function getHPagerNumber() {
      return $this->getColumnValue('h_pager_number');
    } // getHPagerNumber()
    
    /**
    * Set value of 'h_pager_number' field
    *
    * @access public   
    * @param string $value
    * @return boolean
    */
    function setHPagerNumber($value) {
      return $this->setColumnValue('h_pager_number', $value);
    } // setHPagerNumber() 

    /**
    * Return value of 'o_web_page' field
    *
    * @access public
    * @param void
    * @return string 
    */
    function getOWebPage() {
      return $this->getColumnValue('o_web_page');
    } // getOWebPage()
    
    /**
    * Set value of 'o_web_page' field
    *
    * @access public   
    * @param string $value
    * @return boolean
    */
    function setOWebPage($value) {
      return $this->setColumnValue('o_web_page', $value);
    } // setOWebPage() 

    /**
    * Return value of 'o_address' field
    *
    * @access public
    * @param void
    * @return string 
    */
    function getOAddress() {
      return $this->getColumnValue('o_address');
    } // getOAddress()
    
    /**
    * Set value of 'o_address' field
    *
    * @access public   
    * @param string $value
    * @return boolean
    */
    function setOAddress($value) {
      return $this->setColumnValue('o_address', $value);
    } // setOAddress() 

    /**
    * Return value of 'o_city' field
    *
    * @access public
    * @param void
    * @return string 
    */
    function getOCity() {
      return $this->getColumnValue('o_city');
    } // getOCity()
    
    /**
    * Set value of 'o_city' field
    *
    * @access public   
    * @param string $value
    * @return boolean
    */
    function setOCity($value) {
      return $this->setColumnValue('o_city', $value);
    } // setOCity() 

    /**
    * Return value of 'o_state' field
    *
    * @access public
    * @param void
    * @return string 
    */
    function getOState() {
      return $this->getColumnValue('o_state');
    } // getOState()
    
    /**
    * Set value of 'o_state' field
    *
    * @access public   
    * @param string $value
    * @return boolean
    */
    function setOState($value) {
      return $this->setColumnValue('o_state', $value);
    } // setOState() 

    /**
    * Return value of 'o_zipcode' field
    *
    * @access public
    * @param void
    * @return string 
    */
    function getOZipcode() {
      return $this->getColumnValue('o_zipcode');
    } // getOZipcode()
    
    /**
    * Set value of 'o_zipcode' field
    *
    * @access public   
    * @param string $value
    * @return boolean
    */
    function setOZipcode($value) {
      return $this->setColumnValue('o_zipcode', $value);
    } // setOZipcode() 

    /**
    * Return value of 'o_country' field
    *
    * @access public
    * @param void
    * @return string 
    */
    function getOCountry() {
      return $this->getColumnValue('o_country');
    } // getOCountry()
    
    /**
    * Set value of 'o_country' field
    *
    * @access public   
    * @param string $value
    * @return boolean
    */
    function setOCountry($value) {
      return $this->setColumnValue('o_country', $value);
    } // setOCountry() 

    /**
    * Return value of 'o_phone_number2' field
    *
    * @access public
    * @param void
    * @return string 
    */
    function getOPhoneNumber2() {
      return $this->getColumnValue('o_phone_number2');
    } // getOPhoneNumber2()
    
    /**
    * Set value of 'o_phone_number2' field
    *
    * @access public   
    * @param string $value
    * @return boolean
    */
    function setOPhoneNumber2($value) {
      return $this->setColumnValue('o_phone_number2', $value);
    } // setOPhoneNumber2() 

    /**
    * Return value of 'o_phone_number' field
    *
    * @access public
    * @param void
    * @return string 
    */
    function getOPhoneNumber() {
      return $this->getColumnValue('o_phone_number');
    } // getOPhoneNumber()
    
    /**
    * Set value of 'o_phone_number' field
    *
    * @access public   
    * @param string $value
    * @return boolean
    */
    function setOPhoneNumber($value) {
      return $this->setColumnValue('o_phone_number', $value);
    } // setOPhoneNumber() 

    /**
    * Return value of 'o_fax_number' field
    *
    * @access public
    * @param void
    * @return string 
    */
    function getOFaxNumber() {
      return $this->getColumnValue('o_fax_number');
    } // getOFaxNumber()
    
    /**
    * Set value of 'o_fax_number' field
    *
    * @access public   
    * @param string $value
    * @return boolean
    */
    function setOFaxNumber($value) {
      return $this->setColumnValue('o_fax_number', $value);
    } // setOFaxNumber() 

    /**
    * Return value of 'o_birthday' field
    *
    * @access public
    * @param void
    * @return datetimevalue 
    */
    function getOBirthday() {
      return $this->getColumnValue('o_birthday');
    } // getOBirthday()
    
    /**
    * Set value of 'o_birthday' field
    *
    * @access public   
    * @param datetimevalue $value
    * @return boolean
    */
    function setOBirthday($value) {
      return $this->setColumnValue('o_birthday', $value);
    } // setOBirthday() 

    /**
    * Return value of 'picture_file' field
    *
    * @access public
    * @param void
    * @return string 
    */
    function getPictureFile() {
      return $this->getColumnValue('picture_file');
    } // getPictureFile()
    
    /**
    * Set value of 'picture_file' field
    *
    * @access public   
    * @param string $value
    * @return boolean
    */
    function setPictureFile($value) {
      return $this->setColumnValue('picture_file', $value);
    } // setPictureFile() 

    /**
    * Return value of 'timezone' field
    *
    * @access public
    * @param void
    * @return float 
    */
    function getTimezone() {
      return $this->getColumnValue('timezone');
    } // getTimezone()
    
    /**
    * Set value of 'timezone' field
    *
    * @access public   
    * @param float $value
    * @return boolean
    */
    function setTimezone($value) {
      return $this->setColumnValue('timezone', $value);
    } // setTimezone() 

    /**
    * Return value of 'notes' field
    *
    * @access public
    * @param void
    * @return string 
    */
    function getNotes() {
      return $this->getColumnValue('notes');
    } // getNotes()
    
    /**
    * Set value of 'notes' field
    *
    * @access public   
    * @param string $value
    * @return boolean
    */
    function setNotes($value) {
      return $this->setColumnValue('notes', $value);
    } // setNotes() 

    /**
    * Return value of 'user_id' field
    *
    * @access public
    * @param void
    * @return integer 
    */
    function getUserId() {
      return $this->getColumnValue('user_id');
    } // getUserId()
    
    /**
    * Set value of 'user_id' field
    *
    * @access public   
    * @param integer $value
    * @return boolean
    */
    function setUserId($value) {
      return $this->setColumnValue('user_id', $value);
    } // setUserId() 

    /**
    * Return value of 'is_private' field
    *
    * @access public
    * @param void
    * @return boolean 
    */
    function getIsPrivate() {
      return $this->getColumnValue('is_private');
    } // getIsPrivate()
    
    /**
    * Set value of 'is_private' field
    *
    * @access public   
    * @param boolean $value
    * @return boolean
    */
    function setIsPrivate($value) {
      return $this->setColumnValue('is_private', $value);
    } // setIsPrivate() 

    /**
    * Return value of 'created_on' field
    *
    * @access public
    * @param void
    * @return DateTimeValue 
    */
    function getCreatedOn() {
      return $this->getColumnValue('created_on');
    } // getCreatedOn()
    
    /**
    * Set value of 'created_on' field
    *
    * @access public   
    * @param DateTimeValue $value
    * @return boolean
    */
    function setCreatedOn($value) {
      return $this->setColumnValue('created_on', $value);
    } // setCreatedOn() 

    /**
    * Return value of 'created_by_id' field
    *
    * @access public
    * @param void
    * @return integer 
    */
    function getCreatedById() {
      return $this->getColumnValue('created_by_id');
    } // getCreatedById()
    
    /**
    * Set value of 'created_by_id' field
    *
    * @access public   
    * @param integer $value
    * @return boolean
    */
    function setCreatedById($value) {
      return $this->setColumnValue('created_by_id', $value);
    } // setCreatedById() 
    
    /**
    * Return value of 'updated_on' field
    *
    * @access public
    * @param void
    * @return DateTimeValue 
    */
    function getUpdatedOn() {
      return $this->getColumnValue('updated_on');
    } // getUpdatedOn()
    
    /**
    * Set value of 'updated_on' field
    *
    * @access public   
    * @param DateTimeValue $value
    * @return boolean
    */
    function setUpdatedOn($value) {
      return $this->setColumnValue('updated_on', $value);
    } // setUpdatedOn() 

    /**
    * Return value of 'updated_by_id' field
    *
    * @access public
    * @param void
    * @return integer 
    */
    function getUpdatedById() {
      return $this->getColumnValue('updated_by_id');
    } // getUpdatedById()
    
    /**
    * Set value of 'updated_by_id' field
    *
    * @access public   
    * @param integer $value
    * @return boolean
    */
    function setUpdatedById($value) {
      return $this->setColumnValue('updated_by_id', $value);
    } // setUpdatedById() 
    
    /** Return value of 'trashed_on' field
    *
    * @access public
    * @param void
    * @return DateTimeValue 
    */
    function getTrashedOn() {
      return $this->getColumnValue('trashed_on');
    } // getTrashedOn()
    
    /**
    * Set value of 'trashed_on' field
    *
    * @access public   
    * @param DateTimeValue $value
    * @return boolean
    */
    function setTrashedOn($value) {
      return $this->setColumnValue('trashed_on', $value);
    } // setTrashedOn() 
    
    /**
    * Return value of 'trashed_by_id' field
    *
    * @access public
    * @param void
    * @return integer 
    */
    function getTrashedById() {
      return $this->getColumnValue('trashed_by_id');
    } // getTrashedById()
    
    /**
    * Set value of 'trashed_by_id' field
    *
    * @access public   
    * @param integer $value
    * @return boolean
    */
    function setTrashedById($value) {
      return $this->setColumnValue('trashed_by_id', $value);
    } // setTrashedById()

    /**
    * Return value of 'archived_by_id' field
    *
    * @access public
    * @param void
    * @return integer 
    */
    function getArchivedById() {
      return $this->getColumnValue('archived_by_id');
    } // getArchivedById()
    
    /**
    * Set value of 'archived_by_id' field
    *
    * @access public   
    * @param integer $value
    * @return boolean
    */
    function setArchivedById($value) {
      return $this->setColumnValue('archived_by_id', $value);
    } // setArchivedById()
    
	/** Return value of 'archived_on' field
    *
    * @access public
    * @param void
    * @return DateTimeValue 
    */
    function getArchivedOn() {
      return $this->getColumnValue('archived_on');
    } // getArchivedOn()
    
    /**
    * Set value of 'archived_on' field
    *
    * @access public   
    * @param DateTimeValue $value
    * @return boolean
    */
    function setArchivedOn($value) {
      return $this->setColumnValue('archived_on', $value);
    } // setArchivedOn() 
    
    /**
    * Return manager instance
    *
    * @access protected
    * @param void
    * @return Contacts 
    */
    function manager() {
      if(!($this->manager instanceof Contacts)) $this->manager = Contacts::instance();
      return $this->manager;
    } // manager
  
  } // BaseContact 

?>
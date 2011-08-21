<?php

  /**
  * ProjectContact class
  * Generated on Wed, 15 Mar 2006 22:57:46 +0100 by DataObject generation tool
  *
  * @author Ilija Studen <ilija.studen@gmail.com>
  */
  class ProjectContact extends BaseProjectContact {
  
  	private $contact;

  	protected $project;

    /**
    * This project object is taggable
    *
    * @var boolean
    */
    protected $is_taggable = true;
    
  	/**
    * Return Contact
    *
    * @access public
    * @param void
    * @return Contact
    */
  	function getContact()
  	{
  		if(is_null($this->contact)) {
  			$this->contact = Contacts::findById($this->getContactId());
  		} // if
  		return $this->contact;
  	}
  	
  /**
    * Return Project
    *
    * @access public
    * @param void
    * @return Project
    */
  	function getProject()
  	{
  		if(is_null($this->project)) {
  			$this->project = Projects::findById($this->getProjectId());
  		} // if
  		return $this->project;
  	}
  	 
  	
	// ---------------------------------------------------
    //  Permissions
    // ---------------------------------------------------
    
    
    /**
    * Returns true if $user can access this contact
    *
    * @param User $user
    * @return boolean
    */
    function canView(User $user) {
       return can_read($user,$this);
    } // canView
    
    /**
     * Check if specific user can add contacts to specific project
     *
     * @access public
     * @param User $user
     * @param Project $project
     * @return booelean
     */
    function canAdd(User $user, Project $project) {
       return can_add($user,$project,get_class(Contacts::instance()));
    } // canAdd
    
    /**
    * Check if specific user can edit this contact
    *
    * @access public
    * @param User $user
    * @return boolean
    */
    function canEdit(User $user) {
       return can_write($user,$this);
    } // canEdit
    
    /**
    * Check if specific user can delete this contact
    *
    * @access public
    * @param User $user
    * @return boolean
    */
    function canDelete(User $user) {
       return can_delete($user, $this);
    } // canDelete
    
  

  } // ProjectContact 

?>
<?php

  /**
  * ProjectContacts, generated on Wed, 15 Mar 2006 22:57:46 +0100 by 
  * DataObject generation tool
  *
  * @author Ilija Studen <ilija.studen@gmail.com>
  */
  class ProjectContacts extends BaseProjectContacts {
  
    /**
    * Return all contacts that are involved in specific project
    *
    * @access public
    * @param Project $project
    * @param string $additional_conditions
    * @return array
    */
    static function getContactsByProject(Project $project, $additional_conditions = null) {
      $contacts_table = Contacts::instance()->getTableName(true);
      $project_contacts_table=  ProjectContacts::instance()->getTableName(true);
      
      $contacts = array();
      
      $sql = "SELECT $contacts_table.* FROM $contacts_table, $project_contacts_table WHERE ($contacts_table.`id` = $project_contacts_table.`contact_id` AND $project_contacts_table.`project_id` = " . DB::escape($project->getId()) . ')';
      if(trim($additional_conditions) <> '') $sql .= " AND ($additional_conditions)";
      
      $rows = DB::executeAll($sql);
      if(is_array($rows)) {
        foreach($rows as $row) {
          $contacts[] = Contacts::instance()->loadFromRow($row);
        } // foreach
      } // if
      
      return count($contacts) ? $contacts : null;
    } // getContactsByProject
    
    /**
    * Return contacts of specific company involeved in specific project
    *
    * @access public
    * @param Company $company
    * @param Project $project
    * @return array
    */
    function getCompanyContactsByProject(Company $company, Project $project) {
      $contacts_table = Contacts::instance()->getTableName(true);
      return self::getContactsByProject($project, "$contacts_table.`company_id` = " . DB::escape($company->getId()));
    } // getCompanyContactsByProject
    
    /**
    * Return all projects that this contact is part of
    *
    * @access public
    * @param Contact $contact
    * @param 
    * @return array
    */
    function getProjectsByContact(Contact $contact, $additional_conditions = null) {
      $projects_table = Projects::instance()->getTableName(true);
      $project_contacts_table=  ProjectContacts::instance()->getTableName(true);
      
      $projects = array();
      
      $sql = "SELECT $projects_table.* FROM $projects_table, $project_contacts_table WHERE ($projects_table.`id` = $project_contacts_table.`project_id` AND $project_contacts_table.`contact_id` = " . DB::escape($contact->getId()) . ')';
      if(trim($additional_conditions) <> '') {
        $sql .= " AND ($additional_conditions)";
      } // if
      $sql .= " ORDER BY $projects_table.`name`";
      
      $rows = DB::executeAll($sql);
      if(is_array($rows)) {
        foreach($rows as $row) {
          $projects[] = Projects::instance()->loadFromRow($row);
        } // foreach
      } // if
      
      return $projects;
    }
      
    /**
    * Return all roles for a specific contact
    *
    * @access public
    * @param Contact $contact
    * @param 
    * @return array
    */
    function getRolesByContact(Contact $contact, $additional_conditions = null) {
      return ProjectContacts::findAll(array('conditions' => '`contact_id` = '.$contact->getId()));
    } // getProjectsByContact
	
    
    /**
    * Return all roles for a specific project
    *
    * @access public
    * @param Project $project
    * @param 
    * @return array
    */
    function getRolesByProject(Project $project, $additional_conditions = null) {
      return ProjectContacts::findAll(array('conditions' => '`project_id` = '.$project->getId()));
    } // getProjectsByContact
    
    /**
    * Return all contacts associated with specific project
    *
    * @access public
    * @param Project $project
    * @return boolean
    */
    static function clearByProject(Project $project) {
      return self::delete(array('`project_id` = ?', $project->getId()));
    } // clearByProject
    
    /**
    * Clear permission by contact
    *
    * @param Contact $contact
    * @return boolean
    */
    static function clearByContact(Contact $contact) {
      return self::delete(array('`contact_id` = ?', $contact->getId()));
    } // clearByContact
    
    function deleteRole(Contact $contact, Project $project)
    {
    	$project_contacts_table=  ProjectContacts::instance()->getTableName(true);

    	$sql = "DELETE FROM $project_contacts_table WHERE `project_id` = ".DB::escape($project->getId())." AND `contact_id` = " . DB::escape($contact->getId());

    	$rows = DB::executeAll($sql);
    }
    
    function getRole(Contact $contact, Project $project)
    {
    	return ProjectContacts::findOne(array('conditions' => '`project_id` = '.$project->getId().' AND `contact_id` = '.$contact->getId()));
    }
  } // ProjectContacts 

?>
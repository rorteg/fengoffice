<?php

  /**
  *
  * @author Marcos Saiz<marcos.saiz@gmail.com>
  */
  class Groups extends BaseGroups {
    
    /**
    * Return all registered groups
    *
    * @param void
    * @return array
    */
    static function getAll() {
      return Groups::findAll(); // findAll
    } // getAll
      
//    /**
//    * Return group users
//    *
//    * @param Group $group
//    * @return array
//    */
//    static function getGroupUsers(Group $group) {
//      return groups::findAll(array(
//        'conditions' => array('`client_of_id` = ?', $group->getId()),
//        'order' => '`name`'
//      )); // array
//    } // getgroupClients
    
  } // groups

?>
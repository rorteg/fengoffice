<?php
/**
 * ExternalCalendarUsers
 * Generado el 22/2/2012
 * @author Andres Botta <andres@iugo.com.uy>
 */
class ExternalCalendarUsers extends BaseExternalCalendarUsers {
    
    function findByContactId() {
            return ExternalCalendarUsers::findOne(array('conditions' => array('`contact_id` = ?', logged_user()->getId())));
    }
    
    function findByEmail($email) {
            return ExternalCalendarUsers::findOne(array('conditions' => array('`auth_user` = ? AND `contact_id` <> ?', $email, logged_user()->getId())));
    }
} 
?>
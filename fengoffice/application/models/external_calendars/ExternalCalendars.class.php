<?php
/**
 * ExternalCalendars
 * Generado el 22/2/2012
 * @author Andres Botta <andres@iugo.com.uy>
 */
class ExternalCalendars extends BaseExternalCalendars {
    
    function findByExtCalUserId($user) {
            return ExternalCalendars::findAll(array('conditions' => array('`ext_cal_user_id` = ?', $user)));
    }
} 
?>
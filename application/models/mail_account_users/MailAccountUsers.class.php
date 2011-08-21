<?php

/**
 * MailAccountUsers
 *
 * @author Ignacio de Soto <ignacio.desoto@gmail.com>
 */
class MailAccountUsers extends BaseMailAccountUsers {
	
	const MA_NO_ERROR = 0;
	const MA_ERROR_UNREAD = 1;
	const MA_ERROR_READ = 2;
	
	function getByAccount($account) {
		return MailAccountUsers::findAll(array('conditions' => array('`account_id` = ?', $account->getId())));
	}
	
	function getByUser($user) {
		return MailAccountUsers::findAll(array('conditions' => array('`user_id` = ?', $user->getId())));
	}
	
	function getByAccountAndUser($account, $user) {
		return MailAccountUsers::findOne(array('conditions' => array('`account_id` = ? AND `user_id` = ?', $account->getId(), $user->getId())));
	}
	
	function deleteByAccount($account) {
		return MailAccountUsers::delete(array('`account_id` = ?', $account->getId()));
	}
	
	function deleteByUser($user) {
		return MailAccountUsers::delete(array('`user_id` = ?', $user->getId()));
	}
	
	function countByAccount($account) {
		return MailAccountUsers::count(array('`account_id` = ?', $account->getId()));
	}
	
} // MailAccountUsers

?>
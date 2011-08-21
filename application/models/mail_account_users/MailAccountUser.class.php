<?php

/**
 * MailAccountUser class
 *
 * @author Ignacio de Soto <ignacio.desoto@fengoffice.com>
 */
class MailAccountUser extends BaseMailAccountUser {

	function getUser() {
		return Users::findById($this->getUserId());
	}
	
	function getAccount() {
		return MailAccounts::findById($this->getAccountId());
	}

}
?>
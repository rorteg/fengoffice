<?php

/**
 * UserPassword class
 *
 * @author Pablo Kamil <pablokam@gmail.com>
 */
class UserPassword extends BaseUserPassword {

	
	/**
	 * Save
	 *
	 */
   	function save() {
   		parent::save();
   		// If more than 10 passwords, delete oldest
   		$passwords = UserPasswords::findAll(array(
   			'conditions' => array('`user_id` = ?', $this->getUserId())
   		));
   		
   		if(count($passwords) > 10){
   			$oldest = UserPasswords::getOldestUserPassword($this->getUserId());
   			$oldest[0]->delete();
   		}        
    }
    
	// ---------------------------------------------------
	//  System functions
	// ---------------------------------------------------

	/**
	 * Validate data before save
	 *
	 * @access public
	 * @param array $errors
	 * @return void
	 */
	function validate(&$errors) {		

		// Validate min length for the password
		if(!UserPasswords::validateMinLength($this->password_temp)) {
			$min_pass_length = config_option('min_password_length', 0);			
			$errors[] = lang('password invalid min length', $min_pass_length);
		} // if
		
		// Validate password numbers
		if(!UserPasswords::validateNumbers($this->password_temp)) {
			$pass_numbers = config_option('password_numbers', 0);			
			$errors[] = lang('password invalid numbers', $pass_numbers);
		} // if
		
		// Validate uppercase characters
		if(!UserPasswords::validateUppercaseCharacters($this->password_temp)) {	
			$pass_uppercase = config_option('password_uppercase_characters', 0);		
			$errors[] = lang('password invalid uppercase', $pass_uppercase);
		} // if
		
		// Validate metacharacters
		if(!UserPasswords::validateMetacharacters($this->password_temp)) {	
			$pass_metacharacters = config_option('password_metacharacters', 0);		
			$errors[] = lang('password invalid metacharacters', $pass_metacharacters);
		} // if
		
		// Validate against password history
		if(!UserPasswords::validateAgainstPasswordHistory($this->getUserId(), $this->password_temp)) {			
			$errors[] = lang('password exists history');
		} // if
		
		// Validate new password character difference
		if(!UserPasswords::validateCharDifferences($this->getUserId(), $this->password_temp)) {			
			$errors[] = lang('password invalid difference');
		} // if
		
	} // validate

	/**
	 * Delete this object
	 *
	 * @param void
	 * @return boolean
	 */
	function delete() {		
		return parent::delete();
	} // delete

	// ---------------------------------------------------
	//  DataObject implementation
	// ---------------------------------------------------
 	
	/**
	 * Return object name
	 *
	 * @access public
	 * @param void
	 * @return string
	 */
	function getObjectName() {
		return $this->getDisplayName();
	} // getObjectName

	/**
	 * Return object type name
	 *
	 * @param void
	 * @return string
	 */
	function getObjectTypeName() {
		return 'userpassword';
	} // getObjectTypeName


	function getArrayInfo(){
		$result = array(
			'id' => $this->getId(),
			'user_id' => $this->getUserId(),
			'password' => $this->getPassword(),
			'password_date' => $this->getPasswordDate());
		
		return $result;
	}
} // UserPassword

?>
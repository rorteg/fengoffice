<?php

  /**
  * ldap.config.example.php is sample configuration file for ldap authentication. 
  * Rename it in ldap.config.php and change the values acconrding to your env.
  * 
  * @author Luca Corbo <luca.corbo@2bopen.org>
  */
  
  // The configuration array:
  $config_ldap = array (
      'binddn'    => 'cn=admin,ou=users,dc=example,dc=org',
      'bindpw'    => 'password',
      'basedn'    => 'dc=example,dc=org',
      'host'      => 'ldap.example.org',
      'uid'       => 'uid' //Change in according with your settings to match the userid entry
  );
  return true;
  
?>

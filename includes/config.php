<?php

if(!defined('IN_RKN_PREDATOR'))
{
	echo 'Access to this file is prohibited';
	exit;
}

// ### START USER CONFIGURABLE SETTINGS ### \\

//Begin Database Options
define('DB_USER', 'amateurv_kevin');
define('DB_PASS', 'kevin123');
define('DB_HOST', 'localhost');
define('DB_NAME', 'amateurv_pred1');
define('TBLPRE', 'predator_');

define('RKN__db_driver', 'MySQL');
//End Database Options

define('RKN__cache_queries', false); /** Comment out this line to enable query caching */

/*======================================
The option below allows you to change
the admin cp directory name, which we
recommend doing to ensure maximum
security. Note you'll have to manually
rename the folder or else your Predator
admin control panel will not work.
=======================================*/

define('RKN__adminpath', 'admin');

// ### END USER CONFIGURABLE SETTINGS ### \\

/*
Do not manually edit anything below here!
We mean it, seriously, don't, or you'll
have a very broken site...and we don't
like very broken sites ;)
*/

define('RKN__demo', 0);
?>
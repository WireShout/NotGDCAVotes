<?php

$db['name'] = '';
$db['user'] = '';
$db['pass'] = '';
$db['host'] = '';

$con = mysql_connect($db['host'], $db['user'], $db['pass']);
	if(!$con) die("Our network is currently having issues processing your request. Please retry later.");
$sel = mysql_select_db($db['name'], $con);
	if(!$sel) die("Our network is currently having issues processing your request. Please retry later.");
	
$groupid = 0;
$randomness = '';
	
?>
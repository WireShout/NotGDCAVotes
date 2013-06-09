<?php
/**
 * Copyright 2011 Facebook, Inc.
 * Copyright 2013 Jason Spriggs
 *
 * Licensed under the Apache License, Version 2.0 (the "License"); you may
 * not use this file except in compliance with the License. You may obtain
 * a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS, WITHOUT
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. See the
 * License for the specific language governing permissions and limitations
 * under the License.
 */

require 'fb/facebook.php';

$facebook = new Facebook(array(
  'appId'  => '', //Put your facebook app ID here.
  'secret' => '',
));

// Get User ID
$user = $facebook->getUser();

require 'conf.php';
global $con;
global $groupid;
global $randomness;

if($user) {
	try {
		$user_profile = $facebook->api('/me');
		$user_groups = $facebook->api('/me/groups');
	} catch (FacebookApiException $e) {
	    error_log($e);
		$user = null;
	}
	
	$isInGroup = FALSE;
	foreach($user_groups['data'] as $group) {
		if($group['id'] == $groupid) {
			$isInGroup = TRUE;
		}
	}
	
	if(isset($_POST['validate']) && (sha1($user_profile['id'].$randomness) == $_POST['validate'])) {
		if($isInGroup) {
			if(time() >= 1370869200 && time() <= 1371398400) {
				if((mysql_num_rows(mysql_query("SELECT * FROM `voters` WHERE `id`='" . mysql_real_escape_string($user_profile['id']) . "'", $con)) == 0)) {
					$query = "INSERT INTO `voters` (`id`, `name`) VALUES ('" . mysql_real_escape_string($user_profile['id']) . "', '" . mysql_real_escape_string($user_profile['name']) . "')";
					if(!mysql_query($query, $con)) {
						die("Execution error, contact Jason. - Error: " . mysql_error());
					}
					foreach($_POST['usr'] as $u) {
						$res = mysql_query("SELECT * FROM `nominees` WHERE `id`='" . mysql_real_escape_string($u) ."'", $con);
						if(mysql_num_rows($res) == 1) {
							$query = "UPDATE `nominees` SET `votes`='" . mysql_real_escape_string(mysql_result($res, 0, 'votes') + 1) . "' WHERE `id`=" . mysql_real_escape_string($u);
							if(!mysql_query($query, $con)) {
								die("Execution error, contact Jason. - Error: " . mysql_error());
							}
						} else {
							die("Invalid input.");
						}
					}
					header("Location: index.php");
				} else {
					die("You have voted.");
				}
			} else {
				die("It's not voting time.");
			}
		} else {
			die("You are not in the group.");
		}
	} else {
		die("Invalid origin.");
	}
}

?>
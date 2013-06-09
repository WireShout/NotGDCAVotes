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

$user = $facebook->getUser();

if ($user) {
  try {
    $user_profile = $facebook->api('/me');
	$user_groups = $facebook->api('/me/groups');
  } catch (FacebookApiException $e) {
    error_log($e);
    $user = null;
  }
}

require 'conf.php';
global $con;
global $groupid;
global $randomness;

if ($user) {
  $logoutUrl = $facebook->getLogoutUrl();
} else {
  $loginUrl = $facebook->getLoginUrl(array("scope" => "user_groups"));
}

?>
<!doctype html>
<html xmlns:fb="http://www.facebook.com/2008/fbml">
  <head>
    <title>NotGDCA LGBTQ+ Elections</title>
    <link href="fb/bootstrap.min.css" rel="stylesheet">
  </head>
  <body>
  	<div class="container">
    <h1>NotGDCA LGBTQ+ Elections</h1>

    <?php if (!$user){ ?>
      <a class="btn btn-large btn-primary" href="<?php echo $loginUrl; ?>" style="text-align: center;">Connect With Facebook</a>
    <?php } else { ?>
      <img src="https://graph.facebook.com/<?php echo $user; ?>/picture"> <b><?php echo $user_profile['name']; ?></b>
		<hr>
		
		<?php 
		$isInGroup = FALSE;
		foreach($user_groups['data'] as $group) {
			if($group['id'] == $groupid) {
				$isInGroup = TRUE;
			}
		}
		?>
		
		<?php if($isInGroup){ ?>
			<?php if(time() >= 1370869200 && time() <= 1371398400) { ?>
				<?php if(mysql_num_rows(mysql_query("SELECT * FROM `voters` WHERE `id`=" . mysql_real_escape_string($user_profile['id']), $con)) == 0) {?>
				
					<h3>Voting</h3>
					<form action="churn.php" method="POST">
						<input type="hidden" value="<?php echo sha1($user_profile['id'].$randomness); ?>" name="validate">
						<ul>
						<?php
						$result = mysql_query("SELECT * FROM nominees", $con);
						while($row = mysql_fetch_array($result)) {
							echo '<li><input type="checkbox" name="usr[]" value="' . $row['id'] . '">  ' . $row['name'] . '</li>';
						}
						?>
						</ul>
						<button type="submit" class="btn btn-primary">Submit Vote</button>
					</form>
				<?php } else { ?>
					<div class="alert alert-success">
				        <b>You have voted.</b>
				    </div>
				<?php } ?>
			<?php } else { ?>
				<div class="alert alert-block">
			        <b>It is not time to vote. (Voting is from June 10th 2013 @ 9am EDT to June 16th 2013 @ noon EDT)</b>
			    </div>
			<?php } ?>
		<?php } else { ?>
			<div class="alert alert-error">
		        <b>You are not in the group.</b>
		    </div>
		<?php } ?>
		
    <?php } ?>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.10.1/jquery.min.js"></script>
    </div>
  </body>
</html>
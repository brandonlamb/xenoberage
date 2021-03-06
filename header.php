<?php
// Blacknova Traders - A web-based massively multiplayer space combat and trading game
// Copyright (C) 2001-2012 Ron Harwood and the BNT development team
//
//  This program is free software: you can redistribute it and/or modify
//  it under the terms of the GNU Affero General Public License as
//  published by the Free Software Foundation, either version 3 of the
//  License, or (at your option) any later version.
//
//  This program is distributed in the hope that it will be useful,
//  but WITHOUT ANY WARRANTY; without even the implied warranty of
//  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//  GNU Affero General Public License for more details.
//
//  You should have received a copy of the GNU Affero General Public License
//  along with this program.  If not, see <http://www.gnu.org/licenses/>.
//
// File: header.php

/*
Load the website class auto loader into the 
*/
require_once($_SERVER['DOCUMENT_ROOT'].'/classes/auto.php');
include $_SERVER['DOCUMENT_ROOT']."/config/config.php";

header("Content-type: text/html; charset=utf-8");
header("X-UA-Compatible: IE=Edge, chrome=1");
header("Cache-Control: public"); // Tell the client (and any caches) that this information can be stored in public caches.
header("Connection: Keep-Alive"); // Tell the client to keep going until it gets all data, please.
header("Vary: Accept-Encoding, Accept-Language");
header("Keep-Alive: timeout=15, max=100");
if (!isset($body_class))
{
    $body_class = "bnt";
}
	$facebook = new facebook(array(
	  'appId'  => FB_ID,
	  'secret' => FB_SECRET,
	));


?>
<!DOCTYPE html>
<html lang="<?php echo $l->get('l_lang_attribute'); ?>">
<head>
<meta charset="utf-8">
<meta name="Description" content="Xenobe Rage - A free online game - Open source, web game, with multiplayer space exploration">
<meta name="Keywords" content="Free, online, game, Open source, web game, multiplayer, space, exploration, blacknova, traders, xenobe rage, xr, xenobe">
<meta name="Rating" content="General">
<link rel="shortcut icon" href="images/bntfavicon.ico">
<title>Xenobe Rage</title>
<link rel='stylesheet' type='text/css' href='templates/alienrage/styles/main.css'>
<link rel='stylesheet' type='text/css' href='templates/alienrage/styles/styles.css'>
<link href='http://fonts.googleapis.com/css?family=Ubuntu' rel='stylesheet' type='text/css'>
<script id="facebook-jssdk" src="//connect.facebook.net/en_US/all.js"></script>
<script type="text/javascript" src="http://code.jquery.com/jquery-1.7.1.js"></script>
<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.16/jquery-ui.js"></script>
<link rel="stylesheet" type="text/css" href="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.17/themes/base/jquery-ui.css">
<script type="text/javascript" src="templates/alienrage/js/planet-slider.js"></script>
</head>
<body>
<div id="fb-root"></div>
<script>
					  window.fbAsyncInit = function() {
						FB.init({
						  appId      : '<?php echo $facebook->getAppID(); ?>', // App ID
						  channelUrl : '<?php echo FB_CHANNEL; ?>', // Channel File
						  status     : true, // check login status
						  cookie     : true, // enable cookies to allow the server to access the session
						  oauth      : true, // enable OAuth 2.0
						  xfbml      : true  // parse XFBML
						});

    // Additional initialization code such as adding Event Listeners goes here
  };

  // Load the SDK asynchronously
  (function(d, s, id){
     var js, fjs = d.getElementsByTagName(s)[0];
     if (d.getElementById(id)) {return;}
     js = d.createElement(s); js.id = id;
     js.src = "//connect.facebook.net/en_US/all.js";
     fjs.parentNode.insertBefore(js, fjs);
   }(document, 'script', 'facebook-jssdk'));
</script>
	<div class="table-header"> 
		<div class="tablerow" id="header-fix">
        	<!--<div class="alienrage-logo-large"></div>-->
            <div class="tableOther header">
            <div id="loginContainer">
               </div>
            </div>
        </div>
	</div>
	<div class="table-navigator"> 
		<div class="tablerow"> 
            <div class="tableNavigation user-information-bar ar-player-empire-header">
               <?
			   $sql_manager = new manage_table();
			   $account_details = $sql_manager->get_account_stats($user_ship_id);
			   
			   $user_title = "
			   <span class='ar-user-empire'>Research: </span><span class='ar-user-empire-stat'>".NUMBER($account_details['empire_research'])."</span><span class='ar-player-empire-splitter'> / </span>
			   <span class='ar-user-empire'>Food: </span><span class='ar-user-empire-stat'>".NUMBER($account_details['empire_food'])."</span><span class='ar-player-empire-splitter'> / </span>
			   <span class='ar-user-empire'>Parts: </span><span class='ar-user-empire-stat'>".NUMBER($account_details['empire_parts'])."</span><span class='ar-player-empire-splitter'> / </span>
			   <span class='ar-user-empire'>Cells: </span><span class='ar-user-empire-stat'>".NUMBER($account_details['empire_energy'])."</span><span class='ar-player-empire-splitter'> / </span>
			   <span class='ar-user-empire'>Rare Ores: </span><span class='ar-user-empire-stat'>".NUMBER($account_details['empire_ores'])."</span>
			   ";
			   echo $user_title;
			   ?>
            </div>
		</div>
	</div>
	<div class="table-content"> 
		<div class="tablerow">

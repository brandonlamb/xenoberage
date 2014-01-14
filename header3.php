<?php
// Xenobe Rage Copyright (C) 2012-2013 David Dawson
// Blacknova Traders -  Copyright (C) 2001-2012 Ron Harwood and the BNT development team
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
include "config/config.php";
if (checklogin())
{
    die();
}

$res = $db->Execute("SELECT * FROM {$db->prefix}ships WHERE email='$username'");
db_op_result ($db, $res, __LINE__, __FILE__, $db_logging);
$playerinfo = $res->fields;

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
$log_manager = new manage_log();

$default_logs_show_per_page = 20;
$total_records = $log_manager->count_log_total('ar_player_logs',$playerinfo['ship_id']);
$total_groups = ceil($total_records/$default_logs_show_per_page);



$signame = player_insignia_name ($db, $username);
$user_title = "{$signame} <span class='ar-user-handle'>{$playerinfo['character_name']}</span>{$l->get('l_aboard')} <span class='ar-user-shipname'><a href='report.php' target='_self'>{$playerinfo['ship_name']}</a></span>";

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
<script type="text/javascript" src="http://code.jquery.com/jquery-1.7.1.js"></script>
<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.16/jquery-ui.js"></script>
<link rel="stylesheet" type="text/css" href="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.17/themes/base/jquery-ui.css">
<script type="text/javascript" src="templates/alienrage/js/planet-slider.js"></script>
                <script type="text/javascript">
				$(document).ready(function() {
					var track_load = 0;
					var loading  = false;
					var total_groups = <?php echo $total_groups; ?>;
					/*
					Load first results
					*/
					$('#results').load("feed.php", {'group_no':track_load}, function() {track_load++;});
					
					/*
					When user scrolls the page/bumps the bottom we want the jquery to append more data to the page, and so on until there is no more data to load
					*/
					$(window).scroll(function() {
						
						if($(window).scrollTop() + $(window).height() == $(document).height())
						{
							/*
							More data detected
							*/
							if(track_load <= total_groups && loading==false)
							{
								loading = true;
								$('.log_loading_holder').show();
								
								/*
								Load data from the feed
								*/
								$.post('feed.php',{'group_no': track_load}, function(data){		
									$("#results").append(data);
									$('.log_loading_holder').hide();
									track_load++;
									loading = false; 
								
								}).fail(function(xhr, ajaxOptions, thrownError) {
									/*
									Catch any errors
									*/
									alert(thrownError);
									$('.log_loading_holder').hide();
									loading = false;
								
								});
								
							}
						}
					});
				});
				</script>
				<script type="text/javascript">
                    setTimeout(function() {
                      $("#hide-me").fadeOut(3000, function() { $(this).remove(); });
                    }, 3100);
                </script>
</head>
<body>
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
            <div class="tableNavigation user-information-bar ar-player-header">
               <?
			   echo $user_title;
			   ?>
            </div>
		</div>
	</div>
	<div class="table-content"> 
		<div class="tablerow">

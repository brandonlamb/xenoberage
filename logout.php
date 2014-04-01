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
// File: logout.php

include "config/config.php";
$manage_log = new manage_log();
// New database driven language entries
load_languages($db, $lang, array('logout', 'common', 'global_includes', 'global_funcs', 'combat', 'footer', 'news'), $langvars, $db_logging);

$title = $l_logout;
setcookie ("userpass", "", 0, $gamepath, $gamedomain);
setcookie ("userpass", "", 0); // Delete from default path as well.
setcookie("userID", "", 0, $gamepath, $gamedomain);
setcookie("userID", "", 0);
$_SESSION['logged_in'] = false;

// Clear the session array, clear the session cookie
$_SESSION = array();
setcookie("PHPSESSID","",0,"/");

// Destroy the session entirely
session_destroy();

include "header.php";
?>
<div class="tablecell content both-border">
	<div class="pad">
<?
$current_score = 0;

if (!isset($username))
{
    $username = '';
}
else
{
    $result = $db->Execute("SELECT * FROM {$db->prefix}ships WHERE ship_id='$user_ship_id'");
    db_op_result ($db, $result, __LINE__, __FILE__, $db_logging);
    $playerinfo = $result->fields;
    $current_score = gen_score ($playerinfo['ship_id']);
	##New Log ##
	$manage_log->player_log($playerinfo['ship_id'],2,'','','','',"<font color='#6190a5'>Low Priority</font>","<b>Logged Out</b>");
}

bigtitle ();
echo $l_logout_score . " " . $current_score . ".<br>";
$l_logout_text = str_replace("[name]", $username, $l_logout_text);
$l_logout_text = str_replace("[here]", "<a href='index.php'>" . $l_here . "</a>", $l_logout_text);
echo $l_logout_text;
?>
</div></div>
<?
include "footer.php";
?>

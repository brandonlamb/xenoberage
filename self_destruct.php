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
// File: self_destruct.php

include "config/config.php";
updatecookie();

// New database driven language entries
load_languages($db, $lang, array('self_destruct', 'ranking', 'common', 'global_includes', 'global_funcs', 'footer'), $langvars, $db_logging);

include_once "includes/cancel_bounty.php";

$title = $l_die_title;
include "header.php";

if (checklogin())
{
    die();
}
?>
<div class="tablecell content both-border">
	<div class="pad">
<?
bigtitle();

$result = $db->Execute("SELECT ship_id,character_name FROM {$db->prefix}ships WHERE  ship_id='$user_ship_id'");
db_op_result ($db, $result, __LINE__, __FILE__, $db_logging);
$playerinfo = $result->fields;

if (isset($_GET['sure']))
{
    $sure = $_GET['sure'];
}

if (!isset($sure))
{
    echo "<font color=red><strong>$l_die_rusure</strong></font><br><br>";
    echo "Please Note: You will loose all your Planets if you Self-Destruct!.<br>\n";
    echo "<a href='main.php'>$l_die_nonono</a> $l_die_what<br><br>";
    echo "<a href=self_destruct.php?sure=1>$l_yes!</a> $l_die_goodbye<br><br>";
}
elseif ($sure == 1)
{
    echo "<font color=red><strong>$l_die_check</strong></font><br><br>";
    echo "Please Note: You will loose all your Planets if you Self-Destruct!.<br>\n";
    echo "<a href='main.php'>$l_die_nonono</a> $l_die_what<br><br>";
    echo "<a href=self_destruct.php?sure=2>$l_yes!</a> $l_die_goodbye<br><br>";
}
elseif ($sure == 2)
{
    echo "$l_die_count<br>";
    echo "$l_die_vapor<br><br>";
    $l_die_please = str_replace("[logout]", "<a href='logout.php'>" . $l_logout . "</a>", $l_die_please);
    echo $l_die_please. "<br>";
    db_kill_player ($playerinfo['ship_id'], true);
    cancel_bounty ($db, $playerinfo['ship_id']);
    adminlog ($db, LOG_ADMIN_HARAKIRI, "$playerinfo[character_name]|$ip");
    playerlog ($db, $playerinfo['ship_id'], LOG_HARAKIRI, "$ip");
    echo "Due to nobody looking after your Planets, all your Planets have reduced into dust and ruble. Your Planets are no more.<br>\n";
}
else
{
    echo $l_die_exploit . "<br><br>";
}

TEXT_GOTOMAIN();
?>
</div></div>
<?
include "footer.php";
?>

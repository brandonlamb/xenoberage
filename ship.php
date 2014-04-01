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
// File: ship.php

include "config/config.php";
updatecookie();

$sql_manager = new manage_table();

$title = $l_ship_title;
include "header.php";
?>
<div class="tablecell content both-border">
	<div class="pad">
<?
if (checklogin())
{
    die();
}

$playerinfo = $sql_manager->playerinfo($user_ship_id,""); //Your Ship
$othership = $sql_manager->playerinfo($ship_id,""); //Target Ship

bigtitle();

if ($othership['sector'] != $playerinfo['sector'])
{
    echo "The <font color='#FFFFFF'>" . $othership['ship_name'] . "</font> is no longer in sector " . $playerinfo['sector'] . "<br/>";
}
else
{
	$_SESSION['ship_selected'] = $ship_id;
    echo "You see the <font color=white>" . $othership['ship_name'] . "</font>, owned by <font color=white>" . $othership['character_name'] . "</font>.<br/><br/>";
    echo "You can perform the following actions:<br/><br/>";
    echo "<a href=scan.php?ship_id=$ship_id>Scan</a><br/>";
	if($sql_manager->team_id($ship_id) == 0 ) /*You are not part of any team*/
	{
		echo "<a href=attack.php?ship_id=".$ship_id.">Attack</a><br/>";
	}
	else if ($sql_manager->team_id($ship_id) != $sql_manager->team_id($user_ship_id)) /*your both not members of the same team*/
	{
		echo "<a href=attack.php?ship_id=".$ship_id.">Attack</a><br/>";
	}
	
	echo "<a href=hack.php>Hack</a><br/>";

    echo "<a href=mailto.php?to=$ship_id>Send Message</a><br/>";
}

echo "<br/>";
TEXT_GOTOMAIN();
?>
</div></div>
<?
include "footer.php";
?>

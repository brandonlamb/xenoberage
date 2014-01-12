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

// New database driven language entries
load_languages($db, $lang, array('ship', 'planet', 'main', 'common', 'global_includes', 'global_funcs', 'footer', 'news'), $langvars, $db_logging);

include_once "includes/is_same_team.php";

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

$res = $db->Execute("SELECT team, ship_name, character_name, sector FROM {$db->prefix}ships WHERE email='$username'");
db_op_result ($db, $res, __LINE__, __FILE__, $db_logging);
$playerinfo = $res->fields;
$res2 = $db->Execute("SELECT team, ship_name, character_name, sector FROM {$db->prefix}ships WHERE ship_id=$ship_id");
db_op_result ($db, $res2, __LINE__, __FILE__, $db_logging);
$othership = $res2->fields;

bigtitle();

if ($othership['sector'] != $playerinfo['sector'])
{
    echo "$l_ship_the <font color=white>" . $othership['ship_name'] . "</font> $l_ship_nolonger " . $playerinfo['sector'] . "<br>";
}
else
{
	$_SESSION['ship_selected'] = $ship_id;
    echo "$l_ship_youc <font color=white>" . $othership['ship_name'] . "</font>, $l_ship_owned <font color=white>" . $othership['character_name'] . "</font>.<br><br>";
    echo "$l_ship_perform<br><br>";
    echo "<a href=scan.php?ship_id=$ship_id>$l_planet_scn_link</a><br>";

    if ( !is_same_team($playerinfo['team'], $othership['team']) )
    {
        echo "<a href=attack.php?ship_id=$ship_id>$l_planet_att_link</a><br>";
    }

    echo "<a href=mailto.php?to=$ship_id>$l_send_msg</a><br>";
}

echo "<br>";
TEXT_GOTOMAIN();
?>
</div></div>
<?
include "footer.php";
?>

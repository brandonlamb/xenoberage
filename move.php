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
// File: move.php

include "config/config.php";
updatecookie ();

// New database driven language entries
load_languages($db, $lang, array('move', 'common', 'global_includes', 'global_funcs', 'combat', 'footer', 'news'), $langvars, $db_logging);

$title = $l_move_title;
include "header.php";
?>
<div class="tablecell content both-border">
	<div class="pad">
<?
// Check to see if the user is logged in
if (checklogin () )
{
    die ();
}

// Retrieve the user and ship information
$result = $db->Execute ("SELECT * FROM {$db->prefix}ships WHERE email='$username'");
db_op_result ($db, $result, __LINE__, __FILE__, $db_logging);

// Put the player information into the array: "playerinfo"
$playerinfo = $result->fields;

// Check to see if the player has less than one turn available
// and if so return to the main menu
if ($playerinfo['turns'] < 1)
{
    echo $l_move_turn . '<br><br>';
    TEXT_GOTOMAIN ();
	?>
</div></div>
<?
    include "footer.php";
    die ();
}

// Retrieve all the sector information about the current sector
$result2 = $db->Execute ("SELECT * FROM {$db->prefix}universe WHERE sector_id='$playerinfo[sector]'");
db_op_result ($db, $result2, __LINE__, __FILE__, $db_logging);

// Put the sector information into the array "sectorinfo"
$sectorinfo = $result2->fields;

$sector = null;
if (array_key_exists('sector', $_REQUEST) == true)
{
    $sector = $_REQUEST['sector'];
}

// Retrive all the warp links out of the current sector
$result3 = $db->Execute ("SELECT * FROM {$db->prefix}links WHERE link_start='$playerinfo[sector]'");
db_op_result ($db, $result3, __LINE__, __FILE__, $db_logging);
$i = 0;
$flag = 0;

// Loop through the available warp links to make sure it's a valid move
while (!$result3->EOF)
{
    $row = $result3->fields;
    if ($row['link_dest'] == $sector && $row['link_start'] == $playerinfo['sector'])
    {
        $flag = 1;
    }
    $i++;
    $result3->MoveNext();
}

// Check if there was a valid warp link to move to
if ($flag == 1)
{
    $ok = 1;
    $calledfrom = "move.php";
    include_once "check_fighters.php";
    if ($ok > 0)
    {
        $stamp = date("Y-m-d H-i-s");
        $query = "UPDATE {$db->prefix}ships SET last_login='$stamp',turns=turns-1, turns_used=turns_used+1, sector=$sector WHERE ship_id=$playerinfo[ship_id]";
        log_move ($db, $playerinfo['ship_id'], $sector);
        $move_result = $db->Execute ("$query");
        db_op_result ($db, $move_result, __LINE__, __FILE__, $db_logging);
        if (!$move_result)
        {
            // is this really STILL needed?
            $error = $db->ErrorMsg();
            mail ($admin_mail,"Move Error", "Start Sector: $sectorinfo[sector_id]\nEnd Sector: $sector\nPlayer: $playerinfo[character_name] - $playerinfo[ship_id]\n\nQuery:  $query\n\nSQL error: $error");
        }
    }
    // Enter code for checking dangers in new sector
    include_once "check_mines.php";
    if ($ok == 1)
    {
        header("Location: main.php");
    }
    else
    {
        TEXT_GOTOMAIN ();
    }
}
else
{
    echo $l_move_failed . '<br><br>';
    $resx = $db->Execute("UPDATE {$db->prefix}ships SET cleared_defences=' ' WHERE ship_id=$playerinfo[ship_id]");
    db_op_result ($db, $resx, __LINE__, __FILE__, $db_logging);
    TEXT_GOTOMAIN ();
}

echo "</body></html>";
?>

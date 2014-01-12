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
// File: sched_tow.php

if (preg_match("/sched_tow.php/i", $_SERVER['PHP_SELF']))
{
    echo "You can not access this file directly!";
    die();
}
include "config/config.php";
$manage_log = new manage_log();
echo "<strong>ZONES</strong><br><br>";
echo "Towing bigger players out of restricted zones...";
$num_to_tow = 0;
do
{
    $res = $db->Execute("SELECT ship_id,character_name,hull,sector,{$db->prefix}universe.zone_id,max_hull FROM {$db->prefix}ships,{$db->prefix}universe,{$db->prefix}zones WHERE sector=sector_id AND {$db->prefix}universe.zone_id={$db->prefix}zones.zone_id AND max_hull<>0 AND (({$db->prefix}ships.hull + {$db->prefix}ships.engines + {$db->prefix}ships.computer + {$db->prefix}ships.beams + {$db->prefix}ships.torp_launchers + {$db->prefix}ships.shields + {$db->prefix}ships.armor)/7) >max_hull AND ship_destroyed='N'");
    db_op_result ($db, $res, __LINE__, __FILE__, $db_logging);
    if ($res)
    {
        $num_to_tow = $res->RecordCount();
        echo "<br>$num_to_tow players to tow:<br>";
        while (!$res->EOF)
        {
            $row = $res->fields;
            echo "...towing $row[character_name] out of $row[sector] ...";
            $newsector = mt_rand(0, $sector_max-1);
            echo " to sector $newsector.<br>";
            $query = $db->Execute("UPDATE {$db->prefix}ships SET sector=$newsector,cleared_defences=' ' WHERE ship_id=$row[ship_id]");
            db_op_result ($db, $query, __LINE__, __FILE__, $db_logging);

			##New Log ##
			$manage_log->player_log($row['ship_id'],15,$row['sector'],$newsector,'','notrack',"<font color='#6190a5'>Low Priority</font>","<b>Information</b>");
            log_move ($db, $row['ship_id'], $newsector);
            $res->MoveNext();
        }
    }
    else
    {
        echo "<br>No players to tow.<br>";
    }
} while ($num_to_tow);

$num_to_tow = 0;
do
{
    $res = $db->Execute("SELECT ship_id,character_name,hull,sector,{$db->prefix}universe.zone_id,max_hull FROM {$db->prefix}ships,{$db->prefix}universe,{$db->prefix}zones WHERE sector=sector_id AND {$db->prefix}universe.zone_id={$db->prefix}zones.zone_id AND max_hull<>0 AND {$db->prefix}ships.score>".$fed_max_score." AND ship_destroyed='N'");
    db_op_result ($db, $res, __LINE__, __FILE__, $db_logging);
    if ($res)
    {
        $num_to_tow = $res->RecordCount();
        echo "<br>$num_to_tow players to tow:<br>";
        while (!$res->EOF)
        {
            $row = $res->fields;
            echo "...towing $row[character_name] out of $row[sector] ...";
            $newsector = mt_rand(0, $sector_max-1);
            echo " to sector $newsector.<br>";
            $query = $db->Execute("UPDATE {$db->prefix}ships SET sector=$newsector,cleared_defences=' ' WHERE ship_id=$row[ship_id]");
            db_op_result ($db, $query, __LINE__, __FILE__, $db_logging);
            ##New Log ##
			$manage_log->player_log($row['ship_id'],16,$row['sector'],$newsector,'','notrack',"<font color='#6190a5'>Low Priority</font>","<b>Information</b>");
            log_move ($db, $row['ship_id'], $newsector);
            $res->MoveNext();
        }
    }
    else
    {
        echo "<br>No players to tow.<br>";
    }
} while ($num_to_tow);

echo "<br>";
$multiplier = 0; // No need to run this again
?>

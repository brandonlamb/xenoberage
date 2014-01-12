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
// File: sched_planets.php

if (preg_match("/sched_planets.php/i", $_SERVER['PHP_SELF']))
{
    echo "You can not access this file directly!";
    die();
}
include "config/config.php";
$manage_log = new manage_log();
echo "<strong>PLANETS</strong><p>";

$res = $db->Execute("SELECT * FROM {$db->prefix}planets WHERE owner >0;");
db_op_result ($db, $res, __LINE__, __FILE__, $db_logging);
// Using Planet Update Code from BNT version 0.36 due to code bugs.
// We are now using transactions to off load the SQL stuff in full to the Database Server.

$result = $db->Execute("START TRANSACTION;");
db_op_result ($db, $result, __LINE__, __FILE__, $db_logging);
while (!$res->EOF)
{
    $row = $res->fields;
    $production = floor(min($row['colonists'], $colonist_limit) * $colonist_production_rate);
    $organics_production = floor($production * $organics_prate * $row['prod_organics'] / 100.0);// - ($production * $organics_consumption);
    $organics_production -= floor($production * $organics_consumption);

    if ($row['organics'] + $organics_production < 0)
    {
        $organics_production = -$row['organics'];
        $starvation = floor($row['colonists'] * $starvation_death_rate);
        if ($row['owner'] && $starvation >= 1)
        {
			##New Log ##
			$manage_log->player_log($row['owner'],14,$row['name'],$row['sector_id'],$starvation,'notrack',"<font color='#6190a5'>Low Priority</font>","<b>Information</b>");
        }
    }
    else
    {
        $starvation = 0;
    }

    $ore_production = floor($production * $ore_prate * $row['prod_ore'] / 100.0);
    $goods_production = floor($production * $goods_prate * $row['prod_goods'] / 100.0);
    $energy_production = floor($production * $energy_prate * $row['prod_energy'] / 100.0);
    $reproduction = floor(($row['colonists'] - $starvation) * $colonist_reproduction_rate);

    if (($row['colonists'] + $reproduction - $starvation) > $colonist_limit)
    {
        $reproduction = $colonist_limit - $row['colonists'];
    }

    $total_percent = $row['prod_organics'] + $row['prod_ore'] + $row['prod_goods'] + $row['prod_energy'];

    if ($row['owner'])
    {
        $fighter_production = floor($production * $fighter_prate * $row['prod_fighters'] / 100.0);
        $torp_production = floor($production * $torpedo_prate * $row['prod_torp'] / 100.0);
        $total_percent += $row['prod_fighters'] + $row['prod_torp'];
    }
    else
    {
        $fighter_production = 0;
        $torp_production = 0;
    }

    $credits_production = floor($production * $credits_prate * (100.0 - $total_percent) / 100.0);
    $SQL = "UPDATE {$db->prefix}planets SET organics = organics + $organics_production, ore = ore + $ore_production, goods = goods + $goods_production, energy = energy + $energy_production, colonists = colonists + $reproduction-$starvation, torps = torps + $torp_production, fighters = fighters + $fighter_production, credits = credits * $interest_rate + $credits_production WHERE planet_id=$row[planet_id] LIMIT 1; ";
    $ret = $db->Execute($SQL);
    db_op_result ($db, $ret, __LINE__, __FILE__, $db_logging);
    $res->MoveNext();
}

$ret = $db->Execute("COMMIT;");
db_op_result ($db, $ret, __LINE__, __FILE__, $db_logging);
global $sched_planet_valid_credits;
if ($sched_planet_valid_credits == true)
{
    $ret = $db->Execute("UPDATE {$db->prefix}planets SET credits = $max_credits_without_base WHERE credits > $max_credits_without_base AND base = 'N'");
    db_op_result ($db, $ret, __LINE__, __FILE__, $db_logging);
}

echo "Planets updated.<br><br>";
echo "<br>";

?>

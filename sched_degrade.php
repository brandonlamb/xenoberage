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
// File: sched_degrade.php

if (preg_match("/sched_degrade.php/i", $_SERVER['PHP_SELF']))
{
    echo "You can not access this file directly!";
    die();
}
include "config/config.php";
$manage_log = new manage_log();
echo "<strong>Degrading Sector Fighters with no friendly base</strong><br><br>";
$res = $db->Execute("SELECT * FROM {$db->prefix}sector_defence WHERE defence_type = 'F'");
db_op_result ($db, $res, __LINE__, __FILE__, $db_logging);

while (!$res->EOF)
{
    $row = $res->fields;
    $res3 = $db->Execute ("SELECT * FROM {$db->prefix}ships WHERE ship_id = ?;", array($row['ship_id']));
    db_op_result ($db, $res3, __LINE__, __FILE__, $db_logging);
    $sched_playerinfo = $res3->fields;
    $res2 = $db->Execute ("SELECT * FROM {$db->prefix}planets WHERE (owner = ? OR (corp = ? AND ? <> 0)) AND sector_id = ? AND energy > 0;", array($row['ship_id'], $sched_playerinfo['team'], $sched_playerinfo['team'], $row['sector_id']));
    db_op_result ($db, $res2, __LINE__, __FILE__, $db_logging);
    if ($res2->EOF)
    {
        $resa = $db->Execute ("UPDATE {$db->prefix}sector_defence SET quantity = quantity - GREATEST(ROUND(quantity * ?),1) WHERE defence_id = ? AND quantity > 0;", array($defence_degrade_rate, $row['defence_id']));
        db_op_result ($db, $resa, __LINE__, __FILE__, $db_logging);
        $degrade_rate = $defence_degrade_rate * 100;
		##New Log ##
		$manage_log->player_log($row['ship_id'],13,$row['sector_id'],$degrade_rate,'','notrack',"<font color='#E9AB17'>Medium Priority</font>",'<b><font color="#FF0000">Warning</font></b>');
    }
    else
    {
        $energy_required = ROUND($row['quantity'] * $energy_per_fighter);
        $res4 = $db->Execute ("SELECT IFNULL(SUM(energy),0) AS energy_available FROM {$db->prefix}planets WHERE (owner = ? OR (corp = ? AND ? <> 0)) AND sector_id = ?;", array($row['ship_id'], $sched_playerinfo['team'], $sched_playerinfo['team'], $row['sector_id']));
        db_op_result ($db, $res4, __LINE__, __FILE__, $db_logging);
        $planet_energy = $res4->fields;
        $energy_available = $planet_energy['energy_available'];
        echo "available $energy_available, required $energy_required.";
        if ($energy_available > $energy_required)
        {
            while (!$res2->EOF)
            {
                $degrade_row = $res2->fields;
                $resb = $db->Execute ("UPDATE {$db->prefix}planets SET energy = energy - GREATEST(ROUND(? * (energy / ?)),1)  WHERE planet_id = ?;", array($energy_required, $energy_available, $degrade_row['planet_id']));
                db_op_result ($db, $resb, __LINE__, __FILE__, $db_logging);
                $res2->MoveNext();
            }
        }
        else
        {
            $resc = $db->Execute ("UPDATE {$db->prefix}sector_defence SET quantity = quantity - GREATEST(ROUND(quantity * ?),1) WHERE defence_id = ?;", array($defence_degrade_rate, $row['defence_id']));
            db_op_result ($db, $resc, __LINE__, __FILE__, $db_logging);
            $degrade_rate = $defence_degrade_rate * 100;
			##New Log ##
			$manage_log->player_log($row['ship_id'],13,$row['sector_id'],$degrade_rate,'','notrack',"<font color='#E9AB17'>Medium Priority</font>",'<b><font color="#FF0000">Warning</font></b>');
        }
    }
    $res->MoveNext();
}
$resx = $db->Execute("DELETE FROM {$db->prefix}sector_defence WHERE quantity <= 0");
db_op_result ($db, $resx, __LINE__, __FILE__, $db_logging);
?>

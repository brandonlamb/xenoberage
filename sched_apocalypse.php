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
// File: sched_apocalypse.php

if (preg_match("/sched_apocalypse.php/i", $_SERVER['PHP_SELF']))
{
    echo "You can not access this file directly!";
    die();
}
include "config/config.php";
$manage_log = new manage_log();
echo "<strong>Pirate Raid</strong><br><br>";
$pirate_day = $db->Execute("SELECT * FROM {$db->prefix}planets WHERE credits > $planet_max_credits");
db_op_result ($db, $pirate_day, __LINE__, __FILE__, $db_logging);
$chance = 9;
$reccount = $pirate_day->RecordCount();
if ($reccount > 200)
{
  	$chance = 7; // increase chance it will happen if we have lots of planets meeting the criteria
}
$affliction = mt_rand(1,$chance); // the chance something bad will happen
if ($pirate_day && $affliction < 3 && $reccount > 0)
{
	
    $i=1;
    $targetnum = mt_rand(1,$reccount);
    while (!$pirate_day->EOF)
    {
        if ($i==$targetnum)
        {echo "loooool";
            $targetinfo=$pirate_day->fields;
            break;
        }
        $i++;
        $pirate_day->MoveNext();
    }
	
    if ($affliction == 1)
    {
		/*pirates launch raid on your bank!*/
        echo "Pirates raided planet ".$targetinfo['planet_id']."!!<br/>.";
		$pirates_steal = rand(1,20)/100;
        $resx = $db->Execute("UPDATE {$db->prefix}planets SET credits = ROUND(credits-credits*$pirates_steal) WHERE planet_id = $targetinfo[planet_id]");
        db_op_result ($db, $resx, __LINE__, __FILE__, $db_logging);
		$stolen_credits = NUMBER($targetinfo['credits']*$pirates_steal);
		##New Log ##
		$manage_log->player_log($targetinfo['owner'],29,$targetinfo['name'],$targetinfo['sector_id'],$stolen_credits,'notrack',"<font color='#E9AB17'>Medium Priority</font>",'<b><font color="#FF0000">Warning</font></b>');
    }
}
echo "<br/>";


##################################################################################################################
echo "<strong>ZOMBIE APOCALYPSE</strong><br><br>";
$doomsday = $db->Execute("SELECT * FROM {$db->prefix}planets WHERE colonists > $doomsday_value");
db_op_result ($db, $doomsday, __LINE__, __FILE__, $db_logging);
$chance = 9;
$reccount = $doomsday->RecordCount();
if ($reccount > 200)
{
  	$chance = 7; // increase chance it will happen if we have lots of planets meeting the criteria
}
$affliction = mt_rand(1,$chance); // the chance something bad will happen
if ($doomsday && $affliction < 3 && $reccount > 0)
{
    $i=1;
    $targetnum = mt_rand(1,$reccount);
    while (!$doomsday->EOF)
    {
        if ($i==$targetnum)
        {
            $targetinfo=$doomsday->fields;
            break;
        }
        $i++;
        $doomsday->MoveNext();
    }
	
    if ($affliction == 1) // Space Plague
    {
         echo "Zombie virus detected on planet ".$targetinfo['planet_id']."!!<br/>.";
        $resx = $db->Execute("UPDATE {$db->prefix}planets SET colonists = ROUND(colonists-colonists*$space_plague_kills) WHERE planet_id = $targetinfo[planet_id]");
        db_op_result ($db, $resx, __LINE__, __FILE__, $db_logging);
        $logpercent = ROUND($space_plague_kills * 100);
		##New Log ##
		$manage_log->player_log($targetinfo['owner'],11,$targetinfo['name'],$targetinfo['sector_id'],$logpercent,'notrack',"<font color='#6190a5'>Low Priority</font>",'<b><font color="#FF0000">Warning</font></b>');
    }
    else
    {
        echo "Coronal mass ejection detected!<br>.";
        $resy = $db->Execute("UPDATE {$db->prefix}planets SET energy = 0 WHERE planet_id = $targetinfo[planet_id]");
        db_op_result ($db, $resy, __LINE__, __FILE__, $db_logging);
		##New Log ##
		$manage_log->player_log($targetinfo['owner'],12,$targetinfo['name'],$targetinfo['sector_id'],'','notrack',"<font color='#6190a5'>Low Priority</font>",'<b><font color="#FF0000">Warning</font></b>');
    }
}
echo "<br>";
?>

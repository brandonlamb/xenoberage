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
// File: sched_xenobe.php

if (preg_match("/sched_xenobe.php/i", $_SERVER['PHP_SELF']))
{
    echo "You can not access this file directly!";
    die();
}
require_once($_SERVER['DOCUMENT_ROOT']."/config/config.php");
$process_xenobes = new manage_xenobe();
echo $process_xenobes->process_xenobes();
/*
include "config/config.php";
// Xenobe turn updates
echo "<br><strong>Xenobe TURNS</strong><br><br>";

// Include functions
include_once "includes/xenobe_hunter.php";
include_once "includes/xenobe_move.php";
include_once "includes/xenobe_regen.php";
include_once "includes/xenobe_to_sec_def.php";
include_once "includes/xenobe_to_ship.php";
include_once "includes/xenobe_trade.php";
include_once "includes/xenobe_to_planet.php";

// New database driven language entries
load_languages($db, $lang, array('sched_xenobe', 'common', 'global_includes', 'combat', 'footer', 'news'), $langvars, $db_logging);

global $targetlink;
global $xenobeisdead;

// Make Xenobe selection
$furcount = $furcount0 = $furcount0a = $furcount1 = $furcount1a = $furcount2 = $furcount2a = $furcount3 = $furcount3a = $furcount3h = 0;

// Lock the tables
$resa = $db->Execute("LOCK TABLES {$db->prefix}xenobe WRITE, {$db->prefix}ships WRITE;");
db_op_result ($db, $resa, __LINE__, __FILE__, $db_logging);

$res = $db->Execute("SELECT * FROM {$db->prefix}ships JOIN {$db->prefix}xenobe WHERE email=xenobe_id and active='Y' and ship_destroyed='N' ORDER BY ship_id;");
db_op_result ($db, $res, __LINE__, __FILE__, $db_logging);



while (!$res->EOF)
{
    $xenobeisdead = 0;
    $playerinfo = $res->fields;
    // Regenerate / Buy stats
	
    xenoberegen();

    // Run through orders
    $furcount++;
    if (mt_rand(1,5) > 1)                                 // 20% Chance of not moving at all
    {
      // Orders = 0 Sentinel
      if ($playerinfo[orders] == 0)
      {
		  echo "<br/><b>Sentinal</b></BR>";
        $furcount0++;
        // Find a target in my sector, not myself, not on a planet

        $SQL = "SELECT * FROM {$db->prefix}ships WHERE sector=$playerinfo[sector] AND email!='$playerinfo[email]' AND email NOT LIKE '%@xenobe' AND planet_id=0 AND ship_id > 1";
        $reso0 = $db->Execute($SQL);
        db_op_result ($db, $res0, __LINE__, __FILE__, $db_logging);
        if (!$reso0->EOF)
        {
			
          $rowo0 = $reso0->fields;
          if ($playerinfo[aggression] == 0)            // O = 0 & Aggression = 0 Peaceful
          {
            // This Guy Does Nothing But Sit As A Target Himself
          }
          elseif ($playerinfo[aggression] == 1)        // O = 0 & Aggression = 1 Attack sometimes O = 0
          {
            // Xenobe's only compare number of fighters when determining if they have an attack advantage
            if ($playerinfo[ship_fighters] > $rowo0[ship_fighters])
            {
              $furcount0a++;
              playerlog ($db, $playerinfo[ship_id], LOG_Xenobe_ATTACK, "$rowo0[character_name]");
              xenobetoship($rowo0[ship_id]);
              if ($xenobeisdead>0) {
                $res->MoveNext();
                continue;
              }
            }
          }
          elseif ($playerinfo[aggression] == 2)        // O = 0 & Aggression = 2 attack always
          {
            $furcount0a++;
            playerlog ($db, $playerinfo[ship_id], LOG_Xenobe_ATTACK, "$rowo0[character_name]");
            xenobetoship($rowo0[ship_id]);
            if ($xenobeisdead>0) {
              $res->MoveNext();
              continue;
            }
          }
        }
      }
      // Orders = 1 roam
      elseif ($playerinfo[orders] == 1)
      {
		  echo "<br/><b>Roaming</b></BR>";
        $furcount1++;
        // Roam to a new sector before doing anything else
        $targetlink = $playerinfo[sector];
        xenobemove($playerinfo, $sector_max, $targetlink, $xenobeisdead, $db);
        if ($xenobeisdead>0) {
          $res->MoveNext();
          continue;
        }
        // Find a target in my sector, not myself
        $reso1 = $db->Execute("SELECT * FROM {$db->prefix}ships WHERE sector=$targetlink and email!='$playerinfo[email]' and ship_id > 1");
        db_op_result ($db, $reso1, __LINE__, __FILE__, $db_logging);
        if (!$reso1->EOF)
        {
          $rowo1= $reso1->fields;
          if ($playerinfo[aggression] == 0)            // O = 1 & Aggression = 0 Peaceful O = 1
          {
            // This Guy Does Nothing But Roam Around As A Target Himself
          }
          elseif ($playerinfo[aggression] == 1)        // O = 1 & AGRESSION = 1 ATTACK SOMETIMES
          {
            // Xenobe's only compare number of fighters when determining if they have an attack advantage
            if ($playerinfo[ship_fighters] > $rowo1[ship_fighters] && $rowo1[planet_id] == 0)
            {
              $furcount1a++;
              playerlog ($db, $playerinfo[ship_id], LOG_Xenobe_ATTACK, "$rowo1[character_name]");
              xenobetoship($rowo1[ship_id]);
              if ($xenobeisdead>0) {
                $res->MoveNext();
                continue;
              }
            }
          }
          elseif ($playerinfo[aggression] == 2)        //  O = 1 & AGRESSION = 2 ATTACK ALLWAYS
          {
            $furcount1a++;
            playerlog ($db, $playerinfo[ship_id], LOG_Xenobe_ATTACK, "$rowo1[character_name]");
            if (!$rowo1[planet_id] == 0) {              // IS ON PLANET
              xenobetoplanet($rowo1[planet_id]);
            } else {
              xenobetoship($rowo1[ship_id]);
            }
            if ($xenobeisdead>0) {
              $res->MoveNext();
              continue;
            }
          }
        }
      }
      // ORDERS = 2 ROAM AND TRADE
      elseif ($playerinfo[orders] == 2)
      {
		  echo "<br/><b>Trading</b></BR>";
        $furcount2++;
        // ROAM TO A NEW SECTOR BEFORE DOING ANYTHING ELSE
        $targetlink = $playerinfo[sector];
        xenobemove($playerinfo, $sector_max, $targetlink, $xenobeisdead, $db);
        if ($xenobeisdead>0) {
          $res->MoveNext();
          continue;
        }
        // NOW TRADE BEFORE WE DO ANY AGGRESSION CHECKS
        xenobetrade();
        // FIND A TARGET
        // IN MY SECTOR, NOT MYSELF
        $reso2 = $db->Execute("SELECT * FROM {$db->prefix}ships WHERE sector=$targetlink and email!='$playerinfo[email]' and ship_id > 1");
        db_op_result ($db, $reso2, __LINE__, __FILE__, $db_logging);
        if (!$reso2->EOF)
        {
          $rowo2=$reso2->fields;
          if ($playerinfo[aggression] == 0)            // O = 2 & AGRESSION = 0 PEACEFUL
          {
            // This Guy Does Nothing But Roam And Trade
          }
          elseif ($playerinfo[aggression] == 1)        // O = 2 & AGRESSION = 1 ATTACK SOMETIMES 
          {
            // Xenobe's only compare number of fighters when determining if they have an attack advantage
            if ($playerinfo[ship_fighters] > $rowo2[ship_fighters] && $rowo2[planet_id] == 0)
            {
              $furcount2a++;
              playerlog ($db, $playerinfo[ship_id], LOG_Xenobe_ATTACK, "$rowo2[character_name]");
              xenobetoship($rowo2[ship_id]);
              if ($xenobeisdead>0) {
                $res->MoveNext();
                continue;
              }
            }
          }
          elseif ($playerinfo[aggression] == 2)        // O = 2 & AGRESSION = 2 ATTACK ALLWAYS
          {
            $furcount2a++;
            playerlog ($db, $playerinfo[ship_id], LOG_Xenobe_ATTACK, "$rowo2[character_name]");
            if (!$rowo2[planet_id] == 0) {              // IS ON PLANET
              xenobetoplanet($rowo2[planet_id]);
            } else {
              xenobetoship($rowo2[ship_id]);
            }
            if ($xenobeisdead>0) {
              $res->MoveNext();
              continue;
            }
          }
        }
      }
      // ORDERS = 3 ROAM AND HUNT
      elseif ($playerinfo[orders] == 3)
      {
		  echo "<br/><b>Roam And Hunting</b></BR>";
        $furcount3++;
        // LET SEE IF WE GO HUNTING THIS ROUND BEFORE WE DO ANYTHING ELSE
        $hunt=mt_rand(0,3);                               // 25% CHANCE OF HUNTING
        // Uncomment below for Debugging
        //$hunt=0;
        if ($hunt==0)
        {
			echo "wtf";
        $furcount3h++;
        
		#DISABLED XENOBE HUNTER
		
		//xenobehunter($playerinfo, $targetlink, $xenobeisdead, $db);
        if ($xenobeisdead>0) {
			echo "im here";
          $res->MoveNext();
          continue;
        }
        } 
		else
        {
          // ROAM TO A NEW SECTOR BEFORE DOING ANYTHING ELSE
		  echo "Doing Somthing Else...<br/>";
		  $targetlink = $playerinfo[sector];
          xenobemove($playerinfo, $sector_max, $targetlink, $xenobeisdead, $db);
          if ($xenobeisdead>0) {
            $res->MoveNext();
            continue;
          }
          // FIND A TARGET
          // IN MY SECTOR, NOT MYSELF
          $reso3 = $db->Execute("SELECT * FROM {$db->prefix}ships WHERE sector=$playerinfo[sector] and email!='$playerinfo[email]' and ship_id > 1");
          db_op_result ($db, $reso3, __LINE__, __FILE__, $db_logging);
          if (!$reso3->EOF)
          {
            $rowo3=$reso3->fields;
            if ($playerinfo[aggression] == 0)            // O = 3 & AGRESSION = 0 PEACEFUL
            {
              // This Guy Does Nothing But Roam Around As A Target Himself
            }
            elseif ($playerinfo[aggression] == 1)        // O = 3 & AGRESSION = 1 ATTACK SOMETIMES
            {
              // Xenobe's only compare number of fighters when determining if they have an attack advantage
              if ($playerinfo[ship_fighters] > $rowo3[ship_fighters] && $rowo3[planet_id] == 0)
              {
                $furcount3a++;
                playerlog ($db, $playerinfo[ship_id], LOG_Xenobe_ATTACK, "$rowo3[character_name]");
                xenobetoship($rowo3[ship_id]);
                if ($xenobeisdead>0) {
                  $res->MoveNext();
                  continue;
                }
              }
            }
            elseif ($playerinfo[aggression] == 2)        // O = 3 & AGRESSION = 2 ATTACK ALLWAYS
            {
              $furcount3a++;
              playerlog ($db, $playerinfo[ship_id], LOG_Xenobe_ATTACK, "$rowo3[character_name]");
              if (!$rowo3[planet_id] == 0) {              // IS ON PLANET
                xenobetoplanet($rowo3[planet_id]);
              } else {
                xenobetoship($rowo3[ship_id]);
              }
              if ($xenobeisdead>0) {
                $res->MoveNext();
                continue;
              }
            }
          }
        }
      }
    }
    $res->MoveNext();
  }
  $res->_close();

  $furnonmove = $furcount - ($furcount0 + $furcount1 + $furcount2 + $furcount3);
  echo "Counted $furcount Xenobe players that are ACTIVE with working ships.<br>";
  echo "$furnonmove Xenobe players did not do anything this round. <br>";
  echo "$furcount0 Xenobe players had SENTINEL orders of which $furcount0a launched attacks. <br>";
  echo "$furcount1 Xenobe players had ROAM orders of which $furcount1a launched attacks. <br>";
  echo "$furcount2 Xenobe players had ROAM AND TRADE orders of which $furcount2a launched attacks. <br>";
  echo "$furcount3 Xenobe players had ROAM AND HUNT orders of which $furcount3a launched attacks and $furcount3h went hunting. <br>";
  echo "Xenobe TURNS COMPLETE. <br>";
  echo "<br>";
  // END OF Xenobe TURNS

// Unlock the tables.
$result = $db->Execute("UNLOCK TABLES;");
db_op_result ($db, $result, __LINE__, __FILE__, $db_logging);
*/
?>

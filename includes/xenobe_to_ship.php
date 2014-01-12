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
// File: xenobetoship.php

function xenobetoship($ship_id)
{
  // SETUP GENERAL VARIABLES
  global $attackerbeams;
  global $attackerfighters;
  global $attackershields;
  global $attackertorps;
  global $attackerarmor;
  global $attackertorpdamage;
  global $start_energy;
  global $playerinfo;
  global $rating_combat_factor;
  global $upgrade_cost;
  global $upgrade_factor;
  global $sector_max;
  global $xenobeisdead;
  global $db, $db_logging;

  // LOOKUP TARGET DETAILS
  $resa = $db->Execute("LOCK TABLES {$db->prefix}ships WRITE, {$db->prefix}universe WRITE, {$db->prefix}zones READ, {$db->prefix}planets READ, {$db->prefix}news WRITE, {$db->prefix}logs WRITE");
  db_op_result ($db, $resa, __LINE__, __FILE__, $db_logging);
  $resultt = $db->Execute ("SELECT * FROM {$db->prefix}ships WHERE ship_id='$ship_id'");
  db_op_result ($db, $resultt, __LINE__, __FILE__, $db_logging);
  $targetinfo=$resultt->fields;

  // VERIFY NOT ATTACKING ANOTHER XENOBE
    // Added because the xenobe were killing each other off
  if (strstr($targetinfo[email], '@xenobe'))                       // He's a xenobe
        {
        $resb = $db->Execute("UNLOCK TABLES");
        db_op_result ($db, $resb, __LINE__, __FILE__, $db_logging);

    return;
        }

  // VERIFY SECTOR ALLOWS ATTACK
  $sectres = $db->Execute ("SELECT sector_id,zone_id FROM {$db->prefix}universe WHERE sector_id='$targetinfo[sector]'");
  db_op_result ($db, $sectres, __LINE__, __FILE__, $db_logging);
  $sectrow = $sectres->fields;
  $zoneres = $db->Execute ("SELECT zone_id,allow_attack FROM {$db->prefix}zones WHERE zone_id=$sectrow[zone_id]");
  db_op_result ($db, $zoneres, __LINE__, __FILE__, $db_logging);
  $zonerow = $zoneres->fields;
  if ($zonerow[allow_attack]=="N")                        //  DEST LINK MUST ALLOW ATTACKING
  {
    playerlog ($db, $playerinfo[ship_id], LOG_RAW, "Attack failed, you are in a sector that prohibits attacks.");

    return;
  }

  // USE EMERGENCY WARP DEVICE
  if ($targetinfo[dev_emerwarp]>0)
  {
    playerlog ($db, $targetinfo[ship_id], LOG_ATTACK_EWD, "Xenobe $playerinfo[character_name]");
    $dest_sector=mt_rand(0,$sector_max);
    $result_warp = $db->Execute ("UPDATE {$db->prefix}ships SET sector=$dest_sector, dev_emerwarp=dev_emerwarp-1 WHERE ship_id=$targetinfo[ship_id]");
    db_op_result ($db, $result_warp, __LINE__, __FILE__, $db_logging);

    return;
  }

  // SETUP ATTACKER VARIABLES
  $attackerbeams = NUM_BEAMS($playerinfo[beams]);
  if ($attackerbeams > $playerinfo[ship_energy]) $attackerbeams = $playerinfo[ship_energy];
  $playerinfo[ship_energy] = $playerinfo[ship_energy] - $attackerbeams;
  $attackershields = NUM_SHIELDS($playerinfo[shields]);
  if ($attackershields > $playerinfo[ship_energy]) $attackershields = $playerinfo[ship_energy];
  $playerinfo[ship_energy] = $playerinfo[ship_energy] - $attackershields;
  $attackertorps = round(pow ($level_factor, $playerinfo['torp_launchers'])) * 2;
  if ($attackertorps > $playerinfo[torps]) $attackertorps = $playerinfo[torps];
  $playerinfo[torps] = $playerinfo[torps] - $attackertorps;
  $attackertorpdamage = $torp_dmg_rate * $attackertorps;
  $attackerarmor = $playerinfo[armor_pts];
  $attackerfighters = $playerinfo[ship_fighters];
  $playerdestroyed = 0;

  // SETUP TARGET VARIABLES
  $targetbeams = NUM_BEAMS($targetinfo[beams]);
  if ($targetbeams>$targetinfo[ship_energy]) $targetbeams=$targetinfo[ship_energy];
  $targetinfo[ship_energy]=$targetinfo[ship_energy]-$targetbeams;
  $targetshields = NUM_SHIELDS($targetinfo[shields]);
  if ($targetshields>$targetinfo[ship_energy]) $targetshields=$targetinfo[ship_energy];
  $targetinfo[ship_energy]=$targetinfo[ship_energy]-$targetshields;
  $targettorpnum = round(pow ($level_factor, $targetinfo['torp_launchers']))*2;
  if ($targettorpnum > $targetinfo[torps]) $targettorpnum = $targetinfo[torps];
  $targetinfo[torps] = $targetinfo[torps] - $targettorpnum;
  $targettorpdmg = $torp_dmg_rate*$targettorpnum;
  $targetarmor = $targetinfo[armor_pts];
  $targetfighters = $targetinfo[ship_fighters];
  $targetdestroyed = 0;

  // BEGIN COMBAT PROCEDURES
  if ($attackerbeams > 0 && $targetfighters > 0)
  {                         // ATTACKER HAS BEAMS - TARGET HAS FIGHTERS - BEAMS VS FIGHTERS
    if ($attackerbeams > round($targetfighters / 2))
    {                                  // ATTACKER BEAMS GT HALF TARGET FIGHTERS
      $lost = $targetfighters-(round($targetfighters/2));
      $targetfighters = $targetfighters-$lost;                 // T LOOSES HALF ALL FIGHTERS
      $attackerbeams = $attackerbeams-$lost;                   // A LOOSES BEAMS EQ TO HALF T FIGHTERS
    } else
    {                                  // ATTACKER BEAMS LE HALF TARGET FIGHTERS
      $targetfighters = $targetfighters-$attackerbeams;        // T LOOSES FIGHTERS EQ TO A BEAMS
      $attackerbeams = 0;                                      // A LOOSES ALL BEAMS
    }
  }
  if ($attackerfighters > 0 && $targetbeams > 0)
  {                         // TARGET HAS BEAMS - ATTACKER HAS FIGHTERS - BEAMS VS FIGHTERS
    if ($targetbeams > round($attackerfighters / 2))
    {                                  // TARGET BEAMS GT HALF ATTACKER FIGHTERS
      $lost=$attackerfighters-(round($attackerfighters/2));
      $attackerfighters=$attackerfighters-$lost;               // A LOOSES HALF ALL FIGHTERS
      $targetbeams=$targetbeams-$lost;                         // T LOOSES BEAMS EQ TO HALF A FIGHTERS
    } else
    {                                  // TARGET BEAMS LE HALF ATTACKER FIGHTERS
      $attackerfighters=$attackerfighters-$targetbeams;        // A LOOSES FIGHTERS EQ TO T BEAMS
      $targetbeams=0;                                          // T LOOSES ALL BEAMS
    }
  }
  if ($attackerbeams > 0)
  {                         // ATTACKER HAS BEAMS LEFT - CONTINUE COMBAT - BEAMS VS SHIELDS
    if ($attackerbeams > $targetshields)
    {                                  // ATTACKER BEAMS GT TARGET SHIELDS
      $attackerbeams=$attackerbeams-$targetshields;            // A LOOSES BEAMS EQ TO T SHIELDS
      $targetshields=0;                                        // T LOOSES ALL SHIELDS
    } else
    {                                  // ATTACKER BEAMS LE TARGET SHIELDS
      $targetshields=$targetshields-$attackerbeams;            // T LOOSES SHIELDS EQ TO A BEAMS
      $attackerbeams=0;                                        // A LOOSES ALL BEAMS
    }
  }
  if ($targetbeams > 0)
  {                         // TARGET HAS BEAMS LEFT - CONTINUE COMBAT - BEAMS VS SHIELDS
    if ($targetbeams > $attackershields)
    {                                  // TARGET BEAMS GT ATTACKER SHIELDS
      $targetbeams=$targetbeams-$attackershields;              // T LOOSES BEAMS EQ TO A SHIELDS
      $attackershields=0;                                      // A LOOSES ALL SHIELDS
    } else
    {                                  // TARGET BEAMS LE ATTACKER SHIELDS
      $attackershields=$attackershields-$targetbeams;          // A LOOSES SHIELDS EQ TO T BEAMS
      $targetbeams=0;                                          // T LOOSES ALL BEAMS
    }
  }
  if ($attackerbeams > 0)
  {                         // ATTACKER HAS BEAMS LEFT - CONTINUE COMBAT - BEAMS VS ARMOR
    if ($attackerbeams > $targetarmor)
    {                                  // ATTACKER BEAMS GT TARGET ARMOR
      $attackerbeams=$attackerbeams-$targetarmor;              // A LOOSES BEAMS EQ TO T ARMOR
      $targetarmor=0;                                          // T LOOSES ALL ARMOR (T DESTROYED)
    } else
    {                                  // ATTACKER BEAMS LE TARGET ARMOR
      $targetarmor=$targetarmor-$attackerbeams;                // T LOOSES ARMORS EQ TO A BEAMS
      $attackerbeams=0;                                        // A LOOSES ALL BEAMS
    }
  }
  if ($targetbeams > 0)
  {                        // TARGET HAS BEAMS LEFT - CONTINUE COMBAT - BEAMS VS ARMOR
    if ($targetbeams > $attackerarmor)
    {                                 // TARGET BEAMS GT ATTACKER ARMOR
      $targetbeams=$targetbeams-$attackerarmor;                // T LOOSES BEAMS EQ TO A ARMOR
      $attackerarmor=0;                                        // A LOOSES ALL ARMOR (A DESTROYED)
    } else
    {                                 // TARGET BEAMS LE ATTACKER ARMOR
      $attackerarmor=$attackerarmor-$targetbeams;              // A LOOSES ARMOR EQ TO T BEAMS
      $targetbeams=0;                                          // T LOOSES ALL BEAMS
    }
  }
  if ($targetfighters > 0 && $attackertorpdamage > 0)
  {                        // ATTACKER FIRES TORPS - TARGET HAS FIGHTERS - TORPS VS FIGHTERS
    if ($attackertorpdamage > round($targetfighters / 2))
    {                                 // ATTACKER FIRED TORPS GT HALF TARGET FIGHTERS
      $lost=$targetfighters-(round($targetfighters/2));
      $targetfighters=$targetfighters-$lost;                   // T LOOSES HALF ALL FIGHTERS
      $attackertorpdamage=$attackertorpdamage-$lost;           // A LOOSES FIRED TORPS EQ TO HALF T FIGHTERS
    } else
    {                                 // ATTACKER FIRED TORPS LE HALF TARGET FIGHTERS
      $targetfighters=$targetfighters-$attackertorpdamage;     // T LOOSES FIGHTERS EQ TO A TORPS FIRED
      $attackertorpdamage=0;                                   // A LOOSES ALL TORPS FIRED
    }
  }
  if ($attackerfighters > 0 && $targettorpdmg > 0)
  {                        // TARGET FIRES TORPS - ATTACKER HAS FIGHTERS - TORPS VS FIGHTERS
    if ($targettorpdmg > round($attackerfighters / 2))
    {                                 // TARGET FIRED TORPS GT HALF ATTACKER FIGHTERS
      $lost=$attackerfighters-(round($attackerfighters/2));
      $attackerfighters=$attackerfighters-$lost;               // A LOOSES HALF ALL FIGHTERS
      $targettorpdmg=$targettorpdmg-$lost;                     // T LOOSES FIRED TORPS EQ TO HALF A FIGHTERS
    } else
    {                                 // TARGET FIRED TORPS LE HALF ATTACKER FIGHTERS
      $attackerfighters=$attackerfighters-$targettorpdmg;      // A LOOSES FIGHTERS EQ TO T TORPS FIRED
      $targettorpdmg=0;                                        // T LOOSES ALL TORPS FIRED
    }
  }
  if ($attackertorpdamage > 0)
  {                        // ATTACKER FIRES TORPS - CONTINUE COMBAT - TORPS VS ARMOR
    if ($attackertorpdamage > $targetarmor)
    {                                 // ATTACKER FIRED TORPS GT HALF TARGET ARMOR
      $attackertorpdamage=$attackertorpdamage-$targetarmor;    // A LOOSES FIRED TORPS EQ TO T ARMOR
      $targetarmor=0;                                          // T LOOSES ALL ARMOR (T DESTROYED)
    } else
    {                                 // ATTACKER FIRED TORPS LE HALF TARGET ARMOR
      $targetarmor=$targetarmor-$attackertorpdamage;           // T LOOSES ARMOR EQ TO A TORPS FIRED
      $attackertorpdamage=0;                                   // A LOOSES ALL TORPS FIRED
    }
  }
  if ($targettorpdmg > 0)
  {                        // TARGET FIRES TORPS - CONTINUE COMBAT - TORPS VS ARMOR
    if ($targettorpdmg > $attackerarmor)
    {                                 // TARGET FIRED TORPS GT HALF ATTACKER ARMOR
      $targettorpdmg=$targettorpdmg-$attackerarmor;            // T LOOSES FIRED TORPS EQ TO A ARMOR
      $attackerarmor=0;                                        // A LOOSES ALL ARMOR (A DESTROYED)
    } else
    {                                 // TARGET FIRED TORPS LE HALF ATTACKER ARMOR
      $attackerarmor=$attackerarmor-$targettorpdmg;            // A LOOSES ARMOR EQ TO T TORPS FIRED
      $targettorpdmg=0;                                        // T LOOSES ALL TORPS FIRED
    }
  }
  if ($attackerfighters > 0 && $targetfighters > 0)
  {                        // ATTACKER HAS FIGHTERS - TARGET HAS FIGHTERS - FIGHTERS VS FIGHTERS
    if ($attackerfighters > $targetfighters)
    {                                 // ATTACKER FIGHTERS GT TARGET FIGHTERS
      $temptargfighters=0;                                     // T WILL LOOSE ALL FIGHTERS
    } else
    {                                 // ATTACKER FIGHTERS LE TARGET FIGHTERS
      $temptargfighters=$targetfighters-$attackerfighters;     // T WILL LOOSE FIGHTERS EQ TO A FIGHTERS
    }
    if ($targetfighters > $attackerfighters)
    {                                 // TARGET FIGHTERS GT ATTACKER FIGHTERS
      $tempplayfighters=0;                                     // A WILL LOOSE ALL FIGHTERS
    } else
    {                                 // TARGET FIGHTERS LE ATTACKER FIGHTERS
      $tempplayfighters=$attackerfighters-$targetfighters;     // A WILL LOOSE FIGHTERS EQ TO T FIGHTERS
    }
    $attackerfighters=$tempplayfighters;
    $targetfighters=$temptargfighters;
  }
  if ($attackerfighters > 0)
  {                        // ATTACKER HAS FIGHTERS - CONTINUE COMBAT - FIGHTERS VS ARMOR
    if ($attackerfighters > $targetarmor)
    {                                 // ATTACKER FIGHTERS GT TARGET ARMOR
      $targetarmor=0;                                          // T LOOSES ALL ARMOR (T DESTROYED)
    } else
    {                                 // ATTACKER FIGHTERS LE TARGET ARMOR
      $targetarmor=$targetarmor-$attackerfighters;             // T LOOSES ARMOR EQ TO A FIGHTERS
    }
  }
  if ($targetfighters > 0)
  {                        // TARGET HAS FIGHTERS - CONTINUE COMBAT - FIGHTERS VS ARMOR
    if ($targetfighters > $attackerarmor)
    {                                 // TARGET FIGHTERS GT ATTACKER ARMOR
      $attackerarmor=0;                                        // A LOOSES ALL ARMOR (A DESTROYED)
    } else
    {                                 // TARGET FIGHTERS LE ATTACKER ARMOR
      $attackerarmor=$attackerarmor-$targetfighters;           // A LOOSES ARMOR EQ TO T FIGHTERS
    }
  }

  // FIX NEGATIVE VALUE VARS
  if ($attackerfighters < 0) $attackerfighters = 0;
  if ($attackertorps    < 0) $attackertorps = 0;
  if ($attackershields  < 0) $attackershields = 0;
  if ($attackerbeams    < 0) $attackerbeams = 0;
  if ($attackerarmor    < 0) $attackerarmor = 0;
  if ($targetfighters   < 0) $targetfighters = 0;
  if ($targettorpnum    < 0) $targettorpnum = 0;
  if ($targetshields    < 0) $targetshields = 0;
  if ($targetbeams      < 0) $targetbeams = 0;
  if ($targetarmor      < 0) $targetarmor = 0;

  // DEAL WITH DESTROYED SHIPS

  // TARGET SHIP WAS DESTROYED
  if (!$targetarmor>0)
  {
    if ($targetinfo[dev_escapepod] == "Y")
    // TARGET HAD ESCAPE POD
    {
      $rating=round($targetinfo[rating]/2);
      $resc = $db->Execute("UPDATE {$db->prefix}ships SET hull=0, engines=0, power=0, computer=0,sensors=0, beams=0, torp_launchers=0, torps=0, armor=0, armor_pts=100, cloak=0, shields=0, sector=0, ship_ore=0, ship_organics=0, ship_energy=1000, ship_colonists=0, ship_goods=0, ship_fighters=100, ship_damage=0, on_planet='N', planet_id=0, dev_warpedit=0, dev_genesis=0, dev_beacon=0, dev_emerwarp=0, dev_escapepod='N', dev_fuelscoop='N', dev_minedeflector=0, ship_destroyed='N', rating='$rating',dev_lssd='N' WHERE ship_id=$targetinfo[ship_id]");
      db_op_result ($db, $resc, __LINE__, __FILE__, $db_logging);
      playerlog ($db, $targetinfo[ship_id], LOG_ATTACK_LOSE, "Xenobe $playerinfo[character_name]|Y");
    } else
    // TARGET HAD NO POD
    {
      playerlog ($db, $targetinfo[ship_id], LOG_ATTACK_LOSE, "Xenobe $playerinfo[character_name]|N");
      db_kill_player($targetinfo['ship_id']);
    }
    if ($attackerarmor>0)
    {
      // ATTACKER STILL ALIVE TO SALVAGE TRAGET
      $rating_change=round($targetinfo[rating]*$rating_combat_factor);
      $free_ore = round($targetinfo[ship_ore]/2);
      $free_organics = round($targetinfo[ship_organics]/2);
      $free_goods = round($targetinfo[ship_goods]/2);
      $free_holds = NUM_HOLDS($playerinfo[hull]) - $playerinfo[ship_ore] - $playerinfo[ship_organics] - $playerinfo[ship_goods] - $playerinfo[ship_colonists];
      if ($free_holds > $free_goods)
      {                                                        // FIGURE OUT WHAT WE CAN CARRY
        $salv_goods=$free_goods;
        $free_holds=$free_holds-$free_goods;
      } elseif ($free_holds > 0)
      {
        $salv_goods=$free_holds;
        $free_holds=0;
      } else
      {
        $salv_goods=0;
      }
      if ($free_holds > $free_ore)
      {
        $salv_ore=$free_ore;
        $free_holds=$free_holds-$free_ore;
      } elseif ($free_holds > 0)
      {
        $salv_ore=$free_holds;
        $free_holds=0;
      } else
      {
        $salv_ore=0;
      }
      if ($free_holds > $free_organics)
      {
        $salv_organics=$free_organics;
        $free_holds=$free_holds-$free_organics;
      } elseif ($free_holds > 0)
      {
        $salv_organics=$free_holds;
        $free_holds=0;
      } else
      {
        $salv_organics=0;
      }
      $ship_value=$upgrade_cost*(round(pow ($upgrade_factor, $targetinfo['hull']))+round(pow ($upgrade_factor, $targetinfo['engines']))+round(pow ($upgrade_factor, $targetinfo['power']))+round(pow ($upgrade_factor, $targetinfo['computer']))+round(pow ($upgrade_factor, $targetinfo['sensors']))+round(pow ($upgrade_factor, $targetinfo['beams']))+round(pow ($upgrade_factor, $targetinfo['torp_launchers']))+round(pow ($upgrade_factor, $targetinfo['shields']))+round(pow ($upgrade_factor, $targetinfo['armor']))+round(pow ($upgrade_factor, $targetinfo['cloak'])));
      $ship_salvage_rate=mt_rand(10,20);
      $ship_salvage=$ship_value*$ship_salvage_rate/100;
      playerlog ($db, $playerinfo[ship_id], LOG_RAW, "Attack successful, $targetinfo[character_name] was defeated and salvaged for $ship_salvage credits.");
      $resd = $db->Execute ("UPDATE {$db->prefix}ships SET ship_ore=ship_ore+$salv_ore, ship_organics=ship_organics+$salv_organics, ship_goods=ship_goods+$salv_goods, credits=credits+$ship_salvage WHERE ship_id=$playerinfo[ship_id]");
      db_op_result ($db, $resd, __LINE__, __FILE__, $db_logging);
      $armor_lost = $playerinfo[armor_pts] - $attackerarmor;
      $fighters_lost = $playerinfo[ship_fighters] - $attackerfighters;
      $energy=$playerinfo[ship_energy];
      $rese = $db->Execute ("UPDATE {$db->prefix}ships SET ship_energy=$energy,ship_fighters=ship_fighters-$fighters_lost, torps=torps-$attackertorps,armor_pts=armor_pts-$armor_lost, rating=rating-$rating_change WHERE ship_id=$playerinfo[ship_id]");
      db_op_result ($db, $rese, __LINE__, __FILE__, $db_logging);
    }
  }

  // TARGET AND ATTACKER LIVE
  if ($targetarmor>0 && $attackerarmor>0)
  {
    $rating_change=round($targetinfo[rating]*.1);
    $armor_lost = $playerinfo[armor_pts] - $attackerarmor;
    $fighters_lost = $playerinfo[ship_fighters] - $attackerfighters;
    $energy=$playerinfo[ship_energy];
    $target_rating_change=round($targetinfo[rating]/2);
    $target_armor_lost = $targetinfo[armor_pts] - $targetarmor;
    $target_fighters_lost = $targetinfo[ship_fighters] - $targetfighters;
    $target_energy=$targetinfo[ship_energy];
    playerlog ($db, $playerinfo[ship_id], LOG_RAW, "Attack failed, $targetinfo[character_name] survived.");
    playerlog ($db, $targetinfo[ship_id], LOG_ATTACK_WIN, "Xenobe $playerinfo[character_name]|$target_armor_lost|$target_fighters_lost");
    $resf = $db->Execute ("UPDATE {$db->prefix}ships SET ship_energy=$energy,ship_fighters=ship_fighters-$fighters_lost, torps=torps-$attackertorps,armor_pts=armor_pts-$armor_lost, rating=rating-$rating_change WHERE ship_id=$playerinfo[ship_id]");
    db_op_result ($db, $resf, __LINE__, __FILE__, $db_logging);
    $resg = $db->Execute ("UPDATE {$db->prefix}ships SET ship_energy=$target_energy,ship_fighters=ship_fighters-$target_fighters_lost, armor_pts=armor_pts-$target_armor_lost, torps=torps-$targettorpnum, rating=$target_rating_change WHERE ship_id=$targetinfo[ship_id]");
    db_op_result ($db, $resg, __LINE__, __FILE__, $db_logging);
  }

  // ATTACKER SHIP DESTROYED
  if (!$attackerarmor>0)
  {
    playerlog ($db, $playerinfo[ship_id], LOG_RAW, "$targetinfo[character_name] destroyed your ship!");
    db_kill_player($playerinfo['ship_id']);
    $xenobeisdead = 1;
    if ($targetarmor>0)
    {
      // TARGET STILL ALIVE TO SALVAGE ATTACKER
      $rating_change=round($playerinfo[rating]*$rating_combat_factor);
      $free_ore = round($playerinfo[ship_ore]/2);
      $free_organics = round($playerinfo[ship_organics]/2);
      $free_goods = round($playerinfo[ship_goods]/2);
      $free_holds = NUM_HOLDS($targetinfo[hull]) - $targetinfo[ship_ore] - $targetinfo[ship_organics] - $targetinfo[ship_goods] - $targetinfo[ship_colonists];
      if ($free_holds > $free_goods)
      {                                                        // FIGURE OUT WHAT TARGET CAN CARRY
        $salv_goods=$free_goods;
        $free_holds=$free_holds-$free_goods;
      } elseif ($free_holds > 0)
      {
        $salv_goods=$free_holds;
        $free_holds=0;
      } else
      {
        $salv_goods=0;
      }
      if ($free_holds > $free_ore)
      {
        $salv_ore=$free_ore;
        $free_holds=$free_holds-$free_ore;
      } elseif ($free_holds > 0)
      {
        $salv_ore=$free_holds;
        $free_holds=0;
      } else
      {
        $salv_ore=0;
      }
      if ($free_holds > $free_organics)
      {
        $salv_organics=$free_organics;
        $free_holds=$free_holds-$free_organics;
      } elseif ($free_holds > 0)
      {
        $salv_organics=$free_holds;
        $free_holds=0;
      } else
      {
        $salv_organics=0;
      }
      $ship_value=$upgrade_cost*(round(pow ($upgrade_factor, $playerinfo[hull]))+round(pow ($upgrade_factor, $playerinfo[engines]))+round(pow ($upgrade_factor, $playerinfo[power]))+round(pow ($upgrade_factor, $playerinfo[computer]))+round(pow ($upgrade_factor, $playerinfo[sensors]))+round(pow ($upgrade_factor, $playerinfo[beams]))+round(pow ($upgrade_factor, $playerinfo[torp_launchers]))+round(pow ($upgrade_factor, $playerinfo[shields]))+round(pow ($upgrade_factor, $playerinfo[armor]))+round(pow ($upgrade_factor, $playerinfo[cloak])));
      $ship_salvage_rate=mt_rand(10,20);
      $ship_salvage=$ship_value*$ship_salvage_rate/100;
      playerlog ($db, $targetinfo[ship_id], LOG_ATTACK_WIN, "Xenobe $playerinfo[character_name]|$armor_lost|$fighters_lost");
      playerlog ($db, $targetinfo[ship_id], LOG_RAW, "You destroyed the Xenobe ship and salvaged $salv_ore units of ore, $salv_organics units of organics, $salv_goods units of goods, and salvaged $ship_salvage_rate% of the ship for $ship_salvage credits.");
      $resh = $db->Execute ("UPDATE {$db->prefix}ships SET ship_ore=ship_ore+$salv_ore, ship_organics=ship_organics+$salv_organics, ship_goods=ship_goods+$salv_goods, credits=credits+$ship_salvage WHERE ship_id=$targetinfo[ship_id]");
      db_op_result ($db, $resh, __LINE__, __FILE__, $db_logging);
      $armor_lost = $targetinfo[armor_pts] - $targetarmor;
      $fighters_lost = $targetinfo[ship_fighters] - $targetfighters;
      $energy=$targetinfo[ship_energy];
      $resi = $db->Execute ("UPDATE {$db->prefix}ships SET ship_energy=$energy,ship_fighters=ship_fighters-$fighters_lost, torps=torps-$targettorpnum,armor_pts=armor_pts-$armor_lost, rating=rating-$rating_change WHERE ship_id=$targetinfo[ship_id]");
      db_op_result ($db, $resi, __LINE__, __FILE__, $db_logging);
    }
  }
  $resj = $db->Execute("UNLOCK TABLES");
  db_op_result ($db, $resj, __LINE__, __FILE__, $db_logging);
}
?>

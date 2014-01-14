<?php
// Blacknova Traders - attackerweb-based massively multiplayer space combat and trading game
// Copyright (C) 2001-2012 Ron Harwood and the BNT development team
//
//  This program is free software: you can redistribute it and/or modify
//  it under the terms of the GNU Affero General Public License as
//  published by the Free Software Foundation, either version 3 of the
//  License, or (at your option) any later version.
//
//  This program is distributed in the hope that it will be useful,
//  but WITHOUT ANY WARRANTY; without even the implied warranty of
//  MERCHANTABILITY or FITNESS FOR attackerPARTICULAR PURPOSE.  See the
//  GNU Affero General Public License for more details.
//
//  You should have received a copy of the GNU Affero General Public License
//  along with this program.  If not, see <http://www.gnu.org/licenses/>.
//
// File: includes/xenobetoplanet.php

function xenobetoplanet($planet_id)
{
    // Xenobe planet attack code
    global $playerinfo, $planetinfo, $torp_dmg_rate, $level_factor;
    global $rating_combat_factor, $upgrade_cost, $upgrade_factor, $sector_max, $xenobeisdead, $db;

    $resh = $db->Execute("LOCK TABLES {$db->prefix}ships WRITE, {$db->prefix}universe WRITE, {$db->prefix}planets WRITE, {$db->prefix}news WRITE, {$db->prefix}logs WRITE");
    db_op_result ($db, $resh, __LINE__, __FILE__);

    $resultp = $db->Execute ("SELECT * FROM {$db->prefix}planets WHERE planet_id=?", array($planet_id)); // Get target planet information
    db_op_result ($db, $resultp, __LINE__, __FILE__);
    $planetinfo = $resultp->fields;

    $resulto = $db->Execute ("SELECT * FROM {$db->prefix}ships WHERE ship_id=?", array($planetinfo['owner'])); // Get target player information
    db_op_result ($db, $resulto, __LINE__, __FILE__);
    $ownerinfo = $resulto->fields;

    $base_factor = ($planetinfo['base'] == 'Y') ? $basedefense : 0;

    // Planet beams
    $targetbeams = NUM_BEAMS ($ownerinfo['beams'] + $base_factor);
    if ($targetbeams > $planetinfo['energy'])
    {
        $targetbeams = $planetinfo['energy'];
    }
    $planetinfo['energy'] -= $targetbeams;

    // Planet shields
    $targetshields = NUM_SHIELDS ($ownerinfo['shields'] + $base_factor);
    if ($targetshields > $planetinfo['energy'])
    {
        $targetshields = $planetinfo['energy'];
    }
    $planetinfo['energy'] -= $targetshields;

    // Planet torps
    $torp_launchers = round (pow ($level_factor, ($ownerinfo['torp_launchers'])+ $base_factor)) * 10;
    $torps = $planetinfo['torps'];
    $targettorps = $torp_launchers;

    if ($torp_launchers > $torps)
    {
        $targettorps = $torps;
    }
    $planetinfo['torps'] -= $targettorps;
    $targettorpdmg = $torp_dmg_rate * $targettorps;

    // Planet fighters
    $targetfighters = $planetinfo['fighters'];

    // Attacker beams
    $attackerbeams = NUM_BEAMS ($playerinfo['beams']);
    if ($attackerbeams > $playerinfo['ship_energy'])
    {
        $attackerbeams = $playerinfo['ship_energy'];
    }
    $playerinfo['ship_energy'] -= $attackerbeams;

    // Attacker shields
    $attackershields = NUM_SHIELDS ($playerinfo['shields']);
    if ($attackershields > $playerinfo['ship_energy'])
    {
        $attackershields = $playerinfo['ship_energy'];
    }
    $playerinfo['ship_energy'] -= $attackershields;

    // Attacker torps
    $attackertorps = round (pow ($level_factor, $playerinfo['torp_launchers'])) * 2;
    if ($attackertorps > $playerinfo['torps'])
    {
        $attackertorps = $playerinfo['torps'];
    }
    $playerinfo['torps'] -= $attackertorps;
    $attackertorpdamage = $torp_dmg_rate * $attackertorps;

    // Attacker fighters
    $attackerfighters = $playerinfo['ship_fighters'];

    // Attacker armor
    $attackerarmor = $playerinfo['armor_pts'];

    // Begin combat
    if ($attackerbeams > 0 && $targetfighters > 0)              // Attacker has beams - Target has fighters - Beams v. fighters
    {
        if ($attackerbeams > $targetfighters)                   // Attacker beams beat Target fighters
        {
            $lost = $targetfighters;
            $targetfighters = 0;                                // Target loses all fighters
            $attackerbeams = $attackerbeams - $lost;            // Attacker loses beams equal to Target fighters
        }
        else                                                    // Attacker beams less than or equal to Target fighters
        {
            $targetfighters = $targetfighters - $attackerbeams; // Target loses fighters equal to attacker beams
            $attackerbeams = 0;                                 // Attacker loses all beams
        }
    }

    if ($attackerfighters > 0 && $targetbeams > 0)                          // Target has beams - Attacker has fighters - Beams v. fighters
    {
        if ($targetbeams > round ($attackerfighters / 2))                   // Target beams greater than half attacker fighters
        {
            $lost = $attackerfighters - (round ($attackerfighters / 2));    // Attacker loses half of all fighters
            $attackerfighters = $attackerfighters - $lost;
            $targetbeams = $targetbeams - $lost;                            // Target loses beams equal to half of attackers fighters
        }
        else
        {                                                              // Target beams are less than half of attackers fighters
            $attackerfighters = $attackerfighters - $targetbeams;      // Attacker loses fighters equal to target beams
            $targetbeams = 0;                                          // Target loses all beams
        }
    }

    if ($attackerbeams > 0)                                     // Attacker has beams left - continue combat - Beams v. shields
    {
        if ($attackerbeams > $targetshields)                    // Attacker beams greater than target shields
        {
            $attackerbeams = $attackerbeams - $targetshields;   // Attacker loses beams equal to target shields
            $targetshields = 0;                                 // Target loses all shields
        }
        else                                                    // Attacker beams less than or equal to target shields
        {
            $targetshields = $targetshields - $attackerbeams;   // Target loses shields equal to attacker beams
            $attackerbeams = 0;                                 // Attacker loses all beams
        }
    }

    if ($targetbeams > 0)                                                // Target has beams left - Continue combat - Beams v. shields
    {
        if ($targetbeams > $attackershields)                             // Target beams greater than attacker shields
        {
            $targetbeams = $targetbeams - $attackershields;              // Target loses beams equal to attacker shields
            $attackershields = 0;                                        // Attacker loses all shields
        }
        else                                                             // Target beams less than or equal to attacker shields
        {
            $attackershields = $attackershields - $targetbeams;          // Attacker loses sheilds equal to target beams
            $targetbeams = 0;                                            // Target loses all beams
        }
    }

    if ($targetbeams > 0)                                   // Target has beams left - continue combat - beams v. armor
    {
        if ($targetbeams > $attackerarmor)                  // Target beams greater than attacker armor
        {
            $targetbeams = $targetbeams - $attackerarmor;   // Target loses beams equal to attacker armor
            $attackerarmor = 0;                             // Attacker loses all armor (attacker destroyed)
        }
        else                                                // Target beams less than or equal to attacker armor
        {
            $attackerarmor = $attackerarmor - $targetbeams; // Attacker loses armor equal to target beams
            $targetbeams = 0;                               // Target loses all beams
        }
    }

    if ($targetfighters > 0 && $attackertorpdamage > 0)                 // Attacker fires torpedoes - target has fighters - torps v. fighters
    {
        if ($attackertorpdamage > $targetfighters)                      // Attacker fired torpedoes greater than target fighters
        {
            $lost = $targetfighters;
            $targetfighters = 0;                                        // Target loses all fighters
            $attackertorpdamage = $attackertorpdamage - $lost;          // Attacker loses fired torpedoes equal to target fighters
        }
        else                                                            // Attacker fired torpedoes less than or equal to half of the target fighters
        {
            $targetfighters = $targetfighters - $attackertorpdamage;    // Target loses fighters equal to attacker torpedoes fired
            $attackertorpdamage=0;                                      // Attacker loses all torpedoes fired
        }
    }

    if ($attackerfighters > 0 && $targettorpdmg > 0)                        // Target fires torpedoes - attacker has fighters - torpedoes v. fighters
    {
        if ($targettorpdmg > round ($attackerfighters / 2))                 // Target fired torpedoes greater than half of attackers fighters
        {
            $lost = $attackerfighters - (round ($attackerfighters / 2));
            $attackerfighters = $attackerfighters - $lost;                  // Attacker loses half of all fighters
            $targettorpdmg = $targettorpdmg - $lost;                        // Target loses fired torpedoes equal to half of attacker fighters
        }
        else
        {                                                                   // Target fired torpedoes less than or equal to half of attacker fighters
            $attackerfighters = $attackerfighters - $targettorpdmg;         // Attacker loses fighters equal to target torpedoes fired
            $targettorpdmg = 0;                                             // Tartget loses all torpedoes fired
        }
    }

    if ($targettorpdmg > 0)                                     // Target fires torpedoes - continue combat - torpedoes v. armor
    {
        if ($targettorpdmg > $attackerarmor)                    // Target fired torpedoes greater than half of attacker armor
        {
            $targettorpdmg = $targettorpdmg - $attackerarmor;   // Target loses fired torpedoes equal to attacker armor
            $attackerarmor = 0;                                 // Attacker loses all armor (Attacker destroyed)
        }
        else
        {                                                       // Target fired torpedoes less than or equal to half attacker armor
            $attackerarmor = $attackerarmor - $targettorpdmg;   // Attacker loses armor equal to the target torpedoes fired
            $targettorpdmg = 0;                                 // Target loses all torpedoes fired
        }
    }

    if ($attackerfighters > 0 && $targetfighters > 0)                    // Attacker has fighters - target has fighters - fighters v. fighters
    {
        if ($attackerfighters > $targetfighters)                         // Attacker fighters greater than target fighters
        {
            $temptargfighters = 0;                                       // Target will lose all fighters
        }
        else                                                             // Attacker fighters less than or equal to target fighters
        {                                                                // Attackers fighters less than or equal to target fighters
            $temptargfighters = $targetfighters - $attackerfighters;     // Target will loose fighters equal to attacker fighters
        }

        if ($targetfighters > $attackerfighters)
        {                                                                // Target fighters greater than attackers fighters
            $tempplayfighters = 0;                                       // Attackerwill loose ALL fighters
        }
        else
        {                                                                // Target fighters less than or equal to attackers fighters
            $tempplayfighters = $attackerfighters - $targetfighters;     // Attacker will loose fighters equal to target fighters
        }
        $attackerfighters = $tempplayfighters;
        $targetfighters = $temptargfighters;
    }

    if ($targetfighters > 0)                                            // Target has fighters - continue combat - fighters v. armor
    {
        if ($targetfighters > $attackerarmor)
        {                                                               // Target fighters greater than attackers armor
            $attackerarmor = 0;                                         // attacker loses all armor (attacker destroyed)
        }
        else
        {                                                               // Target fighters less than or equal to attackers armor
            $attackerarmor = $attackerarmor - $targetfighters;          // attacker loses armor equal to target fighters
        }
    }

    // Fix negative values
    if ($attackerfighters < 0) $attackerfighters = 0;
    if ($attackertorps    < 0) $attackertorps = 0;
    if ($attackershields  < 0) $attackershields = 0;
    if ($attackerbeams    < 0) $attackerbeams = 0;
    if ($attackerarmor    < 0) $attackerarmor = 0;
    if ($targetfighters   < 0) $targetfighters = 0;
    if ($targettorps      < 0) $targettorps = 0;
    if ($targetshields    < 0) $targetshields = 0;
    if ($targetbeams      < 0) $targetbeams = 0;

    if (!$attackerarmor > 0) // Check if attackers ship destroyed
    {
        playerlog ($db, $playerinfo['ship_id'], LOG_RAW, "Ship destroyed by planetary defenses on planet $planetinfo[name]");
        db_kill_player ($playerinfo['ship_id']);
        $xenobeisdead = 1;

        $free_ore = round ($playerinfo['ship_ore'] / 2);
        $free_organics = round ($playerinfo['ship_organics'] / 2);
        $free_goods = round ($playerinfo['ship_goods'] / 2);
        $ship_value = $upgrade_cost * (round (pow ($upgrade_factor, $playerinfo['hull'])) + round (pow ($upgrade_factor, $playerinfo['engines']))+round (pow ($upgrade_factor, $playerinfo['power'])) + round (pow ($upgrade_factor, $playerinfo['computer'])) + round (pow ($upgrade_factor, $playerinfo['sensors']))+round (pow ($upgrade_factor, $playerinfo['beams'])) + round (pow ($upgrade_factor, $playerinfo['torp_launchers'])) + round (pow ($upgrade_factor, $playerinfo['shields'])) + round (pow ($upgrade_factor, $playerinfo['armor'])) + round (pow ($upgrade_factor, $playerinfo['cloak'])));
        $ship_salvage_rate = mt_rand (10, 20);
        $ship_salvage = $ship_value * $ship_salvage_rate / 100;
        $fighters_lost = $planetinfo['fighters'] - $targetfighters;

        // Log attack to planet owner
        playerlog ($db, $planetinfo['owner'], LOG_PLANET_NOT_DEFEATED, "$planetinfo[name]|$playerinfo[sector]|Xenobe $playerinfo[character_name]|$free_ore|$free_organics|$free_goods|$ship_salvage_rate|$ship_salvage");

        // Update planet
        $resi = $db->Execute("UPDATE {$db->prefix}planets SET energy=?, fighters=fighters-?, torps=torps-?, ore=ore+?, goods=goods+?, organics=organics+?, credits=credits+? WHERE planet_id=?", array($planetinfo['energy'], $fighters_lost, $targettorps, $free_ore, $free_goods, $free_organics, $ship_salvage, $planetinfo['planet_id']));
        db_op_result ($db, $resi, __LINE__, __FILE__);
    }
    else  // Must have made it past planet defences
    {
        $armor_lost = $playerinfo['armor_pts'] - $attackerarmor;
        $fighters_lost = $playerinfo['ship_fighters'] - $attackerfighters;
        $target_fighters_lost = $planetinfo['ship_fighters'] - $targetfighters;
        playerlog ($db, $playerinfo['ship_id'], LOG_RAW, "Made it past defenses on planet $planetinfo[name]");

        // Update attackers
        $resj = $db->Execute ("UPDATE {$db->prefix}ships SET ship_energy=?, ship_fighters=ship_fighters-?, torps=torps-?, armor_pts=armor_pts-? WHERE ship_id=?", array($playerinfo['ship_energy'], $fighters_lost, $attackertorps, $armor_lost, $playerinfo['ship_id']));
        db_op_result ($db, $resj, __LINE__, __FILE__);
        $playerinfo['ship_fighters'] = $attackerfighters;
        $playerinfo['torps'] = $attackertorps;
        $playerinfo['armor_pts'] = $attackerarmor;

        // Update planet
        $resk = $db->Execute ("UPDATE {$db->prefix}planets SET energy=?, fighters=?, torps=torps-? WHERE planet_id=?", array($planetinfo['energy'], $targetfighters, $targettorps, $planetinfo['planet_id']));
        db_op_result ($db, $resk, __LINE__, __FILE__);
        $planetinfo['fighters'] = $targetfighters;
        $planetinfo['torps'] = $targettorps;

        // Now we must attack all ships on the planet one by one
        $resultps = $db->Execute("SELECT ship_id,ship_name FROM {$db->prefix}ships WHERE planet_id=? AND on_planet='Y'", array($planetinfo['planet_id']));
        db_op_result ($db, $resultps, __LINE__, __FILE__);
        $shipsonplanet = $resultps->RecordCount();
        if ($shipsonplanet > 0)
        {
            while (!$resultps->EOF && $xenobeisdead < 1)
            {
                $onplanet = $resultps->fields;
                xenobetoship ($onplanet['ship_id']);
                $resultps->MoveNext();
            }
        }

        $resultps = $db->Execute("SELECT ship_id,ship_name FROM {$db->prefix}ships WHERE planet_id=? AND on_planet='Y'", array($planetinfo['planet_id']));
        db_op_result ($db, $resultps, __LINE__, __FILE__);
        $shipsonplanet = $resultps->RecordCount();
        if ($shipsonplanet == 0 && $xenobeisdead < 1)
        {
            // Must have killed all ships on the planet
            playerlog ($db, $playerinfo['ship_id'], LOG_RAW, "Defeated all ships on planet $planetinfo[name]");

            // Log attack to planet owner
            playerlog ($db, $planetinfo['owner'], LOG_PLANET_DEFEATED, "$planetinfo[name]|$playerinfo[sector]|$playerinfo[character_name]");

            // Update planet
            $resl = $db->Execute("UPDATE {$db->prefix}planets SET fighters=0, torps=0, base='N', owner=0, corp=0 WHERE planet_id=?", array($planetinfo['planet_id']));
            db_op_result ($db, $resl, __LINE__, __FILE__);
            calc_ownership ($planetinfo['sector_id']);
        }
        else
        {
            // Must have died trying
            playerlog ($db, $playerinfo['ship_id'], LOG_RAW, "We were KILLED by ships defending planet $planetinfo[name]");
            // Log attack to planet owner
            playerlog ($db, $planetinfo['owner'], LOG_PLANET_NOT_DEFEATED, "$planetinfo[name]|$playerinfo[sector]|Xenobe $playerinfo[character_name]|0|0|0|0|0");
            // No salvage for planet because it went to the ship that won
        }
    }

    $resx = $db->Execute("UNLOCK TABLES");
    db_op_result ($db, $resx, __LINE__, __FILE__);
}
?>

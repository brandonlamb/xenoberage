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
// File: combat.php

function calcplanetbeams()
{
    global $playerinfo, $ownerinfo, $sectorinfo, $basedefense, $planetinfo, $db, $db_logging;

    $energy_available = $planetinfo['energy'];
    $base_factor = ($planetinfo['base'] == 'Y') ? $basedefense : 0;
    $planetbeams = NUM_BEAMS ($ownerinfo['beams'] + $base_factor);
    $res = $db->Execute("SELECT * FROM {$db->prefix}ships WHERE planet_id=$planetinfo[planet_id] AND on_planet='Y'");
    db_op_result ($db, $res, __LINE__, __FILE__, $db_logging);
    while (!$res->EOF)
    {
        $planetbeams = $planetbeams + NUM_BEAMS ($res->fields['beams']);
        $res->MoveNext();
    }

    if ($planetbeams > $energy_available)
    {
        $planetbeams = $energy_available;
    }
    $planetinfo['energy'] -= $planetbeams;

    return $planetbeams;
}

function calcplanettorps()
{
    global $playerinfo, $ownerinfo, $sectorinfo, $level_factor, $basedefense, $planetinfo, $db, $db_logging;

    $base_factor = ($planetinfo['base'] == 'Y') ? $basedefense : 0;

    $res = $db->Execute("SELECT * FROM {$db->prefix}ships WHERE planet_id=$planetinfo[planet_id] AND on_planet='Y'");
    db_op_result ($db, $res, __LINE__, __FILE__, $db_logging);
    $torp_launchers = round (pow ($level_factor, ($ownerinfo['torp_launchers'])+ $base_factor)) * 10;
    $torps = $planetinfo['torps'];
    if ($res)
    {
       while (!$res->EOF)
       {
           $ship_torps =  round (pow ($level_factor, $res->fields['torp_launchers'])) * 10;
           $torp_launchers = $torp_launchers + $ship_torps;
           $res->MoveNext();
       }
    }
    if ($torp_launchers > $torps)
    {
        $planettorps = $torps;
    }
    else
    {
        $planettorps = $torp_launchers;
    }

    $planetinfo['torps'] -= $planettorps;
    return $planettorps;
}

function calcplanetshields()
{
    global $playerinfo, $ownerinfo, $sectorinfo, $basedefense, $planetinfo, $db, $db_logging;

    $base_factor = ($planetinfo['base'] == 'Y') ? $basedefense : 0;
    $res = $db->Execute("SELECT * FROM {$db->prefix}ships WHERE planet_id=$planetinfo[planet_id] AND on_planet='Y'");
    db_op_result ($db, $res, __LINE__, __FILE__, $db_logging);
    $planetshields = NUM_SHIELDS ($ownerinfo['shields'] + $base_factor);
    $energy_available = $planetinfo['energy'];
    while (!$res->EOF)
    {
        $planetshields += NUM_SHIELDS ($res->fields['shields']);
        $res->MoveNext();
    }

    if ($planetshields > $energy_available)
    {
        $planetshields = $energy_available;
    }
    $planetinfo['energy'] -= $planetshields;

    return $planetshields;
}

function planetbombing()
{
    global $playerinfo, $ownerinfo, $sectorinfo, $planetinfo, $planetbeams, $planetfighters, $attackerfighters;
    global $planettorps, $torp_dmg_rate, $l_cmb_atleastoneturn, $db, $db_logging;
    global $l_bombsaway, $l_bigfigs, $l_bigbeams, $l_bigtorps, $l_strafesuccess;

    if ($playerinfo['turns'] < 1)
    {
        echo $l_cmb_atleastoneturn . "<br><br>";
        TEXT_GOTOMAIN();
		?>
</div></div>
<?
        include "footer.php";
        die();
    }

    $res = $db->Execute("LOCK TABLES {$db->prefix}ships WRITE, {$db->prefix}planets WRITE");
    db_op_result ($db, $res, __LINE__, __FILE__, $db_logging);

    echo $l_bombsaway . "<br><br>\n";

    $attackerfighterslost = 0;
    $planetfighterslost = 0;
    $attackerfightercapacity = NUM_FIGHTERS ($playerinfo['computer']);
    $ownerfightercapacity = NUM_FIGHTERS ($ownerinfo['computer']);
    $beamsused = 0;
    $planettorps = calcplanettorps();
    $planetbeams = calcplanetbeams();
    $planetfighters = $planetinfo['fighters'];
    $attackerfighters = $playerinfo['ship_fighters'];

    if ($ownerfightercapacity / $attackerfightercapacity < 1)
    {
        echo $l_bigfigs . "<br><br>\n";
    }

    if ($planetbeams <= $attackerfighters)
    {
        $attackerfighterslost = $planetbeams;
        $beamsused = $planetbeams;
    }
    else
    {
        $attackerfighterslost = $attackerfighters;
        $beamsused = $attackerfighters;
    }

    if ($attackerfighters <= $attackerfighterslost)
    {
        echo $l_bigbeams . "<br>\n";
    }
    else
    {
        $attackerfighterslost+=$planettorps * $torp_dmg_rate;

        if ($attackerfighters <= $attackerfighterslost)
        {
            echo $l_bigtorps . "<br>\n";
        }
        else
        {
            echo $l_strafesuccess . "<br>\n";
            if ($ownerfightercapacity / $attackerfightercapacity > 1)
            {
                $planetfighterslost = $attackerfighters - $attackerfighterslost;

            }
            else
            {
                $planetfighterslost = round (($attackerfighters - $attackerfighterslost) * $ownerfightercapacity / $attackerfightercapacity);
            }
            if ($planetfighterslost > $planetfighters)
            {
                $planetfighterslost = $planetfighters;
            }
        }
    }

    echo "<br><br>\n";
    playerlog ($db, $ownerinfo[ship_id], LOG_PLANET_BOMBED, "$planetinfo[name]|$playerinfo[sector]|$playerinfo[character_name]|$beamsused|$planettorps|$planetfighterslost");
    $res = $db->Execute("UPDATE {$db->prefix}ships SET turns=turns-1, turns_used=turns_used+1, ship_fighters=ship_fighters-$attackerfighters WHERE ship_id=$playerinfo[ship_id]");
    db_op_result ($db, $res, __LINE__, __FILE__, $db_logging);
    $res = $db->Execute("UPDATE {$db->prefix}planets SET energy=energy-$beamsused,fighters=fighters-$planetfighterslost, torps=torps-$planettorps WHERE planet_id=$planetinfo[planet_id]");
    db_op_result ($db, $res, __LINE__, __FILE__, $db_logging);
    $res = $db->Execute("UNLOCK TABLES");
    db_op_result ($db, $res, __LINE__, __FILE__, $db_logging);
}

function planetcombat()
{
    global $playerinfo, $ownerinfo, $sectorinfo, $planetinfo, $torpedo_price, $colonist_price, $ore_price, $organics_price, $goods_price, $energy_price;

    global $planetbeams, $planetfighters, $planetshields, $planettorps, $attackerbeams, $attackerfighters, $attackershields, $upgrade_factor, $upgrade_cost;
    global $attackertorps, $attackerarmor, $torp_dmg_rate, $level_factor, $attackertorpdamage, $start_energy, $min_value_capture, $l_cmb_atleastoneturn;
    global $l_cmb_atleastoneturn, $l_cmb_shipenergybb, $l_cmb_shipenergyab, $l_cmb_shipenergyas, $l_cmb_shiptorpsbtl, $l_cmb_shiptorpsatl;
    global $l_cmb_planettorpdamage, $l_cmb_beams, $l_cmb_fighters, $l_cmb_shields, $l_cmb_torps;
    global $l_cmb_torpdamage, $l_cmb_armor, $l_cmb_you, $l_cmb_planet, $l_cmb_combatflow, $l_cmb_defender, $l_cmb_attackingplanet;
    global $l_cmb_youfireyourbeams, $l_cmb_defenselost, $l_cmb_defenselost2, $l_cmb_planetarybeams, $l_cmb_planetarybeams2;
    global $l_cmb_youdestroyedplanetshields, $l_cmb_beamsexhausted, $l_cmb_breachedyourshields, $l_cmb_destroyedyourshields;
    global $l_cmb_breachedyourarmor, $l_cmb_destroyedyourarmor, $l_cmb_torpedoexchangephase, $l_cmb_nofightersleft;
    global $l_cmb_youdestroyfighters, $l_cmb_planettorpsdestroy, $l_cmb_planettorpsdestroy2, $l_cmb_torpsbreachedyourarmor;
    global $l_cmb_planettorpsdestroy3, $l_cmb_youdestroyedallfighters, $l_cmb_youdestroyplanetfighters, $l_cmb_fightercombatphase;
    global $l_cmb_youdestroyedallfighters2, $l_cmb_youdestroyplanetfighters2, $l_cmb_allyourfightersdestroyed, $l_cmb_fightertofighterlost;
    global $l_cmb_youbreachedplanetshields, $l_cmb_shieldsremainup, $l_cmb_fighterswarm, $l_cmb_swarmandrepel, $l_cmb_engshiptoshipcombat;
    global $l_cmb_shipdock, $l_cmb_approachattackvector, $l_cmb_noshipsdocked, $l_cmb_yourshipdestroyed, $l_cmb_escapepod;
    global $l_cmb_finalcombatstats, $l_cmb_youlostfighters, $l_cmb_youlostarmorpoints, $l_cmb_energyused, $l_cmb_planetdefeated;
    global $l_cmb_citizenswanttodie, $l_cmb_youmaycapture, $l_cmb_planetnotdefeated, $l_cmb_planetstatistics;
    global $l_cmb_fighterloststat, $l_cmb_energyleft;
    global $db, $db_logging;

    if ($playerinfo['turns'] < 1 )
    {
        echo $l_cmb_atleastoneturn . "<br><br>";
        TEXT_GOTOMAIN();
		?>
</div></div>
<?
        include "footer.php";
        die();
    }

    // Planetary defense system calculation

    $planetbeams        = calcplanetbeams();
    $planetfighters     = $planetinfo['fighters'];
    $planetshields      = calcplanetshields();
    $planettorps        = calcplanettorps();

    // Attacking ship calculations

    $attackerbeams      = NUM_BEAMS ($playerinfo['beams']);
    $attackerfighters   = $playerinfo['ship_fighters'];
    $attackershields    = NUM_SHIELDS ($playerinfo['shields']);
    $attackertorps      = round (pow ($level_factor, $playerinfo['torp_launchers'])) * 2;
    $attackerarmor      = $playerinfo['armor_pts'];

    // Now modify player beams, shields and torpedos on available materiel
    $start_energy = $playerinfo['ship_energy'];

    // Beams
    if ($attackerbeams > $playerinfo['ship_energy'])
    {
        $attackerbeams   = $playerinfo['ship_energy'];
    }
    $playerinfo['ship_energy'] = $playerinfo['ship_energy'] - $attackerbeams;

    // Shields
    if ($attackershields > $playerinfo['ship_energy'])
    {
        $attackershields = $playerinfo['ship_energy'];
    }
    $playerinfo['ship_energy'] = $playerinfo['ship_energy'] - $attackershields;

    // Torpedos
    if ($attackertorps > $playerinfo['torps'])
    {
        $attackertorps = $playerinfo['torps'];
    }
    $playerinfo['torps'] = $playerinfo['torps'] - $attackertorps;

    // Setup torp damage rate for both Planet and Ship
    $planettorpdamage   = $torp_dmg_rate * $planettorps;
    $attackertorpdamage = $torp_dmg_rate * $attackertorps;

    echo "
    <center>
    <HR>
    <table width='75%' border='0'>
    <tr align='center'>
    <td width='9%' height='27'></td>
    <td width='12%' height='27'><font color='white'>$l_cmb_beams</font></td>
    <td width='17%' height='27'><font color='white'>$l_cmb_fighters</font></td>
    <td width='18%' height='27'><font color='white'>$l_cmb_shields</font></td>
    <td width='11%' height='27'><font color='white'>$l_cmb_torps</font></td>
    <td width='22%' height='27'><font color='white'>$l_cmb_torpdamage</font></td>
    <td width='11%' height='27'><font color='white'>$l_cmb_armor</font></td>
    </tr>
    <tr align='center'>
    <td width='9%'> <font color='red'>$l_cmb_you</td>
    <td width='12%'><font color='red'><strong>$attackerbeams</strong></font></td>
    <td width='17%'><font color='red'><strong>$attackerfighters</strong></font></td>
    <td width='18%'><font color='red'><strong>$attackershields</strong></font></td>
    <td width='11%'><font color='red'><strong>$attackertorps</strong></font></td>
    <td width='22%'><font color='red'><strong>$attackertorpdamage</strong></font></td>
    <td width='11%'><font color='red'><strong>$attackerarmor</strong></font></td>
    </tr>
    <tr align='center'>
    <td width='9%'> <font color='#6098F8'>$l_cmb_planet</font></td>
    <td width='12%'><font color='#6098F8'><strong>$planetbeams</strong></font></td>
    <td width='17%'><font color='#6098F8'><strong>$planetfighters</strong></font></td>
    <td width='18%'><font color='#6098F8'><strong>$planetshields</strong></font></td>
    <td width='11%'><font color='#6098F8'><strong>$planettorps</strong></font></td>
    <td width='22%'><font color='#6098F8'><strong>$planettorpdamage</strong></font></td>
    <td width='11%'><font color='#6098F8'><strong>N/A</strong></font></td>
    </tr>
    </table>
    <HR>
    </center>
    ";

    // Begin actual combat calculations

    $planetdestroyed   = 0;
    $attackerdestroyed = 0;

    echo "<br><center><strong><font size='+2'>$l_cmb_combatflow</font></strong><br><br>\n";
    echo "<table width='75%' border='0'><tr align='center'><td><font color='red'>$l_cmb_you</font></td><td><font color='#6098F8'>$l_cmb_defender</font></td>\n";
    echo "<tr align='center'><td><font color='red'><strong>$l_cmb_attackingplanet $playerinfo[sector]</strong></font></td><td></td>";
    echo "<tr align='center'><td><font color='red'><strong>$l_cmb_youfireyourbeams</strong></font></td><td></td>\n";
    if ($planetfighters > 0 && $attackerbeams > 0)
    {
        if ($attackerbeams > $planetfighters)
        {
            $l_cmb_defenselost = str_replace("[cmb_planetfighters]", $planetfighters, $l_cmb_defenselost);
            echo "<tr align='center'><td></td><td><font color='#6098F8'><strong>$l_cmb_defenselost</strong></font>";
            $attackerbeams = $attackerbeams - $planetfighters;
            $planetfighters = 0;
        }
        else
        {
            $l_cmb_defenselost2 = str_replace("[cmb_attackerbeams]", $attackerbeams, $l_cmb_defenselost2);
            $planetfighters = $planetfighters - $attackerbeams;
            echo "<tr align='center'><td></td><td><font color='#6098F8'><strong>$l_cmb_defenselost2</strong></font>";
            $attackerbeams = 0;
        }
    }

    if ($attackerfighters > 0 && $planetbeams > 0)
    {
        // If there are more beams on the planet than attacker has fighters
        if ($planetbeams > round ($attackerfighters / 2))
        {
            // Half the attacker fighters
            $temp = round ($attackerfighters / 2);
            // Attacker loses half his fighters
            $lost = $attackerfighters - $temp;
            // Set attacker fighters to 1/2 it's original value
            $attackerfighters = $temp;
            // Subtract half the attacker fighters from available planetary beams
            $planetbeams = $planetbeams - $lost;
            $l_cmb_planetarybeams = str_replace("[cmb_temp]", $temp, $l_cmb_planetarybeams);
            echo "<tr align='center'><td><font color='red'><strong>$l_cmb_planetarybeams</strong></font><TD></TD>";
        }
        else
        {
            $l_cmb_planetarybeams2 = str_replace("[cmb_planetbeams]", $planetbeams, $l_cmb_planetarybeams2);
            $attackerfighters = $attackerfighters - $planetbeams;
            echo "<tr align='center'><td><font color='red'><strong>$l_cmb_planetarybeams2</strong></font><TD></TD>";
            $planetbeams = 0;
        }
    }
    if ($attackerbeams > 0)
    {
        if ($attackerbeams > $planetshields)
        {
            $attackerbeams = $attackerbeams - $planetshields;
            $planetshields = 0;
            echo "<tr align='center'><td><font color='red'><strong>$l_cmb_youdestroyedplanetshields</font></strong><td></td>";
        }
        else
        {
            $l_cmb_beamsexhausted = str_replace("[cmb_attackerbeams]", $attackerbeams, $l_cmb_beamsexhausted);
            echo "<tr align='center'><td><font color='red'><strong>$l_cmb_beamsexhausted</font></strong><td></td>";
            $planetshields = $planetshields - $attackerbeams;
            $attackerbeams = 0;
        }
    }
    if ($planetbeams > 0)
    {
        if ($planetbeams > $attackershields)
        {
            $planetbeams = $planetbeams - $attackershields;
            $attackershields = 0;
            echo "<tr align='center'><td></td><td><font color='#6098F8'><strong>$l_cmb_breachedyourshields</font></strong></td>";
        }
        else
        {
            $attackershields = $attackershields - $planetbeams;
            $l_cmb_destroyedyourshields = str_replace("[cmb_planetbeams]", $planetbeams, $l_cmb_destroyedyourshields);
            echo "<tr align='center'><td></td><font color='#6098F8'><strong>$l_cmb_destroyedyourshields</font></strong></td>";
            $planetbeams = 0;
        }
    }
    if ($planetbeams > 0)
    {
        if ($planetbeams > $attackerarmor)
        {
            $attackerarmor = 0;
            echo "<tr align='center'><td></td><td><font color='#6098F8'><strong>$l_cmb_breachedyourarmor</strong></font></td>";
        }
        else
        {
            $attackerarmor = $attackerarmor - $planetbeams;
            $l_cmb_destroyedyourarmor = str_replace("[cmb_planetbeams]", $planetbeams, $l_cmb_destroyedyourarmor);
            echo "<tr align='center'><td></td><td><font color='#6098F8'><strong>$l_cmb_destroyedyourarmor</font></strong></td>";
        }
    }
    echo "<tr align='center'><td><font color='YELLOW'><strong>$l_cmb_torpedoexchangephase</strong></font></td><td><strong><font color='YELLOW'>$l_cmb_torpedoexchangephase</strong></font></td><br>";
    if ($planetfighters > 0 && $attackertorpdamage > 0)
    {
        if ($attackertorpdamage > $planetfighters)
        {
            $l_cmb_nofightersleft = str_replace("[cmb_planetfighters]", $planetfighters, $l_cmb_nofightersleft);
            echo "<tr align='center'><td><font color='red'><strong>$l_cmb_nofightersleft</font></strong></td><td></td>";
            $attackertorpdamage = $attackertorpdamage - $planetfighters;
            $planetfighters = 0;
        }
        else
        {
            $planetfighters = $planetfighters - $attackertorpdamage;
            $l_cmb_youdestroyfighters = str_replace("[cmb_attackertorpdamage]", $attackertorpdamage, $l_cmb_youdestroyfighters);
            echo "<tr align='center'><td><font color='red'><strong>$l_cmb_youdestroyfighters</font></strong></td><td></td>";
            $attackertorpdamage = 0;
        }
    }
    if ($attackerfighters > 0 && $planettorpdamage > 0)
    {
        if ($planettorpdamage > round ($attackerfighters / 2))
        {
            $temp = round ($attackerfighters / 2);
            $lost = $attackerfighters - $temp;
            $attackerfighters = $temp;
            $planettorpdamage = $planettorpdamage - $lost;
            $l_cmb_planettorpsdestroy = str_replace("[cmb_temp]", $temp, $l_cmb_planettorpsdestroy);
            echo "<tr align='center'><td></td><td><font color='red'><strong>$l_cmb_planettorpsdestroy</strong></font></td>";
        }
        else
        {
            $attackerfighters = $attackerfighters - $planettorpdamage;
            $l_cmb_planettorpsdestroy2 = str_replace("[cmb_planettorpdamage]", $planettorpdamage, $l_cmb_planettorpsdestroy2);
            echo "<tr align='center'><td></td><td><font color='red'><strong>$l_cmb_planettorpsdestroy2</strong></font></td>";
            $planettorpdamage = 0;
        }
    }
    if ($planettorpdamage > 0)
    {
        if ($planettorpdamage > $attackerarmor)
        {
            $attackerarmor = 0;
            echo "<tr align='center'><td><font color='red'><strong>$l_cmb_torpsbreachedyourarmor</strong></font></td><td></td>";
        }
        else
        {
            $attackerarmor = $attackerarmor - $planettorpdamage;
            $l_cmb_planettorpsdestroy3 = str_replace("[cmb_planettorpdamage]", $planettorpdamage, $l_cmb_planettorpsdestroy3);
            echo "<tr align='center'><td><font color='red'><strong>$l_cmb_planettorpsdestroy3</strong></font></td><td></td>";
        }
    }
    if ($attackertorpdamage > 0 && $planetfighters > 0)
    {
        $planetfighters = $planetfighters - $attackertorpdamage;
        if ($planetfighters < 0)
        {
            $planetfighters = 0;
            echo "<tr align='center'><td><font color='red'><strong>$l_cmb_youdestroyedallfighters</strong></font></td><td></td>";
        }
        else
        {
            $l_cmb_youdestroyplanetfighters = str_replace("[cmb_attackertorpdamage]", $attackertorpdamage, $l_cmb_youdestroyplanetfighters);
            echo "<tr align='center'><td><font color='red'><strong>$l_cmb_youdestroyplanetfighters</strong></font></td><td></td>";
        }
    }
    echo "<tr align='center'><td><font color='YELLOW'><strong>$l_cmb_fightercombatphase</strong></font></td><td><strong><font color='YELLOW'>$l_cmb_fightercombatphase</strong></font></td><br>";
    if ($attackerfighters > 0 && $planetfighters > 0)
    {
        if ($attackerfighters > $planetfighters)
        {
            echo "<tr align='center'><td><font color='red'><strong>$l_cmb_youdestroyedallfighters2</strong></font></td><td></td>";
            $tempplanetfighters = 0;
        }
        else
        {
            $l_cmb_youdestroyplanetfighters2 = str_replace("[cmb_attackerfighters]", $attackerfighters, $l_cmb_youdestroyplanetfighters2);
            echo "<tr align='center'><td><font color='red'><strong>$l_cmb_youdestroyplanetfighters2</strong></font></td><td></td>";
            $tempplanetfighters = $planetfighters - $attackerfighters;
        }
        if ($planetfighters > $attackerfighters)
        {
            echo "<tr align='center'><td><font color='red'><strong>$l_cmb_allyourfightersdestroyed</strong></font></td><td></td>";
            $tempplayfighters = 0;
        }
        else
        {
            $tempplayfighters = $attackerfighters - $planetfighters;
            $l_cmb_fightertofighterlost = str_replace("[cmb_planetfighters]", $planetfighters, $l_cmb_fightertofighterlost);
            echo "<tr align='center'><td><font color='red'><strong>$l_cmb_fightertofighterlost</strong></font></td><td></td>";
        }
        $attackerfighters = $tempplayfighters;
        $planetfighters = $tempplanetfighters;
    }
    if ($attackerfighters > 0 && $planetshields > 0)
    {
        if ($attackerfighters > $planetshields)
        {
            $attackerfighters = $attackerfighters - round ($planetshields / 2);
            echo "<tr align='center'><td><font color='red'><strong>$l_cmb_youbreachedplanetshields</strong></font></td><td></td>";
            $planetshields = 0;
        }
        else
        {
            $l_cmb_shieldsremainup = str_replace("[cmb_attackerfighters]", $attackerfighters, $l_cmb_shieldsremainup);
            echo "<tr align='center'><td></td><font color='#6098F8'><strong>$l_cmb_shieldsremainup</strong></font></td>";
            $planetshields = $planetshields - $attackerfighters;
        }
    }
    if ($planetfighters > 0)
    {
        if ($planetfighters > $attackerarmor)
        {
            $attackerarmor = 0;
            echo "<tr align='center'><td><font color='red'><strong>$l_cmb_fighterswarm</strong></font></td><td></td>";
        }
        else
        {
            $attackerarmor = $attackerarmor - $planetfighters;
            echo "<tr align='center'><td><font color='red'><strong>$l_cmb_swarmandrepel</strong></font></td><td></td>";
        }
    }

    echo "</table></center>\n";
    // Send each docked ship in sequence to attack agressor
    $result4 = $db->Execute("SELECT * FROM {$db->prefix}ships WHERE planet_id=$planetinfo[planet_id] AND on_planet='Y'");
    db_op_result ($db, $result4, __LINE__, __FILE__, $db_logging);
    $shipsonplanet = $result4->RecordCount();

    if ($shipsonplanet > 0)
    {
        $l_cmb_shipdock = str_replace("[cmb_shipsonplanet]", $shipsonplanet, $l_cmb_shipdock);
        echo "<br><br><center>$l_cmb_shipdock<br>$l_cmb_engshiptoshipcombat</center><br><br>\n";
        while (!$result4->EOF)
        {
            $onplanet = $result4->fields;

            if ($attackerfighters < 0)
            {
                $attackerfighters = 0;
            }

            if ($attackertorps    < 0)
            {
                $attackertorps = 0;
            }

            if ($attackershields  < 0)
            {
                $attackershields = 0;
            }

            if ($attackerbeams    < 0)
            {
                $attackerbeams = 0;
            }

            if ($attackerarmor    < 1)
            {
                break;
            }

            echo "<br>-$onplanet[ship_name] $l_cmb_approachattackvector-<br>";
            shiptoship ($onplanet['ship_id']);
            $result4->MoveNext();
        }
    }
    else
        echo "<br><br><center>$l_cmb_noshipsdocked</center><br><br>\n";

    if ($attackerarmor < 1)
    {
        $free_ore = round ($playerinfo['ship_ore'] / 2 );
        $free_organics = round ($playerinfo['ship_organics'] / 2 );
        $free_goods = round ($playerinfo['ship_goods'] / 2 );
        $ship_value = $upgrade_cost * (round (pow ($upgrade_factor, $playerinfo['hull'])) + round (pow ($upgrade_factor, $playerinfo['engines'])) + round (pow ($upgrade_factor, $playerinfo['power'])) + round (pow ($upgrade_factor, $playerinfo['computer'])) + round (pow ($upgrade_factor, $playerinfo['sensors'])) + round (pow ($upgrade_factor, $playerinfo['beams'])) + round (pow ($upgrade_factor, $playerinfo['torp_launchers'])) + round (pow ($upgrade_factor, $playerinfo['shields'])) + round (pow ($upgrade_factor, $playerinfo['armor'])) + round (pow ($upgrade_factor, $playerinfo['cloak'])));
        $ship_salvage_rate = mt_rand (0,10);
        $ship_salvage = $ship_value * $ship_salvage_rate / 100;
        echo "<br><center><font size='+2' COLOR='red'><strong>$l_cmb_yourshipdestroyed</font></strong></center><br>";
        if ($playerinfo['dev_escapepod'] == "Y")
        {
            echo "<center><font color='white'>$l_cmb_escapepod</font></center><br><br>";
            $resx = $db->Execute("UPDATE {$db->prefix}ships SET hull=0,engines=0,power=0,sensors=0,computer=0,beams=0,torp_launchers=0,torps=0,armor=0,armor_pts=100,cloak=0,shields=0,sector=0,ship_organics=0,ship_ore=0,ship_goods=0,ship_energy=$start_energy,ship_colonists=0,ship_fighters=100,dev_warpedit=0,dev_genesis=0,dev_beacon=0,dev_emerwarp=0,dev_escapepod='N',dev_fuelscoop='N',dev_minedeflector=0,on_planet='N',dev_lssd='N' WHERE ship_id=$playerinfo[ship_id]");
            db_op_result ($db, $resx, __LINE__, __FILE__, $db_logging);
            collect_bounty ($planetinfo['owner'], $playerinfo['ship_id']);
        }
        else
        {
            db_kill_player ($playerinfo['ship_id']);
            collect_bounty ($planetinfo['owner'], $playerinfo['ship_id']);
        }
    }
    else
    {
        $free_ore = 0;
        $free_goods = 0;
        $free_organics = 0;
        $ship_salvage_rate = 0;
        $ship_salvage = 0;
        $planetrating = $ownerinfo['hull'] + $ownerinfo['engines'] + $ownerinfo['computer'] + $ownerinfo['beams'] + $ownerinfo['torp_launchers'] + $ownerinfo['shields'] + $ownerinfo['armor'];
        if ($ownerinfo['rating'] != 0 )
        {
            $rating_change = ($ownerinfo['rating'] / abs ($ownerinfo['rating'])) * $planetrating * 10;
        }
        else
        {
            $rating_change=-100;
        }
        echo "<center><br><strong><font size='+2'>$l_cmb_finalcombatstats</font></strong><br><br>";
        $fighters_lost = $playerinfo['ship_fighters'] - $attackerfighters;
        $l_cmb_youlostfighters = str_replace("[cmb_fighters_lost]", $fighters_lost, $l_cmb_youlostfighters);
        $l_cmb_youlostfighters = str_replace("[cmb_playerinfo_ship_fighters]", $playerinfo['ship_fighters'], $l_cmb_youlostfighters);
        echo "$l_cmb_youlostfighters<br>";
        $armor_lost = $playerinfo['armor_pts'] - $attackerarmor;
        $l_cmb_youlostarmorpoints = str_replace("[cmb_armor_lost]", $armor_lost, $l_cmb_youlostarmorpoints);
        $l_cmb_youlostarmorpoints = str_replace("[cmb_playerinfo_armor_pts]", $playerinfo['armor_pts'], $l_cmb_youlostarmorpoints);
        $l_cmb_youlostarmorpoints = str_replace("[cmb_attackerarmor]", $attackerarmor, $l_cmb_youlostarmorpoints);
        echo "$l_cmb_youlostarmorpoints<br>";
        $energy = $playerinfo['ship_energy'];
        $energy_lost = $start_energy - $playerinfo['ship_energy'];
        $l_cmb_energyused = str_replace("[cmb_energy_lost]", $energy_lost, $l_cmb_energyused);
        $l_cmb_energyused = str_replace("[cmb_playerinfo_ship_energy]", $start_energy, $l_cmb_energyused);
        echo "$l_cmb_energyused<br></center>";
        $resx = $db->Execute("UPDATE {$db->prefix}ships SET ship_energy=$energy,ship_fighters=ship_fighters-$fighters_lost, torps=torps-$attackertorps,armor_pts=armor_pts-$armor_lost, rating=rating-$rating_change WHERE ship_id=$playerinfo[ship_id]");
        db_op_result ($db, $resx, __LINE__, __FILE__, $db_logging);
    }

    $result4 = $db->Execute("SELECT * FROM {$db->prefix}ships WHERE planet_id=$planetinfo[planet_id] AND on_planet='Y'");
    db_op_result ($db, $result4, __LINE__, __FILE__, $db_logging);
    $shipsonplanet = $result4->RecordCount();

    if ($planetshields < 1 && $planetfighters < 1 && $attackerarmor > 0 && $shipsonplanet == 0)
    {
        echo "<br><br><center><font color='GREEN'><strong>$l_cmb_planetdefeated</strong></font></center><br><br>";

        // Patch to stop players dumping credits for other players.
        $self_tech = get_avg_tech ($playerinfo);
        $target_tech = round (get_avg_tech ($ownerinfo));

        $roll = mt_rand (0, (integer) $target_tech);
        if ($roll > $self_tech)
        {
            // Reset Planet Assets.
            $sql  = "UPDATE {$db->prefix}planets ";
            $sql .= "SET organics = '0', ore = '0', goods = '0', energy = '0', colonists = '2', credits = '0', fighters = '0', torps = '0', corp = '0', base = 'N', sells = 'N', prod_organics = '20', prod_ore = '20', prod_goods = '20', prod_energy = '20', prod_fighters = '10', prod_torp = '10' ";
            $sql .= "WHERE `dev_planets`.`planet_id` =$planetinfo[planet_id] LIMIT 1;";
            $resx = $db->Execute($sql);
            db_op_result ($db, $resx, __LINE__, __FILE__, $db_logging);
            echo "<div style='text-align:center; font-size:18px; color:#f00;'>The planet become unstable due to not being looked after, and all life and assets have been destroyed.</div>\n";
        }

        if ($min_value_capture != 0)
        {
            $playerscore = gen_score ($playerinfo['ship_id']);
            $playerscore *= $playerscore;

            $planetscore = $planetinfo['organics'] * $organics_price + $planetinfo['ore'] * $ore_price + $planetinfo['goods'] * $goods_price + $planetinfo['energy'] * $energy_price + $planetinfo['fighters'] * $fighter_price + $planetinfo['torps'] * $torpedo_price + $planetinfo['colonists'] * $colonist_price + $planetinfo['credits'];
            $planetscore = $planetscore * $min_value_capture / 100;

            if ($playerscore < $planetscore)
            {
                echo "<center>$l_cmb_citizenswanttodie</center><br><br>";
                $resx = $db->Execute("DELETE FROM {$db->prefix}planets WHERE planet_id=$planetinfo[planet_id]");
                db_op_result ($db, $resx, __LINE__, __FILE__, $db_logging);
                playerlog ($db, $ownerinfo['ship_id'], LOG_PLANET_DEFEATED_D, "$planetinfo[name]|$playerinfo[sector]|$playerinfo[character_name]");
                adminlog ($db, LOG_ADMIN_PLANETDEL, "$playerinfo[character_name]|$ownerinfo[character_name]|$playerinfo[sector]");
                gen_score ($ownerinfo['ship_id']);
            }
            else
            {
                $l_cmb_youmaycapture = str_replace("[capture]", "<a href='planet.php?planet_id=" , $planetinfo['planet_id'] . "&amp;command=capture'>", $l_cmb_youmaycapture);
                echo "<center><font color=red>$l_cmb_youmaycapture</font></center><br><br>";
                playerlog ($db, $ownerinfo['ship_id'], LOG_PLANET_DEFEATED, "$planetinfo[name]|$playerinfo[sector]|$playerinfo[character_name]");
                gen_score ($ownerinfo['ship_id']);
                $update7a = $db->Execute("UPDATE {$db->prefix}planets SET owner=0, fighters=0, torps=torps-$planettorps, base='N', defeated='Y' WHERE planet_id=$planetinfo[planet_id]");
                db_op_result ($db, $update7a, __LINE__, __FILE__, $db_logging);
            }
        }
        else
        {
            $l_cmb_youmaycapture = str_replace("[capture]", "<a href='planet.php?planet_id=" . $planetinfo['planet_id'] . "&amp;command=capture'>[Capture]</a>", $l_cmb_youmaycapture);
            echo "<center>$l_cmb_youmaycapture</center><br><br>";
            playerlog ($db, $ownerinfo['ship_id'], LOG_PLANET_DEFEATED, "$planetinfo[name]|$playerinfo[sector]|$playerinfo[character_name]");
            gen_score ($ownerinfo['ship_id']);
            $update7a = $db->Execute("UPDATE {$db->prefix}planets SET owner=0,fighters=0, torps=torps-$planettorps, base='N', defeated='Y' WHERE planet_id=$planetinfo[planet_id]");
            db_op_result ($db, $update7a, __LINE__, __FILE__, $db_logging);
        }
        calc_ownership ($planetinfo['sector_id']);
    }
    else
    {
        echo "<br><br><center><font color='#6098F8'><strong>$l_cmb_planetnotdefeated</strong></font></center><br><br>";
        $fighters_lost = $planetinfo['fighters'] - $planetfighters;
        $l_cmb_fighterloststat = str_replace("[cmb_fighters_lost]", $fighters_lost, $l_cmb_fighterloststat);
        $l_cmb_fighterloststat = str_replace("[cmb_planetinfo_fighters]", $planetinfo['fighters'], $l_cmb_fighterloststat);
        $l_cmb_fighterloststat = str_replace("[cmb_planetfighters]", $planetfighters, $l_cmb_fighterloststat);
        $energy = $planetinfo['energy'];
        playerlog ($db, $ownerinfo['ship_id'], LOG_PLANET_NOT_DEFEATED, "$planetinfo[name]|$playerinfo[sector]|$playerinfo[character_name]|$free_ore|$free_organics|$free_goods|$ship_salvage_rate|$ship_salvage");
        gen_score ($ownerinfo['ship_id']);
        $update7b = $db->Execute("UPDATE {$db->prefix}planets SET energy=$energy,fighters=fighters-$fighters_lost, torps=torps-$planettorps, ore=ore+$free_ore, goods=goods+$free_goods, organics=organics+$free_organics, credits=credits+$ship_salvage WHERE planet_id=$planetinfo[planet_id]");
        db_op_result ($db, $update7b, __LINE__, __FILE__, $db_logging);
    }
    $update = $db->Execute("UPDATE {$db->prefix}ships SET turns=turns-1, turns_used=turns_used+1 WHERE ship_id=$playerinfo[ship_id]");
    db_op_result ($db, $update, __LINE__, __FILE__, $db_logging);
}

function shiptoship($ship_id)
{
    global $attackerbeams, $attackerfighters, $attackershields, $attackertorps, $attackerarmor, $attackertorpdamage, $start_energy, $level_factor;
    global $torp_dmg_rate, $rating_combat_factor, $upgrade_factor, $upgrade_cost, $armor_lost, $fighters_lost, $playerinfo;
    global $db, $db_logging;
    global $l_cmb_startingstats, $l_cmb_statattackerbeams, $l_cmb_statattackerfighters, $l_cmb_statattackershields, $l_cmb_statattackertorps;
    global $l_cmb_statattackerarmor, $l_cmb_statattackertorpdamage, $l_cmb_isattackingyou, $l_cmb_beamexchange, $l_cmb_beamsdestroy;
    global $l_cmb_beamsdestroy2, $l_cmb_nobeamsareleft, $l_cmb_beamshavenotarget, $l_cmb_fighterdestroyedbybeams, $l_cmb_beamsdestroystillhave;
    global $l_cmb_fighterunhindered, $l_cmb_youhavenofightersleft, $l_cmb_breachedsomeshields, $l_cmb_shieldsarehitbybeams, $l_cmb_nobeamslefttoattack;
    global $l_cmb_yourshieldsbreachedby, $l_cmb_yourshieldsarehit, $l_cmb_hehasnobeamslefttoattack, $l_cmb_yourbeamsbreachedhim;
    global $l_cmb_yourbeamshavedonedamage, $l_cmb_nobeamstoattackarmor, $l_cmb_yourarmorbreachedbybeams, $l_cmb_yourarmorhitdamaged;
    global $l_cmb_torpedoexchange, $l_cmb_hehasnobeamslefttoattackyou, $l_cmb_yourtorpsdestroy, $l_cmb_yourtorpsdestroy2;
    global $l_cmb_youhavenotorpsleft, $l_cmb_hehasnofighterleft, $l_cmb_torpsdestroyyou, $l_cmb_someonedestroyedfighters, $l_cmb_hehasnotorpsleftforyou;
    global $l_cmb_youhavenofightersanymore, $l_cmb_youbreachedwithtorps, $l_cmb_hisarmorishitbytorps, $l_cmb_notorpslefttoattackarmor;
    global $l_cmb_yourarmorbreachedbytorps, $l_cmb_yourarmorhitdmgtorps, $l_cmb_hehasnotorpsforyourarmor, $l_cmb_fightersattackexchange;
    global $l_cmb_enemylostallfighters, $l_cmb_helostsomefighters, $l_cmb_youlostallfighters, $l_cmb_youalsolostsomefighters, $l_cmb_hehasnofightersleftattack;
    global $l_cmb_younofightersattackleft, $l_cmb_youbreachedarmorwithfighters, $l_cmb_youhitarmordmgfighters, $l_cmb_youhavenofighterstoarmor;
    global $l_cmb_hasbreachedarmorfighters, $l_cmb_yourarmorishitfordmgby, $l_cmb_nofightersleftheforyourarmor, $l_cmb_hehasbeendestroyed;
    global $l_cmb_escapepodlaunched, $l_cmb_yousalvaged, $l_cmb_yousalvaged2, $l_cmb_youdidntdestroyhim, $l_cmb_shiptoshipcombatstats;

    $resx = $db->Execute("LOCK TABLES {$db->prefix}ships WRITE, {$db->prefix}universe WRITE, {$db->prefix}zones READ");
    db_op_result ($db, $resx, __LINE__, __FILE__, $db_logging);

    $result2 = $db->Execute ("SELECT * FROM {$db->prefix}ships WHERE ship_id='$ship_id'");
    db_op_result ($db, $result2, __LINE__, __FILE__, $db_logging);
    $targetinfo = $result2->fields;

    echo "<br><br>-=-=-=-=-=-=-=--<br>
    $l_cmb_startingstats:<br>
    <br>
    $l_cmb_statattackerbeams: $attackerbeams<br>
    $l_cmb_statattackerfighters: $attackerfighters<br>
    $l_cmb_statattackershields: $attackershields<br>
    $l_cmb_statattackertorps: $attackertorps<br>
    $l_cmb_statattackerarmor: $attackerarmor<br>
    $l_cmb_statattackertorpdamage: $attackertorpdamage<br>";

    $targetbeams = NUM_BEAMS ($targetinfo['beams']);
    if ($targetbeams > $targetinfo['ship_energy'])
    {
        $targetbeams = $targetinfo['ship_energy'];
    }
    $targetinfo['ship_energy'] = $targetinfo['ship_energy'] - $targetbeams;
    $targetshields = NUM_SHIELDS ($targetinfo['shields']);
    if ($targetshields > $targetinfo['ship_energy'])
    {
        $targetshields = $targetinfo['ship_energy'];
    }
    $targetinfo['ship_energy'] = $targetinfo['ship_energy'] - $targetshields;

    $targettorpnum = round (pow ($level_factor, $targetinfo['torp_launchers'])) * 2;
    if ($targettorpnum > $targetinfo['torps'])
    {
        $targettorpnum = $targetinfo['torps'];
    }
    $targettorpdmg = $torp_dmg_rate * $targettorpnum;
    $targetarmor = $targetinfo['armor_pts'];
    $targetfighters = $targetinfo['ship_fighters'];
    $targetdestroyed = 0;
    $playerdestroyed = 0;
    echo "-->$targetinfo[ship_name] $l_cmb_isattackingyou<br><br>";
    echo "$l_cmb_beamexchange<br>";
    if ($targetfighters > 0 && $attackerbeams > 0)
    {
        if ($attackerbeams > round ($targetfighters / 2))
        {
            $temp = round ($targetfighters/2);
            $lost = $targetfighters-$temp;
            $targetfighters = $temp;
            $attackerbeams = $attackerbeams-$lost;
            $l_cmb_beamsdestroy = str_replace("[cmb_lost]", $lost, $l_cmb_beamsdestroy);
            echo "<-- $l_cmb_beamsdestroy<br>";
        }
        else
        {
            $targetfighters = $targetfighters-$attackerbeams;
            $l_cmb_beamsdestroy2 = str_replace("[cmb_attackerbeams]", $attackerbeams, $l_cmb_beamsdestroy2);
            echo "--> $l_cmb_beamsdestroy2<br>";
            $attackerbeams = 0;
        }
    }
    elseif ($targetfighters > 0 && $attackerbeams < 1)
    echo "$l_cmb_nobeamsareleft<br>";
    else
        echo "$l_cmb_beamshavenotarget<br>";
    if ($attackerfighters > 0 && $targetbeams > 0)
    {
        if ($targetbeams > round ($attackerfighters / 2))
        {
            $temp=round ($attackerfighters/2);
            $lost = $attackerfighters - $temp;
            $attackerfighters = $temp;
            $targetbeams = $targetbeams - $lost;
            $l_cmb_fighterdestroyedbybeams = str_replace("[cmb_targetinfo_ship_name]", $targetinfo['ship_name'], $l_cmb_fighterdestroyedbybeams);
            $l_cmb_fighterdestroyedbybeams = str_replace("[cmb_lost]", $lost, $l_cmb_fighterdestroyedbybeams);
            echo "--> $l_cmb_fighterdestroyedbybeams<br>";
        }
        else
        {
            $attackerfighters = $attackerfighters - $targetbeams;
            $l_cmb_beamsdestroystillhave = str_replace("[cmb_targetinfo_ship_name]", $targetinfo['ship_name'], $l_cmb_beamsdestroystillhave);
            $l_cmb_beamsdestroystillhave = str_replace("[cmb_targetbeams]", $targetbeams, $l_cmb_beamsdestroystillhave);
            $l_cmb_beamsdestroystillhave = str_replace("[cmb_attackerfighters]", $attackerfighters, $l_cmb_beamsdestroystillhave);
            echo "<-- $l_cmb_beamsdestroystillhave<br>";
            $targetbeams = 0;
        }
    }
    elseif ($attackerfighters > 0 && $targetbeams < 1)
    {
        echo "$l_cmb_fighterunhindered<br>";
    }
    else
    {
        echo "$l_cmb_youhavenofightersleft<br>";
    }

    if ($attackerbeams > 0)
    {
        if ($attackerbeams > $targetshields)
        {
            $attackerbeams = $attackerbeams - $targetshields;
            $targetshields = 0;
            $l_cmb_breachedsomeshields = str_replace("[cmb_targetinfo_ship_name]", $targetinfo['ship_name'], $l_cmb_breachedsomeshields);
            echo "<-- $l_cmb_breachedsomeshields<br>";
        }
        else
        {
            $l_cmb_shieldsarehitbybeams = str_replace("[cmb_targetinfo_ship_name]", $targetinfo['ship_name'], $l_cmb_shieldsarehitbybeams);
            $l_cmb_shieldsarehitbybeams = str_replace("[cmb_attackerbeams]", $attackerbeams, $l_cmb_shieldsarehitbybeams);
            echo "$l_cmb_shieldsarehitbybeams<br>";
            $targetshields = $targetshields - $attackerbeams;
            $attackerbeams = 0;
        }
    }
    else
    {
        $l_cmb_nobeamslefttoattack = str_replace("[cmb_targetinfo_ship_name]", $targetinfo['ship_name'], $l_cmb_nobeamslefttoattack);
        echo "$l_cmb_nobeamslefttoattack<br>";
    }
    if ($targetbeams > 0)
    {
        if ($targetbeams > $attackershields)
        {
            $targetbeams = $targetbeams - $attackershields;
            $attackershields = 0;
            $l_cmb_yourshieldsbreachedby = str_replace("[cmb_targetinfo_ship_name]", $targetinfo['ship_name'], $l_cmb_yourshieldsbreachedby);
            echo "--> $l_cmb_yourshieldsbreachedby<br>";
        }
        else
        {
            $l_cmb_yourshieldsarehit = str_replace("[cmb_targetinfo_ship_name]", $targetinfo['ship_name'], $l_cmb_yourshieldsarehit);
            $l_cmb_yourshieldsarehit = str_replace("[cmb_targetbeams]", $targetbeams, $l_cmb_yourshieldsarehit);
            echo "<-- $l_cmb_yourshieldsarehit<br>";
            $attackershields = $attackershields - $targetbeams;
            $targetbeams = 0;
        }
    }
    else
    {
        $l_cmb_hehasnobeamslefttoattack = str_replace("[cmb_targetinfo_ship_name]", $targetinfo['ship_name'], $l_cmb_hehasnobeamslefttoattack);
        echo "$l_cmb_hehasnobeamslefttoattack<br>";
    }
    if ($attackerbeams > 0)
    {
        if ($attackerbeams > $targetarmor)
        {
            $targetarmor=0;
            $l_cmb_yourbeamsbreachedhim = str_replace("[cmb_targetinfo_ship_name]", $targetinfo['ship_name'], $l_cmb_yourbeamsbreachedhim);
            echo "--> $l_cmb_yourbeamsbreachedhim<br>";
        }
        else
        {
            $targetarmor=$targetarmor-$attackerbeams;
            $l_cmb_yourbeamshavedonedamage = str_replace("[cmb_attackerbeams]", $attackerbeams, $l_cmb_yourbeamshavedonedamage);
            $l_cmb_yourbeamshavedonedamage = str_replace("[cmb_targetinfo_ship_name]", $targetinfo['ship_name'], $l_cmb_yourbeamshavedonedamage);
            echo "$l_cmb_yourbeamshavedonedamage<br>";
        }
    }
    else
    {
        $l_cmb_nobeamstoattackarmor = str_replace("[cmb_targetinfo_ship_name]", $targetinfo['ship_name'], $l_cmb_nobeamstoattackarmor);
        echo "$l_cmb_nobeamstoattackarmor<br>";
    }
    if ($targetbeams > 0)
    {
        if ($targetbeams > $attackerarmor)
        {
            $attackerarmor = 0;
            $l_cmb_yourarmorbreachedbybeams = str_replace("[cmb_targetinfo_ship_name]", $targetinfo['ship_name'], $l_cmb_yourarmorbreachedbybeams);
            echo "--> $l_cmb_yourarmorbreachedbybeams<br>";
        }
        else
        {
            $attackerarmor = $attackerarmor - $targetbeams;
            $l_cmb_yourarmorhitdamaged = str_replace("[cmb_targetinfo_ship_name]", $targetinfo['ship_name'], $l_cmb_yourarmorhitdamaged);
            $l_cmb_yourarmorhitdamaged = str_replace("[cmb_targetbeams]", $targetbeams, $l_cmb_yourarmorhitdamaged);
            echo "<-- $l_cmb_yourarmorhitdamaged<br>";
        }
    }
    else
    {
        $l_cmb_hehasnobeamslefttoattackyou = str_replace("[cmb_targetinfo_ship_name]", $targetinfo['ship_name'], $l_cmb_hehasnobeamslefttoattackyou);
        echo "$l_cmb_hehasnobeamslefttoattackyou<br>";
    }
    echo "<br>$l_cmb_torpedoexchange<br>";
    if ($targetfighters > 0 && $attackertorpdamage > 0)
    {
        if ($attackertorpdamage > round ($targetfighters / 2))
        {
            $temp=round ($targetfighters / 2);
            $lost=$targetfighters - $temp;
            $targetfighters = $temp;
            $attackertorpdamage = $attackertorpdamage - $lost;
            $l_cmb_yourtorpsdestroy = str_replace("[cmb_targetinfo_ship_name]", $targetinfo['ship_name'], $l_cmb_yourtorpsdestroy);
            $l_cmb_yourtorpsdestroy = str_replace("[cmb_lost]", $lost, $l_cmb_yourtorpsdestroy);
            echo "--> $l_cmb_yourtorpsdestroy<br>";
        }
        else
        {
            $targetfighters = $targetfighters - $attackertorpdamage;
            $l_cmb_yourtorpsdestroy2 = str_replace("[cmb_targetinfo_ship_name]", $targetinfo['ship_name'], $l_cmb_yourtorpsdestroy2);
            $l_cmb_yourtorpsdestroy2 = str_replace("[cmb_attackertorpdamage]", $attackertorpdamage, $l_cmb_yourtorpsdestroy2);
            echo "<-- $l_cmb_yourtorpsdestroy2<br>";
            $attackertorpdamage = 0;
        }
    }
    elseif ($targetfighters > 0 && $attackertorpdamage < 1)
    {
        $l_cmb_youhavenotorpsleft = str_replace("[cmb_targetinfo_ship_name]", $targetinfo['ship_name'], $l_cmb_youhavenotorpsleft);
        echo "$l_cmb_youhavenotorpsleft<br>";
    }
    else
    {
        $l_cmb_hehasnofighterleft = str_replace("[cmb_targetinfo_ship_name]", $targetinfo['ship_name'], $l_cmb_hehasnofighterleft);
        echo "$l_cmb_hehasnofighterleft<br>";
    }
    if ($attackerfighters > 0 && $targettorpdmg > 0)
    {
        if ($targettorpdmg > round ($attackerfighters / 2))
        {
            $temp = round ($attackerfighters / 2);
            $lost = $attackerfighters - $temp;
            $attackerfighters = $temp;
            $targettorpdmg = $targettorpdmg - $lost;
            $l_cmb_torpsdestroyyou = str_replace("[cmb_targetinfo_ship_name]", $targetinfo['ship_name'], $l_cmb_torpsdestroyyou);
            $l_cmb_torpsdestroyyou = str_replace("[cmb_lost]", $lost, $l_cmb_torpsdestroyyou);
            echo "--> $l_cmb_torpsdestroyyou<br>";
        }
        else
        {
            $attackerfighters = $attackerfighters - $targettorpdmg;
            $l_cmb_someonedestroyedfighters = str_replace("[cmb_targetinfo_ship_name]", $targetinfo['ship_name'], $l_cmb_someonedestroyedfighters);
            $l_cmb_someonedestroyedfighters = str_replace("[cmb_targettorpdmg]", $targettorpdmg, $l_cmb_someonedestroyedfighters);
            echo "<-- $l_cmb_someonedestroyedfighters<br>";
            $targettorpdmg=0;
        }
    }
    elseif ($attackerfighters > 0 && $targettorpdmg < 1)
    {
        $l_cmb_hehasnotorpsleftforyou = str_replace("[cmb_targetinfo_ship_name]", $targetinfo['ship_name'], $l_cmb_hehasnotorpsleftforyou);
        echo "$l_cmb_hehasnotorpsleftforyou<br>";
    }
    else
    {
        $l_cmb_youhavenofightersanymore = str_replace("[cmb_targetinfo_ship_name]", $targetinfo['ship_name'], $l_cmb_youhavenofightersanymore);
        echo "$l_cmb_youhavenofightersanymore<br>";
    }
    if ($attackertorpdamage > 0)
    {
        if ($attackertorpdamage > $targetarmor)
        {
            $targetarmor = 0;
            $l_cmb_youbreachedwithtorps = str_replace("[cmb_targetinfo_ship_name]", $targetinfo['ship_name'], $l_cmb_youbreachedwithtorps);
            echo "--> $l_cmb_youbreachedwithtorps<br>";
        }
        else
        {
            $targetarmor=$targetarmor-$attackertorpdamage;
            $l_cmb_hisarmorishitbytorps = str_replace("[cmb_targetinfo_ship_name]", $targetinfo['ship_name'], $l_cmb_hisarmorishitbytorps);
            $l_cmb_hisarmorishitbytorps = str_replace("[cmb_attackertorpdamage]", $attackertorpdamage, $l_cmb_hisarmorishitbytorps);
            echo "<-- $l_cmb_hisarmorishitbytorps<br>";
        }
    }
    else
    {
        $l_cmb_notorpslefttoattackarmor = str_replace("[cmb_targetinfo_ship_name]", $targetinfo['ship_name'], $l_cmb_notorpslefttoattackarmor);
        echo "$l_cmb_notorpslefttoattackarmor<br>";
    }
    if ($targettorpdmg > 0)
    {
        if ($targettorpdmg > $attackerarmor)
        {
            $attackerarmor = 0;
            $l_cmb_yourarmorbreachedbytorps = str_replace("[cmb_targetinfo_ship_name]", $targetinfo['ship_name'], $l_cmb_yourarmorbreachedbytorps);
            echo "<-- $l_cmb_yourarmorbreachedbytorps<br>";
        }
        else
        {
            $attackerarmor=$attackerarmor-$targettorpdmg;
            $l_cmb_yourarmorhitdmgtorps = str_replace("[cmb_targettorpdmg]", $targettorpdmg, $l_cmb_yourarmorhitdmgtorps);
            $l_cmb_yourarmorhitdmgtorps = str_replace("[cmb_targetinfo_ship_name]", $targetinfo['ship_name'], $l_cmb_yourarmorhitdmgtorps);
            echo "<-- $l_cmb_yourarmorhitdmgtorps<br>";
        }
    }
    else
    {
        $l_cmb_hehasnotorpsforyourarmor = str_replace("[cmb_targetinfo_ship_name]", $targetinfo['ship_name'], $l_cmb_hehasnotorpsforyourarmor);
        echo "$l_cmb_hehasnotorpsforyourarmor<br>";
    }
    echo "<br>$l_cmb_fightersattackexchange<br>";
    if ($attackerfighters > 0 && $targetfighters > 0)
    {
        if ($attackerfighters > $targetfighters)
        {
            $l_cmb_enemylostallfighters = str_replace("[cmb_targetinfo_ship_name]", $targetinfo['ship_name'], $l_cmb_enemylostallfighters);
            echo "--> $l_cmb_enemylostallfighters<br>";
            $temptargfighters = 0;
        }
        else
        {
            $l_cmb_helostsomefighters = str_replace("[cmb_targetinfo_ship_name]", $targetinfo['ship_name'], $l_cmb_helostsomefighters);
            $l_cmb_helostsomefighters = str_replace("[cmb_attackerfighters]", $attackerfighters, $l_cmb_helostsomefighters);
            echo "$l_cmb_helostsomefighters<br>";
            $temptargfighters = $targetfighters - $attackerfighters;
        }
        if ($targetfighters > $attackerfighters)
        {
            echo "<-- $l_cmb_youlostallfighters<br>";
            $tempplayfighters = 0;
        }
        else
        {
            $l_cmb_youalsolostsomefighters = str_replace("[cmb_targetfighters]", $targetfighters, $l_cmb_youalsolostsomefighters);
            echo "<-- $l_cmb_youalsolostsomefighters<br>";
            $tempplayfighters = $attackerfighters - $targetfighters;
        }
        $attackerfighters = $tempplayfighters;
        $targetfighters = $temptargfighters;
    }
    elseif ($attackerfighters > 0 && $targetfighters < 1)
    {
        $l_cmb_hehasnofightersleftattack = str_replace("[cmb_targetinfo_ship_name]", $targetinfo['ship_name'], $l_cmb_hehasnofightersleftattack);
        echo "$l_cmb_hehasnofightersleftattack<br>";
    }
    else
    {
        $l_cmb_younofightersattackleft = str_replace("[cmb_targetinfo_ship_name]", $targetinfo['ship_name'], $l_cmb_younofightersattackleft);
        echo "$l_cmb_younofightersattackleft<br>";
    }
    if ($attackerfighters > 0)
    {
        if ($attackerfighters > $targetarmor)
        {
            $targetarmor = 0;
            $l_cmb_youbreachedarmorwithfighters = str_replace("[cmb_targetinfo_ship_name]", $targetinfo['ship_name'], $l_cmb_youbreachedarmorwithfighters);
            echo "--> $l_cmb_youbreachedarmorwithfighters<br>";
        }
        else
        {
            $targetarmor = $targetarmor - $attackerfighters;
            $l_cmb_youhitarmordmgfighters = str_replace("[cmb_targetinfo_ship_name]", $targetinfo['ship_name'], $l_cmb_youhitarmordmgfighters);
            $l_cmb_youhitarmordmgfighters = str_replace("[cmb_attackerfighters]", $attackerfighters, $l_cmb_youhitarmordmgfighters);
            echo "<-- $l_cmb_youhitarmordmgfighters<br>";
        }
    }
    else
    {
        $l_cmb_youhavenofighterstoarmor = str_replace("[cmb_targetinfo_ship_name]", $targetinfo['ship_name'], $l_cmb_youhavenofighterstoarmor);
        echo "$l_cmb_youhavenofighterstoarmor<br>";
    }
    if ($targetfighters > 0)
    {
        if ($targetfighters > $attackerarmor)
        {
            $attackerarmor = 0;
            $l_cmb_hasbreachedarmorfighters = str_replace("[cmb_targetinfo_ship_name]", $targetinfo['ship_name'], $l_cmb_hasbreachedarmorfighters);
            echo "<-- $l_cmb_hasbreachedarmorfighters<br>";
        }
        else
        {
            $attackerarmor = $attackerarmor - $targetfighters;
            $l_cmb_yourarmorishitfordmgby = str_replace("[cmb_targetfighters]", $targetfighters, $l_cmb_yourarmorishitfordmgby);
            $l_cmb_yourarmorishitfordmgby = str_replace("[cmb_targetinfo_ship_name]", $targetinfo['ship_name'], $l_cmb_yourarmorishitfordmgby);
            echo "--> $l_cmb_yourarmorishitfordmgby<br>";
        }
    }
    else
    {
        $l_cmb_nofightersleftheforyourarmor = str_replace("[cmb_targetinfo_ship_name]", $targetinfo['ship_name'], $l_cmb_nofightersleftheforyourarmor);
        echo "$l_cmb_nofightersleftheforyourarmor<br>";
    }
    if ($targetarmor < 1)
    {
        $l_cmb_hehasbeendestroyed = str_replace("[cmb_targetinfo_ship_name]", $targetinfo['ship_name'], $l_cmb_hehasbeendestroyed);
        echo "<br>$l_cmb_hehasbeendestroyed<br>";
        if ($attackerarmor > 0)
        {
            $rating_change=round ($targetinfo['rating'] * $rating_combat_factor);
            $free_ore = round ($targetinfo['ship_ore'] / 2);
            $free_organics = round ($targetinfo['ship_organics'] / 2);
            $free_goods = round ($targetinfo['ship_goods'] / 2);
            $free_holds = NUM_HOLDS ($playerinfo['hull']) - $playerinfo['ship_ore'] - $playerinfo['ship_organics'] - $playerinfo['ship_goods'] - $playerinfo['ship_colonists'];
            if ($free_holds > $free_goods)
            {
                $salv_goods = $free_goods;
                $free_holds = $free_holds - $free_goods;
            }
            elseif ($free_holds > 0)
            {
                $salv_goods = $free_holds;
                $free_holds = 0;
            }
            else
            {
                $salv_goods = 0;
            }
            if ($free_holds > $free_ore)
            {
                $salv_ore = $free_ore;
                $free_holds = $free_holds - $free_ore;
            }
            elseif ($free_holds > 0)
            {
                $salv_ore = $free_holds;
                $free_holds = 0;
            }
            else
            {
                $salv_ore = 0;
            }
            if ($free_holds > $free_organics)
            {
                $salv_organics = $free_organics;
                $free_holds = $free_holds - $free_organics;
            }
            elseif ($free_holds > 0)
            {
                $salv_organics = $free_holds;
                $free_holds = 0;
            }
            else
            {
                $salv_organics = 0;
            }
            $ship_value = $upgrade_cost * (round (pow ($upgrade_factor, $targetinfo['hull']))+round (pow ($upgrade_factor, $targetinfo['engines']))+round (pow ($upgrade_factor, $targetinfo['power']))+round (pow ($upgrade_factor, $targetinfo['computer']))+round (pow ($upgrade_factor, $targetinfo['sensors']))+round (pow ($upgrade_factor, $targetinfo['beams']))+round (pow ($upgrade_factor, $targetinfo['torp_launchers']))+round (pow ($upgrade_factor, $targetinfo['shields']))+round (pow ($upgrade_factor, $targetinfo['armor']))+round (pow ($upgrade_factor, $targetinfo['cloak'])));
            $ship_salvage_rate = mt_rand (10,20);
            $ship_salvage = $ship_value*$ship_salvage_rate / 100;
            $l_cmb_yousalvaged = str_replace("[cmb_salv_ore]", $salv_ore, $l_cmb_yousalvaged);
            $l_cmb_yousalvaged = str_replace("[cmb_salv_organics]", $salv_organics, $l_cmb_yousalvaged);
            $l_cmb_yousalvaged = str_replace("[cmb_salv_goods]", $salv_goods, $l_cmb_yousalvaged);
            $l_cmb_yousalvaged = str_replace("[cmb_salvage_rate]", $ship_salvage_rate, $l_cmb_yousalvaged);
            $l_cmb_yousalvaged = str_replace("[cmb_salvage]", $ship_salvage, $l_cmb_yousalvaged);
            $l_cmb_yousalvaged2 = str_replace("[cmb_number_rating_change]", NUMBER (abs($rating_change)), $l_cmb_yousalvaged2);
            echo $l_cmb_yousalvaged . "<br>" . $l_cmb_yousalvaged2;
            $update3 = $db->Execute ("UPDATE {$db->prefix}ships SET ship_ore=ship_ore+$salv_ore, ship_organics=ship_organics+$salv_organics, ship_goods=ship_goods+$salv_goods, credits=credits+$ship_salvage WHERE ship_id=$playerinfo[ship_id]");
            db_op_result ($db, $update3, __LINE__, __FILE__, $db_logging);
        }

        if ($targetinfo[dev_escapepod] == "Y")
        {
            $rating = round ($targetinfo['rating'] / 2 );
            echo "$l_cmb_escapepodlaunched<br><br>";
            echo "<br><br>ship_id=$targetinfo[ship_id]<br><br>";
            $test = $db->Execute("UPDATE {$db->prefix}ships SET hull=0,engines=0,power=0,sensors=0,computer=0,beams=0,torp_launchers=0,torps=0,armor=0,armor_pts=100,cloak=0,shields=0,sector=0,ship_organics=0,ship_ore=0,ship_goods=0,ship_energy=$start_energy,ship_colonists=0,ship_fighters=100,dev_warpedit=0,dev_genesis=0,dev_beacon=0,dev_emerwarp=0,dev_escapepod='N',dev_fuelscoop='N',dev_minedeflector=0,on_planet='N',rating='$rating',dev_lssd='N' WHERE ship_id=$targetinfo[ship_id]");
            db_op_result ($db, $test, __LINE__, __FILE__, $db_logging);
            playerlog ($db, $targetinfo['ship_id'], LOG_ATTACK_LOSE, "$playerinfo[character_name]|Y");
            collect_bounty ($playerinfo['ship_id'], $targetinfo['ship_id']);
        }
        else
        {
            playerlog ($db, $targetinfo['ship_id'], LOG_ATTACK_LOSE, "$playerinfo[character_name]|N");
            db_kill_player ($targetinfo['ship_id']);
            collect_bounty ($playerinfo['ship_id'], $targetinfo['ship_id']);
        }
    }
    else
    {
        $l_cmb_youdidntdestroyhim = str_replace("[cmb_targetinfo_ship_name]", $targetinfo['ship_name'], $l_cmb_youdidntdestroyhim);
        echo "$l_cmb_youdidntdestroyhim<br>";
        $target_rating_change = round ($targetinfo['rating'] * .1);
        $target_armor_lost = $targetinfo['armor_pts'] - $targetarmor;
        $target_fighters_lost = $targetinfo['ship_fighters'] - $targetfighters;
        $target_energy = $targetinfo['ship_energy'];
        playerlog ($db, $targetinfo['ship_id'], LOG_ATTACKED_WIN, "$playerinfo[character_name] $armor_lost $fighters_lost");
        $update4 = $db->Execute ("UPDATE {$db->prefix}ships SET ship_energy=$target_energy,ship_fighters=ship_fighters-$target_fighters_lost, armor_pts=armor_pts-$target_armor_lost, torps=torps-$targettorpnum WHERE ship_id=$targetinfo[ship_id]");
        db_op_result ($db, $update4, __LINE__, __FILE__, $db_logging);
    }
    echo "<br>_+_+_+_+_+_+_<br>";
    echo "$l_cmb_shiptoshipcombatstats<br>";
    echo "$l_cmb_statattackerbeams: $attackerbeams<br>";
    echo "$l_cmb_statattackerfighters: $attackerfighters<br>";
    echo "$l_cmb_statattackershields: $attackershields<br>";
    echo "$l_cmb_statattackertorps: $attackertorps<br>";
    echo "$l_cmb_statattackerarmor: $attackerarmor<br>";
    echo "$l_cmb_statattackertorpdamage: $attackertorpdamage<br>";
    echo "_+_+_+_+_+_+<br>";
    $resx = $db->Execute("UNLOCK TABLES");
    db_op_result ($db, $resx, __LINE__, __FILE__, $db_logging);
}
?>

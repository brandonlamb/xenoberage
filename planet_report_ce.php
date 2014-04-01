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
// File: planet_report_ce.php

include "config/config.php";
updatecookie();

// New database driven language entries
load_languages($db, $lang, array('planet_report', 'rsmove', 'common', 'global_includes', 'global_funcs', 'footer', 'news'), $langvars, $db_logging);

$title = $l_pr_title;
include "header.php";

if (checklogin())
{
    die();
}

// This is required by Setup Info
// planet_hack_fix,0.2.0,25-02-2004,TheMightyDude
?>
<div class="tablecell content both-border">
	<div class="pad">
<?
bigtitle();

echo "<br>";
echo "Click <a href=planet_report.php>here</A> to return to report menu<br>";

if (isset($_POST["TPCreds"]))
{
  collect_credits($_POST["TPCreds"]);
}
elseif (isset($buildp) AND isset($builds))
{
  go_build_base($buildp, $builds);
}
else
{
  change_planet_production($_POST);
}

echo "<br><br>";
TEXT_GOTOMAIN();


function go_build_base($planet_id, $sector_id)
{
  global $db;
  global $db_logging;
  global $base_ore, $base_organics, $base_goods, $base_credits;
  global $l_planet_bbuild;
  global $username;

  echo "<br>Click <a href=planet_report.php?PRepType=1>here</A> to return to the Planet Status Report<br><br>";

  $result = $db->Execute("SELECT * FROM {$db->prefix}ships WHERE ship_id='$user_ship_id'");
  db_op_result ($db, $result, __LINE__, __FILE__, $db_logging);
  $playerinfo=$result->fields;

  $result2 = $db->Execute("SELECT * FROM {$db->prefix}universe WHERE sector_id=$playerinfo[sector]");
  db_op_result ($db, $result2, __LINE__, __FILE__, $db_logging);
  $sectorinfo=$result2->fields;

  $result3 = $db->Execute("SELECT * FROM {$db->prefix}planets WHERE planet_id=$planet_id");
  db_op_result ($db, $result3, __LINE__, __FILE__, $db_logging);
  $planetinfo=$result3->fields;

  // Error out and return if the Player isn't the owner of the Planet
  // verify player owns the planet which is to have the base created on.
  if ($planetinfo['owner'] != $playerinfo['ship_id'])
  {
    echo "<div style='color:#f00; font-size:16px;'>Base Construction Failed!</div>\n";
    echo "<div style='color:#f00; font-size:16px;'>Invalid Planet or Sector Information Supplied.</div>\n";

    return (boolean) false;
  }

  if (!is_numeric($planet_id) || !is_numeric($sector_id))
  {
    $ip = $_SERVER['REMOTE_ADDR'];
    $hack_id = 0x1337;
    adminlog($db, LOG_ADMIN_PLANETCHEAT, "{$hack_id}|{$ip}|{$planet_id}|{$sector_id}|{$playerinfo['ship_id']}");
    echo "<div style='color:#f00; font-size:16px;'>Base Construction Failed!</div>\n";

    return (boolean) false;
  }  // build a base

  Real_Space_Move($sector_id);
  echo "<br>Click <a href=planet.php?planet_id=$planet_id>here</A> to go to the Planet Menu<br><br>";

  if ($planetinfo['ore'] >= $base_ore && $planetinfo['organics'] >= $base_organics && $planetinfo['goods'] >= $base_goods && $planetinfo['credits'] >= $base_credits)
  {
    // Create The Base
    $update1 = $db->Execute("UPDATE {$db->prefix}planets SET base='Y', ore=$planetinfo[ore]-$base_ore, organics=$planetinfo[organics]-$base_organics, goods=$planetinfo[goods]-$base_goods, credits=$planetinfo[credits]-$base_credits WHERE planet_id=$planet_id");
    db_op_result ($db, $update1, __LINE__, __FILE__, $db_logging);

    // Update User Turns
    $update1b = $db->Execute("UPDATE {$db->prefix}ships SET turns=turns-1, turns_used=turns_used+1 WHERE ship_id=$playerinfo[ship_id]");
    db_op_result ($db, $update1b, __LINE__, __FILE__, $db_logging);

    // Refresh Plant Info
    $result3 = $db->Execute("SELECT * FROM {$db->prefix}planets WHERE planet_id=$planet_id");
    db_op_result ($db, $result3, __LINE__, __FILE__, $db_logging);
    $planetinfo=$result3->fields;

    // Notify User Of Base Results
    echo "$l_planet_bbuild<br><br>";
    // Calc Ownership and Notify User Of Results
    $ownership = calc_ownership($playerinfo['sector']);
    if (!empty($ownership))
    {
      echo "$ownership<p>";
    }
  }
}


function collect_credits($planetarray)
{
  global $db, $username, $sector_max;
  global $db_logging;

  $CS = "GO"; // Current State

  // Look up Player info that wants to collect the credits.
  $result1 = $db->Execute("SELECT * FROM {$db->prefix}ships WHERE ship_id='$user_ship_id' LIMIT 1");
  db_op_result ($db, $result1, __LINE__, __FILE__, $db_logging);
  $playerinfo = $result1->fields;

  // Set var as an Array.
  $s_p_pair = array();

  // create an array of sector -> planet pairs
  for ($i = 0; $i < count($planetarray); $i++)
  {
    $res = $db->Execute("SELECT * FROM {$db->prefix}planets WHERE planet_id=$planetarray[$i];");
    db_op_result ($db, $res, __LINE__, __FILE__, $db_logging);

    // Only add to array if the Player owns the Planet.
    if ($res->fields['owner'] == $playerinfo['ship_id'] && $res->fields['sector_id'] < $sector_max)
    {
      $s_p_pair[$i]= array($res->fields['sector_id'], $planetarray[$i]);
    }
    else
    {
      $hack_id = 20100401;
      $ip = $_SERVER['REMOTE_ADDR'];
      $planet_id = $res->fields['planet_id'];
      $sector_id = $res->fields['sector_id'];
      adminlog($db, LOG_ADMIN_PLANETCHEAT, "{$hack_id}|{$ip}|{$planet_id}|{$sector_id}|{$playerinfo['ship_id']}");
      break;
    }
  }

  // Sort the array so that it is in order of sectors, lowest number first, not closest
  sort($s_p_pair);
  reset($s_p_pair);

  // run through the list of sector planet pairs realspace moving to each sector and then performing the transfer.
  // Based on the way realspace works we don't need a sub loop -- might add a subloop to clean things up later.

  for ($i = 0; $i < count($s_p_pair) && $CS == "GO"; $i++)
  {
    echo "<br>";
    $CS = Real_space_move($s_p_pair[$i][0]);

    if ($CS == "HOSTILE")
    {
      $CS = "GO";
    } else if ($CS == "GO")
    {
      $CS = Take_Credits($s_p_pair[$i][0], $s_p_pair[$i][1]);
    }
    else
    {
      echo "<br> NOT ENOUGH TURNS TO TAKE CREDITS<br>";
    }
    echo "<br>";
  }

  if ($CS != "GO" && $CS != "HOSTILE")
  {
    echo "<br>Not enough turns to complete credit collection<br>";
  }

  echo "<br>";
  echo "Click <a href=planet_report.php?PRepType=1>here</A> to return to the Planet Status Report<br>";
}


function change_planet_production($prodpercentarray)
{
//  NOTES on what this function does and how
//  Declares some global variables so they are accessable
//    $db and default production values from the config.php file
//
//  We need to track what the player_id is and what corp they belong to if they belong to a corp,
//    these two values are not passed in as arrays
//    ship_id = the owner of the planet          ($ship_id = $prodpercentarray['ship_id'])
//    team_id = the corperation creators ship_id ($team_id = $prodpercentarray['team_id'])
//
//  First we generate a list of values based on the commodity
//    (ore, organics, goods, energy, fighters, torps, corp, team, sells)
//
//  Second we generate a second list of values based on the planet_id
//  Because team and ship_id are not arrays we do not pass them through the second list command.
//  When we write the ore production percent we also clear the selling and corp values out of the db
//  When we pass through the corp array we set the value to $team we grabbed out of the array.
//  in the sells and corp the prodpercent = the planet_id.
//
//  We run through the database checking to see if any planet production is greater than 100, or possibly negative
//    if so we set the planet to the default values and report it to the player.
//
//  There has got to be a better way, but at this time I am not sure how to do it.
//  Off the top of my head if we could sort the data passed in, in order of planets we could check before we do the writes
//  This would save us from having to run through the database a second time checking our work.

//  This should patch the game from being hacked with planet Hack.

  global $db;
  global $db_logging;
  global $default_prod_ore, $default_prod_organics, $default_prod_goods, $default_prod_energy, $default_prod_fighters, $default_prod_torp;
  global $username, $l_unnamed;

  $result = $db->Execute("SELECT ship_id,team FROM {$db->prefix}ships WHERE ship_id='$user_ship_id'");
  db_op_result ($db, $result, __LINE__, __FILE__, $db_logging);
  $ship_id = $result->fields['ship_id'];
  $team_id = $result->fields['team'];

  $planet_hack = false;
  $hack_id     = 0x0000;
  $hack_count  = array(0, 0, 0);

  echo "Click <a href=planet_report.php?PRepType=2>here</A> to return to the Change Planet Production Report<br><br>";

  while (list($commod_type, $valarray) = each($prodpercentarray))
  {
    if ($commod_type != "team_id" && $commod_type != "ship_id")
    {
      while (list($planet_id, $prodpercent) = each($valarray))
      {
        if ($commod_type == "prod_ore" || $commod_type == "prod_organics" || $commod_type == "prod_goods" || $commod_type == "prod_energy" || $commod_type == "prod_fighters" || $commod_type == "prod_torp")
        {
          $res = $db->Execute("SELECT COUNT(*) AS owned_planet FROM {$db->prefix}planets WHERE planet_id=$planet_id AND owner = $ship_id");
          db_op_result ($db, $res, __LINE__, __FILE__, $db_logging);
          if ($res->fields['owned_planet'] == 0)
          {
            $ip = $_SERVER['REMOTE_ADDR'];
            $stamp = date("Y-m-d H:i:s");
            $planet_hack=true;
            $hack_id = 0x18582;
            $hack_count[0]++;
            adminlog($db, LOG_ADMIN_PLANETCHEAT, "{$hack_id}|{$ip}|{$planet_id}|{$ship_id}|commod_type={$commod_type}");
          }

          $resx = $db->Execute("UPDATE {$db->prefix}planets SET $commod_type=$prodpercent WHERE planet_id=$planet_id AND owner = $ship_id");
          db_op_result ($db, $resx, __LINE__, __FILE__, $db_logging);
          $resy = $db->Execute("UPDATE {$db->prefix}planets SET sells='N' WHERE planet_id=$planet_id AND owner = $ship_id");
          db_op_result ($db, $resy, __LINE__, __FILE__, $db_logging);
          $resz = $db->Execute("UPDATE {$db->prefix}planets SET corp=0 WHERE planet_id=$planet_id AND owner = $ship_id");
          db_op_result ($db, $resz, __LINE__, __FILE__, $db_logging);
        }
        elseif ($commod_type == "sells")
        {
          $resx = $db->Execute("UPDATE {$db->prefix}planets SET sells='Y' WHERE planet_id=$prodpercent AND owner = $ship_id");
          db_op_result ($db, $resx, __LINE__, __FILE__, $db_logging);
        }
        elseif ($commod_type == "corp")
        {
          /* Compare entered team_id and one in the db */
          /* If different then use one from db */

          $res = $db->Execute("SELECT {$db->prefix}ships.team as owner FROM {$db->prefix}ships, {$db->prefix}planets WHERE ( {$db->prefix}ships.ship_id = {$db->prefix}planets.owner ) AND ( {$db->prefix}planets.planet_id ='$prodpercent')");
          db_op_result ($db, $res, __LINE__, __FILE__, $db_logging);
          if ($res) $team_id=$res->fields['owner']; else $team_id = 0;

          $resx = $db->Execute("UPDATE {$db->prefix}planets SET corp=$team_id WHERE planet_id=$prodpercent AND owner = $ship_id");
          db_op_result ($db, $resx, __LINE__, __FILE__, $db_logging);
          if ($prodpercentarray['team_id'] != $team_id)
          {
            /* Oh dear they are different so send admin a log */
            $ip = $_SERVER['REMOTE_ADDR'];
            $stamp = date("Y-m-d H:i:s");
            $planet_hack=true;
            $hack_id = 0x18531;
            $hack_count[1]++;
            adminlog($db, LOG_ADMIN_PLANETCHEAT,"{$hack_id}|{$ip}|{$prodpercent}|{$ship_id}|{$prodpercentarray[team_id]} not {$team_id}");
          }
        }
        else
        {
          $ip = $_SERVER['REMOTE_ADDR'];
          $stamp = date("Y-m-d H:i:s");
          $planet_hack=true;
          $hack_id = 0x18598;
          $hack_count[2]++;
          adminlog($db, LOG_ADMIN_PLANETCHEAT,"{$hack_id}|{$ip}|{$planet_id}|{$ship_id}|commod_type={$commod_type}");
        }
      }
    }
  }

  if ($planet_hack)
  {
    $serial_data = serialize($prodpercentarray);
    adminlog($db, LOG_ADMIN_PLANETCHEAT+1000, "{$ship_id}|{$serial_data}");
    printf("<font color=\"red\"><strong>Your Cheat has been logged to the admin (%08x) [%02X:%02X:%02X].</strong></font><br>\n", (int)$hack_id, (int)$hack_count[0], (int)$hack_count[1], (int)$hack_count[2]);
  }

  echo "<br>";
  echo "Production Percentages Updated <br><br>";
  echo "Checking Values for excess of 100% and negative production values <br><br>";

  $res = $db->Execute("SELECT * FROM {$db->prefix}planets WHERE owner=$ship_id ORDER BY sector_id");
  db_op_result ($db, $res, __LINE__, __FILE__, $db_logging);
  $i = 0;
  if ($res)
  {
    while (!$res->EOF)
    {
      $planets[$i] = $res->fields;
      $i++;
      $res->MoveNext();
    }

    foreach ($planets as $planet)
    {
      if (empty($planet['name']))
      {
        $planet['name'] = $l_unnamed;
      }

      if ($planet['prod_ore'] < 0)
        $planet['prod_ore'] = 110;
      if ($planet['prod_organics'] < 0)
        $planet['prod_organics'] = 110;
      if ($planet['prod_goods'] < 0)
        $planet['prod_goods'] = 110;
      if ($planet['prod_energy'] < 0)
        $planet['prod_energy'] = 110;
      if ($planet['prod_fighters'] < 0)
        $planet['prod_fighters'] = 110;
      if ($planet['prod_torp'] < 0)
        $planet['prod_torp'] = 110;

      if ($planet['prod_ore'] + $planet['prod_organics'] + $planet['prod_goods'] + $planet['prod_energy'] + $planet['prod_fighters'] + $planet['prod_torp'] > 100)
      {
        echo "Planet $planet[name] in sector $planet[sector_id] has a negative production value or exceeds 100% production.  Resetting to default production values<br>";
        $resa = $db->Execute("UPDATE {$db->prefix}planets SET prod_ore=$default_prod_ore           WHERE planet_id=$planet[planet_id]");
        db_op_result ($db, $resa, __LINE__, __FILE__, $db_logging);

        $resb = $db->Execute("UPDATE {$db->prefix}planets SET prod_organics=$default_prod_organics WHERE planet_id=$planet[planet_id]");
        db_op_result ($db, $resb, __LINE__, __FILE__, $db_logging);

        $resc = $db->Execute("UPDATE {$db->prefix}planets SET prod_goods=$default_prod_goods       WHERE planet_id=$planet[planet_id]");
        db_op_result ($db, $resc, __LINE__, __FILE__, $db_logging);

        $resd = $db->Execute("UPDATE {$db->prefix}planets SET prod_energy=$default_prod_energy     WHERE planet_id=$planet[planet_id]");
        db_op_result ($db, $resd, __LINE__, __FILE__, $db_logging);

        $rese = $db->Execute("UPDATE {$db->prefix}planets SET prod_fighters=$default_prod_fighters WHERE planet_id=$planet[planet_id]");
        db_op_result ($db, $rese, __LINE__, __FILE__, $db_logging);

        $resf = $db->Execute("UPDATE {$db->prefix}planets SET prod_torp=$default_prod_torp         WHERE planet_id=$planet[planet_id]");
        db_op_result ($db, $resf, __LINE__, __FILE__, $db_logging);
      }
    }
  }
} // <== Moved from line 215 to fix Invalid argument supplied for foreach().

function Take_Credits($sector_id, $planet_id)
{
  global $db, $username;
  global $db_logging;
  global $l_unnamed;
  
  // Get basic Database information (ship and planet)
  $res = $db->Execute("SELECT * FROM {$db->prefix}ships WHERE ship_id='$user_ship_id'");
  db_op_result ($db, $res, __LINE__, __FILE__, $db_logging);
  $playerinfo = $res->fields;

  $res = $db->Execute("SELECT * FROM {$db->prefix}planets WHERE planet_id=$planet_id");
  db_op_result ($db, $res, __LINE__, __FILE__, $db_logging);
  $planetinfo = $res->fields;

  // Set the name for unamed planets to be "unnamed"
  if (empty($planetinfo['name']))
  {
    $planet['name'] = $l_unnamed;
  }

  //verify player is still in same sector as the planet
  if ($playerinfo['sector'] == $planetinfo['sector_id'])
  {
    if ($playerinfo['turns'] >= 1)
    {
      // verify player owns the planet to take credits from
      if ($planetinfo['owner'] == $playerinfo['ship_id'])
      {
        // get number of credits from the planet and current number player has on ship
        $CreditsTaken = $planetinfo['credits'];
        $CreditsOnShip = $playerinfo['credits'];
        $NewShipCredits = $CreditsTaken + $CreditsOnShip;

        // update the planet record for credits
        $res = $db->Execute("UPDATE {$db->prefix}planets SET credits=0 WHERE planet_id=$planetinfo[planet_id]");
        db_op_result ($db, $res, __LINE__, __FILE__, $db_logging);

        // update the player record
        // credits
        $res = $db->Execute("UPDATE {$db->prefix}ships SET credits=$NewShipCredits WHERE ship_id='$user_ship_id'");
        db_op_result ($db, $res, __LINE__, __FILE__, $db_logging);
        // turns
        $res = $db->Execute("UPDATE {$db->prefix}ships SET turns=turns-1 WHERE ship_id='$user_ship_id'");
        db_op_result ($db, $res, __LINE__, __FILE__, $db_logging);

        echo "Took " . NUMBER($CreditsTaken) . " Credits from planet $planetinfo[name]. <br>";
        echo "Your ship - " . $playerinfo['ship_name'] . " - now has " . NUMBER($NewShipCredits) . " onboard. <br>";
        $retval = "GO";
      }
      else
      {
        echo "<br><br>You do not own planet {$planetinfo['name']} !!<br><br>";
        $retval = "BREAK-INVALID";
      }
    }
    else
    {
      echo "<br><br>You do not have enough turns to take credits from $planetinfo[name] in sector $planetinfo[sector_id]<br><br>";
      $retval = "BREAK-TURNS";
    }
  }
  else
  {
    echo "<br><br>You must be in the same sector as the planet to transfer to/from the planet<br><br>";
    $retval = "BREAK-SECTORS";
  }

  return($retval);
}

function Real_Space_Move($destination)
{
  global $db;
  global $level_factor, $mine_hullsize;
  global $username;
  global $db_logging;

  global $l_rs_ready, $l_rs_movetime, $l_rs_noturns;

  $res = $db->Execute("SELECT * FROM {$db->prefix}ships WHERE ship_id='$user_ship_id'");
  db_op_result ($db, $res, __LINE__, __FILE__, $db_logging);
  $playerinfo = $res->fields;

  $result2 = $db->Execute("SELECT angle1,angle2,distance FROM {$db->prefix}universe WHERE sector_id=$playerinfo[sector]");
  db_op_result ($db, $result2, __LINE__, __FILE__, $db_logging);
  $start = $result2->fields;
  $result3 = $db->Execute("SELECT angle1,angle2,distance FROM {$db->prefix}universe WHERE sector_id=$destination");
  db_op_result ($db, $result3, __LINE__, __FILE__, $db_logging);
  $finish = $result3->fields;

  $deg = pi() / 180;
  $sa1 = $start['angle1'] * $deg;
  $sa2 = $start['angle2'] * $deg;
  $fa1 = $finish['angle1'] * $deg;
  $fa2 = $finish['angle2'] * $deg;
  $x = ($start['distance'] * sin($sa1) * cos($sa2)) - ($finish['distance'] * sin($fa1) * cos($fa2));
  $y = ($start['distance'] * sin($sa1) * sin($sa2)) - ($finish['distance'] * sin($fa1) * sin($fa2));
  $z = ($start['distance'] * cos($sa1)) - ($finish['distance'] * cos($fa1));
  $distance = round(sqrt(pow ($x, 2) + pow ($y, 2) + pow ($z, 2)));
  $shipspeed = pow ($level_factor, $playerinfo['engines']);
  $triptime = round($distance / $shipspeed);

  if ($triptime == 0 && $destination != $playerinfo['sector'])
  {
    $triptime = 1;
  }

  if ($playerinfo['dev_fuelscoop'] == "Y")
  {
    $energyscooped = $distance * 100;
  }
  else
  {
    $energyscooped = 0;
  }

  if ($playerinfo['dev_fuelscoop'] == "Y" && $energyscooped == 0 && $triptime == 1)
  {
    $energyscooped = 100;
  }
  $free_power = NUM_ENERGY($playerinfo['power']) - $playerinfo['ship_energy'];

  // amount of energy that can be stored is less than amount scooped amount scooped is set to what can be stored
  if ($free_power < $energyscooped)
  {
    $energyscooped = $free_power;
  }

  // make sure energyscooped is not null
  if (!isset($energyscooped))
  {
    $energyscooped = "0";
  }

  // make sure energyscooped not negative, or decimal
  if ($energyscooped < 1)
  {
    $energyscooped = 0;
  }

  // check to see if already in that sector
  if ($destination == $playerinfo['sector'])
  {
    $triptime = 0;
    $energyscooped = 0;
  }

  if ($triptime > $playerinfo['turns'])
  {
    $l_rs_movetime=str_replace("[triptime]", NUMBER($triptime), $l_rs_movetime);
    echo "$l_rs_movetime<br><br>";
    echo "$l_rs_noturns";
    $resx = $db->Execute("UPDATE {$db->prefix}ships SET cleared_defences=' ' WHERE ship_id=$playerinfo[ship_id]");
    db_op_result ($db, $resx, __LINE__, __FILE__, $db_logging);

    $retval = "BREAK-TURNS";
  }
  else
  {

// modified from traderoute.php
// Sector Defense Check
  $hostile = 0;

  $result99 = $db->Execute("SELECT * FROM {$db->prefix}sector_defence WHERE sector_id = $destination AND ship_id <> $playerinfo[ship_id]");
  db_op_result ($db, $result99, __LINE__, __FILE__, $db_logging);
  if (!$result99->EOF)
  {
     $fighters_owner = $result99->fields;
     $nsresult = $db->Execute("SELECT * FROM {$db->prefix}ships WHERE ship_id=$fighters_owner[ship_id]");
     db_op_result ($db, $nsresult, __LINE__, __FILE__, $db_logging);
     $nsfighters = $nsresult->fields;
     if ($nsfighters['team'] != $playerinfo['team'] || $playerinfo['team']==0)
     {
       $hostile = 1;
     }
  }

  $result98 = $db->Execute("SELECT * FROM {$db->prefix}sector_defence WHERE sector_id = $destination AND ship_id <> $playerinfo[ship_id]");
  db_op_result ($db, $result98, __LINE__, __FILE__, $db_logging);
  if (!$result98->EOF)
  {
     $fighters_owner = $result98->fields;
     $nsresult = $db->Execute("SELECT * FROM {$db->prefix}ships WHERE ship_id=$fighters_owner[ship_id]");
     db_op_result ($db, $nsresult, __LINE__, __FILE__, $db_logging);
     $nsfighters = $nsresult->fields;
     if ($nsfighters['team'] != $playerinfo['team'] || $playerinfo['team']==0)
     {
       $hostile = 1;
     }
  }

  if (($hostile > 0) && ($playerinfo['hull'] > $mine_hullsize))
  {
    $retval = "HOSTILE";
    // need to add a language value for this
    echo "CANNOT MOVE TO SECTOR $destination THROUGH HOSTILE DEFENSES<br>";


  } else
  {
       $stamp = date("Y-m-d H-i-s");
       $update = $db->Execute("UPDATE {$db->prefix}ships SET last_login='$stamp',sector=$destination,ship_energy=ship_energy+$energyscooped,turns=turns-$triptime,turns_used=turns_used+$triptime WHERE ship_id=$playerinfo[ship_id]");
       db_op_result ($db, $update, __LINE__, __FILE__, $db_logging);
       $l_rs_ready = str_replace("[sector]", $destination, $l_rs_ready);

       $l_rs_ready = str_replace("[triptime]", NUMBER($triptime), $l_rs_ready);
       $l_rs_ready = str_replace("[energy]", NUMBER($energyscooped), $l_rs_ready);
       echo "$l_rs_ready<br>";
       $retval = "GO";
  }
 }

  return($retval);
}
?>
</div></div>
<?
include "footer.php";
?>

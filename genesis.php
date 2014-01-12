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
// File: genesis.php

// If anyone who's coded this thing is willing to update it to
// support multiple planets, go ahead. I suggest removing this
// code completely from here and putting it in the planet menu
// instead. Easier to manage, makes more sense too.

include "config/config.php";
updatecookie ();

// New database driven language entries
load_languages($db, $lang, array('genesis', 'common', 'global_includes', 'global_funcs', 'footer', 'news'), $langvars, $db_logging);

$title = $l_gns_title;
include "header.php";

if (checklogin () )
{
    die ();
}
?>
<div class="tablecell content both-border">
	<div class="pad">
<?
// Adding db lock to prevent more than 5 planets in a sector
$resx = $db->Execute("LOCK TABLES {$db->prefix}ships WRITE, {$db->prefix}planets WRITE, {$db->prefix}universe READ, {$db->prefix}zones READ, {$db->prefix}adodb_logsql WRITE;");
db_op_result ($db, $resx, __LINE__, __FILE__, $db_logging);

$result = $db->Execute("SELECT * FROM {$db->prefix}ships WHERE email=?;", array($username));
db_op_result ($db, $result, __LINE__, __FILE__, $db_logging);
$playerinfo = $result->fields;

$result2 = $db->Execute("SELECT * FROM {$db->prefix}universe WHERE sector_id=?;", array($playerinfo['sector']));
db_op_result ($db, $result2, __LINE__, __FILE__, $db_logging);
$sectorinfo = $result2->fields;

$result3 = $db->Execute("SELECT * FROM {$db->prefix}planets WHERE sector_id=?;", array($playerinfo['sector']));
db_op_result ($db, $result3, __LINE__, __FILE__, $db_logging);
$planetinfo = $result3->fields;
$num_planets = $result3->RecordCount();

// Generate Planetname
$planetname = substr($playerinfo['character_name'],0,1) . substr($playerinfo['ship_name'],0,1) . "-" . $playerinfo['sector'] . "-" . ($num_planets + 1);

bigtitle ();

$destroy = null;
if (array_key_exists('destroy', $_GET) == true)//isset($_GET['destroy']))
{
    $destroy = $_GET['destroy'];
}

if ($playerinfo['turns'] < 1)
{
    echo $l_gns_turn;
}
elseif ($playerinfo['on_planet'] == 'Y')
{
    echo $l_gns_onplanet;
}
elseif ($num_planets >= $max_planets_sector)
{
    echo $l_gns_full;
}
elseif ($sectorinfo['sector_id'] >= $sector_max )
{
    echo "Invalid sector<br>\n";
}
elseif ($playerinfo['dev_genesis'] < 1)
{
  echo $l_gns_nogenesis;
}
else
{
    $res = $db->Execute("SELECT allow_planet, corp_zone, owner FROM {$db->prefix}zones WHERE zone_id='$sectorinfo[zone_id]'");
    db_op_result ($db, $res, __LINE__, __FILE__, $db_logging);
    $zoneinfo = $res->fields;
    if ($zoneinfo['allow_planet'] == 'N')
    {
        echo $l_gns_forbid;
    }
    elseif ($zoneinfo['allow_planet'] == 'L')
    {
        if ($zoneinfo['corp_zone'] == 'N')
        {
            if ($playerinfo['team'] == 0 && $zoneinfo['owner'] != $playerinfo['ship_id'])
            {
                echo $l_gns_bforbid;
            }
            else
            {
                $res = $db->Execute("SELECT team FROM {$db->prefix}ships WHERE ship_id=$zoneinfo[owner]");
                db_op_result ($db, $res, __LINE__, __FILE__, $db_logging);
                $ownerinfo = $res->fields;
                if ($ownerinfo['team'] != $playerinfo['team'])
                {
                    echo $l_gns_bforbid;
                }
                else
                {
                    $query1 = "INSERT INTO {$db->prefix}planets VALUES(NULL, $playerinfo[sector], '$planetname', 0, 0, 0, 0, 0, 0, 0, 0, $playerinfo[ship_id], 0, 'N', 'N', $default_prod_organics, $default_prod_ore, $default_prod_goods, $default_prod_energy, $default_prod_fighters, $default_prod_torp, 'N')";
                    $update1 = $db->Execute($query1);
                    db_op_result ($db, $update1, __LINE__, __FILE__, $db_logging);
                    $query2 = "UPDATE {$db->prefix}ships SET turns_used=turns_used+1, turns=turns-1, dev_genesis=dev_genesis-1 WHERE ship_id=$playerinfo[ship_id]";
                    $update2 = $db->Execute($query2);
                    db_op_result ($db, $update2, __LINE__, __FILE__, $db_logging);
                    echo $l_gns_pcreate;
                }
            }
        }
        elseif ($playerinfo['team'] != $zoneinfo['owner'])
        {
            echo $l_gns_bforbid;
        }
        else
        {
            $query1 = "INSERT INTO {$db->prefix}planets VALUES(NULL, $playerinfo[sector], '$planetname', 0, 0, 0, 0, 0, 0, 0, 0, $playerinfo[ship_id], 0, 'N', 'N', $default_prod_organics, $default_prod_ore, $default_prod_goods, $default_prod_energy, $default_prod_fighters, $default_prod_torp, 'N')";
            $update1 = $db->Execute($query1);
            db_op_result ($db, $update1, __LINE__, __FILE__, $db_logging);
            $query2 = "UPDATE {$db->prefix}ships SET turns_used=turns_used+1, turns=turns-1, dev_genesis=dev_genesis-1 WHERE ship_id=$playerinfo[ship_id]";
            $update2 = $db->Execute($query2);
            db_op_result ($db, $update2, __LINE__, __FILE__, $db_logging);
            echo $l_gns_pcreate;
        }
    }
    else
    {
        $query1 = "INSERT INTO {$db->prefix}planets VALUES(NULL, $playerinfo[sector], '$planetname', 0, 0, 0, 0, 0, 0, 0, 0, $playerinfo[ship_id], 0, 'N', 'N', $default_prod_organics, $default_prod_ore, $default_prod_goods, $default_prod_energy, $default_prod_fighters, $default_prod_torp, 'N')";
        $update1 = $db->Execute($query1);
        db_op_result ($db, $update1, __LINE__, __FILE__, $db_logging);
        $query2 = "UPDATE {$db->prefix}ships SET turns_used=turns_used+1, turns=turns-1, dev_genesis=dev_genesis-1 WHERE ship_id=$playerinfo[ship_id]";
        $update2 = $db->Execute($query2);
        db_op_result ($db, $update2, __LINE__, __FILE__, $db_logging);
        echo $l_gns_pcreate;
    }
}

$resx = $db->Execute("UNLOCK TABLES");
db_op_result ($db, $resx, __LINE__, __FILE__, $db_logging);
echo "<br><br>";

TEXT_GOTOMAIN();
?>
</div></div>
<?
include "footer.php";
?>

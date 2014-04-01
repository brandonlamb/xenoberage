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
// File: plaent3.php

include "config/config.php";
updatecookie ();

// New database driven language entries
load_languages($db, $lang, array('planet', 'common', 'global_includes', 'global_funcs', 'footer', 'news'), $langvars, $db_logging);

$title = $l_planet3_title;
include "header.php";
?>
<div class="tablecell content both-border">
	<div class="pad">
<?
if (checklogin () )
{
    die ();
}

// Fixed The Phantom Planet Transfer Bug
// Needs to be validated and type cast into their correct types.
// [GET]
// (int)    planet_id

// [POST]
// (int)    trade_ore
// (int)    trade_organics
// (int)    trade_goods
// (int)    trade_energy

// Empty out Planet and Ship vars
$planetinfo = null;
$playerinfo = null;

// Validate and set the type of $_POST vars
$trade_ore = (int) $_POST['trade_ore'];
$trade_organics = (int) $_POST['trade_organics'];
$trade_goods = (int) $_POST['trade_goods'];
$trade_energy = (int) $_POST['trade_energy'];

// Validate and set the type of $_GET vars;
$planet_id = (int) $_GET['planet_id'];

bigtitle ();

// Check if planet_id is valid.
if ($planet_id <= 0)
{
    echo "Invalid Planet<br><br>";
    TEXT_GOTOMAIN ();
    include "footer.php";
    die ();
}

$result = $db->Execute("SELECT * FROM {$db->prefix}ships WHERE ship_id='$user_ship_id'");
db_op_result ($db, $result, __LINE__, __FILE__, $db_logging);
$playerinfo = $result->fields;

$result2 = $db->Execute("SELECT * FROM {$db->prefix}planets WHERE planet_id=$planet_id");
db_op_result ($db, $result2, __LINE__, __FILE__, $db_logging);
$planetinfo = $result2->fields;

// Check to see if it returned valid planet info.
if ($planetinfo == false)
{
    echo "Invalid Planet<br><br>";
    TEXT_GOTOMAIN ();
    die ();
}

if ($playerinfo['turns'] < 1)
{
    echo $l_trade_turnneed . '<br><br>';
    TEXT_GOTOMAIN ();
    include "footer.php";
    die ();
}

if ($planetinfo['sector_id'] != $playerinfo['sector'])
{
    echo $l_planet2_sector . '<br><br>';
    TEXT_GOTOMAIN ();
    include "footer.php";
    die ();
}

if (empty ($planetinfo))
{
    echo "$l_planet_none<br>";
    TEXT_GOTOMAIN ();
    include "footer.php";
    die ();
}

$trade_ore = round (abs ($trade_ore));
$trade_organics = round (abs ($trade_organics));
$trade_goods = round (abs ($trade_goods));
$trade_energy = round (abs ($trade_energy));
$ore_price = ($ore_price + $ore_delta / 4);
$organics_price = ($organics_price + $organics_delta / 4);
$goods_price = ($goods_price + $goods_delta / 4);
$energy_price = ($energy_price + $energy_delta / 4);

if ($planetinfo['sells'] == 'Y')
{
    $cargo_exchanged = $trade_ore + $trade_organics + $trade_goods;

    $free_holds = NUM_HOLDS ($playerinfo['hull']) - $playerinfo['ship_ore'] - $playerinfo['ship_organics'] - $playerinfo['ship_goods'] - $playerinfo['ship_colonists'];
    $free_power = NUM_ENERGY ($playerinfo['power']) - $playerinfo['ship_energy'];
    $total_cost = ($trade_ore * $ore_price) + ($trade_organics * $organics_price) + ($trade_goods * $goods_price) + ($trade_energy * $energy_price);

    if ($free_holds < $cargo_exchanged)
    {
        echo "$l_notenough_cargo  <a href=planet.php?planet_id=$planet_id>$l_clickme</a> $l_toplanetmenu<br><br>";
    }
    elseif ($trade_energy > $free_power)
    {
        echo "$l_notenough_power <a href=planet.php?planet_id=$planet_id>$l_clickme</a> $l_toplanetmenu<br><br>";
    }
    elseif ($playerinfo['turns'] < 1 )
    {
        echo "$l_notenough_turns<br><br>";
    }
    elseif ($playerinfo['credits'] < $total_cost)
    {
        echo "$l_notenough_credits<br><br>";
    }
    elseif ($trade_organics > $planetinfo['organics'])
    {
        echo "$l_exceed_organics  ";
    }
    elseif ($trade_ore > $planetinfo['ore'])
    {
        echo "$l_exceed_ore  ";
    }
    elseif ($trade_goods > $planetinfo['goods'])
    {
        echo "$l_exceed_goods  ";
    }
    elseif ($trade_energy > $planetinfo['energy'])
    {
        echo "$l_exceed_energy  ";
    }
    else
    {
        echo "$l_totalcost: $total_cost<br>$l_traded_ore: $trade_ore<br>$l_traded_organics: $trade_organics<br>$l_traded_goods: $trade_goods<br>$l_traded_energy: $trade_energy<br><br>";
        // Update ship cargo, credits and turns
        $trade_result = $db->Execute ("UPDATE {$db->prefix}ships SET turns=turns-1, turns_used=turns_used+1, credits=credits-$total_cost, ship_ore=ship_ore+$trade_ore, ship_organics=ship_organics+$trade_organics, ship_goods=ship_goods+$trade_goods, ship_energy=ship_energy+$trade_energy WHERE ship_id=$playerinfo[ship_id]");
        db_op_result ($db, $trade_result, __LINE__, __FILE__, $db_logging);

        $trade_result2 = $db->Execute ("UPDATE {$db->prefix}planets SET ore=ore-$trade_ore, organics=organics-$trade_organics, goods=goods-$trade_goods, energy=energy-$trade_energy, credits=credits+$total_cost WHERE planet_id=$planet_id");
        db_op_result ($db, $trade_result2, __LINE__, __FILE__, $db_logging);
        echo "$l_trade_complete<br><br>";
    }
}

gen_score ($planetinfo['owner']);
TEXT_GOTOMAIN ();
?>
</div></div>
<?
include "footer.php";
?>

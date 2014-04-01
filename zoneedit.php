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
// File: zoneedit.php

include "config/config.php";
updatecookie();

// New database driven language entries
load_languages($db, $lang, array('zoneedit', 'report', 'port', 'main', 'zoneinfo', 'common', 'global_includes', 'global_funcs', 'footer', 'news'), $langvars, $db_logging);

$title = $l_ze_title;
include "header.php";
?>
<div class="tablecell content both-border">
	<div class="pad">
<?
if (checklogin())
{
    die();
}

bigtitle();

$command = null;
if (array_key_exists('command', $_GET) == true)
{
    $command = $_GET['command'];
}

$zone = null;
if (array_key_exists('zone', $_GET) == true)
{
    $zone = $_GET['zone'];
}

$name = null;
if (array_key_exists('name', $_POST) == true)
{
    $name = $_POST['name'];
}

$beacons = null;
if (array_key_exists('beacons', $_POST) == true)
{
    $beacons = $_POST['beacons'];
}

$attacks = null;
if (array_key_exists('attacks', $_POST) == true)
{
    $attacks = $_POST['attacks'];
}

$warpedits = null;
if (array_key_exists('warpedits', $_POST) == true)
{
    $warpedits = $_POST['warpedits'];
}

$defenses = null;
if (array_key_exists('defenses', $_POST) == true)
{
    $defenses = $_POST['defenses'];
}

$planets = null;
if (array_key_exists('planets', $_POST) == true)
{
    $planets = $_POST['planets'];
}

$trades = null;
if (array_key_exists('trades', $_POST) == true)
{
    $trades = $_POST['trades'];
}


$res = $db->Execute("SELECT * FROM {$db->prefix}zones WHERE zone_id='$zone'");
db_op_result ($db, $res, __LINE__, __FILE__, $db_logging);
if ($res->EOF)
{
    zoneedit_die($l_zi_nexist);
}
$curzone = $res->fields;

if ($curzone['corp_zone'] == 'N')
{
    $result = $db->Execute("SELECT ship_id FROM {$db->prefix}ships WHERE ship_id='$user_ship_id'");
    db_op_result ($db, $result, __LINE__, __FILE__, $db_logging);
    $ownerinfo = $result->fields;
}
else
{
    $result = $db->Execute("SELECT creator, id FROM {$db->prefix}teams WHERE creator=$curzone[owner]");
    db_op_result ($db, $result, __LINE__, __FILE__, $db_logging);
    $ownerinfo = $result->fields;
}

if (($curzone['corp_zone'] == 'N' && $curzone['owner'] != $ownerinfo['ship_id']) || ($curzone['corp_zone'] == 'Y' && $curzone['owner'] != $ownerinfo['id'] && $row['owner'] == $ownerinfo['creator']))
{
    zoneedit_die($l_ze_notowner);
}

if ($command == 'change')
{
    zoneedit_change();
}

$ybeacon = null;
$nbeacon = null;
$lbeacon = null;
if ($curzone['allow_beacon'] == 'Y')
{
    $ybeacon = "checked";
}
elseif ($curzone['allow_beacon'] == 'N')
{
    $nbeacon = "checked";
}
else
{
    $lbeacon = "checked";
}

$yattack = null;
$nattack = null;
if ($curzone['allow_attack'] == 'Y')
{
    $yattack = "checked";
}
else
{
    $nattack = "checked";
}

$ywarpedit = null;
$nwarpedit = null;
$lwarpedit = null;
if ($curzone['allow_warpedit'] == 'Y')
{
    $ywarpedit = "checked";
}
elseif ($curzone['allow_warpedit'] == 'N')
{
    $nwarpedit = "checked";
}
else
{
    $lwarpedit = "checked";
}

$yplanet = null;
$nplanet = null;
$lplanet = null;
if ($curzone['allow_planet'] == 'Y')
{
    $yplanet = "checked";
}
elseif ($curzone['allow_planet'] == 'N')
{
    $nplanet = "checked";
}
else
{
    $lplanet = "checked";
}

$ytrade = null;
$ntrade = null;
$ltrade = null;
if ($curzone['allow_trade'] == 'Y')
{
    $ytrade = "checked";
}
elseif ($curzone['allow_trade'] == 'N')
{
    $ntrade = "checked";
}
else
{
    $ltrade = "checked";
}

$ydefense = null;
$ndefense = null;
$ldefense = null;
if ($curzone['allow_defenses'] == 'Y')
{
    $ydefense = "checked";
}
elseif ($curzone['allow_defenses'] == 'N')
{
    $ndefense = "checked";
}
else
{
    $ldefense = "checked";
}

echo "<form action=zoneedit.php?command=change&zone=$zone method=post>" .
     "<table border=0><tr>" .
     "<td align=right><font size=2><strong>$l_ze_name : &nbsp;</strong></font></td>" .
     "<td><input type=text name=name size=30 maxlength=30 value=\"$curzone[zone_name]\"></td>" .
     "</tr><tr>" .
     "<td align=right><font size=2><strong>$l_ze_allow $l_beacons : &nbsp;</strong></font></td>" .
     "<td><input type=radio name=beacons value=Y $ybeacon>&nbsp;$l_yes&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type=radio name=beacons value=N $nbeacon>&nbsp;$l_no&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type=radio name=beacons value=L $lbeacon>&nbsp;$l_zi_limit</td>" .
     "</tr><tr>" .
     "<td align=right><font size=2><strong>$l_ze_attacks : &nbsp;</strong></font></td>" .
     "<td><input type=radio name=attacks value=Y $yattack>&nbsp;$l_yes&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type=radio name=attacks value=N $nattack>&nbsp;$l_no</td>" .
     "</tr><tr>" .
     "<td align=right><font size=2><strong>$l_ze_allow $l_warpedit : &nbsp;</strong></font></td>" .
     "<td><input type=radio name=warpedits value=Y $ywarpedit>&nbsp;$l_yes&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type=radio name=warpedits value=N $nwarpedit>&nbsp;$l_no&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type=radio name=warpedits value=L $lwarpedit>&nbsp;$l_zi_limit</td>" .
     "</tr><tr>" .
     "<td align=right><font size=2><strong>$l_zi_allow $l_sector_def : &nbsp;</strong></font></td>" .
     "<td><input type=radio name=defenses value=Y $ydefense>&nbsp;$l_yes&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type=radio name=defenses value=N $ndefense>&nbsp;$l_no&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type=radio name=defenses value=L $ldefense>&nbsp;$l_zi_limit</td>" .
     "</tr><tr>" .
     "<td align=right><font size=2><strong>$l_ze_genesis : &nbsp;</strong></font></td>" .
     "<td><input type=radio name=planets value=Y $yplanet>&nbsp;$l_yes&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type=radio name=planets value=N $nplanet>&nbsp;$l_no&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type=radio name=planets value=L $lplanet>&nbsp;$l_zi_limit</td>" .
     "</tr><tr>" .
     "<td align=right><font size=2><strong>$l_zi_allow $l_title_port : &nbsp;</strong></font></td>" .
     "<td><input type=radio name=trades value=Y $ytrade>&nbsp;$l_yes&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type=radio name=trades value=N $ntrade>&nbsp;$l_no&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type=radio name=trades value=L $ltrade>&nbsp;$l_zi_limit</td>" .
     "</tr><tr>" .
     "<td colspan=2 align=center><br><input type=submit value=$l_submit></td></tr>" .
     "</table>" .
     "</form>";

echo "<a href=zoneinfo.php?zone=$zone>$l_clickme</a> $l_ze_return.<p>";
TEXT_GOTOMAIN();

include "footer.php";

function zoneedit_change()
{
    global $zone;
    global $name;
    global $beacons;
    global $attacks;
    global $warpedits;
    global $planets;
    global $trades;
    global $defenses;
    global $l_clickme, $l_ze_saved, $l_ze_return;
    global $db, $db_logging;

    if (!get_magic_quotes_gpc())
    {
        $name = addslashes($name);
    }

    $resx = $db->Execute("UPDATE {$db->prefix}zones SET zone_name='$name', allow_beacon='$beacons', allow_attack='$attacks', allow_warpedit='$warpedits', allow_planet='$planets', allow_trade='$trades', allow_defenses='$defenses' WHERE zone_id=$zone");
    db_op_result ($db, $resx, __LINE__, __FILE__, $db_logging);
    echo $l_ze_saved . "<p>";
    echo "<a href=zoneinfo.php?zone=$zone>" . $l_clickme . "</a> " . $l_ze_return . ".<p>";
    TEXT_GOTOMAIN();
?>
</div></div>
<?
    include "footer.php";
    die();
}

function zoneedit_die($error_msg)
{
    echo "<p>" . $error_msg . "<p>";
    TEXT_GOTOMAIN();
	?>
</div></div>
<?
    include "footer.php";
    die();
}
?>

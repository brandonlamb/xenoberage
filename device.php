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
// File: device.php

include "config/config.php";
updatecookie ();

// New database driven language entries
load_languages($db, $lang, array('device', 'common', 'global_includes', 'global_funcs', 'report', 'footer'), $langvars, $db_logging);

$title = $l_device_title;
$body_class = 'device';
include "header.php";

if ( checklogin () )
{
    die ();
}

$res = $db->Execute("SELECT * FROM {$db->prefix}ships WHERE ship_id='$user_ship_id'");
$playerinfo = $res->fields;
?>
<div class="tablecell content both-border">
	<div class="pad">
<?
bigtitle ();
echo "Your ship is equipped with the following devices (click on a device to use it):";
	/*
	Build table
	*/	
	?>
    <div class="general-table-container">
    <?

echo "<table><tbody>";
echo "<tr><td>Device</td><td>Quantity</td><td>Usage</td></tr>";
echo "<tr>";
echo "<td><a href='beacon.php'>Space Beacons</A></td><td><span class=\"table_word_white\">" . NUMBER($playerinfo['dev_beacon']) . "</span></td><td><span class=\"table_word_orange\">Manual</span></td>";
echo "</tr>";
echo "<tr>";
echo "<td><a href='warpedit.php'>Warp Editors</A></td><td><span class=\"table_word_white\">" . NUMBER($playerinfo['dev_warpedit']) . "</span></td><td><span class=\"table_word_orange\">Manual</span></td>";
echo "</tr>";
echo "<tr>";
echo "<td><a href='genesis.php'>Genesis Torpedoes</A></td><td><span class=\"table_word_white\">" . NUMBER($playerinfo['dev_genesis']) . "</span></td><td><span class=\"table_word_orange\">Manual</span></td>";
echo "</tr>";
echo "<tr>";
echo "<td><span class=\"table_word_gold\">Mine Deflectors</span></td><td><span class=\"table_word_white\">" . NUMBER($playerinfo['dev_minedeflector']) . "</span></td><td><span class=\"table_word_yellow\">Automatic</span></td>";
echo "</tr>";
echo "<tr>";
echo "<td><a href='mines.php?op=1'>Space Mines</A></td><td><span class=\"table_word_white\">" . NUMBER($playerinfo['torps']) . "</span></td><td><span class=\"table_word_orange\">Manual</span></td>";
echo "</tr>";
echo "<tr>";
echo "<td><a href='mines.php?op=2'>Space Fighters</A></td><td><span class=\"table_word_white\">" . NUMBER($playerinfo['ship_fighters']) . "</span></td><td><span class=\"table_word_orange\">Manual</span></td>";
echo "</tr>";
echo "<tr>";
echo "<td><a href='emerwarp.php'>Emergency Warp Device</A></td><td><span class=\"table_word_white\">" . NUMBER($playerinfo['dev_emerwarp']) . "</span></td><td><span class=\"table_word_orange\">Manual</span> / <span class=\"table_word_yellow\">Automatic</span></td>";
echo "</tr>";
echo "<tr>";
echo "<td><span class=\"table_word_gold\">Escape Pod</span></td><td><span class=\"table_word_white\">" . (($playerinfo['dev_escapepod'] == 'Y') ? $l_yes : $l_no) . "</span></td><td><span class=\"table_word_yellow\">Automatic</span></td>";
echo "</tr>";
echo "<tr>";
echo "<td><span class=\"table_word_gold\">Fuel Scoop</span></td><td><span class=\"table_word_white\">" . (($playerinfo['dev_fuelscoop'] == 'Y') ? $l_yes : $l_no) . "</span></td><td><span class=\"table_word_yellow\">Automatic</span></td>";
echo "</tr>";
echo "<tr>";
echo "<td><span class=\"table_word_gold\">Warp Analyser</span></td><td><span class=\"table_word_white\">" . (($playerinfo['dev_lssd'] == 'Y') ? $l_yes : $l_no) . "</span></td><td><span class=\"table_word_yellow\">Automatic</span></td>";
echo "</tr>";
echo "</tbody></table>";

?>
</div>
<?

TEXT_GOTOMAIN ();
?>
</div></div>
<?
include "footer.php";
?>

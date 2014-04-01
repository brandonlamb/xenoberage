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
// File: galaxy_new.php

include "config/config.php";
updatecookie ();

// New database driven language entries
load_languages($db, $lang, array('main', 'port', 'galaxy', 'common', 'global_includes', 'global_funcs', 'footer'), $langvars, $db_logging);

global $l_map_title;
$title = $l_map_title;
include "header.php";
?>
<div class="tablecell content both-border">
	<div class="pad">
<?
if (checklogin () )
{
    die();
}

$res = $db->Execute("SELECT * FROM {$db->prefix}ships WHERE ship_id='$user_ship_id'");
db_op_result ($db, $res, __LINE__, __FILE__, $db_logging);
$playerinfo = $res->fields;
$result3 = $db->Execute("SELECT distinct {$db->prefix}movement_log.sector_id, port_type, beacon FROM {$db->prefix}movement_log,{$db->prefix}universe WHERE ship_id = $playerinfo[ship_id] AND {$db->prefix}movement_log.sector_id={$db->prefix}universe.sector_id order by sector_id ASC;");
db_op_result ($db, $result3, __LINE__, __FILE__, $db_logging);
$row = $result3->fields;

bigtitle ();

$tile['special'] = "port-special.png";
$tile['ore'] = "port-ore.png";
$tile['organics'] = "port-organics.png";
$tile['energy'] = "port-energy.png";
$tile['goods'] = "port-goods.png";
$tile['none'] = "space.png";
$tile['unknown'] = "uspace.png";

$cur_sector= 0; // Clear this before iterating through the sectors

// Display sectors as imgs, and each class in css in header.php; then match the width and height here
$div_w = 20; // Only this width to match the included images
$div_h = 20; // Only this height to match the included images
$div_border = 2; // CSS border is 1 so this should be 2
$div_xmax = 40; // Where to wrap to next line
$div_ymax = $sector_max / $div_xmax;
$map_width = ($div_w + $div_border) * $div_xmax;  // Define the containing div to be the right width to wrap at $div_xmax

// Setup containing div to hold the width of the images
echo "\n<div id='map' style='position:relative;width:".$map_width."px'>\n";
for ($r = 0; $r < $div_ymax; $r++) // Loop the rows
{
    for ($c = 0; $c < $div_xmax; $c++) // Loop the columns
    {
        if (isset ($row['sector_id']) && ($row['sector_id'] == $cur_sector) && $row != false )
        {
            $p = $row['port_type'];
            // Build the alt text for each image
            $alt  = $l_sector . ": {$row['sector_id']} Port: {$row['port_type']} ";

            if (!is_null($row['beacon']))
            {
                $alt .= "{$row['beacon']}";
            }

            echo "\n<a href=\"rsmove.php?engage=1&amp;destination=" . $row['sector_id'] . "\">";
            echo "<img class='map ".$row['port_type']."' src='images/" . $tile[$p] . "' alt='" . $alt . "'></a> ";

            // Move to next explored sector in database results
            $result3->Movenext ();
            $row = $result3->fields;
            $cur_sector = $cur_sector + 1;
        }
        else
        {
            $p = 'unknown';
            // Build the alt text for each image
            $alt  = ($c+($div_xmax*$r)) . " - " . $l_unknown . " ";

            // I have not figured out why this formula works, but $row[sector_id] doesn't, so I'm not switching it.
            echo "<a href=\"rsmove.php?engage=1&amp;destination=". ($c+($div_xmax*$r)) ."\">";
            echo "<img class='map un' src='images/" . $tile[$p] . "' alt='" . $alt . "'></a> ";
            $cur_sector = $cur_sector + 1;
        }
    }
}

// This is the row numbers on the side of the map
for ($a = 1; $a < ($sector_max/$div_xmax +1); $a++)
{
    echo "\n<div style='position:absolute;left:".($map_width+10)."px;top:".(($a-1) * ($div_h+$div_border))."px;'>".(($a*$div_xmax)-1)."</div>";
}

echo "</div><div style='clear:both'></div><br>";
echo "    <div><img alt='" . $l_port . ": " . $l_special_port . "' src='images/{$tile['special']}'> &lt;- " . $l_special_port . "</div>\n";
echo "    <div><img alt='" . $l_port . ": " . $l_ore_port . "' src='images/{$tile['ore']}'> &lt;- " . $l_ore_port . "</div>\n";
echo "    <div><img alt='" . $l_port . ": " . $l_organics_port . "' src='images/{$tile['organics']}'> &lt;- " . $l_organics_port . "</div>\n";
echo "    <div><img alt='" . $l_port . ": " . $l_energy_port . "' src='images/{$tile['energy']}'> &lt;- " . $l_energy_port . "</div>\n";
echo "    <div><img alt='" . $l_port . ": " . $l_goods_port . "' src='images/{$tile['goods']}'> &lt;- " . $l_goods_port . "</div>\n";
echo "    <div><img alt='" . $l_port . ": " . $l_no_port . "' src='images/{$tile['none']}'> &lt;- " . $l_no_port . "</div>\n";
echo "    <div><img alt='" . $l_port . ": " . $l_unexplored . "' src='images/{$tile['unknown']}'> &lt;- " . $l_unexplored . "</div>\n";

echo "<br><br>";
TEXT_GOTOMAIN();
?>
</div></div>
<?
include "footer.php";
?>

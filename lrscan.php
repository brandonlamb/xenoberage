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
// File: lrscan.php

include "config/config.php";
updatecookie ();

// New database driven language entries
load_languages($db, $lang, array('main', 'lrscan', 'common', 'global_includes', 'global_funcs', 'combat', 'footer', 'news'), $langvars, $db_logging);

$title = $l_lrs_title;
include "header.php";
if (checklogin () )
{
    die ();
}
?>
<div class="tablecell content both-border">
	<div class="pad">
<?
bigtitle ();

if (isset($_GET['sector']))
{
    $sector = $_GET['sector'];
}
else
{
    $sector = null;
}

function get_player ($db, $ship_id)
{
    global $db_logging;

    $res = $db->Execute("SELECT character_name FROM {$db->prefix}ships WHERE ship_id = ?;", array($ship_id));
    db_op_result ($db, $res, __LINE__, __FILE__, $db_logging);
    if ($res)
    {
        $row = $res->fields;
        $character_name = $row['character_name'];
        return $character_name;
    }
    else
    {
        return "Unknown";
    }
}

// Get user info
$result = $db->Execute("SELECT * FROM {$db->prefix}ships WHERE ship_id=?", array($user_ship_id));
db_op_result ($db, $result, __LINE__, __FILE__, $db_logging);
$playerinfo = $result->fields;

if ($sector == "*")
{
    $num_links = 0;

    if (!$allow_fullscan)
    {
        echo $l_lrs_nofull . "<br><br>";
        TEXT_GOTOMAIN ();
        include "footer.php";
        die();
    }

    if ($playerinfo['turns'] < $fullscan_cost)
    {
        $l_lrs_noturns=str_replace("[turns]", $fullscan_cost, $l_lrs_noturns);
        echo $l_lrs_noturns . "<br><br>";
        TEXT_GOTOMAIN ();
        include "footer.php";
        die();
    }

    echo "$l_lrs_used " . NUMBER ($fullscan_cost) . " $l_lrs_turns. " . NUMBER ($playerinfo['turns'] - $fullscan_cost) . " $l_lrs_left.<br><br>";

    // Deduct the appropriate number of turns
    $resx = $db->Execute("UPDATE {$db->prefix}ships SET turns=turns-?, turns_used=turns_used+? WHERE ship_id=?;", array($fullscan_cost, $fullscan_cost, $playerinfo['ship_id']));
    db_op_result ($db, $resx, __LINE__, __FILE__, $db_logging);

    // User requested a full long range scan
    $l_lrs_reach=str_replace("[sector]", $playerinfo['sector'], $l_lrs_reach);
    echo $l_lrs_reach . "<br><br>";

	/*
	Build table
	*/	
	?>
    <div class="general-table-container">
    <?

    // Get sectors which can be reached from the player's current sector
    $result = $db->Execute("SELECT * FROM {$db->prefix}links WHERE link_start=? ORDER BY link_dest;", array($playerinfo['sector']));
    db_op_result ($db, $result, __LINE__, __FILE__, $db_logging);
    echo "<table><tbody>";
    echo "  <tr>";
    echo "    <td>$l_sector</td>";
    echo "    <td></td>";
    echo "    <td>$l_lrs_links</td>";
    echo "    <td>$l_lrs_ships</td>";
    echo "    <td colspan=2>$l_port</td>";
    echo "    <td>$l_planets</td>";
    echo "    <td>$l_mines</td>";
    echo "    <td>$l_fighters</td>";

    if ($playerinfo['dev_lssd'] == 'Y')
    {
        echo "    <td>" . $l_lss . "</td>";
    }

    echo "  </tr>";
    while (!$result->EOF)
    {
        $row = $result->fields;
        // Get number of sectors which can be reached from scanned sector
        $result2 = $db->Execute("SELECT COUNT(*) AS count FROM {$db->prefix}links WHERE link_start=?;", array($row['link_dest']));
        db_op_result ($db, $result2, __LINE__, __FILE__, $db_logging);
        $row2 = $result2->fields;
        $num_links = $row2['count'];

        // Get number of ships in scanned sector
        $result2 = $db->Execute("SELECT COUNT(*) AS count FROM {$db->prefix}ships WHERE sector=? AND on_planet='N' and ship_destroyed='N';", array($row['link_dest']));
        db_op_result ($db, $result2, __LINE__, __FILE__, $db_logging);
        $row2 = $result2->fields;
        $num_ships = $row2['count'];

        // Get port type and discover the presence of a planet in scanned sector
        $result2 = $db->Execute("SELECT * FROM {$db->prefix}universe WHERE sector_id=?;", array($row['link_dest']));
        db_op_result ($db, $result2, __LINE__, __FILE__, $db_logging);
        $result3 = $db->Execute("SELECT planet_id FROM {$db->prefix}planets WHERE sector_id=?;", array($row['link_dest']));
        db_op_result ($db, $result3, __LINE__, __FILE__, $db_logging);
        $resultSDa = $db->Execute("SELECT SUM(quantity) as mines from {$db->prefix}sector_defence WHERE sector_id=? and defence_type='M';", array($row['link_dest']));
        db_op_result ($db, $resultSDa, __LINE__, __FILE__, $db_logging);
        $resultSDb = $db->Execute("SELECT SUM(quantity) as fighters from {$db->prefix}sector_defence WHERE sector_id=? and defence_type='F';", array($row['link_dest']));
        db_op_result ($db, $resultSDb, __LINE__, __FILE__, $db_logging);

        $sectorinfo = $result2->fields;
        $defM = $resultSDa->fields;
        $defF = $resultSDb->fields;
        $port_type = $sectorinfo['port_type'];
        $has_planet = $result3->RecordCount();
        $has_mines = NUMBER ($defM['mines']);
        $has_fighters = NUMBER ($defF['fighters']);

        if ($port_type != "none")
        {
            $icon_alt_text = ucfirst (t_port($port_type));
            $icon_port_type_name = $port_type . ".png";
            $image_string = "<img align=absmiddle height=12 width=12 alt=\"$icon_alt_text\" src=\"images/$icon_port_type_name\">&nbsp;";
        }
        else
        {
            $image_string = "&nbsp;";
        }

        echo "<tr><td><a href=move.php?sector=$row[link_dest]>$row[link_dest]</a></td><td><a href=lrscan.php?sector=$row[link_dest]>Scan</a></td><td>$num_links</td><td>$num_ships</td><td width=12>$image_string</td><td>" . t_port($port_type) . "</td><td>$has_planet</td><td>$has_mines</td><td>$has_fighters</td>";
        if ($playerinfo['dev_lssd'] == 'Y')
        {
			echo "<td>";
            $resx = $db->Execute("SELECT * from {$db->prefix}movement_log WHERE ship_id <> ? AND sector_id = ? ORDER BY time DESC LIMIT 1;", array($playerinfo['ship_id'], $row['link_dest']));
            db_op_result ($db, $resx, __LINE__, __FILE__, $db_logging);
            if (!$resx)
            {
                echo "None";
            }
            else
            {
                $myrow = $resx->fields;
                echo get_player($db, $myrow['ship_id']);
            }
			echo "</td>";
        }

        echo "</tr>";
        $result->MoveNext();
    }
    echo "</tbody></table>";
	?>
    </div>
    <?
    if ($num_links == 0)
    {
        echo "$l_none.";
    }
    else
    {
        echo "<br>$l_lrs_click";
    }
}
else
{
    // User requested a single sector (standard) long range scan
    // Get scanned sector information
    $result2 = $db->Execute("SELECT * FROM {$db->prefix}universe WHERE sector_id=?;", array($sector));
    db_op_result ($db, $result2, __LINE__, __FILE__, $db_logging);
    $sectorinfo = $result2->fields;

    // Get sectors which can be reached through scanned sector
    $result3 = $db->Execute("SELECT link_dest FROM {$db->prefix}links WHERE link_start=? ORDER BY link_dest ASC;", array($sector));
    db_op_result ($db, $result3, __LINE__, __FILE__, $db_logging);
    $i=0;

    while (!$result3->EOF)
    {
        $links[$i] = $result3->fields['link_dest'];
        $i++;
        $result3->MoveNext();
    }
    $num_links=$i;

    // Get sectors which can be reached from the player's current sector
    $result3a = $db->Execute("SELECT link_dest FROM {$db->prefix}links WHERE link_start=?;", array($playerinfo['sector']));
    db_op_result ($db, $result3a, __LINE__, __FILE__, $db_logging);
    $i = 0;
    $flag = 0;

    while (!$result3a->EOF)
    {
        if ($result3a->fields['link_dest'] == $sector)
        {
            $flag = 1;
        }
        $i++;
        $result3a->MoveNext();
    }

    if ($flag == 0)
    {
        echo "$l_lrs_cantscan<br><br>";
        TEXT_GOTOMAIN();
        die();
    }
	/*
	Build table
	*/	
	?>
    <div class="general-table-container">
    <?

    echo "<table><tbody>";
    echo "<tr><td colspan=\"2\">Sector ".$sector;
    if ($sectorinfo['sector_name'] != "")
    {
        echo " ($sectorinfo[sector_name])";
    }
    echo "</tr>";
    echo "<tr><td class=\"long-rang-scan-titles-cell\"><span class=\"long-rang-scan-titles\">Links</span></td>";
    echo "<td>";
    if ($num_links == 0)
    {
        echo "$l_none";
    }
    else
    {
        for ($i = 0; $i < $num_links; $i++)
        {
            echo "$links[$i]";
            if ($i + 1 != $num_links)
            {
                echo ", ";
            }
        }
    }

    echo "</td></tr>";
    echo "<tr><td class=\"long-rang-scan-titles-cell\"><span class=\"long-rang-scan-titles\">Ships</span></td>";
    echo "<td>";
    if ($sector != 0)
    {
        // Get ships located in the scanned sector
        $result4 = $db->Execute("SELECT ship_id, ship_name, character_name, cloak FROM {$db->prefix}ships WHERE sector=? AND on_planet='N';", array($sector));
        db_op_result ($db, $result4, __LINE__, __FILE__, $db_logging);
        if ($result4->EOF)
        {
            echo "$l_none";
        }
        else
        {
            $num_detected = 0;
            while (!$result4->EOF)
            {
                $row = $result4->fields;
                // Display other ships in sector - unless they are successfully cloaked
                $success = SCAN_SUCCESS($playerinfo['sensors'], $row['cloak']);
                if ($success < 5)
                {
                    $success = 5;
                }

                if ($success > 95)
                {
                    $success = 95;
                }

                $roll = mt_rand (1, 100);
                if ($roll < $success)
                {
                    $num_detected++;
                    echo $row['ship_name'] . "(" . $row['character_name'] . ")<br>";
                }
                $result4->MoveNext();
            }

            if (!$num_detected)
            {
                echo "$l_none";
            }
        }
    }
    else
    {
        echo "$l_lrs_zero";
    }

    echo "</td></tr>";
    echo "<tr><td class=\"long-rang-scan-titles-cell\"><span class=\"long-rang-scan-titles\">Port</span></td>";
    echo "<td>";
    if ($sectorinfo['port_type'] == "none")
    {
        echo "$l_none";
    }
    else
    {
        if ($sectorinfo['port_type'] != "none")
        {
            $port_type = $sectorinfo['port_type'];
            $icon_alt_text = ucfirst (t_port($port_type));
            $icon_port_type_name = $port_type . ".png";
            $image_string = "<img align=absmiddle height=12 width=12 alt=\"$icon_alt_text\" src=\"images/$icon_port_type_name\">";
        }
        echo "$image_string " . t_port($sectorinfo['port_type']);
    }
    echo "</td></tr>";
    echo "<tr><td class=\"long-rang-scan-titles-cell\"><span class=\"long-rang-scan-titles\">Planets</span></td>";
    echo "<td>";
    $query = $db->Execute("SELECT name, owner FROM {$db->prefix}planets WHERE sector_id=?;", array($sectorinfo['sector_id']));
    db_op_result ($db, $query, __LINE__, __FILE__, $db_logging);

    if ($query->EOF)
    {
        echo "$l_none";
    }

    while (!$query->EOF)
    {
		echo "<ul>";
        $planet = $query->fields;
        if (empty($planet['name']))
        {
            echo "<li>$l_unnamed";
        }
        else
        {
            echo "<li>$planet[name]";
        }

        if ($planet['owner'] == 0)
        {
            echo " ($l_unowned)</li>";
        }
        else
        {
            $result5 = $db->Execute("SELECT character_name FROM {$db->prefix}ships WHERE ship_id=?;", array($planet['owner']));
            db_op_result ($db, $result5, __LINE__, __FILE__, $db_logging);
            $planet_owner_name = $result5->fields;
            echo " <span class=\"table_word_faded\">Owned By $planet_owner_name[character_name]</span> </li>";
        }
		echo "</ul>";
        $query->MoveNext();
    }

    $resultSDa = $db->Execute("SELECT SUM(quantity) as mines from {$db->prefix}sector_defence WHERE sector_id=? and defence_type='M';", array($sector));
    db_op_result ($db, $resultSDa, __LINE__, __FILE__, $db_logging);
    $resultSDb = $db->Execute("SELECT SUM(quantity) as fighters from {$db->prefix}sector_defence WHERE sector_id=? and defence_type='F';", array($sector));
    db_op_result ($db, $resultSDb, __LINE__, __FILE__, $db_logging);
    $defM = $resultSDa->fields;
    $defF = $resultSDb->fields;

    echo "</td></tr>";
    echo "<tr><td class=\"long-rang-scan-titles-cell\"><span class=\"long-rang-scan-titles\">Mines</span></td>";
    $has_mines =  NUMBER ($defM['mines']);
    echo "<td>" . $has_mines;
    echo "</td></tr>";
    echo "<tr><td class=\"long-rang-scan-titles-cell\"><span class=\"long-rang-scan-titles\">Fighters</span></td>";
    $has_fighters =  NUMBER ($defF['fighters']);
    echo "<td>" . $has_fighters;
    echo "</td></tr>";
    if ($playerinfo['dev_lssd'] == 'Y')
    {
        echo "<tr><td class=\"long-rang-scan-titles-cell\"><span class=\"long-rang-scan-titles\">Last ship seen in this sector</span></td>";
        echo "<td>";
        $resx = $db->Execute("SELECT * FROM {$db->prefix}movement_log WHERE ship_id <> ? AND sector_id = ? ORDER BY time DESC LIMIT 1;", array($playerinfo['ship_id'], $sector));
        db_op_result ($db, $resx, __LINE__, __FILE__, $db_logging);
        if (!$resx)
        {
            echo "None";
        }
        else
        {
            $myrow = $resx->fields;
            echo "<span class=\"table_word_red\">".get_player($db, $myrow['ship_id'])."</span>";
        }
    	echo "</td></tr>";
    }
    else
    {
    }
    echo "</tbody></table>";
	?>
    </div>
    <?
    echo "<a href=move.php?sector=$sector>$l_clickme</a> $l_lrs_moveto $sector.";
}

echo "<br><br>";
TEXT_GOTOMAIN();
?>
</div></div>
<?
include "footer.php";
?>

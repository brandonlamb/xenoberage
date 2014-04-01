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
// File: beacon.php

include "config/config.php";
updatecookie();

// New database driven language entries
load_languages($db, $lang, array('beacon', 'common', 'global_includes', 'global_funcs', 'combat', 'footer', 'news'), $langvars, $db_logging);

$title = $l_beacon_title;
include "header.php";

if (checklogin())
{
    die();
}

$result = $db->Execute ("SELECT * FROM {$db->prefix}ships WHERE ship_id=?", array($user_ship_id));
db_op_result ($db, $result, __LINE__, __FILE__, $db_logging);
$playerinfo = $result->fields;

$result2 = $db->Execute ("SELECT * FROM {$db->prefix}universe WHERE sector_id=?", array($playerinfo['sector']));
db_op_result ($db, $result2, __LINE__, __FILE__, $db_logging);
$sectorinfo = $result2->fields;

$allowed_rsw = "N";
?>
<div class="tablecell content both-border">
	<div class="pad">
<?
bigtitle();

if (!isset($_POST['beacon_text']))
{
    $beacon_text = null;
}
else
{
    $beacon_text = $_POST['beacon_text'];
}

if ($playerinfo['dev_beacon'] > 0)
{
    $res = $db->Execute("SELECT allow_beacon FROM {$db->prefix}zones WHERE zone_id=?", array($sectorinfo['zone_id']));
    db_op_result ($db, $res, __LINE__, __FILE__, $db_logging);
    $zoneinfo = $res->fields;
    if ($zoneinfo['allow_beacon'] == 'N')
    {
        echo $l_beacon_notpermitted . "<br><br>";
    }
    elseif ($zoneinfo['allow_beacon'] == 'L')
    {
        $result3 = $db->Execute("SELECT * FROM {$db->prefix}zones WHERE zone_id=?", array($sectorinfo['zone_id']));
        db_op_result ($db, $result3, __LINE__, __FILE__, $db_logging);
        $zoneowner_info = $result3->fields;
        $result5 = $db->Execute("SELECT team FROM {$db->prefix}ships WHERE ship_id=?", array($zoneowner_info['owner']));
        db_op_result ($db, $result5, __LINE__, __FILE__, $db_logging);
        $zoneteam = $result5->fields;

        if ($zoneowner_info['owner'] != $playerinfo['ship_id'])
        {
            if (($zoneteam['team'] != $playerinfo['team']) || ($playerinfo['team'] == 0))
            {
                echo $l_beacon_notpermitted . "<br><br>";
            }
            else
            {
                $allowed_rsw = "Y";
            }
        }
        else
        {
            $allowed_rsw = "Y";
        }
    }
    else
    {
        $allowed_rsw = "Y";
    }

    if ($allowed_rsw == "Y")
    {
        if ($beacon_text == "")
        {
            if ($sectorinfo['beacon'] != "")
            {
                echo $l_beacon_reads . ": " . $sectorinfo['beacon'] . "<br><br>";
            }
            else
            {
                echo $l_beacon_none . "<br><br>";
            }
            echo "<form action=beacon.php method=post>";
            echo "<table>";
            echo "<tr><td>" . $l_beacon_enter . ":</td><td><input type=text name=beacon_text size=40 maxlength=80></td></tr>";
            echo "</table>";
            echo "<input type=submit value=" . $l_submit . "><input type=reset value=" . $l_reset . ">";
            echo "</form>";
        }
        else
        {
            $beacon_text = trim (strip_tags ($beacon_text));
            echo $l_beacon_nowreads . ": " . $beacon_text . ".<br><br>";
            $update = $db->Execute("UPDATE {$db->prefix}universe SET beacon=? WHERE sector_id=?", array($beacon_text, $sectorinfo['sector_id']));
            db_op_result ($db, $update, __LINE__, __FILE__, $db_logging);
            $update = $db->Execute("UPDATE {$db->prefix}ships SET dev_beacon=dev_beacon-1 WHERE ship_id=?", array($playerinfo['ship_id']));
            db_op_result ($db, $update, __LINE__, __FILE__, $db_logging);
        }
    }
}
else
{
    echo $l_beacon_donthave . "<br><br>";
}

TEXT_GOTOMAIN();
?>
</div></div>
<?
include "footer.php";
?>

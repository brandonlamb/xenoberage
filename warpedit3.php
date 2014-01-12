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
// File: warpedit3.php

include "config/config.php";
updatecookie();

// New database driven language entries
load_languages($db, $lang, array('warpedit', 'common', 'global_includes', 'global_funcs', 'footer', 'news'), $langvars, $db_logging);

$title = $l_warp_title;
include "header.php";

if (checklogin())
{
    die();
}
?>
<div class="tablecell content both-border">
	<div class="pad">
<?
$bothway = null;
if (array_key_exists('bothway', $_POST)== true)
{
    $bothway = $_POST['bothway'];
}

$target_sector = null;
if (array_key_exists('target_sector', $_POST)== true)
{
    $target_sector = $_POST['target_sector'];
}

bigtitle();

$result = $db->Execute("SELECT * FROM {$db->prefix}ships WHERE email=?;", array($username));
db_op_result ($db, $result, __LINE__, __FILE__, $db_logging);
$playerinfo = $result->fields;

if ($playerinfo['turns'] < 1)
{
    echo $l_warp_turn . "<br><br>";
    TEXT_GOTOMAIN();
    include "footer.php";
    die();
}

if ($playerinfo['dev_warpedit'] < 1)
{
    echo $l_warp_none . "<br><br>";
    TEXT_GOTOMAIN();
    include "footer.php";
    die();
}

if (is_null($target_sector))
{
    // This is the best that I can do without adding a new language variable.
    echo $l_warp_nosector ."<br><br>";
    TEXT_GOTOMAIN();
    die();
}

$res = $db->Execute("SELECT allow_warpedit,{$db->prefix}universe.zone_id FROM {$db->prefix}zones,{$db->prefix}universe WHERE sector_id=? AND {$db->prefix}universe.zone_id={$db->prefix}zones.zone_id;", array($playerinfo['sector']));
db_op_result ($db, $res, __LINE__, __FILE__, $db_logging);
$zoneinfo = $res->fields;
if ($zoneinfo['allow_warpedit'] == 'N')
{
    echo $l_warp_forbid . "<br><br>";
    TEXT_GOTOMAIN();
    include "footer.php";
    die();
}

$target_sector = round($target_sector);
$result = $db->Execute("SELECT * FROM {$db->prefix}ships WHERE email=?;", array($username));
db_op_result ($db, $result, __LINE__, __FILE__, $db_logging);
$playerinfo = $result->fields;

$res = $db->Execute("SELECT allow_warpedit,{$db->prefix}universe.zone_id FROM {$db->prefix}zones,{$db->prefix}universe WHERE sector_id=? AND {$db->prefix}universe.zone_id={$db->prefix}zones.zone_id;", array($target_sector));
db_op_result ($db, $res, __LINE__, __FILE__, $db_logging);
$zoneinfo = $res->fields;
if ($zoneinfo['allow_warpedit'] == 'N' && $bothway)
{
    $l_warp_forbidtwo = str_replace("[target_sector]", $target_sector, $l_warp_forbidtwo);
    echo $l_warp_forbidtwo . "<br><br>";
    TEXT_GOTOMAIN();
    include "footer.php";
    die();
}

$result2 = $db->Execute("SELECT * FROM {$db->prefix}universe WHERE sector_id=?;", array($target_sector));
db_op_result ($db, $result2, __LINE__, __FILE__, $db_logging);
$row = $result2->fields;
if (!$row)
{
    echo $l_warp_nosector . "<br><br>";
    TEXT_GOTOMAIN();
    die();
}

$result3 = $db->Execute("SELECT * FROM {$db->prefix}links WHERE link_start=?;", array($playerinfo['sector']));
db_op_result ($db, $result3, __LINE__, __FILE__, $db_logging);
if ($result3 instanceof ADORecordSet)
{
    $flag = 0;
    while (!$result3->EOF)
    {
        $row = $result3->fields;
        if ($target_sector == $row['link_dest'])
        {
            $flag = 1;
        }
        $result3->MoveNext();
    }
    if ($flag != 1)
    {
        $l_warp_unlinked = str_replace("[target_sector]", $target_sector, $l_warp_unlinked);
        echo $l_warp_unlinked . "<br><br>";
    }
    else
    {
        $delete1 = $db->Execute("DELETE FROM {$db->prefix}links WHERE link_start=? AND link_dest=?;", array($playerinfo['sector'], $target_sector));
        db_op_result ($db, $delete1, __LINE__, __FILE__, $db_logging);

        $update1 = $db->Execute("UPDATE {$db->prefix}ships SET dev_warpedit=dev_warpedit - 1, turns=turns-1, turns_used=turns_used+1 WHERE ship_id=?;", array($playerinfo['ship_id']));
        db_op_result ($db, $update1, __LINE__, __FILE__, $db_logging);
        if (is_null($bothway))
        {
            echo "$l_warp_removed $target_sector.<br><br>";
        }
        else
        {
            $delete2 = $db->Execute("DELETE FROM {$db->prefix}links WHERE link_start=? AND link_dest=?;", array($target_sector, $playerinfo['sector']));
            db_op_result ($db, $delete2, __LINE__, __FILE__, $db_logging);
            echo "$l_warp_removedtwo $target_sector.<br><br>";
        }
    }
}

TEXT_GOTOMAIN();
?>
</div></div>
<?
include "footer.php";
?>

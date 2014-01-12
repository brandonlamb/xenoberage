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
// File: includes/db_kill_player.php

if (preg_match("/db_kill_player.php/i", $_SERVER['PHP_SELF'])) {
      echo "You can not access this file directly!";
      die();
}

function db_kill_player ($ship_id, $remove_planets = false)
{
    global $default_prod_ore;
    global $default_prod_organics;
    global $default_prod_goods;
    global $default_prod_energy;
    global $default_prod_fighters;
    global $default_prod_torp;
    global $db, $l;

    $resa = $db->Execute("UPDATE {$db->prefix}ships SET ship_destroyed='Y', on_planet='N', sector=0, cleared_defences=' ' WHERE ship_id=?", array($ship_id));
    db_op_result ($db, $resa, __LINE__, __FILE__);
    $resb = $db->Execute("DELETE FROM {$db->prefix}bounty WHERE placed_by = ?", array($ship_id));
    db_op_result ($db, $resb, __LINE__, __FILE__);

    $res = $db->Execute("SELECT DISTINCT sector_id FROM {$db->prefix}planets WHERE owner=? AND base='Y'", array($ship_id));
    db_op_result ($db, $res, __LINE__, __FILE__);
    $i = 0;

    while (!$res->EOF && $res)
    {
        $sectors[$i] = $res->fields['sector_id'];
        $i++;
        $res->MoveNext();
    }

    if ($remove_planets == true && $ship_id > 0)
    {
        $resc = $db->Execute("DELETE FROM {$db->prefix}planets WHERE owner = ?", array($ship_id));
        db_op_result ($db, $resc, __LINE__, __FILE__);
    }
    else
    {
        $resd = $db->Execute("UPDATE {$db->prefix}planets SET owner=0, corp=0, fighters=0, base='N' WHERE owner=?", array($ship_id));
        db_op_result ($db, $resd, __LINE__, __FILE__);
    }

    if (!empty($sectors))
    {
        foreach ($sectors as $sector)
        {
            calc_ownership ($sector);
        }
    }

    $rese = $db->Execute("DELETE FROM {$db->prefix}sector_defence WHERE ship_id=?", array($ship_id));
    db_op_result ($db, $rese, __LINE__, __FILE__);

    $res = $db->Execute("SELECT zone_id FROM {$db->prefix}zones WHERE corp_zone='N' AND owner=?", array($ship_id));
    db_op_result ($db, $res, __LINE__, __FILE__);
    $zone = $res->fields;

    $resf = $db->Execute("UPDATE {$db->prefix}universe SET zone_id=1 WHERE zone_id=?", array($zone['zone_id']));
    db_op_result ($db, $resf, __LINE__, __FILE__);

    $query = $db->Execute("SELECT character_name FROM {$db->prefix}ships WHERE ship_id=?", array($ship_id));
    db_op_result ($db, $query, __LINE__, __FILE__);
    $name = $query->fields;

    $headline = $name['character_name'] . $l->get('l_killheadline');

    $newstext = str_replace("[name]", $name['character_name'], $l->get('l_news_killed'));

    $news = $db->Execute("INSERT INTO {$db->prefix}news (headline, newstext, user_id, date, news_type) VALUES (?,?,?,NOW(), 'killed')", array($headline, $newstext, $ship_id));
    db_op_result ($db, $news, __LINE__, __FILE__);
}
?>

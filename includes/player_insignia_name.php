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
// File: includes/player_insignia_name.php

if (preg_match("/player_insignia_name.php/i", $_SERVER['PHP_SELF'])) {
      echo "You can not access this file directly!";
      die();
}

function player_insignia_name ($db, $a_username)
{
    global $l;
    unset ($player_insignia);

    // Lookup players score.
    $res = $db->Execute("SELECT score FROM {$db->prefix}ships WHERE ship_id=?", array($a_username));
    db_op_result ($db, $res, __LINE__, __FILE__);
    $playerinfo = $res->fields;

    for ($i = 0; $i < 20; $i++)
    {
        $value = pow (2, $i*2 );
        if (!$value)
        {
            // Pow returned false so we need to return an error.
            $player_insignia = "<span style='color:#f00;'>ERR</span> [<span style='color:#09f; font-size:12px; cursor:help;' title='Error looking up insignia, please report this error.'>?</span>]";
            break;
        }

        $value *= (500 * 2);
        if ($playerinfo['score'] <= $value)
        {
            // Ok we have found our Insignia, now set and break out of the for loop.
            $temp_insignia = "l_insignia_" . $i;
            $player_insignia = $l->get($temp_insignia);
            break;
        }
    }

    if (!isset($player_insignia))
    {
        // Hmm, player has out ranked out highest rank, so just return that.
        $player_insignia = $l->get('l_insignia_19');
    }

    return $player_insignia;
}
?>

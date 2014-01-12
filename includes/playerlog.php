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
// File: includes/playerlog.php

if (preg_match("/playerlog.php/i", $_SERVER['PHP_SELF'])) {
      echo "You can not access this file directly!";
      die();
}

function playerlog ($db, $sid, $log_type, $data = "")
{
    $data = addslashes ($data);

    // Write log_entry to the player's log - identified by player's ship_id - sid.
    if ($sid != "" && !empty($log_type))
    {
        $resa = $db->Execute("INSERT INTO {$db->prefix}logs VALUES(NULL, ?, ?, NOW(), ?)", array($sid, $log_type, $data));
        db_op_result ($db, $resa, __LINE__, __FILE__);
    }
}
?>

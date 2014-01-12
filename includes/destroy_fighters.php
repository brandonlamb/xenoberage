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
// File: includes/destroy_fighters.php

if (preg_match("/destroy_fighters.php/i", $_SERVER['PHP_SELF'])) {
      echo "You can not access this file directly!";
      die();
}

function destroy_fighters ($db, $sector, $num_fighters)
{
    $result3 = $db->Execute ("SELECT * FROM {$db->prefix}sector_defence WHERE sector_id=? AND defence_type ='F' ORDER BY quantity ASC", array($sector));
    db_op_result ($db, $result3, __LINE__, __FILE__);

    // Put the defence information into the array "defenceinfo"
    if ($result3 instanceof ADORecordSet)
    {
        while (!$result3->EOF && $num_fighters > 0)
        {
            $row = $result3->fields;
            if ($row['quantity'] > $num_fighters)
            {
                $update = $db->Execute("UPDATE {$db->prefix}sector_defence SET quantity=quantity - ? WHERE defence_id = ?", array($num_fighters, $row['defence_id']));
                db_op_result ($db, $update, __LINE__, __FILE__);
                $num_fighters = 0;
            }
            else
            {
                $update = $db->Execute("DELETE FROM {$db->prefix}sector_defence WHERE defence_id = ?", array($row['defence_id']));
                db_op_result ($db, $update, __LINE__, __FILE__);
                $num_fighters -= $row['quantity'];
            }
            $result3->MoveNext();
        }
    }
}
?>

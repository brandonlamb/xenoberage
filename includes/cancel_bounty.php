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
// File: includes/cancel_bounty.php

if (preg_match("/cancel_bounty.php/i", $_SERVER['PHP_SELF'])) {
      echo "You can not access this file directly!";
      die();
}

function cancel_bounty ($db, $bounty_on)
{
    $res = $db->Execute("SELECT * FROM {$db->prefix}bounty,{$db->prefix}ships WHERE bounty_on = ? AND bounty_on = ship_id", array($bounty_on));
    db_op_result ($db, $res, __LINE__, __FILE__);
    if ($res)
    {
        while (!$res->EOF)
        {
            $bountydetails = $res->fields;
            if ($bountydetails['placed_by'] != 0)
            {
                $update = $db->Execute("UPDATE {$db->prefix}ships SET credits = credits + ? WHERE ship_id = ?", array($bountydetails['amount'], $bountydetails['placed_by']));
                db_op_result ($db, $update, __LINE__, __FILE__);
                playerlog ($db, $bountydetails['placed_by'], LOG_BOUNTY_CANCELLED, "$bountydetails[amount]|$bountydetails[character_name]");
             }

             $delete = $db->Execute("DELETE FROM {$db->prefix}bounty WHERE bounty_id = ?", array($bountydetails['bounty_id']));
             db_op_result ($db, $delete, __LINE__, __FILE__);
             $res->MoveNext();
         }
     }
}
?>

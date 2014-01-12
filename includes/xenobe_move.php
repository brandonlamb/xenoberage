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
// File: includes/xenobemove.php

function xenobemove()
{
    global $playerinfo, $sector_max, $targetlink, $xenobeisdead, $db;

    // Obtain a target link
    if ($targetlink == $playerinfo['sector'])
    {
        $targetlink = 0;
    }

    $linkres = $db->Execute ("SELECT * FROM {$db->prefix}links WHERE link_start=?", array($playerinfo['sector']));
    db_op_result ($db, $linkres, __LINE__, __FILE__);
    if ($linkres > 0)
    {
        while (!$linkres->EOF)
        {
            $row = $linkres->fields;

            // Obtain sector information
            $sectres = $db->Execute ("SELECT sector_id,zone_id FROM {$db->prefix}universe WHERE sector_id=?", array($row['link_dest']));
            db_op_result ($db, $sectres, __LINE__, __FILE__);
            $sectrow = $sectres->fields;

            $zoneres = $db->Execute("SELECT zone_id,allow_attack FROM {$db->prefix}zones WHERE zone_id=?", array($sectrow['zone_id']));
            db_op_result ($db, $zoneres, __LINE__, __FILE__);
            $zonerow = $zoneres->fields;
            if ($zonerow['allow_attack'] == "Y") // Dest link must allow attacking
            {
                $setlink = mt_rand (0,2);                        // 33% Chance of replacing destination link with this one
                if ($setlink == 0 || !$targetlink > 0)           // Unless there is no dest link, choose this one
                {
                    $targetlink = $row['link_dest'];
                }
            }
            $linkres->MoveNext();
        }
    }

    if (!$targetlink > 0) // If there is no acceptable link, use a worm hole.
    {
        $wormto = mt_rand (1, ($sector_max - 15));  // Generate a random sector number
        $limitloop = 1;                             // Limit the number of loops
        while (!$targetlink > 0 && $limitloop < 15)
        {
            // Obtain sector information
            $sectres = $db->Execute ("SELECT sector_id,zone_id FROM {$db->prefix}universe WHERE sector_id=?", array($wormto));
            db_op_result ($db, $sectres, __LINE__, __FILE__);
            $sectrow = $sectres->fields;

            $zoneres = $db->Execute ("SELECT zone_id,allow_attack FROM {$db->prefix}zones WHERE zone_id=?", array($sectrow['zone_id']));
            db_op_result ($db, $zoneres, __LINE__, __FILE__);
            $zonerow = $zoneres->fields;
            if ($zonerow['allow_attack'] == "Y")
            {
                $targetlink = $wormto;
                playerlog ($db, $playerinfo['ship_id'], LOG_RAW, "Used a wormhole to warp to a zone where attacks are allowed.");
            }
            $wormto++;
            $wormto++;
            $limitloop++;
        }
    }

    if ($targetlink > 0) // Check for sector defenses
    {
        $resultf = $db->Execute ("SELECT * FROM {$db->prefix}sector_defence WHERE sector_id=? and defence_type ='F' ORDER BY quantity DESC", array($targetlink));
        db_op_result ($db, $resultf, __LINE__, __FILE__);
        $i = 0;
        $total_sector_fighters = 0;
        if ($resultf > 0)
        {
            while (!$resultf->EOF)
            {
                $defences[$i] = $resultf->fields;
                $total_sector_fighters += $defences[$i]['quantity'];
                $i++;
                $resultf->MoveNext();
            }
        }

        $resultm = $db->Execute ("SELECT * FROM {$db->prefix}sector_defence WHERE sector_id=? and defence_type ='M'", array($targetlink));
        db_op_result ($db, $resultm, __LINE__, __FILE__);
        $i = 0;
        $total_sector_mines = 0;
        if ($resultm > 0)
        {
            while (!$resultm->EOF)
            {
                $defences[$i] = $resultm->fields;
                $total_sector_mines += $defences[$i]['quantity'];
                $i++;
                $resultm->MoveNext();
            }
        }

        if ($total_sector_fighters > 0 || $total_sector_mines > 0 || ($total_sector_fighters > 0 && $total_sector_mines > 0)) // If destination link has defences
        {
            if ($playerinfo['aggression'] == 2 || $playerinfo['aggression'] == 1)
            {
                xenobetosecdef(); // Attack sector defences
                return;
            }
            else
            {
                playerlog ($db, $playerinfo['ship_id'], LOG_RAW, "Move failed, the sector is defended by $total_sector_fighters fighters and $total_sector_mines mines.");
                return;
            }
        }
    }

    if ($targetlink > 0) // Move to target link
    {
        $stamp = date("Y-m-d H-i-s");
        $move_result = $db->Execute ("UPDATE {$db->prefix}ships SET last_login=?, turns_used=turns_used+1, sector=? WHERE ship_id=?", array($stamp, $targetlink, $playerinfo['ship_id']));
        db_op_result ($db, $move_result, __LINE__, __FILE__);
        if (!$move_result)
        {
            $error = $db->ErrorMsg();
            playerlog ($db, $playerinfo['ship_id'], LOG_RAW, "Move failed with error: $error ");
        }
    }
    else
    {
        playerlog ($db, $playerinfo['ship_id'], LOG_RAW, "Move failed due to lack of target link."); // We have no target link for some reason
        $targetlink = $playerinfo['sector'];         // Reset target link so it is not zero
    }
}
?>

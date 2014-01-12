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
// File: includes/xenoberegen.php

function xenoberegen()
{
    global $playerinfo, $xen_unemployment, $xenobeisdead, $db;

    // Xenobe Unempoyment Check
    $playerinfo['credits'] = $playerinfo['credits'] + $xen_unemployment;
    $maxenergy = NUM_ENERGY ($playerinfo['power']); // Regenerate energy
    if ($playerinfo['ship_energy'] <= ($maxenergy - 50))  // Stop regen when within 50 of max
    {
        $playerinfo['ship_energy'] = $playerinfo['ship_energy'] + round (($maxenergy - $playerinfo['ship_energy']) / 2); // Regen half of remaining energy
        $gene = "regenerated Energy to $playerinfo[ship_energy] units,";
    }

    $maxarmor = NUM_ARMOR ($playerinfo['armor']); // Regenerate armor
    if ($playerinfo['armor_pts'] <= ($maxarmor - 50))  // Stop regen when within 50 of max
    {
        $playerinfo['armor_pts'] = $playerinfo['armor_pts'] + round (($maxarmor - $playerinfo['armor_pts']) / 2); // Regen half of remaining armor
        $gena = "regenerated Armor to $playerinfo[armor_pts] points,";
    }

    // Buy fighters & torpedos at 6 credits per fighter
    $available_fighters = NUM_FIGHTERS ($playerinfo['computer']) - $playerinfo['ship_fighters'];
    if (($playerinfo['credits'] > 5) && ($available_fighters > 0))
    {
        if (round ($playerinfo['credits'] / 6) > $available_fighters)
        {
            $purchase = ($available_fighters * 6);
            $playerinfo['credits'] = $playerinfo['credits'] - $purchase;
            $playerinfo['ship_fighters'] = $playerinfo['ship_fighters'] + $available_fighters;
            $genf = "purchased $available_fighters fighters for $purchase credits,";
        }

        if (round ($playerinfo['credits'] / 6) <= $available_fighters)
        {
            $purchase = (round ($playerinfo['credits'] / 6));
            $playerinfo['ship_fighters'] = $playerinfo['ship_fighters'] + $purchase;
            $genf = "purchased $purchase fighters for $playerinfo[credits] credits,";
            $playerinfo['credits'] = 0;
        }
    }

    // Xenobe pay 3 credits per torpedo
    $available_torpedoes = NUM_TORPEDOES ($playerinfo['torp_launchers']) - $playerinfo['torps'];
    if (($playerinfo['credits'] > 2) && ($available_torpedoes > 0))
    {
        if (round ($playerinfo['credits'] / 3) > $available_torpedoes)
        {
            $purchase = ($available_torpedoes * 3);
            $playerinfo['credits'] = $playerinfo['credits'] - $purchase;
            $playerinfo['torps'] = $playerinfo['torps'] + $available_torpedoes;
            $gent = "purchased $available_torpedoes torpedoes for $purchase credits,";
        }

        if (round ($playerinfo['credits'] / 3) <= $available_torpedoes)
        {
            $purchase = (round ($playerinfo['credits'] / 3));
            $playerinfo['torps'] = $playerinfo['torps'] + $purchase;
            $gent = "purchased $purchase torpedoes for $playerinfo[credits] credits,";
            $playerinfo['credits'] = 0;
        }
    }

    // Update Xenobe record
    $resg = $db->Execute ("UPDATE {$db->prefix}ships SET ship_energy=?, armor_pts=?, ship_fighters=?, torps=?, credits=? WHERE ship_id=?", array($playerinfo['ship_energy'], $playerinfo['armor_pts'], $playerinfo['ship_fighters'], $playerinfo['torps'], $playerinfo['credits'], $playerinfo['ship_id']));
    db_op_result ($db, $resg, __LINE__, __FILE__);
    if (!$gene=='' || !$gena=='' || !$genf=='' || !$gent=='')
    {
        playerlog ($db, $playerinfo[ship_id], LOG_RAW, "Xenobe $gene $gena $genf $gent and has been updated.");
    }
}
?>

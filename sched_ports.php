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
// File: sched_ports.php

    # Stop external linking.
    if ( preg_match("/sched_ports.php/i", $_SERVER['PHP_SELF']) )
    {
        echo "You can not access this file directly!";
        die();
    }

    # Update Ore in Ports
    echo "<strong>PORTS</strong><br><br>";
    echo "Adding ore to all commodities ports...";
    $resa = $db->Execute("UPDATE {$db->prefix}universe SET port_ore=port_ore+($ore_rate*$multiplier*$port_regenrate) WHERE port_type='ore' AND port_ore<$ore_limit");
    QUERYOK($resa);
    echo "Adding ore to all ore ports...";
    $resb = $db->Execute("UPDATE {$db->prefix}universe SET port_ore=port_ore+($ore_rate*$multiplier*$port_regenrate) WHERE port_type!='special' AND port_type!='none' AND port_ore<$ore_limit");
    QUERYOK($resb);
    echo "Ensuring minimum ore levels are 0...";
    $resc = $db->Execute("UPDATE {$db->prefix}universe SET port_ore=0 WHERE port_ore<0");
    QUERYOK($resc);
    echo "<br>";

    # Update Organics in Ports
    echo "Adding organics to all commodities ports...";
    $resd = $db->Execute("UPDATE {$db->prefix}universe SET port_organics=port_organics+($organics_rate*$multiplier*$port_regenrate) WHERE port_type='organics' AND port_organics<$organics_limit");
    QUERYOK($resd);
    echo "Adding organics to all organics ports...";
    $rese = $db->Execute("UPDATE {$db->prefix}universe SET port_organics=port_organics+($organics_rate*$multiplier*$port_regenrate) WHERE port_type!='special' AND port_type!='none' AND port_organics<$organics_limit");
    QUERYOK($rese);
    echo "Ensuring minimum organics levels are 0...";
    $resf = $db->Execute("UPDATE {$db->prefix}universe SET port_organics=0 WHERE port_organics<0");
    QUERYOK($resf);
    echo "<br>";

    # Update Goods in Ports
    echo "Adding goods to all commodities ports...";
    $resg = $db->Execute("UPDATE {$db->prefix}universe SET port_goods=port_goods+($goods_rate*$multiplier*$port_regenrate) WHERE port_type='goods' AND port_goods<$goods_limit");
    QUERYOK($resg);
    echo "Adding goods to all goods ports...";
    $resh = $db->Execute("UPDATE {$db->prefix}universe SET port_goods=port_goods+($goods_rate*$multiplier*$port_regenrate) WHERE port_type!='special' AND port_type!='none' AND port_goods<$goods_limit");
    QUERYOK($resh);
    echo "Ensuring minimum goods levels are 0...";
    $resi = $db->Execute("UPDATE {$db->prefix}universe SET port_goods=0 WHERE port_goods<0");
    QUERYOK($resi);
    echo "<br>";

    # Update Energy in Ports
    echo "Adding energy to all commodities ports...";
    $resj = $db->Execute("UPDATE {$db->prefix}universe SET port_energy=port_energy+($energy_rate*$multiplier*$port_regenrate) WHERE port_type='energy' AND port_energy<$energy_limit");
    QUERYOK($resj);
    echo "Adding energy to all energy ports...";
    $resk = $db->Execute("UPDATE {$db->prefix}universe SET port_energy=port_energy+($energy_rate*$multiplier*$port_regenrate) WHERE port_type!='special' AND port_type!='none' AND port_energy<$energy_limit");
    QUERYOK($resk);
    echo "Ensuring minimum energy levels are 0...";
    $resl = $db->Execute("UPDATE {$db->prefix}universe SET port_energy=0 WHERE port_energy<0");
    QUERYOK($resl);
    echo "<br>";

    # Now check to see if any ports are over max, if so rectify.
    echo "Checking Energy Port Cap...";
    $resm = $db->Execute("UPDATE {$db->prefix}universe SET port_energy=$energy_limit WHERE port_energy > $energy_limit");
    QUERYOK($resm);
    echo "Checking Goods Port Cap...";
    $resn = $db->Execute("UPDATE {$db->prefix}universe SET port_goods=$goods_limit WHERE  port_goods > $goods_limit");
    QUERYOK($resn);
    echo "Checking Organics Port Cap...";
    $reso = $db->Execute("UPDATE {$db->prefix}universe SET port_organics=$organics_limit WHERE port_organics > $organics_limit");
    QUERYOK($reso);
    echo "Checking Ore Port Cap...";
    $resp = $db->Execute("UPDATE {$db->prefix}universe SET port_ore=$ore_limit WHERE port_ore > $ore_limit");
    QUERYOK($resp);
    $multiplier = 0;
?>

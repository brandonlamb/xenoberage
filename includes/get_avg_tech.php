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
// File: includes/get_avg_tech.php

if (preg_match("/get_avg_tech.php/i", $_SERVER['PHP_SELF'])) {
      echo "You can not access this file directly!";
      die();
}

function get_avg_tech ($ship_info = null, $type = "ship")
{
        // Defined in config.php
        global $calc_ship_tech, $calc_planet_tech;

        if ($type == "ship")
        {
                $calc_tech = $calc_ship_tech;
        }
        else
        {
                $calc_tech = $calc_planet_tech;
        }

        $count = count ($calc_tech);

        $shipavg  = 0;
        for ($i = 0; $i < $count; $i++)
        {
                $shipavg += $ship_info[$calc_tech[$i]];
        }
        $shipavg /= $count;

        return $shipavg;
}
?>

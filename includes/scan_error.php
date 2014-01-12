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
// File: includes/scan_error.php

if (preg_match("/scan_error.php/i", $_SERVER['PHP_SELF'])) {
      echo "You can not access this file directly!";
      die();
}

function SCAN_ERROR ($level_scan, $level_cloak)
{
    global $scan_error_factor;

    $sc_error = (4 + $level_scan / 2 - $level_cloak / 2) * $scan_error_factor;

    if ($sc_error < 1)
    {
        $sc_error = 1;
    }

    if ($sc_error > 99)
    {
        $sc_error = 99;
    }

    return $sc_error;
}
?>

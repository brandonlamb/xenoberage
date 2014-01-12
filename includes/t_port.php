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
// File: includes/t_port.php

if (preg_match("/t_port.php/i", $_SERVER['PHP_SELF'])) {
      echo "You can not access this file directly!";
      die();
}

function t_port ($ptype)
{
    global $l;

    switch ($ptype)
    {
    case "ore":
        $ret = $l->get('l_ore');
        break;
    case "none":
        $ret = $l->get('l_none');
        break;
    case "energy":
        $ret = $l->get('l_energy');
        break;
    case "organics":
        $ret = $l->get('l_organics');
        break;
    case "goods":
        $ret = $l->get('l_goods');
        break;
    case "special":
        $ret = $l->get('l_special');
        break;
    }

    return $ret;
}
?>

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
// File: includes/newsservices.php

/*
Assumes $day is a valid formatted time
*/
function getpreviousday ($day)
{
    //convert the formatted date into a timestamp
    $day = strtotime ($day);

    //subtract one day in seconds from the timestamp
    $day = $day - 86400;

    //return the final amount formatted as YYYY/MM/DD
    return date ("Y/m/d", $day);
}

function getnextday ($day)
{
    //convert the formatted date into a timestamp
    $day = strtotime ($day);

    //add one day in seconds to the timestamp
    $day = $day + 86400;

    //return the final amount formatted as YYYY/MM/DD
    return date ("Y/m/d", $day);
}

?>

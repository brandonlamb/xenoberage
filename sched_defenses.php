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
// File: sched_defenses.php

if (preg_match("/sched_defenses.php/i", $_SERVER['PHP_SELF']))
{
    echo "You can not access this file directly!";
    die();
}

echo "<strong>Sector Defence Cleanup</strong><br><br>";
if (!isset($swordfish) || $swordfish != $adminpass)
{
    die("Script has not been called properly");
}

$res = $db->Execute("DELETE FROM {$db->prefix}sector_defence WHERE quantity <= 0");
db_op_result ($db, $res, __LINE__, __FILE__, $db_logging);
$multiplier = 0; // No need to run this again
?>

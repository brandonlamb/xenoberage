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
// File: sched_ranking.php

if (preg_match("/sched_ranking.php/i", $_SERVER['PHP_SELF']))
{
    echo "You can not access this file directly!";
    die();
}

echo "<strong>Ranking</strong><br><br>";
$res = $db->Execute("SELECT ship_id FROM {$db->prefix}ships WHERE ship_destroyed='N'");
db_op_result ($db, $res, __LINE__, __FILE__, $db_logging);
while (!$res->EOF)
{
    gen_score($res->fields['ship_id']);
    $res->MoveNext();
}
echo "<br>";
$multiplier = 0;
?>

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
// File: corp.php

include "config/config.php";
updatecookie ();

// New database driven language entries
load_languages($db, $lang, array('corp', 'common', 'global_includes', 'combat', 'footer', 'news'), $langvars, $db_logging);

$title = $l_corpm_title;
include "header.php" ;

if ( checklogin ())
{
    die ();
}

$result = $db->Execute("SELECT * FROM {$db->prefix}ships WHERE ship_id='$user_ship_id'");
db_op_result ($db, $result, __LINE__, __FILE__, $db_logging);
$playerinfo = $result->fields;

$planet_id = stripnum ($planet_id);

$result2 = $db->Execute("SELECT * FROM {$db->prefix}planets WHERE planet_id=$planet_id");
db_op_result ($db, $result2, __LINE__, __FILE__, $db_logging);
if ($result2)
{
    $planetinfo = $result2->fields;
}

if ($planetinfo['owner'] == $playerinfo['ship_id'] || ($planetinfo['corp'] == $playerinfo['team'] && $playerinfo['team'] > 0))
{
?>
<div class="tablecell content both-border">
	<div class="pad">
<?
    bigtitle();
    if ($action == "planetcorp")
    {
        echo $l_corpm_tocorp . "<br>";
        $result = $db->Execute("UPDATE {$db->prefix}planets SET corp='$playerinfo[team]', owner=$playerinfo[ship_id] WHERE planet_id=$planet_id");
        db_op_result ($db, $result, __LINE__, __FILE__, $db_logging);
        $ownership = calc_ownership ($playerinfo['sector']);
        if (!empty ($ownership))
        {
            echo "<p>$ownership<p>";
        }
    }

    if ($action == "planetpersonal")
    {
        echo $l_corpm_topersonal . "<br>";
        $result = $db->Execute("UPDATE {$db->prefix}planets SET corp='0', owner=$playerinfo[ship_id] WHERE planet_id=$planet_id");
        db_op_result ($db, $result, __LINE__, __FILE__, $db_logging);
        $ownership = calc_ownership ($playerinfo['sector']);
        
        // Kick other players off the planet
        $result = $db->Execute("UPDATE {$db->prefix}ships SET on_planet='N' WHERE on_planet='Y' AND planet_id = $planet_id AND ship_id <> $playerinfo[ship_id]");
        db_op_result ($db, $result, __LINE__, __FILE__, $db_logging);
        if (!empty($ownership))
        {
            echo "<p>" . $ownership . "<p>";
        }
    }
    TEXT_GOTOMAIN();
	?>
</div></div>
<?
}
else
{
    echo "<br>" . $l_corpm_exploit . "<br>";
    TEXT_GOTOMAIN();
	?>
</div></div>
<?
}

include "footer.php";
?>

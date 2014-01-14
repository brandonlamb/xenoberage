<?php
// Xenobe Rage Copyright (C) 2012-2013 David Dawson
// Blacknova Traders -  Copyright (C) 2001-2012 Ron Harwood and the BNT development team
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
// File: zoneinfo.php

include "config/config.php";
updatecookie ();

$body_class = 'zoneinfo';
$title = "Zone Information";
include "header.php";
?>
<div class="tablecell content both-border">
	<div class="pad">
<?
if (checklogin () )
{
    die ();
}

bigtitle ();

$res = $db->Execute("SELECT * FROM {$db->prefix}ships WHERE email='$username'");
db_op_result ($db, $res, __LINE__, __FILE__, $db_logging);
$playerinfo = $res->fields;

$res = $db->Execute("SELECT * FROM {$db->prefix}zones WHERE zone_id='$zone'");
db_op_result ($db, $res, __LINE__, __FILE__, $db_logging);
$zoneinfo = $res->fields;

if ($res->EOF)
{
    echo $l_zi_nexist;
}
else
{
    $row = $res->fields;

    if ($zoneinfo['zone_id'] < 5)
    {
        $zonevar = "l_zname_" . $zoneinfo['zone_id'];
        $zoneinfo['zone_name'] = $$zonevar;
    }

    if ($row['zone_id'] == '2')
    {
        $ownername = "<span class=\"table_word_yellow\">Federation</span>";
    }
    elseif ($row['zone_id'] == '3')
    {
        $ownername = "<span class=\"table_word_gold\">The Free-Trade Coalition</span>";
    }
    elseif ($row['zone_id'] == '1')
    {
        $ownername = "<span class=\"table_word_white\">Nobody</span>";
    }
    elseif ($row['zone_id'] == '4')
    {
        $ownername = "<span class=\"table_word_red\"><strong>Contested space</strong></span>";
    }
    else
    {
        if ($row['corp_zone'] == 'N')
        {
            $result = $db->Execute("SELECT ship_id, character_name FROM {$db->prefix}ships WHERE ship_id=".$row['owner']."");
            db_op_result ($db, $result, __LINE__, __FILE__, $db_logging);
            $ownerinfo = $result->fields;
            $ownername = $ownerinfo['character_name'];
        }
        else
        {
            $result = $db->Execute("SELECT team_name, creator, id FROM {$db->prefix}teams WHERE id=".$row['owner']."");
            db_op_result ($db, $result, __LINE__, __FILE__, $db_logging);
            $ownerinfo = $result->fields;
            $ownername = $ownerinfo['team_name'];
        }
    }

    if ($row['allow_beacon'] == 'Y')
    {
        $beacon = "<span class=\"table_word_green\">Allowed</span>";
    }
    elseif ($row['allow_beacon'] == 'N')
    {
        $beacon = "<span class=\"table_word_orange\">Not allowed</span>";
    }
    else
    {
        $beacon = "<span class=\"table_word_blue\">Limited to owner and allies</span>";
    }

    if ($row['allow_attack'] == 'Y')
    {
        $attack = "<span class=\"table_word_green\">Allowed</span>";
    }
    else
    {
        $attack = "<span class=\"table_word_orange\">Not allowed</span>";
    }

    if ($row['allow_defenses'] == 'Y')
    {
        $defense = "<span class=\"table_word_green\">Allowed</span>";
    }
    elseif ($row['allow_defenses'] == 'N')
    {
        $defense = "<span class=\"table_word_orange\">Not allowed</span>";
    }
    else
    {
        $defense = "<span class=\"table_word_blue\">Limited to owner and allies</span>";
    }

    if ($row['allow_warpedit'] == 'Y')
    {
        $warpedit = "<span class=\"table_word_green\">Allowed</span>";
    }
    elseif ($row['allow_warpedit'] == 'N')
    {
        $warpedit = "<span class=\"table_word_orange\">Not allowed</span>";
    }
    else
    {
        $warpedit = "<span class=\"table_word_blue\">Limited to owner and allies</span>";
    }

    if ($row['allow_planet'] == 'Y')
    {
        $planet = "<span class=\"table_word_green\">Allowed</span>";
    }
    elseif ($row['allow_planet'] == 'N')
    {
        $planet = "<span class=\"table_word_orange\">Not allowed</span>";
    }
    else
    {
        $planet = "<span class=\"table_word_blue\">Limited to owner and allies</span>";
    }

    if ($row['allow_trade'] == 'Y')
    {
        $trade = "<span class=\"table_word_green\">Allowed</span>";
    }
    elseif ($row['allow_trade'] == 'N')
    {
        $trade = "<span class=\"table_word_orange\">Not allowed</span>";
    }
    else
    {
        $trade = "<span class=\"table_word_blue\">Limited to owner and allies</span>";
    }

    if ($row['max_hull'] == 0)
    {
        $hull = "<span class=\"table_word_green\">Unlimited</span>";
    }
    else
    {
        $hull = $row['max_hull'];
    }

    if (($row['corp_zone'] == 'N' && $row['owner'] == $playerinfo['ship_id']) || ($row['corp_zone'] == 'Y' && $row['owner'] == $playerinfo['team'] && $playerinfo['ship_id'] == $ownerinfo['creator']))
    {
        echo "<center>You are in control of this zone. <a href=zoneedit.php?zone=$zone>Click Me</a> to change your sector laws.</center><p>";
    }
	/*
	Build table
	*/	
	?>
    <div class="general-table-container">
    <?
    echo "<table><tbody>" .
         "<tr><td colspan=\"2\">".$row['zone_name']."</td></tr>" .
         "<tr><td class=\"zone-info-headers\">Zone Owner</td><td class=\"value\">".$ownername."</td></tr>" .
         "<tr><td class=\"zone-info-headers\">Space Beacons</td><td>".$beacon."</td></tr>" .
         "<tr><td class=\"zone-info-headers\">Attacking</td><td>".$attack."</td></tr>" .
         "<tr><td class=\"zone-info-headers\">Sector Defences</td><td>".$defense."</td></tr>" .
         "<tr><td class=\"zone-info-headers\">Warp Editors</td><td>".$warpedit."</td></tr>" .
         "<tr><td class=\"zone-info-headers\">Planets</td><td>".$planet."</td></tr>" .
         "<tr><td class=\"zone-info-headers\">Port Trading</td><td>".$trade."</td></tr>" .
         "<tr><td class=\"zone-info-headers\">Maximum average tech level allowed</td><td>".$hull."</td></tr>" .
         "</tbody></table></div>";
}
echo "<br><br>";

TEXT_GOTOMAIN();
?>
</div></div>
<?
include "footer.php";
?>

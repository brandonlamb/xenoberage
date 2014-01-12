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
// File: ranking.php

include "config/config.php";
updatecookie();

if (!isset($_GET['lang']))
{
    $_GET['lang'] = null;
    $lang = $default_lang;
    $link = 'ranking.php';
    $link_back = '';
}
else
{
    $lang = $_GET['lang'];
    $link = "ranking.php?lang=" . $lang;
    $link_back = '?lang=' . $lang;
}

// New database driven language entries
load_languages($db, $lang, array('main', 'ranking', 'common', 'global_includes', 'global_funcs', 'footer', 'teams'), $langvars, $db_logging);

$l_ranks_title = str_replace("[max_ranks]", $max_ranks, $l_ranks_title);
$title = $l_ranks_title;
include "header2.php";
?>
<div class="xenobe-container"><div class="setting-container">
	<div class="header-image"></div>
    <h1>Top 100 Players</h1>
<?

//$res = $db->Execute("SELECT COUNT(*) AS num_players FROM {$db->prefix}ships WHERE ship_destroyed='N' and email NOT LIKE '%@xenobe' AND turns_used >0");
$res = $db->Execute("SELECT COUNT(*) AS num_players FROM {$db->prefix}ships WHERE ship_destroyed='N' AND turns_used >0");
db_op_result ($db, $res, __LINE__, __FILE__, $db_logging);
$row = $res->fields;
$num_players = $row['num_players'];

if (!isset($_GET['sort']))
{
    $_GET['sort'] = '';
}
$sort = $_GET['sort'];

if ($sort=="turns")
{
    $by="turns_used DESC,character_name ASC";
}
else if ($sort=="points")
{
    $by="points DESC,character_name ASC";
}
else if ($sort=="login")
{
    $by="last_login DESC,character_name ASC";
}
else if ($sort=="good")
{
    $by="rating DESC,character_name ASC";
}
else if ($sort=="bad")
{
    $by="rating ASC,character_name ASC";
}
else if ($sort=="team")
{
    $by="{$db->prefix}teams.team_name DESC, character_name ASC";
}
else if ($sort=="efficiency")
{
    $by="efficiency DESC";
}
else
{
    $by="score DESC,character_name ASC";
}

//$res = $db->Execute("SELECT {$db->prefix}ships.email,{$db->prefix}ships.score,{$db->prefix}ships.character_name,{$db->prefix}ships.turns_used,{$db->prefix}ships.last_login,UNIX_TIMESTAMP({$db->prefix}ships.last_login) as online,{$db->prefix}ships.rating, {$db->prefix}teams.team_name, if ({$db->prefix}ships.turns_used<150,0,ROUND({$db->prefix}ships.score/{$db->prefix}ships.turns_used)) AS efficiency FROM {$db->prefix}ships LEFT JOIN {$db->prefix}teams ON {$db->prefix}ships.team = {$db->prefix}teams.id  WHERE ship_destroyed='N' and email NOT LIKE '%@xenobe' AND turns_used >0 ORDER BY $by LIMIT $max_ranks");

$res = $db->Execute("SELECT {$db->prefix}ships.email,{$db->prefix}ships.score,{$db->prefix}ships.points,{$db->prefix}ships.character_name,{$db->prefix}ships.turns_used,{$db->prefix}ships.last_login,UNIX_TIMESTAMP({$db->prefix}ships.last_login) as online,{$db->prefix}ships.rating, {$db->prefix}teams.team_name, if ({$db->prefix}ships.turns_used<150,0,ROUND({$db->prefix}ships.score/{$db->prefix}ships.turns_used)) AS efficiency FROM {$db->prefix}ships LEFT JOIN {$db->prefix}teams ON {$db->prefix}ships.team = {$db->prefix}teams.id  WHERE ship_destroyed='N' AND turns_used >0 ORDER BY $by LIMIT $max_ranks");
db_op_result ($db, $res, __LINE__, __FILE__, $db_logging);



if (!$res)
{
    echo "$l_ranks_none<br>";
}
else
{
    echo "<br>$l_ranks_pnum: " . NUMBER($num_players);
    echo "<br>$l_ranks_dships<br><br>";
	/*
	Build table
	*/	
	?>
    <div class="general-table-container">
    <?
	
	    if ($link != null)
    {
        $link .= "?sort=";
    }
    echo "<table><tbody>";
    echo "<tr>";
    echo "<td>Rank</td>";
    echo "<td><a href=\"" . $link . "score\">Score</a></td>";
    echo "<td><a href=\"" . $link . "points\">Points</a></td>";
    echo "<td>Player</td>";

    echo "<td><a href=\"" . $link . "turns\">Used Turns</a></td>";
    echo "<td><a href=\"" . $link . "login\">Last Activity</a></td>";
    echo "<td><a href=\"" . $link . "team\">Team</a></td>";
	echo "<td><a href=\"" . $link . "team\">Kills</a></td>";
    echo "<td><a href=\"" . $link . "efficiency\">Efficiency Rating.</a></td></tr>";
    $i = '';
    while (!$res->EOF)
    {
        $row = $res->fields;
        $i++;
        $rating=round(sqrt( abs($row['rating']) ));
        if (abs($row['rating'])!=$row['rating'])
        {
            $rating=-1*$rating;
        }

        $curtime = TIME();
		
        $time = $row['online'];

		$difftime = ($curtime - $time) / 60;        
        $temp_turns = $row['turns_used'];
        if ($temp_turns <= 0)
        {
            $temp_turns = 1;
        }

        $online = " ";
        if ($difftime <= 5)
        {
            $user_active_now = '<td class="user-ranking-user-online">Active Now</td>';
        }
		else
		{
			$user_active_now = '<td>'.$row['last_login'].'</td>';
		}
		if ( preg_match("/(\@xenobe)$/", $row['email']) !== 0 ) // He is a Xenobe
		{
			$the_team = '<span class="player_xenobe_ranking">Xenobe Consortium</span>';
		}
		else
		{
			$the_team = $row['team_name'];
		}
        echo "<tr><td>" . NUMBER($i) . "</td><td>" . NUMBER($row['score']) . "</td><td>" . NUMBER($row['points']) . "</td><td>";
        echo "<span class='table_word_gold'>$row[character_name]</span></td><td>" . NUMBER($row['turns_used']) . "</td>".$user_active_now."<td>".$the_team."&nbsp;</td><td>0 / 0</td><td>$row[efficiency]</td></tr>";

        $res->MoveNext();
    }
    echo "</tbody></table>";
	?>
    </div>
    <?
	/*
	End table
	*/
}

echo "<br>";

if (!isset($_GET['lang']))
{
    $_GET['lang'] = null;
    $lang = $default_lang;
}
else
{
    $lang = $_GET['lang'];
}

echo "<div class=\"page-back\">";
if (empty($username))
{
	echo "Click <a href='index.php'>here</a> to go back to the home page";
}
else
{
	echo "Click <a href='main.php'>here</a> to go back to the main menu";
}
?>
</div>
    <div class="footer">
        <div class="github"><a href="https://github.com/xgermz/xenoberage"><div class="logo-github"></div></a></div>
        <div class="copyright"><span class="bolder">Xenobe Rage</span> &copy;2012 - 2013 David Dawson. All rights reserved.<br /><span class="bolder">Blacknova Traders</span> &copy;2000-2012 Ron Harwood &amp; the BNT Dev team. All rights reserved.</div>
    </div>
</div></div>
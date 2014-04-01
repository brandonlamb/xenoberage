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
// File: settings.php

include "config/config.php";

if (!isset($_GET['lang']))
{
    $_GET['lang'] = null;
    $lang = $default_lang;
    $link = '';
}
else
{
    $lang = $_GET['lang'];
    $link = "?lang=" . $lang;
}

// New database driven language entries
load_languages($db, $lang, array('settings', 'common', 'global_includes', 'global_funcs', 'footer', 'news'), $langvars, $db_logging);

include "header2.php";
?>
<div class="xenobe-container"><div class="setting-container">
	<div class="header-image"></div>
<?
function line($item, $value, $align = "left")
{
    echo "<tr><td>&nbsp;{$item}</td><td>{$value}&nbsp;</td></tr>\n";
}
echo "<h1>Game Settings</h1>";
	?>
    <div class="general-table-container">
    <?
	
echo "<table><body>";
line("Game version:",$release_version, "right");
line("Game name:",$game_name, "right");
line("Average tech level needed to hit mines",$mine_hullsize, "right");
line("Averaged Tech level When Emergency Warp Degrades",$ewd_maxhullsize, "right");

$num = NUMBER($sector_max);
line("Number of Sectors",$num, "right");
line("Maximum Links per sector",$link_max, "right");
line("Maximum average tech level for Federation Sectors",$fed_max_hull, "right");
line("Maximum score for Federation Sectors",NUMBER($fed_max_score), "right");

$bank_enabled = $allow_ibank ? "Yes" : "No";
line("Intergalactic Bank Enabled",$bank_enabled, "right");

if ($allow_ibank)
{
    $rate = $ibank_interest * 100;
    line("IGB Interest rate per update",$rate, "right");

    $rate = $ibank_loaninterest * 100;
    line("IGB Loan rate per update",$rate, "right");
}
line("Tech Level upgrade for Bases",$basedefense, "right");

$num = NUMBER($colonist_limit);
line("Colonists Limit",$num, "right");

$num = NUMBER($max_turns);
line("Maximum number of accumulated turns",$num, "right");
line("Maximum number of planets per sector",$max_planets_sector, "right");
line("Maximum number of traderoutes per player",$max_traderoutes_player, "right");
line("Colonist Production Rate",$colonist_production_rate, "right");
line("Unit of Energy used per sector fighter",$energy_per_fighter, "right");

$rate = $defence_degrade_rate * 100;
line("Sector fighter degradation percentage rate",$rate, "right");
line("Number of planets with bases need for sector ownership&nbsp;",$min_bases_to_own, "right");

$rate = NUMBER(($interest_rate - 1) * 100 , 3);
line("Planet interest rate",$rate, "right");

$rate = 1 / $colonist_production_rate;

$num = NUMBER($rate/$fighter_prate);
line("Colonists needed to produce 1 Fighter each turn",$num, "right");

$num = NUMBER($rate/$torpedo_prate);
line("Colonists needed to produce 1 Torpedo each turn",$num, "right");

$num = NUMBER($rate/$ore_prate);
line("Colonists needed to produce 1 Ore each turn",$num, "right");

$num = NUMBER($rate/$organics_prate);
line("Colonists needed to produce 1 Organics each turn",$num, "right");

$num = NUMBER($rate/$goods_prate);
line("Colonists needed to produce 1 Goods each turn",$num, "right");

$num = NUMBER($rate/$energy_prate);
line("Colonists needed to produce 1 Energy each turn",$num, "right");

$num = NUMBER($rate/$credits_prate);
line("Colonists needed to produce 1 Credits each turn",$num, "right");

$num = NUMBER($planet_max_credits);
line("Max Credits Any Planet Can Hold",$num, "right");
echo "</tbody></table></div>\n";
echo "<br>\n";
echo "<br>\n";


echo "<h1>Game Scheduler Settings</h1>";
	?>
    <div class="general-table-container">
    <?
	
echo "<table><tbody>";
line("Ticks happen every","{$sched_ticks} minutes", "right");
line("{$turns_per_tick} Turns will happen every","{$sched_turns} minutes", "right");
line("Defenses will be checked every","{$sched_turns} minutes", "right");
line("Xenobes will play every","{$sched_turns} minutes", "right");

if ($allow_ibank)
{
    line("Interests on IGB accounts will be accumulated every&nbsp;", "{$sched_igb} minutes", "right");
}

line("News will be generated every","{$sched_news} minutes", "right");
line("Planets will generate production every","{$sched_planets} minutes", "right");
$use_new_sched_planet = true; // We merged this change in, so all new versions use this
line(" -> Using new Planet Update Code",($use_new_sched_planet?"<span style='color:#0f0;'>Yes</span>":"<span style='color:#ff0;'>No</span>"), "right");
line(" -> Limit captured planets Max Credits to {$max_credits_without_base}",($sched_planet_valid_credits?"<span style='color:#0f0;'>Yes</span>":"<span style='color:#ff0;'>No</span>"), "right");
line("Ports will regenerate x {$port_regenrate} every","{$sched_ports} minutes", "right");
line("Ships will be towed from fed sectors every","{$sched_turns} minutes", "right");
line("Rankings will be generated every","{$sched_ranking} minutes", "right");
line("Sector Defences will degrade every","{$sched_degrade} minutes", "right");
line("The planetary apocalypse will occur every&nbsp;","{$sched_apocalypse} minutes", "right");

echo "</tbody></table></div>";
echo "<div class=\"page-back\">";
if (empty($username))
{
	echo "Click <a href='index.php" . $link . "'>here</a> to go back to the home page";
}
else
{
	echo "Click <a href='main.php" . $link . "'>here</a> to go back to the main menu";
}
?>
</div>
    <div class="footer">
        <div class="github"><a href="https://github.com/xgermz/xenoberage"><div class="logo-github"></div></a></div>
        <div class="copyright"><span class="bolder">Xenobe Rage</span> &copy;2012 - 2013 David Dawson. All rights reserved.<br /><span class="bolder">Blacknova Traders</span> &copy;2000-2012 Ron Harwood &amp; the BNT Dev team. All rights reserved.</div>
    </div>
</div></div>

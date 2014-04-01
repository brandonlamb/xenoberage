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
// File: main.php

include "config/config.php";

// New database driven language entries
load_languages($db, $lang, array('common', 'global_includes'), $langvars, $db_logging);

updatecookie();
if (checklogin())
{
    die();
}

$title = $l->get('l_main_title');
include "header.php";

$stylefontsize = "12Pt";
$picsperrow = 7;

$res = $db->Execute("SELECT * FROM {$db->prefix}ships WHERE ship_id='$user_ship_id'");
db_op_result ($db, $res, __LINE__, __FILE__, $db_logging);
$playerinfo = $res->fields;

if ($playerinfo['cleared_defences'] > ' ')
{
    echo $l->get('l_incompletemove') . " <br>";
    echo "<a href=$playerinfo[cleared_defences]>" . $l->get('l_clicktocontinue') . "</a>";
    die();
}

$res = $db->Execute("SELECT * FROM {$db->prefix}universe WHERE sector_id='".$playerinfo[sector]."'");
db_op_result ($db, $res, __LINE__, __FILE__, $db_logging);
$sectorinfo = $res->fields;

if ($playerinfo['on_planet'] == "Y")
{
    $res2 = $db->Execute("SELECT planet_id, owner FROM {$db->prefix}planets WHERE planet_id=$playerinfo[planet_id]");
    db_op_result ($db, $res2, __LINE__, __FILE__, $db_logging);
    if ($res2->RecordCount() != 0)
    {
        echo "<a href=planet.php?planet_id=$playerinfo[planet_id]>" . $l->get('l_clickme') . "</a> " . $l->get('l_toplanetmenu') . "    <br>";
        header("Location: planet.php?planet_id=" . $playerinfo['planet_id'] . "&id=" . $playerinfo['ship_id']);
        die();
    }
    else
    {
        $db->Execute("UPDATE {$db->prefix}ships SET on_planet='N' WHERE ship_id=".$playerinfo[ship_id]."");
        echo "<br>" . $l->get('l_nonexistant_pl') . "<br><br>";
    }
}

$res = $db->Execute("SELECT * FROM {$db->prefix}links WHERE link_start='".$playerinfo[sector]."' ORDER BY link_dest ASC");
db_op_result ($db, $res, __LINE__, __FILE__, $db_logging);

$i = 0;
if ($res != false)
{
    while (!$res->EOF)
    {
        $links[$i] = $res->fields['link_dest'];
        $i++;
        $res->MoveNext();
    }
}
$num_links = $i;

$res = $db->Execute("SELECT * FROM {$db->prefix}planets WHERE sector_id='".$playerinfo[sector]."'");
db_op_result ($db, $res, __LINE__, __FILE__, $db_logging);

$i = 0;
if ($res != false)
{
    while (!$res->EOF)
    {
        $planets[$i] = $res->fields;
        $i++;
        $res->MoveNext();
    }
}
$num_planets = $i;

$res = $db->Execute("SELECT * FROM {$db->prefix}sector_defence,{$db->prefix}ships WHERE {$db->prefix}sector_defence.sector_id='".$playerinfo[sector]."'
                                                    AND {$db->prefix}ships.ship_id = {$db->prefix}sector_defence.ship_id ");
db_op_result ($db, $res, __LINE__, __FILE__, $db_logging);

$i = 0;
if ($res != false)
{
    while (!$res->EOF)
    {
        $defences[$i] = $res->fields;
        $i++;
        $res->MoveNext();
    }
}
$num_defences = $i;

$res = $db->Execute("SELECT zone_id,zone_name FROM {$db->prefix}zones WHERE zone_id='$sectorinfo[zone_id]'");
db_op_result ($db, $res, __LINE__, __FILE__, $db_logging);
$zoneinfo = $res->fields;
function percent($num_amount, $num_total) {
$count1 = $num_amount / $num_total;
$count2 = $count1 * 100;
$count = number_format($count2, 0);
return $count;
}
$shiptypes[0]= "ship-1.gif";
$shiptypes[1]= "ship-2.gif";
$shiptypes[2]= "ship-3.gif";
$shiptypes[3]= "ship-4.gif";
$shiptypes[4]= "ship-5.gif";
$shiptypes[5]= "ship-6.gif";
$shiptypes[6]= "ship-7.gif";
$shiptypes[7]= "ship-8.gif";

$planettypes[0]= '<div class="planet-0"></div>';
$planettypes[1]= '<div class="planet-1"></div>';
$planettypes[2]= '<div class="planet-2"></div>';
$planettypes[3]= '<div class="planet-3"></div>';
$planettypes[4]= '<div class="planet-4"></div>';
$planettypes[5]= '<div class="planet-5"></div>';
$planettypes[6]= '<div class="planet-6"></div>';
$planettypes[7]= '<div class="planet-7"></div>';
$planettypes[8]= '<div class="planet-8"></div>';
$planettypes[9]= '<div class="planet-9"></div>';
$planettypes[10]= '<div class="planet-10"></div>';


$result = $db->Execute("SELECT * FROM {$db->prefix}messages WHERE recp_id=? AND notified=?;", array($playerinfo['ship_id'], "N") );
db_op_result ($db, $result, __LINE__, __FILE__, $db_logging);
if ($result->RecordCount() > 0)
{
    $alert_message = "{$l->get('l_youhave')} {$result->RecordCount()}{$l->get('l_messages_wait')}";
    echo "<script>\n";
    echo "  alert('{$alert_message}');\n";
    echo "</script>\n";

    $res = $db->Execute("UPDATE {$db->prefix}messages SET notified='Y' WHERE recp_id=?;", array($playerinfo['ship_id']));
    db_op_result ($db, $res, __LINE__, __FILE__, $db_logging);
}

$ply_turns        = NUMBER($playerinfo['turns']);
$ply_turnsused    = NUMBER($playerinfo['turns_used']);
$ply_score        = NUMBER($playerinfo['score']);
$ply_credits    = NUMBER($playerinfo['credits']);


/*

######################
Start of left menu
######################

*/

?>
<div class="tablecell menu">
	<div class="pad">
        <h5 class="left-menu-counts-h5">Your Character</h5>
        <div class="left-menu-counts">Handle<br /><b><?php echo $playerinfo['character_name']; ?></b></div>
        <div class="left-menu-counts">Current Ship<br /><b><?php echo $playerinfo['ship_name']; ?></b></div>
        	<div class="left-menu-counts">Location<br /><b>Sector <?php echo $playerinfo['sector']; ?></b></div>
            <div class="left-menu-counts">Turns Available<br /><b><?php echo $ply_turns; ?></b></div>
            <div class="left-menu-counts">Turns Used<br /><b><?php echo $ply_turnsused; ?></b></div>
            <div class="left-menu-counts">Score<br /><b><?php echo $ply_score; ?></b></div>
            <div class="left-menu-counts">Credits<br /><b><?php echo $ply_credits; ?></b></div>
        <div class="left-divider"></div>
        <h5 class="left-menu-counts-h5">Manage Account</h5>
			<div style='padding-left:4px; text-align:left;'><a href='device.php'>Devices</a></div>
			<div style='padding-left:4px; text-align:left;'><a href='planet_report.php'>Planets</a></div>
			
			<div style='padding-left:4px; text-align:left;'><a href='self_destruct.php'>Self Destruct</a></div>
			<div style='padding-left:4px; text-align:left;'><a href='options.php'>Options</a></div>
			<div style='padding-left:4px; text-align:left;'><a href='settings.php'>Settings</a></div>
            <div style='padding-left:4px; text-align:left;'><a href='logout.php'>Logout</a></div>
		<h5 class="left-menu-counts-h5">Communication</h5>
            <div style='padding-left:4px; text-align:left;'><a href='ranking.php'>Rankings</a></div>
            <div style='padding-left:4px; text-align:left;'><a href='news.php'>News</a></div>
            <div style='padding-left:4px; text-align:left;'><a href='teams.php'>Teams</a></div>
            <div style='padding-left:4px; text-align:left;'><a href='readmail.php'>Read Mail</a></div>
            <div style='padding-left:4px; text-align:left;'><a href='mailto2.php'>Send Mail</a></div>
            <div style='padding-left:4px; text-align:left;'><a href='log.php'>Logs</a></div>
            <div style='padding-left:4px; text-align:left;'><a href='log2.php'>New Logs!!</a></div>
			<div style='padding-left:4px; text-align:left;'><a href='defence_report.php'>Defence Report</a></div>
            <?
			if ($ksm_allowed == true)
			{
				echo "<div style='padding-left:4px; text-align:left;'><a href='galaxy.php'>Map</a></div>";
			}
			?>
		<div class="left-divider"></div>
		<div style='padding-left:4px; text-align:left;'><a href='https://github.com/xgermz/xenoberage/issues' target='_blank'>Tickets</a></div>            
		<div class="left-divider"></div>
        <h5 class="left-menu-counts-h5">Trade Routes</h5>
        <?
		// Menu
		$i = 0;
		$num_traderoutes = 0;
		
		// Port querry
		$query = $db->Execute("SELECT * FROM {$db->prefix}traderoutes WHERE source_type=? AND source_id=? AND owner=? ORDER BY dest_id ASC;", array("P", $playerinfo['sector'], $playerinfo['ship_id']) );
		db_op_result ($db, $query, __LINE__, __FILE__, $db_logging);
		while (!$query->EOF)
		{
			$traderoutes[$i]=$query->fields;
			$i++;
			$num_traderoutes++;
			$query->MoveNext();
		}
		// Sector Defense Trade route query - this is still under developement
		$query = $db->Execute("SELECT * FROM {$db->prefix}traderoutes WHERE source_type='D' AND source_id=".$playerinfo[sector]." AND owner=".$playerinfo[ship_id]." ORDER BY dest_id ASC");
		db_op_result ($db, $query, __LINE__, __FILE__, $db_logging);
		while (!$query->EOF)
		{
			$traderoutes[$i]=$query->fields;
			$i++;
			$num_traderoutes++;
			$query->MoveNext();
		}
		
		// Personal planet traderoute type query
		$query = $db->Execute("SELECT * FROM {$db->prefix}planets, {$db->prefix}traderoutes WHERE source_type='L' AND source_id={$db->prefix}planets.planet_id AND {$db->prefix}planets.sector_id=".$playerinfo[sector]." AND {$db->prefix}traderoutes.owner=".$playerinfo[ship_id]."");
		db_op_result ($db, $query, __LINE__, __FILE__, $db_logging);
		while (!$query->EOF)
		{
			$traderoutes[$i]=$query->fields;
			$i++;
			$num_traderoutes++;
			$query->MoveNext();
		}
		
		// Team planet traderoute type query
		$query = $db->Execute("SELECT * FROM {$db->prefix}planets, {$db->prefix}traderoutes WHERE source_type='C' AND source_id={$db->prefix}planets.planet_id AND {$db->prefix}planets.sector_id=".$playerinfo[sector]." AND {$db->prefix}traderoutes.owner=".$playerinfo[ship_id]."");
		db_op_result ($db, $query, __LINE__, __FILE__, $db_logging);
		while (!$query->EOF)
		{
			$traderoutes[$i]=$query->fields;
			$i++;
			$num_traderoutes++;
			$query->MoveNext();
		}
		
		if ($num_traderoutes == 0)
		{
			echo "  <div class='left-trade-route'><a class='dis'>&nbsp;{$l->get('l_none')} &nbsp;</a></div>";
		}
		else
		{
			
			$i=0;
			while ($i<$num_traderoutes)
			{
				echo "<div class='left-trade-route'>&nbsp;<a href=traderoute.php?engage={$traderoutes[$i]['traderoute_id']}>";
				if ($traderoutes[$i]['source_type'] == 'P')
				{
					echo $l->get('l_port') . "&nbsp;";
				}
				elseif ($traderoutes[$i]['source_type'] == 'D')
				{
					echo "Def's ";
				}
				else
				{
					$query = $db->Execute("SELECT name FROM {$db->prefix}planets WHERE planet_id=?;", array($traderoutes[$i]['source_id']) );
					db_op_result ($db, $query, __LINE__, __FILE__, $db_logging);
					if (!$query || $query->RecordCount() == 0)
					{
						echo $l->get('l_unknown');
					}
					else
					{
						$planet = $query->fields;
						if ($planet['name'] == "")
						{
							echo $l->get('l_unnamed') . " ";
						}
						else
						{
							echo "$planet[name] ";
						}
					}
				}
		
				if ($traderoutes[$i]['circuit'] == '1')
				{
					echo "=&gt;&nbsp;";
				}
				else
				{
					echo "&lt;=&gt;&nbsp;";
				}
		
				if ($traderoutes[$i]['dest_type'] == 'P')
				{
					echo $traderoutes[$i]['dest_id'];
				}
				elseif ($traderoutes[$i]['dest_type'] == 'D')
				{
					echo "Def's in " .  $traderoutes[$i]['dest_id'] . "";
				}
				else
				{
					$query = $db->Execute("SELECT name FROM {$db->prefix}planets WHERE planet_id=" . $traderoutes[$i]['dest_id']);
					db_op_result ($db, $query, __LINE__, __FILE__, $db_logging);
		
					if (!$query || $query->RecordCount() == 0)
					{
						echo $l->get('l_unknown');
					}
					else
					{
						$planet = $query->fields;
						if ($planet['name'] == "")
						{
							echo $l->get('l_unnamed');
						}
						else
						{
							echo $planet['name'];
						}
					}
				}
				echo "</a>&nbsp;<br>";
				$i++;
				echo "</div>\n";
			}
		}
		?>
            <div class="right-divider"></div>
        <div class="left-menu-trade-routes">[<a class=dis href="traderoute.php">Manage Trade Route</a>]</div>
    </div>
</div>

<?
/*

######################
End of left menu
######################

######################
Start Main Viewer
######################

*/
?>
<div class="tablecell content">
<?
if($sectorinfo['port_type']!="none")
{
	echo "<div class=\"port-background\">";
}

?>
<div class="sector-information sector-information-bar">            	
<?
if (empty($sectorinfo['beacon']) || strlen(trim($sectorinfo['beacon'])) <=0)
{
    $sectorinfo['beacon'] = null;
}
echo "<div class=\"sector-information-beacon\">".$sectorinfo['beacon']."</div>";

if ($zoneinfo['zone_id'] < 5)
{
    $zonevar = "l_zname_" . $zoneinfo['zone_id'];
    $zoneinfo['zone_name'] = $$zonevar;
}

echo "<div class=\"sector-information-zone\"><a class='new_link' href='zoneinfo.php?zone={$zoneinfo['zone_id']}'>{$zoneinfo['zone_name']}</a></div>";


echo "<br>\n";
?>
 </div>
<div class="pad">
<?


echo "<td style='vertical-align:top;'>\n";
if ($sectorinfo['port_type'] == "special")
{
    echo "<div class=\"station-communication-container\"><div class=\"station-communication-array-info\">Communication Array Detected.<br/> Click to open a channel to the <span class=\"table_word_white\">Shipyard</span></div><a href='port.php' title='Open Channel To Shipyard'><div class=\"station-communication-array\"></div></a></div>\n";
}
else if ($sectorinfo['port_type'] == "ore")
{
    echo "<div class=\"station-communication-container\"><div class=\"station-communication-array-info\">Communication Array Detected.<br/> Click to open a channel to the <span class=\"table_word_white\">Ore Refinery</span></div><a href='port.php' title='Open Channel To Ore Refinery'><div class=\"station-communication-array\"></div></a></div>\n";
}
else if ($sectorinfo['port_type'] == "goods")
{
	echo "<div class=\"station-communication-container\"><div class=\"station-communication-array-info\">Communication Array Detected.<br/> Click to open a channel to the <span class=\"table_word_white\">Goods Factory</span></div><a href='port.php' title='Open Channel To The Goods Factory'><div class=\"station-communication-array\"></div></a></div>\n";
}
else if ($sectorinfo['port_type'] == "energy")
{
	echo "<div class=\"station-communication-container\"><div class=\"station-communication-array-info\">Communication Array Detected.<br/> Click to open a channel to the <span class=\"table_word_white\">Power Plant</span></div><a href='port.php' title='Open Channel To The Power Plant'><div class=\"station-communication-array\"></div></a></div>\n";
}
else if ($sectorinfo['port_type'] == "organics")
{
	echo "<div class=\"station-communication-container\"><div class=\"station-communication-array-info\">Communication Array Detected.<br/> Click to open a channel to the <span class=\"table_word_white\">Hydroponics Facility</span></div><a href='port.php' title='Open Channel To The Hydroponics Facility'><div class=\"station-communication-array\"></div></a></div>\n";
}
else if ($sectorinfo['port_type'] != "none" && strlen($sectorinfo['port_type']) >0)
{
	/*
	Unknown port, wtf. Pirate port?
	*/
    echo "<div style='color:#fff; text-align:center; font-size:14px;'>\n";
    echo "{$l->get('l_tradingport')}:&nbsp;<span style='color:#0f0;'>Pirate</span>\n";
    echo "<br>\n";
    echo "<a class='new_link' style='font-size:14px;' href='port.php' title='Dock with Space Port'><img style='width:89px; height:55px;' src='templates/alienrage/images/tations/port_pirate.gif' alt='Space Station Port'></a>\n";
    echo "</div>\n";
}
else
{
    echo "<div style='color:#fff; text-align:center;'>{$l->get('l_tradingport')}&nbsp;{$l->get('l_none')}</div>\n";
}

echo "<br>\n";

// Put all the Planets into a div container and center it.
echo "<div style='margin-left:auto; margin-right:auto; text-align:center; border:transparent 1px solid;'>\n";
echo "<div style='text-align:center; font-size:12px; color:#fff; font-weight:bold;'>{$l->get('l_planet_in_sec')} {$sectorinfo['sector_id']}</div>\n";
echo "<table style='height:150px; text-align:center; margin:auto; border:0px'>\n";
echo "  <tr>\n";

if ($num_planets > 0)
{
    $totalcount=0;
    $curcount=0;
    $i=0;

    while ($i < $num_planets)
    {
        if ($planets[$i]['owner'] != 0)
        {
            $result5 = $db->Execute("SELECT * FROM {$db->prefix}ships WHERE ship_id=?;", array($planets[$i]['owner']) );
            db_op_result ($db, $result5, __LINE__, __FILE__, $db_logging);
            $planet_colonists = $result5->fields;
			$percentage_colonists = percent($planets[$i]['colonists'],$colonist_limit);
			if($percentage_colonists<10)
			{
				$planetlevel = 0;
			}
			
            else if ($percentage_colonists < 20)
            {
                $planetlevel = 1;
            }
            else if ($percentage_colonists < 30)
            {
                $planetlevel = 2;
            }
			else if ($percentage_colonists < 40)
            {
                $planetlevel = 3;
            }
			else if ($percentage_colonists < 50)
            {
                $planetlevel = 4;
            }
			else if ($percentage_colonists < 60)
            {
                $planetlevel = 5;
            }
			else if ($percentage_colonists < 70)
            {
                $planetlevel = 6;
            }
			 else if ($percentage_colonists < 80)
            {
                $planetlevel = 7;
            }
			else if ($percentage_colonists < 90)
            {
                $planetlevel = 8;
            }
			else if ($percentage_colonists < 100)
            {
                $planetlevel = 9;
            }
			else
            {
                $planetlevel = 10;
            }
        }
        else
        {
            $planetlevel=0;
        }

        echo "<td style='margin-left:auto; margin-right:auto; vertical-align:top; width:80px; height:80px; padding:1px;'>";
        echo "<a href='planet.php?planet_id={$planets[$i]['planet_id']}'>";
        echo $planettypes[$planetlevel];
		echo "</a><br><span style='font-size:10px; color:#fff;'>";

        if (empty($planets[$i]['name']))
        {
            echo $l->get('l_unnamed');
        }
        else
        {
            echo $planets[$i]['name'];
        }

        if ($planets[$i]['owner'] == 0)
        {
            echo "<br>(" . $l->get('l_unowned') . ")";
        }
        else
        {
			$result5 = $db->Execute("SELECT character_name FROM {$db->prefix}ships WHERE ship_id=?;", array($planets[$i]['owner']));
            db_op_result ($db, $result5, __LINE__, __FILE__, $db_logging);
            $planet_owner_name = $result5->fields;
            echo "<br>(".$planet_owner_name['character_name'].")";
        }
        echo "</span></td>";

        $totalcount++;
        if ($curcount == $picsperrow - 1)
        {
            echo "</tr><tr>";
            $curcount=0;
        }
        else
        {
            $curcount++;
        }
        $i++;
    }
}
else
{
    echo "<td style='margin-left:auto; margin-right:auto; vertical-align:top'>";
    echo "<br><span style='color:white; size:1.25em'>" . $l->get('l_none') . "</span><br><br>";
}

echo "</tr>\n";
echo "</table>\n";
echo "</div>\n";

// Put all the Planets into a div container and center it.
echo "<div style='text-align:center; border:transparent 1px solid;'>\n";


if ($playerinfo['sector'] != 0)
{
    $sql  = null;
    $sql .= "SELECT {$db->prefix}ships.*, {$db->prefix}teams.team_name, {$db->prefix}teams.id ";
    $sql .= "FROM {$db->prefix}ships LEFT OUTER JOIN {$db->prefix}teams ON {$db->prefix}ships.team = {$db->prefix}teams.id ";
    $sql .= "WHERE {$db->prefix}ships.ship_id<>".$playerinfo[ship_id]." AND {$db->prefix}ships.sector=".$playerinfo[sector]." AND {$db->prefix}ships.on_planet='N' ";
#    $sql .= "WHERE {$db->prefix}ships.sector=".$playerinfo[sector]." AND {$db->prefix}ships.on_planet='N' ";
    $sql .= "ORDER BY RAND();";
    $result4 = $db->Execute($sql);
    db_op_result ($db, $result4, __LINE__, __FILE__, $db_logging);

    if ($result4 != false )
    {
        $ships_detected = 0;
        $ship_detected = null;
        while (!$result4->EOF)
        {
            $row=$result4->fields;
            $success = SCAN_SUCCESS($playerinfo['sensors'], $row['cloak']);
            if ($success < 5)
            {
                $success = 5;
            }
            if ($success > 95)
            {
                $success = 95;
            }
            $roll = mt_rand(1, 100);

            if ($roll < $success)
            {
                $shipavg = get_avg_tech($row, "ship");
				
				if ($shipavg < 10)
				   $ship_image = 0;
				elseif ($shipavg < 15)
				   $ship_image = 1;
				elseif ($shipavg < 20)
					$ship_image = 2;
				elseif ($shipavg < 25)
					$ship_image = 3;
				elseif ($shipavg < 30)
					$ship_image = 4;
				elseif ($shipavg < 35)
					$ship_image = 5;
				elseif ($shipavg < 40)
					$ship_image = 6;
				elseif ($shipavg < 45)
					$ship_image = 7;
				else
				   $ship_image = 7;

                $row['shiplevel'] = $ship_image;
                $ship_detected[] = $row;
                $ships_detected ++;
            }
            $result4->MoveNext();
        }
        if ($ships_detected <= 0)
        {
           /*Show nothing*/
        }
        else
        {
            echo "<div style='padding-top:4px; padding-bottom:4px; width:500px; margin:auto; background-color:#303030;'>" . $l->get('l_main_ships_detected') . "</div>\n";
            echo "<div style='width:498px; margin:auto; overflow:auto; height:145px; scrollbar-base-color: #303030; scrollbar-arrow-color: #fff; padding:0px;'>\n";
            echo "<table style='padding:0px; border-spacing:1px;'>\n";
            echo "  <tr>\n";

            for ($iPlayer=0; $iPlayer < count($ship_detected); $iPlayer++)
            {
                echo "<td style='text-align:center; vertical-align:top; padding:1px;'>\n";
                echo "<div style='width:160px; height:120px; background: URL(images/bg_alpha.png) repeat; padding:1px;'>\n";
                echo "<a href=ship.php?ship_id={$ship_detected[$iPlayer]['ship_id']}>\n";
                echo "  <img title='Interact with Ship' src=\"templates/alienrage/images/ships/", $shiptypes[$ship_detected[$iPlayer]['shiplevel']],"\" style='width:80px; height:60px; border:0px'>\n";
                echo "</a>\n";
                echo "<div style='font-size:12px; color:#fff; white-space:nowrap;'>{$ship_detected[$iPlayer]['ship_name']}<br>\n";
                echo "(<span style='color:#ff0; white-space:nowrap;'>{$ship_detected[$iPlayer]['character_name']}</span>)<br>\n";
                if ($ship_detected[$iPlayer]['team_name'])
                {
                    echo "(<span style='color:#0f0; white-space:nowrap;'>{$ship_detected[$iPlayer]['team_name']}</span>)\n";
                }
                echo "</div>\n";

                echo "</div>\n";
                echo "</td>\n";
            }
            echo "  </tr>\n";
            echo "</table>\n";
            echo "</div>\n";
        }
    }
    else
    {
        echo "<div style='color:#fff;'>{$l->get('l_none')}</div>\n";
    }
}
else
{
        echo "<div style='color:#fff;'>{$l->get('l_sector_0')}</div>\n";
}
echo "</div>";

if ($num_defences>0)
{
            echo "<div style='padding-top:4px; padding-bottom:4px; width:500px; margin:auto; background-color:#303030; text-align:center;'>" . $l->get('l_sector_def') . "</div>\n";
            echo "<div style='width:498px; margin:auto; overflow:auto; height:125px; scrollbar-base-color: #303030; scrollbar-arrow-color: #fff; padding:0px; text-align:center;'>\n";
}
?>
<table>
<tr>
<?php
if ($num_defences > 0)
{
    $totalcount=0;
    $curcount=0;
    $i=0;
    while ($i < $num_defences)
    {
        $defence_id = $defences[$i]['defence_id'];
        echo "<td style='vertical-align:top; background: URL(images/bg_alpha.png) repeat;'><div style=' width:160px; font-size:12px; '>";
        if ($defences[$i]['defence_type'] == 'F')
        {
            echo "<a class='new_link' href='modify_defences.php?defence_id=$defence_id'><img src=\"images/fighters.png\" style='border:0px' alt='Fighters'></a>\n";
            $def_type = $l->get('l_fighters');
            $mode = $defences[$i]['fm_setting'];
            if ($mode == 'attack')
            {
                $mode = $l->get('l_md_attack');
            }
            else
            {
                $mode = $l->get('l_md_toll');
            }
            $def_type .= $mode;
        }
        elseif ($defences[$i]['defence_type'] == 'M')
        {
            echo "<div><a href='modify_defences.php?defence_id=$defence_id'><img src=\"templates/alienrage/images/space-mines.png\" style='border:0px' alt='Mines'></a></div>\n";
            $def_type = $l->get('l_mines');
        }

        $char_name = $defences[$i]['character_name'];
        $qty = $defences[$i]['quantity'];
        echo "<div style='font-size:1em; color:#fff;'>$char_name<br>( $qty $def_type )</div>\n";
		echo "</div></td>";

        $totalcount++;
        if ($curcount == $picsperrow - 1)
        {
            echo "</tr><tr>";
            $curcount=0;
        }
        else
        {
            $curcount++;
        }
        $i++;
    }
    echo "</tr></table>";
echo "</div>\n";
}
else
{
    echo "<td style='vertical-align:top; text-align:center;'>";
    echo "</td></tr></table>";
}
if($sectorinfo['port_type']!="none")
{
	echo "</div>";
}
?>

	</div>
</div>

<?php
/*

########################
End Main Viewer
#########################


########################
Start right menu
#########################


*/
?>
<div class="tablecell extra">
	<div class="pad">
    	<h5 class="right-menu-counts-h5">Armourments</h5>
        <div class="right-menu-counts"><img style='height:12px; width:12px;' alt="Fighters" src="images/fig-small.png">  Fighters <br /><b><?php echo NUMBER($playerinfo['ship_fighters']); ?></b></div>
        <div class="right-menu-counts"><img style='height:12px; width:12px;' alt="Torpedoes" src="images/torp-small.png">  Torpedoes <br /><b><?php echo NUMBER($playerinfo['torps']); ?></b></div>
        <div class="right-menu-counts"><img style='height:12px; width:12px;' alt="Armour" src="images/armor-small.png">  Armour Points <br /><b><?php echo NUMBER($playerinfo['armor_pts']); ?></b></div>
        <div class="right-menu-counts"><img style='height:12px; width:12px;' alt="Energy" src="images/energy-small.png">  Energy <br /><b><?php echo NUMBER($playerinfo['ship_energy']); ?></b></div>
        <h5 class="right-menu-counts-h5">Cargo</h5>
        <div class="right-menu-counts"><img style='height:12px; width:12px;' alt="Ore" src="images/ore.png">  Ore <br /><b><?php echo NUMBER($playerinfo['ship_ore']); ?></b></div>
        <div class="right-menu-counts"><img style='height:12px; width:12px;' alt="Organics" src="images/organics.png">  Organics <br /><b><?php echo NUMBER($playerinfo['ship_organics']); ?></b></div>
        <div class="right-menu-counts"><img style='height:12px; width:12px;' alt="Goods" src="images/goods.png">  Goods <br /><b><?php echo NUMBER($playerinfo['ship_goods']); ?></b></div>
        <div class="right-menu-counts"><img style='height:12px; width:12px;' alt="Energy" src="images/energy.png">  Energy <br /><b><?php echo NUMBER($playerinfo['ship_energy']); ?></b></div>
        <div class="right-menu-counts"><img style='height:12px; width:12px;' alt="Colonists" src="images/colonists.png">  Colonists <br /><b><?php echo NUMBER($playerinfo['ship_colonists']); ?></b></div>
        <div class="right-divider"></div>
        <h5 class="right-menu-counts-h5">Realspace</h5>
        <div class="right-menu-row">
             <div class="right-menu-rs"><a href="rsmove.php?engage=1&amp;destination=<?php echo $playerinfo['preset1']; ?>">=&gt;&nbsp;<?php echo $playerinfo['preset1']; ?></a></div>
             <div class="right-menu-rs-right">[<a href=preset.php>Set</a>]</div>
        </div>
        <div class="right-menu-row">
            <div class="right-menu-rs"><a href="rsmove.php?engage=1&amp;destination=<?php echo $playerinfo['preset2']; ?>">=&gt;&nbsp;<?php echo $playerinfo['preset2']; ?></a></div>
            <div class="right-menu-rs-right">[<a href=preset.php>Set</a>]</div>
        </div>
        <div class="right-menu-row">
            <div class="right-menu-rs"><a href="rsmove.php?engage=1&amp;destination=<?php echo $playerinfo['preset3']; ?>">=&gt;&nbsp;<?php echo $playerinfo['preset3']; ?></a></div>
            <div class="right-menu-rs-right">[<a href=preset.php>Set</a>]</div>
        </div>
        <div class="right-menu-row">
            <div class="right-menu-rs"><a href="rsmove.php?engage=1&amp;destination=<?php echo $playerinfo['preset4']; ?>">=&gt;&nbsp;<?php echo $playerinfo['preset4']; ?></a></div>
            <div class="right-menu-rs-right">[<a href=preset.php>Set</a>]</div>
        </div>
        <div class="right-menu-row">
            <div class="right-menu-rs"><a href="rsmove.php?engage=1&amp;destination=<?php echo $playerinfo['preset5']; ?>">=&gt;&nbsp;<?php echo $playerinfo['preset5']; ?></a></div>
            <div class="right-menu-rs-right">[<a href=preset.php>Set</a>]</div>
        </div>
        <div class="right-menu-row">
        <?php 
		$left_se = $playerinfo['sector']-1;
		if($left_se<0)
		{
			$left_se = $sector_max-1;
		}
		$right_se = $playerinfo['sector']+1;
		if(($right_se ==$sector_max) or ($right_se > $sector_max))
		{ 
		$right_se = 0;
		}
		?>
            <div class="right-menu-rs"><a href="rsmove.php?engage=1&amp;destination=<?php echo $left_se; ?>"><strong> <-- </strong></a></div>
            <div class="right-menu-rs-right"><a href="rsmove.php?engage=1&amp;destination=<?php echo $right_se; ?>"><strong> --> </strong></a></div>
        </div>
        <div class="right-divider"></div>

            <div class="right-menu-realspace">
            <form action=rsmove.php method=post>
            <input type="text" name=destination size=10 maxlength=10>
            <input type="submit" value="Go" id="right-menu-realspace-button">
            </form>
            </div>

        <div class="right-divider"></div>
        <div class="right-menu-full-scan">[<a class=dis href="navcomp.php">Find Route</a>]</div>
        <div class="right-divider"></div>
        <h5 class="right-menu-counts-h5">Warp Links</h5>
        <?
		if (!$num_links)
		{
			echo '<div class="right-menu-rs">No Warp Links</div>';
		}
		else
		{
			for ($i = 0; $i < $num_links; $i++)
			{
				?>
                <div class="right-menu-row">
                    <div class="right-menu-rs"><a href='move.php?sector=<? echo $links[$i]; ?>'>=&gt; <? echo $links[$i]; ?></a></div>
                    <div class="right-menu-rs-right">[<a href='lrscan.php?sector=<? echo $links[$i]; ?>'>Scan</a>]</div>
                </div>
        		 <?
			}
		}
		?>
        <div class="right-divider"></div>
        <div class="right-menu-full-scan">[<a class=dis href="lrscan.php?sector=*">Full Scan</a>]</div>
    </div>
</div>

<?php

/*

######################
End Right menu
######################

*/

include "footer.php";
?>

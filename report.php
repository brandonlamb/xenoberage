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
// File: report.php

include "config/config.php";
updatecookie();

// New database driven language entries
load_languages($db, $lang, array('main', 'report', 'device', 'common', 'global_includes', 'global_funcs', 'footer'), $langvars, $db_logging);

$title = $l_report_title;
include "header.php";
?>
<div class="tablecell content both-border">

<?
if (checklogin())
{
    die();
}

$result = $db->Execute("SELECT * FROM {$db->prefix}ships WHERE ship_id='$user_ship_id'");
db_op_result ($db, $result, __LINE__, __FILE__, $db_logging);

$playerinfo=$result->fields;

$shiptypes[0]= "ship-1.gif";
$shiptypes[1]= "ship-2.gif";
$shiptypes[2]= "ship-3.gif";
$shiptypes[3]= "ship-4.gif";
$shiptypes[4]= "ship-5.gif";
$shiptypes[5]= "ship-6.gif";
$shiptypes[6]= "ship-7.gif";
$shiptypes[7]= "ship-8.gif";


$shipavg = get_avg_tech($playerinfo, "ship");

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
   
/*Need to move inside a class*/   
function filter_ship_levels($ship_component_level){
	if ($ship_component_level < 10)
	{
	   $shiplevel = 0;
	}
	elseif ($ship_component_level < 20)
	{
	   $shiplevel = 1;
	}
	elseif ($ship_component_level < 30)
	{
	   $shiplevel = 2;
	}
	elseif ($ship_component_level < 40)
	{
	   $shiplevel = 3;
	}
	else
	{
	   $shiplevel = 4;
	}
	return $shiplevel;
}
function percent($num_amount, $num_total) {
$count1 = $num_amount / $num_total;
$count2 = $count1 * 100;
$count = number_format($count2, 0);
return $count;
}





$holds_used = $playerinfo['ship_ore'] + $playerinfo['ship_organics'] + $playerinfo['ship_goods'] + $playerinfo['ship_colonists'];
$holds_max = NUM_HOLDS($playerinfo['hull']);
$armor_pts_max = NUM_ARMOR($playerinfo['armor']);
$ship_fighters_max = NUM_FIGHTERS($playerinfo['computer']);
$torps_max = NUM_TORPEDOES($playerinfo['torp_launchers']);
$energy_max = NUM_ENERGY($playerinfo['power']);
$escape_pod = ($playerinfo['dev_escapepod'] == 'Y') ? $l_yes : $l_no;
$fuel_scoop = ($playerinfo['dev_fuelscoop'] == 'Y') ? $l_yes : $l_no;
$lssd = ($playerinfo['dev_lssd'] == 'Y') ? $l_yes : $l_no;

if($playerinfo['dev_escapepod'] == 'N')
{
	$escape_pod_warning = '<span class="ship-component-warning-span">CRITICAL COMPONENT NOT INSTALLED</span>';
}

if($playerinfo['dev_minedeflector'] == '0')
{
	$mine_deflector_warning = '<span class="ship-component-warning-span">WARNING YOU HAVE NO MINEDEFLECTORS</span>';
}
if($playerinfo['dev_emerwarp'] == '0')
{
	$emergancy_warp_warning = '<span class="ship-component-warning-span">WARNING NO EMERGENCY WARPS INSTALLED</span>';
}

?>
<div class="ship-container">
<?

echo "<a href=\"main.php\"><h1><< Back</h1></a><p align=center>";
?>
	<div class="ship-graphic"><img src="templates/alienrage/images/ships/<? echo $shiptypes[$ship_image]; ?>" border=0></div>
    <div class="ship-user-container">
        <div class="ship-stats-user  ship-divider-top">
            <div class="ship-component-name"> Pilot</div>
            <div class="ship-storage-quantity"><? echo $playerinfo[character_name]; ?></div>
        </div>
        <div class="ship-stats-user">
            <div class="ship-component-name"> Ship Name</div>
            <div class="ship-storage-quantity"><? echo $playerinfo[ship_name]; ?></div>
        </div>
        <div class="ship-stats-user">
            <div class="ship-component-name"> Credits</div>
            <div class="ship-storage-quantity"><? echo NUMBER($playerinfo['credits']); ?></div>
        </div>
    </div>
    <div class="ship-component-container">
        <div class="ship-component-component ship-divider-top">
        	<div class="ship-group-title">COMPONENTS</div>
        </div>
        <div class="ship-component-component">
            <div class="ship-component-name"> Hull</div>
            <div class="ship-component-bars">
                <div class="ship-progress">
                    <div class="ship-bar ship-<? echo filter_ship_levels($playerinfo[hull]); ?>" style="width:<? echo ($playerinfo[hull]/$max_upgrades_devices)*100; ?>%">&nbsp;</div>
                    <div class="ship-component-warning"></div >
                </div>
                <div class="ship-lvl">(<? echo $l_level." ".$playerinfo[hull]; ?>)</div>
            </div>
        </div>
        
        <div class="ship-component-component">
            <div class="ship-component-name"> Engines</div>
            <div class="ship-component-bars">
                <div class="ship-progress">
                    <div class="ship-bar ship-<? echo filter_ship_levels($playerinfo[engines]); ?>" style="width:<? echo ($playerinfo[engines]/$max_upgrades_devices)*100; ?>%">&nbsp;</div>
                    <div class="ship-component-warning"></div >
                </div>
                <div class="ship-lvl">(<? echo $l_level." ".$playerinfo[engines]; ?>)</div>
            </div>
        </div>
        
        <div class="ship-component-component">
            <div class="ship-component-name"> Power</div>
            <div class="ship-component-bars">
                <div class="ship-progress">
                    <div class="ship-bar ship-<? echo filter_ship_levels($playerinfo[power]); ?>" style="width:<? echo ($playerinfo[power]/$max_upgrades_devices)*100; ?>%">&nbsp;</div>
                    <div class="ship-component-warning"></div >
                </div>
                <div class="ship-lvl">(<? echo $l_level." ".$playerinfo[power]; ?>)</div>
            </div>
        </div>
        
        <div class="ship-component-component">
            <div class="ship-component-name"> Computer</div>
            <div class="ship-component-bars">
                <div class="ship-progress">
                    <div class="ship-bar ship-<? echo filter_ship_levels($playerinfo[computer]); ?>" style="width:<? echo ($playerinfo[computer]/$max_upgrades_devices)*100; ?>%">&nbsp;</div>
                    <div class="ship-component-warning"></div >
                </div>
                <div class="ship-lvl">(<? echo $l_level." ".$playerinfo[computer]; ?>)</div>
            </div>
        </div>
        
        <div class="ship-component-component">
            <div class="ship-component-name"> Sensors</div>
            <div class="ship-component-bars">
                <div class="ship-progress">
                    <div class="ship-bar ship-<? echo filter_ship_levels($playerinfo[sensors]); ?>" style="width:<? echo ($playerinfo[sensors]/$max_upgrades_devices)*100; ?>%">&nbsp;</div>
                    <div class="ship-component-warning"></div >
                </div>
                <div class="ship-lvl">(<? echo $l_level." ".$playerinfo[sensors]; ?>)</div>
            </div>
        </div>
        
        <div class="ship-component-component">
            <div class="ship-component-name"> Armour</div>
            <div class="ship-component-bars">
                <div class="ship-progress">
                    <div class="ship-bar ship-<? echo filter_ship_levels($playerinfo[armor]); ?>" style="width:<? echo ($playerinfo[armor]/$max_upgrades_devices)*100; ?>%">&nbsp;</div>
                    <div class="ship-component-warning"></div >
                </div>
                <div class="ship-lvl">(<? echo $l_level." ".$playerinfo[armor]; ?>)</div>
            </div>
        </div>
        
        <div class="ship-component-component">
            <div class="ship-component-name"> Shields</div>
            <div class="ship-component-bars">
                <div class="ship-progress">
                    <div class="ship-bar ship-<? echo filter_ship_levels($playerinfo[shields]); ?>" style="width:<? echo ($playerinfo[shields]/$max_upgrades_devices)*100; ?>%">&nbsp;</div>
                    <div class="ship-component-warning"></div >
                </div>
                <div class="ship-lvl">(<? echo $l_level." ".$playerinfo[shields]; ?>)</div>
            </div>
        </div>
        
        <div class="ship-component-component">
            <div class="ship-component-name"> Beams</div>
            <div class="ship-component-bars">
                <div class="ship-progress">
                    <div class="ship-bar ship-<? echo filter_ship_levels($playerinfo[beams]); ?>" style="width:<? echo ($playerinfo[beams]/$max_upgrades_devices)*100; ?>%">&nbsp;</div>
                    <div class="ship-component-warning"></div >
                </div>
                <div class="ship-lvl">(<? echo $l_level." ".$playerinfo[beams]; ?>)</div>
            </div>
        </div>
        
        <div class="ship-component-component">
            <div class="ship-component-name"> Torpedo Launchers</div>
            <div class="ship-component-bars">
                <div class="ship-progress">
                    <div class="ship-bar ship-<? echo filter_ship_levels($playerinfo[torp_launchers]); ?>" style="width:<? echo ($playerinfo[torp_launchers]/$max_upgrades_devices)*100; ?>%">&nbsp;</div>
                    <div class="ship-component-warning"></div >
                </div>
                <div class="ship-lvl">(<? echo $l_level." ".$playerinfo[torp_launchers]; ?>)</div>
            </div>
        </div>
        
        <div class="ship-component-component">
            <div class="ship-component-name"> Cloak</div>
            <div class="ship-component-bars">
                <div class="ship-progress">
                    <div class="ship-bar ship-<? echo filter_ship_levels($playerinfo[cloak]); ?>" style="width:<? echo ($playerinfo[cloak]/$max_upgrades_devices)*100; ?>%">&nbsp;</div>
                    <div class="ship-component-warning"></div >
                </div>
                <div class="ship-lvl">(<? echo $l_level." ".$playerinfo[cloak]; ?>)</div>
            </div>
        </div>

        <div class="ship-stats-component ship-divider-top">
            <div class="ship-component-name"> Escape Pod</div>
            <div class="ship-storage-quantity"><? echo $escape_pod_warning; echo $escape_pod; ?></div>
        </div>
        <div class="ship-stats-component">
            <div class="ship-component-name"> Warp Analyser</div>
            <div class="ship-storage-quantity"><? echo $lssd; ?></div>
        </div>
        <div class="ship-stats-component">
            <div class="ship-component-name"> Energy Scoop</div>
            <div class="ship-storage-quantity"><? echo $fuel_scoop; ?></div>
        </div>

        <div class="ship-component-component ship-divider-top">
            <div class="ship-component-name"> Ship Average</div>
            <div class="ship-component-bars">
                <div class="ship-progress">
                    <div class="ship-bar ship-<? echo filter_ship_levels($shipavg); ?>" style="width:<? echo ($shipavg/$max_upgrades_devices)*100; ?>%">&nbsp;</div>
                    <div class="ship-component-warning"></div >
                </div>
                <div class="ship-lvl">(<? echo $l_level." ".NUMBER($shipavg, 2); ?>)</div>
            </div>
        </div>
        <?
		/*
		Ship Strength compares components vs what your carrying, e.g.torp launchers vs torpedoes carrying.
		*/
		
		$componenet_strength = $playerinfo[power] + $playerinfo[torp_launchers] + $playerinfo[armor] + $playerinfo[computer];
		$componenet_strength = $componenet_strength/4;
		$strength_total = percent($playerinfo['armor_pts'],$armor_pts_max) + percent($playerinfo['ship_fighters'],$ship_fighters_max) + percent($playerinfo['torps'],$torps_max) + percent($playerinfo['ship_energy'],$energy_max);
		$strength_total = $strength_total / 4;
		?>
        <div class="ship-component-component">
            <div class="ship-component-name"> Ship Strength</div>
            <div class="ship-component-bars">
                <div class="ship-progress">
                    <div class="ship-bar ship-<? echo filter_ship_levels($componenet_strength); ?>" style="width:<? echo $strength_total; ?>%">&nbsp;</div>
                    <div class="ship-component-warning"></div >
                </div>
                <div class="ship-lvl ship-divider-bottom"></div>
            </div>
        </div>
        
        
    </div>
    
    
    <div class="ship-stats-container">
        <div class="ship-stats-component ship-divider-top">
        	<div class="ship-group-title">STORAGE</div>
        </div>
        <div class="ship-stats-component">
            <div class="ship-component-name"> Energy</div>
            <div class="ship-component-bars">
                <div class="ship-progress">
                    <div class="ship-bar ship-0" style="width:<? echo percent($playerinfo['ship_energy'],$energy_max); ?>%">&nbsp;</div>
                    <div class="ship-component-warning"></div >
                </div>
                <div class="ship-lvl ship-divider-bottom">(<? echo NUMBER($playerinfo['ship_energy'])."/".NUMBER($energy_max); ?>)</div>
            </div>
        </div>
        <div class="ship-stats-component">
            <div class="ship-component-name"> Storage</div>
            <div class="ship-component-bars">
                <div class="ship-progress">
                    <div class="ship-bar ship-0" style="width:<? echo percent($holds_used,$holds_max); ?>%">&nbsp;</div>
                    <div class="ship-component-warning"></div >
                </div>
                <div class="ship-lvl ship-divider-bottom">(<? echo NUMBER($holds_used)."/".NUMBER($holds_max); ?>)</div>
            </div>
        </div>
        <div class="ship-stats-component">
            <div class="ship-component-name"> Organics</div>
            <div class="ship-storage-quantity"><? echo NUMBER($playerinfo['ship_organics']); ?></div>
        </div>
        <div class="ship-stats-component">
            <div class="ship-component-name"> Ore</div>
            <div class="ship-storage-quantity"><? echo NUMBER($playerinfo['ship_ore']); ?></div>
        </div>
        <div class="ship-stats-component">
            <div class="ship-component-name"> Goods</div>
            <div class="ship-storage-quantity"><? echo NUMBER($playerinfo['ship_goods']); ?></div>
        </div>
        <div class="ship-stats-component">
            <div class="ship-component-name"> Colonists</div>
            <div class="ship-storage-quantity"><? echo NUMBER($playerinfo['ship_colonists']); ?></div>
        </div>
        
        
        <div class="ship-stats-component ship-divider-top">
        	<div class="ship-group-title ">WEAPONS</div>
        </div>
        <div class="ship-stats-component">
            <div class="ship-component-name"> Armour Points</div>
            <div class="ship-component-bars">
                <div class="ship-progress">
                    <div class="ship-bar ship-0" style="width:<? echo percent($playerinfo['armor_pts'],$armor_pts_max); ?>%">&nbsp;</div>
                    <div class="ship-component-warning"></div >
                </div>
                <div class="ship-lvl ship-divider-bottom">(<? echo NUMBER($playerinfo['armor_pts'])."/".NUMBER($armor_pts_max); ?>)</div>
            </div>
        </div>
        <div class="ship-stats-component">
            <div class="ship-component-name"> Fighters</div>
            <div class="ship-component-bars">
                <div class="ship-progress">
                    <div class="ship-bar ship-0" style="width:<? echo percent($playerinfo['ship_fighters'],$ship_fighters_max); ?>%">&nbsp;</div>
                    <div class="ship-component-warning"></div >
                </div>
                <div class="ship-lvl ship-divider-bottom">(<? echo NUMBER($playerinfo['ship_fighters'])."/".NUMBER($ship_fighters_max); ?>)</div>
            </div>
        </div>
        <div class="ship-stats-component">
            <div class="ship-component-name"> Torpedoes</div>
            <div class="ship-component-bars">
                <div class="ship-progress">
                    <div class="ship-bar ship-0" style="width:<? echo percent($playerinfo['torps'],$torps_max); ?>%">&nbsp;</div>
                    <div class="ship-component-warning"></div >
                </div>
                <div class="ship-lvl ship-divider-bottom">(<? echo NUMBER($playerinfo['torps'])."/".NUMBER($torps_max); ?>)</div>
            </div>
        </div>
        <div class="ship-stats-component ship-divider-top">
        	<div class="ship-group-title ">DEVICES</div>
        </div>
        <div class="ship-stats-component">
            <div class="ship-component-name"> Space Beacons</div>
            <div class="ship-storage-quantity"><? echo $playerinfo[dev_beacon]; ?></div>
        </div>
        <div class="ship-stats-component">
            <div class="ship-component-name"> Warp Editors</div>
            <div class="ship-storage-quantity"><? echo $playerinfo[dev_warpedit]; ?></div>
        </div>
        <div class="ship-stats-component">
            <div class="ship-component-name"> Genesis Torpedoes</div>
            <div class="ship-storage-quantity"><? echo $playerinfo[dev_genesis]; ?></div>
        </div>
        <div class="ship-stats-component">
            <div class="ship-component-name"> Emegency Warp Device</div>
            <div class="ship-storage-quantity"><? echo$emergancy_warp_warning; echo $playerinfo[dev_emerwarp]; ?></div>
        </div>
        <div class="ship-stats-component">
            <div class="ship-component-name"> Mine Deflectors</div>
            <div class="ship-storage-quantity"><? echo $mine_deflector_warning; echo $playerinfo[dev_minedeflector]; ?></div>
        </div>
        
        
        
        
    </div>
</div>

<?


TEXT_GOTOMAIN();
?>
</div>
<?
include "footer.php";
?>


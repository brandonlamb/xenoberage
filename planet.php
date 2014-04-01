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
// File: planet.php

include "config/config.php";
include "combat.php";
updatecookie ();

$title = "Planet Menu";
include "header.php";
$sql_manager = new manage_table();
if (checklogin () )
{
    die ();
}

$destroy = null;
if (array_key_exists('destroy', $_GET) == true)
{
    $destroy = $_GET['destroy'];
}

$command = null;
if (array_key_exists('command', $_REQUEST) == true)
{
    $command = $_REQUEST['command'];
}

$planet_id = null;
if (array_key_exists('planet_id', $_GET) == true)
{
    $planet_id = (int) $_GET['planet_id'];
}
?>
<div class="tablecell content both-border">
	<div class="pad">
<?
bigtitle ();

// Get the Player Info
$playerinfo = $sql_manager->playerinfo($user_ship_id,"");

// Empty out Planet and Ship vars
$planetinfo = null;

// Check if planet_id is valid.
if ($planet_id <= 0 )
{
    echo "Invalid Planet<br/><br/>";
    text_GOTOMAIN ();
    include "footer.php";
    die ();
}

$sectorinfo = $sql_manager->universeinfo($playerinfo['sector'],""); # Load Sector Information
$planetinfo = $sql_manager->planetinfo($planet_id,""); # Load Planet Information
$are_you_real = $sql_manager->real_planet($planet_id);
// Check to see if it returned valid planet info.
if ($are_you_real<1 || (is_bool($planetinfo) && $planetinfo == false))
{
  echo "Invalid Planet<br/><br/>";
  text_GOTOMAIN ();
  die ();
}

if (!is_bool($planetinfo) && $planetinfo != false )
// If there is a planet in the sector show appropriate menu
{
    if ($playerinfo['sector'] != $planetinfo['sector_id'])
    {
        if ($playerinfo['on_planet'] == 'Y')
        {
			/*User not in the sector, most likely the ship was destroyed when a player attacked the planet they where parked on*/
            $resx = $db->Execute("UPDATE {$db->prefix}ships SET on_planet='N' WHERE ship_id=$playerinfo[ship_id]");
            db_op_result ($db, $resx, __LINE__, __FILE__, $db_logging);
        }

        echo "You are not in the same sector as this planet, so you can't interact with it.<br/>";
        text_GOTOMAIN ();
        include "footer.php";
        die ();
    }

    if (($planetinfo['owner'] == 0  || $planetinfo['defeated'] == 'Y') && $command != "capture")
    {
        if ($planetinfo['owner'] == 0)
        {
            echo "This planet is unowned.<br/><br/>";
        }
        echo "You may <a href=planet.php?planet_id=$planet_id&command=capture>Capture</a> the planet or just leave it undefended.<br/><br/>";
        echo "<br/>";
        text_GOTOMAIN ();
        include "footer.php";
        die ();
    }

    if ($planetinfo['owner'] != 0)
    {
		$ownerinfo = $sql_manager->playerinfo($planetinfo['owner'],"");
    }

    if (empty($command))
    {
        // Kami Multi Browser Window Attack Fix
        $_SESSION['planet_selected'] = $planet_id;

        // If there is no planet command already
        if (empty ($planetinfo['name']) )
        {
            echo "Welcome to ".$ownerinfo['character_name']."'s un-named planet.<br/><br/>";
        }
        else
        {
            echo "Welcome to ".$planetinfo['name'].", owned by ".$ownerinfo['character_name'].".<br/><br/>";
        }

        if ($playerinfo['ship_id'] == $planetinfo['owner'])
        {
            if ($destroy == 1 && $allow_genesis_destroy)
            {
                echo "<font color=red>Do you really want to destroy this planet?</font><br/><a href=planet.php?planet_id=$planet_id&destroy=2>yes</A><br/>";
                echo "<a href=planet.php?planet_id=$planet_id>no!</A><br/><br/>";
            }
            elseif ($destroy == 2 && $allow_genesis_destroy)
            {
                if ($playerinfo['dev_genesis'] > 0)
                {
                    $update = $db->Execute("DELETE FROM {$db->prefix}planets WHERE planet_id=$planet_id");
                    db_op_result ($db, $update, __LINE__, __FILE__, $db_logging);
                    $update2 = $db->Execute("UPDATE {$db->prefix}ships SET turns_used=turns_used+1, turns=turns-1,dev_genesis=dev_genesis-1 WHERE ship_id=$playerinfo[ship_id]");
                    db_op_result ($db, $update2, __LINE__, __FILE__, $db_logging);
                    $update3 = $db->Execute("UPDATE {$db->prefix}ships SET on_planet='N' WHERE planet_id=$planet_id");
                    db_op_result ($db, $update3, __LINE__, __FILE__, $db_logging);
                    calc_ownership ($playerinfo['sector']);
                    header("Location: main.php");
                }
                else
                {
                    echo "You do not have any genesis devices.<br/>";
                }
            }
            elseif ($allow_genesis_destroy)
            {
                echo "<A onclick=\"javascript: alert ('alert:Doing this will totally destroy this planet');\" href=planet.php?planet_id=$planet_id&destroy=1>Destroy Planet</a><br/>";
            }
        }

        if ($planetinfo['owner'] == $playerinfo['ship_id'] || ($planetinfo['corp'] == $playerinfo['team'] && $playerinfo['team'] > 0))
        {
            // Owner menu
            echo "Turns available: ".$playerinfo[turns]."<br/>";

            echo "<a href=planet.php?planet_id=$planet_id&command=name>Name</a> planet<br/>";


            if ($playerinfo['on_planet'] == 'Y' && $playerinfo['planet_id'] == $planet_id)
            {
                echo "You are presently on the surface of the planet.<br/>";
                echo "<a href=planet.php?planet_id=$planet_id&command=leave>Leave</a> Planet<br/>";
                echo "<a href='logout.php'>Logout</a><br/>";
            }
            else
            {
                echo "You are presently in orbit of the planet.<br/>";
                echo "<a href=planet.php?planet_id=$planet_id&command=land>Land</a> on Planet<br/>";
            }

            echo "<a href=planet.php?planet_id=$planet_id&command=transfer>Transfer</a> commodities/resources/colonists to/from Planet<br/>";
            if ($planetinfo['sells'] == "Y")
            {
                echo "Planet is presently selling commodities.<br/>";
            }
            else
            {
                echo "Planet is not presently selling commodities!<br/>";
            }

            echo "Toggle planet <a href=planet.php?planet_id=$planet_id&command=sell>Selling</a> commodities<br/>";
            if ($planetinfo['base'] == "N")
            {
                echo "<a href=planet.php?planet_id=$planet_id&command=base>Build</a> a base<br/>";
            }
            else
            {
				
				if ($planetinfo['facility_hydroponics'] == "Y" && $planetinfo['facility_research'] == "Y" && $planetinfo['facility_military'] == "Y" && $planetinfo['facility_medical'] == "Y" && $planetinfo['facility_solarplant'] == "Y" && $planetinfo['facility_shipyard'] == "Y" && $planetinfo['facility_bank'] == "Y")
            	{
				}
				else
				{
					echo "You have a base on this planet.<br/><br/>FACILITIES NOT ACTIVE, ARE CURRENTLY IN STATISCIAL TEST MODE, YOU MAY BUILD THEM, BUT THATS ALL. This is to aid refining a new economy for use within the game, need to ensure its not too fast or too slow!<br/><br/>";
				
				}
                if ($planetinfo['facility_hydroponics'] == "N")
				{
					echo "<a href=planet.php?planet_id=$planet_id&command=facility_hydroponics>Build</a> a hydroponics facility <span class='planet-facilities-requiremenets'>(Requires: ".NUMBER($requirement_hydro_credits)." Credits / Organics: ".NUMBER($requirement_hydro_organics)." / Goods: ".NUMBER($requirement_hydro_goods)." )</span><br/> <sub>Allows planet to generate food without requiring the colonists to farm food, also produces food required for your ships crew!</sub><br/>";
				}
				if ($planetinfo['facility_bank'] == "N")
				{
					echo "<a href=planet.php?planet_id=$planet_id&command=facility_bank>Build</a> a marketing facility <span class='planet-facilities-requiremenets'>(Requires: ".NUMBER($requirement_banking_creds)." Credits / Military Facility)</span><br/> <sub>Allows your people to set up buisness, industry etc, supplies your empire with Rare Ores</sub><br/>";
				}
				if ($planetinfo['facility_shipyard'] == "N")
				{
					echo "<a href=planet.php?planet_id=$planet_id&command=facility_shipyard>Build</a> a shipyard facility <span class='planet-facilities-requiremenets'>(Requires: ".NUMBER($requirement_shipyard_credits)." Credits / Goods: ".number($requirement_shipyard_goods)." / Ore: ".NUMBER($requirement_shipyard_ore)." / Military Facility)</span><br/> <sub>Builds parts required for upgrading your ship.</sub><br/>";
				}
				if ($planetinfo['facility_solarplant'] == "N")
				{
					echo "<a href=planet.php?planet_id=$planet_id&command=facility_solarplant>Build</a> a solarplant facility <span class='planet-facilities-requiremenets'>(Requires: ".NUMBER($requirement_solar_credits)." Credits / Goods: ".number($requirement_solar_goods)." / Ore: ".NUMBER($requirement_solar_ore).")</span><br/> <sub>Used to power planets shields and beams, also to produce organic energy cells (Cells).</sub><br/>";
				}
				if ($planetinfo['facility_medical'] == "N")
				{
					echo "<a href=planet.php?planet_id=$planet_id&command=facility_medical>Build</a> a medical facility <span class='planet-facilities-requiremenets'>(Requires: ".NUMBER($requirement_medical_credits)." Credits / Goods: ".NUMBER($requirement_medical_goods)." / Colonists: ".NUMBER($requirement_medical_cols)." / Hydroponics Facility)</span><br/> <sub>Better healthcare for your colonists, significantly reduced chance of a zombie outbreak!</sub><br/>";
				}
				if ($planetinfo['facility_military'] == "N")
				{
					echo "<a href=planet.php?planet_id=$planet_id&command=facility_military>Build</a> a military facility <span class='planet-facilities-requiremenets'>(Requires: ".NUMBER($requirement_military_credits)." Credits / Fighters: ".NUMBER($requirement_military_figs)." / Torpedoes: ".NUMBER($requirement_military_torps)." / Colonists: ".NUMBER($requirement_military_cols).")</span><br/> <sub>Strengthens your planets defences!</sub><br/>";
				}
				if ($planetinfo['facility_research'] == "N")
				{
					echo "<a href=planet.php?planet_id=$planet_id&command=facility_research>Build</a> a research facility <span class='planet-facilities-requiremenets'>(Requires: ".NUMBER($requirement_research_credits)." Credits / Colonists: ".NUMBER($requirement_research_cols).")</span><br/> <sub>Increases the hacking ability of your ship!</sub><br/>";
				}
				if ($planetinfo['facility_homeworld'] == "N")
				{
					echo "<a href=planet.php?planet_id=$planet_id&command=facility_homeworld>Build</a> a homeworld senate <span class='planet-facilities-requiremenets'>(Requires: Research Facility, Military Facility, Solar Plant Facility, Medical Facility, Shipyard Facility, Banking Facility, Hydrophonics Facility.)</span><br/> <sub>Allows you to assign a senator to the planet!</sub><br/>";
				}
            }

            echo "<br/><a href=log2.php>View</a> Players Log<br/>";

            if ($playerinfo['ship_id'] == $planetinfo['owner'])
            {
                if ($playerinfo['team'] != 0)
                {
                    if ($planetinfo['corp'] == 0)
                    {
                        echo "You can also make this planet a <a href=corp.php?planet_id=$planet_id&action=planetcorp>Corporate Planet</a>.<br/>";
                    }
                    else
                    {
                        echo "You can also make this planet a <a href=corp.php?planet_id=$planet_id&action=planetpersonal>Personal Planet</a>.<br/>";
                    }
                }
            }

            // Change production rates			
            echo "<form action=planet.php?planet_id=$planet_id&command=productions method=post>";
			?>
			<div class="planet-stats-table-container">
			<?
            echo "<table id=\"planet-slider\"><tbody>";
            echo "<tr><td></td><td>Ore</td><td>Organics</td><td>Goods</td><td>Energy</td><td>Fighters</td><td>Torpedoes</td></tr>";
            echo "<tr>";
            echo "<td>Current Quantities</td>";
            echo "<td>" . NUMBER ($planetinfo['ore']) . "</td>";
            echo "<td>" . NUMBER ($planetinfo['organics']) . "</td>";
            echo "<td>" . NUMBER ($planetinfo['goods']) . "</td>";
            echo "<td>" . NUMBER ($planetinfo['energy']) . "</td>";
            echo "<td>" . NUMBER ($planetinfo['fighters']) . "</td>";
            echo "<td>" . NUMBER ($planetinfo['torps']) . "</td>";
            echo "</tr>";
            echo "<tr><td>Production Percentages</td>";
			?>
            <td>
                <span class='slider-background'>
                    <div class="slider"></div>
                    <input type="text" class="percentage-value" name="pore" value="<? echo $planetinfo[prod_ore]; ?>" min="0" max="100" maxlength="6" />
                </span>
            </td>
            <td>
                <span class='slider-background'>
                    <div class="slider"></div>
                    <input type="text" class="percentage-value" name="porganics" value="<? echo $planetinfo[prod_organics]; ?>" min="0" max="100" maxlength="6" />
                </span>
            </td>
            <td>
                <span class='slider-background'>
                    <div class="slider"></div>
                    <input type="text" class="percentage-value" name="pgoods" value="<? echo $planetinfo[prod_goods]; ?>" min="0" max="100" maxlength="6" />
                </span>
            </td>
            <td>
                <span class='slider-background'>
                    <div class="slider"></div>
                    <input type="text" class="percentage-value" name="penergy" value="<? echo $planetinfo[prod_energy]; ?>" min="0" max="100" maxlength="6" />
                </span>
            </td>
            <td>
                <span class='slider-background'>
                    <div class="slider"></div>
                    <input type="text" class="percentage-value" name="pfighters" value="<? echo $planetinfo[prod_fighters]; ?>" min="0" max="100" maxlength="6" />
                </span>
            </td>
            <td>
                <span class='slider-background'>
                    <div class="slider"></div>
                    <input type="text" class="percentage-value" name="ptorp" value="<? echo $planetinfo[prod_torp]; ?>" min="0" max="100" maxlength="6" />
                </span>
            </td>
            </tr>
            <?
            echo "<tr><td class=\"planet-interface-title\">Total Colonists:</td><td colspan=\"6\">" . NUMBER ($planetinfo['colonists']) . "</td></tr>";
            echo "<tr><td class=\"planet-interface-title\">Total Credits:</td><td colspan=\"6\"><span class=\"table_word_yellow table-credits-large\">" . NUMBER ($planetinfo['credits']) . "</span></td></tr>";
            echo "</tbody></table></div>* Production of credits beyond banking interest is 100 - other percentages<br/><br/>";
            echo "<input type=submit value=Update>";
			?>
                <input type='hidden' id="sliderMax" name='sliderMax' value='100'/>
                <input type="hidden" id="sliderTotal" name="sliderTotal" value="0" />
            <?
            echo "</form>";
	
        }
        else
        {
            // Visitor menu
            if ($planetinfo['sells'] == "Y")
			{
                echo "<a href=planet.php?planet_id=$planet_id&command=buy>Buy</a> commodities from Planet<br/>";
            }
            else
            {
                echo "Planet is not presently selling commodities.<br/>";
            }

            // Fix for corp member leaving a non corp planet
            if (($planetinfo['planet_id'] == $playerinfo['planet_id'] && $playerinfo['on_planet'] == "Y") && $planetinfo['corp'] == 0)
            {
                echo "<a href=planet.php?planet_id=$planet_id&command=leave>Leave Friendly Planet</a><br/>";
            }

            $retOwnerInfo = NULL;

            $owner_found = get_planet_owner_information ($db, $planetinfo['planet_id'], $retOwnerInfo);
            if ($owner_found == true && !is_null($retOwnerInfo))
            {
                if ($retOwnerInfo['team'] == $playerinfo['team'] && ($playerinfo['team'] != 0 || $retOwnerInfo['team'] != 0))
                {
                    echo "<div style='color:#ff0;'>Sorry, no Options available for Friendly Owned Private Planets.</div>\n";
                }
                else
                {
                    echo "<a href=planet.php?planet_id=$planet_id&command=attac>Attack</a><br/>";
                    echo "<a href=planet.php?planet_id=$planet_id&command=scan>Scan</a><br/>";
                    if ($sofa_on)
                    {
                        echo "<a href=planet.php?planet_id=$planet_id&command=bom>Sub-Orbital Fighter Attack</a><br/>";
                    }
                }
            }
        }
    }
    elseif ($planetinfo['owner'] == $playerinfo['ship_id'] || ($planetinfo['corp'] == $playerinfo['team'] && $playerinfo['team'] > 0))
    {
        // Player owns planet and there is a command
        if ($command == "sell")
        {
            if ($planetinfo['sells'] == "Y")
            {
                // Set planet to not sell
                echo "Planet now set not to sell.<br/>";
                $result4 = $db->Execute("UPDATE {$db->prefix}planets SET sells='N' WHERE planet_id=$planet_id");
                db_op_result ($db, $result4, __LINE__, __FILE__, $db_logging);
            }
            else
            {
                echo "Planet now set to sell.<br/>";
                $result4b = $db->Execute ("UPDATE {$db->prefix}planets SET sells='Y' WHERE planet_id=$planet_id");
                db_op_result ($db, $result4b, __LINE__, __FILE__, $db_logging);
            }
        }
        elseif ($command == "name")
        {
            // Name menu
            echo "<form action=\"planet.php?planet_id=$planet_id&command=cname\" method=\"post\">";
            echo "Enter new planet name:  ";
            echo "<input type=\"text\" name=\"new_name\" size=\"20\" maxlength=\"20\" value=\"$planetinfo[name]\"><br/><br/>";
            echo "<input type=\"submit\" value=\"Submit\"><input type=\"reset\" value=\"Reset\"><br/><br/>";
            echo "</form>";
        }
        elseif ($command == "cname")
        {
            // Name2 menu
            $new_name = trim (strip_tags ($_POST['new_name']) );
            $new_name = addslashes ($new_name);
            $result5 = $db->Execute("UPDATE {$db->prefix}planets SET name='$new_name' WHERE planet_id=$planet_id");
            db_op_result ($db, $result5, __LINE__, __FILE__, $db_logging);
            $new_name = stripslashes ($new_name);
            echo "Planet name changed to ".$new_name;
        }
        elseif ($command == "land")
        {
            // Land menu
            echo "You have landed on the planet's surface.<br/><br/>";
            $update = $db->Execute("UPDATE {$db->prefix}ships SET on_planet='Y', planet_id=$planet_id WHERE ship_id=$playerinfo[ship_id]");
            db_op_result ($db, $update, __LINE__, __FILE__, $db_logging);
        }
        elseif ($command == "leave")
        {
            // Leave menu
            echo "You are no longer on the planet's surface.<br/><br/>";
            $update = $db->Execute("UPDATE {$db->prefix}ships SET on_planet='N' WHERE ship_id=$playerinfo[ship_id]");
            db_op_result ($db, $update, __LINE__, __FILE__, $db_logging);
        }
        elseif ($command == "transfer")
        {
            // Transfer menu
            $free_holds = NUM_HOLDS ($playerinfo['hull']) - $playerinfo['ship_ore'] - $playerinfo['ship_organics'] - $playerinfo['ship_goods'] - $playerinfo['ship_colonists'];
            $free_power = NUM_ENERGY ($playerinfo['power']) - $playerinfo['ship_energy'];
            echo "You have room for ".NUMBER ($free_holds)." units of additional cargo.  You have capacity for ".NUMBER ($free_power)." units of addtional power<br/><br/>";
            echo "<form action=planet2.php?planet_id=$planet_id method=post>";
			/*
			Build table
			*/	
			?>
			<div class="general-table-container">
			<?
            echo "<table><tbody>";
            echo "<tr><td>Commodity</td><td>Planet</td><td>Ship</td><td>Transfer</td><td>To Planet?</td><td>All?</td></tr>";
			
            echo "<tr><td>Ore</td><td>" . NUMBER ($planetinfo['ore']) . "</td><td>" . NUMBER ($playerinfo['ship_ore']) . "</td><td><input type=text name=transfer_ore size=10 value='0'  maxlength=20></td><td><div class='checkbox-square'><input type=CHECKBOX id='tpore' name=tpore value=-1><label for='tpore'></label></div></td><td><div class='checkbox-square'><input type=CHECKBOX id='allore' name=allore value=-1><label for='allore'></label></div></td></tr>";
			
            echo "<tr><td>Organics</td><td>" . NUMBER ($planetinfo['organics']) . "</td><td>" . NUMBER ($playerinfo['ship_organics']) . "</td><td><input type=text name=transfer_organics size=10 value='0'  maxlength=20></td><td><div class='checkbox-square'><input type=CHECKBOX id='tporganics' name=tporganics value=-1><label for='tporganics'></label></div></td><td><div class='checkbox-square'><input type=CHECKBOX id='allorganics' name=allorganics value=-1><label for='allorganics'></label></div></td></tr>";
			
            echo "<tr><td>Goods</td><td>" . NUMBER ($planetinfo['goods']) . "</td><td>" . NUMBER ($playerinfo['ship_goods']) . "</td><td><input type=text name=transfer_goods size=10 value='0'  maxlength=20></td><td><div class='checkbox-square'><input type=CHECKBOX id='tpgoods' name=tpgoods value=-1><label for='tpgoods'></label></div></td><td><div class='checkbox-square'><input type=CHECKBOX id='allgoods' name=allgoods value=-1><label for='allgoods'></label></div></td></tr>";
			
            echo "<tr><td>Energy</td><td>" . NUMBER ($planetinfo['energy']) . "</td><td>" . NUMBER ($playerinfo['ship_energy']) . "</td><td><input type=text name=transfer_energy size=10 value='0'  maxlength=20></td><td><div class='checkbox-square'><input type=CHECKBOX id='tpenergy' name=tpenergy value=-1><label for='tpenergy'></label></div></td><td><div class='checkbox-square'><input type=CHECKBOX id='allenergy' name=allenergy value=-1><label for='allenergy'></label></div></td></tr>";
			
            echo "<tr><td>Colonists</td><td>" . NUMBER ($planetinfo['colonists']) . "</td><td>" . NUMBER ($playerinfo['ship_colonists']) . "</td><td><input type=text name=transfer_colonists size=10 value='0'  maxlength=20></td><td><div class='checkbox-square'><input type=CHECKBOX id='tpcolonists' name=tpcolonists value=-1><label for='tpcolonists'></label></div></td><td><div class='checkbox-square'><input type=CHECKBOX id='allcolonists' name=allcolonists value=-1><label for='allcolonists'></label></div></td></tr>";
			
            echo "<tr><td>Fighters</td><td>" . NUMBER ($planetinfo['fighters']) . "</td><td>" . NUMBER ($playerinfo['ship_fighters']) . "</td><td><input type=text name=transfer_fighters size=10 value='0'  maxlength=20></td><td><div class='checkbox-square'><input type=CHECKBOX id='tpfighters' name=tpfighters value=-1><label for='tpfighters'></label></div></td><td><div class='checkbox-square'><input type=CHECKBOX id='allfighters' name=allfighters value=-1><label for='allfighters'></label></div></td></tr>";
			
            echo "<tr><td>Torpedoes</td><td>" . NUMBER ($planetinfo['torps']) . "</td><td>" . NUMBER ($playerinfo['torps']) . "</td><td><input type=text name=transfer_torps size=10 value='0'  maxlength=20></td><td><div class='checkbox-square'><input type=CHECKBOX id='tptorps' name=tptorps value=-1><label for='tptorps'></label></div></td><td><div class='checkbox-square'><input type=CHECKBOX id='alltorps' name=alltorps value=-1><label for='alltorps'></label></div></td></tr>";
			
            echo "<tr><td>Credits</td><td><span class=\"table_word_yellow\">" . NUMBER ($planetinfo['credits']) . "</span></td><td>" . NUMBER ($playerinfo['credits']) . "</td><td><input type=text name=transfer_credits size=10 value='0' maxlength=20></td><td><div class='checkbox-square'><input type=CHECKBOX id='tpcredits' name=tpcredits value=-1><label for='tpcredits'></label></div></td><td><div class='checkbox-square'><input type=CHECKBOX id='allcredits' name=allcredits value=-1><label for='allcredits'></label></div></td></tr>";
            echo "</tbody></table></div>";
            echo "<input type=submit value=Transfer>&nbsp;<input type=RESET value=Reset>";
            echo "</form>";
			

			
        }
        elseif ($command == "base")
        {
            if (array_key_exists('planet_selected', $_SESSION) == false )
            {
                $_SESSION['planet_selected'] = '';
            }

            // Kami Multi Browser Window Attack Fix
            if ($_SESSION['planet_selected'] != $planet_id && $_SESSION['planet_selected'] != '')
            {
                adminlog($db, 57, "{$ip}|{$playerinfo['ship_id']}|Tried to create a base without clicking on the Planet.");
                echo "You need to Click on the planet first.<br/><br/>";
                text_GOTOMAIN ();
                include "footer.php";
                die ();
            }
            unset($_SESSION['planet_selected']);

            // Build a base
            if ($planetinfo['ore'] >= $base_ore && $planetinfo['organics'] >= $base_organics && $planetinfo['goods'] >= $base_goods && $planetinfo['credits'] >= $base_credits)
            {
                // Check if the player has enough turns to create the base.
                if ($playerinfo['turns'] <= 0)
                {
                    echo "You do not have enough turns to perform this operation!";
                }
                else
                {
                    // Create The Base
                    $update1 = $db->Execute("UPDATE {$db->prefix}planets SET base='Y', ore=$planetinfo[ore]-$base_ore, organics=$planetinfo[organics]-$base_organics, goods=$planetinfo[goods]-$base_goods, credits=$planetinfo[credits]-$base_credits WHERE planet_id=$planet_id");
                    db_op_result ($db, $update1, __LINE__, __FILE__, $db_logging);

                    // Update User Turns
                    $update1b = $db->Execute("UPDATE {$db->prefix}ships SET turns=turns-1, turns_used=turns_used+1 WHERE ship_id=$playerinfo[ship_id]");
                    db_op_result ($db, $update1b, __LINE__, __FILE__, $db_logging);

                    // Refresh Plant Info
                    $result3 = $db->Execute("SELECT * FROM {$db->prefix}planets WHERE planet_id=$planet_id");
                    db_op_result ($db, $result3, __LINE__, __FILE__, $db_logging);
                    $planetinfo = $result3->fields;

                    // Notify User Of Base Results
                    echo "Base constructed<br/><br/>";

                    // Calc Ownership and Notify User Of Results
                    $ownership = calc_ownership ($playerinfo['sector'] );
                    if (!empty($ownership))
                    {
                        echo "$ownership<p>";
                    }
                }
            }
            else
            {
                echo "To build a base there must be at least ".$base_credits." credits, ".$base_ore." units of ore, ".$base_organics." units of organics, and ".$base_goods." units of goods on the planet .<br/><br/>";
            }
        }
		####################################################################################################################################################################################
		####################################################################################################################################################################################
		## Facility Commands Slot In Here!
		####################################################################################################################################################################################
		####################################################################################################################################################################################
        elseif ($command == "facility_hydroponics")
        {
            if (array_key_exists('planet_selected', $_SESSION) == false )
            {
                $_SESSION['planet_selected'] = '';
            }
            // Kami Multi Browser Window Attack Fix
            if ($_SESSION['planet_selected'] != $planet_id && $_SESSION['planet_selected'] != '')
            {
                adminlog($db, 57, "{$ip}|{$playerinfo['ship_id']}|Tried to create a hydrophonics facility without clicking on the Planet.");
                echo "You need to Click on the planet first.<br/><br/>";
                text_GOTOMAIN ();
                include "footer.php";
                die ();
            }
            unset($_SESSION['planet_selected']);

            // Build a hydrophonics facility
            if ($planetinfo['organics'] >= $requirement_hydro_organics && $planetinfo['goods'] >= $requirement_hydro_goods && $planetinfo['credits'] >= $requirement_hydro_credits)
            {
                // Check if the player has enough turns to create the base.
                if ($playerinfo['turns'] <= 0)
                {
                    echo "You do not have enough turns to perform this operation!";
                }
                else
                {
                    // Create The Base
                    $update1 = $db->Execute("UPDATE {$db->prefix}planets SET facility_hydroponics='Y', organics=$planetinfo[organics]-$requirement_hydro_organics, goods=$planetinfo[goods]-$requirement_hydro_goods, credits=$planetinfo[credits]-$requirement_hydro_credits WHERE planet_id=$planet_id");
                    db_op_result ($db, $update1, __LINE__, __FILE__, $db_logging);

                    // Update User Turns
                    $update1b = $db->Execute("UPDATE {$db->prefix}ships SET turns=turns-1, turns_used=turns_used+1 WHERE ship_id=$playerinfo[ship_id]");
                    db_op_result ($db, $update1b, __LINE__, __FILE__, $db_logging);

                    // Refresh Planet Info
                    $result3 = $db->Execute("SELECT * FROM {$db->prefix}planets WHERE planet_id=$planet_id");
                    db_op_result ($db, $result3, __LINE__, __FILE__, $db_logging);
                    $planetinfo = $result3->fields;

                    // Notify User Of Base Results
                    echo "Hydrophonics Facility Built<br/><br/>";
                }
            }
            else
            {
                echo "To build a hydrophonics facility there must be at least ".NUMBER($requirement_hydro_credits)." credits,".NUMBER($requirement_hydro_organics)." units of organics, ".NUMBER($requirement_hydro_goods)." units of goods on the planet .<br/><br/>";
            }
        }
        elseif ($command == "facility_bank")
        {
            if (array_key_exists('planet_selected', $_SESSION) == false )
            {
                $_SESSION['planet_selected'] = '';
            }
            // Kami Multi Browser Window Attack Fix
            if ($_SESSION['planet_selected'] != $planet_id && $_SESSION['planet_selected'] != '')
            {
                adminlog($db, 57, "{$ip}|{$playerinfo['ship_id']}|Tried to create a marketing facility without clicking on the Planet.");
                echo "You need to Click on the planet first.<br/><br/>";
                text_GOTOMAIN ();
                include "footer.php";
                die ();
            }
            unset($_SESSION['planet_selected']);

            // Build a banking facility
            if ($planetinfo['credits'] >= $requirement_banking_creds && $planetinfo['facility_military'] == "Y")
            {
                // Check if the player has enough turns to create the base.
                if ($playerinfo['turns'] <= 0)
                {
                    echo "You do not have enough turns to perform this operation!";
                }
                else
                {
                    // Create The Base
                    $update1 = $db->Execute("UPDATE {$db->prefix}planets SET facility_bank='Y', credits=$planetinfo[credits]-$requirement_banking_creds WHERE planet_id=$planet_id");
                    db_op_result ($db, $update1, __LINE__, __FILE__, $db_logging);

                    // Update User Turns
                    $update1b = $db->Execute("UPDATE {$db->prefix}ships SET turns=turns-1, turns_used=turns_used+1 WHERE ship_id=$playerinfo[ship_id]");
                    db_op_result ($db, $update1b, __LINE__, __FILE__, $db_logging);

                    // Refresh Planet Info
                    $result3 = $db->Execute("SELECT * FROM {$db->prefix}planets WHERE planet_id=$planet_id");
                    db_op_result ($db, $result3, __LINE__, __FILE__, $db_logging);
                    $planetinfo = $result3->fields;

                    // Notify User Of Base Results
                    echo "Banking Facility Built<br/><br/>";
                }
            }
            else
            {
                echo "To build a marketing facility there must be at least ".NUMBER($requirement_banking_creds)." credits on the planet, including a military base.<br/><br/>";
            }
        }
        elseif ($command == "facility_shipyard")
        {
            if (array_key_exists('planet_selected', $_SESSION) == false )
            {
                $_SESSION['planet_selected'] = '';
            }
            // Kami Multi Browser Window Attack Fix
            if ($_SESSION['planet_selected'] != $planet_id && $_SESSION['planet_selected'] != '')
            {
                adminlog($db, 57, "{$ip}|{$playerinfo['ship_id']}|Tried to create a shipyard facility without clicking on the Planet.");
                echo "You need to Click on the planet first.<br/><br/>";
                text_GOTOMAIN ();
                include "footer.php";
                die ();
            }
            unset($_SESSION['planet_selected']);

            // Build a shipyard facility
            if ($planetinfo['credits'] >= $requirement_shipyard_credits && $planetinfo['ore'] >= $requirement_shipyard_ore && $planetinfo['goods'] >= $requirement_shipyard_goods && $planetinfo['facility_military'] == "Y")
            {
                // Check if the player has enough turns to create the base.
                if ($playerinfo['turns'] <= 0)
                {
                    echo "You do not have enough turns to perform this operation!";
                }
                else
                {
                    // Create The Base
                    $update1 = $db->Execute("UPDATE {$db->prefix}planets SET facility_shipyard='Y', credits=$planetinfo[credits]-$requirement_shipyard_credits, ore=$planetinfo[ore]-$requirement_shipyard_ore, goods=$planetinfo[goods]-$requirement_shipyard_goods WHERE planet_id=$planet_id");
                    db_op_result ($db, $update1, __LINE__, __FILE__, $db_logging);

                    // Update User Turns
                    $update1b = $db->Execute("UPDATE {$db->prefix}ships SET turns=turns-1, turns_used=turns_used+1 WHERE ship_id=$playerinfo[ship_id]");
                    db_op_result ($db, $update1b, __LINE__, __FILE__, $db_logging);

                    // Refresh Planet Info
                    $result3 = $db->Execute("SELECT * FROM {$db->prefix}planets WHERE planet_id=$planet_id");
                    db_op_result ($db, $result3, __LINE__, __FILE__, $db_logging);
                    $planetinfo = $result3->fields;

                    // Notify User Of Base Results
                    echo "Shipyard Facility Built<br/><br/>";
                }
            }
            else
            {
                echo "To build a shipyard facility there must be at least ".NUMBER($requirement_shipyard_credits)." credits, ".NUMBER($requirement_shipyard_ore)." ore units, ".NUMBER($requirement_shipyard_goods)." units of goods on the planet, including a military base.<br/><br/>";
            }
        }
        elseif ($command == "facility_solarplant")
        {
            if (array_key_exists('planet_selected', $_SESSION) == false )
            {
                $_SESSION['planet_selected'] = '';
            }
            // Kami Multi Browser Window Attack Fix
            if ($_SESSION['planet_selected'] != $planet_id && $_SESSION['planet_selected'] != '')
            {
                adminlog($db, 57, "{$ip}|{$playerinfo['ship_id']}|Tried to create a solarplant facility without clicking on the Planet.");
                echo "You need to Click on the planet first.<br/><br/>";
                text_GOTOMAIN ();
                include "footer.php";
                die ();
            }
            unset($_SESSION['planet_selected']);

            // Build a solarplant facility
            if ($planetinfo['credits'] >= $requirement_solar_credits && $planetinfo['ore'] >= $requirement_solar_ore && $planetinfo['goods'] >= $requirement_solar_goods)
            {
                // Check if the player has enough turns to create the base.
                if ($playerinfo['turns'] <= 0)
                {
                    echo "You do not have enough turns to perform this operation!";
                }
                else
                {
                    // Create The Base
                    $update1 = $db->Execute("UPDATE {$db->prefix}planets SET facility_solarplant='Y', credits=$planetinfo[credits]-$requirement_solar_credits, ore=$planetinfo[ore]-$requirement_solar_ore, goods=$planetinfo[goods]-$requirement_solar_goods WHERE planet_id=$planet_id");
                    db_op_result ($db, $update1, __LINE__, __FILE__, $db_logging);

                    // Update User Turns
                    $update1b = $db->Execute("UPDATE {$db->prefix}ships SET turns=turns-1, turns_used=turns_used+1 WHERE ship_id=$playerinfo[ship_id]");
                    db_op_result ($db, $update1b, __LINE__, __FILE__, $db_logging);

                    // Refresh Planet Info
                    $result3 = $db->Execute("SELECT * FROM {$db->prefix}planets WHERE planet_id=$planet_id");
                    db_op_result ($db, $result3, __LINE__, __FILE__, $db_logging);
                    $planetinfo = $result3->fields;

                    // Notify User Of Base Results
                    echo "Solarplant Facility Built<br/><br/>";
                }
            }
            else
            {
                echo "To build a solarplant facility there must be at least ".NUMBER($requirement_solar_credits)." credits, ".NUMBER($requirement_solar_ore)." ore units, ".NUMBER($requirement_solar_goods)." units of goods on the planet including a military base.<br/><br/>";
            }
        }
        elseif ($command == "facility_medical")
        {
            if (array_key_exists('planet_selected', $_SESSION) == false )
            {
                $_SESSION['planet_selected'] = '';
            }
            // Kami Multi Browser Window Attack Fix
            if ($_SESSION['planet_selected'] != $planet_id && $_SESSION['planet_selected'] != '')
            {
                adminlog($db, 57, "{$ip}|{$playerinfo['ship_id']}|Tried to create a medical facility without clicking on the Planet.");
                echo "You need to Click on the planet first.<br/><br/>";
                text_GOTOMAIN ();
                include "footer.php";
                die ();
            }
            unset($_SESSION['planet_selected']);

            // Build a solarplant facility
            if ($planetinfo['credits'] >= $requirement_medical_credits && $planetinfo['colonists'] >= $requirement_medical_cols && $planetinfo['goods'] >= $requirement_medical_goods && $planetinfo['facility_hydroponics'] == "Y")
            {
                // Check if the player has enough turns to create the base.
                if ($playerinfo['turns'] <= 0)
                {
                    echo "You do not have enough turns to perform this operation!";
                }
                else
                {
                    // Create The Base
                    $update1 = $db->Execute("UPDATE {$db->prefix}planets SET facility_medical='Y', credits=$planetinfo[credits]-$requirement_medical_credits, colonists=$planetinfo[colonists]-$requirement_medical_cols, goods=$planetinfo[goods]-$requirement_medical_goods WHERE planet_id=$planet_id");
                    db_op_result ($db, $update1, __LINE__, __FILE__, $db_logging);

                    // Update User Turns
                    $update1b = $db->Execute("UPDATE {$db->prefix}ships SET turns=turns-1, turns_used=turns_used+1 WHERE ship_id=$playerinfo[ship_id]");
                    db_op_result ($db, $update1b, __LINE__, __FILE__, $db_logging);

                    // Refresh Planet Info
                    $result3 = $db->Execute("SELECT * FROM {$db->prefix}planets WHERE planet_id=$planet_id");
                    db_op_result ($db, $result3, __LINE__, __FILE__, $db_logging);
                    $planetinfo = $result3->fields;

                    // Notify User Of Base Results
                    echo "Medical Facility Built<br/><br/>";
                }
            }
            else
            {
                echo "To build a medical facility there must be at least ".NUMBER($requirement_medical_credits)." credits, ".NUMBER($requirement_medical_goods)." units of goods, ".NUMBER($requirement_medical_cols)." colonists on the planet, including a hydroponics facility.<br/><br/>";
            }
        }
        elseif ($command == "facility_military")
        {
            if (array_key_exists('planet_selected', $_SESSION) == false )
            {
                $_SESSION['planet_selected'] = '';
            }
            // Kami Multi Browser Window Attack Fix
            if ($_SESSION['planet_selected'] != $planet_id && $_SESSION['planet_selected'] != '')
            {
                adminlog($db, 57, "{$ip}|{$playerinfo['ship_id']}|Tried to create a military facility without clicking on the Planet.");
                echo "You need to Click on the planet first.<br/><br/>";
                text_GOTOMAIN ();
                include "footer.php";
                die ();
            }
            unset($_SESSION['planet_selected']);

            // Build a military  facility
            if ($planetinfo['credits'] >= $requirement_military_credits && $planetinfo['fighters'] >= $requirement_military_figs && $planetinfo['colonists'] >= $requirement_military_cols && $planetinfo['torps'] >= $requirement_military_torps)
            {
                // Check if the player has enough turns to create the base.
                if ($playerinfo['turns'] <= 0)
                {
                    echo "You do not have enough turns to perform this operation!";
                }
                else
                {
                    // Create The Base
                    $update1 = $db->Execute("UPDATE {$db->prefix}planets SET facility_military='Y', credits=$planetinfo[credits]-$requirement_military_credits, fighters=$planetinfo[fighters]-$requirement_military_figs, colonists=$planetinfo[colonists]-$requirement_military_cols, torps=$planetinfo[torps]-$requirement_military_torps WHERE planet_id=$planet_id");
                    db_op_result ($db, $update1, __LINE__, __FILE__, $db_logging);

                    // Update User Turns
                    $update1b = $db->Execute("UPDATE {$db->prefix}ships SET turns=turns-1, turns_used=turns_used+1 WHERE ship_id=$playerinfo[ship_id]");
                    db_op_result ($db, $update1b, __LINE__, __FILE__, $db_logging);

                    // Refresh Planet Info
                    $result3 = $db->Execute("SELECT * FROM {$db->prefix}planets WHERE planet_id=$planet_id");
                    db_op_result ($db, $result3, __LINE__, __FILE__, $db_logging);
                    $planetinfo = $result3->fields;

                    // Notify User Of Base Results
                    echo "Military Facility Built<br/><br/>";
                }
            }
            else
            {
                echo "To build a military facility there must be at least ".NUMBER($requirement_military_credits)." credits, ".NUMBER($requirement_military_figs)." fighters, ".NUMBER($requirement_military_cols)." colonists, ".NUMBER($requirement_military_torps)." torpedoes on the planet.<br/><br/>";
            }
        }
        elseif ($command == "facility_research")
        {
            if (array_key_exists('planet_selected', $_SESSION) == false )
            {
                $_SESSION['planet_selected'] = '';
            }
            // Kami Multi Browser Window Attack Fix
            if ($_SESSION['planet_selected'] != $planet_id && $_SESSION['planet_selected'] != '')
            {
                adminlog($db, 57, "{$ip}|{$playerinfo['ship_id']}|Tried to create a research facility without clicking on the Planet.");
                echo "You need to Click on the planet first.<br/><br/>";
                text_GOTOMAIN ();
                include "footer.php";
                die ();
            }
            unset($_SESSION['planet_selected']);

            // Build a solarplant facility
            if ($planetinfo['credits'] >= $requirement_research_credits && $planetinfo['colonists'] >= $requirement_research_cols)
            {
                // Check if the player has enough turns to create the base.
                if ($playerinfo['turns'] <= 0)
                {
                    echo "You do not have enough turns to perform this operation!";
                }
                else
                {
                    // Create The Base
                    $update1 = $db->Execute("UPDATE {$db->prefix}planets SET facility_research='Y', credits=$planetinfo[credits]-$requirement_research_credits, colonists=$planetinfo[colonists]-$requirement_research_cols WHERE planet_id=$planet_id");
                    db_op_result ($db, $update1, __LINE__, __FILE__, $db_logging);

                    // Update User Turns
                    $update1b = $db->Execute("UPDATE {$db->prefix}ships SET turns=turns-1, turns_used=turns_used+1 WHERE ship_id=$playerinfo[ship_id]");
                    db_op_result ($db, $update1b, __LINE__, __FILE__, $db_logging);

                    // Refresh Planet Info
                    $result3 = $db->Execute("SELECT * FROM {$db->prefix}planets WHERE planet_id=$planet_id");
                    db_op_result ($db, $result3, __LINE__, __FILE__, $db_logging);
                    $planetinfo = $result3->fields;

                    // Notify User Of Base Results
                    echo "Research Facility Built<br/><br/>";
                }
            }
            else
            {
                echo "To build a research facility there must be at least ".NUMBER($requirement_research_credits)." credits, ".NUMBER($requirement_research_cols)." colonists on the planet.<br/><br/>";
            }
        }
        elseif ($command == "facility_homeworld")
        {
            if (array_key_exists('planet_selected', $_SESSION) == false )
            {
                $_SESSION['planet_selected'] = '';
            }
            // Kami Multi Browser Window Attack Fix
            if ($_SESSION['planet_selected'] != $planet_id && $_SESSION['planet_selected'] != '')
            {
                adminlog($db, 57, "{$ip}|{$playerinfo['ship_id']}|Tried to create a homeworld without clicking on the Planet.");
                echo "You need to Click on the planet first.<br/><br/>";
                text_GOTOMAIN ();
                include "footer.php";
                die ();
            }
            unset($_SESSION['planet_selected']);

            // Build a solarplant facility
            if ($planetinfo['facility_hydroponics'] == "Y" && $planetinfo['facility_research'] == "Y" && $planetinfo['facility_military'] == "Y" && $planetinfo['facility_medical'] == "Y" && $planetinfo['facility_solarplant'] == "Y" && $planetinfo['facility_shipyard'] == "Y" && $planetinfo['facility_bank'] == "Y")
            {
                // Check if the player has enough turns to create the base.
                if ($playerinfo['turns'] <= 0)
                {
                    echo "You do not have enough turns to perform this operation!";
                }
                else
                {
                    // Create The Base
                    $update1 = $db->Execute("UPDATE {$db->prefix}planets SET facility_homeworld='Y' WHERE planet_id=$planet_id");
                    db_op_result ($db, $update1, __LINE__, __FILE__, $db_logging);

                    // Update User Turns
                    $update1b = $db->Execute("UPDATE {$db->prefix}ships SET turns=turns-1, turns_used=turns_used+1 WHERE ship_id=$playerinfo[ship_id]");
                    db_op_result ($db, $update1b, __LINE__, __FILE__, $db_logging);

                    // Refresh Planet Info
                    $result3 = $db->Execute("SELECT * FROM {$db->prefix}planets WHERE planet_id=$planet_id");
                    db_op_result ($db, $result3, __LINE__, __FILE__, $db_logging);
                    $planetinfo = $result3->fields;

                    // Notify User Of Base Results
                    echo "Homeworld assigned and Built<br/><br/>";
                }
            }
            else
            {
                echo "To build a Homeworld, you need to ensure you have all facilities built first.<br/><br/>";
            }
        }
		####################################################################################################################################################################################
		####################################################################################################################################################################################
		## End Facilities
		####################################################################################################################################################################################
		####################################################################################################################################################################################
		
		
        elseif ($command == "productions")
        {
            // Change production percentages
            $pore       = (int) array_key_exists('pore', $_POST)?$_POST['pore']:0;
            $porganics  = (int) array_key_exists('porganics', $_POST)?$_POST['porganics']:0;
            $pgoods     = (int) array_key_exists('pgoods', $_POST)?$_POST['pgoods']:0;
            $penergy    = (int) array_key_exists('penergy', $_POST)?$_POST['penergy']:0;
            $pfighters  = (int) array_key_exists('pfighters', $_POST)?$_POST['pfighters']:0;
            $ptorp      = (int) array_key_exists('ptorp', $_POST)?$_POST['ptorp']:0;

            if ($porganics < 0.0 || $pore < 0.0 || $pgoods < 0.0 || $penergy < 0.0 || $pfighters < 0.0 || $ptorp < 0.0)
            {
                echo "You may not change production percentages to a negative number.<br/><br/>";
            }
            elseif (($porganics + $pore + $pgoods + $penergy + $pfighters + $ptorp) > 100.0)
            {
                echo "You may not change production percentages to higher than a total of 100%.<br/><br/>";
            }
            else
            {
                $resx = $db->Execute("UPDATE {$db->prefix}planets SET prod_ore=$pore,prod_organics=$porganics,prod_goods=$pgoods,prod_energy=$penergy,prod_fighters=$pfighters,prod_torp=$ptorp WHERE planet_id=$planet_id");
                db_op_result ($db, $resx, __LINE__, __FILE__, $db_logging);
                echo "Production percentages changed.<br/><br/>";
            }
        }
        else
        {
            echo "Command not available.<br/>";
        }
    }
    elseif (($planetinfo['planet_id'] == $playerinfo['planet_id'] && $playerinfo['on_planet'] == "Y") && $planetinfo['corp'] == 0) // Fix for corp member leaving a non corp planet
    {
        if ($command == "leave")
        {
            // Leave menu
            echo "You are no longer on the planet's surface.<br/><br/>";
            $update = $db->Execute("UPDATE {$db->prefix}ships SET on_planet = 'N', planet_id = 0 WHERE ship_id=$playerinfo[ship_id]");
            db_op_result ($db, $update, __LINE__, __FILE__, $db_logging);
            echo "Click <a href='main.php'>here</a> to return to the main menu.<br/>";
            header("Location: main.php");
        }
    }
    else
    {
        // Player doesn't own planet and there is a command
        if ($command == "buy")
        {
            if ($planetinfo['sells'] == "Y")
            {
                $ore_price = ($ore_price + $ore_delta / 4);
                $organics_price = ($organics_price + $organics_delta / 4);
                $goods_price = ($goods_price + $goods_delta / 4);
                $energy_price = ($energy_price + $energy_delta / 4);
                echo "<form action=planet3.php?planet_id=$planet_id method=post>";
				/*
				Build table
				*/	
				?>
				<div class="general-table-container">
				<?
                echo "<table><tbody>";
                echo "<tr><td>Commodity</td><td>Available</td><td>Price</td><td>Buy</td><td>Ship</td></tr>";
                echo "<tr><td>Ore</td><td>$planetinfo[ore]</td><td>$ore_price</td><td><input type=text name=trade_ore size=10 maxlength=20 value=0></td><td>$playerinfo[ship_ore]</td></tr>";
                echo "<tr><td>Organics</td><td>$planetinfo[organics]</td><td>$organics_price</td><td><input type=text name=trade_organics size=10 maxlength=20 value=0></td><td>$playerinfo[ship_organics]</td></tr>";
                echo "<tr><td>Goods</td><td>$planetinfo[goods]</td><td>$goods_price</td><td><input type=text name=trade_goods size=10 maxlength=20 value=0></td><td>$playerinfo[ship_goods]</td></tr>";
                echo "<tr><td>Energy</td><td>$planetinfo[energy]</td><td>$energy_price</td><td><input type=text name=trade_energy size=10 maxlength=20 value=0></td><td>$playerinfo[ship_energy]</td></tr>";
                echo "</tbody></table></div>";
                echo "<input type=submit value=Submit><input type=reset value=Reset><br/></form>";
            }
            else
            {
                echo "Planet is not presently selling commodities.<br/>";
            }
        }
        elseif ($command == "attac")
        {
            // Kami Multi Browser Window Attack Fix
            if (array_key_exists('planet_selected', $_SESSION) == false || $_SESSION['planet_selected'] != $planet_id)
            {
                adminlog($db, 57, "{$ip}|{$playerinfo['ship_id']}|Tried to start an attack without clicking on the Planet.");
                echo "You need to Click on the planet first.<br/><br/>";
                text_GOTOMAIN ();
                include "footer.php";
                die ();
            }

            // Check to see if sure
            if ($planetinfo['sells'] == "Y")
            {
                echo "<a href=planet.php?planet_id=$planet_id&command=buy>Buy</a> commodities from Planet<br/>";
            }
            else
            {
                echo "Planet is not presently selling commodities.<br/>";
            }

            $retOwnerInfo = NULL;
            $owner_found = get_planet_owner_information ($db, $planetinfo['planet_id'], $retOwnerInfo);
            if ($owner_found == true && !is_null($retOwnerInfo))
            {
                if ($retOwnerInfo['team'] == $playerinfo['team'] && ($playerinfo['team'] != 0 || $retOwnerInfo['team'] != 0))
                {
                    echo "<div style='color:#ff0;'>Sorry, You cannot attack a Friendly Owned Private Planet.</div>\n";
                }
                else
                {
                    echo "<a href=planet.php?planet_id=$planet_id&command=attack>Are you sure you want to attack the planet?</a><br/>";
                    echo "<a href=planet.php?planet_id=$planet_id&command=scan>Scan</a><br/>";
                    if ($sofa_on)
                    {
                        echo "<a href=planet.php?planet_id=$planet_id&command=bom>Sub-Orbital Fighter Attack</a><br/>";
                    }
                }
            }
        }
        elseif ($command == "attack")
        {
            // Kami Multi Browser Window Attack Fix
            if (array_key_exists('planet_selected', $_SESSION) == false || $_SESSION['planet_selected'] != $planet_id)
            {
                adminlog($db, 57, "{$ip}|{$playerinfo['ship_id']}|Tried to Attack without clicking on the Planet.");
                echo "You need to Click on the planet first.<br/><br/>";
                text_GOTOMAIN ();
                include "footer.php";
                die ();
            }
            unset ($_SESSION['planet_selected']);

            $retOwnerInfo = NULL;
            $owner_found = get_planet_owner_information ($db, $planetinfo['planet_id'], $retOwnerInfo);
            if ($owner_found == true && !is_null($retOwnerInfo))
            {
                if ($retOwnerInfo['team'] == $playerinfo['team'] && ($playerinfo['team'] != 0 || $retOwnerInfo['team'] != 0))
                {
                    echo "<div style='color:#f00;'>Look we have told you, You cannot attack a Friendly Owned Private Planet!</div>\n";
                }
                else
                {
                    planetcombat ();
                }
            }
        }
        elseif ($command == "bom")
        {
            // Check to see if sure...
            if ($planetinfo['sells'] == "Y" && $sofa_on)
            {
                echo "<a href=planet.php?planet_id=$planet_id&command=buy>Buy</a> commodities from Planet<br/>";
            }
            else
            {
                echo "Planet is not presently selling commodities.<br/>";
            }

            echo "<a href=planet.php?planet_id=$planet_id&command=attac>Attack</a><br/>";
            echo "<a href=planet.php?planet_id=$planet_id&command=scan>Scan</a><br/>";
            echo "<a href=planet.php?planet_id=$planet_id&command=bomb>Sub-Orbital Fighter Attack</a>Let Them Burn<br/>";
        }
        elseif ($command == "bomb" && $sofa_on)
        {
            planetbombing ();
        }
        elseif ($command == "scan")
        {
            // Kami Multi Browser Window Attack Fix
            if (array_key_exists('planet_selected', $_SESSION) == false || $_SESSION['planet_selected'] != $planet_id)
            {
                adminlog($db, 57, "{$ip}|{$playerinfo['ship_id']}|Tried to Scan without clicking on the Planet.");
                echo "You need to Click on the planet first.<br/><br/>";
                text_GOTOMAIN ();
                include "footer.php";
                die ();
            }
            unset($_SESSION['planet_selected']);

            // Scan menu
            if ($playerinfo['turns'] < 1)
            {
                echo "You need at least one turn to scan a planet.<br/><br/>";
                text_GOTOMAIN ();
                include "footer.php";
                die ();
            }

            // Determine per cent chance of success in scanning target ship - based on player's sensors and opponent's cloak
            $success = (10 - $ownerinfo['cloak'] / 2 + $playerinfo['sensors']) * 5;
            if ($success < 5)
            {
                $success = 5;
            }
            if ($success > 95)
            {
                $success = 95;
            }

            $roll = mt_rand (1, 100);
            if ($roll > $success)
            {
                // If scan fails - inform both player and target.
                echo "Sensors cannot get a fix on target!<br/><br/>";
                text_GOTOMAIN ();
                playerlog ($db, $ownerinfo['ship_id'], LOG_PLANET_SCAN_FAIL, "$planetinfo[name]|$playerinfo[sector]|$playerinfo[character_name]");
                include "footer.php";
                die ();
            }
            else
            {
                playerlog ($db, $ownerinfo['ship_id'], LOG_PLANET_SCAN, "$planetinfo[name]|$playerinfo[sector]|$playerinfo[character_name]");
                // Scramble results by scan error factor.
                $sc_error = SCAN_ERROR ($playerinfo['sensors'], $ownerinfo['cloak']);
                if (empty ($planetinfo['name']))
                {
                    $planetinfo['name'] = "Unnamed";
                }

                echo "Scan results of ".$planetinfo['name'].", owned by:  ".$ownerinfo['character_name']."<br/><br/>";
				/*
				Build table
				*/	
				?>
				<div class="general-table-container">
				<?
                echo "<table><tbody>";
                echo "<tr><td>Commodities:</td><td></td>";
                echo "<tr><td>Organics:</td>";
                $roll = mt_rand (1, 100);
                if ($roll < $success)
                {
                    $sc_planet_organics = NUMBER (round ($planetinfo['organics'] * $sc_error / 100));
                    echo "<td>".$sc_planet_organics."</td></tr>";
                }
                else
                {
                    echo "<td>???</td></tr>";
                }

                echo "<tr><td>Ore:</td>";
                $roll = mt_rand (1, 100);
                if ($roll < $success)
                {
                    $sc_planet_ore = NUMBER (round ($planetinfo['ore'] * $sc_error / 100));
                    echo "<td>".$sc_planet_ore."</td></tr>";
                }
                else
                {
                    echo "<td>???</td></tr>";
                }

                echo "<tr><td>Goods:</td>";
                $roll = mt_rand (1, 100);
                if ($roll < $success)
                {
                    $sc_planet_goods = NUMBER (round ($planetinfo['goods'] * $sc_error / 100));
                    echo "<td>".$sc_planet_goods."</td></tr>";
                }
                else
                {
                    echo "<td>???</td></tr>";
                }
                echo "<tr><td>Energy:</td>";
                $roll = mt_rand (1, 100);
                if ($roll < $success)
                {
                    $sc_planet_energy = NUMBER (round ($planetinfo['energy'] * $sc_error / 100));
                    echo "<td>".$sc_planet_energy."</td></tr>";
                }
                else
                {
                    echo "<td>???</td></tr>";
                }
                echo "<tr><td>Colonists:</td>";
                $roll = mt_rand (1, 100);
                if ($roll < $success)
                {
                    $sc_planet_colonists = NUMBER (round ($planetinfo['colonists'] * $sc_error / 100));
                    echo "<td>".$sc_planet_colonists."</td></tr>";
                }
                else
                {
                    echo "<td>???</td></tr>";
                }
                echo "<tr><td>Credits:</td>";
                $roll = mt_rand (1, 100);
                if ($roll < $success)
                {
                    $sc_planet_credits = NUMBER (round ($planetinfo['credits'] * $sc_error / 100));
                    echo "<td>".$sc_planet_credits."</td></tr>";
                }
                else
                {
                    echo "<td>???</td></tr>";
                }
				echo "</tbody></table></div>";
				##
				## Facilities
				##
				?>
				<div class="general-table-container">
				<?
                echo "<table><tbody>";
                echo "<tr><td>Facilities:</td><td></td>";
                echo "<tr><td>Base:</td>";
                $roll = mt_rand (1, 100);
                if ($roll < $success)
                {
                    echo "<td>".$planetinfo['base']."</td></tr>";
                }
                else
                {
                    echo "<td>???</td></tr>";
                }
                echo "<tr><td>Research:</td>";
                $roll = mt_rand (1, 100);
                if ($roll < $success)
                {
                    echo "<td>".$planetinfo['facility_research']."</td></tr>";
                }
                else
                {
                    echo "<td>???</td></tr>";
                }
                echo "<tr><td>Medical:</td>";
                $roll = mt_rand (1, 100);
                if ($roll < $success)
                {
                    echo "<td>".$planetinfo['facility_medical']."</td></tr>";
                }
                else
                {
                    echo "<td>???</td></tr>";
                }
                echo "<tr><td>Banking:</td>";
                $roll = mt_rand (1, 100);
                if ($roll < $success)
                {
                    echo "<td>".$planetinfo['facility_bank']."</td></tr>";
                }
                else
                {
                    echo "<td>???</td></tr>";
                }
                echo "<tr><td>Military:</td>";
                $roll = mt_rand (1, 100);
                if ($roll < $success)
                {
                    echo "<td>".$planetinfo['facility_military']."</td></tr>";
                }
                else
                {
                    echo "<td>???</td></tr>";
                }
                echo "<tr><td>Hydroponics:</td>";
                $roll = mt_rand (1, 100);
                if ($roll < $success)
                {
                    echo "<td>".$planetinfo['facility_hydroponics']."</td></tr>";
                }
                else
                {
                    echo "<td>???</td></tr>";
                }
				echo "</tbody></table></div>";
				##
				## Defences
				##
				?>
				<div class="general-table-container">
				<?
                echo "<table><tbody>";
                echo "<tr><td>Defenses:</td><td></td>";
                echo "<tr><td>Base Torpedoes:</td>";
                $roll = mt_rand (1, 100);
                if ($roll < $success)
                {
                    $sc_base_torp = NUMBER (round ($planetinfo['torps'] * $sc_error / 100));
                    echo "<td>$sc_base_torp</td></tr>";
                }
                else
                {
                    echo "<td>???</td></tr>";
                }
                echo "<tr><td>Fighters:</td>";
                $roll = mt_rand (1, 100);
                if ($roll < $success)
                {
                    $sc_planet_fighters = NUMBER (round ($planetinfo['fighters'] * $sc_error / 100));
                    echo "<td>$sc_planet_fighters</td></tr>";
                }
                else
                {
                    echo "<td>???</td></tr>";
                }
                echo "<tr><td>Beams:</td>";
                $roll = mt_rand (1, 100);
                if ($roll < $success)
                {
                    $sc_beams = NUMBER (round ($ownerinfo['beams'] * $sc_error / 100));
                    echo "<td>$sc_beams</td></tr>";
                }
                else
                {
                    echo "<td>???</td></tr>";
                }
                echo "<tr><td>Torpedo launchers:</td>";
                $roll = mt_rand (1, 100);
                if ($roll < $success)
                {
                    $sc_torp_launchers = NUMBER (round ($ownerinfo['torp_launchers'] * $sc_error / 100));
                    echo "<td>$sc_torp_launchers</td></tr>";
                }
                else
                {
                    echo "<td>???</td></tr>";
                }
                echo "<tr><td>Shields:</td>";
                $roll = mt_rand (1, 100);
                if ($roll < $success)
                {
                    $sc_shields = NUMBER (round ($ownerinfo['shields'] * $sc_error / 100));
                    echo "<td>$sc_shields</td></tr>";
                }
                else
                {
                    echo "<td>???</td></tr>";
                }
                echo "</tbody></table></div>";

                $res = $db->Execute("SELECT * FROM {$db->prefix}ships WHERE on_planet = 'Y' and planet_id = $planet_id");
                db_op_result ($db, $res, __LINE__, __FILE__, $db_logging);

                while (!$res->EOF)
                {
                    $row = $res->fields;
                    $success = SCAN_SUCCESS ($playerinfo['sensors'], $row['cloak']);
                    if ($success < 5)
                    {
                        $success = 5;
                    }
                    if ($success > 95)
                    {
                        $success = 95;
                    }

                    $roll = mt_rand (1, 100);

                    if ($roll < $success)
                    {
                        echo $row['character_name']." is on the planet.<br/>";
                    }
                    $res->MoveNext();
                }
            }
            $update = $db->Execute("UPDATE {$db->prefix}ships SET turns=turns-1, turns_used=turns_used+1 WHERE ship_id=$playerinfo[ship_id]");
            db_op_result ($db, $update, __LINE__, __FILE__, $db_logging);
        }
        elseif ($command == "capture" &&  $planetinfo['owner'] == 0)
        {
            echo "Planet captured!<br/>";
            $update = $db->Execute("UPDATE {$db->prefix}planets SET corp=0, owner=$playerinfo[ship_id], base='N', defeated='N' WHERE planet_id=$planet_id");
            db_op_result ($db, $update, __LINE__, __FILE__, $db_logging);
            $ownership = calc_ownership ($playerinfo['sector']);

            if (!empty($ownership))
            {
                echo "$ownership<p>";
            }

            if ($planetinfo['owner'] != 0)
            {
                gen_score( $planetinfo['owner'] );
            }

            if ($planetinfo['owner'] != 0)
            {
                $res = $db->Execute("SELECT character_name FROM {$db->prefix}ships WHERE ship_id=$planetinfo[owner]");
                db_op_result ($db, $res, __LINE__, __FILE__, $db_logging);
                $query = $res->fields;
                $planetowner = $query['character_name'];
            }
            else
            {
                $planetowner = "nobody";
            }

            playerlog ($db, $playerinfo['ship_id'], LOG_PLANET_CAPTURED, "$planetinfo[colonists]|$planetinfo[credits]|$planetowner");
        }
        elseif ($command == "capture" &&  ($planetinfo['owner'] == 0 || $planetinfo['defeated'] == 'Y'))
        {
            echo "Planet not defeated!<br/>";
            $resx = $db->Execute("UPDATE {$db->prefix}planets SET defeated='N' WHERE planet_id=$planetinfo[planet_id]");
            db_op_result ($db, $resx, __LINE__, __FILE__, $db_logging);
        }
        else
        {
            echo "Command not available.<br/>";
        }
    }
}
else
{
    echo "You are not in the same sector as this planet, so you can't interact with it.";
}

if ($command != "")
{
    echo "<br/><a href=planet.php?planet_id=$planet_id>Click Here</a> to go to the planet menu.<br/><br/>";
}

if ($allow_ibank)
{
  echo "Access the planet's <a href=\"igb.php?planet_id=$planet_id\">IGB Bank</A>.<br/><br/>";
}
echo "<a href =\"bounty.php\">Place Or View Bounties</A><p>";

text_GOTOMAIN ();
?>
</div></div>
<?
include "footer.php";
?>

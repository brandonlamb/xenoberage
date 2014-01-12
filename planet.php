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

// New database driven language entries
load_languages($db, $lang, array('bounty', 'port', 'main', 'planet', 'report', 'common', 'global_includes', 'global_funcs', 'footer', 'news', 'combat'), $langvars, $db_logging);

$title = $l_planet_title;
include "header.php";

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
$result = $db->Execute("SELECT * FROM {$db->prefix}ships WHERE email=?;", array($username));
db_op_result ($db, $result, __LINE__, __FILE__, $db_logging);
$playerinfo = $result->fields;

// Empty out Planet and Ship vars
$planetinfo = null;

// Check if planet_id is valid.
if ($planet_id <= 0 )
{
    echo "Invalid Planet<br><br>";
    text_GOTOMAIN ();
    include "footer.php";
    die ();
}

$result2 = $db->Execute("SELECT * FROM {$db->prefix}universe WHERE sector_id=?;", array($playerinfo['sector']));
db_op_result ($db, $result2, __LINE__, __FILE__, $db_logging);
$sectorinfo = $result2->fields;

$result3 = $db->Execute("SELECT * FROM {$db->prefix}planets WHERE planet_id=?;", array($planet_id));
db_op_result ($db, $result3, __LINE__, __FILE__, $db_logging);
$planetinfo = $result3->fields;

// Check to see if it returned valid planet info.
if (!$result3 instanceof ADORecordSet || (is_bool($planetinfo) && $planetinfo == false))
{
  echo "Invalid Planet<br><br>";
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
            $resx = $db->Execute("UPDATE {$db->prefix}ships SET on_planet='N' WHERE ship_id=$playerinfo[ship_id]");
            db_op_result ($db, $resx, __LINE__, __FILE__, $db_logging);
        }

        echo "$l_planet_none <p>";
        text_GOTOMAIN ();
        include "footer.php";
        die ();
    }

    if (($planetinfo['owner'] == 0  || $planetinfo['defeated'] == 'Y') && $command != "capture")
    {
        if ($planetinfo['owner'] == 0)
        {
            echo "$l_planet_unowned.<br><br>";
        }
        $capture_link = "<a href=planet.php?planet_id=$planet_id&command=capture>$l_planet_capture1</a>";
        $l_planet_capture2 = str_replace ("[capture]", $capture_link, $l_planet_capture2);
        echo "$l_planet_capture2.<br><br>";
        echo "<br>";
        text_GOTOMAIN ();
        include "footer.php";
        die ();
    }

    if ($planetinfo['owner'] != 0)
    {
        $result3 = $db->Execute("SELECT * FROM {$db->prefix}ships WHERE ship_id=$planetinfo[owner]");
        db_op_result ($db, $result3, __LINE__, __FILE__, $db_logging);
        $ownerinfo = $result3->fields;
    }

    if (empty($command))
    {
        // Kami Multi Browser Window Attack Fix
        $_SESSION['planet_selected'] = $planet_id;

        // If there is no planet command already
        if (empty ($planetinfo['name']) )
        {
            $l_planet_unnamed = str_replace ("[name]", $ownerinfo['character_name'], $l_planet_unnamed);
            echo "$l_planet_unnamed<br><br>";
        }
        else
        {
            $l_planet_named = str_replace ("[name]", $ownerinfo['character_name'], $l_planet_named);
            $l_planet_named = str_replace ("[planetname]", $planetinfo['name'], $l_planet_named);
            echo "$l_planet_named<br><br>";
        }

        if ($playerinfo['ship_id'] == $planetinfo['owner'])
        {
            if ($destroy == 1 && $allow_genesis_destroy)
            {
                echo "<font color=red>$l_planet_confirm</font><br><a href=planet.php?planet_id=$planet_id&destroy=2>yes</A><br>";
                echo "<a href=planet.php?planet_id=$planet_id>no!</A><br><br>";
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
                    echo "$l_gns_nogenesis<br>";
                }
            }
            elseif ($allow_genesis_destroy)
            {
                echo "<A onclick=\"javascript: alert ('alert:$l_planet_warning');\" href=planet.php?planet_id=$planet_id&destroy=1>$l_planet_destroyplanet</a><br>";
            }
        }

        if ($planetinfo['owner'] == $playerinfo['ship_id'] || ($planetinfo['corp'] == $playerinfo['team'] && $playerinfo['team'] > 0))
        {
            // Owner menu
            echo "$l_turns_have $playerinfo[turns]<p>";

            $l_planet_name_link = "<a href=planet.php?planet_id=$planet_id&command=name>" . $l_planet_name_link . "</a>";
            $l_planet_name = str_replace ("[name]", $l_planet_name_link, $l_planet_name2);

            echo "$l_planet_name<br>";

            $l_planet_leave_link = "<a href=planet.php?planet_id=$planet_id&command=leave>" . $l_planet_leave_link . "</a>";
            $l_planet_leave= str_replace ("[leave]", $l_planet_leave_link, $l_planet_leave);

            $l_planet_land_link = "<a href=planet.php?planet_id=$planet_id&command=land>" . $l_planet_land_link . "</a>";
            $l_planet_land= str_replace ("[land]", $l_planet_land_link, $l_planet_land);

            if ($playerinfo['on_planet'] == 'Y' && $playerinfo['planet_id'] == $planet_id)
            {
                echo "$l_planet_onsurface<br>";
                echo "$l_planet_leave<br>";
                $l_planet_logout = str_replace ("[logout]", "<a href='logout.php'>" . $l_logout . "</a>", $l_planet_logout);
                echo "$l_planet_logout<br>";
            }
            else
            {
                echo "$l_planet_orbit<br>";
                echo "$l_planet_land<br>";
            }

            $l_planet_transfer_link = "<a href=planet.php?planet_id=$planet_id&command=transfer>" . $l_planet_transfer_link . "</a>";
            $l_planet_transfer = str_replace ("[transfer]", $l_planet_transfer_link, $l_planet_transfer);
            echo "$l_planet_transfer<br>";
            if ($planetinfo['sells'] == "Y")
            {
                echo $l_planet_selling;
            }
            else
            {
                echo $l_planet_not_selling;
            }

            $l_planet_tsell_link = "<a href=planet.php?planet_id=$planet_id&command=sell>" . $l_planet_tsell_link ."</a>";
            $l_planet_tsell = str_replace ("[selling]", $l_planet_tsell_link, $l_planet_tsell);
            echo "$l_planet_tsell<br>";
            if ($planetinfo['base'] == "N")
            {
                $l_planet_bbase_link = "<a href=planet.php?planet_id=$planet_id&command=base>" . $l_planet_bbase_link . "</a>";
                $l_planet_bbase = str_replace ("[build]", $l_planet_bbase_link, $l_planet_bbase);
                echo "$l_planet_bbase<br>";
            }
            else
            {
                echo "$l_planet_hasbase<br>";
            }

            $l_planet_readlog_link = "<a href=log.php>" . $l_planet_readlog_link ."</a>";
            $l_planet_readlog = str_replace ("[View]", $l_planet_readlog_link, $l_planet_readlog);
            echo "<br>$l_planet_readlog<br>";

            if ($playerinfo['ship_id'] == $planetinfo['owner'])
            {
                if ($playerinfo['team'] != 0)
                {
                    if ($planetinfo['corp'] == 0)
                    {
                        $l_planet_mcorp_linkC = "<a href=corp.php?planet_id=$planet_id&action=planetcorp>" . $l_planet_mcorp_linkC . "</a>";
                        $l_planet_mcorp = str_replace ("[planet]", $l_planet_mcorp_linkC, $l_planet_mcorp);
                        echo "$l_planet_mcorp<br>";
                    }
                    else
                    {
                        $l_planet_mcorp_linkP = "<a href=corp.php?planet_id=$planet_id&action=planetpersonal>" . $l_planet_mcorp_linkP . "</a>";
                        $l_planet_mcorp = str_replace ("[planet]", $l_planet_mcorp_linkP, $l_planet_mcorp);
                        echo "$l_planet_mcorp<br>";
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
            echo "</tbody></table></div>$l_planet_interest<br><br>";
            echo "<input type=submit value=$l_planet_update>";
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
                $l_planet_buy_link = "<a href=planet.php?planet_id=$planet_id&command=buy>" . $l_planet_buy_link ."</a>";
                $l_planet_buy = str_replace ("[buy]", $l_planet_buy_link, $l_planet_buy);
                echo "$l_planet_buy<br>";
            }
            else
            {
                echo "$l_planet_not_selling.<br>";
            }

            // Fix for corp member leaving a non corp planet
            if (($planetinfo['planet_id'] == $playerinfo['planet_id'] && $playerinfo['on_planet'] == "Y") && $planetinfo['corp'] == 0)
            {
                $l_planet_leave_link = "<a href=planet.php?planet_id=$planet_id&command=leave>Leave Friendly Planet</a>";
                echo "<p>$l_planet_leave_link</p>\n";
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
                    $l_planet_att_link = "<a href=planet.php?planet_id=$planet_id&command=attac>" . $l_planet_att_link ."</a>";
                    $l_planet_att = str_replace ("[attack]", $l_planet_att_link, $l_planet_att);
                    $l_planet_scn_link = "<a href=planet.php?planet_id=$planet_id&command=scan>" . $l_planet_scn_link ."</a>";
                    $l_planet_scn = str_replace ("[scan]", $l_planet_scn_link, $l_planet_scn);
                    echo "$l_planet_att<br>";
                    echo "$l_planet_scn<br>";
                    if ($sofa_on)
                    {
                        echo "<a href=planet.php?planet_id=$planet_id&command=bom>$l_sofa</a><br>";
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
                echo "$l_planet_nownosell<br>";
                $result4 = $db->Execute("UPDATE {$db->prefix}planets SET sells='N' WHERE planet_id=$planet_id");
                db_op_result ($db, $result4, __LINE__, __FILE__, $db_logging);
            }
            else
            {
                echo "$l_planet_nowsell<br>";
                $result4b = $db->Execute ("UPDATE {$db->prefix}planets SET sells='Y' WHERE planet_id=$planet_id");
                db_op_result ($db, $result4b, __LINE__, __FILE__, $db_logging);
            }
        }
        elseif ($command == "name")
        {
            // Name menu
            echo "<form action=\"planet.php?planet_id=$planet_id&command=cname\" method=\"post\">";
            echo "$l_planet_iname:  ";
            echo "<input type=\"text\" name=\"new_name\" size=\"20\" maxlength=\"20\" value=\"$planetinfo[name]\"><br><br>";
            echo "<input type=\"submit\" value=\"$l_submit\"><input type=\"reset\" value=\"$l_reset\"><br><br>";
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
            echo "$l_planet_cname $new_name.";
        }
        elseif ($command == "land")
        {
            // Land menu
            echo "$l_planet_landed<br><br>";
            $update = $db->Execute("UPDATE {$db->prefix}ships SET on_planet='Y', planet_id=$planet_id WHERE ship_id=$playerinfo[ship_id]");
            db_op_result ($db, $update, __LINE__, __FILE__, $db_logging);
        }
        elseif ($command == "leave")
        {
            // Leave menu
            echo "$l_planet_left<br><br>";
            $update = $db->Execute("UPDATE {$db->prefix}ships SET on_planet='N' WHERE ship_id=$playerinfo[ship_id]");
            db_op_result ($db, $update, __LINE__, __FILE__, $db_logging);
        }
        elseif ($command == "transfer")
        {
            // Transfer menu
            global $l_planet;
            $free_holds = NUM_HOLDS ($playerinfo['hull']) - $playerinfo['ship_ore'] - $playerinfo['ship_organics'] - $playerinfo['ship_goods'] - $playerinfo['ship_colonists'];
            $free_power = NUM_ENERGY ($playerinfo['power']) - $playerinfo['ship_energy'];
            $l_planet_cinfo = str_replace ("[cargo]", NUMBER ($free_holds), $l_planet_cinfo);
            $l_planet_cinfo = str_replace ("[energy]", NUMBER ($free_power), $l_planet_cinfo);
            echo "$l_planet_cinfo<br><br>";
            echo "<form action=planet2.php?planet_id=$planet_id method=post>";
			/*
			Build table
			*/	
			?>
			<div class="general-table-container">
			<?
            echo "<table><tbody>";
            echo "<tr><td>$l_commodity</td><td>$l_planet</td><td>$l_ship</td><td>$l_planet_transfer_link</td><td>$l_planet_toplanet</td><td>$l_all?</td></tr>";
			
            echo "<tr><td>$l_ore</td><td>" . NUMBER ($planetinfo['ore']) . "</td><td>" . NUMBER ($playerinfo['ship_ore']) . "</td><td><input type=text name=transfer_ore size=10 value='0'  maxlength=20></td><td><div class='checkbox-square'><input type=CHECKBOX id='tpore' name=tpore value=-1><label for='tpore'></label></div></td><td><div class='checkbox-square'><input type=CHECKBOX id='allore' name=allore value=-1><label for='allore'></label></div></td></tr>";
			
            echo "<tr><td>$l_organics</td><td>" . NUMBER ($planetinfo['organics']) . "</td><td>" . NUMBER ($playerinfo['ship_organics']) . "</td><td><input type=text name=transfer_organics size=10 value='0'  maxlength=20></td><td><div class='checkbox-square'><input type=CHECKBOX id='tporganics' name=tporganics value=-1><label for='tporganics'></label></div></td><td><div class='checkbox-square'><input type=CHECKBOX id='allorganics' name=allorganics value=-1><label for='allorganics'></label></div></td></tr>";
			
            echo "<tr><td>$l_goods</td><td>" . NUMBER ($planetinfo['goods']) . "</td><td>" . NUMBER ($playerinfo['ship_goods']) . "</td><td><input type=text name=transfer_goods size=10 value='0'  maxlength=20></td><td><div class='checkbox-square'><input type=CHECKBOX id='tpgoods' name=tpgoods value=-1><label for='tpgoods'></label></div></td><td><div class='checkbox-square'><input type=CHECKBOX id='allgoods' name=allgoods value=-1><label for='allgoods'></label></div></td></tr>";
			
            echo "<tr><td>$l_energy</td><td>" . NUMBER ($planetinfo['energy']) . "</td><td>" . NUMBER ($playerinfo['ship_energy']) . "</td><td><input type=text name=transfer_energy size=10 value='0'  maxlength=20></td><td><div class='checkbox-square'><input type=CHECKBOX id='tpenergy' name=tpenergy value=-1><label for='tpenergy'></label></div></td><td><div class='checkbox-square'><input type=CHECKBOX id='allenergy' name=allenergy value=-1><label for='allenergy'></label></div></td></tr>";
			
            echo "<tr><td>$l_colonists</td><td>" . NUMBER ($planetinfo['colonists']) . "</td><td>" . NUMBER ($playerinfo['ship_colonists']) . "</td><td><input type=text name=transfer_colonists size=10 value='0'  maxlength=20></td><td><div class='checkbox-square'><input type=CHECKBOX id='tpcolonists' name=tpcolonists value=-1><label for='tpcolonists'></label></div></td><td><div class='checkbox-square'><input type=CHECKBOX id='allcolonists' name=allcolonists value=-1><label for='allcolonists'></label></div></td></tr>";
			
            echo "<tr><td>$l_fighters</td><td>" . NUMBER ($planetinfo['fighters']) . "</td><td>" . NUMBER ($playerinfo['ship_fighters']) . "</td><td><input type=text name=transfer_fighters size=10 value='0'  maxlength=20></td><td><div class='checkbox-square'><input type=CHECKBOX id='tpfighters' name=tpfighters value=-1><label for='tpfighters'></label></div></td><td><div class='checkbox-square'><input type=CHECKBOX id='allfighters' name=allfighters value=-1><label for='allfighters'></label></div></td></tr>";
			
            echo "<tr><td>$l_torps</td><td>" . NUMBER ($planetinfo['torps']) . "</td><td>" . NUMBER ($playerinfo['torps']) . "</td><td><input type=text name=transfer_torps size=10 value='0'  maxlength=20></td><td><div class='checkbox-square'><input type=CHECKBOX id='tptorps' name=tptorps value=-1><label for='tptorps'></label></div></td><td><div class='checkbox-square'><input type=CHECKBOX id='alltorps' name=alltorps value=-1><label for='alltorps'></label></div></td></tr>";
			
            echo "<tr><td>$l_credits</td><td><span class=\"table_word_yellow\">" . NUMBER ($planetinfo['credits']) . "</span></td><td>" . NUMBER ($playerinfo['credits']) . "</td><td><input type=text name=transfer_credits size=10 value='0' maxlength=20></td><td><div class='checkbox-square'><input type=CHECKBOX id='tpcredits' name=tpcredits value=-1><label for='tpcredits'></label></div></td><td><div class='checkbox-square'><input type=CHECKBOX id='allcredits' name=allcredits value=-1><label for='allcredits'></label></div></td></tr>";
            echo "</tbody></table></div>";
            echo "<input type=submit value=$l_planet_transfer_link>&nbsp;<input type=RESET value=Reset>";
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
                echo "You need to Click on the planet first.<br><br>";
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
                    echo "$l_igb_notenturns";
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
                    echo "$l_planet_bbuild<br><br>";

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
                $l_planet_baseinfo = str_replace ("[base_credits]", $base_credits, $l_planet_baseinfo);
                $l_planet_baseinfo = str_replace ("[base_ore]", $base_ore, $l_planet_baseinfo);
                $l_planet_baseinfo = str_replace ("[base_organics]", $base_organics, $l_planet_baseinfo);
                $l_planet_baseinfo = str_replace ("[base_goods]", $base_goods, $l_planet_baseinfo);
                echo "$l_planet_baseinfo<br><br>";
            }
        }
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
                echo "$l_planet_p_under<br><br>";
            }
            elseif (($porganics + $pore + $pgoods + $penergy + $pfighters + $ptorp) > 100.0)
            {
                echo "$l_planet_p_over<br><br>";
            }
            else
            {
                $resx = $db->Execute("UPDATE {$db->prefix}planets SET prod_ore=$pore,prod_organics=$porganics,prod_goods=$pgoods,prod_energy=$penergy,prod_fighters=$pfighters,prod_torp=$ptorp WHERE planet_id=$planet_id");
                db_op_result ($db, $resx, __LINE__, __FILE__, $db_logging);
                echo "$l_planet_p_changed<br><br>";
            }
        }
        else
        {
            echo "$l_command_no<br>";
        }
    }
    elseif (($planetinfo['planet_id'] == $playerinfo['planet_id'] && $playerinfo['on_planet'] == "Y") && $planetinfo['corp'] == 0) // Fix for corp member leaving a non corp planet
    {
        if ($command == "leave")
        {
            // Leave menu
            echo "$l_planet_left<br><br>";
            $update = $db->Execute("UPDATE {$db->prefix}ships SET on_planet = 'N', planet_id = 0 WHERE ship_id=$playerinfo[ship_id]");
            db_op_result ($db, $update, __LINE__, __FILE__, $db_logging);
            $l_global_mmenu = str_replace ("[here]","<a href='main.php'>" . $l_here . "</a>", $l_global_mmenu);
            echo $l_global_mmenu . "<br>\n";
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
                echo "<tr><td>$l_commodity</td><td>$l_avail</td><td>$l_price</td><td>$l_buy</td><td>$l_cargo</td></tr>";
                echo "<tr><td>$l_ore</td><td>$planetinfo[ore]</td><td>$ore_price</td><td><input type=text name=trade_ore size=10 maxlength=20 value=0></td><td>$playerinfo[ship_ore]</td></tr>";
                echo "<tr><td>$l_organics</td><td>$planetinfo[organics]</td><td>$organics_price</td><td><input type=text name=trade_organics size=10 maxlength=20 value=0></td><td>$playerinfo[ship_organics]</td></tr>";
                echo "<tr><td>$l_goods</td><td>$planetinfo[goods]</td><td>$goods_price</td><td><input type=text name=trade_goods size=10 maxlength=20 value=0></td><td>$playerinfo[ship_goods]</td></tr>";
                echo "<tr><td>$l_energy</td><td>$planetinfo[energy]</td><td>$energy_price</td><td><input type=text name=trade_energy size=10 maxlength=20 value=0></td><td>$playerinfo[ship_energy]</td></tr>";
                echo "</tbody></table></div>";
                echo "<input type=submit value=$l_submit><input type=reset value=$l_reset><br></form>";
            }
            else
            {
                echo "$l_planet_not_selling<br>";
            }
        }
        elseif ($command == "attac")
        {
            // Kami Multi Browser Window Attack Fix
            if (array_key_exists('planet_selected', $_SESSION) == false || $_SESSION['planet_selected'] != $planet_id)
            {
                adminlog($db, 57, "{$ip}|{$playerinfo['ship_id']}|Tried to start an attack without clicking on the Planet.");
                echo "You need to Click on the planet first.<br><br>";
                text_GOTOMAIN ();
                include "footer.php";
                die ();
            }

            // Check to see if sure
            if ($planetinfo['sells'] == "Y")
            {
                $l_planet_buy_link = "<a href=planet.php?planet_id=$planet_id&command=buy>" . $l_planet_buy_link ."</a>";
                $l_planet_buy = str_replace ("[buy]", $l_planet_buy_link, $l_planet_buy);
                echo "$l_planet_buy<br>";
            }
            else
            {
                echo "$l_planet_not_selling<br>";
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
                    $l_planet_att_link = "<a href=planet.php?planet_id=$planet_id&command=attack>" . $l_planet_att_link ."</a>";
                    $l_planet_att = str_replace ("[attack]", $l_planet_att_link, $l_planet_att);
                    $l_planet_scn_link = "<a href=planet.php?planet_id=$planet_id&command=scan>" . $l_planet_scn_link ."</a>";
                    $l_planet_scn = str_replace ("[scan]", $l_planet_scn_link, $l_planet_scn);
                    echo "$l_planet_att $l_planet_att_sure<br>";
                    echo "$l_planet_scn<br>";
                    if ($sofa_on)
                    {
                        echo "<a href=planet.php?planet_id=$planet_id&command=bom>$l_sofa</a><br>";
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
                echo "You need to Click on the planet first.<br><br>";
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
                $l_planet_buy_link = "<a href=planet.php?planet_id=$planet_id&command=buy>" . $l_planet_buy_link ."</a>";
                $l_planet_buy = str_replace ("[buy]", $l_planet_buy_link, $l_planet_buy);
                echo "$l_planet_buy<br>";
            }
            else
            {
                echo "$l_planet_not_selling<br>";
            }

            $l_planet_att_link = "<a href=planet.php?planet_id=$planet_id&command=attac>" . $l_planet_att_link ."</a>";
            $l_planet_att = str_replace ("[attack]", $l_planet_att_link, $l_planet_att);
            $l_planet_scn_link="<a href=planet.php?planet_id=$planet_id&command=scan>" . $l_planet_scn_link ."</a>";
            $l_planet_scn = str_replace ("[scan]", $l_planet_scn_link, $l_planet_scn);
            echo "$l_planet_att<br>";
            echo "$l_planet_scn<br>";
            echo "<a href=planet.php?planet_id=$planet_id&command=bomb>$l_sofa</a>$l_planet_att_sure<br>";
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
                echo "You need to Click on the planet first.<br><br>";
                text_GOTOMAIN ();
                include "footer.php";
                die ();
            }
            unset($_SESSION['planet_selected']);

            // Scan menu
            if ($playerinfo['turns'] < 1)
            {
                echo "$l_plant_scn_turn<br><br>";
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
                echo "$l_planet_noscan<br><br>";
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
                    $planetinfo['name'] = $l_unnamed;
                }

                $l_planet_scn_report = str_replace ("[name]", $planetinfo['name'], $l_planet_scn_report);
                $l_planet_scn_report = str_replace ("[owner]", $ownerinfo['character_name'], $l_planet_scn_report);
                echo "$l_planet_scn_report<br><br>";
				/*
				Build table
				*/	
				?>
				<div class="general-table-container">
				<?
                echo "<table><tbody>";
                echo "<tr><td>$l_commodities:</td><td></td>";
                echo "<tr><td>$l_organics:</td>";
                $roll = mt_rand (1, 100);
                if ($roll < $success)
                {
                    $sc_planet_organics = NUMBER (round ($planetinfo['organics'] * $sc_error / 100));
                    echo "<td>$sc_planet_organics</td></tr>";
                }
                else
                {
                    echo "<td>???</td></tr>";
                }

                echo "<tr><td>$l_ore:</td>";
                $roll = mt_rand (1, 100);
                if ($roll < $success)
                {
                    $sc_planet_ore = NUMBER (round ($planetinfo['ore'] * $sc_error / 100));
                    echo "<td>$sc_planet_ore</td></tr>";
                }
                else
                {
                    echo "<td>???</td></tr>";
                }

                echo "<tr><td>$l_goods:</td>";
                $roll = mt_rand (1, 100);
                if ($roll < $success)
                {
                    $sc_planet_goods = NUMBER (round ($planetinfo['goods'] * $sc_error / 100));
                    echo "<td>$sc_planet_goods</td></tr>";
                }
                else
                {
                    echo "<td>???</td></tr>";
                }
                echo "<tr><td>$l_energy:</td>";
                $roll = mt_rand (1, 100);
                if ($roll < $success)
                {
                    $sc_planet_energy = NUMBER (round ($planetinfo['energy'] * $sc_error / 100));
                    echo "<td>$sc_planet_energy</td></tr>";
                }
                else
                {
                    echo "<td>???</td></tr>";
                }
                echo "<tr><td>$l_colonists:</td>";
                $roll = mt_rand (1, 100);
                if ($roll < $success)
                {
                    $sc_planet_colonists = NUMBER (round ($planetinfo['colonists'] * $sc_error / 100));
                    echo "<td>$sc_planet_colonists</td></tr>";
                }
                else
                {
                    echo "<td>???</td></tr>";
                }
                echo "<tr><td>$l_credits:</td>";
                $roll = mt_rand (1, 100);
                if ($roll < $success)
                {
                    $sc_planet_credits = NUMBER (round ($planetinfo['credits'] * $sc_error / 100));
                    echo "<td>$sc_planet_credits</td></tr>";
                }
                else
                {
                    echo "<td>???</td></tr>";
                }

                echo "<tr><td>$l_defense:</td><td></td>";
                echo "<tr><td>$l_base:</td>";
                $roll = mt_rand (1, 100);
                if ($roll < $success)
                {
                    echo "<td>$planetinfo[base]</td></tr>";
                }
                else
                {
                    echo "<td>???</td></tr>";
                }

                echo "<tr><td>$l_base $l_torps:</td>";
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
                echo "<tr><td>$l_fighters:</td>";
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
                echo "<tr><td>$l_beams:</td>";
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
                echo "<tr><td>$l_torp_launch:</td>";
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
                echo "<tr><td>$l_shields</td>";
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
//            $roll=mt_rand (1, 100);
//            if ($ownerinfo[sector] == $playerinfo[sector] && $ownerinfo[on_planet] == 'Y' && $roll < $success)
//            {
//               echo "$ownerinfo[character_name] $l_planet_ison<br>";
//            }

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
                        echo "$row[character_name] $l_planet_ison<br>";
                    }
                    $res->MoveNext();
                }
            }
            $update = $db->Execute("UPDATE {$db->prefix}ships SET turns=turns-1, turns_used=turns_used+1 WHERE ship_id=$playerinfo[ship_id]");
            db_op_result ($db, $update, __LINE__, __FILE__, $db_logging);
        }
        elseif ($command == "capture" &&  $planetinfo['owner'] == 0)
        {
            echo "$l_planet_captured<br>";
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
                $planetowner = "$l_planet_noone";
            }

            playerlog ($db, $playerinfo['ship_id'], LOG_PLANET_CAPTURED, "$planetinfo[colonists]|$planetinfo[credits]|$planetowner");
        }
        elseif ($command == "capture" &&  ($planetinfo['owner'] == 0 || $planetinfo['defeated'] == 'Y'))
        {
            echo "$l_planet_notdef<br>";
            $resx = $db->Execute("UPDATE {$db->prefix}planets SET defeated='N' WHERE planet_id=$planetinfo[planet_id]");
            db_op_result ($db, $resx, __LINE__, __FILE__, $db_logging);
        }
        else
        {
            echo "$l_command_no<br>";
        }
    }
}
else
{
    echo "$l_planet_none<p>";
}

if ($command != "")
{
    echo "<br><a href=planet.php?planet_id=$planet_id>$l_clickme</a> $l_toplanetmenu<br><br>";
}

if ($allow_ibank)
{
  echo "$l_ifyouneedplan <a href=\"igb.php?planet_id=$planet_id\">$l_igb_term</A>.<br><br>";
}
echo "<a href =\"bounty.php\">$l_by_placebounty</A><p>";

text_GOTOMAIN ();
?>
</div></div>
<?
include "footer.php";
?>

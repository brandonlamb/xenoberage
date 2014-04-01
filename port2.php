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
// File: port2.php

include "config/config.php";
updatecookie();

// New database driven language entries
load_languages($db, $lang, array('port', 'device', 'report', 'common', 'global_includes', 'global_funcs', 'footer', 'news'), $langvars, $db_logging);

include_once "includes/is_loan_pending.php";

$title = $l_title_port;
include "header.php";

if (checklogin () )
{
    die();
}
?>
<div class="tablecell content both-border">
	<div class="pad">
<?
$result = $db->Execute("SELECT * FROM {$db->prefix}ships WHERE ship_id='$user_ship_id'");
db_op_result ($db, $result, __LINE__, __FILE__, $db_logging);
$playerinfo = $result->fields;

$result2 = $db->Execute("SELECT * FROM {$db->prefix}universe WHERE sector_id='$playerinfo[sector]'");
db_op_result ($db, $result2, __LINE__, __FILE__, $db_logging);
$sectorinfo = $result2->fields;

$res = $db->Execute("SELECT * FROM {$db->prefix}zones WHERE zone_id=$sectorinfo[zone_id]");
db_op_result ($db, $res, __LINE__, __FILE__, $db_logging);
$zoneinfo = $res->fields;

if ($zoneinfo['allow_trade'] == 'N')
{
    $title = $l_no_trade;
    bigtitle ();
    echo $l_no_trade_info . "<p>";
    TEXT_GOTOMAIN();
    include "footer.php";
    die();
}
elseif ($zoneinfo['allow_trade'] == 'L')
{
    if ($zoneinfo['corp_zone'] == 'N')
    {
        $res = $db->Execute("SELECT team FROM {$db->prefix}ships WHERE ship_id=$zoneinfo[owner]");
        db_op_result ($db, $res, __LINE__, __FILE__, $db_logging);
        $ownerinfo = $res->fields;

        if ($playerinfo['ship_id'] != $zoneinfo['owner'] && $playerinfo['team'] == 0 || $playerinfo['team'] != $ownerinfo['team'])
        {
            $title = $l_no_trade;
            bigtitle ();
            echo $l_no_trade_out . "<p>";
            TEXT_GOTOMAIN ();
            include "footer.php";
            die ();
        }
    }
    else
    {
        if ($playerinfo['team'] != $zoneinfo['owner'])
        {
            $title = $l_no_trade;
            bigtitle ();
            echo $l_no_trade_out . "<p>";
            TEXT_GOTOMAIN ();
            include "footer.php";
            die ();
        }
    }
}

bigtitle ();

$color_red = "red";
$color_green = "#0f0"; // Light green
$trade_deficit = "$l_cost : ";
$trade_benefit = "$l_profit : ";

function BuildOneCol ( $text = "&nbsp;", $align = "left" )
{
    echo"
    <tr>
      <td colspan=99 align=".$align.">".$text.".</td>
    </tr>
    ";
}

function BuildTwoCol ( $text_col1 = "&nbsp;", $text_col2 = "&nbsp;", $align_col1 = "left", $align_col2 = "left" )
{
    echo"
    <tr>
      <td align=".$align_col1.">".$text_col1."</td>
      <td align=".$align_col2.">".$text_col2."</td>
    </tr>";
}


function phpTrueDelta ($futurevalue, $shipvalue)
{
    $tempval = $futurevalue - $shipvalue;
    return $tempval;
}

function phpChangeDelta ($desiredvalue, $currentvalue)
{
    global $upgrade_cost;

    $Delta = 0;
    $DeltaCost = 0;
    $Delta = $desiredvalue - $currentvalue;

    while ($Delta > 0 )
    {
        $DeltaCost = $DeltaCost + pow (2, $desiredvalue - $Delta);
        $Delta = $Delta - 1;
    }

    $DeltaCost = $DeltaCost * $upgrade_cost;
    return $DeltaCost;
}

if ($playerinfo['turns'] < 1 )
{
    echo $l_trade_turnneed . "<br><br>";
}
else
{
    if ($sectorinfo['port_type'] == "special")
    {
        // Kami multi-browser window upgrade fix
        if (array_key_exists('port_shopping', $_SESSION) == false || $_SESSION['port_shopping'] != true)
        {
            adminlog ($db, 57, "{$ip}|{$playerinfo['ship_id']}|Tried to re-upgrade their ship without requesting new items.");
            echo "<META HTTP-EQUIV='Refresh' CONTENT='2; URL=main.php'>";
            echo "<div style='color:#f00; font-size:18px;'>Your last Sales Transaction has already been delivered, Please enter the Special Port and select your order.</div>\n";
            echo "<br>\n";
            echo "<div style='color:#fff; font-size:12px;'>Auto redirecting in 2 seconds.</div>\n";
            echo "<br>\n";

            TEXT_GOTOMAIN ();
            include "footer.php";
            die();
        }
        unset ($_SESSION['port_shopping']);

        if (is_loan_pending ($db, $playerinfo['ship_id']))
        {
            echo $l_port_loannotrade . "<p>";
            echo "<a href=igb.php>" . $l_igb_term . "</a><p>";
            TEXT_GOTOMAIN ();
            include "footer.php";
            die ();
        }

        // Clear variables that are not selected in the form
        if (!isset($_POST['hull_upgrade']))
        {
            $hull_upgrade = null;
        }
        else
        {
            $hull_upgrade = $_POST['hull_upgrade'];
        }

        if (!isset($_POST['engine_upgrade']))
        {
            $engine_upgrade = null;
        }
        else
        {
            $engine_upgrade = $_POST['engine_upgrade'];
        }

        if (!isset($_POST['power_upgrade']))
        {
            $power_upgrade = null;
        }
        else
        {
            $power_upgrade = $_POST['power_upgrade'];
        }

        if (!isset($_POST['computer_upgrade']))
        {
            $computer_upgrade = null;
        }
        else
        {
            $computer_upgrade = $_POST['computer_upgrade'];
        }

        if (!isset($_POST['sensors_upgrade']))
        {
            $sensors_upgrade = null;
        }
        else
        {
            $sensors_upgrade = $_POST['sensors_upgrade'];
        }

        if (!isset($_POST['beams_upgrade']))
        {
            $beams_upgrade = null;
        }
        else
        {
            $beams_upgrade = $_POST['beams_upgrade'];
        }

        if (!isset($_POST['armor_upgrade']))
        {
            $armor_upgrade = null;
        }
        else
        {
            $armor_upgrade = $_POST['armor_upgrade'];
        }

        if (!isset($_POST['cloak_upgrade']))
        {
            $cloak_upgrade = null;
        }
        else
        {
            $cloak_upgrade = $_POST['cloak_upgrade'];
        }

        if (!isset($_POST['torp_launchers_upgrade']))
        {
            $torp_launchers_upgrade = null;
        }
        else
        {
            $torp_launchers_upgrade = $_POST['torp_launchers_upgrade'];
        }

        if (!isset($_POST['shields_upgrade']))
        {
            $shields_upgrade = null;
        }
        else
        {
            $shields_upgrade = $_POST['shields_upgrade'];
        }

        if (!isset($_POST['fighter_number']))
        {
            $fighter_number = null;
        }
        else
        {
            $fighter_number = $_POST['fighter_number'];
        }

        if (!isset($_POST['torpedo_number']))
        {
            $torpedo_number = null;
        }
        else
        {
            $torpedo_number = $_POST['torpedo_number'];
        }

        if (!isset($_POST['armor_number']))
        {
            $armor_number = null;
        }
        else
        {
            $armor_number = $_POST['armor_number'];
        }

        if (!isset($_POST['colonist_number']))
        {
            $colonist_number = null;
        }
        else
        {
            $colonist_number = $_POST['colonist_number'];
        }

        if (!isset($_POST['dev_genesis_number']))
        {
            $dev_genesis_number = null;
        }
        else
        {
            $dev_genesis_number = $_POST['dev_genesis_number'];
        }

        if (!isset($_POST['dev_beacon_number']))
        {
            $dev_beacon_number = null;
        }
        else
        {
            $dev_beacon_number = $_POST['dev_beacon_number'];
        }

        if (!isset($_POST['dev_emerwarp_number']))
        {
            $dev_emerwarp_number = null;
        }
        else
        {
            $dev_emerwarp_number = $_POST['dev_emerwarp_number'];
        }

        if (!isset($_POST['dev_warpedit_number']))
        {
            $dev_warpedit_number = null;
        }
        else
        {
            $dev_warpedit_number = $_POST['dev_warpedit_number'];
        }

        if (!isset($_POST['dev_minedeflector_number']))
        {
            $dev_minedeflector_number = null;
        }
        else
        {
            $dev_minedeflector_number = $_POST['dev_minedeflector_number'];
        }

        if (!isset($_POST['escapepod_purchase']))
        {
            $escapepod_purchase = null;
        }
        else
        {
            $escapepod_purchase = $_POST['escapepod_purchase'];
        }

        if (!isset($_POST['fuelscoop_purchase']))
        {
            $fuelscoop_purchase = null;
        }
        else
        {
            $fuelscoop_purchase = $_POST['fuelscoop_purchase'];
        }

        if (!isset($_POST['lssd_purchase']))
        {
            $lssd_purchase = null;
        }
        else
        {
            $lssd_purchase = $_POST['lssd_purchase'];
        }

        $hull_upgrade_cost = 0;
        if ($hull_upgrade > $playerinfo['hull'])
        {
            $hull_upgrade_cost = phpChangeDelta ($hull_upgrade, $playerinfo['hull']);
        }

        $engine_upgrade_cost = 0;
        if ($engine_upgrade > $playerinfo['engines'])
        {
            $engine_upgrade_cost = phpChangeDelta ($engine_upgrade, $playerinfo['engines']);
        }

        $power_upgrade_cost = 0;
        if ($power_upgrade > $playerinfo['power'])
        {
            $power_upgrade_cost = phpChangeDelta ($power_upgrade, $playerinfo['power']);
        }

        $computer_upgrade_cost = 0;
        if ($computer_upgrade > $playerinfo['computer'])
        {
            $computer_upgrade_cost = phpChangeDelta ($computer_upgrade, $playerinfo['computer']);
        }

        $sensors_upgrade_cost = 0;
        if ($sensors_upgrade > $playerinfo['sensors'])
        {
            $sensors_upgrade_cost = phpChangeDelta ($sensors_upgrade, $playerinfo['sensors']);
        }

        $beams_upgrade_cost = 0;
        if ($beams_upgrade > $playerinfo['beams'])
        {
            $beams_upgrade_cost = phpChangeDelta ($beams_upgrade, $playerinfo['beams']);
        }

        $armor_upgrade_cost = 0;
        if ($armor_upgrade > $playerinfo['armor'])
        {
            $armor_upgrade_cost = phpChangeDelta ($armor_upgrade, $playerinfo['armor']);
        }

        $cloak_upgrade_cost = 0;
        if ($cloak_upgrade > $playerinfo['cloak'])
        {
            $cloak_upgrade_cost = phpChangeDelta ($cloak_upgrade, $playerinfo['cloak']);
        }

        $torp_launchers_upgrade_cost = 0;
        if ($torp_launchers_upgrade > $playerinfo['torp_launchers'])
        {
            $torp_launchers_upgrade_cost = phpChangeDelta ($torp_launchers_upgrade, $playerinfo['torp_launchers']);
        }

        $shields_upgrade_cost = 0;
        if ($shields_upgrade > $playerinfo['shields'])
        {
            $shields_upgrade_cost = phpChangeDelta ($shields_upgrade, $playerinfo['shields']);
        }

        if ($fighter_number < 0)
        {
            $fighter_number = 0;
        }

        $fighter_number = round (abs ($fighter_number));
        $fighter_max = NUM_FIGHTERS ($playerinfo['computer']) - $playerinfo['ship_fighters'];
        if ($fighter_max < 0)
        {
            $fighter_max = 0;
        }

        if ($fighter_number > $fighter_max)
        {
            $fighter_number = $fighter_max;
        }

        $fighter_cost    = $fighter_number * $fighter_price;
        if ($torpedo_number < 0)
        {
            $torpedo_number = 0;
        }

        $torpedo_number = round (abs ($torpedo_number));
        $torpedo_max = NUM_TORPEDOES ($playerinfo['torp_launchers']) - $playerinfo['torps'];
        if ($torpedo_max < 0)
        {
            $torpedo_max = 0;
        }

        if ($torpedo_number > $torpedo_max)
        {
            $torpedo_number = $torpedo_max;
        }

        $torpedo_cost = $torpedo_number * $torpedo_price;
        if ($armor_number < 0)
        {
            $armor_number = 0;
        }

        $armor_number = round (abs ($armor_number));
        $armor_max = NUM_ARMOR ($playerinfo['armor']) - $playerinfo['armor_pts'];
        if ($armor_max < 0)
        {
            $armor_max = 0;
        }

        if ($armor_number > $armor_max)
        {
            $armor_number = $armor_max;
        }

       $armor_cost     = $armor_number * $armor_price;
        if ($colonist_number < 0)
        {
            $colonist_number = 0;
        }

        $colonist_number = round (abs ($colonist_number));
        $colonist_max    = NUM_HOLDS ($playerinfo['hull']) - $playerinfo['ship_ore'] - $playerinfo['ship_organics'] - $playerinfo['ship_goods'] - $playerinfo['ship_colonists'];

        if ($colonist_max < 0)
        {
            $colonist_max = 0;
        }

        if ($colonist_number > $colonist_max)
        {
            $colonist_number = $colonist_max;
        }

        $colonist_cost = $colonist_number * $colonist_price;

        $dev_genesis_number = min (round (abs ($dev_genesis_number)), $max_genesis - $playerinfo['dev_genesis']);
        $dev_genesis_cost = $dev_genesis_number * $dev_genesis_price;

        $dev_beacon_number = min (round (abs ($dev_beacon_number)), $max_beacons - $playerinfo['dev_beacon']);
        $dev_beacon_cost = $dev_beacon_number * $dev_beacon_price;

        $dev_emerwarp_number = min (round (abs ($dev_emerwarp_number)), $max_emerwarp - $playerinfo['dev_emerwarp']);
        $dev_emerwarp_cost = $dev_emerwarp_number * $dev_emerwarp_price;

        $dev_warpedit_number = min (round (abs ($dev_warpedit_number)), $max_warpedit - $playerinfo['dev_warpedit']);
        $dev_warpedit_cost = $dev_warpedit_number * $dev_warpedit_price;

        $dev_minedeflector_number = round (abs ($dev_minedeflector_number));
        $dev_minedeflector_cost = $dev_minedeflector_number * $dev_minedeflector_price;

        $dev_escapepod_cost = 0;
        $dev_fuelscoop_cost = 0;
        $dev_lssd_cost = 0;

        if (($escapepod_purchase) && ($playerinfo['dev_escapepod'] != 'Y'))
        {
            $dev_escapepod_cost = $dev_escapepod_price;
        }

        if (($fuelscoop_purchase) && ($playerinfo['dev_fuelscoop'] != 'Y'))
        {
            $dev_fuelscoop_cost = $dev_fuelscoop_price;
        }

        if (($lssd_purchase) && ($playerinfo['dev_lssd'] != 'Y'))
        {
            $dev_lssd_cost = $dev_lssd_price;
        }

        $total_cost = $hull_upgrade_cost + $engine_upgrade_cost + $power_upgrade_cost + $computer_upgrade_cost +
                      $sensors_upgrade_cost + $beams_upgrade_cost + $armor_upgrade_cost + $cloak_upgrade_cost +
                      $torp_launchers_upgrade_cost + $fighter_cost + $torpedo_cost + $armor_cost + $colonist_cost +
                      $dev_genesis_cost + $dev_beacon_cost + $dev_emerwarp_cost + $dev_warpedit_cost + $dev_minedeflector_cost +
                      $dev_escapepod_cost + $dev_fuelscoop_cost + $dev_lssd_cost + $shields_upgrade_cost;
        if ($total_cost > $playerinfo['credits'])
        {
            echo "You do not have enough credits for this transaction.  The total cost is " . NUMBER ($total_cost) . " credits and you only have " . NUMBER ($playerinfo['credits']) . " credits.<br><br>Click <a href=port.php>here</A> to return to the supply depot.<br><br>";
        }
        else
        {
            $trade_credits = NUMBER (abs ($total_cost));
				?>
                <div class="general-table-container">
                <?
            echo "<table><tbody>
                    <tr>
                        <td>$l_trade_result</td>
                    </tr>
                    <tr>
                        <td>Total Trade Costed: <span class=\"table_word_yellow\">" . $trade_credits . "</span> Credits</td>
                    </tr>";

            //  Total cost is " . NUMBER (abs ($total_cost)) . " credits.<br><br>";
            $query = "UPDATE {$db->prefix}ships SET credits=credits-$total_cost";
            if ($hull_upgrade > $playerinfo['hull'])
            {
                $tempvar = 0;
                $tempvar = phpTrueDelta ($hull_upgrade, $playerinfo['hull']);
                $query = $query . ", hull=hull+$tempvar";
                BuildOneCol ("$l_hull $l_trade_upgraded $hull_upgrade");
            }

            if ($engine_upgrade > $playerinfo['engines'])
            {
                $tempvar = 0;
                $tempvar = phpTrueDelta ($engine_upgrade, $playerinfo['engines']);
                $query = $query . ", engines=engines + $tempvar";
                BuildOneCol ("$l_engines $l_trade_upgraded $engine_upgrade");
            }

            if ($power_upgrade > $playerinfo['power'])
            {
                $tempvar = 0; $tempvar=phpTrueDelta ($power_upgrade, $playerinfo['power']);
                $query = $query . ", power=power+$tempvar";
                BuildOneCol ("$l_power $l_trade_upgraded $power_upgrade");
            }

            if ($computer_upgrade > $playerinfo['computer'])
            {
                $tempvar = 0; $tempvar=phpTrueDelta ($computer_upgrade, $playerinfo['computer']);
                $query = $query . ", computer=computer+$tempvar";
                BuildOneCol ("$l_computer $l_trade_upgraded $computer_upgrade");
            }

            if ($sensors_upgrade > $playerinfo['sensors'])
            {
                $tempvar = 0; $tempvar=phpTrueDelta ($sensors_upgrade, $playerinfo['sensors']);
                $query = $query . ", sensors=sensors+$tempvar";
                BuildOneCol ("$l_sensors $l_trade_upgraded $sensors_upgrade");
            }

            if ($beams_upgrade > $playerinfo['beams'])
            {
                $tempvar = 0; $tempvar=phpTrueDelta ($beams_upgrade, $playerinfo['beams']);
                $query = $query . ", beams=beams+$tempvar";
                BuildOneCol ("$l_beams $l_trade_upgraded $beams_upgrade");
            }

            if ($armor_upgrade > $playerinfo['armor'])
            {
                $tempvar = 0; $tempvar=phpTrueDelta ($armor_upgrade, $playerinfo['armor']);
                $query = $query . ", armor=armor+$tempvar";
                BuildOneCol ("$l_armor $l_trade_upgraded $armor_upgrade");
            }

            if ($cloak_upgrade > $playerinfo['cloak'])
            {
                $tempvar = 0; $tempvar=phpTrueDelta ($cloak_upgrade, $playerinfo['cloak']);
                $query = $query . ", cloak=cloak+$tempvar";
                BuildOneCol ("$l_cloak $l_trade_upgraded $cloak_upgrade");
            }

            if ($torp_launchers_upgrade > $playerinfo['torp_launchers'])
            {
                $tempvar = 0; $tempvar=phpTrueDelta ($torp_launchers_upgrade, $playerinfo['torp_launchers']);
                $query = $query . ", torp_launchers=torp_launchers+$tempvar";
                BuildOneCol ("$l_torp_launch $l_trade_upgraded $torp_launchers_upgrade");
            }

            if ($shields_upgrade > $playerinfo['shields'])
            {
                $tempvar = 0; $tempvar=phpTrueDelta ($shields_upgrade, $playerinfo['shields']);
                $query = $query . ", shields=shields+$tempvar";
                BuildOneCol ("$l_shields $l_trade_upgraded $shields_upgrade");
            }

            if ($fighter_number)
            {
                $query = $query . ", ship_fighters=ship_fighters+$fighter_number";
                BuildTwoCol("$l_fighters $l_trade_added:", $fighter_number, "left", "right" );
            }

            if ($torpedo_number)
            {
                $query = $query . ", torps=torps+$torpedo_number";
                BuildTwoCol("$l_torps $l_trade_added:", $torpedo_number, "left", "right" );
            }

            if ($armor_number)
            {
                $query = $query . ", armor_pts=armor_pts+$armor_number";
                BuildTwoCol("$l_armorpts $l_trade_added:", $armor_number, "left", "right" );
            }

            if ($colonist_number)
            {
                $query = $query . ", ship_colonists=ship_colonists+$colonist_number";
                BuildTwoCol("$l_colonists $l_trade_added:", $colonist_number, "left", "right" );
            }

            if ($dev_genesis_number)
            {
                $query = $query . ", dev_genesis=dev_genesis+$dev_genesis_number";
                BuildTwoCol("$l_genesis $l_trade_added:", $dev_genesis_number, "left", "right" );
            }

            if ($dev_beacon_number)
            {
                $query = $query . ", dev_beacon=dev_beacon+$dev_beacon_number";
                BuildTwoCol("$l_beacons $l_trade_added:", $dev_beacon_number , "left", "right" );
            }

            if ($dev_emerwarp_number)
            {
                $query = $query . ", dev_emerwarp=dev_emerwarp+$dev_emerwarp_number";
                BuildTwoCol("$l_ewd $l_trade_added:", $dev_emerwarp_number , "left", "right" );
            }

            if ($dev_warpedit_number)
            {
                $query = $query . ", dev_warpedit=dev_warpedit+$dev_warpedit_number";
                BuildTwoCol("$l_warpedit $l_trade_added:", $dev_warpedit_number , "left", "right" );
            }

            if ($dev_minedeflector_number)
            {
                $query = $query . ", dev_minedeflector=dev_minedeflector+$dev_minedeflector_number";
                BuildTwoCol("$l_deflect $l_trade_added:", $dev_minedeflector_number , "left", "right" );
            }

            if (($escapepod_purchase) && ($playerinfo['dev_escapepod'] != 'Y'))
            {
                $query = $query . ", dev_escapepod='Y'";
                BuildOneCol ("$l_escape_pod $l_trade_installed");
            }

            if (($fuelscoop_purchase) && ($playerinfo['dev_fuelscoop'] != 'Y'))
            {
                $query = $query . ", dev_fuelscoop='Y'";
                BuildOneCol ("$l_fuel_scoop $l_trade_installed");
            }

            if (($lssd_purchase) && ($playerinfo['dev_lssd'] != 'Y'))
            {
                $query = $query . ", dev_lssd='Y'";
                BuildOneCol ("Warp Analyser $l_trade_installed");
            }

            $query = $query . ", turns=turns-1, turns_used=turns_used+1 WHERE ship_id=$playerinfo[ship_id]";
            $purchase = $db->Execute("$query");
            db_op_result ($db, $purchase, __LINE__, __FILE__, $db_logging);

#           if ($colonist_max < 0 )
#           {
#               BuildTwoCol("<span style='color:#f00;'>Detected Overflow</span>", "<span style='color:#0f0;'>Fixed</span>", "left", "right");
#               $resx = $db->Execute("UPDATE {$db->prefix}ships SET ship_ore=0, ship_organics=0, ship_goods=0, ship_energy=0, ship_colonists =0 WHERE ship_id=$playerinfo[ship_id] LIMIT 1;");
#               db_op_result ($db, $resx, __LINE__, __FILE__, $db_logging);
#           }

            $hull_upgrade = 0;
            echo "</tbody></table>";
?>
</div>
<?
            echo "<div style='font-size:16px; color:#fff;'><br>[<span style='color:#0f0;'>Border Patrol</span>]<br>\n";
            echo "Halt, while we scan your cargo...<br>\n";

            if ((NUM_HOLDS($playerinfo['hull']) - $playerinfo['ship_ore'] - $playerinfo['ship_organics'] - $playerinfo['ship_goods'] - $playerinfo['ship_colonists']) < 0 )
            {
                // BuildTwoCol("<span style='color:#f00;'>Detected Illegal Cargo</span>", "<span style='color:#0f0;'>Fixed</span>", "left", "right");
                echo "<span style='color:#f00; font-weight:bold;'>Detected illegal cargo, as a penalty, we are confiscating all of your cargo, you may now continue.</span>\n";
                $resx = $db->Execute("UPDATE {$db->prefix}ships SET ship_ore=0, ship_organics=0, ship_goods=0, ship_energy=0, ship_colonists =0 WHERE ship_id=$playerinfo[ship_id] LIMIT 1;");
                db_op_result ($db, $resx, __LINE__, __FILE__, $db_logging);
                adminlog ($db, 5001, "Detected illegal cargo on shipID: {$playerinfo['ship_id']}");
            }
            else
            {
                echo "<span style='color:#0f0;'>Detected no illegal cargo, you may continue.</span>\n";
            }
            echo "</div>\n";
        }
    }
    elseif ($sectorinfo['port_type'] != "none")
    {
        // Here is the trade fonction to strip out some "spaghetti code". The function saves about 60 lines of code, I hope it will be
        // easier to modify/add something in this part.
        $price_array = array();


        // Clear variables that are not selected in the form
        if (!isset($_POST['trade_ore']))
        {
            $trade_ore = null;
        }
        else
        {
            $trade_ore = $_POST['trade_ore'];
        }

        if (!isset($_POST['trade_organics']))
        {
            $trade_organics = null;
        }
        else
        {
            $trade_organics = $_POST['trade_organics'];
        }

        if (!isset($_POST['trade_goods']))
        {
            $trade_goods = null;
        }
        else
        {
            $trade_goods = $_POST['trade_goods'];
        }

        if (!isset($_POST['trade_energy']))
        {
            $trade_energy = null;
        }
        else
        {
            $trade_energy = $_POST['trade_energy'];
        }

        function trade ($price, $delta, $max, $limit, $factor, $port_type, $origin)
        {
            global $trade_color, $trade_deficit, $trade_result, $trade_benefit, $sectorinfo, $color_green, $color_red, $price_array;

            if ($sectorinfo['port_type'] ==  $port_type )
            {
                $price_array[$port_type] = $price - $delta * $max / $limit * $factor;
            }
            else
            {
                $price_array[$port_type] = $price + $delta * $max / $limit * $factor;
                $origin = -$origin;
            }

            // Debug info
            // print "$origin*$price_array[$port_type]=";
            // print $origin*$price_array[$port_type]."<br>";
            return $origin;
        }

        $trade_ore      = round (abs ($trade_ore));
        $trade_organics = round (abs ($trade_organics));
        $trade_goods    = round (abs ($trade_goods));
        $trade_energy   = round (abs ($trade_energy));

        $trade_ore       =  trade($ore_price,        $ore_delta,       $sectorinfo['port_ore'],        $ore_limit,       $inventory_factor, "ore",        $trade_ore);
        $trade_organics  =  trade($organics_price,   $organics_delta,  $sectorinfo['port_organics'],   $organics_limit,  $inventory_factor, "organics",   $trade_organics );
        $trade_goods     =  trade($goods_price,      $goods_delta,     $sectorinfo['port_goods'],      $goods_limit,     $inventory_factor, "goods",      $trade_goods);
        $trade_energy    =  trade($energy_price,     $energy_delta,    $sectorinfo['port_energy'],     $energy_limit,    $inventory_factor, "energy",     $trade_energy);

        $ore_price       =  $price_array['ore'];
        $organics_price  =  $price_array['organics'];
        $goods_price     =  $price_array['goods'];
        $energy_price    =  $price_array['energy'];

        $cargo_exchanged = $trade_ore + $trade_organics + $trade_goods;

        $free_holds = NUM_HOLDS ($playerinfo['hull']) - $playerinfo['ship_ore'] - $playerinfo['ship_organics'] - $playerinfo['ship_goods'] - $playerinfo['ship_colonists'];
        $free_power = NUM_ENERGY($playerinfo['power']) - $playerinfo['ship_energy'];
        $total_cost = $trade_ore * $ore_price + $trade_organics * $organics_price + $trade_goods * $goods_price + $trade_energy * $energy_price;

        // Debug info
        // echo "$trade_ore * $ore_price + $trade_organics * $organics_price + $trade_goods * $goods_price + $trade_energy * $energy_price = $total_cost";

        if ($free_holds < $cargo_exchanged)
        {
            echo $l_notenough_cargo . " <br><br>";
        }
        elseif ($trade_energy > $free_power)
        {
            echo $l_notenough_power . " <br><br>";
        }
        elseif ($playerinfo['turns'] < 1)
        {
            echo $l_notenough_turns . ".<br><br>";
        }
        elseif ($playerinfo['credits'] < $total_cost)
        {
           echo $l_notenough_credits . " <br><br>";
        }
        elseif ($trade_ore < 0 && abs ($playerinfo['ship_ore']) < abs ($trade_ore))
        {
            echo "$l_notenough_ore ";
        }
        elseif ($trade_organics < 0 && abs ($playerinfo['ship_organics']) < abs ($trade_organics))
        {
            echo "$l_notenough_organics ";
        }
        elseif ($trade_goods < 0 && abs ($playerinfo['ship_goods']) < abs ($trade_goods))
        {
            echo "$l_notenough_goods ";
        }
        elseif ($trade_energy < 0 && abs ($playerinfo['ship_energy']) < abs ($trade_energy))
        {
            echo "$l_notenough_energy ";
        }
        elseif (abs ($trade_organics) > $sectorinfo['port_organics'])
        {
            echo $l_exceed_organics;
        }
        elseif (abs ($trade_ore) > $sectorinfo['port_ore'])
        {
            echo $l_exceed_ore;
        }
        elseif (abs ($trade_goods) > $sectorinfo['port_goods'])
        {
            echo $l_exceed_goods;
        }
        elseif (abs ($trade_energy) > $sectorinfo['port_energy'])
        {
            echo $l_exceed_energy;
        }
        else
        {
            if ($total_cost == 0 )
            {
                $trade_color   = "#fff";
                $trade_result  = "$l_cost : ";
            }
            elseif ($total_cost < 0 )
            {
                $trade_color   = $color_green;
                $trade_result  = $trade_benefit;
            }
            else
            {
                $trade_color   = $color_red;
                $trade_result  = $trade_deficit;
            }
	/*
	Build table
	*/	
	?>
    <div class="general-table-container">
    <?
            echo "<table><tbody>
                    <tr>
                        <td colspan=\"2\">$l_trade_result</td>
                    </tr>
                    <tr>
                        <td colspan=\"2\">Total Trade Cost: <span class=\"table_word_yellow\">" . NUMBER (abs ($total_cost)) . "</span> Credits</td>
                    </tr>
                    <tr>
                        <td>$l_traded_ore:</td><td>" . NUMBER ($trade_ore) . "</td>
                    </tr>
                   <tr>
                        <td>$l_traded_organics: </td><td>" . NUMBER ($trade_organics) . "</td>
                    </tr>
                    <tr>
                        <td>$l_traded_goods: </td><td>" . NUMBER ($trade_goods) . "</td>
                    </tr>
                    <tr>
                        <td>$l_traded_energy: </td><td>" . NUMBER ($trade_energy) . "</td>
                    </tr>
                    </tbody></table></div>";

            // Update ship cargo, credits and turns
            $trade_result     = $db->Execute("UPDATE {$db->prefix}ships SET turns=turns-1, turns_used=turns_used+1, rating=rating+1, credits=credits-$total_cost, ship_ore=ship_ore+$trade_ore, ship_organics=ship_organics+$trade_organics, ship_goods=ship_goods+$trade_goods, ship_energy=ship_energy+$trade_energy WHERE ship_id=$playerinfo[ship_id]");
            db_op_result ($db, $trade_result, __LINE__, __FILE__, $db_logging);

            // Make all trades positive to change port values
            $trade_ore        = round (abs ($trade_ore));
            $trade_organics   = round (abs ($trade_organics));
            $trade_goods      = round (abs ($trade_goods));
            $trade_energy     = round (abs ($trade_energy));

            // Decrease supply and demand on port
            $trade_result2    = $db->Execute("UPDATE {$db->prefix}universe SET port_ore=port_ore-$trade_ore, port_organics=port_organics-$trade_organics, port_goods=port_goods-$trade_goods, port_energy=port_energy-$trade_energy WHERE sector_id=$sectorinfo[sector_id]");
            db_op_result ($db, $trade_result2, __LINE__, __FILE__, $db_logging);

            echo $l_trade_complete . ".<br><br>";
        }
    }
}

echo "<br><br>";
TEXT_GOTOMAIN ();

if ($sectorinfo['port_type'] == "special")
{
    echo "<br><br>Click <a href=port.php>here</A> to return to the supply depot.";
}
?>
</div></div>
<?
include "footer.php";
?>

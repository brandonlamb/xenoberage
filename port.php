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
// File: port.php

include "config/config.php";
updatecookie ();

// New database driven language entries
load_languages($db, $lang, array('port', 'report', 'device', 'common', 'global_includes', 'global_funcs', 'combat', 'footer', 'news', 'bounty'), $langvars, $db_logging);

include_once "includes/text_javascript_begin.php";
include_once "includes/text_javascript_end.php";
include_once "includes/is_loan_pending.php";

$body_class = 'port';
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
$res = $db->Execute("SELECT * FROM {$db->prefix}ships WHERE ship_id='$user_ship_id'");
db_op_result ($db, $res, __LINE__, __FILE__, $db_logging);
$playerinfo = $res->fields;

// Fix negative quantities. How do the quantities acutally get negative?

if ($playerinfo['ship_ore'] < 0 )
{
    $fixres = $db->Execute("UPDATE {$db->prefix}ships SET ship_ore=0 WHERE ship_id='$user_ship_id'");
    db_op_result ($db, $fixres, __LINE__, __FILE__, $db_logging);
    $playerinfo['ship_ore'] = 0;
}

if ($playerinfo['ship_organics'] < 0 )
{
    $fixres = $db->Execute("UPDATE {$db->prefix}ships SET ship_organics=0 WHERE ship_id='$user_ship_id'");
    db_op_result ($db, $fixres, __LINE__, __FILE__, $db_logging);
    $playerinfo['ship_organics'] = 0;
}

if ($playerinfo['ship_energy'] < 0 )
{
    $fixres = $db->Execute("UPDATE {$db->prefix}ships SET ship_energy=0 WHERE ship_id='$user_ship_id'");
    db_op_result ($db, $fixres, __LINE__, __FILE__, $db_logging);
    $playerinfo['ship_energy'] = 0;
}

if ($playerinfo['ship_goods'] < 0 )
{
    $fixres = $db->Execute("UPDATE {$db->prefix}ships SET ship_goods=0 WHERE ship_id='$user_ship_id'");
    db_op_result ($db, $fixres, __LINE__, __FILE__, $db_logging);
    $playerinfo['ship_goods'] = 0;
}

$res = $db->Execute("SELECT * FROM {$db->prefix}universe WHERE sector_id='$playerinfo[sector]'");
db_op_result ($db, $res, __LINE__, __FILE__, $db_logging);
$sectorinfo = $res->fields;

if ($sectorinfo['port_ore'] < 0 )
{
    $fixres = $db->Execute("UPDATE {$db->prefix}universe SET port_ore=0 WHERE sector_id=$playerinfo[sector]");
    db_op_result ($db, $fixres, __LINE__, __FILE__, $db_logging);
    $sectorinfo['port_ore'] = 0;
}

if ($sectorinfo['port_goods'] < 0 )
{
    $fixres = $db->Execute("UPDATE {$db->prefix}universe SET port_goods=0 WHERE sector_id=$playerinfo[sector]");
    db_op_result ($db, $fixres, __LINE__, __FILE__, $db_logging);
    $sectorinfo['port_goods'] = 0;
}

if ($sectorinfo['port_organics'] < 0 )
{
    $fixres = $db->Execute("UPDATE {$db->prefix}universe SET port_organics=0 WHERE sector_id=$playerinfo[sector]");
    db_op_result ($db, $fixres, __LINE__, __FILE__, $db_logging);
    $sectorinfo['port_organics'] = 0;
}

if ($sectorinfo['port_energy'] < 0 )
{
    $fixres = $db->Execute("UPDATE {$db->prefix}universe SET port_energy=0 WHERE sector_id=$playerinfo[sector]");
    db_op_result ($db, $fixres, __LINE__, __FILE__, $db_logging);
    $sectorinfo['port_energy'] = 0;
}

$res = $db->Execute("SELECT * FROM {$db->prefix}zones WHERE zone_id=$sectorinfo[zone_id]");
db_op_result ($db, $res, __LINE__, __FILE__, $db_logging);
$zoneinfo = $res->fields;

if ($zoneinfo['zone_id'] == 4)
{
    $title = $l_sector_war;
    bigtitle ();
    echo $l_war_info . "<p>";
    TEXT_GOTOMAIN();
    include "footer.php";
    die();
}
elseif ($zoneinfo['allow_trade'] == 'N')
{
    // Translation needed
    $title = "Trade forbidden";
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
            // Translation needed
            $title = "Trade forbidden";
            bigtitle ();
            echo "Trading at this port is not allowed for outsiders<p>";
            TEXT_GOTOMAIN();
            include "footer.php";
            die();
        }
    }
    else
    {
        if ($playerinfo['team'] != $zoneinfo['owner'])
        {
            $title = $l_no_trade;
            bigtitle ();
            echo $l_no_trade_out . "<p>";
            TEXT_GOTOMAIN();
            include "footer.php";
            die();
        }
    }
}

if ($sectorinfo['port_type'] != "none" && $sectorinfo['port_type'] != "special")
{
    $title = $l_title_trade;
    bigtitle ();

    if ($sectorinfo['port_type'] == "ore")
    {
        $ore_price = $ore_price - $ore_delta * $sectorinfo['port_ore'] / $ore_limit * $inventory_factor;
        $sb_ore = $l_selling;
    }
    else
    {
        $ore_price = $ore_price + $ore_delta * $sectorinfo['port_ore'] / $ore_limit * $inventory_factor;
        $sb_ore = $l_buying;
    }

    if ($sectorinfo['port_type'] == "organics")
    {
        $organics_price = $organics_price - $organics_delta * $sectorinfo['port_organics'] / $organics_limit * $inventory_factor;
        $sb_organics = $l_selling;
    }
    else
    {
        $organics_price = $organics_price + $organics_delta * $sectorinfo['port_organics'] / $organics_limit * $inventory_factor;
        $sb_organics = $l_buying;
    }

    if ($sectorinfo['port_type'] == "goods")
    {
        $goods_price = $goods_price - $goods_delta * $sectorinfo['port_goods'] / $goods_limit * $inventory_factor;
        $sb_goods = $l_selling;
    }
    else
    {
        $goods_price = $goods_price + $goods_delta * $sectorinfo['port_goods'] / $goods_limit * $inventory_factor;
        $sb_goods = $l_buying;
    }

    if ($sectorinfo['port_type'] == "energy")
    {
        $energy_price = $energy_price - $energy_delta * $sectorinfo['port_energy'] / $energy_limit * $inventory_factor;
        $sb_energy = $l_selling;
    }
    else
    {
        $energy_price = $energy_price + $energy_delta * $sectorinfo['port_energy'] / $energy_limit * $inventory_factor;
        $sb_energy = $l_buying;
    }

    // Establish default amounts for each commodity
    if ($sb_ore == $l_buying)
    {
        $amount_ore = $playerinfo['ship_ore'];
    }
    else
    {
        $amount_ore = NUM_HOLDS($playerinfo['hull']) - $playerinfo['ship_ore'] - $playerinfo['ship_colonists'];
    }

    if ($sb_organics == $l_buying)
    {
        $amount_organics = $playerinfo['ship_organics'];
    }
    else
    {
        $amount_organics = NUM_HOLDS($playerinfo['hull']) - $playerinfo['ship_organics'] - $playerinfo['ship_colonists'];
    }

    if ($sb_goods == $l_buying)
    {
        $amount_goods = $playerinfo['ship_goods'];
    }
    else
    {
        $amount_goods = NUM_HOLDS($playerinfo['hull']) - $playerinfo['ship_goods'] - $playerinfo['ship_colonists'];
    }

    if ($sb_energy == $l_buying)
    {
        $amount_energy = $playerinfo['ship_energy'];
    }
    else
    {
        $amount_energy = NUM_ENERGY ($playerinfo['power']) - $playerinfo['ship_energy'];
    }

    // Limit amounts to port quantities
    $amount_ore = min ($amount_ore, $sectorinfo['port_ore']);
    $amount_organics = min ($amount_organics, $sectorinfo['port_organics']);
    $amount_goods = min ($amount_goods, $sectorinfo['port_goods']);
    $amount_energy = min ($amount_energy, $sectorinfo['port_energy']);

    // Limit amounts to what the player can afford
    if ($sb_ore == $l_selling)
    {
        $amount_ore = min ($amount_ore, floor (($playerinfo['credits'] + $amount_organics * $organics_price + $amount_goods * $goods_price + $amount_energy * $energy_price) / $ore_price));
    }

    if ($sb_organics == $l_selling)
    {
        $amount_organics = min ($amount_organics, floor (($playerinfo['credits'] + $amount_ore * $ore_price + $amount_goods * $goods_price + $amount_energy * $energy_price) / $organics_price));
    }

    if ($sb_goods == $l_selling)
    {
        $amount_goods = min ($amount_goods, floor (($playerinfo['credits'] + $amount_ore * $ore_price + $amount_organics * $organics_price + $amount_energy * $energy_price) / $goods_price));
    }

    if ($sb_energy == $l_selling)
    {
        $amount_energy = min ($amount_energy, floor (($playerinfo['credits'] + $amount_ore * $ore_price + $amount_organics * $organics_price + $amount_goods * $goods_price) / $energy_price));
    }

    echo "<form action=port2.php method=post>";
	/*
	Build table
	*/	
	?>
    <div class="general-table-container">
    <?
    echo "<table><tbody>";
    echo "<tr><td>$l_commodity</td><td>$l_buying/$l_selling</td><td>$l_amount</td><td>$l_price</td><td>$l_buy/$l_sell</td><td>$l_cargo</td></tr>";
    echo "<tr><td>$l_ore</td><td>$sb_ore</td><td>" . NUMBER ($sectorinfo['port_ore']) . "</td><td>$ore_price</td><td><input type=TEXT NAME=trade_ore SIZE=10 MAXLENGTH=20 value=$amount_ore></td><td>" . NUMBER ($playerinfo['ship_ore']) . "</td></tr>";
    echo "<tr><td>$l_organics</td><td>$sb_organics</td><td>" . NUMBER ($sectorinfo['port_organics']) . "</td><td>$organics_price</td><td><input type=TEXT NAME=trade_organics SIZE=10 MAXLENGTH=20 value=$amount_organics></td><td>" . NUMBER ($playerinfo['ship_organics']) . "</td></tr>";
    echo "<tr><td>$l_goods</td><td>$sb_goods</td><td>" . NUMBER ($sectorinfo['port_goods']) . "</td><td>$goods_price</td><td><input type=TEXT NAME=trade_goods SIZE=10 MAXLENGTH=20 value=$amount_goods></td><td>" . NUMBER ($playerinfo['ship_goods']) . "</td></tr>";
    echo "<tr><td>$l_energy</td><td>$sb_energy</td><td>" . NUMBER ($sectorinfo['port_energy']) . "</td><td>$energy_price</td><td><input type=TEXT NAME=trade_energy SIZE=10 MAXLENGTH=20 value=$amount_energy></td><td>" . NUMBER ($playerinfo['ship_energy']) . "</td></tr>";
    echo "</tbody></table>";
	?>
    </div>
    <?
    echo "<input type=submit value=$l_trade>";

    echo "</form>";

    $free_holds = NUM_HOLDS($playerinfo['hull']) - $playerinfo['ship_ore'] - $playerinfo['ship_organics'] - $playerinfo['ship_goods'] - $playerinfo['ship_colonists'];
    $free_power = NUM_ENERGY($playerinfo['power']) - $playerinfo['ship_energy'];

    $l_trade_st_info = str_replace ("[free_holds]", NUMBER ($free_holds), $l_trade_st_info);
    $l_trade_st_info = str_replace ("[free_power]", NUMBER ($free_power), $l_trade_st_info);
    $l_trade_st_info = str_replace ("[credits]", NUMBER ($playerinfo['credits']), $l_trade_st_info);
    echo $l_trade_st_info;
}
elseif ($sectorinfo['port_type'] == "special")
{
    $title = $l_special_port;
    bigtitle ();

    // Kami Multi-browser window upgrade fix
    $_SESSION['port_shopping'] = true;

    if (is_loan_pending ($db, $playerinfo['ship_id']))
    {
        echo $l_port_loannotrade . "<p>";
        echo "<a href=igb.php>" . $l_igb_term . "</a><p>";
        TEXT_GOTOMAIN();
        include "footer.php";
        die();
    }

    if ($bounty_all_special == true)
    {
        $res2 = $db->Execute("SELECT SUM(amount) as total_bounty FROM {$db->prefix}bounty WHERE placed_by = 0 AND bounty_on = $playerinfo[ship_id];");
        db_op_result ($db, $res2, __LINE__, __FILE__, $db_logging);
    }
    else
    {
        $res2 = $db->Execute("SELECT SUM(amount) as total_bounty FROM {$db->prefix}bounty WHERE placed_by = 0 AND bounty_on = $playerinfo[ship_id] AND {$sectorinfo[zone_id]}=2;");
        db_op_result ($db, $res2, __LINE__, __FILE__, $db_logging);
    }

    if ($res2)
    {
        $bty = $res2->fields;
        if ($bty['total_bounty'] > 0)
        {
            $bank_sql = "SELECT * FROM {$db->prefix}ibank_accounts WHERE ship_id = $playerinfo[ship_id]";
            $bank_res = $db->Execute($bank_sql);
            db_op_result ($db, $bank_res, __LINE__, __FILE__, $db_logging);
            $bank_row = $bank_res->fields;

            if (isset($pay) && $pay == 1)
            {
                if ($playerinfo['credits'] < $bty['total_bounty'])
                {
                    $l_port_btynotenough = str_replace ("[amount]", NUMBER ($bty[total_bounty]), $l_port_btynotenough);
                    echo $l_port_btynotenough . "<br>";
                    TEXT_GOTOMAIN();
                    die();
                }
                else
                {
                    $resx = $db->Execute("UPDATE {$db->prefix}ships SET credits=credits-$bty[total_bounty] WHERE ship_id = $playerinfo[ship_id]");
                    db_op_result ($db, $resx, __LINE__, __FILE__, $db_logging);
                    $resx = $db->Execute("DELETE FROM {$db->prefix}bounty WHERE bounty_on = $playerinfo[ship_id] AND placed_by = 0");
                    db_op_result ($db, $resx, __LINE__, __FILE__, $db_logging);
                    $l_port_bountypaid = str_replace ("[here]","<a href='port.php'>" . $l_here . "</a>",$l_port_bountypaid);
                    echo $l_port_bountypaid . "<br>";
                    die();
                }
            }
            elseif (isset($pay) && $pay == 2)
            {
                $bank_sql = "SELECT * FROM {$db->prefix}ibank_accounts WHERE ship_id = $playerinfo[ship_id]";
                $bank_res = $db->Execute($bank_sql);
                db_op_result ($db, $bank_res, __LINE__, __FILE__, $db_logging);
                $bank_row = $bank_res->fields;

                $bounty_payment = $bank_row['balance'];
                if ($bounty_payment >1000)
                {
                    $bounty_payment -= 1000;

                    if ($bank_row['balance'] >= $bty['total_bounty'])
                    {
                        // Translation needed
                        echo "Full Payment Mode<br>\n";
                        echo "You have paid your entire bounty<br>\n";
                        echo "<br>\n";

                        $bounty_payment = $bty['total_bounty'];

                        $resx = $db->Execute("UPDATE {$db->prefix}ibank_accounts SET balance=balance-$bounty_payment WHERE ship_id = $playerinfo[ship_id]");
                        db_op_result ($db, $resx, __LINE__, __FILE__, $db_logging);

                        $resx = $db->Execute("DELETE FROM {$db->prefix}bounty WHERE bounty_on = $playerinfo[ship_id] AND placed_by = 0");
                        db_op_result ($db, $resx, __LINE__, __FILE__, $db_logging);

                        echo $l_port_bountypaid . "<br>";
                        die();
                    }
                    else
                    {
                        // Translation needed
                        echo "Partial Payment Mode<br>\n";
                        echo "You don't have enough Credits within your Intergalactic Bank Account to pay your entire bounty.<br>\n";
                        echo "However you can pay your bounty off in instalments.<br>\n";
                        echo "And your first instalment will be " . NUMBER ($bounty_payment)." credits.<br>\n";
                        echo "<br>\n";

                        $resx = $db->Execute("UPDATE {$db->prefix}ibank_accounts SET balance=balance-$bounty_payment WHERE ship_id = $playerinfo[ship_id]");
                        db_op_result ($db, $resx, __LINE__, __FILE__, $db_logging);
                        $resx = $db->Execute("UPDATE {$db->prefix}bounty SET amount = amount - $bounty_payment  WHERE bounty_on = $playerinfo[ship_id] AND placed_by = 0");
                        db_op_result ($db, $resx, __LINE__, __FILE__, $db_logging);
                        echo "You have paid part of the bounty.<br>\n";
                        echo "<br>\n";

                        TEXT_GOTOMAIN();
                        die();
                    }
                }
                else
                {
                    // Translation needed
                    echo "Sorry you don't have enough funds in the bank.<br>\n";
                    echo "Try doing some trading then transfer your funds over to the <a href='igb.php'>Intergalactic Bank</a><br>\n";
                    echo "<br>\n";

                    TEXT_GOTOMAIN();
                    die();
                }

                $bounty_left    = $bty['total_bounty'] - $bounty_payment;
                TEXT_GOTOMAIN();
                die();
            }
            else
            {
                echo $l_port_bounty . "<br>";
                echo "<br>\n";

                echo "Option Plan 1: Payment from Ship<br>\n";
                $l_port_bounty2 = str_replace ("[amount]", NUMBER ($bty['total_bounty']), $l_port_bounty2);
                $l_port_bounty2 = str_replace ("[here]","<a href='port.php?pay=1'>" . $l_here . "</a>", $l_port_bounty2);
                echo $l_port_bounty2 . "<br>";
                echo "<br>\n";

                echo "Option Plan 2: Payment from Intergalactic Bank [Full/Partial Payments]<br>\n";
                $l_port_bounty3 = "Click <a href='port.php?pay=2'>here</a> to pay the bounty of [amount] Credits from your Intergalactic Bank Account.";
                $l_port_bounty3 = str_replace ("[amount]", NUMBER ($bty['total_bounty']), $l_port_bounty3);
                echo $l_port_bounty3 . "<br>\n";
                echo "<br>\n";

                echo "<a href=\"bounty.php\">" . $l_by_placebounty . "</a><br><br>";
                TEXT_GOTOMAIN();
                die();
            }
        }
    }

    $genesis_free = $max_genesis - $playerinfo['dev_genesis'];
    $beacon_free = $max_beacons - $playerinfo['dev_beacon'];
    $emerwarp_free = $max_emerwarp - $playerinfo['dev_emerwarp'];
    $warpedit_free = $max_warpedit - $playerinfo['dev_warpedit'];
    $fighter_max = NUM_FIGHTERS ($playerinfo['computer']);
    $fighter_free = $fighter_max - $playerinfo['ship_fighters'];
    $torpedo_max = NUM_TORPEDOES ($playerinfo['torp_launchers']);
    $torpedo_free = $torpedo_max - $playerinfo['torps'];
    $armor_max = NUM_ARMOR ($playerinfo['armor']);
    $armor_free = $armor_max - $playerinfo['armor_pts'];
    $colonist_max = NUM_HOLDS ($playerinfo['hull']) - $playerinfo['ship_ore'] - $playerinfo['ship_organics'] - $playerinfo['ship_goods'];

    if ($colonist_max < 0 )
    {
        $colonist_max = 0;
    }

    $colonist_free = $colonist_max - $playerinfo['ship_colonists'];
    TEXT_JAVASCRIPT_BEGIN ();

    echo "function MakeMax(name, val)\n";
    echo "{\n";
    echo " if (document.forms[0].elements[name].value != val)\n";
    echo " {\n";
    echo "  if (val != 0)\n";
    echo "  {\n";
    echo "  document.forms[0].elements[name].value = val;\n";
    echo "  }\n";
    echo " }\n";
    echo "}\n";

    // changeDelta function //
    echo "function changeDelta(desiredvalue,currentvalue)\n";
    echo "{\n";
    echo "  Delta=0; DeltaCost=0;\n";
    echo "  Delta = desiredvalue - currentvalue;\n";
    echo "\n";
    echo "    while (Delta>0) \n";
    echo "    {\n";
    echo "     DeltaCost=DeltaCost + Math.pow(2,desiredvalue-Delta); \n";
    echo "     Delta=Delta-1;\n";
    echo "    }\n";
    echo "\n";
    echo "  DeltaCost=DeltaCost * $upgrade_cost\n";
    echo "  return DeltaCost;\n";
    echo "}\n";

    echo "function countTotal()\n";
    echo "{\n";
    echo "// Here we cycle through all form values (other than buy, or full), and regexp out all non-numerics. (1,000 = 1000)\n";
    echo "// Then, if its become a null value (type in just a, it would be a blank value. blank is bad.) we set it to zero.\n";
    echo "var form = document.forms[0];\n";
    echo "var i = form.elements.length;\n";
    echo "while (i > 0)\n";
    echo " {\n";
    echo " if ((form.elements[i-1].value != 'Buy') && (form.elements[i-1].value != 'Full'))\n";
    echo "  {\n";
    echo "  var tmpval = form.elements[i-1].value.replace(/\D+/g, \"\");\n";
    echo "  if (tmpval != form.elements[i-1].value)\n";
    echo "   {\n";
    echo "   form.elements[i-1].value = form.elements[i-1].value.replace(/\D+/g, \"\");\n";
    echo "   }\n";
    echo "  }\n";
    echo " if (form.elements[i-1].value == '')\n";
    echo "  {\n";
    echo "  form.elements[i-1].value ='0';\n";
    echo "  }\n";
    echo " i--;\n";
    echo "}\n";
    echo "// Here we set all 'Max' items to 0 if they are over max - player amt.\n";

    echo "if (($genesis_free < form.dev_genesis_number.value) && (form.dev_genesis_number.value != 'Full'))\n";
    echo " {\n";
    echo " form.dev_genesis_number.value=0\n";
    echo " }\n";

    echo "if (($beacon_free < form.dev_beacon_number.value) && (form.dev_beacon_number.value != 'Full'))\n";
    echo " {\n";
    echo " form.dev_beacon_number.value=0\n";
    echo " }\n";

    echo "if (($emerwarp_free < form.dev_emerwarp_number.value) && (form.dev_emerwarp_number.value != 'Full'))\n";
    echo " {\n";
    echo " form.dev_emerwarp_number.value=0\n";
    echo " }\n";

    echo "if (($warpedit_free < form.dev_warpedit_number.value) && (form.dev_warpedit_number.value != 'Full'))\n";
    echo " {\n";
    echo " form.dev_warpedit_number.value=0\n";
    echo " }\n";

    echo "if (($fighter_free < form.fighter_number.value) && (form.fighter_number.value != 'Full'))\n";
    echo " {\n";
    echo " form.fighter_number.value=0\n";
    echo " }\n";

    echo "if (($torpedo_free < form.torpedo_number.value) && (form.torpedo_number.value != 'Full'))\n";
    echo "  {\n";
    echo "  form.torpedo_number.value=0\n";
    echo "  }\n";

    echo "if (($armor_free < form.armor_number.value) && (form.armor_number.value != 'Full'))\n";
    echo "  {\n";
    echo "  form.armor_number.value=0\n";
    echo "  }\n";

    echo "if (($colonist_free < form.colonist_number.value) && (form.colonist_number.value != 'Full' ))\n";
    echo "  {\n";
    echo "  form.colonist_number.value=0\n";
    echo "  }\n";

    echo "// Done with the bounds checking\n";
    echo "// Pluses must be first, or if empty will produce a javascript error\n";
    echo "form.total_cost.value = 0\n";

    // NaN Fix :: Needed to be put in an if statment to check for Full.
    if ($genesis_free > 0)
    {
        echo "+ form.dev_genesis_number.value * $dev_genesis_price \n";
    }

    // NaN Fix :: Needed to be put in an if statment to check for Full.
    if ($beacon_free > 0)
    {
        echo "+ form.dev_beacon_number.value * $dev_beacon_price\n";
    }

    if ($emerwarp_free > 0)
    {
        echo "+ form.dev_emerwarp_number.value * $dev_emerwarp_price\n";
    }

    // NaN Fix :: Needed to be put in an if statment to check for Full.
    if ($warpedit_free > 0)
    {
        echo "+ form.dev_warpedit_number.value * $dev_warpedit_price\n";
    }

    echo "+ form.elements['dev_minedeflector_number'].value * $dev_minedeflector_price\n";

    if ($playerinfo['dev_escapepod'] == 'N')
    {
        echo "+ (form.escapepod_purchase.checked ?  $dev_escapepod_price : 0)\n";
    }

    if ($playerinfo['dev_fuelscoop'] == 'N')
    {
        echo "+ (form.fuelscoop_purchase.checked ?  $dev_fuelscoop_price : 0)\n";
    }
    if ($playerinfo['dev_lssd'] == 'N')
    {
        echo "+ (form.lssd_purchase.checked ?  $dev_lssd_price : 0)\n";
    }

    echo "+ changeDelta(form.hull_upgrade.value,$playerinfo[hull])\n";
    echo "+ changeDelta(form.engine_upgrade.value,$playerinfo[engines])\n";
    echo "+ changeDelta(form.power_upgrade.value,$playerinfo[power])\n";
    echo "+ changeDelta(form.computer_upgrade.value,$playerinfo[computer])\n";
    echo "+ changeDelta(form.sensors_upgrade.value,$playerinfo[sensors])\n";
    echo "+ changeDelta(form.beams_upgrade.value,$playerinfo[beams])\n";
    echo "+ changeDelta(form.armor_upgrade.value,$playerinfo[armor])\n";
    echo "+ changeDelta(form.cloak_upgrade.value,$playerinfo[cloak])\n";
    echo "+ changeDelta(form.torp_launchers_upgrade.value,$playerinfo[torp_launchers])\n";
    echo "+ changeDelta(form.shields_upgrade.value,$playerinfo[shields])\n";

    if ($playerinfo['ship_fighters'] != $fighter_max)
    {
        echo "+ form.fighter_number.value * $fighter_price ";
    }

    if ($playerinfo['torps'] != $torpedo_max)
    {
        echo "+ form.torpedo_number.value * $torpedo_price ";
    }

    if ($playerinfo['armor_pts'] != $armor_max)
    {
        echo "+ form.armor_number.value * $armor_price ";
    }

    if ($playerinfo['ship_colonists'] != $colonist_max)
    {
        echo "+ form.colonist_number.value * $colonist_price ";
    }

    echo ";\n";
    echo "  if (form.total_cost.value > $playerinfo[credits])\n";
    echo "  {\n";
    echo "    form.total_cost.value = '$l_no_credits';\n";
//  echo "    form.total_cost.value = 'You are short '+(form.total_cost.value - $playerinfo[credits]) +' credits';\n";
    echo "  }\n";
    echo "  form.total_cost.length = form.total_cost.value.length;\n";
    echo "\n";
    echo "form.engine_costper.value=changeDelta(form.engine_upgrade.value,$playerinfo[engines]);\n";
    echo "form.power_costper.value=changeDelta(form.power_upgrade.value,$playerinfo[power]);\n";
    echo "form.computer_costper.value=changeDelta(form.computer_upgrade.value,$playerinfo[computer]);\n";
    echo "form.sensors_costper.value=changeDelta(form.sensors_upgrade.value,$playerinfo[sensors]);\n";
    echo "form.beams_costper.value=changeDelta(form.beams_upgrade.value,$playerinfo[beams]);\n";
    echo "form.armor_costper.value=changeDelta(form.armor_upgrade.value,$playerinfo[armor]);\n";
    echo "form.cloak_costper.value=changeDelta(form.cloak_upgrade.value,$playerinfo[cloak]);\n";
    echo "form.torp_launchers_costper.value=changeDelta(form.torp_launchers_upgrade.value,$playerinfo[torp_launchers]);\n";
    echo "form.hull_costper.value=changeDelta(form.hull_upgrade.value,$playerinfo[hull]);\n";
    echo "form.shields_costper.value=changeDelta(form.shields_upgrade.value,$playerinfo[shields]);\n";
    echo "}";
    TEXT_JAVASCRIPT_END();

    $onblur = "ONBLUR=\"countTotal()\"";
    $onfocus =  "ONFOCUS=\"countTotal()\"";
    $onchange =  "ONCHANGE=\"countTotal()\"";
    $onclick =  "ONCLICK=\"countTotal()\"";

    // Create dropdowns when called
    function dropdown ($element_name, $current_value)
    {
        global $onchange;
        global $max_upgrades_devices;
        $i = $current_value;
        $dropdownvar = "<select size='1' name='$element_name'";
        $dropdownvar = "$dropdownvar $onchange>\n";
        while ($i <= (int) $max_upgrades_devices)
        {
            if ($current_value == $i)
            {
                $dropdownvar = "$dropdownvar        <option value='$i' selected>$i</option>\n";
            }
            else
            {
                $dropdownvar = "$dropdownvar        <option value='$i'>$i</option>\n";
            }
            $i++;
        }

        $dropdownvar = "$dropdownvar       </select>\n";
        return $dropdownvar;
    }


    echo "<P>\n";
    $l_creds_to_spend = str_replace ("[credits]", NUMBER ($playerinfo['credits']), $l_creds_to_spend);
    echo $l_creds_to_spend . "<br>\n";
    if ($allow_ibank)
    {
        $igblink = "\n<a href=igb.php>" . $l_igb_term . "</a>";
        $l_ifyouneedmore = str_replace ("[igb]", $igblink, $l_ifyouneedmore);

        echo $l_ifyouneedmore . "<br>";
    }
    echo "\n";
    echo "<a href=\"bounty.php\">$l_by_placebounty</a><br>\n";
    echo " <form action=port2.php method=post>\n";
	/*
	Build table
	*/	
	?>
    <div class="port-table-container">
    <?
    echo "  <table><tbody>";
    echo "   <tr>";
    echo "    <td>$l_device</td>";
    echo "    <td>$l_cost</td>";
    echo "    <td>$l_current</td>";
    echo "    <td>$l_max</td>";
    echo "    <td>$l_qty</td>";
    echo "    <td>$l_ship_levels</td>";
    echo "    <td>$l_cost</td>";
    echo "    <td>$l_current</td>";
    echo "    <td>$l_upgrade</td>";
    echo "   </tr>";
    echo "   <tr>\n";
#   echo "    <td>$l_genesis</td>\n";
#   echo "    <td>" . NUMBER ($dev_genesis_price) . "</td>\n";
#   echo "    <td>" . NUMBER ($playerinfo[dev_genesis]) . "</td>\n";
#   echo "    <td>$l_unlimited</td>\n";
#   echo "    <td><input type=TEXT NAME=dev_genesis_number SIZE=4 MAXLENGTH=4 value=0 $onblur></td>\n";

    echo "    <td>$l_genesis</td>\n";
    echo "    <td>" . NUMBER ($dev_genesis_price) . "</td>\n";
    echo "    <td>" . NUMBER ($playerinfo['dev_genesis']) . "</td>\n";
    echo "    <td>";
    if ($playerinfo['dev_genesis'] != $max_genesis)
    {
        echo"<a href='#' onClick=\"MakeMax('dev_genesis_number', $genesis_free);countTotal();return false;\">";
        echo NUMBER ($genesis_free) . "</a></td>\n";
        echo"    <td><input type=TEXT NAME=dev_genesis_number SIZE=4 MAXLENGTH=4 value=0 $onblur>";
    }
    else
    {
        echo "0</td>\n";
        echo "    <td><input type=text readonly NAME=dev_genesis_number MAXLENGTH=10 value=$l_full $onblur tabindex='0'>";
    }

    echo "</td>\n";
    echo "    <td>$l_hull</td>\n";
    echo "    <td><input type=text readonly class=\"port-cost-upgrades\" size=25 name=hull_costper value='0' tabindex='0' $onblur></td>\n";
    echo "    <td>" . NUMBER ($playerinfo['hull']) . "</td>\n";
    echo "    <td>\n       ";
    echo dropdown("hull_upgrade",$playerinfo['hull']);
    echo "    </td>\n";
    echo "   </tr>\n";
    echo "   <tr>\n";
#   echo "    <td>$l_beacons</td>\n";
#   echo "    <td>" . NUMBER ($dev_beacon_price) . "</td>\n";
#   echo "    <td>" . NUMBER ($playerinfo['dev_beacon']) . "</td>\n";
#   echo "    <td>$l_unlimited</td>\n";
#   echo "    <td><input type=TEXT NAME=dev_beacon_number SIZE=4 MAXLENGTH=4 value=0 $onblur></td>\n";

    echo "    <td>$l_beacons</td>\n";
    echo "    <td>" . NUMBER ($dev_beacon_price) . "</td>\n";
    echo "    <td>" . NUMBER ($playerinfo['dev_beacon']) . "</td>\n";
    echo "    <td>";
    if ($playerinfo['dev_beacon'] != $max_beacons)
    {
        echo"<a href='#' onClick=\"MakeMax('dev_beacon_number', $beacon_free);countTotal();return false;\">";
        echo NUMBER ($beacon_free) . "</a></td>\n";
        echo"    <td><input type=TEXT NAME=dev_beacon_number SIZE=4 MAXLENGTH=4 value=0 $onblur>";
    }
    else
    {
        echo "0</td>\n";
        echo "    <td><input type=text readonly NAME=dev_beacon_number MAXLENGTH=10 value=$l_full $onblur tabindex='0'>";
    }
    echo "</td>\n";
    echo "    <td>$l_engines</td>\n";
    echo "    <td><input type=text readonly  class=\"port-cost-upgrades\" size=25 name=engine_costper value='0' tabindex='0' $onblur></td>\n";
    echo "    <td>" . NUMBER ($playerinfo['engines']) . "</td>\n";
    echo "    <td>\n       ";
    echo dropdown("engine_upgrade",$playerinfo['engines']);
    echo "    </td>\n";
    echo "   </tr>\n";
    echo "   <tr>\n";
    echo "    <td>$l_ewd</td>\n";
    echo "    <td>" . NUMBER ($dev_emerwarp_price) . "</td>\n";
    echo "    <td>" . NUMBER ($playerinfo['dev_emerwarp']) . "</td>\n";
    echo "    <td>";
    if ($playerinfo['dev_emerwarp'] != $max_emerwarp)
    {
        echo"<a href='#' onClick=\"MakeMax('dev_emerwarp_number', $emerwarp_free);countTotal();return false;\">";
        echo NUMBER ($emerwarp_free) . "</a></td>\n";
        echo"    <td><input type=TEXT NAME=dev_emerwarp_number SIZE=4 MAXLENGTH=4 value=0 $onblur>";
    }
    else
    {
        echo "0</td>\n";
        echo "    <td><input type=text readonly NAME=dev_emerwarp_number MAXLENGTH=10 value=$l_full $onblur tabindex='0'>";
    }

    echo "</td>\n";
    echo "    <td>$l_power</td>\n";
    echo "    <td><input type=text readonly name=power_costper class=\"port-cost-upgrades\" size=25 value='0' tabindex='0' $onblur></td>\n";
    echo "    <td>" . NUMBER ($playerinfo['power']) . "</td>\n";
    echo "    <td>\n       ";
    echo dropdown("power_upgrade",$playerinfo['power']);
    echo "    </td>\n";
    echo "  </tr>\n";
    echo "  <tr>\n";
#   echo "    <td>$l_warpedit</td>\n";
#   echo "    <td>" . NUMBER ($dev_warpedit_price) . "</td>\n";
#   echo "    <td>" . NUMBER ($playerinfo['dev_warpedit']) . "</td><td>$l_unlimited</td><td><input type=TEXT NAME=dev_warpedit_number SIZE=4 MAXLENGTH=4 value=0 $onblur></td>";

    echo "    <td>$l_warpedit</td>\n";
    echo "    <td>" . NUMBER ($dev_warpedit_price) . "</td>\n";
    echo "    <td>" . NUMBER ($playerinfo['dev_warpedit']) . "</td>\n";
    echo "    <td>";
    if ($playerinfo['dev_warpedit'] != $max_warpedit)
    {
        echo"<a href='#' onClick=\"MakeMax('dev_warpedit_number', $warpedit_free);countTotal();return false;\">";
        echo NUMBER ($warpedit_free) . "</a></td>\n";
        echo"    <td><input type=TEXT NAME=dev_warpedit_number SIZE=4 MAXLENGTH=4 value=0 $onblur>";
    }
    else
    {
        echo "0</td>\n";
        echo "    <td><input type=text readonly NAME=dev_warpedit_number MAXLENGTH=10 value=$l_full $onblur tabindex='0'>";
    }

    echo "</td>\n";

    echo "    <td>$l_computer</td>\n";
    echo "    <td><input type=text readonly name=computer_costper class=\"port-cost-upgrades\" size=25 value='0' tabindex='0' $onblur></td>\n";
    echo "    <td>" . NUMBER ($playerinfo['computer']) . "</td>\n";
    echo "    <td>\n       ";
    echo dropdown("computer_upgrade",$playerinfo['computer']);
    echo "    </td>\n";
    echo "  </tr>\n";
    echo "  <tr>\n";
    echo "    <td colspan=\"5\">&nbsp;</td>\n";
    echo "    <td>$l_sensors</td>\n";
    echo "    <td><input type=text readonly name=sensors_costper class=\"port-cost-upgrades\" size=25 value='0' tabindex='0' $onblur></td>\n";
    echo "    <td>" . NUMBER ($playerinfo['sensors']) . "</td>\n";
    echo "    <td>\n       ";
    echo dropdown("sensors_upgrade",$playerinfo['sensors']);
    echo "    </td>\n";
    echo "  </tr>";
    echo "  <tr>\n";
    echo "    <td>$l_deflect</td>\n";
    echo "    <td>" . NUMBER ($dev_minedeflector_price) . "</td>\n";
    echo "    <td>" . NUMBER ($playerinfo['dev_minedeflector']) . "</td>\n";
    echo "    <td>$l_unlimited</td>\n";
    echo "    <td><input type=TEXT NAME=dev_minedeflector_number SIZE=4 MAXLENGTH=10 value=0 $onblur></td>\n";
    echo "    <td>$l_beams</td>\n";
    echo "    <td><input type=text readonly name=beams_costper class=\"port-cost-upgrades\" size=25 value='0' tabindex='0' $onblur></td>";
    echo "    <td>" . NUMBER ($playerinfo['beams']) . "</td>\n";
    echo "    <td>\n       ";
    echo dropdown("beams_upgrade",$playerinfo['beams']);
    echo "    </td>\n";
    echo "  </tr>\n";
    echo "  <tr>\n";
    echo "    <td>$l_escape_pod</td>\n";
    echo "    <td>" . NUMBER ($dev_escapepod_price) . "</td>\n";
    if ($playerinfo['dev_escapepod'] == "N")
    {
        echo "    <td>$l_none</td>\n";
        echo "    <td>&nbsp;</td>\n";
        echo "    <td><div class='checkbox-square'><input type=CHECKBOX NAME=escapepod_purchase id='escapepod_purchase' value=1 $onclick><label for='escapepod_purchase'></label></div></td>\n";
    }
    else
    {
        echo "    <td>$l_equipped</td>\n";
        echo "    <td></td>\n";
        echo "    <td></td>\n";
    }

    echo "    <td>$l_armor</td>\n";
    echo "    <td><input type=text readonly name=armor_costper class=\"port-cost-upgrades\" size=25 value='0' tabindex='0' $onblur></td>\n";
    echo "    <td>" . NUMBER ($playerinfo['armor']) . "</td>\n";
    echo "    <td>\n       ";
    echo dropdown("armor_upgrade",$playerinfo['armor']);
    echo "    </td>\n";
    echo "  </tr>\n";
    echo "  <tr>\n";
    echo "    <td>$l_fuel_scoop</td>\n";
    echo "    <td>" . NUMBER ($dev_fuelscoop_price) . "</td>\n";
    if ($playerinfo['dev_fuelscoop'] == "N")
    {
        echo "    <td>$l_none</td>\n";
        echo "    <td>&nbsp;</td>\n";
        echo "    <td><div class='checkbox-square'><input type=CHECKBOX NAME=fuelscoop_purchase id='fuelscoop_purchase' value=1 $onclick><label for='fuelscoop_purchase'></label></div></td>\n";
		
    }
    else
    {
        echo "    <td>$l_equipped</td>\n";
        echo "    <td></td>\n";
        echo "    <td></td>\n";
    }

    echo "    <td>$l_cloak</td>\n";
    echo "    <td><input type=text readonly name=cloak_costper class=\"port-cost-upgrades\" size=25 value='0' tabindex='0' $onblur $onfocus></td>\n";
    echo "    <td>" . NUMBER ($playerinfo['cloak']) . "</td>\n";
    echo "    <td>\n       ";
    echo dropdown("cloak_upgrade",$playerinfo['cloak']);
    echo "    </td>\n";
    echo "  </tr>\n";
    echo "  <tr>\n";
    echo "    <td>Warp Analyser</td>\n";
    echo "    <td>" . NUMBER ($dev_lssd_price) . "</td>\n";
    if ($playerinfo['dev_lssd'] == "N")
    {
        echo "    <td>$l_none</td>\n";
        echo "    <td>&nbsp;</td>\n";
        echo "    <td><div class='checkbox-square'><input type=CHECKBOX NAME=lssd_purchase id='lssd_purchase' value=1 $onclick><label for='lssd_purchase'></label></div></td>\n";
		
    }
    else
    {
        echo "    <td>$l_equipped</td>\n";
        echo "    <td></td>\n";
        echo "    <td></td>\n";
    }

    echo "    <td>$l_torp_launch</td>\n";
    echo "    <td><input type=text readonly name=torp_launchers_costper class=\"port-cost-upgrades\" size=25 value='0' tabindex='0' $onblur></td>\n";
    echo "    <td>" . NUMBER ($playerinfo['torp_launchers']) . "</td>\n";
    echo "    <td>\n       ";
    echo dropdown("torp_launchers_upgrade",$playerinfo['torp_launchers']);
    echo "    </td>\n";
    echo "  </tr>\n";
    echo "  <tr>\n";
echo "    <td colspan=\"5\">&nbsp;</td>\n";
    echo "    <td>$l_shields</td>\n";
    echo "    <td><input type=text readonly name=shields_costper value='0' class=\"port-cost-upgrades\" size=25 tabindex='0' $onblur></td>\n";
    echo "    <td>" . NUMBER ($playerinfo['shields']) . "</td>\n";
    echo "    <td>\n       ";
    echo dropdown("shields_upgrade",$playerinfo['shields']);
    echo "    </td>\n";
    echo "  </tr>\n";
    echo " </tbody></table>\n";
	?>
    </div><div class="port-table-container">
    <?
    echo " <table><tbody>\n";
    echo "  <tr>\n";
    echo "    <td>$l_item</td>\n";
    echo "    <td>$l_cost</td>\n";
    echo "    <td>$l_current</td>\n";
    echo "    <td>$l_max</td>\n";
    echo "    <td>$l_qty</td>\n";
    echo "    <td>$l_item</td>\n";
    echo "    <td>$l_cost</td>\n";
    echo "    <td>$l_current</td>\n";
    echo "    <td>$l_max</td>\n";
    echo "    <td>$l_qty</td>\n";
    echo "  </tr>\n";
    echo "  <tr>\n";
    echo "    <td>$l_fighters</td>\n";
    echo "    <td>" . NUMBER ($fighter_price) . "</td>\n";
    echo "    <td>" . NUMBER ($playerinfo['ship_fighters']) . " / " . NUMBER ($fighter_max) . "</td>\n";
    echo "    <td>";
    if ($playerinfo['ship_fighters'] != $fighter_max)
    {
        echo "<a href='#' onClick=\"MakeMax('fighter_number', $fighter_free);countTotal();return false;\" $onblur>" . NUMBER ($fighter_free) . "</a></td>\n";
        echo "    <td><input type=TEXT NAME=fighter_number SIZE=6 MAXLENGTH=10 value=0 $onblur>";
    }
    else
    {
        echo "0<td><input type=text readonly NAME=fighter_number MAXLENGTH=10 value=$l_full $onblur tabindex='0'>";
    }

    echo "    </td>\n";
    echo "    <td>$l_torps</td>\n";
    echo "    <td>" . NUMBER ($torpedo_price) . "</td>\n";
    echo "    <td>" . NUMBER ($playerinfo['torps']) . " / " . NUMBER ($torpedo_max) . "</td>\n";
    echo "    <td>";
    if ($playerinfo['torps'] != $torpedo_max)
    {
        echo "<a href='#' onClick=\"MakeMax('torpedo_number', $torpedo_free);countTotal();return false;\" $onblur>" . NUMBER ($torpedo_free) . "</a></td>\n";
        echo "    <td><input type=TEXT NAME=torpedo_number SIZE=6 MAXLENGTH=10 value=0 $onblur>";
    }
    else
    {
        echo "0<td><input type=text readonly NAME=torpedo_number MAXLENGTH=10 value=$l_full $onblur tabindex='0'>";
    }

    echo "</td>\n";
    echo "  </tr>\n";
    echo "  <tr>\n";
    echo "    <td>$l_armorpts</td>\n";
    echo "    <td>" . NUMBER ($armor_price) . "</td>\n";
    echo "    <td>" . NUMBER ($playerinfo['armor_pts']) . " / " . NUMBER ($armor_max) . "</td>\n";
    echo "    <td>";
    if ($playerinfo['armor_pts'] != $armor_max)
    {
        echo "<a href='#' onClick=\"MakeMax('armor_number', $armor_free);countTotal();return false;\" $onblur>" . NUMBER ($armor_free) . "</a></td>\n";
        echo "    <td><input type=TEXT NAME=armor_number SIZE=6 MAXLENGTH=10 value=0 $onblur>";
    }
    else
    {
        echo "0<td><input type=text readonly NAME=armor_number MAXLENGTH=10 value=$l_full tabindex='0' $onblur>";
    }

    echo "</td>\n";
    echo "    <td>$l_colonists</td>\n";
    echo "    <td>" . NUMBER ($colonist_price) . "</td>\n";
    echo "    <td>" . NUMBER ($playerinfo['ship_colonists']) . " / ". NUMBER ($colonist_max). "</td>\n";
    echo "    <td>";
    if ($playerinfo['ship_colonists'] != $colonist_max)
    {
        echo "<a href='#' onClick=\"MakeMax('colonist_number', $colonist_free);countTotal();return false;\" $onblur>" . NUMBER ($colonist_free) . "</a></td>\n";
        echo "    <td><input type=TEXT NAME=colonist_number SIZE=6 MAXLENGTH=10 value=0 $onblur>";
    }
    else
    {
        echo "0<td><input type=text readonly NAME=colonist_number MAXLENGTH=10 value=$l_full tabindex='0' $onblur>";
    }
    echo "    </td>\n";
    echo "  </tr>\n";
    echo " </tbody></table>";
	?>
    </div><div class="port-table-container">
    <?
    echo " <table><tbody>\n";
    echo "  <tr style=\"background-color: transparent;\">\n";
    echo "    <td><input type=submit value=$l_buy $onclick></td>\n";
    echo "    <td style=\"text-align:right\">$l_totalcost: <input type=TEXT style=\"text-align:right\" class=\"purchase-total-cost\"NAME=total_cost SIZE=22 value=0 $onfocus $onblur $onchange $onclick></td>\n";
    echo "  </tr>\n";
    echo " </tbody></table>\n";
	?>
    </div>
    <?
    echo "</form><br>\n";
    echo "$l_would_dump <a href=dump.php>$l_here</a>.\n";
}
else
{
    echo $l_noport . "!\n";
}

echo "\n";
echo "<br><br>\n";
TEXT_GOTOMAIN();
echo "\n";
?>
</div></div>
<?
include "footer.php";
?>

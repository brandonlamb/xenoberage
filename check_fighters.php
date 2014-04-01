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
// File: check_fighters.php

if (preg_match("/check_fighters.php/i", $_SERVER['PHP_SELF'])) {
    echo "You can not access this file directly!";
    die();
}

// New database driven language entries
load_languages($db, $lang, array('check_fighters', 'common', 'global_includes', 'global_funcs', 'combat', 'footer', 'news'), $langvars, $db_logging);

include_once "includes/distribute_toll.php";

$result2 = $db->Execute ("SELECT * FROM {$db->prefix}universe WHERE sector_id=?;", array($sector));
db_op_result ($db, $result2, __LINE__, __FILE__, $db_logging);

// Put the sector information into the array "sectorinfo"
$sectorinfo=$result2->fields;

$result3 = $db->Execute ("SELECT * FROM {$db->prefix}sector_defence WHERE sector_id=? and defence_type ='F' ORDER BY quantity DESC;", array($sector));
db_op_result ($db, $result3, __LINE__, __FILE__, $db_logging);

// Put the defence information into the array "defences"
$i = 0;
$total_sector_fighters = 0;
$owner = true;

$response = null;
if (array_key_exists('response', $_POST) == true)
{
    $response = $_POST['response'];
}

$destination = null;
if (array_key_exists('destination', $_REQUEST) == true)
{
    $destination = $_REQUEST['destination'];
}

$engage = null;
if (array_key_exists('engage', $_REQUEST) == true)
{
    $engage = $_REQUEST['engage'];
}


while (!$result3->EOF)
{
    $row = $result3->fields;
    $defences[$i] = $row;
    $total_sector_fighters += $defences[$i]['quantity'];
    if ($defences[$i]['ship_id'] != $playerinfo['ship_id'])
    {
        $owner = false;
    }
    $i++;
    $result3->MoveNext();
}

$num_defences = $i;
if ($num_defences > 0 && $total_sector_fighters > 0 && !$owner)
{
    // Find out if the fighter owner and player are on the same team
    // All sector defences must be owned by members of the same team
    $fm_owner = $defences[0]['ship_id'];
    $result2 = $db->Execute("SELECT * FROM {$db->prefix}ships WHERE ship_id=?;", array($fm_owner));
    db_op_result ($db, $result2, __LINE__, __FILE__, $db_logging);
    $fighters_owner = $result2->fields;
    if ($fighters_owner['team'] != $playerinfo['team'] || $playerinfo['team'] == 0)
    {
        switch ($response)
        {
            case "fight":
                $resx = $db->Execute("UPDATE {$db->prefix}ships SET cleared_defences = ' ' WHERE ship_id = ?;", array($playerinfo['ship_id']));
                db_op_result ($db, $resx, __LINE__, __FILE__, $db_logging);
				?>
<div class="tablecell content both-border">
	<div class="pad">
<?
                bigtitle();
                include_once "sector_fighters.php";
                break;

            case "retreat":
                $resx = $db->Execute("UPDATE {$db->prefix}ships SET cleared_defences = ' ' WHERE ship_id = ?;", array($playerinfo['ship_id']));
                db_op_result ($db, $resx, __LINE__, __FILE__, $db_logging);
                $stamp = date("Y-m-d H-i-s");
                $resx = $db->Execute("UPDATE {$db->prefix}ships SET last_login='$stamp',turns=turns-2, turns_used=turns_used+2, sector=? WHERE ship_id=?;", array($playerinfo['sector'], $playerinfo['ship_id']));
                db_op_result ($db, $resx, __LINE__, __FILE__, $db_logging);
				?>
<div class="tablecell content both-border">
	<div class="pad">
<?
                bigtitle();
                echo "$l_chf_youretreatback<br>";
                TEXT_GOTOMAIN();
				?>
</div></div>
<?
                die();
                break;

            case "pay":
                $resx = $db->Execute("UPDATE {$db->prefix}ships SET cleared_defences = ' ' WHERE ship_id = ?;", array($playerinfo['ship_id']));
                db_op_result ($db, $resx, __LINE__, __FILE__, $db_logging);
                $fighterstoll = $total_sector_fighters * $fighter_price * 0.6;
                if ($playerinfo[credits] < $fighterstoll)
                {
                    echo "$l_chf_notenoughcreditstoll<br>";
                    echo "$l_chf_movefailed<br>";
                    // Undo the move
                    $resx = $db->Execute("UPDATE {$db->prefix}ships SET sector=? WHERE ship_id=?;", array($playerinfo['sector'], $playerinfo['ship_id']));
                    db_op_result ($db, $resx, __LINE__, __FILE__, $db_logging);
                    $ok = 0;
                }
                else
                {
                    $tollstring = NUMBER($fighterstoll);
                    $l_chf_youpaidsometoll = str_replace("[chf_tollstring]", $tollstring, $l_chf_youpaidsometoll);
                    echo "$l_chf_youpaidsometoll<br>";
                    $resx = $db->Execute("UPDATE {$db->prefix}ships SET credits=credits-$fighterstoll WHERE ship_id=?;", array($playerinfo['ship_id']));
                    db_op_result ($db, $resx, __LINE__, __FILE__, $db_logging);
                    distribute_toll ($db, $sector, $fighterstoll, $total_sector_fighters);
                    playerlog ($db, $playerinfo['ship_id'], LOG_TOLL_PAID, "$tollstring|$sector");
                    $ok = 1;
                }
                break;

            case "sneak":
                $resx = $db->Execute("UPDATE {$db->prefix}ships SET cleared_defences = ' ' WHERE ship_id = ?;", array($playerinfo['ship_id']));
                db_op_result ($db, $resx, __LINE__, __FILE__, $db_logging);
                $success = SCAN_SUCCESS($fighters_owner['sensors'], $playerinfo['cloak']);
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
                    // Sector defences detect incoming ship
					?>
<div class="tablecell content both-border">
	<div class="pad">
<?
                    bigtitle();
                    echo "$l_chf_thefightersdetectyou<br>";
                    include_once "sector_fighters.php";
                    break;
                }
                else
                {
                    // Sector defences don't detect incoming ship
                    $ok = 1;
                }
                break;

            default:
                $interface_string = $calledfrom . '?sector='.$sector.'&destination='.$destination.'&engage='.$engage;
                $resx = $db->Execute("UPDATE {$db->prefix}ships SET cleared_defences = ? WHERE ship_id = ?;", array($interface_string, $playerinfo['ship_id']));
                db_op_result ($db, $resx, __LINE__, __FILE__, $db_logging);
                $fighterstoll = $total_sector_fighters * $fighter_price * 0.6;
				?>
<div class="tablecell content both-border">
	<div class="pad">
<?
                bigtitle();
                echo "<form action='{$calledfrom}' method='post'>";
                $l_chf_therearetotalfightersindest = str_replace("[chf_total_sector_fighters]", $total_sector_fighters, $l_chf_therearetotalfightersindest);
                echo "$l_chf_therearetotalfightersindest<br>";
                if ($defences[0]['fm_setting'] == "toll")
                {
                    $l_chf_creditsdemanded = str_replace("[chf_number_fighterstoll]", NUMBER($fighterstoll), $l_chf_creditsdemanded);
                    echo "$l_chf_creditsdemanded<br>";
                }

                $l_chf_youcanretreat = str_replace("[retreat]", "<strong>Retreat</strong>", $l_chf_youcanretreat);
                echo $l_chf_youcan . " <br><input type='radio' name='response' value='retreat'>" . $l_chf_youcanretreat . "<br></input>";
                if ($defences[0]['fm_setting'] == "toll")
                {
                    $l_chf_inputpay = str_replace("[pay]", "<strong>Pay</strong>", $l_chf_inputpay);
                    echo "<input type='radio' name='response' checked value='pay'>" . $l_chf_inputpay . "<br></input>";
                }

                echo "<input type='radio' name='response' checked value='fight'>";
                $l_chf_inputfight = str_replace("[fight]", "<strong>Fight</strong>", $l_chf_inputfight);
                echo $l_chf_inputfight . "<br></input>";

                echo "<input type=radio name=response checked value=sneak>";
                $l_chf_inputcloak = str_replace("[cloak]", "<strong>Cloak</strong>", $l_chf_inputcloak);
                echo $l_chf_inputcloak . "<br></input><br>";

                echo "<input type='submit' value='{$l_chf_go}'><br><br>";
                echo "<input type='hidden' name='sector' value='{$sector}'>";
                echo "<input type='hidden' name='engage' value='1'>";
                echo "<input type='hidden' name='destination' value='{$destination}'>";
                echo "</form>";
                die();
                break;

        }
        // Clean up any sectors that have used up all mines or fighters
        $resx = $db->Execute("DELETE FROM {$db->prefix}sector_defence WHERE quantity <= 0 ");
        db_op_result ($db, $resx, __LINE__, __FILE__, $db_logging);
    }
}
?>

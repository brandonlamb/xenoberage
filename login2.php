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
// File: login2.php

include "config/config.php";
$manage_log = new manage_log();
// Test to see if server is closed to logins
$playerfound = false;

$email = $_POST['email'];
$pass = $_POST['pass'];

if ($_POST['email'] != null)
{
    $res = $db->Execute("SELECT * FROM {$db->prefix}ships WHERE email='$email'");
    db_op_result ($db, $res, __LINE__, __FILE__, $db_logging);
    if ($res)
    {
        $playerfound = $res->RecordCount();
    }
    $playerinfo = $res->fields;
    $lang = $playerinfo['lang'];
}

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
load_languages($db, $lang, array('login2', 'login', 'common', 'global_includes', 'global_funcs', 'footer', 'news'), $langvars, $db_logging);

// first placement of cookie - don't use updatecookie.
$userpass = $email."+".$pass;
setcookie("userpass", $userpass, time() + (3600*24)*365, $gamepath, $gamedomain);

if ($server_closed)
{
    $title = $l_login_sclosed;
    include "header.php";
?>
<div class="tablecell content both-border">
	<div class="pad">
<?
    echo "<div style='text-align:center; color:#ff0; font-size:20px;'><br>$l_login_closed_message</div><br>\n";
    TEXT_GOTOLOGIN();
?>
</div></div>
<?
    include "footer.php";
    die();
}

$title = $l_login_title2;

// Check Banned
$banned = 0;

if (isset($playerinfo))
{
    $res = $db->Execute("SELECT * FROM {$db->prefix}ip_bans WHERE '$ip' LIKE ban_mask OR '$playerinfo[ip_address]' LIKE ban_mask");
    db_op_result ($db, $res, __LINE__, __FILE__, $db_logging);
    if ($res->RecordCount() != 0)
    {
        setcookie("userpass", "", 0, $gamepath, $gamedomain);
        setcookie("userpass", "", 0); // Delete from default path as well.
        $banned = 1;
    }
}

include "header.php";
?>
<div class="tablecell content both-border">
	<div class="pad">
<?
bigtitle ();

if ($playerfound)
{
    if ($playerinfo['password'] == $pass)
    {
        if ($playerinfo['ship_destroyed'] == "N")
        {
			##New Log ##
			$manage_log->player_log($playerinfo['ship_id'],1,'','','','',"<font color='#6190a5'>Low Priority</font>","<b>Logged In</b>");
            // player's ship has not been destroyed
            $stamp = date("Y-m-d H-i-s");
            $update = $db->Execute("UPDATE {$db->prefix}ships SET last_login='$stamp',ip_address='$ip' WHERE ship_id=$playerinfo[ship_id]");
            db_op_result ($db, $update, __LINE__, __FILE__, $db_logging);
            $_SESSION['logged_in'] = true;
            TEXT_GOTOMAIN();
            header("Location: main.php"); // This redirect avoids any rendering for the user of login2. Its a direct transition, visually
        }
        else
        {
            // player's ship has been destroyed
            if ($playerinfo['dev_escapepod'] == "Y")
            {
                $resx = $db->Execute("UPDATE {$db->prefix}ships SET hull=0,engines=0,power=0,computer=0,sensors=0,beams=0,torp_launchers=0,torps=0,armor=0,armor_pts=100,cloak=0,shields=0,sector=0,ship_ore=0,ship_organics=0,ship_energy=1000,ship_colonists=0,ship_goods=0,ship_fighters=100,ship_damage=0,on_planet='N',dev_warpedit=0,dev_genesis=0,dev_beacon=0,dev_emerwarp=0,dev_escapepod='N',dev_fuelscoop='N',dev_minedeflector=0,ship_destroyed='N',dev_lssd='N' WHERE ship_id=$playerinfo[ship_id]");
                db_op_result ($db, $resx, __LINE__, __FILE__, $db_logging);
                $l_login_died = str_replace("[here]", "<a href='main.php'>" . $l_here . "</a>", $l_login_died);
                echo $l_login_died;
            }
            else
            {
                echo "You have died in a horrible incident, <a href=log.php>here</a> is the blackbox information that was retrieved from your ships wreckage.<br><br>";

                // Check if $newbie_nice is set, if so, verify ship limits
                if ($newbie_nice == "YES")
                {
                    $newbie_info = $db->Execute("SELECT hull,engines,power,computer,sensors,armor,shields,beams,torp_launchers,cloak FROM {$db->prefix}ships WHERE ship_id='$playerinfo[ship_id]' AND hull<='$newbie_hull' AND engines<='$newbie_engines' AND power<='$newbie_power' AND computer<='$newbie_computer' AND sensors<='$newbie_sensors' AND armor<='$newbie_armor' AND shields<='$newbie_shields' AND beams<='$newbie_beams' AND torp_launchers<='$newbie_torp_launchers' AND cloak<='$newbie_cloak'");
                    db_op_result ($db, $newbie_info, __LINE__, __FILE__, $db_logging);
                    $num_rows = $newbie_info->RecordCount();

                    if ($num_rows)
                    {
                        echo "<br><br>" . $l_login_newbie . "<br><br>";
                        $resx = $db->Execute("UPDATE {$db->prefix}ships SET hull=0,engines=0,power=0,computer=0,sensors=0,beams=0,torp_launchers=0,torps=0,armor=0,armor_pts=100,cloak=0,shields=0,sector=0,ship_ore=0,ship_organics=0,ship_energy=1000,ship_colonists=0,ship_goods=0,ship_fighters=100,ship_damage=0,credits=1000,on_planet='N',dev_warpedit=0,dev_genesis=0,dev_beacon=0,dev_emerwarp=0,dev_escapepod='N',dev_fuelscoop='N',dev_minedeflector=0,ship_destroyed='N',dev_lssd='N' WHERE ship_id=$playerinfo[ship_id]");
                        db_op_result ($db, $resx, __LINE__, __FILE__, $db_logging);

                        $l_login_newlife = str_replace("[here]", "<a href='main.php'>" . $l_here . "</a>", $l_login_newlife);
                        echo $l_login_newlife;
                    }
                    else
                    {
                        echo "<br><br>" . $l_login_looser . "<br><br>" . $l_login_looser2;
                    }

                } // End if $newbie_nice
                else
                {
                    echo "<br><br>" . $l_login_looser . "<br><br>" . $l_login_looser2;
                }
            }
        }
    }
    else
    {
        // password is incorrect
        echo $l_login_4gotpw1a . "<br><br>" . $l_login_4gotpw1b . " <a href='mail.php?mail=" . $email . "'>" . $l_clickme . "</a> " . $l_login_4gotpw2a . "<br><br>" . $l_login_4gotpw2b . " <a href='index.php'>" . $l_clickme . "</a> " . $l_login_4gotpw3 . " " . $ip . "...";
        adminlog($db, (1000 + LOG_BADLOGIN), "{$ip}|{$email}|{$pass}");
		##New Log ##
		$manage_log->player_log($playerinfo['ship_id'],3,'','','','',"<font color='#FF0000'>High Priority</font>",'<b><font color="#FF0000">Warning</font></b>');
    }
}
else
{
    $l_login_noone = str_replace("[here]", "<a href='new.php" . $link . "'>" . $l_here . "</a>", $l_login_noone);
    echo "<strong>" . $l_login_noone . "</strong><br>";
}
?>
</div></div>
<?
include "footer.php";
?>

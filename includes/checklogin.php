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
// File: includes/checklogin.php

if (preg_match("/checklogin.php/i", $_SERVER['PHP_SELF'])) {
      echo "You can not access this file directly!";
      die();
}

function checklogin ()
{
    $flag = 0;
	$shared_function = new shared();
    global $username, $password, $db, $l, $user_ship_id, $user_cookie_ip, $user_cookie_host, $user_cookie_agent, $lang;
	
    $result1 = $db->Execute("SELECT * FROM {$db->prefix}ships WHERE ship_id=? LIMIT 1", array($user_ship_id));
    db_op_result ($db, $result1, __LINE__, __FILE__);
    $playerinfo = $result1->fields;
    // Check the cookie to see if username/password are empty - check password against database
	//needs changing to check session ID inside cookie matches session ID on server DB, if not force user to log in again!

	
	/*Check user browser and cookie match*/
	$shared_function = new shared();
	$ip_array = $shared_function->sortIP();
	$user_ip_address = $ip_array[0];
	$user_agent = $_SERVER['HTTP_USER_AGENT'];
	$user_host = gethostbyaddr($_SERVER['REMOTE_ADDR']);
	if ($username == "" or $user_ship_id == "" or $password == "")
	{
        $title = $l->get('l_error');
        include "header.php";
        echo str_replace("[here]", "<a href='index.php'>" . $l->get('l_here') . "</a>", $l->get('l_global_needlogin'));
        include "footer.php";
        $flag = 1;
	}
	else
	{
		/*check computers match*/
		if(($user_cookie_ip==$user_ip_address) && ($user_cookie_host==$user_host) && ($user_cookie_agent==$user_agent))
		{
			if ($playerinfo)
			{
				$ip = $_SERVER['REMOTE_ADDR'];
				$stamp = date("Y-m-d H:i:s");
				$timestamp['now']  = (int)strtotime ($stamp);
				$timestamp['last'] = (int)strtotime ($playerinfo['last_login']);
		
				// Update the players last_login ever 60 seconds to cut back SQL Queries.
				if($timestamp['now'] >= ($timestamp['last'] +60))
				{
					$update = $db->Execute("UPDATE {$db->prefix}ships SET last_login = ?, ip_address = ? WHERE ship_id = ?;", array($stamp, $ip, $playerinfo['ship_id']));
				}
			}
		}
		else
		{
			$title = $l->get('l_error');
			include "header.php";
			echo str_replace("[here]", "<a href='index.php'>" . $l->get('l_here') . "</a>", $l->get('l_global_needlogin'));
			include "footer.php";
			$flag = 1;
		}
	}
/*
    // Check for destroyed ship
    if ($playerinfo['ship_destroyed'] == "Y")
    {
        // if the player has an escapepod, set the player up with a new ship
        if ($playerinfo['dev_escapepod'] == "Y")
        {
            $result2 = $db->Execute("UPDATE {$db->prefix}ships SET hull=0, engines=0, power=0, computer=0,sensors=0, beams=0, torp_launchers=0, torps=0, armor=0, armor_pts=100, cloak=0, shields=0, sector=0, ship_ore=0, ship_organics=0, ship_energy=1000, ship_colonists=0, ship_goods=0, ship_fighters=100, ship_damage=0, on_planet='N', dev_warpedit=0, dev_genesis=0, dev_beacon=0, dev_emerwarp=0, dev_escapepod='N', dev_fuelscoop='N', dev_minedeflector=0, ship_destroyed='N',dev_lssd='N' WHERE email=?", array($username));
            db_op_result ($db, $result2, __LINE__, __FILE__);
            echo str_replace("[here]", "<a href='main.php'>" . $l->get('l_here') . "</a>", $l->get('l_login_died'));
            $flag = 1;
        }
        else
        {
            // if the player doesn't have an escapepod - they're dead, delete them. But we can't delete them yet.
            // (This prevents the self-distruct inherit bug)
            echo str_replace("[here]", "<a href='log.php'>" . ucfirst($l->get('l_here')) . "</a>", $l->get('l_global_died')) . "<br><br>" . $l->get('l_global_died2');
            echo str_replace("[logout]", "<a href='logout.php'>" . $l->get('l_logout') . "</a>", $l->get('l_die_please'));
            $flag = 1;
        }
    }
*/
    global $server_closed;
    if ($server_closed && $flag == 0)
    {
        $title = $l->get('l_login_closed_message');
        include "header.php";
        echo $l->get('l_login_closed_message');
        include "footer.php";
        $flag = 1;
    }

    return $flag;
}
?>

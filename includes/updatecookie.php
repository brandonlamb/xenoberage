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
// File: includes/updatecookie.php

if (preg_match("/updatecookie.php/i", $_SERVER['PHP_SELF'])) {
      echo "You can not access this file directly!";
      die();
}

function updatecookie()
{
    // Refresh the cookie with username/password - Times out after 60 mins, and player must login again.
    global $gamepath, $gamedomain, $userpass, $username, $password, $user_ship_id, $user_ip_address, $user_agent, $user_host;
	$shared_function = new shared();
	$ip_array = $shared_function->sortIP();
	$user_ip_address = $ip_array[0];
	$user_agent = $_SERVER['HTTP_USER_AGENT'];
	$user_host = gethostbyaddr($_SERVER['REMOTE_ADDR']);
	$cookie_session_id = md5($user_agent);
	$data = array('username'=>$username, 'password'=>$cookie_session_id,'user_id'=>$user_ship_id,'user_ip'=>$user_ip_address,'user_host'=>$user_host,'user_agent'=>$user_agent);
	$data=serialize($data);
	setcookie("userID", $data, time() + (3600*24)*365, $gamepath, $gamedomain);
}
?>

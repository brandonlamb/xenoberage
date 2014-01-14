<?php
#############################################################################################
# Xenobe Rage - A Refreshed take on Blacknova traders, improving on the impressive hard work#
# done by the Blacknova development team													#
# Copyright (C) 2012-2013 David Dawson and the Xenobe Rage Development Team					#
# Blacknova Traders - A web-based massively multiplayer space combat and trading game		#
# Copyright (C) 2001-2012 Ron Harwood and the BNT development team							#
#																							#
#  This program is free software: you can redistribute it and/or modify						#
#  it under the terms of the GNU Affero General Public License as							#
#  published by the Free Software Foundation, either version 3 of the						#
#  License, or (at your option) any later version.											#
#																							#
#  This program is distributed in the hope that it will be useful,							#
#  but WITHOUT ANY WARRANTY; without even the implied warranty of							#
#  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the							#
#  GNU Affero General Public License for more details.										#
#																							#
#  You should have received a copy of the GNU Affero General Public License					#
#  along with this program.  If not, see <http://www.gnu.org/licenses/>.					#
#############################################################################################

#############################################################################################	
# Player Managment																			#
#																							#
# @copyright 	2013 - David Dawson															#
# @Contact		web.developer@live.co.uk													#
# @license    	http://www.gnu.org/licenses/agpl.txt										#
#############################################################################################
 
#############################################################################################	
# Notes:																					#
#																							#
# 																							#
#																							#
# 																							#
#############################################################################################
require_once($_SERVER['DOCUMENT_ROOT']."/config/config.php");
class shared { 
	private $_db;
	public function __construct()
	{
		if (!session_id())
		{
			session_start();
		}
		$this->connect = db::init();
	}
	private function probe_ip_type($address)
	{
		$test_for_ipv6 = strpos($address, "::ffff:");
		return (strpos($address, ":") !== FALSE) && ($test_for_ipv6 === FALSE || $test_for_ipv6 != 0);
	}
	private function VisitorIP()
	{ 
		if (!empty($_SERVER['HTTP_CLIENT_IP']))
		{
		  $ip=$_SERVER['HTTP_CLIENT_IP'];
		}
		elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR']))
		{
		  $ip=$_SERVER['HTTP_X_FORWARDED_FOR'];
		}
		else
		{
		  $ip=$_SERVER['REMOTE_ADDR'];
		}
		return trim($ip);
	}
	public function sortIP()
	{
		$currentIP = $this->VisitorIP();
		if($this->probe_ip_type($currentIP)==false)
		{
			//IP Address is an Ipv4 Address
			$iparr = preg_split("/\./", $currentIP); 
			$part1 = $iparr[0];
			$part2 = $iparr[1];
			$part3 = $iparr[2];
			$part4 = $iparr[3];
			
			$shown_ip = $part1.".".$part2.".".$part3;
			$type_1_ban = $part1.".".$part2.".".$part3;
			$type_2_ban = $part1.".".$part2;

		}
		else
		{
			//IP Address is an Ivp6 Address
			$iparr = preg_split("/\:/", $currentIP); 
			$part1 = $iparr[0];
			$part2 = $iparr[1];
			$part3 = $iparr[2];
			$part4 = $iparr[3];
			$part5 = $iparr[4];
			$part6 = $iparr[5];
			$part7 = $iparr[6];
			$part8 = $iparr[7];

			$shown_ip = $part1.":".$part2.":".$part3;
			$type_1_ban = $part1.":".$part2.":".$part3;
			$type_2_ban = $part6.":".$part7.":".$part8;
		}
		
		return array($currentIP,$shown_ip,$type_1_ban,$type_2_ban);
	/*
	USAGE:
	$requested_ip = new shared();
	$ip_array =  $requested_ip->sortIP();
	echo $ip_array[0]; //Full IP
	echo $ip_array[1]; //Shown IP
	echo $ip_array[2]; //Level 2 Ban Ip to be used
	echo $ip_array[3]; //Level 3 Ban IP to be used
	*/  
	}
	public function manage_time($type_requested) {  
		date_default_timezone_set('europe/paris');
		$lt = localtime(time(), TRUE);
		if($type_requested=="full")
		{
			$output_time = date('Y-m-d H:i:s');
		}
		else if($type_requested=="full2")
		{
			$output_time = date('Y-m-d H:i');
		}
		else if($type_requested=="date")
		{
			$output_time = date('Y-m-d');
		}
		else if($type_requested=="time")
		{
			$output_time = date('H:i:s');
		}
		return $output_time;
	}
}
?>
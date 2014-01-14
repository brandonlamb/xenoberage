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
# Database Connection																		#
#																							#
# @copyright 	2013 - David Dawson															#
# @Contact		web.developer@live.co.uk													#
# @license    	http://www.gnu.org/licenses/agpl.txt										#
#############################################################################################
 
#############################################################################################	
# Notes:																					#
#																							#
# Need to secure this more, any suggestions guys?											#
#																							#
# 																							#
#############################################################################################

define ('DB_HOST', 	'');
define ('DB_NAME', 	'');
define ('DB_USER', 	'');
define ('DB_PASS', 	'');
class db
{
	private static $db;

	
	public static function init()
	{
		if (!self::$db)
		{
			try {
				$dsn = 'mysql:host='.DB_HOST.';dbname='.DB_NAME;
				self::$db = new PDO($dsn, DB_USER, DB_PASS);
				self::$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			} catch (PDOException $e) {
				// Normally, we would log this
				die('Connection error: ' . $e->getMessage() . '<br/>');
			}
		}
		return self::$db;
	}
}
?>
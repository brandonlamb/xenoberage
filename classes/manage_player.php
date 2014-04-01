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
class manage_player { 
	private $_db;
	public function __construct()
	{
		if (!session_id())
		{
			session_start();
		}
		$this->connect = db::init();
	}
	public function last_activity_stamp($ship_id){
		global $db_prefix;
		$manage_log = new manage_log();
		$shared_function = new shared();
		if($ship_id>0)
		{
			$timestamp = $shared_function->manage_time("full");
			$timestamp_ship = $this->connect->prepare("UPDATE ".$db_prefix."ships SET last_login='".$timestamp."' WHERE ship_id='".$ship_id."'");		
			if($timestamp_ship->execute())
			{									
			}
			else
			{
			}
		}
		else
		{
		}
	}
	/*
	
	*/
	public function generate_score($user_id)
	{
		
		$sql_manager = new manage_table();
		global $db_prefix;
		global $db;
		global $upgrade_factor;
		global $upgrade_cost;
		global $torpedo_price;
		global $armor_price;
		global $fighter_price;
		global $ore_price;
		global $organics_price;
		global $goods_price;
		global $energy_price;
		global $colonist_price;
		global $dev_genesis_price;
		global $dev_beacon_price;
		global $dev_emerwarp_price;
		global $dev_warpedit_price;
		global $dev_minedeflector_price;
		global $dev_escapepod_price;
		global $dev_fuelscoop_price;
		global $dev_lssd_price;
		global $base_ore;
		global $base_goods;
		global $base_organics;
		global $base_credits;
		
		$calc_hull           = "ROUND(pow($upgrade_factor,hull))";
		$calc_engines        = "ROUND(pow($upgrade_factor,engines))";
		$calc_power          = "ROUND(pow($upgrade_factor,power))";
		$calc_computer       = "ROUND(pow($upgrade_factor,computer))";
		$calc_sensors        = "ROUND(pow($upgrade_factor,sensors))";
		$calc_beams          = "ROUND(pow($upgrade_factor,beams))";
		$calc_torp_launchers = "ROUND(pow($upgrade_factor,torp_launchers))";
		$calc_shields        = "ROUND(pow($upgrade_factor,shields))";
		$calc_armor          = "ROUND(pow($upgrade_factor,armor))";
		$calc_cloak          = "ROUND(pow($upgrade_factor,cloak))";
		$calc_levels         = "($calc_hull + $calc_engines + $calc_power + $calc_computer + $calc_sensors + $calc_beams + $calc_torp_launchers + $calc_shields + $calc_armor + $calc_cloak) * $upgrade_cost";
	
		$calc_torps          = "".$db_prefix."ships.torps * $torpedo_price";
		$calc_armor_pts      = "armor_pts * $armor_price";
		$calc_ship_ore       = "ship_ore * $ore_price";
		$calc_ship_organics  = "ship_organics * $organics_price";
		$calc_ship_goods     = "ship_goods * $goods_price";
		$calc_ship_energy    = "ship_energy * $energy_price";
		$calc_ship_colonists = "ship_colonists * $colonist_price";
		$calc_ship_fighters  = "ship_fighters * $fighter_price";
		$calc_equip          = "$calc_torps + $calc_armor_pts + $calc_ship_ore + $calc_ship_organics + $calc_ship_goods + $calc_ship_energy + $calc_ship_colonists + $calc_ship_fighters";
	
		$calc_dev_warpedit      = "dev_warpedit * $dev_warpedit_price";
		$calc_dev_genesis       = "dev_genesis * $dev_genesis_price";
		$calc_dev_beacon        = "dev_beacon * $dev_beacon_price";
		$calc_dev_emerwarp      = "dev_emerwarp * $dev_emerwarp_price";
		$calc_dev_escapepod     = "if (dev_escapepod='Y', $dev_escapepod_price, 0)";
		$calc_dev_fuelscoop     = "if (dev_fuelscoop='Y', $dev_fuelscoop_price, 0)";
		$calc_dev_lssd          = "if (dev_lssd='Y', $dev_lssd_price, 0)";
		$calc_dev_minedeflector = "dev_minedeflector * $dev_minedeflector_price";
		$calc_dev               = "$calc_dev_warpedit + $calc_dev_genesis + $calc_dev_beacon + $calc_dev_emerwarp + $calc_dev_escapepod + $calc_dev_fuelscoop + $calc_dev_minedeflector + $calc_dev_lssd";
	
		$calc_planet_goods      = "SUM(".$db_prefix."planets.organics) * $organics_price + SUM(".$db_prefix."planets.ore) * $ore_price + SUM(".$db_prefix."planets.goods) * $goods_price + SUM(".$db_prefix."planets.energy) * $energy_price";
		$calc_planet_colonists  = "SUM(".$db_prefix."planets.colonists) * $colonist_price";
		$calc_planet_defence    = "SUM(".$db_prefix."planets.fighters) * $fighter_price + if (".$db_prefix."planets.base='Y', $base_credits + SUM(".$db_prefix."planets.torps) * $torpedo_price, 0)";
		$calc_planet_credits    = "SUM(".$db_prefix."planets.credits)";		
		
		$result_planet = $this->connect->query("SELECT if(COUNT(*)>0, ".$calc_planet_goods." + ".$calc_planet_colonists." + ".$calc_planet_defence." + ".$calc_planet_credits.", 0) as planet_score FROM ".$db_prefix."planets WHERE owner=".$user_id."");
	    $result_planet = $result_planet->fetch();
		$planet_score = $result_planet['planet_score'];
		
		$result_player = $this->connect->query("SELECT if(COUNT(*)>0, ".$calc_levels." + ".$calc_equip." + ".$calc_dev." + ".$db_prefix."ships.credits, 0) AS ship_score FROM ".$db_prefix."ships LEFT JOIN ".$db_prefix."planets ON ".$db_prefix."planets.owner=ship_id WHERE ship_id=".$user_id." AND ship_destroyed='N'");
		$result_player = $result_player->fetch();
		$ship_score = $result_player['ship_score'];

		$result_bank = $this->connect->query("SELECT (balance - loan) as bank_score FROM ".$db_prefix."ibank_accounts WHERE ship_id=".$user_id."");

		$result_bank = $result_bank->fetch();
		$bank_score = $result_bank['bank_score'];

		$score = $ship_score + $planet_score + $bank_score;
		if ($score < 0)
		{
			$score = 0;
		}

		$score = (integer) ROUND (SQRT ($score));

		$updated_stats = array('score'=>$score);
		$sql_manager->updatePlayer($user_id,"ships",$updated_stats);
	
		return $score;
	}
}
?>
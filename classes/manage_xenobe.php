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
# Xenobe Managment																			#
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

class manage_xenobe { 
	private $_db;
	public function __construct()
	{
		if (!session_id())
		{
			session_start();
		}
		$this->connect = db::init();
	}
	#############################################################################################
	# REGENERATE XENOBE																			#
	#############################################################################################
	private function regenerate_xenobe($id,$xenobeisdead){
		global $xen_unemployment,$db_prefix;
		$sql_manager = new manage_table();
		$ship_manager = new manage_ship();
		/*First check if the xenobe is alive, otherwise this function doesnt need to executed*/
		$xenobeisdead = $sql_manager->check_player_alive($id);
		if($xenobeisdead=='Y')
		{
			/*
			Xenobe is dead, should we delete him?
			*/
		}
		else
		{
			/*Xenobe get discounts through pirate ports and as such get cheaper (and inferior) equipment*/
			$xenobe_fighter_price = 6;
			$xenobe_torp_price = 3;
			
			/*Regerate the Xenobe using the xenobes credits.*/
			$playerinfo = $sql_manager->playerinfo($id,"");
			
			$playerinfo['credits'] = $playerinfo['credits'] + $xen_unemployment; /*Pay them their benefits*/
			# Regenerate Energy #
			
			$maxenergy = $ship_manager->number_energy($playerinfo['power']); // Regenerate energy
			if ($playerinfo['ship_energy'] <= ($maxenergy - 50))  // Stop regen when within 50 of max
			{
				$playerinfo['ship_energy'] = $playerinfo['ship_energy'] + round (($maxenergy - $playerinfo['ship_energy']) / 2); // Regen half of remaining energy
				$output .= "regenerated Energy to $playerinfo[ship_energy] units, ";
			}
			
			# Regenerate Fighters #
			
			$available_fighters = $ship_manager->number_fighters($playerinfo['computer']) - $playerinfo['ship_fighters'];
			if (($playerinfo['credits'] > 5) && ($available_fighters > 0))
			{
				if (round ($playerinfo['credits'] / $xenobe_fighter_price) > $available_fighters)
				{
					$purchase = ($available_fighters * $xenobe_fighter_price);
					$playerinfo['credits'] = $playerinfo['credits'] - $purchase;
					$playerinfo['ship_fighters'] = $playerinfo['ship_fighters'] + $available_fighters;
					$output .= "purchased $available_fighters fighters for $purchase credits, ";
				}
		
				if (round ($playerinfo['credits'] / $xenobe_fighter_price) <= $available_fighters)
				{
					$purchase = (round ($playerinfo['credits'] / $xenobe_fighter_price));
					$playerinfo['ship_fighters'] = $playerinfo['ship_fighters'] + $purchase;
					$output .= "purchased $purchase fighters for $playerinfo[credits] credits, ";
					$playerinfo['credits'] = 0;
				}
			}
			
			# Regenerate Armour #
			
			$maxarmor = $ship_manager->number_armour($playerinfo['armor']);
			if ($playerinfo['armor_pts'] <= ($maxarmor - 50))  // Stop regen when within 50 of max
			{
				$playerinfo['armor_pts'] = $playerinfo['armor_pts'] + round (($maxarmor - $playerinfo['armor_pts']) / 2); // Regen half of remaining armor
				$output .= "regenerated Armor to $playerinfo[armor_pts] points, ";
			}
			
			# Regenerate Torpedoes #
	
			$available_torpedoes = $ship_manager->number_torps($playerinfo['torp_launchers']) - $playerinfo['torps'];
			if (($playerinfo['credits'] > 2) && ($available_torpedoes > 0))
			{
				if (round ($playerinfo['credits'] / $xenobe_torp_price) > $available_torpedoes)
				{
					$purchase = ($available_torpedoes * $xenobe_torp_price);
					$playerinfo['credits'] = $playerinfo['credits'] - $purchase;
					$playerinfo['torps'] = $playerinfo['torps'] + $available_torpedoes;
					$output .= "purchased $available_torpedoes torpedoes for $purchase credits, ";
				}
		
				if (round ($playerinfo['credits'] / $xenobe_torp_price) <= $available_torpedoes)
				{
					$purchase = (round ($playerinfo['credits'] / $xenobe_torp_price));
					$playerinfo['torps'] = $playerinfo['torps'] + $purchase;
					$output .= "purchased $purchase torpedoes for $playerinfo[credits] credits, ";
					$playerinfo['credits'] = 0;
				}
			}
			# Update the Xenobe #
			$updated_stats = array('ship_energy'=>$playerinfo['ship_energy'],'armor_pts'=>$playerinfo['armor_pts'],'ship_fighters'=>$playerinfo['ship_fighters'],'torps'=>$playerinfo['torps'],'credits'=>$playerinfo['credits']);
			$sql_manager->updatePlayer($id,"ships",$updated_stats);
		}
	}
	#############################################################################################
	# XENOBE SCAN FOR TARGETS																	#
	#############################################################################################
	private function check_for_target($id,$sector,$email){
		/*This has been altered, as before when xenobes where targeting players they where targeting in sequence depending on the users ID. Therfore if the xenobe attacked the first player and survived, 
		it would then move on to attack the next player with less force*/
		$sql_manager = new manage_table();
		$find_me = "sector='".$sector."' AND email!='".$email."' AND  email NOT LIKE '%@xenobe' AND planet_id=0 AND ship_id > 1";
		$found_player = $sql_manager->find_player("*","ships",$find_me);
		//return $found_player;
		$potential_targets = array();
		$n = 0;
		if($found_player)
		{
			foreach ($found_player as $found_row)
			{
				/*Now build an array of targets for the xenobe to pick from*/
				array_push($potential_targets, $found_row['ship_id']);
				$n++;
			}
		}
		/*Randomly select a target*/

		$selected = array_rand($potential_targets,1);
		return $potential_targets[$selected];

	}
	#############################################################################################
	# MOVE XENOBE TO NEW SECTOR																	#
	#############################################################################################
	private function move_xenobe($id,$aggression,$turns){
		global $db_prefix,$sector_max;
		$sql_manager = new manage_table();
		$player_manager = new manage_player();
		$manage_log = new manage_log();
		/*get xenobe current sector*/
		$xenobe_data = $sql_manager->playerinfo($id,"");
		/*check current warp links, are there any? pick one at random to warp to*/
		//$link_query = ;
		$sector_links = $sql_manager->process_query("SELECT * FROM ".$db_prefix."links WHERE link_start='".$xenobe_data['sector']."'");
		$available_links = array();
		if($sector_links)
		{
			#Sector Links Detected#
			foreach ($sector_links as $row)
			{
				/*List all the links, and pick one at random*/
				array_push($available_links, $row['link_dest']);
			}
			$selected = array_rand($available_links,1);
			$target_sector = $available_links[$selected];
		}
		else
		{
			#No Sector Links Detected - PICKING RANDOM FROM THE UNIVERSE#
			$target_sector = mt_rand (1, $sector_max);
			/*We want the xenobes to have a presence everywhere, for intimidation reasons*/
		}
		/*Right target sector has been selected, now we just need to move there. check for fighters and mines*/
		/*
		To override for testing purposes, you can force a single target sector using  $target_sector = 400; for example here
		*/
		$total_sector_mines = $sql_manager->sector_defence_quantities($target_sector,'M',$id,"investigate");
		$total_sector_fighters = $sql_manager->sector_defence_quantities($target_sector,'F',$id,"investigate");

		$total_sector_fighters = (int)$total_sector_fighters;
		$total_sector_mines = (int)$total_sector_mines;
		if ($total_sector_fighters > 0 || $total_sector_mines > 0 || ($total_sector_fighters > 0 && $total_sector_mines > 0))
		{
			 if($aggression>0)
			 {
				/*Attacks sector defence*/ 
				if($sql_manager->move_ship($id,$target_sector,$turns))
				{

					$sector_manager = new manage_sector();
					if($sector_manager->sector_defence_challange($target_sector,$id))
					{
						echo "<br/>ship moved to sector<br/>";
						/*Ship in sector, got through defences - do nothing*/
						##New Log ##
						$player_manager->last_activity_stamp($id);
						$manage_log->player_log($id,17,$target_sector,'','','',"<font color='#6190a5'>Low Priority</font>","<b>Information</b>");
					}
					else
					{
						echo "<br/>Ship was destroyed<br/>";
						/*ship was destroyed trying to enter sector*/
						
						if($xenobe_data['dev_emerwarp']=="Y")
						{
							##New Log ##
							$manage_log->player_log($id,19,$target_sector,'','','',"<font color='#FF0000'><b>Extreme Priority</b></font>",'<b><font color="#FF0000">Warning</font></b>');
							$sql_manager->reset_ship($id);
						}
						else
						{
							##New Log ##
							$manage_log->player_log($id,18,$target_sector,'','','',"<font color='#FF0000'><b>Extreme Priority</b></font>",'<b><font color="#FF0000">Warning</font></b>');
							$sql_manager->reset_ship($id);
							$sql_manager->kill_ship($id);
						}
						/*Need function to remove bounty? or ignore this? only remove bounty in ship to ship or ship to planet combat situations?*/
					}
				}
				else
				{
					##not enough turns##
					/*move failed, stay in sector*/
				}
			 }
			 else
			 {
				 /*Avoids sector*/
			 }
		}
		else
		{
			if($target_sector>0)
			{
				if($sql_manager->move_ship($id,$target_sector,$turns))
				{
					$player_manager->last_activity_stamp($id);
					/*no defences sucessful move*/
				}
				else
				{
					/*move failed, stay in sector*/
				}
			}
			else
			{
				/*Avoids sector*/
			}
		}
		

	}
	
	#############################################################################################
	# PROCESS XENOBE																			#
	#############################################################################################
	public function process_xenobes(){
		global $db_prefix;
		$sql_manager = new manage_table();
		$ship_manager = new manage_ship();
		$chance_xenobe_does_somthing = 20;
		$count_xenobes = 0;
		$count_xenobes_sentinal = 0;
		$count_xenobes_sentinal_attacking = 0;
		$count_xenobes_roam = 0;
		$count_xenobes_roam_attacking = 0;
		$count_xenobes_roamandtrade = 0;
		$count_xenobes_roamandtrade_attacking = 0;
		$count_xenobes_roamandhunt = 0;
		$count_xenobes_roamandhunt_attacking = 0;
		$count_xenobes_roamandhunt_hunting = 0;
		/*First we want to lock the tables*/
		$tables_to_lock_array = array('xenobe','ships','sector_defence','player_logs','security_logs');
		$tables_to_read_array = array('zones','universe','links');
		if($sql_manager->lock_table($tables_to_lock_array,$tables_to_read_array))
		{
			/*Now the table is locked, we can have some fun with the xenobes*/
			echo "<b>SCHEDULE - XENOBE<b><br/>";
			/*Cycle through all the xenobes and do somthing with them*/
			$join_query = "SELECT * FROM ".$db_prefix."ships JOIN ".$db_prefix."xenobe WHERE email=xenobe_id and active='Y' and ship_destroyed='N' ORDER BY ship_id;";
			$xenobe_pilots = $sql_manager->process_query($join_query);
			if($xenobe_pilots)
			{
				foreach ($xenobe_pilots as $row)
				{
					/*Xenobe is alive at the beginning of this function*/
					$xenobeisdead = 'N'; 
					/*Refresh the Xenobe, but make it pay for its own upgrades out of the credits it has onboard!*/
					$this->regenerate_xenobe($row['ship_id'],$xenobeisdead);
					/*Do somthing with each Xenobe Pilot*/
					$count_xenobes++;
					if(mt_rand(1,100)>$chance_xenobe_does_somthing)
					{
	#############################################################################################
	# ORDERS = 0 = SENTINAL																		#
	#############################################################################################
	if ($row['orders'] == 0)
	{
		echo "<b>Sentinal<b><br/>";
		$count_xenobes_sentinal++;
		$target_found = $this->check_for_target($row['ship_id'],$row['sector'],$row['email']);
		if($target_found)
		{					
			/*Load target Data*/
			$target_data = $sql_manager->playerinfo($target_found,"");
			if($row['aggression']==0)
			{
				/*Do nothing*/
			}
			else if($row['aggression']==1)
			{
				if($row['ship_fighters'] > $target_data['ship_fighters'])
				{
					/*Xenobe has more fighters then the target ship... launch an attack*/
					$count_xenobes_sentinal_attacking++;
					# 	LAUNCH ATTACK 	#
					echo $ship_manager->attack_target_ship($target_found,$row['ship_id']);
					$xenobeisdead = $sql_manager->check_player_alive($row['ship_id']);
					if($xenobeisdead=='Y')
					{
						/*Xenobe dead, skip to the next xenobe*/
						continue;
					}
				}	
			}
			else if($row['aggression']==2)
			{
				/*Xenobe has decided the target shouldnt be alive, attacks without any care for its own ship*/
				$count_xenobes_sentinal_attacking++;
				# 	LAUNCH ATTACK 	#
				echo $ship_manager->attack_target_ship($target_found,$row['ship_id']);
				$xenobeisdead = $sql_manager->check_player_alive($row['ship_id']);
				if($xenobeisdead=='Y')
				{
					/*Xenobe dead, skip to the next xenobe*/
				continue;
				}
			}					
		}
	}
	#############################################################################################
	# ORDERS = 1 = ROAM																			#
	#############################################################################################
	else if ($row['orders'] == 1)
	{
		
		echo "<b>Roam<b><br/>";
		$this->move_xenobe($row['ship_id'],$row['aggression'],$row['turns']);
		$count_xenobes_roam++;
	}
	#############################################################################################
	# ORDERS = 2 = ROAM AND TRADE																#
	#############################################################################################
	else if ($row['orders'] == 2)
	{
		echo "<b>Roam And Trade<b><br/>";
		$count_xenobes_roamandtrade++;
	}
	#############################################################################################
	# ORDERS = 3 = ROAM AND HUNT																#
	#############################################################################################
	else if ($row['orders'] == 3)
	{
		echo "<b>Roam And Hunt<b><br/>";
		$count_xenobes_roamandhunt++;
	}
	#############################################################################################
	# END ORDERS																				#
	#############################################################################################
					}
				}
			}
			else
			{
				/*No Xenobes Found, they're either all dead or somthing has gone wrong!*/
			}
		}
		else
		{
			/*Somthing went wrong*/
			echo "boo boo";
		}
		/*Now unlock the tables*/
		$sql_manager = new manage_table();
		if($sql_manager->unlock_table())
		{
			/*Now we have finished, unlock the tables.*/
			echo "<b>Unlocked</b><br/>";
		}
		else
		{
			/*Somthing went wrong*/
			echo "bo bo version 2";
		}
	
	}
}

?>
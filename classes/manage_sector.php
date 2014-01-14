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
# Log Managment																				#
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
class manage_sector { 
	private $_db;
	public function __construct()
	{
		if (!session_id())
		{
			session_start();
		}
		$this->connect = db::init();
	}
	/*
	
	*/
	private function sector_fighter_engaged($playerbeams,$sector_defence_fighters,$playertorpdmg,$playerarmor,$playerfighters,$playershields,$id){
           // Beams Vs fighters
		   if ($sector_defence_fighters > 0 && $playerbeams > 0)
            {
                if ($playerbeams > round ($sector_defence_fighters / 2))
                {
                    $temp = round ($sector_defence_fighters / 2);
                    $sector_defence_fighters = $temp;
                    $playerbeams = $playerbeams - $temp;
                }
                else
                {
                    $sector_defence_fighters = $sector_defence_fighters - $playerbeams;
                    $playerbeams = 0;
                }
            }

            // Torpedoes Vs fighters
            if ($sector_defence_fighters > 0 && $playertorpdmg > 0)
            {
                if ($playertorpdmg > round ($sector_defence_fighters / 2))
                {
                    $temp = round ($sector_defence_fighters / 2);
                    $sector_defence_fighters = $temp;
                    $playertorpdmg = $playertorpdmg - $temp;
                }
                else
                {
                    $sector_defence_fighters = $sector_defence_fighters - $playertorpdmg;
                    $playertorpdmg = 0;
                }
            }

            // Fighters Vs fighters
            if ($playerfighters > 0 && $sector_defence_fighters > 0)
            {
                if ($playerfighters > $sector_defence_fighters)
                {
                    echo "You destroyed all the sector fighters<br/>";
                    $temptargfighters = 0;
                }
                else
                {
                    $temptargfighters = $sector_defence_fighters - $playerfighters;
                }

                if ($sector_defence_fighters > $playerfighters)
                {
					echo "You lost all your fighters<br/>";
                    $tempplayfighters = 0;
                }
                else
                {
                    $tempplayfighters = $playerfighters - $sector_defence_fighters;
                }

                $playerfighters = $tempplayfighters;
                $sector_defence_fighters = $temptargfighters;
            }
			
			// Fighters Vs Shields
			if($sector_defence_fighters > 0 && $playershields > 0)
			{
				if($playershields > $sector_defence_fighters)
				{
					$playershields = $playershields - $sector_defence_fighters;
					/*sector looses percentage of fighters in attack run*/
					$temp = mt_rand(1,$playershields);
					$sector_defence_fighters = $sector_defence_fighters - $temp;
				}
				else
				{
					$sector_defence_fighters = $sector_defence_fighters - $playershields;
					$playershields = 0;
				}
			}

            // There are still fighters, so armor Vs fighters
            if ($sector_defence_fighters > 0)
            {
                if ($sector_defence_fighters > $playerarmor)
                {
					/*sector looses percentage of fighters in attack run*/
					$temp = mt_rand(1,$playerarmor);
					$sector_defence_fighters = $sector_defence_fighters - $temp;
                    $playerarmor = 0;
                }
                else
                {
                    $playerarmor = $playerarmor - $sector_defence_fighters;
                }
            }
			return array($playerbeams,$sector_defence_fighters,$playertorpdmg,$playerarmor,$playerfighters,$playershields);
	}
	private function sector_minefield_engaged($playerminedeflect,$playershields,$playerarmor,$sector_defence_minefield,$id){
		if ($playerminedeflect >= $sector_defence_minefield)
		{
			$playerminedeflect = $playerminedeflect - $sector_defence_minefield;
			/*player hit all mines, but go through ok*/
		}
		else
		{
			$mines_left = $sector_defence_minefield - $playerminedeflect;
			// Shields Vs mines
			if ($playershields >= $mines_left)
			{
				$playershields = $playershields - $mines_left;
				$mines_left = 0;
			}
			else
			{
				$mines_left = $mines_left - $playershields;
				$playershields = 0;
				// Armor Vs mines
				if ($playerarmor >= $mines_left)
				{
					$playerarmor = $playerarmor - $mines_left;
					$mines_left = 0;
				}
				else
				{
					$playerarmor = 0;
				}
			}
		}
		return array($playerminedeflect,$playershields,$playerarmor,$sector_defence_minefield);
	}
	
	public function sector_defence_challange($sector,$id){
		global $db_prefix;
		global $torp_dmg_rate;
		global $level_factor;
		$sql_manager = new manage_table();
		$player_manager = new manage_player();
		$ship_manager = new manage_ship();
		$manage_log = new manage_log();
		/*load player information*/
		$player_data = $sql_manager->playerinfo($id,"");
		/*Load sector information - Team defences are removed from the quantities, so if there are any left, it must be hostile, take the apropriate action*/
		$total_sector_mines = $sql_manager->sector_defence_quantities($sector,'M',$id,"investigate");
		$total_sector_fighters = $sql_manager->sector_defence_quantities($sector,'F',$id,"investigate");
		$total_sector_fighters = (int)$total_sector_fighters;
		$total_sector_mines = (int)$total_sector_mines;
		
		
		if ($total_sector_fighters > 0 || $total_sector_mines > 0 || ($total_sector_fighters > 0 && $total_sector_mines > 0))
		{	
			##Hostile defences detected!##
			/*Mark player as in combat*/
			if($sql_manager->ship_in_sector_defence_combat($sector,$id))
			{
				##Log this##
				/*Log player engaging sector defences*/
			}
			else
			{
				##log this##
				/*Failed to update ship and set to in combat*/
			}
			##YOU HAVE ENGAGED THE SECTOR DEFENCES##
           	$sector_defence_fighters = $total_sector_fighters;
            $playerbeams = $ship_manager->number_beams($player_data['beams']);
            if ($playerbeams > $player_data['ship_energy'])
            {
				/*Player Beams Armed*/
                $playerbeams = $player_data['ship_energy'];
            }
            $player_data['ship_energy'] = $player_data['ship_energy'] - $playerbeams;
            $playershields = $ship_manager->number_shields($player_data['shields']);
            if ($playershields > $player_data['ship_energy'])
            {
				/*Player Shields Raised*/
                $playershields = $player_data['ship_energy'];
            }
            $playertorpnum = round (pow ($level_factor, $player_data['torp_launchers'])) * 2;
            if ($playertorpnum > $player_data['torps'])
            {
				/*Torpedos places in firing tubes*/
                $playertorpnum = $player_data['torps'];
            }
			/*Torpedo warheads armed*/
            $playertorpdmg = $torp_dmg_rate * $playertorpnum;
			/*polerize the armour plating*/
            $playerarmor = $player_data['armor_pts'];
			/*pilots to fighter bays*/
            $playerfighters = $player_data['ship_fighters'];
			/*fetch amount of sector mines in minefield*/
            $sector_defence_minefield = $total_sector_mines;
            if ($sector_defence_minefield > 1)
            {
				/*change of getting through minefield*/
				$roll = mt_rand(1 , $sector_defence_minefield);
            }
            else
            {
                $roll = 1;
            }
			/*total mines which are attracted to the hull of the players ship*/
            $sector_defence_minefield = $sector_defence_minefield - $roll;
			/*prepare mine deflectors for launch*/
            $playerminedeflect = $player_data['dev_minedeflector'];
			
			/*Where are the fighters? inside or outside the fighter formation?*/
			$fighter_or_mines_first = mt_rand(1 , 100);
			if($fighter_or_mines_first>50)
			{
				/*Engages the fighters first*/
				
				$fighter_results = $this->sector_fighter_engaged($playerbeams,$sector_defence_fighters,$playertorpdmg,$playerarmor,$playerfighters,$playershields,$id);
				
				$playerbeams = $fighter_results[0];
				$sector_defence_fighters = $fighter_results[1];
				$playertorpdmg = $fighter_results[2];
				$playerarmor = $fighter_results[3];
				$playerfighters = $fighter_results[4];
				$playershields = $fighter_results[5];
				if($playerarmor>0)
				{
					
					if($playerminedeflect >= $roll)
					{
						/*do nothing... players fought off the fighters, and found a hole in minefield*/
						/*ship is in sector \o/*/
						if($sql_manager->ship_in_sector_defence_combat("",$id))
						{
								###
								##
								## Update Sector Information
								##
								###
								$sql_manager->manage_sector_defences($sector,$sector_defence_fighters,$sector_defence_minefield,$id);
								###
								##
								## Update Player Stats
								##
								###
								/*Leftover torpedoes?*/
								$y = $playertorpdmg/$torp_dmg_rate;
								$torpedoes_left = $y;
								/*Leftover energy?*/
								$t = $playershields + $playerbeams;
								$energy_left = $player_data['ship_energy'] - $t;
								if($energy_left<0)
								{$energy_left = 0;}
								$updated_stats = array(
								'cleared_defences'=>' ',
								'ship_energy'=>$energy_left,
								'dev_minedeflector'=>$playerminedeflect,
								'armor_pts'=>$playerarmor,
								'ship_fighters'=>$playerfighters,
								'torps'=>$torpedoes_left
								);

								if($sql_manager->updatePlayer($id,"ships",$updated_stats))
								{
									##Log this##
									/*Log player got passed sector defences*/
									return true;
								}
								else
								{
									##Log this##
									/*Log player failed to update their stats*/
									return false;
								}
						}
						else
						{
							
							##log this##
							/*Failed to update ship setting got into sector - fail entire move*/
							return false;
						}
					}
					else
					{
						$minefield_results = $this->sector_minefield_engaged($playerminedeflect,$playershields,$playerarmor,$sector_defence_minefield,$id);
						$playerminedeflect = $minefield_results[0];
						$playershields = $minefield_results[1];
						$playerarmor = $minefield_results[2];
						$sector_defence_minefield = $minefield_results[3];
						if($playerarmor>0)
						{
							/*ship is in sector \o/*/
							if($sql_manager->ship_in_sector_defence_combat("",$id))
							{
								###
								##
								## Update Sector Information
								##
								###
								$sql_manager->manage_sector_defences($sector,$sector_defence_fighters,$sector_defence_minefield,$id);
								###
								##
								## Update Player Stats
								##
								###
								/*Leftover torpedoes?*/
								$y = $playertorpdmg/$torp_dmg_rate;
								$torpedoes_left = $y;
								/*Leftover energy?*/
								$t = $playershields + $playerbeams;
								$energy_left = $player_data['ship_energy'] - $t;
								if($energy_left<0)
								{$energy_left = 0;}
								$updated_stats = array(
								'cleared_defences'=>' ',
								'ship_energy'=>$energy_left,
								'dev_minedeflector'=>$playerminedeflect,
								'armor_pts'=>$playerarmor,
								'ship_fighters'=>$playerfighters,
								'torps'=>$torpedoes_left
								);

								if($sql_manager->updatePlayer($id,"ships",$updated_stats))
								{
									##Log this##
									/*Log player got passed sector defences*/
									return true;
								}
								else
								{
									##Log this##
									/*Log player failed to update their stats*/
									return false;
								}
							}
							else
							{
								##log this##
								/*Failed to update ship setting got into sector - fail entire move*/
								return false;
							}
						}
						else
						{
							###
							##
							## Update Sector Information
							##
							###
							
							$sql_manager->manage_sector_defences($sector,$sector_defence_fighters,$sector_defence_minefield,$id);
							//ship destroyed!
							return false;
						}
					}
				}
				else
				{
					echo "player died";
					###
					##
					## Update Sector Information
					##
					###
					$sql_manager->manage_sector_defences($sector,$sector_defence_fighters,$sector_defence_minefield,$id);
					echo "passed function";
					//ship destroyed!
					return false;
				}
			}
			else
			{
				/*Engages the minefield first*/
				if($playerminedeflect >= $roll)
				{
					/*do nothing... hole in minefield sucessfully found. Now lets go and get those fighters...*/
				}
				else
				{
					$minefield_results = $this->sector_minefield_engaged($playerminedeflect,$playershields,$playerarmor,$sector_defence_minefield,$id);
					$playerminedeflect = $minefield_results[0];
					$playershields = $minefield_results[1];
					$playerarmor = $minefield_results[2];
					$sector_defence_minefield = $minefield_results[3];
				}
				if($playerarmor>0)
				{
					$fighter_results = $this->sector_fighter_engaged($playerbeams,$sector_defence_fighters,$playertorpdmg,$playerarmor,$playerfighters,$playershields,$id);
					$playerbeams = $fighter_results[0];
					$sector_defence_fighters = $fighter_results[1];
					$playertorpdmg = $fighter_results[2];
					$playerarmor = $fighter_results[3];
					$playerfighters = $fighter_results[4];
					$playershields = $fighter_results[5];
					if($playerarmor>0)
					{
						/*ship is in sector \o/*/
						if($sql_manager->ship_in_sector_defence_combat("",$id))
						{
								###
								##
								## Update Sector Information
								##
								###
								$sql_manager->manage_sector_defences($sector,$sector_defence_fighters,$sector_defence_minefield,$id);
								###
								##
								## Update Player Stats
								##
								###
								/*Leftover torpedoes?*/
								$y = $playertorpdmg/$torp_dmg_rate;
								$torpedoes_left = $y;
								/*Leftover energy?*/
								$t = $playershields + $playerbeams;
								$energy_left = $player_data['ship_energy'] - $t;
								if($energy_left<0)
								{$energy_left = 0;}
								$updated_stats = array(
								'cleared_defences'=>' ',
								'ship_energy'=>$energy_left,
								'dev_minedeflector'=>$playerminedeflect,
								'armor_pts'=>$playerarmor,
								'ship_fighters'=>$playerfighters,
								'torps'=>$torpedoes_left
								);
								if($sql_manager->updatePlayer($id,"ships",$updated_stats))
								{
									##Log this##
									/*Log player got passed sector defences*/
									return true;
								}
								else
								{
									##Log this##
									/*Log player failed to update their stats*/
									return false;
								}
						}
						else
						{
							##log this##
							/*Failed to update ship setting got into sector - fail entire move*/
							return false;
						}
					}
					else
					{
						###
						##
						## Update Sector Information
						##
						###
						$sql_manager->manage_sector_defences($sector,$sector_defence_fighters,$sector_defence_minefield,$id);
						//ship destroyed!
						return false;
					}
				}
				else
				{
					###
					##
					## Update Sector Information
					##
					###
					$sql_manager->manage_sector_defences($sector,$sector_defence_fighters,$sector_defence_minefield,$id);
					//ship destroyed!
					return false;
				}
			}
		}
		else
		{
			/*NO sector defences, or sector defences belong to self or friend. Player in sector.*/
			return true;
		}
	}

}
?>
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
# Ship Managment																			#
#																							#
# @copyright 	2013 - David Dawson															#
# @Contact		web.developer@live.co.uk													#
# @license    	http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later					#
#############################################################################################
 
#############################################################################################	
# Notes:																					#
#																							#
# 																							#
#																							#
# 																							#
#############################################################################################
require_once($_SERVER['DOCUMENT_ROOT']."/config/config.php");
class manage_ship { 
	private $_db;
	public function __construct()
	{
		if (!session_id())
		{
			session_start();
		}
		$this->connect = db::init();
	}
	
	public function number_armour($level){
		global $level_factor;
		return round (pow ($level_factor, $level) * 100);
	}
	public function number_beams($level){
		global $level_factor;
		return round (pow ($level_factor, $level) * 100);
	}
	public function number_fighters($level){
		global $level_factor;
		return round (pow ($level_factor, $level) * 100);
	}
	public function number_holds($level){
		global $level_factor;
		return round (pow ($level_factor, $level) * 100);
	}
	public function number_shields($level){
		global $level_factor;
		return round (pow ($level_factor, $level) * 100);
	}
	public function number_torps($level){
		global $level_factor;
		return round (pow ($level_factor, $level) * 100);
	}
	public function number_energy($level){
		global $level_factor;
		return round (pow ($level_factor, $level) * 500);
	}
	public function ship_average_tech($ship_info = null, $type = "ship")
	{
			// Defined in config.php
			global $calc_ship_tech, $calc_planet_tech;
	
			if ($type == "ship")
			{
					$calc_tech = $calc_ship_tech;
			}
			else
			{
					$calc_tech = $calc_planet_tech;
			}
	
			$count = count ($calc_tech);
	
			$shipavg  = 0;
			for ($i = 0; $i < $count; $i++)
			{
					$shipavg += $ship_info[$calc_tech[$i]];
			}
			$shipavg /= $count;
	
			return $shipavg;
	}
	public function stripnum($str)
	{
		$output = preg_replace('/[^0-9]/', '', $str);
		return $output;
	}
	
	#############################################################################################
	# ENGAGE THE ENEMY																			#
	#############################################################################################
	public function attack_target_ship($victim_id,$attacker_id){
		global $db_prefix;
		global $ewd_maxhullsize;
		global $sector_max;
		global $bounty_ratio;
		global $bounty_minturns;
		global $bounty_maxvalue;
		global $level_factor;
		global $torp_dmg_rate;
		global $rating_combat_factor;
		global $upgrade_cost;
		global $upgrade_factor;
		$sql_manager = new manage_table();
		$player_manager = new manage_player();
		$manage_log = new manage_log();
		###############################################################################################
		###																							###
		###   LAUNCHING ATTACK LAUNCHING ATTACK LAUNCHING ATTACK LAUNCHING ATTACK LAUNCHING ATTACK  ###
		###																							###
		###############################################################################################

		$tables_to_lock_array = array('xenobe','sector_defence','ships','universe','bounty','planets','news','movement_log','logs','ibank_accounts','languages','zones','player_logs','security_logs');
		if($sql_manager->lock_table($tables_to_lock_array))
		{
			$victim_id = $this->stripnum($victim_id);
			$attacker_id = $this->stripnum($attacker_id);
			/* Get Player Data */
			$target_data = $sql_manager->playerinfo($victim_id,"");
			$attacker_data = $sql_manager->playerinfo($attacker_id,"");

			$playerscore = $player_manager->generate_score($attacker_id);
			$targetscore = $player_manager->generate_score($victim_id);

			$playerscore = $playerscore * $playerscore;
			$targetscore = $targetscore * $targetscore;
			
			$zone_information = $sql_manager->zone_information($target_data['sector'],'allow_attack');
			
			# stage 1 - check both are in the same sector
			if($target_data['sector']!=$attacker_data['sector'] || $target_data['on_planet']=="Y")
			{
				echo "Target not in the sector, or the target is on a planet. You sir have failed.<br/>";
				# Security Log #
				$manage_log->security_log($attacker_data['ship_id'],23,$attacker_data['ship_id'],$target_data['ship_id']);
			}
			# stage 2 - check the players have turns
			else if ($attacker_data['turns'] < 1)
			{
				echo "You have no turns to attack with. You sir have failed.<br/>";
				$manage_log->security_log($attacker_data['ship_id'],24,$attacker_data['ship_id'],$target_data['ship_id'],$attacker_data['turns']);
			}
			# stage 3 - check teams
			else if(($attacker_data['team']==$target_data['team']) and ($target_data['team']>0)) 
			{
				echo "Oi, trying to attack your friends, shame on you... SHAME ON YOU!<br/>";
				$manage_log->security_log($attacker_data['ship_id'],25,$attacker_data['ship_id'],$target_data['ship_id']);
			}
			#stage 4 - check attacking allowed in the zone
			else if($zone_information['allow_attack']=="N")
			{
				echo "This zone doesnt allow attacking. You sir have failed.<br/>";
				$manage_log->security_log($attacker_data['ship_id'],26,$attacker_data['ship_id'],$target_data['ship_id']);
			}
			#stage 5 - check if allready in combat
			else if (isset($_SESSION['in_combat']) && $_SESSION['in_combat'] === true)
			{
				echo "Whoooops, your allready attacking this ship! Refresh much? Admin has been notified.<br/>";
				## LOG THIS MULTI ATTACK ##
				$manage_log->security_log($attacker_data['ship_id'],27,$attacker_data['ship_id'],$target_data['ship_id']);
			}
			else
			{
				$manage_log->security_log($attacker_data['ship_id'],28,$attacker_data['ship_id'],$target_data['ship_id']);
					/* WE CAN ATTACK ... NO GOING BACK NOW JIM!!!*/
				if (preg_match("/(\@xenobe)$/",$target_data['email']) === 0 ) {
					/*xenobe. we dont want to be blocking the scheduler again*/
				}
				else
				{
					$_SESSION['in_combat'] = (boolean) true;
				}
				// Set In Combat Flag
				
				$success = (10 - $target_data['cloak'] + $attacker_data['sensors']) * 5;
				if ($success < 5)
				{
					$success = 5;
				}
			
				if ($success > 95)
				{
					$success = 95;
				}
				$flee = (10 - $target_data['engines'] + $attacker_data['engines']) * 5;
				if(($target_data['beams']==$attacker_data['beams']) and ($target_data['power']==$attacker_data['power']))
				{
					/*Black holes open when 2 equally matched beams are fired directly at each other with the same power amplilification*/
					$roll3 = mt_rand (1, 10000); /* chance of Accidently creating a black hole!*/
					/*to not ruin the game, there is a 0.04% chance of a black hole appearing. and then the 2 ships have to be matched in size*/
				}
				else 
				{
					$roll3 = 1;	
				}
				$roll = mt_rand (1, 100);
				$roll2 = mt_rand (1, 100);
				

				if ($flee < $roll2)
				{
					echo "Oh no they out manouvered you! UPGRADE THOSE ENGINES! <br/>";
					$attacker_data['turns'] = $attacker_data['turns'] - 1;
					$attacker_data['turns_used'] = $attacker_data['turns_used'] + 1;
					$updated_stats = array('turns'=>$attacker_data['turns'],'turns_used'=>$attacker_data['turns_used']);
					$sql_manager->updatePlayer($attacker_data['ship_id'],"ships",$updated_stats);
					$manage_log->player_log($target_data['ship_id'],4,$attacker_data['ship_id'],'','','',"<font color='#FF0000'>High Priority</font>",'<b><font color="#FF0000">Warning</font></b>');
				}
				elseif ($roll > $success)
				{
					// If scanning failed, inform the target
					echo "Oops you where unable to lock target on the enemy ship, UPGRADE THOSE SENSORS!!!!?!<br/>";
					$attacker_data['turns'] = $attacker_data['turns'] - 1;
					$attacker_data['turns_used'] = $attacker_data['turns_used'] + 1;
					$updated_stats = array('turns'=>$attacker_data['turns'],'turns_used'=>$attacker_data['turns_used']);
					$sql_manager->updatePlayer($attacker_data['ship_id'],"ships",$updated_stats);
					$manage_log->player_log($target_data['ship_id'],9,$attacker_data['ship_id'],'','','',"<font color='#FF0000'>High Priority</font>",'<b><font color="#FF0000">Warning</font></b>');
				}
				else
				{
					$shipavg =  $this->ship_average_tech($target_data, "ship");
					if ($shipavg > $ewd_maxhullsize)
					{
						$chance = ($shipavg - $ewd_maxhullsize) * 10;
					}
					else
					{
						$chance = 0;
					}
					$random_value = mt_rand (1, 100);
					
					if ($roll3>9996)
					{
						$manage_log->security_log($attacker_data['ship_id'],29,$attacker_data['ship_id'],$target_data['ship_id']);
						/*A black hole was accidently created*/
						echo "Uuuuuuuuuuuhhhhhhhh oooooohhhhhhh shsssshshshshhhhhhiiiiiii............bbbbbblllllllaaaaaccccccckkkkkkkkk kk kk kk kk kk  h    h      h     o<br/>";
						$attacker_data['turns'] = $attacker_data['turns'] - 1;
						$attacker_data['turns_used'] = $attacker_data['turns_used'] + 1;
						$updated_stats = array('turns'=>$attacker_data['turns'],'turns_used'=>$attacker_data['turns_used']);
						$sql_manager->updatePlayer($attacker_data['ship_id'],"ships",$updated_stats);
						
						# both ships suffer damage if they have EWD, otherwise both ships destroyed #
						if($target_data['dev_emerwarp'] > 0)
						{
							$dest_sector = mt_rand (1, $sector_max-1);
							$target_data['dev_emerwarp'] = $target_data['dev_emerwarp'] - 1;
							$updated_stats = array('sector'=>$dest_sector,'dev_emerwarp'=>$target_data['dev_emerwarp'],'cleared_defences'=>' ');
							$sql_manager->updatePlayer($target_data['ship_id'],"ships",$updated_stats);
							$manage_log->player_log($target_data['ship_id'],25,'','','','',"<font color='#FF0000'>High Priority</font>",'<b><font color="#FF0000">Warning</font></b>');
						}
						else
						{
							#  SHIP DESTROYED  #
							/*Escape pods are useless....*/
							$sql_manager->reset_ship($target_data['ship_id']);
							$sql_manager->kill_ship($target_data['ship_id']);
							$manage_log->player_log($target_data['ship_id'],24,'','','','',"<font color='#FF0000'><b>Extreme Priority</b></font>",'<b><font color="#FF0000">Warning</font></b>');
						}
						if($attacker_data['dev_emerwarp'] > 0)
						{
							$dest_sector = mt_rand (1, $sector_max-1);
							$rating_change = round ($attacker_data['rating'] * .1);
							$attacker_data['dev_emerwarp'] = $attacker_data['dev_emerwarp'] - 1;
							$attacker_data['turns'] = $attacker_data['turns'] - 1;
							$attacker_data['turns_used'] = $attacker_data['turns_used'] + 1;
							$updated_stats = array('sector'=>$dest_sector,'dev_emerwarp'=>$attacker_data['dev_emerwarp'],'cleared_defences'=>' ','turns'=>$attacker_data['turns'],'turns_used'=>$attacker_data['turns_used'],'rating'=>$rating_change);
							$sql_manager->updatePlayer($attacker_data['ship_id'],"ships",$updated_stats);
							$manage_log->player_log($attacker_data['ship_id'],25,'','','','',"<font color='#FF0000'>High Priority</font>",'<b><font color="#FF0000">Warning</font></b>');
						}
						else
						{
							#  SHIP DESTROYED  #
							/*Escape pods are useless....*/
							$sql_manager->reset_ship($attacker_data['ship_id']);
							$sql_manager->kill_ship($attacker_data['ship_id']);
							$manage_log->player_log($attacker_data['ship_id'],24,'','','','',"<font color='#FF0000'><b>Extreme Priority</b></font>",'<b><font color="#FF0000">Warning</font></b>');
						}
						
					}
					else if ($target_data['dev_emerwarp'] > 0 && $random_value > $chance)
        			{
						/*They did it, they hit the EWD just in time*/
						echo "they hit the EWD.... we missed them ... now to take your anger out on your weapons officer.....<br/>";
						$rating_change = round ($attacker_data['rating'] * .1);
						$dest_sector = mt_rand (1, $sector_max-1);
						$attacker_data['turns'] = $attacker_data['turns'] - 1;
						$attacker_data['turns_used'] = $attacker_data['turns_used'] + 1;
						$updated_stats = array('turns'=>$attacker_data['turns'],'turns_used'=>$attacker_data['turns_used'],'rating'=>$rating_change);
						$sql_manager->updatePlayer($attacker_data['ship_id'],"ships",$updated_stats);
						/*Move the ship that hit the EWD*/
						$target_data['dev_emerwarp'] = $target_data['dev_emerwarp'] - 1;
						$updated_stats = array('sector'=>$dest_sector,'dev_emerwarp'=>$target_data['dev_emerwarp'],'cleared_defences'=>' ');
						$sql_manager->updatePlayer($target_data['ship_id'],"ships",$updated_stats);
						$manage_log->player_log($target_data['ship_id'],6,$attacker_data['ship_id'],'','','',"<font color='#FF0000'>High Priority</font>",'<b><font color="#FF0000">Warning</font></b>');
					}
					else
					{
						/*Go forth and attack*/
						if (($targetscore / $playerscore < $bounty_ratio || $target_data['turns_used'] < $bounty_minturns) && ( preg_match("/(\@xenobe)$/",$target_data['email']) === 0 )) // Bounty-free Xenobe attacking allowed.
						{
							$btyamount = 0;
							$hasbounty = $sql_manager->get_bounty($target_data['ship_id'],0);

							if ($hasbounty['btytotal']>0)
							{
								$btyamount = $hasbounty['btytotal'];
							}
							if ($btyamount <= 0)
							{
								$bounty = round($playerscore * $bounty_maxvalue);
								$sql_manager->create_bounty($attacker_data['ship_id'],0,$bounty);
								
								# LOG BOUNTY ON PLAYER #
								echo "The Federation does not approve of you attacking a smaller ship. Your now barred from all shipyards until you pay your fine.<br/>";
								$manage_log->player_log($attacker_data['ship_id'],26,$target_data['ship_id'],$bounty,'','',"<font color='#E9AB17'>Medium Priority</font>",'<b><font color="#FF0000">Warning</font></b>');

							}
						}
						if ($target_data['dev_emerwarp'] > 0)
						{
							$manage_log->player_log($target_data['ship_id'],7,$attacker_data['ship_id'],'','','',"<font color='#FF0000'>High Priority</font>",'<b><font color="#FF0000">Warning</font></b>');
						}
						
						##############################
						##############################
						##							##
						## SETTING UP ATTACK DATA	##
						##							##
						##############################
						##############################
						
						$targetenergy = $target_data['ship_energy'];
						$playerenergy = $attacker_data['ship_energy'];
						// I added these two so we can have a value for debugging and reporting totals
						// If we use the variables in calcs below, change the display of stats too
			
						$targetbeams = $this->number_beams($target_data['beams']);
						if ($targetbeams > $target_data['ship_energy'])
						{
							$targetbeams = $target_data['ship_energy'];
						}
						$target_data['ship_energy'] = $target_data['ship_energy'] - $targetbeams;
						// Why dont we set targetinfo[ship_energy] to a variable instead?
			
						$playerbeams = $this->number_beams($attacker_data['beams']);
						if ($playerbeams > $attacker_data['ship_energy'])
						{
							$playerbeams = $attacker_data['ship_energy'];
						}
						$attacker_data['ship_energy'] = $attacker_data['ship_energy'] - $playerbeams;
			
						$playershields = $this->number_shields($attacker_data['shields']);
						if ($playershields > $attacker_data['ship_energy'])
						{
							$playershields = $attacker_data['ship_energy'];
						}
						$attacker_data['ship_energy'] = $attacker_data['ship_energy'] - $playershields;
			
						$targetshields = $this->number_shields($target_data['shields']);
						if ($targetshields > $target_data['ship_energy'])
						{
							$targetshields = $target_data['ship_energy'];
						}
						$target_data['ship_energy'] = $target_data['ship_energy'] - $targetshields;
			
						$playertorpnum = round (pow ($level_factor, $attacker_data['torp_launchers']))*10;
						if ($playertorpnum > $attacker_data['torps'])
						{
							$playertorpnum = $attacker_data['torps'];
						}
			
						$targettorpnum = round (pow ($level_factor, $target_data['torp_launchers']))*10;
						if ($targettorpnum > $target_data['torps'])
						{
							$targettorpnum = $target_data['torps'];
						}
						$playertorpdmg = $torp_dmg_rate * $playertorpnum;
						$targettorpdmg = $torp_dmg_rate * $targettorpnum;
						$playerarmor = $attacker_data['armor_pts'];
						$targetarmor = $target_data['armor_pts'];
						$playerfighters = $attacker_data['ship_fighters'];
						$targetfighters = $target_data['ship_fighters'];
						$targetdestroyed = 0;
						$playerdestroyed = 0;
						echo "You are currently attacking ".$target_data['character_name']." aboard ".$target_data['ship_name'].":<br/>";
						
						$bcs_info = null;
						$bcs_info[] = array("Beams (lvl)",        	$playerbeams." <span class=\"table_word_faded\">(".$attacker_data['beams'].")</span>",                		$targetbeams." <span class=\"table_word_faded\">(".$target_data['beams'].")</span>" );
						$bcs_info[] = array("Shields (lvl)",        	$playershields." <span class=\"table_word_faded\">(".$attacker_data['shields'].")</span>",            		$targetshields." <span class=\"table_word_faded\">(".$target_data['shields'].")</span>" );
						$bcs_info[] = array("Energy (Start)",    	$attacker_data['ship_energy']." <span class=\"table_word_faded\">(".$playerenergy.")</span>",        		$target_data['ship_energy']." <span class=\"table_word_faded\">(".$targetenergy.")</span>" );
						$bcs_info[] = array("Torps (lvl)",        	$playertorpnum." <span class=\"table_word_faded\">(".$attacker_data['torp_launchers'].")</span>",    		$targettorpnum." <span class=\"table_word_faded\">(".$target_data['torp_launchers'].")</span>" );
						$bcs_info[] = array("TorpDmg",            	$playertorpdmg,                                        				$targettorpdmg );
						$bcs_info[] = array("Fighters",            	$playerfighters,                                    				$targetfighters );
						$bcs_info[] = array("Armor (lvl)",        	$playerarmor." <span class=\"table_word_faded\">(".$attacker_data['armor'].")</span>",                		$targetarmor." <span class=\"table_word_faded\">(".$target_data['beams'].")</span>" );
						$bcs_info[] = array("Escape Pod",        	$attacker_data['dev_escapepod'],                        			$target_data['dev_escapepod'] );

	/*
	Build table
	*/	
	?>
    <div class="general-table-container">
    <?
	echo "<table><tbody>";
	echo "<tr>";
	echo "<td>Stats</td>";
	echo "<td>You [<span class=\"table_word_blue\">".$attacker_data['character_name']."</span>]</td>";
	echo "<td>Target [<span class=\"table_word_orange\">".$target_data['character_name']."</span>]</td>";
	echo "</tr>";
			
	for ($bcs_index=0; $bcs_index<count($bcs_info); $bcs_index++)
	{
		echo "  <tr>";
		echo "    <td>".$bcs_info[$bcs_index][0]."</td>";
		echo "    <td>".$bcs_info[$bcs_index][1]."</td>";
		echo "    <td>".$bcs_info[$bcs_index][2]."</td>";
		echo "  </tr>";
	}
	echo "</tbody></table>";
	echo "<table><tbody>";
	echo "<tr>";
	echo "<td colspan=\"2\">Target Locked</td>";
	echo "</tr>";
	echo "<tr><td class=\"ship-attack-title\">Firing Beams</td>";
	echo "<td>";
	$bcs_stats_info = false;
	if ($targetfighters > 0 && $playerbeams > 0)
	{
		$bcs_stats_info = true;
		if ($playerbeams > round($targetfighters / 2))
		{
			$temp = round ($targetfighters / 2);
			$lost = $targetfighters - $temp;
			//Maybe we should report on how many beams fired , etc for comparision/bugtracking
			echo $target_data['character_name']." lost ".$lost." fighters<br/>";
			$targetfighters = $temp;
			$playerbeams = $playerbeams - $lost;
		}
		else
		{
			$targetfighters = $targetfighters - $playerbeams;
			echo $target_data['character_name']." lost ".$playerbeams." fighters<br/>";
			$playerbeams = 0;
		}
	}
			
	if ($playerfighters > 0 && $targetbeams > 0)
	{
		$bcs_stats_info = true;
		if ($targetbeams > round ($playerfighters / 2))
		{
			$temp = round ($playerfighters / 2);
			$lost = $playerfighters - $temp;
			echo "You lost ".$lost." fighters<br>";
			$playerfighters = $temp;
			$targetbeams = $targetbeams - $lost;
		}
		else
		{
			$playerfighters = $playerfighters - $targetbeams;
			echo "You lost ".$targetbeams." fighters<br>";
			$targetbeams = 0;
		}
	}
	
	if ($playerbeams > 0)
	{
		$bcs_stats_info = true;
		if ($playerbeams > $targetshields)
		{
			$playerbeams = $playerbeams - $targetshields;
			$targetshields = 0;
			echo $target_data['character_name']."'s shields are down!<br>";
		}
		else
		{
			echo $target_data['character_name']."'s shields are hit for ".$playerbeams." damage.<br>";
			$targetshields = $targetshields - $playerbeams;
			$playerbeams = 0;
		}
	}
	if ($targetbeams > 0)
	{
		$bcs_stats_info = true;
		if ($targetbeams > $playershields)
		{
			$targetbeams = $targetbeams - $playershields;
			$playershields = 0;
			echo "Your shields are down!<br/>";
		}
		else
		{
			echo "Your shields are hit for ".$targetbeams." damage.<br/>";
			$playershields = $playershields - $targetbeams;
			$targetbeams = 0;
		}
	}
	if ($playerbeams > 0)
	{
		$bcs_stats_info = true;
		if ($playerbeams > $targetarmor)
		{
			$targetarmor = 0;
			echo $target_data['character_name']."'s armor breached!<br/>";
		}
		else
		{
			$targetarmor = $targetarmor - $playerbeams;
			echo $target_data['character_name']."'s armor is hit for ".$playerbeams." damage.<br/>";
		}
	}
	if ($targetbeams > 0)
	{
		$bcs_stats_info = true;
		if ($targetbeams > $playerarmor)
		{
			$playerarmor = 0;
			echo "Your armor has been breached!" . "<br/>";
		}
		else
		{
			$playerarmor = $playerarmor - $targetbeams;
			echo "Your armor is hit for $targetbeams damage.<br>";
		}
	}
	if ($bcs_stats_info == false)
	{
		echo "No information available.<br>";
	}
	echo "  </td></tr>";
	echo "<tr><td class=\"ship-attack-title\">Launching Torpedos</td>";
	echo "<td>";
	$bcs_stats_info = false;
		if ($targetfighters > 0 && $playertorpdmg > 0)
		{
			$bcs_stats_info = true;
			if ($playertorpdmg > round($targetfighters / 2))
			{
				$temp = round ($targetfighters / 2);
				$lost = $targetfighters - $temp;
				echo $target_data['character_name']." lost ".$lost." fighters<br/>";
				$targetfighters = $temp;
				$playertorpdmg = $playertorpdmg - $lost;
			}
			else
			{
				$targetfighters = $targetfighters - $playertorpdmg;
				echo $target_data['character_name']." lost ".$playertorpdmg." fighters<br/>";
				$playertorpdmg = 0;
			}
		}
		if ($playerfighters > 0 && $targettorpdmg > 0)
		{
			$bcs_stats_info = true;
			if ($targettorpdmg > round($playerfighters / 2))
			{
				$temp = round($playerfighters / 2);
				$lost = $playerfighters - $temp;
				echo "You lost ".$lost." fighters<br/>";
				echo "$temp - $playerfighters - $targettorpdmg";
				$playerfighters = $temp;
				$targettorpdmg = $targettorpdmg - $lost;
			}
			else
			{
				$playerfighters = $playerfighters - $targettorpdmg;
				echo "You lost ".$targettorpdmg." fighters<br/>";
				$targettorpdmg = 0;
			}
		}
		if ($playertorpdmg > 0)
		{
			$bcs_stats_info = true;
			if ($playertorpdmg > $targetarmor)
			{
				$targetarmor=0;
				echo $target_data['character_name']."'s armor breached!<br/>";
			}
			else
			{
				$targetarmor = $targetarmor - $playertorpdmg;
				echo $target_data['character_name']."'s armor is hit for " .$playertorpdmg." damage.<br/>";
			}
		}
		if ($targettorpdmg > 0)
		{
			$bcs_stats_info = true;
			if ($targettorpdmg > $playerarmor)
			{
				$playerarmor = 0;
				echo "Your armor has been breached!" . "<br/>";
			}
			else
			{
				$playerarmor = $playerarmor - $targettorpdmg;
				echo "Your armor is hit for ".$targettorpdmg." damage.<br/>";
			}
		}
		if ($bcs_stats_info == false)
		{
			echo "No information available.<br>";
		}
					
		echo "</tr></td>";
		echo "<tr><td class=\"ship-attack-title\">Fighter have been launched</td>";
		echo "<td>";
		$bcs_stats_info = false;
		if ($playerfighters > 0 && $targetfighters > 0)
		{
			$bcs_stats_info = true;
			if ($playerfighters > $targetfighters)
			{
				echo $target_data['character_name']." Lost all their fighters<br/>";
				$temptargfighters = 0;
			}
			else
			{
				echo $target_data['character_name']." lost ".$playerfighters." fighters.<br/>";
				$temptargfighters = $targetfighters - $playerfighters;
			}
			if ($targetfighters > $playerfighters)
			{
				echo "You lost all fighters.<br/>";
				$tempplayfighters = 0;
			}
			else
			{
				echo "You lost ".$targetfighters." fighters.<br/>";
				$tempplayfighters = $playerfighters - $targetfighters;
			}
			$playerfighters = $tempplayfighters;
			$targetfighters = $temptargfighters;
		}
		if ($playerfighters > 0)
		{
			$bcs_stats_info = true;
			if ($playerfighters > $targetarmor)
			{
				$targetarmor = 0;
				echo $target_data['character_name']."'s armor breached!<br/>";
			}
			else
			{
				$targetarmor = $targetarmor - $playerfighters;
				echo $target_data['character_name']."'s armor is hit for ". $playerfighters." damage.<br/>";
			}
		}
		if ($targetfighters > 0)
		{
			$bcs_stats_info = true;
			if ($targetfighters > $playerarmor)
			{
				$playerarmor = 0;
				echo "Your armor has been breached!" . "<br/>";
			}
			else
			{
				$playerarmor = $playerarmor - $targetfighters;
				echo "Your armor is hit for ".$targetfighters." damage.<br/>";
			}
		}
		if ($bcs_stats_info == false)
		{
			echo "No information available.<br>";
		}
	echo " </td></tr></tbody></table>";
	echo "<table><tbody><tr><td>Results</td></tr><tr><td>";			
						if ($targetarmor < 1)
						{
							######################################
							### 							   ###
							##									##
							##			ENEMY DESTROYED			##
							##									##
							###								   ###
							######################################
							echo $target_data['character_name']."'s ship has been destroyed.<br/>";
							if ($target_data['dev_escapepod'] == "Y")
							{
								#LOG ATTACK LOOSE ESCAPE
								$rating = round ($target_data['rating'] / 2 );
								echo "Escape pod was launch detected! <span class=\"table_word_blue\">You destroyed their ship but they got away in their Escape Pod</span><br/>";
								$sql_manager->reset_ship($target_data['ship_id']);
								$sql_manager->collect_bounty($attacker_data['ship_id'], $target_data['ship_id']);
								/*Reload credits just in case user collected a bounty*/
								$current_credits = $sql_manager->check_credits($attacker_data['ship_id']);
								$attacker_data['credits'] = $current_credits['credits'];
								$manage_log->player_log($target_data['ship_id'],10,$attacker_data['ship_id'],'Y','','',"<font color='#FF0000'><b>Extreme Priority</b></font>",'<b><font color="#FF0000">Warning</font></b>');
							}
							else
							{
								# LOG ATTACK #
								$sql_manager->reset_ship($target_data['ship_id']);
								$sql_manager->kill_ship($target_data['ship_id']);
								$sql_manager->collect_bounty($attacker_data['ship_id'], $target_data['ship_id']);
								$current_credits = $sql_manager->check_credits($attacker_data['ship_id']);
								$attacker_data['credits'] = $current_credits['credits'];
								$manage_log->player_log($target_data['ship_id'],10,$attacker_data['ship_id'],'N','','',"<font color='#FF0000'><b>Extreme Priority</b></font>",'<b><font color="#FF0000">Warning</font></b>');
							}
			
							if ($playerarmor > 0)
							{
							######################################
							### 							   ###
							##									##
							##			YOU SURVIVED			##
							##									##
							###								   ###
							######################################
								$rating_change = round ($target_data['rating'] * $rating_combat_factor);
								// Updating to always get a positive rating increase for xenobe and the credits they are carrying
								$salv_credits = 0;
			
								// Double Death Attack Bug Fix - Returns 0 for real players, 1 for Xenobe players
								
								if ( preg_match("/(\@xenobe)$/", $target_data['email']) !== 0 ) // He is a Xenobe
								{
									$sql_manager->disable_xenobe($target_data['email']);

									if ($rating_change > 0)
									{
										$rating_change = 0 - $rating_change;
										$manage_log->player_log($target_data['ship_id'],10,$attacker_data['ship_id'],'N','','',"<font color='#FF0000'><b>Extreme Priority</b></font>",'<b><font color="#FF0000">Warning</font></b>');
										$salv_credits = $target_data['credits']; # BUG FIX copy the credits before killing ship*/	
										$sql_manager->reset_ship($target_data['ship_id']);
										$sql_manager->kill_ship($target_data['ship_id']);
										$sql_manager->collect_bounty($attacker_data['ship_id'], $target_data['ship_id']);
										$current_credits = $sql_manager->check_credits($attacker_data['ship_id']);
										$attacker_data['credits'] = $current_credits['credits'];
			
									}
									else
									{
										$salv_credits = $target_data['credits'];
									}
								}
								$salvage_percentage = mt_rand(1,10);
								$free_ore = round ($target_data['ship_ore'] / $salvage_percentage );
								$free_organics = round ($target_data['ship_organics'] / $salvage_percentage );
								$free_goods = round ($target_data['ship_goods'] / $salvage_percentage );
								$free_holds = $this->number_holds($attacker_data['hull']) - $attacker_data['ship_ore'] - $attacker_data['ship_organics'] - $attacker_data['ship_goods'] - $attacker_data['ship_colonists'];
								if ($free_holds > $free_goods)
								{
									$salv_goods = $free_goods;
									$free_holds = $free_holds - $free_goods;
								}
								elseif ($free_holds > 0)
								{
									$salv_goods = $free_holds;
									$free_holds = 0;
								}
								else
								{
									$salv_goods = 0;
								}
								if ($free_holds > $free_ore)
								{
									$salv_ore = $free_ore;
									$free_holds = $free_holds - $free_ore;
								}
								elseif ($free_holds > 0)
								{
									$salv_ore = $free_holds;
									$free_holds = 0;
								}
								else
								{
									$salv_ore = 0;
								}
								if ($free_holds > $free_organics)
								{
									$salv_organics = $free_organics;
									$free_holds = $free_holds - $free_organics;
								}
								elseif ($free_holds > 0)
								{
									$salv_organics = $free_holds;
									$free_holds = 0;
								}
								else
								{
									$salv_organics = 0;
								}
								$ship_value = $upgrade_cost*(round (pow ($upgrade_factor, $target_data['hull']))+round (pow ($upgrade_factor, $target_data['engines']))+round (pow ($upgrade_factor, $target_data['power']))+round (pow ($upgrade_factor, $target_data['computer']))+round (pow ($upgrade_factor, $target_data['sensors']))+round (pow ($upgrade_factor, $target_data['beams']))+round (pow ($upgrade_factor, $target_data['torp_launchers']))+round (pow ($upgrade_factor, $target_data['shields']))+round (pow ($upgrade_factor, $target_data['armor']))+round (pow ($upgrade_factor, $target_data['cloak'])));
								$ship_salvage_rate = mt_rand (10, 20);
								$ship_salvage = $ship_value * $ship_salvage_rate / 100 + $salv_credits;  // Added credits for xenobe - 0 if normal player
								
								echo "You salvaged <span class=\"table_word_green\">".$salv_ore."</span> units of ore,<span class=\"table_word_green\"> ".$salv_organics."</span> units of organics, <span class=\"table_word_green\">".$salv_goods."</span> units of goods, and salvaged <span class=\"table_word_yellow\">".$ship_salvage_rate."</span>% of the ship for <span class=\"table_word_yellow\">".$ship_salvage."</span> credits.<br/>Your rating changed by ".NUMBER(abs($rating_change))." points.<br/>";
								$armor_lost = $attacker_data['armor_pts'] - $playerarmor;
								$fighters_lost = $attacker_data['ship_fighters'] - $playerfighters;
								$attacker_data['ship_ore'] = $attacker_data['ship_ore'] +$salv_ore;
								$attacker_data['ship_organics'] = $attacker_data['ship_organics'] +$salv_organics;
								$attacker_data['ship_goods'] = $attacker_data['ship_goods'] +$salv_goods;
								$attacker_data['credits'] = $attacker_data['credits'] +$ship_salvage;
								$attacker_data['ship_energy'] = $attacker_data['ship_energy'];
								$attacker_data['ship_fighters'] = $attacker_data['ship_fighters'] - $fighters_lost;
								$attacker_data['armor_pts'] = $attacker_data['armor_pts'] - $armor_lost;
								$attacker_data['torps'] = $attacker_data['torps'] - $playertorpnum;
								$attacker_data['turns'] = $attacker_data['turns'] - 1;
								$attacker_data['turns_used'] = $attacker_data['turns_used'] + 1;
								$attacker_data['rating'] = $attacker_data['rating'] - $rating_change;

								$updated_stats = array('rating'=>$attacker_data['rating'],'turns_used'=>$attacker_data['turns_used'],'turns'=>$attacker_data['turns'],'torps'=>$attacker_data['torps'],'armor_pts'=>$attacker_data['armor_pts'],'ship_fighters'=>$attacker_data['ship_fighters'],'ship_energy'=>$attacker_data['ship_energy'],'ship_ore'=>$attacker_data['ship_ore'],'ship_organics'=>$attacker_data['ship_organics'],'ship_goods'=>$attacker_data['ship_goods'],'credits'=>$attacker_data['credits']);
								$sql_manager->updatePlayer($attacker_data['ship_id'],"ships",$updated_stats);
								
								echo "You lost <span class=\"table_word_red\">".$armor_lost."</span> Armour, <span class=\"table_word_red\">".$fighters_lost."</span> Fighters, and used <span class=\"table_word_red\">".$playertorpnum."</span> Torpedoes.<br/>";
							}
						}
						else
						{
							######################################
							### 							   ###
							##									##
							##			ENEMY SURVIVED			##
							##									##
							###								   ###
							######################################
							
							echo "You did not destory ".$target_data['character_name']."'s ship.<br>";
			
							$rating_change = round ($target_data['rating'] * .1 );
							$armor_lost = $target_data['armor_pts'] - $targetarmor;
							$fighters_lost = $target_data['ship_fighters'] - $targetfighters;
							$energy = $target_data['ship_energy'];
							$manage_log->player_log($target_data['ship_id'],27,$attacker_data['ship_id'],$armor_lost,$fighters_lost,'',"<font color='#FF0000'><b>Extreme Priority</b></font>",'<b><font color="#FF0000">Warning</font></b>');

							$target_data['ship_energy'] = $target_data['ship_energy'];
							$target_data['ship_fighters'] = $target_data['ship_fighters'] - $fighters_lost;
							$target_data['armor_pts'] = $target_data['armor_pts'] - $armor_lost;
							$target_data['torps'] = $target_data['torps'] - $targettorpnum;
							$updated_stats = array('ship_energy'=>$target_data['ship_energy'],'ship_fighters'=>$target_data['ship_fighters'],'armor_pts'=>$target_data['armor_pts'],'torps'=>$target_data['torps']);
							$sql_manager->updatePlayer($target_data['ship_id'],"ships",$updated_stats);
							
			
							$armor_lost = $attacker_data['armor_pts'] - $playerarmor;
							$fighters_lost = $attacker_data['ship_fighters'] - $playerfighters;
							$energy = $attacker_data['ship_energy'];
							$attacker_data['ship_energy'] = $energy;
							$attacker_data['ship_fighters'] = $attacker_data['ship_fighters'] -$fighters_lost;
							$attacker_data['armor_pts'] = $attacker_data['armor_pts'] -$armor_lost;
							$attacker_data['torps'] = $attacker_data['torps'] -$playertorpnum;
							$attacker_data['turns'] = $attacker_data['turns'] - 1;
							$attacker_data['turns_used'] = $attacker_data['turns_used'] + 1;
							$attacker_data['rating'] = $attacker_data['rating'];
							$updated_stats = array('ship_energy'=>$attacker_data['ship_energy'],'ship_fighters'=>$attacker_data['ship_fighters'],'armor_pts'=>$attacker_data['armor_pts'],'torps'=>$attacker_data['torps'],'turns'=>$attacker_data['turns'],'turns_used'=>$attacker_data['turns_used'],'rating'=>$attacker_data['rating']);
							$sql_manager->updatePlayer($attacker_data['ship_id'],"ships",$updated_stats);
							
							echo "You lost <span class=\"table_word_red\">".$armor_lost."</span> armour, <span class=\"table_word_red\">".$fighters_lost."</span> Fighters, and used <span class=\"table_word_red\">".$playertorpnum."</span> Torpedoes.<br/>";
						}
			
						if ($playerarmor < 1)
						{
							######################################
							### 							   ###
							##									##
							##			YOU LOST... WHAT!		##
							##									##
							###								   ###
							######################################
							echo "Your ship has been destroyed!<br/>";
							if ($attacker_data['dev_escapepod'] == "Y")
							{
								echo "Luckily you have an escape pod!<br/>";
								$sql_manager->reset_ship($attacker_data['ship_id']);
								## LOG EVENT ##
								$sql_manager->collect_bounty($target_data['ship_id'], $attacker_data['ship_id']);
								$current_credits = $sql_manager->check_credits($target_data['ship_id']);
								$target_data['credits'] = $current_credits['credits'];
								$manage_log->player_log($attacker_data['ship_id'],28,$target_data['ship_id'],'Y','','',"<font color='#FF0000'><b>Extreme Priority</b></font>",'<b><font color="#FF0000">Warning</font></b>');
							}
							else
							{
								echo "You didnt have an escape Pod, sadly your ice cold corpse can be seen floating in space....<br/>";
								$sql_manager->reset_ship($attacker_data['ship_id']);
								$sql_manager->kill_ship($attacker_data['ship_id']);
								$sql_manager->collect_bounty($target_data['ship_id'], $attacker_data['ship_id']);
								$current_credits = $sql_manager->check_credits($target_data['ship_id']);
								$target_data['credits'] = $current_credits['credits'];
								$manage_log->player_log($attacker_data['ship_id'],28,$target_data['ship_id'],'N','','',"<font color='#FF0000'><b>Extreme Priority</b></font>",'<b><font color="#FF0000">Warning</font></b>');
							}
			
							if ($targetarmor > 0)
							{
								$salvage_percentage = mt_rand(1,10);
								$free_ore = round ($attacker_data['ship_ore'] / $salvage_percentage);
								$free_organics = round ($attacker_data['ship_organics']/$salvage_percentage);
								$free_goods = round ($attacker_data['ship_goods']/$salvage_percentage);
								$free_holds = $this->number_holds($target_data['hull']) - $target_data['ship_ore'] - $target_data['ship_organics'] - $target_data['ship_goods'] - $target_data['ship_colonists'];
								if ($free_holds > $free_goods)
								{
									$salv_goods = $free_goods;
									$free_holds = $free_holds - $free_goods;
								}
								elseif ($free_holds > 0)
								{
									$salv_goods = $free_holds;
									$free_holds = 0;
								}
								else
								{
									$salv_goods = 0;
								}
			
								if ($free_holds > $free_ore)
								{
									$salv_ore = $free_ore;
									$free_holds = $free_holds - $free_ore;
								}
								elseif ($free_holds > 0)
								{
									$salv_ore = $free_holds;
									$free_holds = 0;
								}
								else
								{
									$salv_ore = 0;
								}
			
								if ($free_holds > $free_organics)
								{
									$salv_organics = $free_organics;
									$free_holds = $free_holds - $free_organics;
								}
								elseif ($free_holds > 0)
								{
									$salv_organics = $free_holds;
									$free_holds = 0;
								}
								else
								{
									$salv_organics = 0;
								}
								$ship_value = $upgrade_cost*(round (pow ($upgrade_factor, $attacker_data['hull']))+round (pow ($upgrade_factor, $attacker_data['engines']))+round (pow ($upgrade_factor, $attacker_data['power']))+round (pow ($upgrade_factor, $attacker_data['computer']))+round (pow ($upgrade_factor, $attacker_data['sensors']))+round (pow ($upgrade_factor, $attacker_data['beams']))+round (pow ($upgrade_factor, $attacker_data['torp_launchers']))+round (pow ($upgrade_factor, $attacker_data['shields']))+round (pow ($upgrade_factor, $attacker_data['armor']))+round (pow ($upgrade_factor, $attacker_data['cloak'])));
								$ship_salvage_rate = mt_rand (10, 20);
								$ship_salvage = $ship_value * $ship_salvage_rate / 100 + $salv_credits;  // Added credits for xenobe - 0 if normal player
			
								echo $target_data['character_name']." salvaged <span class=\"table_word_green\">".$salv_ore."</span> units of ore, <span class=\"table_word_green\">".$salv_organics."</span> units of organics, <span class=\"table_word_green\">".$salv_goods."</span> units of goods, and salvaged <span class=\"table_word_yellow\">".$ship_salvage_rate."</span>% of the ship for <span class=\"table_word_yellow\">".$ship_salvage."</span> credits<br/>";
								
								$target_data['credits'] = $target_data['credits'] +$ship_salvage;
								$target_data['ship_ore'] = $target_data['ship_ore'] +$salv_ore;
								$target_data['ship_organics'] = $target_data['ship_organics'] +$salv_organics;
								$target_data['ship_goods'] = $target_data['ship_goods'] +$salv_goods;
								$target_data['armor_pts'] = $target_data['armor_pts'] - $targetarmor;
								$target_data['ship_fighters'] = $target_data['ship_fighters'] - $targetfighters;
								$target_data['ship_energy'] = $target_data['ship_energy'];
								$target_data['torps'] = $target_data['torps'] - $targettorpnum;
								$updated_stats = array('credits'=>$target_data['ship_energy'],'ship_ore'=>$target_data['ship_ore'],'ship_organics'=>$target_data['ship_organics'],'ship_goods'=>$target_data['ship_goods'],'armor_pts'=>$target_data['armor_pts'],'ship_fighters'=>$target_data['ship_fighters'],'ship_energy'=>$target_data['ship_energy'],'torps'=>$target_data['torps']);
								$sql_manager->updatePlayer($target_data['ship_id'],"ships",$updated_stats);
							}
						}
			

						echo "</td></td></tbody></table></div>";

						## END OF ATTACK ##
					}
				}
			}
		}
		else
		{
			/*Error*/
		}
		/*Now unlock the tables*/
		$sql_manager = new manage_table();
		if($sql_manager->unlock_table())
		{
			/*Now we have finished, unlock the tables.*/
		}
		else
		{
			/*Somthing went wrong*/
		}
		$_SESSION['in_combat'] = (boolean) false;
	}
}
?>
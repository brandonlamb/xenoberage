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
# Table Managment																			#
#																							#
# @copyright 	2013 - David Dawson															#
# @Contact		web.developer@live.co.uk													#
# @license    	http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later					#
#############################################################################################
 
#############################################################################################	
# Notes:																					#
#																							#
# 				ADD IN LOGGING FOR WHEN FUNCTIONS FAIL										#
#																							#
# 																							#
#############################################################################################
require_once($_SERVER['DOCUMENT_ROOT']."/config/config.php");
class manage_table { 
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
	# LOCK ANY TABLE																			#
	#																							#
	#############################################################################################
	public function lock_table($table_write_names,$table_read_names){
		global $db_prefix;
		$manage_log = new manage_log();
		$table_sql_input = "";
		foreach ($table_write_names as &$table) {
			$table_sql_input .= $db_prefix.$table." WRITE,";
		}
		foreach ($table_read_names as &$table) {
			$table_sql_input .= $db_prefix.$table." READ,";
		}
		$table_sql_input = substr($table_sql_input, 0, -1); /*FIX: Remove the last added comma*/
		$lock_table = $this->connect->query("LOCK TABLES ".$table_sql_input.";");
		if($lock_table->execute())
		{
			return true;
		}
		else
		{
			/*Only need to log a failure!*/
			$manage_log->security_log(0,14,$lock_table->errorInfo());
			return false;
		}

	}
	#############################################################################################
	# UNLOCK TABLES																				#
	#																							#
	#############################################################################################
	public function unlock_table(){
		$manage_log = new manage_log();
		$unlock_table = $this->connect->query("UNLOCK TABLES;");
		if($unlock_table->execute())
		{
			return true;
		}
		else
		{
			/*Only need to log a failure*/
			$manage_log->security_log(0,15,$unlock_table->errorInfo());
			return false;
		}
	}
	#############################################################################################
	# PROCESS QUERY																				#
	#																							#
	#																							#
	# WARNING ANYTHING USING THIS FUNCTION NEEDS TO BE CONVERTED TO A STRICTER CLASS			#
	#############################################################################################
	public function process_query($query){
		/*
		$query - the query to be run
		*/
		$result = $this->connect->query($query);
		$result->execute();
		return $result->fetchAll(PDO::FETCH_ASSOC);
		
	}
	#############################################################################################
	# GET ZONE INFORMATION																		#
	#																							#
	#############################################################################################
	public function zone_information($sector,$option){
		global $db_prefix;
		
		$result = $this->connect->query("SELECT ".$option.",".$db_prefix."universe.zone_id FROM ".$db_prefix."zones,".$db_prefix."universe WHERE sector_id=".$sector." AND ".$db_prefix."zones.zone_id=".$db_prefix."universe.zone_id");
		return $result->fetch();
	}
	#############################################################################################
	# GET TEAM ID																		#
	#																							#
	#############################################################################################
	public function team_id($user_id){
		global $db_prefix;		
		$result = $this->connect->query("SELECT team FROM ".$db_prefix."ships WHERE ship_id=".$user_id."");
		$team = $result->fetch();
		return $team['team'];
	}
	#############################################################################################
	# UPDATE SHIP IN COMBAT / COMBAT ENDED																			#
	#																							#
	#############################################################################################
	public function ship_in_sector_defence_combat($sector_id,$ship_id){
		
		global $db_prefix;
		$manage_log = new manage_log();
		if($sector_id > 0)
		{
			$challange = $this->connect->prepare("UPDATE ".$db_prefix."ships SET cleared_defences= ? WHERE ship_id='".$ship_id."'");
			$challange->bindParam(1, $sector_id, PDO::PARAM_INT);
			if($challange->execute())
			{
				/*Ship has engaged the sector defences in sector X*/
				$manage_log->security_log($ship_id,16,$sector_id);
				return true;										
			}
			else
			{
				$manage_log->security_log(0,18,$challange->errorInfo());
				return false;
			}
		}
		else
		{
			$o = "Y";
			$challange = $this->connect->prepare("UPDATE ".$db_prefix."ships SET cleared_defences= ? WHERE ship_id='".$ship_id."'");	
			$challange->bindParam(1, $o, PDO::PARAM_STR);	
			if($challange->execute())
			{
				/*Ship has passed through sector defences*/
				$manage_log->security_log($ship_id,17);
				return true;											
			}
			else
			{
				$manage_log->security_log(0,19,$challange->errorInfo());
				return false;
			}
		}
	}
	#############################################################################################
	# GET SECTOR DEFENCES INFORMATION															#
	#																							#
	#############################################################################################
	public function sector_defence_quantities($sector,$defence,$user_id,$mode){
		global $db_prefix;
		if($mode=="own")
		{
			## User is checking their own sector defence report! ##
			$result = $this->connect->query("SELECT * FROM ".$db_prefix."sector_defence WHERE sector_id=".$sector." and defence_type ='".$defence."' and ship_id='".$user_id."' ORDER BY quantity DESC");
			$result = $result->fetchAll(PDO::FETCH_ASSOC);
			$total = 0;
			if($result)
			{
				foreach ($result as $row)
				{
					$total = $total + $row['quantity'];
				}
			}
		}
		else
		{
			## Investigation a sector ##
			$user_team = $this->team_id($user_id);
			$result = $this->connect->query("SELECT * FROM ".$db_prefix."sector_defence WHERE sector_id=".$sector." and defence_type ='".$defence."' and ship_id!='".$user_id."' ORDER BY quantity DESC");
			$result = $result->fetchAll(PDO::FETCH_ASSOC);
			$total = 0;
			if($result)
			{
				foreach ($result as $row)
				{
					if($user_team==0)
					{
						/*Your not a member of a team!*/
						$total = $total + $row['quantity'];
					}
					else if($user_team==$this->team_id($row['ship_id']))
					{
						/* Your part of the same team! Your not into attacking friends are you? */
					}
					else
					{
						/*Ohhh we can attack them defences!*/
						$total = $total + $row['quantity'];
					}
				}
			}
		}
		return $total;
	}
	#############################################################################################
	# GET ALL PLAYER INFORMATION																#
	#																							#
	#############################################################################################
	public function playerinfo($id,$select){
		global $db_prefix;
		if($select=="")
		{
			$result = $this->connect->query("SELECT * FROM ".$db_prefix."ships WHERE ship_id='".$id."'");
		}
		else
		{
			$result = $this->connect->query("SELECT ".$select." FROM ".$db_prefix."ships WHERE ship_id='".$id."'");
		}

		return $result->fetch();
	}
	#############################################################################################
	# FIND PLAYER																				#
	#																							#
	#############################################################################################
	public function find_player($find,$table,$where){
		global $db_prefix;
		if($where=="")
		{
			$result = $this->connect->query("SELECT * FROM ".$db_prefix.$table." WHERE ".$where."");
		}
		else
		{
			$result = $this->connect->query("SELECT ".$find." FROM ".$db_prefix.$table." WHERE ".$where."");
		}

		return $result->fetchAll(PDO::FETCH_ASSOC);
	}
	#############################################################################################
	# CHECK PLAYER ALIVE																		#
	#																							#
	#############################################################################################
	public function check_player_alive($id){
		global $db_prefix;
		$result = $this->connect->query("SELECT ship_destroyed FROM ".$db_prefix."ships WHERE ship_id='".$id."'");
		$is_alive = $result->fetch();
		return $is_alive['ship_destroyed'];
	}
	#############################################################################################
	# GET USER ID																				#
	#																							#
	#############################################################################################
	public function find_player_userid($search_criteria){
		global $db_prefix;
		$result = $this->connect->query("SELECT ship_id FROM ".$db_prefix."ships WHERE email='".$search_criteria."'");
		return $result->fetch();
	}
	#############################################################################################
	# GET FEDERATION BOUNTY																		#
	#																							#
	#############################################################################################
	public function get_bounty($target_id,$placed_by){
		global $db_prefix;

			$result = $this->connect->query("SELECT SUM(amount) AS btytotal FROM ".$db_prefix."bounty WHERE bounty_on = ".$target_id." AND placed_by = ".$placed_by."");
		return $result->fetch();
	}
	#############################################################################################
	# DELETE BOUNTY																				#
	#																							#
	#############################################################################################
	public function delete_bounty($bounty_id){
		global $db_prefix;
		$manage_log = new manage_log();
		$result = $this->connect->query("DELETE FROM ".$db_prefix."bounty WHERE bounty_id = '".$bounty_id."'");
		if($result->execute())
		{
			return true;
		}
		else
		{
			/*Log failure to delete bounty*/
			$manage_log = new manage_log();
			$manage_log->security_log(0,20,$result->errorInfo());
			return false;
		}
	}
	#############################################################################################
	# CREATE FEDERATION BOUNTY																	#
	#																							#
	#############################################################################################
	public function create_bounty($target_id,$placed_by,$amount){
		global $db_prefix;
		$manage_log = new manage_log();
		$create_bounty = $this->connect->prepare("INSERT INTO ".$db_prefix."bounty SET bounty_on = ? , placed_by = ? , amount = ? ");
		$create_bounty->bindParam(1, $target_id, PDO::PARAM_INT);
		$create_bounty->bindParam(2, $placed_by, PDO::PARAM_INT);
		$create_bounty->bindParam(3, $amount, PDO::PARAM_INT);
		
		if($create_bounty->execute())
		{
			# LOG THIS #											
		}
		else
		{
			/*Log failure to create bounty*/
			$manage_log = new manage_log();
			$manage_log->security_log(0,21,$create_bounty->errorInfo());
		}

	}
	#############################################################################################
	# UPDATE SHIP CREDITS																		#
	#																							#
	#############################################################################################
	public function update_ship_credits($ship_id,$type,$credits){
		global $db_prefix;
		$manage_log = new manage_log();
		if($type=="1")
		{$type = "+";}
		else if($type=="2")
		{$type = "-";}
		else
		{$type = "-";}//All else fails, always subtract just in case its some wierd exploit.
		$update_creds = $this->connect->prepare("UPDATE ".$db_prefix."ships SET credits = credits ".$type." ? WHERE ship_id='".$ship_id."'");	
		$update_creds->bindParam(1, $credits, PDO::PARAM_INT);
		if($update_creds->execute())
		{
			return true;
			# LOG THIS #											
		}
		else
		{
			/*Log failure to create bounty*/
			$manage_log = new manage_log();
			$manage_log->security_log(0,22,$update_creds->errorInfo());
			return false;
			# LOG FAIL #
		}

	}
	#############################################################################################
	# FETCH USER CREDITS																		#
	#																							#
	#############################################################################################
	public function check_credits($ship_id){
		global $db_prefix;
		$result = $this->connect->query("SELECT credits FROM ".$db_prefix."ships WHERE ship_id='".$ship_id."'");
		return $result->fetch();
	}
	#############################################################################################
	# COLLECT BOUNTY																			#
	#																							#
	#############################################################################################
	public function collect_bounty($attacker, $bounty_on)
	{
		$tables_to_lock_array = array('ships','bounty','player_logs','security_logs');
		$this->lock_table($tables_to_lock_array);
		global $db_prefix;
		$manage_log = new manage_log();
		$result = $this->connect->query("SELECT * FROM ".$db_prefix."bounty WHERE bounty_on = ".$bounty_on."");
		$bounty_found = $result->fetchAll(PDO::FETCH_ASSOC);
			foreach ($bounty_found as $bounty)
			{
				/*Found a bounty, now do somthing with it*/
				echo $bounty['amount'] . " on " . $bounty['bounty_on'] . " placed by " . $bounty['placed_by'];
				$current_credits = $this->check_credits($attacker);
				$target_data = $this->playerinfo($bounty_on,"");
				$attacker_data = $this->playerinfo($attacker,"");
				$bounty['amount'] = (int)$bounty['amount'];
				$s = $attacker_data['ship_id'];
				$r = $bounty['amount'];
				if($this->update_ship_credits($s,"1",$r))
				{
					/*now log it*/
					#playerlog ($db, $attacker, LOG_BOUNTY_CLAIMED, "$bountydetails[amount]|$bountydetails[character_name]|$placed");
					#playerlog ($db, $bountydetails['placed_by'], LOG_BOUNTY_PAID, "$bountydetails[amount]|$bountydetails[character_name]");
				}
				else
				{
					/*Allready logged the failure by the function.*/
				}
				
				if($this->delete_bounty($bounty['bounty_id']))
				{
					/*Tell the player the bounty has been removed*/
				}
				else
				{
					/*Allready logged the failure by the function.*/
				}
			}
			$this->unlock_table();
	}
	#############################################################################################
	# RESET PLAYER SHIP																			#
	#																							#
	#############################################################################################
	public function reset_ship($ship_id){
		global $db_prefix;
		$manage_log = new manage_log();
		$reset_player_ship = $this->connect->prepare("UPDATE ".$db_prefix."ships SET hull=0,engines=0,power=0,sensors=0,computer=0,beams=0,torp_launchers=0,torps=0,armor=0,armor_pts=0,cloak=0,shields=0,sector=0,ship_organics=0,ship_ore=0,ship_goods=0,ship_energy=0,ship_colonists=0,ship_fighters=0,dev_warpedit=0,dev_genesis=0,dev_beacon=0,dev_emerwarp=0,dev_escapepod='N',dev_fuelscoop='N',dev_minedeflector=0,on_planet='N',dev_lssd='N' WHERE ship_id='".$ship_id."'");		
		if($reset_player_ship->execute())
		{
			$manage_log->security_log($ship_id,13);
			# LOG THIS #											
		}
		else
		{
			$manage_log->security_log($ship_id,12,$reset_player_ship->errorInfo());
			# LOG FAIL #
		}

	}
	#############################################################################################
	# MOVE SHIP																					#
	#																							#
	#############################################################################################
	public function move_ship($ship_id,$sector,$turns){
		global $db_prefix;
		$manage_log = new manage_log();
		if($turns>0)
		{
			$update_move = $this->connect->prepare("UPDATE ".$db_prefix."ships SET sector = ?, turns_used=turns_used+1, turns=turns-1 WHERE ship_id='".$ship_id."'");	
			$update_move->bindParam(1, $sector, PDO::PARAM_INT);	
			if($update_move->execute())
			{
				$turns = $turns-1; /*Fix for log*/
				$manage_log->security_log($ship_id,10,$sector,$turns);
				return true;
				# LOG THIS #											
			}
			else
			{
				$manage_log->security_log($ship_id,9,$update_move->errorInfo());
				return false;
				# LOG FAIL #
			}
		}
		else
		{
			$manage_log->security_log($ship_id,11,$sector,$turns);
			return false;
			# LOG FAIL #
		}

	}
	#############################################################################################
	# SECTOR DEFENCE MANAGMENT																	#
	#																							#
	#############################################################################################
	public function manage_sector_defences($sector,$sector_fighters,$sector_mines,$attacker_id){
		global $db_prefix;
		$manage_log = new manage_log();
		/*
		First check if the defences are utterly destroyed. 
		*/
		## Checking if fighters destroyed ##
		if(($sector_fighters==0) or ($sector_fighters<1))
		{
			/*we dont want to be wiping out team mates fighters*/
			$user_team = $this->team_id($attacker_id);
			$result = $this->connect->query("SELECT * FROM ".$db_prefix."sector_defence WHERE sector_id=".$sector." and defence_type ='F' and ship_id!='".$attacker_id."' ORDER BY quantity DESC");
			$result = $result->fetchAll(PDO::FETCH_ASSOC);
			$total = 0;
			if($result)
			{
				foreach ($result as $row)
				{
					if($user_team==0)
					{
						/*Your not a member of a team, so you have no team mates to worry about.... delete away!!*/
						$result = $this->connect->query("DELETE FROM ".$db_prefix."sector_defence WHERE sector_id = '".$sector."' AND defence_id = '".$row['defence_id']."' AND defence_type='F'");
						if($result->execute())
						{
							## log sector fighters killed ##
							##New Log ##
							$manage_log->player_log($row['ship_id'],20,$sector,$attacker_id,'','',"<font color='#FF0000'>High Priority</font>",'<b><font color="#FF0000">Warning</font></b>');
						}
						else
						{
							## log failed to kill sector fighters ##
						}
					}
					else if($user_team==$this->team_id($row['ship_id']))
					{
						/* Fighters are part of your own team... cease firing ... CEASE FIRING!?!?! */
					}
					else
					{
						/*Ohhh we can attack them defences!*/
						$result = $this->connect->query("DELETE FROM ".$db_prefix."sector_defence WHERE sector_id = '".$sector."' AND defence_id = '".$row['defence_id']."' AND defence_type='F'");
						if($result->execute())
						{
							## log sector fighters killed ##
							##New Log ##
							$manage_log->player_log($row['ship_id'],20,$sector,$attacker_id,'','',"<font color='#FF0000'>High Priority</font>",'<b><font color="#FF0000">Warning</font></b>');
						}
						else
						{
							## log failed to kill sector fighters ##
						}
					}
				}
			}
		}
		## checking if minefield is destroyed ##
		if($sector_mines==0)
		{
			/*we dont want to be wiping out team mates mine fields*/
			$user_team = $this->team_id($attacker_id);
			$result = $this->connect->query("SELECT * FROM ".$db_prefix."sector_defence WHERE sector_id=".$sector." and defence_type ='M' and ship_id!='".$attacker_id."' ORDER BY quantity DESC");
			$result = $result->fetchAll(PDO::FETCH_ASSOC);
			$total = 0;
			if($result)
			{
				foreach ($result as $row)
				{
					if($user_team==0)
					{
						/*Your not a member of a team, so you have no team mates to worry about.... delete away!!*/
						$result = $this->connect->query("DELETE FROM ".$db_prefix."sector_defence WHERE sector_id = '".$sector."' AND defence_id = '".$row['defence_id']."' AND defence_type='M'");
						if($result->execute())
						{
							## log sector mines killed ##
							##New Log ##
							$manage_log->player_log($row['ship_id'],21,$sector,$attacker_id,'','',"<font color='#FF0000'>High Priority</font>",'<b><font color="#FF0000">Warning</font></b>');
						}
						else
						{
							## log failed to kill sector mines ##
						}
					}
					else if($user_team==$this->team_id($row['ship_id']))
					{
						/* mines are part of your own team... cease firing ... CEASE FIRE!?!?! */
					}
					else
					{
						/*Ohhh we can attack them defences!*/
						$result = $this->connect->query("DELETE FROM ".$db_prefix."sector_defence WHERE sector_id = '".$sector."' AND defence_id = '".$row['defence_id']."' AND defence_type='M'");
						if($result->execute())
						{
							## log sector mines killed ##
							##New Log ##
							$manage_log->player_log($row['ship_id'],21,$sector,$attacker_id,'','',"<font color='#FF0000'>High Priority</font>",'<b><font color="#FF0000">Warning</font></b>');
						}
						else
						{
							## log failed to kill sector mines ##
						}
					}
				}
			}
		}
		/*defences not destroyed... update the sector information to reflect the damage that was inflicted in the attack*/
		if($sector_fighters>0)
		{
			/*we dont want to be wiping out team mates fighters*/
			$user_team = $this->team_id($attacker_id);
			$result = $this->connect->query("SELECT * FROM ".$db_prefix."sector_defence WHERE sector_id=".$sector." and defence_type ='F' and ship_id!='".$attacker_id."' ORDER BY quantity DESC");
			$result = $result->fetchAll(PDO::FETCH_ASSOC);
			if($result)
			{
				foreach ($result as $row)
				{
					if($user_team==0)
					{
						
						/*Your not a member of a team, so you have no team mates to worry about.... delete away!!*/
						if(($sector_fighters==0) or ($sector_fighters<1))
						{
							$result = $this->connect->query("DELETE FROM ".$db_prefix."sector_defence WHERE sector_id = '".$sector."' AND defence_id = '".$row['defence_id']."' AND defence_type='F'");
							if($result->execute())
							{
								## log sector fighters killed ##
								##New Log ##
								$manage_log->player_log($row['ship_id'],20,$sector,$attacker_id,'','',"<font color='#FF0000'>High Priority</font>",'<b><font color="#FF0000">Warning</font></b>');
							}
							else
							{
								## log failed to kill sector fighters ##
							}
						}
						else
						{
							/*lets remove some of those fighters*/
							$result = $this->connect->prepare("UPDATE ".$db_prefix."sector_defence SET quantity = ? WHERE sector_id = '".$sector."' AND defence_id = '".$row['defence_id']."' AND defence_type='F'");	
							$result->bindParam(1, $sector_fighters, PDO::PARAM_INT);	
							if($result->execute())
							{
								## log sector defence changes ##	
								##New Log ##
								$manage_log->player_log($row['ship_id'],22,$sector,$attacker_id,$sector_fighters,'',"<font color='#FF0000'>High Priority</font>",'<b><font color="#FF0000">Warning</font></b>');									
							}
							else
							{
								## log sector defence change failed##
							}
						}
						if($sector_fighters<0)
						{
							/*little fix for negative values, add them back on*/
							$sector_fighters = 0 - $sector_fighters;
						}
					}
					else if($user_team==$this->team_id($row['ship_id']))
					{
						/* Fighters are part of your own team... cease firing ... CEASE FIRING!?!?! */
					}
					else
					{
						/*Ohhh we can attack them defences!*/
						/*Your not a member of a team, so you have no team mates to worry about.... delete away!!*/
						if(($sector_fighters==0) or ($sector_fighters<1))
						{
							$result = $this->connect->query("DELETE FROM ".$db_prefix."sector_defence WHERE sector_id = '".$sector."' AND defence_id = '".$row['defence_id']."' AND defence_type='F'");
							if($result->execute())
							{
								## log sector fighters killed ##
								##New Log ##
								$manage_log->player_log($row['ship_id'],20,$sector,$attacker_id,'','',"<font color='#FF0000'>High Priority</font>",'<b><font color="#FF0000">Warning</font></b>');
							}
							else
							{
								## log failed to kill sector fighters ##
							}
						}
						else
						{
							/*lets remove some of those fighters*/
							$result = $this->connect->prepare("UPDATE ".$db_prefix."sector_defence SET quantity = ? WHERE sector_id = '".$sector."' AND defence_id = '".$row['defence_id']."' AND defence_type='F'");	
							$result->bindParam(1, $sector_fighters, PDO::PARAM_INT);	
							if($result->execute())
							{
								## log sector defence changes ##
								##New Log ##
								$manage_log->player_log($row['ship_id'],22,$sector,$attacker_id,$sector_fighters,'',"<font color='#FF0000'>High Priority</font>",'<b><font color="#FF0000">Warning</font></b>');											
							}
							else
							{
								## log sector defence change failed##
							}
						}
						if($sector_fighters<0)
						{
							/*little fix for negative values, add them back on*/
							$sector_fighters = 0 - $sector_fighters;
						}
					}
				}
			}
		}
		if($sector_mines>0)
		{
			/*we dont want to be wiping out team mates fighters*/
			$user_team = $this->team_id($attacker_id);
			$result = $this->connect->query("SELECT * FROM ".$db_prefix."sector_defence WHERE sector_id=".$sector." and defence_type ='M' and ship_id!='".$attacker_id."' ORDER BY quantity DESC");
			$result = $result->fetchAll(PDO::FETCH_ASSOC);
			if($result)
			{
				foreach ($result as $row)
				{
					if($user_team==0)
					{
						/*Your not a member of a team, so you have no team mates to worry about.... delete away!!*/
						if(($sector_mines==0) or ($sector_mines<1))
						{
							$result = $this->connect->query("DELETE FROM ".$db_prefix."sector_defence WHERE sector_id = '".$sector."' AND defence_id = '".$row['defence_id']."' AND defence_type='M'");
							if($result->execute())
							{
								## log sector mines killed ##
								##New Log ##
								$manage_log->player_log($row['ship_id'],21,$sector,$attacker_id,'','',"<font color='#FF0000'>High Priority</font>",'<b><font color="#FF0000">Warning</font></b>');
							}
							else
							{
								## log failed to kill sector mines ##
							}
						}
						else
						{
							/*lets remove some of those mines*/
							$result = $this->connect->prepare("UPDATE ".$db_prefix."sector_defence SET quantity = ? WHERE sector_id = '".$sector."' AND defence_id = '".$row['defence_id']."' AND defence_type='M'");	
							$result->bindParam(1, $sector_mines, PDO::PARAM_INT);	
							if($result->execute())
							{
								## log sector defence changes ##	
								##New Log ##
								$manage_log->player_log($row['ship_id'],23,$sector,$attacker_id,$sector_mines,'',"<font color='#FF0000'>High Priority</font>",'<b><font color="#FF0000">Warning</font></b>');											
							}
							else
							{
								## log sector defence change failed##
							}
						}
						if($sector_mines<0)
						{
							/*little fix for negative values, add them back on*/
							$sector_mines = 0 - $sector_mines;
						}
					}
					else if($user_team==$this->team_id($row['ship_id']))
					{
						/* mines are part of your own team... cease firing ... CEASE FIRING!?!?! */
					}
					else
					{
						/*Ohhh we can attack them defences!*/
						/*Your not a member of a team, so you have no team mates to worry about.... delete away!!*/
						if(($sector_mines==0) or ($sector_mines<1))
						{
							$result = $this->connect->query("DELETE FROM ".$db_prefix."sector_defence WHERE sector_id = '".$sector."' AND defence_id = '".$row['defence_id']."' AND defence_type='M'");
							if($result->execute())
							{
								## log sector mines killed ##
								##New Log ##
								$manage_log->player_log($row['ship_id'],21,$sector,$attacker_id,'','',"<font color='#FF0000'>High Priority</font>",'<b><font color="#FF0000">Warning</font></b>');
							}
							else
							{
								## log failed to kill sector mines ##
							}
						}
						else
						{
							/*lets remove some of those mines*/
							$result = $this->connect->prepare("UPDATE ".$db_prefix."sector_defence SET quantity = ? WHERE sector_id = '".$sector."' AND defence_id = '".$row['defence_id']."' AND defence_type='M'");	
							$result->bindParam(1, $sector_mines, PDO::PARAM_INT);	
							if($result->execute())
							{
								## log sector defence changes ##	
								##New Log ##
								$manage_log->player_log($row['ship_id'],23,$sector,$attacker_id,$sector_mines,'',"<font color='#FF0000'>High Priority</font>",'<b><font color="#FF0000">Warning</font></b>');											
							}
							else
							{
								## log sector defence change failed##
							}
						}
						if($sector_mines<0)
						{
							/*little fix for negative values, add them back on*/
							$sector_mines = 0 - $sector_mines;
						}
					}
				}
			}
		}
	}
	#############################################################################################
	# KILL PLAYER SHIP																			#
	#																							#
	#############################################################################################
	public function kill_ship($ship_id){
		global $db_prefix;
		$manage_log = new manage_log();
		if($ship_id>0)
		{
			$kill_player_ship = $this->connect->prepare("UPDATE ".$db_prefix."ships SET ship_destroyed='Y' WHERE ship_id='".$ship_id."'");		
			if($kill_player_ship->execute())
			{
				# Security Log #	
				$manage_log->security_log(0,6);											
			}
			else
			{
				# Security Log #
				$manage_log->security_log($ship_id,4,$kill_player_ship->errorInfo());
				
			}
		}
		else
		{
			# LOG FAIL NO TARGET #
			$manage_log->security_log(0,5);
		}
	}
	#############################################################################################
	# DISABLE XENOBE																			#
	#																							#
	#############################################################################################
	public function disable_xenobe($ship_email){
		/*
		
		######## NEED REWRITING - TRACK XENOBES BY ID NOT EMAIL!!?!?!?!
		
		*/
		global $db_prefix;
		$manage_log = new manage_log();
		$disable_xenobe_ship = $this->connect->prepare("UPDATE ".$db_prefix."xenobe SET active='N' WHERE xenobe_id='".$ship_email."'");		
		if($disable_xenobe_ship->execute())
		{
			# LOG THIS #											
		}
		else
		{
			# LOG FAIL #
		}

	}
	
	#############################################################################################
	# UPDATE THE PLAYERS INFORMATION															#
	#																							#
	#############################################################################################
	public function updatePlayer($id,$table,$query){
		global $db_prefix;
		$manage_log = new manage_log();
		foreach ($query as $key => $field) {
			/*Build list of fields to process*/
			$fields_to_update .= $key." = ?, ";
		}
		$fields_to_update = substr($fields_to_update, 0, -2); /*FIX: Remove the last added comma*/
		$update_player = $this->connect->prepare("UPDATE ".$db_prefix.$table." SET ".$fields_to_update." WHERE ship_id='".$id."'");
		$n = 1;
		foreach ($query as $key => &$field) {
			if(is_numeric($field))
			{
				# NUMBER #
				$update_player->bindParam($n, $field, PDO::PARAM_INT);
			}
			else
			{
				# STRING #
				$update_player->bindParam($n, $field, PDO::PARAM_STR);
			}
			$n++;
		}
		if($update_player->execute())
		{
			# LOG THIS #
			$manage_log->security_log($id,7,$table,$fields_to_update);
			return true;											
		}
		else
		{
			# LOG FAIL #
			$manage_log->security_log($id,8,$update_player->errorInfo());
			return false;
		}
			
	}
}
?>
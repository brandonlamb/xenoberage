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
// File: warpedit.php

include "config/config.php";

updatecookie();

$title = "Use The Warp Editor";
include "header.php";
$sql_manager = new manage_table();

if (checklogin())
{
    die();
}

?>
<div class="tablecell content both-border">
	<div class="pad">
<?

$playerinfo = $sql_manager->playerinfo($user_ship_id,"");

bigtitle();

#DOES THE PLAYER HAVE TURNS?#
if ($playerinfo['turns'] < 1)
{
    echo "You need at least one turn to use a warp editor.<br/><br/>";
    TEXT_GOTOMAIN();
    include "footer.php";
    die();
}
#DOES THE PLAYER HAVE WARP EDITORS?#
if ($playerinfo['dev_warpedit'] < 1)
{
    echo "You do not have any warp editors.<br/><br/>";
    TEXT_GOTOMAIN();
    include "footer.php";
    die();
}
#DOES THE ZONE ALLOW WARP EDITING?#
$zone_information = $sql_manager->zone_information($playerinfo['sector'],'allow_warpedit');
if ($zone_information['allow_warpedit'] == 'N')
{
    echo "Using a Warp Editor in this sector is not permitted.<br/><br/>";
    TEXT_GOTOMAIN();
    include "footer.php";
    die();
}
#ARE YOU THE OWNER OF THIS SECTOR?#
if ($zone_information['allow_warpedit'] == 'L')
{
	$zoneowner_info = $sql_manager->zone_owner_information($zone_information['zone_id']);
	$zoneteam = $sql_manager->team_id($zoneowner_info['owner']);

    if ($zoneowner_info['owner'] != $playerinfo['ship_id'])
    {
        if (($zoneteam['team'] != $playerinfo['team']) || ($playerinfo['team'] == 0))
        {
            echo "Using a Warp Editor in this sector is not permitted.<br/><br/>";
            TEXT_GOTOMAIN();
            include "footer.php";
            die();
        }
    }
}

/*Play has turns and has warp editors, check to see if the user is trying to do somthing.*/
if (array_key_exists('editor', $_POST)== true)
{
    $editor_active = $_POST['editor'];
}
$target_sector = NULL;
if (array_key_exists('target_sector', $_POST)== true)
{
	$target_sector = $_POST['target_sector'];
	if(is_numeric($target_sector))
	{
		$target_information = $sql_manager->zone_information($target_sector,'allow_warpedit');
		$are_you_real = $sql_manager->real_sector($target_sector);
	}
	else
	{
		/*Not a valid number, lets rub in some salt*/
		$editor_active = NULL;
		$target_sector = NULL;
		echo "You can only enter valid sectors into the target sector!<br/>";
		TEXT_GOTOMAIN();
		include "footer.php";
		die();
	}
}

if(($are_you_real<1) && (array_key_exists('editor', $_POST)== true))
{
		echo "The sector you selected doesnt excist in this universe!<br/>";
		TEXT_GOTOMAIN();
		include "footer.php";
		die();
}
else
{
	###################
	##
	##	Create Warp Link
	##
	###################
	if(($editor_active=="creating") && (!is_null($target_sector)))
	{
		/*Load the data*/
		$oneway = NULL;
		if (array_key_exists('oneway', $_POST)== true)
		{
			$oneway = $_POST['oneway'];
		}
		
		$total_target_links = $sql_manager->count_sector_links($target_sector);
		$link_conflicts = $sql_manager->check_link_conflicts($playerinfo['sector'],$target_sector);
		
		if(($target_information['allow_warpedit'] == 'N') && !$oneway)
		{
			echo "Using a Warp Editor to create a two-way link to sector ".$target_sector." is not permitted.<br />";
			TEXT_GOTOMAIN();
			include "footer.php";
			die();
		}
		else if($total_target_links >= $link_max)
		{
			echo $target_sector." has far too many warp links, adding more will cause instability within the sector.<br />";
			TEXT_GOTOMAIN();
			include "footer.php";
			die();
		}
		else if($target_sector==$playerinfo['sector'])
		{
			echo "You can't make a warp link to the sector your currently in!<br />";
		}
		else if($link_conflicts<1)
		{
			
			/*link between the sectors doesnt excist (one way) lets create it*/
			if($oneway)
			{
				if($sql_manager->insert_new_warp_link($playerinfo['sector'],$target_sector))
				{
					$playerinfo['dev_warpedit'] = $playerinfo['dev_warpedit'] - 1;
					$playerinfo['turns'] = $playerinfo['turns'] - 1;
					$playerinfo['turns_used'] = $playerinfo['turns_used'] + 1;
					$updated_stats = array('dev_warpedit'=>$playerinfo['dev_warpedit'],'turns'=>$playerinfo['turns'],'turns_used'=>$playerinfo['turns_used']);
					$sql_manager->updatePlayer($user_ship_id,"ships",$updated_stats);
					echo "A new warp link has been created between ". $playerinfo['sector'] ." and " . $target_sector."!<br />";
				}
				else
				{
					echo "An error occoured while trying to create this warp link.<br />";
				}
			}
			else
			{
				if($sql_manager->insert_new_warp_link($playerinfo['sector'],$target_sector))
				{
					$reverse_conflicts = $sql_manager->check_link_conflicts($target_sector,$playerinfo['sector']);
					if($link_conflicts<1)
					{
						/*there is no link from the target sector back to the current sector, but the user wants a 2 way link, so lets create it*/
						if($sql_manager->insert_new_warp_link($target_sector,$playerinfo['sector']))
						{
							$playerinfo['dev_warpedit'] = $playerinfo['dev_warpedit'] - 1;
							$playerinfo['turns'] = $playerinfo['turns'] - 1;
							$playerinfo['turns_used'] = $playerinfo['turns_used'] + 1;
							$updated_stats = array('dev_warpedit'=>$playerinfo['dev_warpedit'],'turns'=>$playerinfo['turns'],'turns_used'=>$playerinfo['turns_used']);
							$sql_manager->updatePlayer($user_ship_id,"ships",$updated_stats);
							echo "A 2-way warp link has been created between ". $playerinfo['sector'] ." and " . $target_sector."!<br />";
						}
						else
						{
							echo "An error occoured while trying to create this warp link.<br />";
						}
					}
				}
				else
				{
					echo "An error occoured while trying to create this warp link.<br />";
				}
			}
		}
		else
		{
			echo "There is allready a warp link active between ". $playerinfo['sector'] ." and " . $target_sector."!<br />";
		}
	
	}
	###################
	##
	##	Remove Warp Link
	##
	###################
	else if(($editor_active=="destroying") && (!is_null($target_sector)))
	{
		/*Load the data*/
		$bothway = NULL;
		if (array_key_exists('bothway', $_POST)== true)
		{
			$bothway = $_POST['bothway'];
		}
		
		$link_conflicts = $sql_manager->check_link_conflicts($playerinfo['sector'],$target_sector);
		
		if(($target_information['allow_warpedit'] == 'N') && $bothway)
		{
			echo "Using a Warp Editor to remove a two-way link to sector ".$target_sector." is not permitted!<br />";
			TEXT_GOTOMAIN();
			include "footer.php";
			die();
		}
		else if($link_conflicts>0)
		{
			/*link between the sectors does excist (one way) lets destroy it*/
			if(!$bothway)
			{
				if($sql_manager->delete_warp_link($playerinfo['sector'],$target_sector))
				{
					$playerinfo['dev_warpedit'] = $playerinfo['dev_warpedit'] - 1;
					$playerinfo['turns'] = $playerinfo['turns'] - 1;
					$playerinfo['turns_used'] = $playerinfo['turns_used'] + 1;
					$updated_stats = array('dev_warpedit'=>$playerinfo['dev_warpedit'],'turns'=>$playerinfo['turns'],'turns_used'=>$playerinfo['turns_used']);
					$sql_manager->updatePlayer($user_ship_id,"ships",$updated_stats);
					echo "The link has been removed between ". $playerinfo['sector'] ." and " . $target_sector."!<br />";
				}
				else
				{
					echo "An error occoured while trying to destroy this warp link.<br />";
				}
			}
			else
			{
				if($sql_manager->delete_warp_link($playerinfo['sector'],$target_sector))
				{
					$reverse_conflicts = $sql_manager->check_link_conflicts($target_sector,$playerinfo['sector']);
					if($link_conflicts>0)
					{
						/*there is a link going back to the current sector and the user wants 2 way, so lets kill it*/
						if($sql_manager->delete_warp_link($target_sector,$playerinfo['sector']))
						{
							$playerinfo['dev_warpedit'] = $playerinfo['dev_warpedit'] - 1;
							$playerinfo['turns'] = $playerinfo['turns'] - 1;
							$playerinfo['turns_used'] = $playerinfo['turns_used'] + 1;
							$updated_stats = array('dev_warpedit'=>$playerinfo['dev_warpedit'],'turns'=>$playerinfo['turns'],'turns_used'=>$playerinfo['turns_used']);
							$sql_manager->updatePlayer($user_ship_id,"ships",$updated_stats);
							echo "A 2-way warp link has been removed between ". $playerinfo['sector'] ." and " . $target_sector."!<br />";
						}
						else
						{
							echo "An error occoured while trying to remove this warp link.<br />";
						}
					}
				}
				else
				{
					echo "An error occoured while trying to destroy this warp link.<br />";
				}
			}
		}
		else
		{
			echo "There is no warp link between ". $playerinfo['sector'] ." and " . $target_sector."!<br />";
		}
	}
}

$live_links = $sql_manager->list_sector_links($playerinfo['sector']);
if(empty($live_links))
{
    echo "There are no links out of this sector.<br/><br/>";	
}
else
{
	echo "Below is a list of links leading from this sector:<br/><ul>";
	foreach ($live_links as $destination_link) {
    echo "<li>".$destination_link . "</li>";
	}
	echo "</ul>";
}
?>
<br/>Create Warp Link<br/>
<form action="warpedit.php" method="post">
<table>
<tr>
	<td>What sector would you like to create a link to?</td>
    <td><input type="text" name="target_sector" size="6" maxlength="6" value="" /></td>
</tr>
<tr>
	<td>One Way?</td>
    <td><input type="checkbox" name="oneway" value="oneway" /></td>
</tr>
</table>
<input type="hidden" name="editor" value="creating" />
<input type="submit" value="Submit"><input type="reset" value="Reset" />
</form>
<br/>Destroy Warp Link<br/>
<form action="warpedit.php" method="post">
<table>
<tr>
	<td>What sector would you like to remove a link to?</td>
    <td><input type="text" name="target_sector" size="6" maxlength="6" value="" /></td>
</tr>
<tr>
	<td>Both Ways?</td>
    <td><input type="checkbox" name="bothway" value="bothway" /></td>
</tr>
</table>
<input type="hidden" name="editor" value="destroying" />
<input type="submit" value="Submit"><input type="reset" value="Reset" />
</form>
<?

TEXT_GOTOMAIN();
?>
</div></div>
<?
include "footer.php";
?>

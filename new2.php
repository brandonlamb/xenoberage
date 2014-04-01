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
// File: new2.php

require_once($_SERVER['DOCUMENT_ROOT']."/config/config.php");

$title = "User Registration";
include "header.php";

$status = false;
$errors = array();
?>

<div class="tablecell content both-border">
	<div class="pad">
<?
bigtitle ();

/*If account creation is closed, do not show the page!*/
if ($account_creation_closed)
{
    die ("Server Is Currently Closed To New Players");
}
##
##
## New Registration Process
##
##
$facebook = new facebook(array('appId'  => FB_ID,'secret' => FB_SECRET,));
$request = $facebook->getSignedRequest();
if ($request)
{
	$register = $request['registration'];
	try
	{
		$db = db::init();
		$fbId = (isset($request['user_id'])) ? $request['user_id'] : 0;
		$user = new user();
		if ($fbId != 0)
		{
			if ($user->fbLogin($fbId)) 
			{
				/*They have account, throw them back to the main page*/
				header('Location: index.php');
				exit();
			}
		}	
			
		$sth = $db->prepare("SELECT * FROM ".$db_prefix."account WHERE username = ?");
		$sth->execute(array($register['username']));
		if (!$sth->fetch())
		{				
			$sth = $db->prepare("SELECT * FROM ".$db_prefix."account WHERE email = ?");
			$sth->execute(array($register['email']));
			if ((false != $sth->fetch()) && ($fbId != 0))
			{
				$sth = $db->prepare("UPDATE ".$db_prefix."account SET facebook_id = ? WHERE email = ?");
				if ($sth->execute(array($fbId, $register['email'])))
				{
					$status = $user->fbLogin($fbId);
				}
			}
			else
			{

				$shared_function = new shared();
				$time_date_full = $shared_function->manage_time("full");
				$location = $register['location'];
				if (is_array($location))
				{
					$location = $location['name'];
				}
				if($fbId>1)
				{
					#user has a facebook id, use it
					$account_id = $fbId;
				}
				else
				{
					#create a randomly large id
					$account_id = rand(1,999).rand(1,999).rand(1,999);
				}
				$sql = "INSERT INTO ".$db_prefix."account (facebook_id, username, password, name, email, location, gender, ip, registration_date, handle, active_ship, user_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
				$data = array(
								$fbId,
								$register['username'],
								md5($register['password']),
								$register['name'],
								$register['email'],
								$location,
								$register['gender'],
								$_SERVER['REMOTE_ADDR'],
								$time_date_full,
								$register['handle'],
								0,
								$account_id
				);
				$sth = $db->prepare($sql);
				if ($sth->execute($data))
				{
					$sql_manager = new manage_table();
					/*
					Now Create Ship (need to code in a way to allow multiple ships - future feature....)
					*/
					##
					
					##get user id from the newly created account
					$user_account_id = $sql_manager->find_account_id($register['username']);
					##create ship from new id
					$create_ship = $sql_manager->create_user_space_ship($user_account_id,$register['shipname'],$register['handle'],$register['email'],$time_date_full,$_SERVER['REMOTE_ADDR']);
					if($create_ship)
					{
						##update user account with active ship
						##################
						## not implementing multiple ships yet, however when we do, first value needs to be set differently with anothe call asking for the ship id
						##################
						$set_active_ship = $sql_manager->set_active_ship($user_account_id,$user_account_id);
						if($set_active_ship)
						{
							$status = $user->login($register['username'], $register['password']);
						}
						else
						{
							echo "Error setting your ship as active";
							die();
							
						}
					}
					else
					{
						echo "Error creating account";
						die();
						
					}
					

				}
				else
				{
					echo "failed account creation";
				}
			}
		}
		else
		{
			array_push($errors, 'Username is already taken!');
		}
	}
	catch (Exception $e) 
	{
		array_push($errors, 'Database error: ' . $e->getMessage());
	}
}
else
{
	array_push($errors, 'Error with validating Signed request.');
}

				 if ($status = true)
				 { ?>
					<h1>Success</h1>
					<p>
						New Account Created!!
						<a href="index.php">Start Playing XR</a>
					</p>
				<?php 
				 }
				 else
				 { ?>
					<h1>Errors occured during registration.</h1>
					<p>Please try again.</p>
					
					<?php
						if (!empty($errors))
						{
							echo '<ul>';
							
							foreach ($errors as $e)
							{
								echo '<li>' . $e . '</li>';
							}
							
							echo '</ul>';
						}
				}

			
			?>
    </div>
    <div class="footer">
         <div class="github"><a href="https://github.com/xgermz/xenoberage"><div class="logo-github"></div></a></div>
        <div class="copyright"><span class="bolder">Xenobe Rage</span> &copy;2012 - 2014 David Dawson. All rights reserved.<br /><span class="bolder">Blacknova Traders</span> &copy;2000-2012 Ron Harwood &amp; the BNT Dev team. All rights reserved.</div>
    </div>
</div>
            <?php
/*






# Get the user supplied post vars.
$username  = null;
$shipname  = null;
$character = null;
if (array_key_exists('character', $_POST))
{
    $character  = $_POST['character'];
}

if (array_key_exists('shipname', $_POST))
{
    $shipname   = $_POST['shipname'];
}

if (array_key_exists('username', $_POST))
{
    $username   = $_POST['username'];
}

if (array_key_exists('lang', $_POST))
{
    $lang   = $_POST['lang'];
}
else
{
    $lang = $default_lang;
}

$character = htmlspecialchars ($character);
$shipname = htmlspecialchars ($shipname);
$character = preg_replace ('/[^A-Za-z0-9\_\s\-\.\']+/', ' ', $character);
$shipname = preg_replace ('/[^A-Za-z0-9\_\s\-\.\']+/', ' ', $shipname);

// $username = $_POST['username']; // This needs to STAY before the db query

if (!get_magic_quotes_gpc())
{
    $username = addslashes ($username);
    $character = addslashes ($character);
    $shipname = addslashes ($shipname);
}

$result = $db->Execute ("SELECT email, character_name, ship_name FROM {$db->prefix}ships WHERE email='$username' OR character_name='$character' OR ship_name='$shipname'");
db_op_result ($db, $result, __LINE__, __FILE__, $db_logging);
$flag = 0;

if ($username == '' || $character == '' || $shipname == '' )
{
    echo $l_new_blank . '<br>';
    $flag = 1;
}

while (!$result->EOF)
{
    $row = $result->fields;
    if (strtolower ($row['email']) == strtolower ($username))
    {
        echo "$l_new_inuse  $l_new_4gotpw1 <a href=mail.php?mail=$username>$l_clickme</a> $l_new_4gotpw2<br>";
        $flag = 1;
    }
    if (strtolower ($row['character_name']) == strtolower($character))
    {
        $l_new_inusechar=str_replace("[character]", $character, $l_new_inusechar);
        echo $l_new_inusechar . '<br>';
        $flag = 1;
    }
    if (strtolower ($row['ship_name']) == strtolower ($shipname))
    {
        $l_new_inuseship = str_replace ("[shipname]", $shipname, $l_new_inuseship);
        echo $l_new_inuseship . '<br>';
        $flag = 1;
    }
    $result->MoveNext();
}

if ($flag == 0)
{
    // Insert code to add player to database
    $makepass = "";
    $syllables = "er,in,tia,wol,fe,pre,vet,jo,nes,al,len,son,cha,ir,ler,bo,ok,tio,nar,sim,ple,bla,ten,toe,cho,co,lat,spe,ak,er,po,co,lor,pen,cil,li,ght,wh,at,the,he,ck,is,mam,bo,no,fi,ve,any,way,pol,iti,cs,ra,dio,sou,rce,sea,rch,pa,per,com,bo,sp,eak,st,fi,rst,gr,oup,boy,ea,gle,tr,ail,bi,ble,brb,pri,dee,kay,en,be,se";
    $syllable_array = explode (",", $syllables);
    for ($count=1; $count<=4; $count++)
    {
        if (mt_rand ()%10 == 1)
        {
            $makepass .= sprintf("%0.0f",(mt_rand ()%50)+1);
        }
        else
        {
            $makepass .= sprintf("%s", $syllable_array[mt_rand ()%62]);
        }
    }
    $stamp=date("Y-m-d H:i:s");
    $query = $db->Execute("SELECT MAX(turns_used + turns) AS mturns FROM {$db->prefix}ships");
    db_op_result ($db, $query, __LINE__, __FILE__, $db_logging);
    $res = $query->fields;

    $mturns = $res['mturns'];

    if ($mturns > $max_turns)
    {
        $mturns = $max_turns;
    }

    $result2 = $db->Execute("INSERT INTO {$db->prefix}ships (ship_name, ship_destroyed, character_name, password, email, armor_pts, credits, ship_energy, ship_fighters, turns, on_planet, dev_warpedit, dev_genesis, dev_beacon, dev_emerwarp, dev_escapepod, dev_fuelscoop, dev_minedeflector, last_login, ip_address, trade_colonists, trade_fighters, trade_torps, trade_energy, cleared_defences, lang, dev_lssd)
                             VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)", array ($shipname, 'N', $character, $makepass, $username, $start_armor, $start_credits, $start_energy, $start_fighters, $mturns, 'N', $start_editors, $start_genesis, $start_beacon, $start_emerwarp, $escape, $scoop, $start_minedeflectors, $stamp, $ip, 'Y', 'N', 'N', 'Y', NULL, $lang, $start_lssd));
    db_op_result ($db, $result2, __LINE__, __FILE__, $db_logging);

    if (!$result2)
    {
        echo $db->ErrorMsg() . "<br>";
    }
    else
    {
        $result2 = $db->Execute("SELECT ship_id FROM {$db->prefix}ships WHERE email='$username'");
        db_op_result ($db, $result2, __LINE__, __FILE__, $db_logging);

        $shipid = $result2->fields;

        // To do: build a bit better "new player" message
        $l_new_message = str_replace("[pass]", $makepass, $l_new_message);
        $l_new_message = str_replace("[ip]", $ip, $l_new_message);

        # Some reason \r\n is broken, so replace them now.
        $l_new_message = str_replace('\r\n', "\r\n", $l_new_message);

        $link_to_game = "http://";
        $link_to_game .= ltrim($gamedomain,".");// Trim off the leading . if any
        //$link_to_game .= str_replace($_SERVER['DOCUMENT_ROOT'],"",dirname(__FILE__));
        $link_to_game .= $gamepath;
        mail("$username", "$l_new_topic", "$l_new_message\r\n\r\n$link_to_game","From: $admin_mail\r\nReply-To: $admin_mail\r\nX-Mailer: PHP/" . phpversion());

        log_move ($db, $shipid['ship_id'], 0); // A new player is placed into sector 0. Make sure his movement log shows it, so they see it on the galaxy map.
        $resx = $db->Execute("INSERT INTO {$db->prefix}zones VALUES(NULL,'$character\'s Territory', $shipid[ship_id], 'N', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 0)");
        db_op_result ($db, $resx, __LINE__, __FILE__, $db_logging);

        $resx = $db->Execute("INSERT INTO {$db->prefix}ibank_accounts (ship_id,balance,loan) VALUES($shipid[ship_id],0,0)");
        db_op_result ($db, $resx, __LINE__, __FILE__, $db_logging);

        if ($display_password)
        {
            echo $l_new_pwis . " " . $makepass . "<br><br>";
        }

        $l_new_pwsent=str_replace("[username]", $_POST['username'], $l_new_pwsent);
        echo $l_new_pwsent . '<br><br>';
        echo "<a href=index.php" . $link . ">$l_clickme</A> $l_new_login";
    }
}
else
{
    $l_new_err = str_replace ("[here]", "<a href='new.php'>" . $l_here . "</a>",$l_new_err);
    echo $l_new_err;
}

*/
?>


</div></div>
<?
include "footer.php";
?>

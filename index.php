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
// File: index.php

$index_page = true;
include "config/config.php";

if (!isset($_GET['lang']))
{
    $_GET['lang'] = null;
    $lang = $default_lang;
    $link = '';
}
else
{
    $lang = $_GET['lang'];
    $link = "?lang=" . $lang;
}

// Check to see if the language database has been installed yet.
$result = $db->Execute("SELECT name, value FROM {$db->prefix}languages WHERE category=? AND language=?;", array('common', $lang));
if (!$result)
{
    // If not, redirect to create_universe.
    header("Location: create_universe.php");
    die ();
}

$title = $l->get('l_welcome_bnt');
$body_class = 'index';

include "header2.php";
?>
<div class="xenobe-container"><div class="homepage-container">
	<div class="header-image"></div>
    <div class="links">
        <?php 
		if($account_creation_closed==true)
		{}
		else
		{
			echo '<a href="new_fb.php">New Player</a> - ';
		}?>
		 <a href="mailto:<?php echo $admin_mail; ?>">Contact Us</a> - <a href="ranking.php<?php echo $link; ?>">Ranking</a> - <a href="faq.php<?php echo $link; ?>">How To Play</a> - <a href="settings.php<?php echo $link; ?>">Settings</a> - <a href="<?php echo $link_forums; ?>" target="_blank">Forums</a></div>
        <?php 
		if($account_creation_closed==true && $server_closed==false)
		{
			?>
			<div class="website-alert">This Server Is Currently Closed To New Players</div>
			<?php
		}
		else if($server_closed==true)
		{
			?>
			<div class="website-alert">The Server Is Currently Down For Maintenance! Please Try Again Later</div>
			<?php
		}
		else
		{
		}?>
    <form action="login2.php" method="post">
    <div class="login-container">
    	<div class="login-option">
        	<label for="handle">
            	Username
                <span class="hint">Your Account Username</span>
            </label>
        	<input type="handle" id="handle" name="handle" size="20" maxlength="255">
        </div>
        <div class="clear"></div>
        <div class="login-option">
            <label for="pass">
            	Password
            	<span class="hint">Forgotten Your Password?</span>
            </label>
            <input type="password" id="pass" name="pass" size="20" maxlength="255">
            </div>
        <div class="clear"></div>
        <div class="login-option">
			<input class="login-button" type="submit" value="Login">
        </div>
        <div class="clear"></div>
        
    </div>
    <div class="cookie-warning">By signing up or logging in you agree to let us set cookies.</div>
    </form>
    <br>
    
    
    <br>
   <div class="core-message"><h1>Server Reset. 02 - April - 2014</h1><div class="core-content">Server Has Been Reset.</div></div>
   <div class="core-message"><h1>Server Updated To 0.18 Code.</h1><div class="core-content">
   Version: Xenoberage 0.18<br/><br/>

Change Log:
<ul>
<li>CHANGE> Various code changes with the view of removing adodb</li>
<li>CHANGE> updated and modified the cookies to be more secure</li>
<li>FIX> Fixed bug with new federation IGB fee's</li>
<li>CHANGE> rewritten part warp edit code (Consolidated 3 scripts to one script)</li>
<li>FIX> fix text error on devices.php</li>
<li>FIX> fixed xenobes spamming security logs with over 4000 entries each per day each!</li>
<li>FIX> fixed planet report build and edit not displaying for correct types</li>
<li>NEW> added in new news items</li>
<li>NEW> added in new planet facilities</li>
<li>CHANGE> implement a facebook registration system, for more secure registration process not relying on emails! (will swap for my own system in the future...)</li>
<li>CHANGE> changed back end to rely on user ID over username.</li>
<li>NEW> Planets now have a random change of being hit by bank robbers if the credits go over 10 Quadrillian (can be modified in config)!</li>
<li>FIX> ability to log into anyones account without a password</li>
<li>FIX> scanning planet didnt show ship stats</li>
<li>FIX> attacking a planet stats was not showing</li>
<li>FIX> attacking other ships works again</li>
<li>FIX> log working again. (was relying on username not account id)</li>
<li>CHANGE> To improve security, all user ids are now randomly assigned unique ID.</li>
<li>FIX> Facilities now generating resources. (first facility generates an addition bonus)</li>
<li>CHANGE> Multiple ships now supported in the code</li>
<li>FIX> Conflicting links redirecting to wrong pages</li>
</ul>
Known Issues:
<ul>
<li>Scanning planets not showing in logs</li>
<li>when you die you dont get resurrected. (replacing with new system anyway....)</li>
<li>bounty in logs showing incorrect money number</li>
<li>sofa doesnt seem to be working.... (unconfirmed)</li>
<li>hack ship not working.</li>
<li>various facilities abilities disabled</li>
<li>unable to "influence" senators</li>
<li>creating universe doesnt create admin account correctly</li>
<li>research points not working</li>
<li>ship kills not showing correctly.</li>
<li>attacking ships on planet uses old attack code (need to finish consolidating the code)</li>
<li>points not accumulating</li>
<li>empire stats not displayed to end user. (beta feature coming in 0.017)</li>
<li>failed login page still shows user handle and ship name on password error. (Keep it as feature?)</li>
<li>FACEBOOK BUG -> If a user enters a password which isnt valid, the facebook check fails without a warning to the user. https://developers.facebook.com/x/bugs/713074325400184/ status is set to Wont Fix. What a pain!?!?!</li>
</ul>
   </div></div>

    </div><div class="footer">
         <div class="github"><a href="https://github.com/xgermz/xenoberage"><div class="logo-github"></div></a></div>
        <div class="copyright"><span class="bolder">Xenobe Rage</span> &copy;2012 - 2014 David Dawson. All rights reserved.<br /><span class="bolder">Blacknova Traders</span> &copy;2000-2012 Ron Harwood &amp; the BNT Dev team. All rights reserved.</div>
    </div>
</div>

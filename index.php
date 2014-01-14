<?php
// Xenobe Rage Copyright (C) 2012-2013 David Dawson
// Blacknova Traders -  Copyright (C) 2001-2012 Ron Harwood and the BNT development team
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
			echo '<a href="new.php<?php echo $link; ?>">New Player</a> - ';
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
    <form action="login2.php<?php echo $link; ?>" method="post">
    <div class="login-container">
    	<div class="login-option">
        	<label for="email">
            	Email Address
                <span class="hint">Your Account Username</span>
            </label>
        	<input type="email" id="email" name="email" size="20" maxlength="40">
        </div>
        <div class="clear"></div>
        <div class="login-option">
            <label for="pass">
            	Password
            	<span class="hint">Forgotten Your Password?</span>
            </label>
            <input type="password" id="pass" name="pass" size="20" maxlength="20">
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
    <div class="core-message"><h1>A Message...</h1><div class="core-content">My goal is the take the current epic game Blacknova Traders, and rewrite every page, tweaking and fixing as I go (Yes, a long term project....)! I have a lot of new idea's to bring to the game, some which will change the fundamentals of the game. I'm no professional coder, however my aim is to develop and refine my own skills through developing this game. I will probabily make mistakes, epic mistakes, but its all part of the process of learning!</div></div>
    <div class="core-message"><div class="core-content">Currently in the process of setting up the github project (Link below). I think ive got the grasp of how to use It, however the real reason for using it is for the version managment, but im at a loss with Git... any advice here? (Use Contact above) Thanks!</div></div>
   

    </div><div class="footer">
         <div class="github"><a href="https://github.com/xgermz/xenoberage"><div class="logo-github"></div></a></div>
        <div class="copyright"><span class="bolder">Xenobe Rage</span> &copy;2012 - 2014 David Dawson. All rights reserved.<br /><span class="bolder">Blacknova Traders</span> &copy;2000-2012 Ron Harwood &amp; the BNT Dev team. All rights reserved.</div>
    </div>
</div>

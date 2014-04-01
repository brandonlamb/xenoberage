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

include "config/config.php";

//require_once($_SERVER['DOCUMENT_ROOT']."/classes/check.facebook.login.php");

	$user = new user();
	$facebook = new facebook(array(
	  'appId'  => FB_ID,
	  'secret' => FB_SECRET,
	));
// New database driven language entries
load_languages($db, $lang, array('new', 'login', 'common', 'global_includes', 'global_funcs', 'footer', 'news'), $langvars, $db_logging);

$title = "User Registration";
include "header.php";
?>


<div class="tablecell content both-border">
	<div class="pad">
<?
bigtitle ();
?>

					<div id="fb-root"></div>
					<script type="text/javascript">
						FB.Event.subscribe('auth.login', function(response) {window.location.reload();});
						FB.Event.subscribe('auth.logout', function(response) {window.location.reload();});
					  (function(d){
						 var js, id = 'facebook-jssdk'; if (d.getElementById(id)) {return;}
						 js = d.createElement('script'); js.id = id; js.async = true;
						 js.src = "//connect.facebook.net/en_US/all.js";
						 d.getElementsByTagName('head')[0].appendChild(js);
					   }(document));
					</script>
       				 <div class="half float_l">
						
						<h2>Login</h2>
						<p>
							If you already have an account you can login 
							using the form below:
						</p>
                        <?
						if($option_2=="noUserPass")
						{
							?>
							 <div id="contact_form_error">You have not entered a Username Or Password</div>
							<?
                        }
						else if($option_2=="incorrectUserPass")
						{
							?>
							 <div id="contact_form_error">Your username or password is incorrect, please try again!</div>
							<?
						}
						?>
                            <div id="contact_form">
                                <form action="login2.php" method="POST">   
                                <label for="username">Username:</label> <input type="text" id="username" name="handle" class="required input_field" <? if($_POST['username']){ echo 'value="'.$_POST['username'].'"'; } ?> />
                                <div class="cleaner h10"></div>										
                                <label for="password">Password:</label> <input type="password" class="validate-email required input_field" name="pass" id="password" />
                                <div class="cleaner h10"></div>					
                                <input type="submit" value="Login" id="submit" name="submit" class="submit_btn float_l" />	
                            </div>
                            <div class="cleaner"></div> 
						</form>
						
					</div>
						<h3>Create a New Account</h3>
                        <p><font color='red'><sub><center>Please ensure scripts are enabled, otherwise you will not see the registration form!</center></sub></font></p>
                        <p><font color='red'><sub><center>WARNING: Due to a bug in the facebook API (yes a facebook bug they will not fix) You need to have a password greater then 12 characters, otherwise you will get stuck in a registration loop!</center></sub></font></p>
						<div
                        class='fb-registration'  
						data-fields='[{"name":"name"},{"name":"birthday"},{"name":"location"},{"name":"gender"},{"name":"email"},{"name":"username","description":"Username","type":"text"},{"name":"handle","description":"Name Your Character","type":"text"},{"name":"shipname","description":"Name Your Ship","type":"text"},{"name":"password"}]' 
						data-redirect-uri='http://beta.xenoberage.com/new2.php' 
                        width='360'>
						</div>
					
					<div class="cleaner"></div>	

</div></div>
<?
include "footer.php";
?>

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
// File: setup_info.php

include "config/config.php";

// Stores the class for creating the universe.
include "setup_info_class.php";

// Load up SETUPINFO Class
$setup_info = new SETUPINFO_CLASS();

// Class Test Switches.
$setup_info->switches['Show_Env_Var']['enabled']    = false;
$setup_info->switches['Test_Cookie']['enabled']     = false;
$setup_info->switches['Enable_Database']['enabled'] = false;
$setup_info->switches['Display_Patches']['enabled'] = false;
$setup_info->switches['Display_Errors']['enabled']  = false;

$setup_info->testcookies();
$setup_info->initDB();

// New database driven language entries
load_languages($db, $lang, array('common', 'global_includes', 'footer', 'news'), $langvars, $db_logging);

$title = $setup_info->appinfo['title'];
include "header.php";
?>
<div class="tablecell content both-border">
	<div class="pad">
<?
$setup_info->DisplayFlush("<div align=\"center\">\n");
$setup_info->DisplayFlush("<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\">\n");
$setup_info->DisplayFlush("  <tr>\n");
$setup_info->DisplayFlush("    <td><font size=\"6\" color=\"#ffffff\">{$setup_info->appinfo['title']}</font></td>\n");
$setup_info->DisplayFlush("  </tr>\n");
$setup_info->DisplayFlush("  <tr>\n");
$setup_info->DisplayFlush("    <td align=\"center\"><font size=\"2\" color=\"#ffffff\"><strong>{$setup_info->appinfo['description']}</strong></font></td>\n");
$setup_info->DisplayFlush("  </tr>\n");
$setup_info->DisplayFlush("  <tr>\n");
$setup_info->DisplayFlush("    <td align=\"center\"><font size=\"2\" color=\"#ffffff\"><strong>Written by {$setup_info->appinfo['author']}</strong></font></td>\n");
$setup_info->DisplayFlush("  </tr>\n");
$setup_info->DisplayFlush("</table>\n");
$setup_info->DisplayFlush("</div><br>\n");
$setup_info->DisplayFlush("<br>\n");

//End of own HTML Tables
$setup_info->DisplayFlush("<font size=\"2\" color=#ffff00><i>Well since a lot of people are having problems setting up Blacknova Traders on a Linux based server.</i></font><br>\n");
$setup_info->DisplayFlush("<font size=\"2\" color=#ffff00><i>Here is the settings that you may require to set.</i></font><br><br>\n");

$setup_info->DisplayFlush("<font size=\"2\" color=#ffffff>ADMINS: <font color=#ffff00>If you get any errors or incorrect info returned then set <font color=\"#0000fff00\">\$setup_info->switches['Show_Env_Var']['enabled'] = true;</font></font></font><br><br>\n");
$setup_info->DisplayFlush("<font size=\"2\" color=#ffffff>To Enable the Cookie Test, set <font color=\"#0000fff00\">\$setup_info->switches['Test_Cookie']['enabled'] = true;</font></font><br>\n");
$setup_info->DisplayFlush("<font size=\"2\" color=#ffffff>To Enable the Database Test, set <font color=\"#0000fff00\">\$setup_info->switches['Enable_Database']['enabled'] = true;</font></font><br>\n");
$setup_info->DisplayFlush("<font size=\"2\" color=#ffffff>To Enable the Display All installed patches, set <font color=\"#0000fff00\">\$setup_info->switches['Display_Patches']['enabled'] = true;</font></font><br>\n");
$setup_info->DisplayFlush("<font size=\"2\" color=#ffffff>To Enable the Display All Errors, set <font color=\"#0000fff00\">\$setup_info->switches['Display_Errors']['enabled'] = true;</font></font><br>\n");
$setup_info->DisplayFlush("<font size=\"2\" color=yellow>Then refresh the page and then save it as htm or html and then Email it to me.</font><br>\n");
$setup_info->DisplayFlush("<br>\n");
$setup_info->DisplayFlush("<div style='height:1px; width:100%; margin:0px; background-color:#808080;'></div>\n");
$setup_info->DisplayFlush("<br>\n");

$Cols = 3;
$switch_info = $setup_info->get_switches();
$setup_info->do_Table_Title("Setup Info Switch Configuration",$Cols);
for ($n = 0; $n < count($switch_info); $n++)
{
    list($switch_name, $switch_array) = each($switch_info);
    $setup_info->do_Table_Row($switch_array['caption'],"<font color='maroon'>".$switch_array['info']."</font>",(($switch_array['value']) ? "<font color='#0000ff'>Enabled</font>" : "<font color='#ff0000'>Disabled</font>"));
}
$setup_info->do_Table_Footer("<br>");

#$setup_info->DisplayFlush("<p><hr align='center' width='80%' size='1'></p>\n");
$setup_info->DisplayFlush("<br>\n");
$setup_info->DisplayFlush("<div style='width:80%; margin:auto; height:1px; background-color:#808080;'></div>\n");

$setup_info->DisplayFlush("<font size=\"2\">// This is just to find out what Server Operating System your running bnt on.</font><br>\n");
$setup_info->DisplayFlush("<font size=\"2\">// And to find out what other software is running e.g. PHP,</font><br>\n");
$setup_info->DisplayFlush("<br>\n");

$Cols = 3; $Wrap = true;
$setup_info->do_Table_Title("Server Software/Operating System",$Cols);

$software_info = $setup_info->get_server_software();

for ($n = 0; $n < count($software_info); $n++)
{
    list($software_name, $software_array) = each($software_info);
    list($software_key, $software_value) = each($software_array);
    $setup_info->do_Table_Row($software_key,$software_value);
}

if ($setup_info->testdb_connection())
{
    $setup_info->do_Table_Row("DB CONNECTION","<font color='#0000ff'><strong>".$setup_info->db_status['status']."</strong></font>");
}
else
{
    $setup_info->do_Table_Row("DB CONNECTION","<font color='#ff0000'><strong>".$setup_info->db_status['status']."<br>".$setup_info->db_status['error']."</strong></font>");
}

if ($setup_info->cookie_test['enabled'])
{
    if ($setup_info->cookie_test['result'])
    {
        $setup_info->do_Table_Row("Cookie Test","<font color='#0000ff'><strong>Passed</strong></font>");
    }
    else
    {
        $setup_info->do_Table_Row("Cookie Test","<font color='#ff0000'><strong>Failed testing Cookies!<br>{$setup_info->cookie_test['status']}</strong></font>");
    }
}
else
{
    $setup_info->do_Table_Row("Cookie Test","<font color='#ff0000'><strong>{$setup_info->cookie_test['status']}</strong></font>");
}

$setup_info->do_Table_Footer("");
$setup_info->DisplayFlush("<br>\n");

$Cols = 3; $Wrap = true;
$setup_info->do_Table_Title("Software Versions",$Cols);

$software_versions = $setup_info->get_software_versions();
for ($n = 0; $n < count($software_versions); $n++)
{
    list($software_name, $software_array) = each($software_versions);
    list($software_key, $software_value) = each($software_array);
    $setup_info->do_Table_Row($software_key,$software_value);
}

$setup_info->do_Table_Blank_Row();
$setup_info->do_Table_Single_Row("* = Module (if any installed).");
$setup_info->do_Table_Footer("<br>");

// Config local settings
#$setup_info->DisplayFlush("<hr align='center' width='80%' size='1'>\n");
$setup_info->DisplayFlush("<br>\n");
$setup_info->DisplayFlush("<div style='width:80%; margin:auto; height:1px; background-color:#808080;'></div>\n");

$setup_info->DisplayFlush("<p><font size=\"2\">// This is what you need to put in your db_config.php file.</font><br>\n");
$setup_info->DisplayFlush("<font size=\"2\">// If you are having problems using this script then email me <a class=\"email\" href=\"mailto:{$setup_info->appinfo['email']}\">{$setup_info->appinfo['author']}</a>.</font><br>\n");
$setup_info->DisplayFlush("<font size=\"2\">// Also if you think the info displayed is Incorrect then Email me <a class=\"email\" href=\"mailto:{$setup_info->appinfo['email']}\">{$setup_info->appinfo['author']}</a> with the following information:</font></p>\n");
$setup_info->DisplayFlush("<ul>\n");
$setup_info->DisplayFlush("  <li><font size=\"2\" color=#ffff00>A htm or html saved page from within you browser of Setup Info with <font size=\"2\" color=#0000fff00>\$setup_info->switches['Show_Env_Var']['enabled'] = true;</font> This is settable within setup_info.php.</font></li>\n");
$setup_info->DisplayFlush("  <li><font size=\"2\" color=#ffff00>What Operating System you are using.</font></li>\n");
$setup_info->DisplayFlush("  <li><font size=\"2\" color=#ffff00>What Version of Apache, PHP and mySQL that you are using.</font></li>\n");
$setup_info->DisplayFlush("  <li><font size=\"2\" color=#ffff00>And if using Windows OS are you using IIS.</font></li>\n");
$setup_info->DisplayFlush("</ul>\n");
$setup_info->DisplayFlush("<p><font size=\"2\">// With this information it will help me to help you much faster and also get my Script to display more reliable information.</font></p>\n");

$setup_info->do_Table_Title("DB Config Settings",$Cols);

$setup_info->do_Table_Blank_Row();
$game_path = $setup_info->get_gamepath();
$setup_info->do_Table_Row("gamepath","<strong>".(!$game_path['status'] ? "<font color='#ff0000'>{$game_path['info']}</font>" : $game_path['result'] )."</strong>");
if (!$game_path['status'])
{
    $setup_info->do_Table_Single_Row("Please set \$setup_info->switches['Show_Env_Var']['enabled'] = true; and email the page result to me.");
}

$setup_info->do_Table_Blank_Row();
$game_domain = $setup_info->get_gamedomain();
$setup_info->do_Table_Row("gamedomain","<strong>".(!$game_domain['status'] ? "<font color='#ff0000'>{$game_domain['info']}</font>" : $game_domain['result'] )."</strong>");
if (!$game_domain['status'])
{
    $setup_info->do_Table_Single_Row("Please set \$setup_info->switches['Show_Env_Var']['enabled'] = true; and email the page result to me.");
}

$setup_info->do_Table_Blank_Row();
$setup_info->do_Table_Single_Row("You need to set this information in db_config.php");
$setup_info->do_Table_Blank_Row();
$setup_info->do_Table_Footer();

/*
// Display BNT DataBase Status.
#$setup_info->DisplayFlush("<hr align='center' width='80%' size='1'>\n");
$setup_info->DisplayFlush("<br>\n");
$setup_info->DisplayFlush("<div style='width:80%; margin:auto; height:1px; background-color:#808080;'></div>\n");

$setup_info->DisplayFlush("<p><font size=\"2\">// This displays status on the BNT Database.</font></p>\n");
$Cols = 3;
$setup_info->do_Table_Title("Blacknova Traders Database Status",$Cols);

$DB_STATUS = $setup_info->validate_database();
$setup_info->do_Table_Row("TableCount",$DB_STATUS['status']);
$setup_info->do_Table_Blank_Row();
foreach ($DB_STATUS as $n => $s)
{
    if ($n!="status")
    {
        $setup_info->do_Table_Row($DB_STATUS[$n]['name'],$DB_STATUS[$n]['info'],$DB_STATUS[$n]['status']);
    }
}
$setup_info->do_Table_Blank_Row();
$setup_info->do_Table_Footer("<br>");
*/

// Display BNT Patch Status.
$setup_info->get_patch_info($patch_info);

#$setup_info->DisplayFlush("<hr align='center' width='80%' size='1'>\n");
$setup_info->DisplayFlush("<br>\n");
$setup_info->DisplayFlush("<div style='width:80%; margin:auto; height:1px; background-color:#808080;'></div>\n");

$setup_info->DisplayFlush("<p><font size=\"2\">// This displays Installed Patch information on the BNT Server.</font></p>\n");

$Cols = 3;
$setup_info->do_Table_Title("Testing for installed patches",$Cols);

foreach ($patch_info as $n => $s)
{
    $setup_info->do_Table_Row($patch_info[$n][0]['name'],$patch_info[$n][0]['info'],$patch_info[$n][0]['patched']);
    if ($patch_info[$n][0]['patched']!="Not Found")
    {
        $setup_info->do_Table_Row("Patch Information","<font color=\"maroon\">Author: </font><font color=\"purple\">".$patch_info[$n][1]['author']."</font><br>\n<font color=\"maroon\">Created: </font><font color=\"purple\">".$patch_info[$n][1]['created']."</font>");
    }
    $setup_info->do_Table_Blank_Row();
}
$setup_info->do_Table_Footer("<br>");

// This gets the Environment Variables
#$setup_info->DisplayFlush("<hr align='center' width='80%' size='1'>\n");
$setup_info->DisplayFlush("<br>\n");
$setup_info->DisplayFlush("<div style='width:80%; margin:auto; height:1px; background-color:#808080;'></div>\n");

$setup_info->DisplayFlush("<p><font size=\"2\">// This is used to help the admin of the server set up BNT, Or its used by me if you are having problems setting up BNT.</font></p>\n");

$Cols = 2;
$Wrap = true;
$setup_info->do_Table_Title("Environment Variables",$Cols);
if ($setup_info->get_env_variables($env_info))
{
    for ($n=0; $n <count($env_info); $n++)
    {
        $setup_info->do_Table_Row($env_info[$n]['name'],$env_info[$n]['value']);
    }
}
else
{
    $env_status = null;
    for ($n=0; $n <count($env_info['status']); $n++)
    {
        $env_status .= $env_info['status'][$n];
        if ($n < count($env_info['status']))
        {
            $env_status .="<br>";
        }
    }
    $setup_info->do_Table_Single_Row("<strong>$env_status</strong>");
}
$setup_info->do_Table_Footer("<br>");

// Current db_config Information.
#$setup_info->DisplayFlush("<hr align='center' width='80%' size='1'>\n");
$setup_info->DisplayFlush("<br>\n");
$setup_info->DisplayFlush("<div style='width:80%; margin:auto; height:1px; background-color:#808080;'></div>\n");

$setup_info->DisplayFlush("<p><font size=\"2\">// This is what you already have set in db_config.php.</font><br>\n");
$setup_info->DisplayFlush("<font size=\"2\">// This will also tell you if what you have set in db_config.php is the same as what Setup Info has Auto Detected.</font></p>\n");

$Cols = 3;
$setup_info->do_Table_Title("Current DB Config Information",$Cols);
$cur_cfg_loc = $setup_info->get_current_db_config_info();

if (is_array($cur_cfg_loc))
{
    for ($n=0; $n<count($cur_cfg_loc)-1;$n++)
    {
        if (is_string($cur_cfg_loc[$n]) & $cur_cfg_loc[$n] =="%SEPERATOR%")
        {
            $setup_info->do_Table_Blank_Row();
        }
        if (is_array($cur_cfg_loc[$n]))
        {
            if (count($cur_cfg_loc[$n])>2)
            {
                $setup_info->do_Table_Row($cur_cfg_loc[$n]['caption'],$cur_cfg_loc[$n]['value'],$cur_cfg_loc[$n]['status']);
            }
            else
            {
                $setup_info->do_Table_Row($cur_cfg_loc[$n]['caption'],$cur_cfg_loc[$n]['value']);
            }
        }
    }
}
$setup_info->do_Table_Footer("<br>");

// Current scheduler information.
#$setup_info->DisplayFlush("<hr align='center' width='80%' size='1'>\n");
$setup_info->DisplayFlush("<br>\n");
$setup_info->DisplayFlush("<div style='width:80%; margin:auto; height:1px; background-color:#808080;'></div>\n");

$setup_info->DisplayFlush("<p><font size=\"2\">// This displays the Scheduler information.</font></p>\n");

$Cols = 3;
$setup_info->do_Table_Title("Scheduler Information",$Cols);

$scheduler_info = $setup_info->get_scheduler_info();

for ($n=0; $n <count($scheduler_info); $n++)
{
    $setup_info->do_Table_Row($scheduler_info[$n]['name'],$scheduler_info[$n]['caption'], $scheduler_info[$n]['value']);
}
$setup_info->do_Table_Footer("<br>");

// My script information.
$setup_info->appinfo;

#$setup_info->DisplayFlush("<hr size=\"1\">\n");
$setup_info->DisplayFlush("<br>\n");
$setup_info->DisplayFlush("<div style='width:100%; margin:auto; height:1px; background-color:#808080;'></div>\n");

$setup_info->DisplayFlush("<div align=\"center\">\n");
$setup_info->DisplayFlush("  <center>\n");
$setup_info->DisplayFlush("  <table cellSpacing=\"0\" width=\"100%\" border=\"0\">\n");
$setup_info->DisplayFlush("<tbody>\n");
$setup_info->DisplayFlush("  <tr>\n");
$setup_info->DisplayFlush("    <td style=\"padding-top:4px;\" vAlign=\"top\" noWrap align=\"left\" width=\"50%\"><font size=\"1\" color=\"white\">Version <font color=\"lime\">{$setup_info->appinfo['version']} (<font color=\"white\">{$setup_info->appinfo['releasetype']}</font>)</font></font></td>\n");
$setup_info->DisplayFlush("        <td style=\"padding-top:4px;\" vAlign=\"top\" noWrap align=\"right\" width=\"50%\"><font size=\"1\" color=\"white\">Created on <font color=\"lime\">{$setup_info->appinfo['createdate']}</font></font></td>\n");
$setup_info->DisplayFlush("      </tr>\n");
$setup_info->DisplayFlush("      <tr>\n");
$setup_info->DisplayFlush("        <td style=\"padding-bottom:4px;\" vAlign=\"top\" noWrap align=\"left\" width=\"50%\"><font size=\"1\" color=\"white\">");

if (function_exists('md5_file'))
{
    $hash = strtoupper(md5_file(basename($_SERVER['PHP_SELF'])));
    $setup_info->DisplayFlush(" Hash: [<font color=\"yellow\">$hash</font>] - [<font color=\"yellow\">". $setup_info->appinfo['hash']."</font>]");
}
else
{
    $setup_info->DisplayFlush(" Hash: [<font color=\"yellow\">Disabled</font>]");
}

$setup_info->DisplayFlush("</font></td>\n");
$setup_info->DisplayFlush("        <td style=\"padding-bottom:4px;\" vAlign=\"top\" noWrap align=\"right\" width=\"50%\"><font size=\"1\" color=\"white\">Updated on <font color=\"lime\">{$setup_info->appinfo['updatedate']}</font></font></td>\n");
$setup_info->DisplayFlush("      </tr>\n");
$setup_info->DisplayFlush("    </tbody>\n");
$setup_info->DisplayFlush("  </table>\n");
$setup_info->DisplayFlush("  </center>\n");
$setup_info->DisplayFlush("</div>\n");
#$setup_info->DisplayFlush("<hr size=\"1\"><br>\n");
$setup_info->DisplayFlush("<div style='width:100%; margin:auto; height:1px; background-color:#808080;'></div>\n");
$setup_info->DisplayFlush("<br>\n");

if (empty($username))
{
    TEXT_GOTOLOGIN();
}
else
{
    TEXT_GOTOMAIN();
}
?>
</div></div>
<?
include "footer.php";
?>

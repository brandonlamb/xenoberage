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
// File: mail.php


include "config/config.php";

// New database driven language entries
load_languages($db, $lang, array('mail', 'common', 'global_funcs', 'global_includes', 'global_funcs', 'combat', 'footer', 'news'), $langvars, $db_logging);

$title = $l_mail_title;
include "header.php";
?>
<div class="tablecell content both-border">
	<div class="pad">
<?
bigtitle();

$result = $db->Execute ("SELECT character_name, email, password FROM {$db->prefix}ships WHERE ship_id=? LIMIT 1;", array($mail));
db_op_result ($db, $result, __LINE__, __FILE__, $db_logging);

if (!$result->EOF)
{
    $playerinfo = $result->fields;
    $l_mail_message = str_replace("[pass]", $playerinfo['password'], $l_mail_message);
    $l_mail_message = str_replace("[name]", $playerinfo['character_name'], $l_mail_message);
    $l_mail_message = str_replace("[ip]", $ip, $l_mail_message);
    $l_mail_message = str_replace("[game_name]", $game_name, $l_mail_message);

    # Some reason \r\n is broken, so replace them now.
    $l_mail_message = str_replace('\r\n', "\r\n", $l_mail_message);

    # Need to set the topic with the game name.
    $l_mail_topic = str_replace("[game_name]", $game_name, $l_mail_topic);

    $link_to_game = "http://";
    $link_to_game .= ltrim($gamedomain,".");// Trim off the leading . if any
    $link_to_game .= $gamepath;

    mail($playerinfo['email'], $l_mail_topic, "$l_mail_message\r\n\r\n{$link_to_game}\r\n", "From: {$admin_mail}\r\nReply-To: {$admin_mail}\r\nX-Mailer: PHP/" . phpversion());
    echo "<div style='color:#fff; width:400px; text-align:left; padding:6px;'>{$l_mail_sent} <span style='color:#0f0;'>{$mail}</span></div>\n";
    echo "<br>\n";
    echo "<div style='font-size:14px; font-weight:bold; color:#f00;'>Please Note: If you do not receive your emails within 5 to 10 mins of it being sent, please notify us as soon as possible either by email or on the forums.<br>DO NOT CREATE ANOTHER ACCOUNT, YOU MAY GET BANNED.</div>\n";
}
else
{
    $l_mail_noplayer=str_replace("[here]", "<a href='new_fb.php'>" . $l_here . "</a>", $l_mail_noplayer);
    echo "<div style='color:#FFF; width:400px; text-align:left; font-size:12px; padding:6px;'>{$l_mail_noplayer}</div>\n";

    echo "<br>\n";
    if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] == true)
    {
        TEXT_GOTOMAIN();
    }
    else
    {
        TEXT_GOTOLOGIN();
    }
}
?>
</div></div>
<?
include "footer.php";
?>

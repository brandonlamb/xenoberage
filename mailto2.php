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
// File: mailto2.php

include "config/config.php";
updatecookie();

// New database driven language entries
load_languages($db, $lang, array('mailto2', 'common', 'global_includes', 'global_funcs', 'footer', 'planet_report'), $langvars, $db_logging);

$title = $l_sendm_title;
include "header.php";
?>
<div class="tablecell content both-border">
	<div class="pad">
<?
if (checklogin())
{
    die();
}

$name = null;
if (array_key_exists('name', $_GET) == true)
{
    $name = (string) $_GET['name'];
}

$content = null;
if (array_key_exists('content', $_POST) == true)
{
    $content = (string) $_POST['content'];
}

$subject = null;
if (array_key_exists('subject', $_REQUEST) == true)
{
    $subject = (string) $_REQUEST['subject'];
}

$to = null;
if (array_key_exists('to', $_POST) == true)
{
    $to = (string) $_POST['to'];
}

$res = $db->Execute("SELECT * FROM {$db->prefix}ships WHERE ship_id=?", array($user_ship_id));
db_op_result ($db, $res, __LINE__, __FILE__, $db_logging);
$playerinfo = $res->fields;

bigtitle();

if (is_null($content))
{
    $res = $db->Execute("SELECT character_name FROM {$db->prefix}ships WHERE email NOT LIKE '%@Xenobe' AND ship_destroyed ='N' AND turns_used > 0 AND ship_id <> {$playerinfo['ship_id']} ORDER BY character_name ASC");
    db_op_result ($db, $res, __LINE__, __FILE__, $db_logging);
    $res2 = $db->Execute("SELECT team_name FROM {$db->prefix}teams WHERE admin ='N' ORDER BY team_name ASC");
    db_op_result ($db, $res2, __LINE__, __FILE__, $db_logging);
    echo "<form action=mailto2.php method=post>\n";
    echo "  <table>\n";
    echo "    <tr>\n";
    echo "      <td>$l_sendm_to:</td>\n";
    echo "      <td>\n";
    echo "        <select NAME=to style='width:200px;'>\n";

    # Add self to list.
    echo "          <OPTION".(($playerinfo['character_name']==$name)?" selected":"").">{$playerinfo['character_name']}</OPTION>\n";

    while (!$res->EOF)
    {
        $row = $res->fields;
        echo "          <OPTION".(($row['character_name']==$name)?" selected":"").">{$row['character_name']}</OPTION>\n";
        $res->MoveNext();
    }

    while (!$res2->EOF && $res2->fields != null)
    {
        $row2 = $res2->fields;
        echo "          <OPTION>$l_sendm_ally $row2[team_name]</OPTION>\n";
        $res2->MoveNext();
    }

    echo "        </select>\n";
    echo "      </td>\n";
    echo "    </tr>\n";
    echo "    <tr>\n";
    echo "      <td>$l_sendm_from:</td>\n";
    echo "      <td><input disabled type='text' name='dummy' size='40' maxlength='40' value=\"$playerinfo[character_name]\"></td>\n";
    echo "    </tr>\n";
    if (isset($subject))
    {
        $subject = "RE: " . $subject;
    }
    else
    {
        $subject = '';
    }

    echo "    <tr>\n";
    echo "      <td>$l_sendm_subj:</td>\n";
    echo "      <td><INPUT TYPE=TEXT NAME=subject SIZE=40 MAXLENGTH=40 VALUE=\"$subject\"></td>\n";
    echo "    </tr>\n";
    echo "    <tr>\n";
    echo "      <td>$l_sendm_mess:</td>\n";
    echo "      <td><TEXTAREA NAME=content ROWS=5 COLS=40></TEXTAREA></td>\n";
    echo "    </tr>";
    echo "    <tr>\n";
    echo "      <td></td>\n";
    echo "      <td><INPUT TYPE=SUBMIT VALUE=$l_sendm_send><INPUT TYPE=RESET VALUE=$l_reset></td>\n";
    echo "    </tr>\n";
    echo "  </table>\n";
    echo "</form>\n";
}
else
{
    echo "$l_sendm_sent<br><br>";

    if (strpos($to, $l_sendm_ally)===false)
    {
        $timestamp = date("Y\-m\-d H\:i\:s");
        $res = $db->Execute("SELECT * FROM {$db->prefix}ships WHERE character_name=?;", array($to));
        db_op_result ($db, $res, __LINE__, __FILE__, $db_logging);
        $target_info = $res->fields;
        $content = htmlspecialchars($content);
        $content = addslashes($content);
        $subject = htmlspecialchars($subject);
        $subject = addslashes($subject);
        $resx = $db->Execute("INSERT INTO {$db->prefix}messages (sender_id, recp_id, sent, subject, message) VALUES (?, ?, ?, ?, ?);", array($playerinfo['ship_id'], $target_info['ship_id'], $timestamp, $subject, $content));
        db_op_result ($db, $resx, __LINE__, __FILE__, $db_logging);
        if (mysql_errno() != 0)
        {
            echo "Message failed to send.<br>\n";
        }
    }
    else
    {
        $timestamp = date("Y\-m\-d H\:i\:s");
        $to = str_replace ($l_sendm_ally, "", $to);
        $to = trim($to);
        $to = addslashes($to);
        $res = $db->Execute("SELECT id FROM {$db->prefix}teams WHERE team_name=?;", array($to));
        db_op_result ($db, $res, __LINE__, __FILE__, $db_logging);
        $row = $res->fields;

        $res2 = $db->Execute("SELECT * FROM {$db->prefix}ships WHERE team=?;", array($row['id']));
        db_op_result ($db, $res2, __LINE__, __FILE__, $db_logging);

        while (!$res2->EOF)
        {
            $row2 = $res2->fields;
            $resx = $db->Execute("INSERT INTO {$db->prefix}messages (sender_id, recp_id, sent, subject, message) VALUES (?, ?, ?, ?, ?);", array($playerinfo['ship_id'], $row2['ship_id'], $timestamp, $subject, $content));
            db_op_result ($db, $resx, __LINE__, __FILE__, $db_logging);
            $res2->MoveNext();
        }
   }
}

TEXT_GOTOMAIN();
?>
</div></div>
<?
include "footer.php";
?>

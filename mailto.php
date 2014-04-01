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
// File: mailto.php

include "config/config.php";
updatecookie ();

// New database driven language entries
load_languages($db, $lang, array('mailto', 'common', 'global_includes', 'global_funcs', 'footer', 'news'), $langvars, $db_logging);

$title = $l_mt_title;
include "header.php";
?>
<div class="tablecell content both-border">
	<div class="pad">
<?
if (checklogin () )
{
    die();
}

$res = $db->Execute("SELECT * FROM {$db->prefix}ships WHERE ship_id='$user_ship_id'");
db_op_result ($db, $res, __LINE__, __FILE__, $db_logging);
$playerinfo = $res->fields;

bigtitle ();

if (empty($content))
{
    $res = $db->Execute("SELECT * FROM {$db->prefix}ships WHERE ship_destroyed = 'N' AND turns_used > 0 ORDER BY character_name ASC");
    db_op_result ($db, $res, __LINE__, __FILE__, $db_logging);
    echo "<form action=mailto2.php method=post>";
    echo "<table>";
    echo "<tr><td>To:</td><td><select name=to style='width:200px;'>";
    while (!$res->EOF)
    {
        $row = $res->fields;
        if ($row['ship_id'] == $to)
        {
            echo "\n<option selectED>$row[character_name]</option>";
        }
        else
        {
            echo "\n<option>$row[character_name]</option>";
        }
        $res->MoveNext();
    }
    echo "</select></td></tr>";
    echo "<tr><td>$l_mt_from</td><td><INPUT DISABLED TYPE=TEXT name=dummy SIZE=40 MAXLENGTH=40 VALUE=\"$playerinfo[character_name]\"></td></tr>";
    echo "<tr><td>$l_mt_subject</td><td><INPUT TYPE=TEXT name=subject SIZE=40 MAXLENGTH=40></td></tr>";
    echo "<tr><td>$l_mt_message:</td><td><TEXTAREA name=content ROWS=5 COLS=40></TEXTAREA></td></tr>";
    echo "<tr><td></td><td><INPUT TYPE=SUBMIT VALUE=$l_mt_send><INPUT TYPE=RESET VALUE=Clear></td>";
    echo "</table>";
    echo "</form>";
}
else
{
    echo "$l_mt_sent<br><br>";
    $content = htmlspecialchars ($content);
    $subject = htmlspecialchars ($subject);

    $res = $db->Execute("SELECT * FROM {$db->prefix}ships WHERE character_name='$to'");
    db_op_result ($db, $res, __LINE__, __FILE__, $db_logging);
    $target_info = $res->fields;
    $db->Execute("INSERT INTO messages (sender_id, recp_id, subject, message) VALUES ('".$playerinfo[ship_id]."', '".$target_info[ship_id]."', '".$subject."', '".$content."')");
    // Using this three lines to get recipients ship_id and sending the message -- blindcoder
}

TEXT_GOTOMAIN();
?>
</div></div>
<?
include "footer.php";
?>

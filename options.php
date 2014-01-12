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
// File: options.php

include "config/config.php";

// New database driven language entries
load_languages($db, $lang, array('options', 'common', 'global_includes', 'global_funcs', 'footer'), $langvars, $db_logging);
updatecookie ();

$body_class = 'options';
$title = $l_opt_title;
include "header.php";

if (checklogin () )
{
    die ();
}
?>
<div class="tablecell content both-border">
	<div class="pad">
<?
bigtitle ();

$res = $db->Execute("SELECT * FROM {$db->prefix}ships WHERE email='$username'");
$playerinfo = $res->fields;

echo "<form action=option2.php method=post>";
echo "<table>";
echo "<tr>";
echo "<th colspan=2><strong>$l_opt_chpass</strong></th>";
echo "</tr>";
echo "<tr>";
echo "<td>$l_opt_curpass</td>";
echo "<td><input type=password name=oldpass size=16 maxlength=16 value=\"\"></td>";
echo "</tr>";
echo "<tr>";
echo "<td>$l_opt_newpass</td>";
echo "<td><input type=password name=newpass1 size=16 maxlength=16 value=\"\"></td>";
echo "</tr>";
echo "<tr>";
echo "<td>$l_opt_newpagain</td>";
echo "<td><input type=password name=newpass2 size=16 maxlength=16 value=\"\"></td>";
echo "</tr>";
echo "<tr>";
echo "<th colspan=2><strong>$l_opt_lang</strong></th>";
echo "</tr>";
echo "<tr>";
echo "<td>$l_opt_select</td><td><select name=newlang>";

foreach ($avail_lang as $curlang)
{
    if ($curlang['file'] == $playerinfo['lang'])
    {
        $selected = "selected";
    }
    else
    {
        $selected = "";
    }
    echo "<option value=" . $curlang['file'] . " " . $selected . ">" . $curlang['name'] . "</option>";
}

echo "</select></td>";
echo "</tr>";
echo "</table>";
echo "<br>";
echo "<input type=submit value=$l_opt_save>";
echo "</form><br>";

TEXT_GOTOMAIN ();
?>
</div></div>
<?
include "footer.php";
?>

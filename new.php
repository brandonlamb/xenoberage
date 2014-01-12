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
// File: new.php

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

// New database driven language entries
load_languages($db, $lang, array('new', 'login', 'common', 'global_includes', 'global_funcs', 'footer', 'news'), $langvars, $db_logging);

$title = $l_new_title;
include "header.php";
?>
<div class="tablecell content both-border">
	<div class="pad">
<?
bigtitle ();

echo "<form action='new2.php" . $link . "' method='post'>\n";
echo "    <dl class='twocolumn-form'>\n";
echo "        <dt style='padding:3px'><label for='username'>{$l_login_email}:</label></dt>\n";
echo "        <dd style='padding:3px'><input type='text' id='username' name='username' size='20' maxlength='40' value='' style='width:200px'></dd>\n";
echo "        <dt style='padding:3px'><label for='shipname'>{$l_new_shipname}:</label></dt>\n";
echo "        <dd style='padding:3px'><input type='text' id='shipname' name='shipname' size='20' maxlength='20' value='' style='width:200px'></dd>\n";
echo "        <dt style='padding:3px'><label for='character'>{$l_new_pname}:</label></dt>\n";
echo "        <dd style='padding:3px'><input type='text' id='character' name='character' size='20' maxlength='20' value='' style='width:200px'></dd>\n";
echo "    </dl>\n";
echo "    <br style='clear:both;'><br>";
echo "    <div style='text-align:center'><input type='submit' value='" . $l_submit . "'>&nbsp;<input type='reset' value='" . $l_reset . "'><br><br>\n";
echo "        " . $l_new_info . "<br></div>\n";
echo "</form>";
?>
</div></div>
<?
include "footer.php";
?>

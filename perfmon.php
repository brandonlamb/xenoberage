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
// File: perfmon.php

include "config/config.php";
updatecookie();

// New database driven language entries
load_languages($db, $lang, array('common', 'global_includes', 'global_funcs', 'footer', 'news'), $langvars, $db_logging);

$title = "Performance Monitor";
include "header.php";
?>
<div class="tablecell content both-border">
	<div class="pad">
<?
bigtitle();

$swordfish = null;
if (array_key_exists('swordfish', $_POST))
{
    $swordfish = $_POST['swordfish'];
}


if ($swordfish != $adminpass)
{
    echo "<form action='perfmon.php' method='post'>";
    echo "Password: <input type='password' name='swordfish' size='20' maxlength='20'><br><br>";
    echo "<input type='submit' value='Submit'><input type='reset' value='Reset'>";
    echo "</form>";
}
else
{
    define('ADODB_PERF_NO_RUN_SQL',1);
    $perf =& NewPerfMonitor($db);

    echo '<style type="text/css">';
    echo '<!--  ';
    echo 'TABLE            { background-color: #000;}';
    echo '-->';
    echo '</style>';

    echo $perf->HealthCheck(); // Not using this until adodb patches removing bgcolor=white are accepted
    echo $perf->SuspiciousSQL(10);
    echo $perf->ExpensiveSQL(10);
    echo $perf->InvalidSQL(10);
}
echo "<br />\n";
TEXT_GOTOMAIN();
?>
</div></div>
<?
include "footer.php";
?>

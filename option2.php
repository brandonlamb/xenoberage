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
// File: option2.php

include "config/config.php";
if (checklogin () )
{
    die ();
}

global $l_opt2_title;
$title = $l_opt2_title;

if ($newpass1 == $newpass2 && $password == $oldpass && $newpass1 != "")
{
	$data = array('username'=>$username, 'password'=>$newpass1,'user_id'=>$user_ship_id);
	$data=serialize($data);
	setcookie("userID", $data, time() + (3600*24)*365, $gamepath, $gamedomain);
}

if (!preg_match("/^[\w]+$/", $newlang))
{
    $newlang = $default_lang;
}
else
{
    $lang = $_POST['newlang'];
}

// New database driven language entries
load_languages($db, $lang, array('option2', 'common', 'global_includes', 'global_funcs', 'combat', 'footer', 'news'), $langvars, $db_logging);

include "header.php";
?>
<div class="tablecell content both-border">
	<div class="pad">
<?
bigtitle ();

if ($newpass1 == "" && $newpass2 == "")
{
    echo $l_opt2_passunchanged . "<br><br>";
}
elseif ($password != $oldpass)
{
    echo $l_opt2_srcpassfalse . "<br><br>";
}
elseif ($newpass1 != $newpass2)
{
    echo $l_opt2_newpassnomatch . "<br><br>";
}
else
{
    $res = $db->Execute("SELECT ship_id,password FROM {$db->prefix}ships WHERE email='$username'");
    db_op_result ($db, $res, __LINE__, __FILE__, $db_logging);
    $playerinfo = $res->fields;
    if ($oldpass != $playerinfo['password'])
    {
        echo $l_opt2_srcpassfalse;
    }
    else
    {
        $res = $db->Execute("UPDATE {$db->prefix}ships SET password='$newpass1' WHERE ship_id=$playerinfo[ship_id]");
        db_op_result ($db, $res, __LINE__, __FILE__, $db_logging);
        if ($res)
        {
            echo $l_opt2_passchanged . "<br><br>";
        }
        else
        {
            echo $l_opt2_passchangeerr . "<br><br>";
        }
    }
}


$res = $db->Execute("UPDATE {$db->prefix}ships SET lang='$lang' WHERE email='$username'");
db_op_result ($db, $res, __LINE__, __FILE__, $db_logging);
foreach ($avail_lang as $curlang)
{
    if ($lang == $curlang['file'])
    {
        $l_opt2_chlang = str_replace("[lang]", "$curlang[name]", $l_opt2_chlang);
        echo $l_opt2_chlang . "<p>";
        break;
    }
}

echo "<br>";
TEXT_GOTOMAIN();
?>
</div></div>
<?
include "footer.php";
?>

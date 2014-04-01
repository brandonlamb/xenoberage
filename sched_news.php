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
// File: sched_news.php

/***********************************************************
This file includes the default language for now, so that news
are generated in the server's default language. The news text
will have to be removed from the database for the next version
************************************************************/

if (preg_match("/sched_news.php/i", $_SERVER['PHP_SELF']))
{
    echo "You can not access this file directly!";
    die();
}

global $default_lang;

// New database driven language entries
load_languages($db, $lang, array('admin', 'common', 'global_includes', 'global_funcs', 'footer', 'news'), $langvars, $db_logging);

echo "<strong>Posting News</strong><br><br>";

// Generation of planet amount
$sql = $db->Execute("SELECT COUNT(owner) AS amount, owner FROM {$db->prefix}planets WHERE owner !='0' GROUP BY owner ORDER BY amount ASC");
db_op_result ($db, $sql, __LINE__, __FILE__, $db_logging);

while (!$sql->EOF)
{
    $row = $sql->fields;
    if ($row['amount'] >= 150)
    {
        $sql2 = $db->Execute("SELECT * FROM {$db->prefix}news WHERE user_id=? AND news_type='planet150';", array($row['owner']));
        db_op_result ($db, $sql2, __LINE__, __FILE__, $db_logging);
        
        if ($sql2->EOF)
        {
			$l_news_p_text150 = "The enormous vast empire of [name], represented by 150 planets in the whole galaxy is getting a threatening strength. One of the BNN reporters found out that [name] is upgrading his ship planning a major war. In an interview [name] announced that is done on defence purpose only!";
            $planetcount = 150;
            $name = get_player_name($row['owner']);
            $l_news_p_headline2=str_replace("[player]",$name,$l_news_p_headline);
            $headline = $l_news_p_headline2 ." ". $planetcount ." ". $l_news_planets;
            $l_news_p_text1502=str_replace("[name]",$name,$l_news_p_text150);
            $news = $db->Execute("INSERT INTO {$db->prefix}news (headline, newstext, user_id, date, news_type) VALUES (?, ?, ?, NOW(), 'planet100');", array($headline, $l_news_p_text1502, $row['owner']));
            db_op_result ($db, $news, __LINE__, __FILE__, $db_logging);
        }
    }
    else if ($row['amount'] >= 100)
    {
        $sql2 = $db->Execute("SELECT * FROM {$db->prefix}news WHERE user_id=? AND news_type='planet100';", array($row['owner']));
        db_op_result ($db, $sql2, __LINE__, __FILE__, $db_logging);
        
        if ($sql2->EOF)
        {
			$l_news_p_text100 = "The enormous vast empire of [name], represented by 100 planets in the whole galaxy is getting a threatening strength. One of the BNN reporters found out that [name] is upgrading his ship planning a major war. In an interview [name] announced that is done on defence purpose only!";
            $planetcount = 100;
            $name = get_player_name($row['owner']);
            $l_news_p_headline2=str_replace("[player]",$name,$l_news_p_headline);
            $headline = $l_news_p_headline2 ." ". $planetcount ." ". $l_news_planets;
            $l_news_p_text1002=str_replace("[name]",$name,$l_news_p_text100);
            $news = $db->Execute("INSERT INTO {$db->prefix}news (headline, newstext, user_id, date, news_type) VALUES (?, ?, ?, NOW(), 'planet100');", array($headline, $l_news_p_text1002, $row['owner']));
            db_op_result ($db, $news, __LINE__, __FILE__, $db_logging);
        }
    }
    else if ($row['amount'] >= 50)
    {
        $sql2 = $db->Execute("SELECT * FROM {$db->prefix}news WHERE user_id=? AND news_type='planet50';", array($row['owner']));
        db_op_result ($db, $sql2, __LINE__, __FILE__, $db_logging);
        
        if ($sql2->EOF)
        {
            $planetcount = 50;
            $name = get_player_name($row['owner']);
            $l_news_p_headline2=str_replace("[player]",$name,$l_news_p_headline);
            $headline = $l_news_p_headline2 ." ". $planetcount ." ". $l_news_planets;
            $l_news_p_text502=str_replace("[name]",$name,$l_news_p_text50);
            $news = $db->Execute("INSERT INTO {$db->prefix}news (headline, newstext, user_id, date, news_type) VALUES (?, ?, ?, NOW(), 'planet50');", array($headline, $l_news_p_text502, $row['owner']));
            db_op_result ($db, $news, __LINE__, __FILE__, $db_logging);
        }
    }
    elseif ($row['amount'] >= 25)
    {
        $sql2 = $db->Execute("SELECT * FROM {$db->prefix}news WHERE user_id=? AND news_type='planet25';", array($row['owner']));
        db_op_result ($db, $sql2, __LINE__, __FILE__, $db_logging);
        
        if ($sql2->EOF)
        {
            $planetcount = 25;
            $name = get_player_name($row['owner']);
            $l_news_p_headline2=str_replace("[player]",$name,$l_news_p_headline);
            $headline = $l_news_p_headline2 ." ". $planetcount ." ". $l_news_planets;
            $l_news_p_text252=str_replace("[name]",$name,$l_news_p_text25);
            $news = $db->Execute("INSERT INTO {$db->prefix}news (headline, newstext, user_id, date, news_type) VALUES (?, ?, ?, NOW(), 'planet25');", array($headline, $l_news_p_text252, $row['owner']));
            db_op_result ($db, $news, __LINE__, __FILE__, $db_logging);
        }
    }
    elseif ($row['amount'] >= 10)
    {
        $sql2 = $db->Execute("SELECT * FROM {$db->prefix}news WHERE user_id='$row[owner]' AND news_type='planet10'");
        db_op_result ($db, $sql2, __LINE__, __FILE__, $db_logging);
        
        if ($sql2->EOF)
        {
            $planetcount = 10;
            $name = get_player_name($row['owner']);
            $l_news_p_headline2=str_replace("[player]",$name,$l_news_p_headline);
            $headline = $l_news_p_headline2 ." ". $planetcount ." ". $l_news_planets;
            $l_news_p_text102=str_replace("[name]",$name,$l_news_p_text10);
            $news = $db->Execute("INSERT INTO {$db->prefix}news (headline, newstext, user_id, date, news_type) VALUES (?, ?, ?, NOW(), 'planet10');", array($headline, $l_news_p_text102, $row['owner']));
            db_op_result ($db, $news, __LINE__, __FILE__, $db_logging);
        }
    }
    elseif ($row['amount'] >= 5)
    {
        $sql2 = $db->Execute("SELECT * FROM {$db->prefix}news WHERE user_id=? AND news_type='planet5';", array($row['owner']));
        db_op_result ($db, $sql2, __LINE__, __FILE__, $db_logging);
        
        if ($sql2->EOF)
        {
            $planetcount = 5;
            $name = get_player_name($row['owner']);
            $l_news_p_headline2=str_replace("[player]",$name,$l_news_p_headline);
            $headline = $l_news_p_headline2 ." ". $planetcount ." ". $l_news_planets;
            $l_news_p_text52=str_replace("[name]",$name,$l_news_p_text5);
            $news = $db->Execute("INSERT INTO {$db->prefix}news (headline, newstext, user_id, date, news_type) VALUES (?, ?, ?, NOW(), 'planet5');", array($headline, $l_news_p_text52, $row['owner']));
            db_op_result ($db, $news, __LINE__, __FILE__, $db_logging);
        }
    }
    $sql->MoveNext();
} // while
// end generation of planet amount


// generation of colonist amount
$sql = $db->Execute("SELECT SUM(colonists) AS amount, owner FROM {$db->prefix}planets WHERE owner !='0' GROUP BY owner ORDER BY amount ASC;");
db_op_result ($db, $sql, __LINE__, __FILE__, $db_logging);

while (!$sql->EOF)
{
    $row = $sql->fields;
    if ($row['amount'] >= 1000000000000)
    {
        $sql2 = $db->Execute("SELECT * FROM {$db->prefix}news WHERE user_id = ? AND news_type = 'col_X2';", array($row['owner']));
        db_op_result ($db, $sql2, __LINE__, __FILE__, $db_logging);
        
        if ($sql2->EOF)
        {
			$l_news_c_text_X2 = "The humongous empire of [name] now has 1 Trillion colonists, BNN reporters found out that [name] is in possesion of some weird cloning mechanism allowing him to breed new colonists in huge amounts. With this amount of colonists, the econmic strength of this empire is enormous, BNN hopes that [name] does not spend his money on warfare";
            $name = get_player_name($row['owner']);
            $l_news_p_headline2=str_replace("[player]",$name,$l_news_p_headline);
            $headline = $l_news_p_headline2 ." 1 Trillion Colonists";
            $l_news_c_text_X22=str_replace("[name]",$name,$l_news_c_text_X2);
            $news = $db->Execute("INSERT INTO {$db->prefix}news (headline, newstext, user_id, date, news_type) VALUES (?, ?, ?, NOW(), 'col_X2');", array($headline, $l_news_c_text_X22, $row['owner']));
            db_op_result ($db, $news, __LINE__, __FILE__, $db_logging);
        }
    }
    else if ($row['amount'] >= 500000000000)
    {
        $sql2 = $db->Execute("SELECT * FROM {$db->prefix}news WHERE user_id = ? AND news_type = 'col_X1';", array($row['owner']));
        db_op_result ($db, $sql2, __LINE__, __FILE__, $db_logging);
        
        if ($sql2->EOF)
        {
			$l_news_c_text_X1 = "The humongous empire of [name] now has 500 Billion colonists, BNN reporters found out that [name] is in possesion of some weird cloning mechanism allowing him to breed new colonists in huge amounts. With this amount of colonists, the econmic strength of this empire is enormous, BNN hopes that [name] does not spend his money on warfare";
            $name = get_player_name($row['owner']);
            $l_news_p_headline2=str_replace("[player]",$name,$l_news_p_headline);
            $headline = $l_news_p_headline2 ." 500 Billion Colonists";
            $l_news_c_text_X12=str_replace("[name]",$name,$l_news_c_text_X1);
            $news = $db->Execute("INSERT INTO {$db->prefix}news (headline, newstext, user_id, date, news_type) VALUES (?, ?, ?, NOW(), 'col_X1');", array($headline, $l_news_c_text_X12, $row['owner']));
            db_op_result ($db, $news, __LINE__, __FILE__, $db_logging);
        }
    }
    else if ($row['amount'] >= 100000000000)
    {
        $sql2 = $db->Execute("SELECT * FROM {$db->prefix}news WHERE user_id = ? AND news_type = 'col100000';", array($row['owner']));
        db_op_result ($db, $sql2, __LINE__, __FILE__, $db_logging);
        
        if ($sql2->EOF)
        {
			$l_news_c_text100000 = "The humongous empire of [name] now has 100 Billion colonists, BNN reporters found out that [name] is in possesion of some weird cloning mechanism allowing him to breed new colonists in huge amounts. With this amount of colonists, the econmic strength of this empire is enormous, BNN hopes that [name] does not spend his money on warfare";
            $colcount = 100000;
            $name = get_player_name($row['owner']);
            $l_news_p_headline2=str_replace("[player]",$name,$l_news_p_headline);
            $headline = $l_news_p_headline2 ." 100 Billion Colonists";
            $l_news_c_text1000002=str_replace("[name]",$name,$l_news_c_text100000);
            $news = $db->Execute("INSERT INTO {$db->prefix}news (headline, newstext, user_id, date, news_type) VALUES (?, ?, ?, NOW(), 'col100000');", array($headline, $l_news_c_text1000002, $row['owner']));
            db_op_result ($db, $news, __LINE__, __FILE__, $db_logging);
        }
    }
    else if ($row['amount'] >= 10000000000)
    {
        $sql2 = $db->Execute("SELECT * FROM {$db->prefix}news WHERE user_id = ? AND news_type = 'col10000';", array($row['owner']));
        db_op_result ($db, $sql2, __LINE__, __FILE__, $db_logging);
        
        if ($sql2->EOF)
        {
			$l_news_c_text10000 = "The humongous empire of [name] now has 10 Billion colonists, BNN reporters found out that [name] is in possesion of some weird cloning mechanism allowing him to breed new colonists in huge amounts. With this amount of colonists, the econmic strength of this empire is enormous, BNN hopes that [name] does not spend his money on warfare";
            $colcount = 10000;
            $name = get_player_name($row['owner']);
            $l_news_p_headline2=str_replace("[player]",$name,$l_news_p_headline);
            $headline = $l_news_p_headline2 ." 10 Billion Colonists";
            $l_news_c_text100002=str_replace("[name]",$name,$l_news_c_text10000);
            $news = $db->Execute("INSERT INTO {$db->prefix}news (headline, newstext, user_id, date, news_type) VALUES (?, ?, ?, NOW(), 'col10000');", array($headline, $l_news_c_text100002, $row['owner']));
            db_op_result ($db, $news, __LINE__, __FILE__, $db_logging);
        }
    }
    else if ($row['amount'] >= 1000000000)
    {
        $sql2 = $db->Execute("SELECT * FROM {$db->prefix}news WHERE user_id = ? AND news_type = 'col1000';", array($row['owner']));
        db_op_result ($db, $sql2, __LINE__, __FILE__, $db_logging);
        
        if ($sql2->EOF)
        {
            $colcount = 1000;
            $name = get_player_name($row['owner']);
            $l_news_p_headline2=str_replace("[player]",$name,$l_news_p_headline);
            $headline = $l_news_p_headline2 ." ". $colcount ." ". $l_news_cols;
            $l_news_c_text10002=str_replace("[name]",$name,$l_news_c_text1000);
            $news = $db->Execute("INSERT INTO {$db->prefix}news (headline, newstext, user_id, date, news_type) VALUES (?, ?, ?, NOW(), 'col1000');", array($headline, $l_news_c_text10002, $row['owner']));
            db_op_result ($db, $news, __LINE__, __FILE__, $db_logging);
        }
    }
    elseif ($row['amount'] >= 500000000)
    {
        $sql2 = $db->Execute("SELECT * FROM {$db->prefix}news WHERE user_id=? AND news_type='col500';", array($row['owner']));
        db_op_result ($db, $sql2, __LINE__, __FILE__, $db_logging);
        
        if ($sql2->EOF)
        {
            $colcount = 500;
            $name = get_player_name($row['owner']);
            $l_news_p_headline2=str_replace("[player]",$name,$l_news_p_headline);
            $headline = $l_news_p_headline2 ." ". $colcount ." ". $l_news_cols;
            $l_news_c_text5002=str_replace("[name]",$name,$l_news_c_text500);
            $news = $db->Execute("INSERT INTO {$db->prefix}news (headline, newstext, user_id, date, news_type) VALUES (?, ?, ?, NOW(), 'col500');", array($headline, $l_news_c_text5002, $row['owner']));
            db_op_result ($db, $news, __LINE__, __FILE__, $db_logging);
        }
    }
    elseif ($row['amount'] >= 100000000)
    {
        $sql2 = $db->Execute("SELECT * FROM {$db->prefix}news WHERE user_id=? AND news_type='col100';", array($row['owner']));
        db_op_result ($db, $sql2, __LINE__, __FILE__, $db_logging);
        
        if ($sql2->EOF)
        {
            $colcount = 100;
            $name = get_player_name($row['owner']);
            $l_news_p_headline2=str_replace("[player]",$name,$l_news_p_headline);
            $headline = $l_news_p_headline2 ." ". $colcount ." ". $l_news_cols;
            $l_news_c_text1002=str_replace("[name]",$name,$l_news_c_text100);
            $news = $db->Execute("INSERT INTO {$db->prefix}news (headline, newstext, user_id, date, news_type) VALUES (?, ?, ?, NOW(), 'col100');", array($headline, $l_news_c_text1002, $row['owner']));
            db_op_result ($db, $news, __LINE__, __FILE__, $db_logging);
        }
    }
    elseif ($row['amount'] >= 25000000)
    {
        $sql2 = $db->Execute("SELECT * FROM {$db->prefix}news WHERE user_id=? AND news_type='col25';", array($row['owner']));
        db_op_result ($db, $sql2, __LINE__, __FILE__, $db_logging);
        
        if ($sql2->EOF)
        {
            $colcount = 25;
            $name = get_player_name($row['owner']);
            $l_news_p_headline2=str_replace("[player]",$name,$l_news_p_headline);
            $headline = $l_news_p_headline2 ." ". $colcount ." ". $l_news_cols;
            $l_news_c_text252=str_replace("[name]",$name,$l_news_c_text25);
            $news = $db->Execute("INSERT INTO {$db->prefix}news (headline, newstext, user_id, date, news_type) VALUES (?, ?, ?, NOW(), 'col25');", array($headline, $l_news_c_text252, $row['owner']));
            db_op_result ($db, $news, __LINE__, __FILE__, $db_logging);
        }
    }
    $sql->MoveNext();
} // while
// end generation of colonist amount

$sql = $db->Execute("SELECT SUM(credits) AS amount, owner FROM {$db->prefix}planets WHERE owner !='0' GROUP BY owner ORDER BY amount ASC;");
db_op_result ($db, $sql, __LINE__, __FILE__, $db_logging);

while (!$sql->EOF)
{
    $row = $sql->fields;
    if ($row['amount'] >= 500000000000)
    {
        $sql2 = $db->Execute("SELECT * FROM {$db->prefix}news WHERE user_id = ? AND news_type = 'credit_1';", array($row['owner']));
        db_op_result ($db, $sql2, __LINE__, __FILE__, $db_logging);
        
        if ($sql2->EOF)
        {
			$name = get_player_name($row['owner']);
			$headline = "Federation Credit Inspection Report";
			$l_news = "While doing routine checks on planets, it was discovered that ".$name."'s empire has amassed a vast wealth of over 500 Billion credits in storage, we will be keeping a close eye on the books from here on out.";
            $news = $db->Execute("INSERT INTO {$db->prefix}news (headline, newstext, user_id, date, news_type) VALUES (?, ?, ?, NOW(), 'credit_1');", array($headline, $l_news, $row['owner']));
            db_op_result ($db, $news, __LINE__, __FILE__, $db_logging);
        }
    }
    else if ($row['amount'] >= 1000000000000)
    {
        $sql2 = $db->Execute("SELECT * FROM {$db->prefix}news WHERE user_id = ? AND news_type = 'credit_2';", array($row['owner']));
        db_op_result ($db, $sql2, __LINE__, __FILE__, $db_logging);
        
        if ($sql2->EOF)
        {
			$name = get_player_name($row['owner']);
			$headline = "Federation Credit Inspection Report";
			$l_news = "While doing routine checks on planets, it was discovered that ".$name."'s empire has amassed a vast wealth of over 1 Trillion credits in storage, why ".$name." is building up a large cash reserve has yet to be determined!.";
            $news = $db->Execute("INSERT INTO {$db->prefix}news (headline, newstext, user_id, date, news_type) VALUES (?, ?, ?, NOW(), 'credit_2');", array($headline, $l_news, $row['owner']));
            db_op_result ($db, $news, __LINE__, __FILE__, $db_logging);
        }
    }
    else if ($row['amount'] >= 100000000000000)
    {
        $sql2 = $db->Execute("SELECT * FROM {$db->prefix}news WHERE user_id = ? AND news_type = 'credit_3';", array($row['owner']));
        db_op_result ($db, $sql2, __LINE__, __FILE__, $db_logging);
        
        if ($sql2->EOF)
        {
			$name = get_player_name($row['owner']);
			$headline = "Federation Credit Inspection Report";
			$l_news = "After previous reports, inspectors organised another inspection of ".$name."'s banks, however while carrying out this inspection, the inspectors went missing. A coded message was later intercepted, and contain the following.. ".$name." has amassed a vast wealth of over 100 trillion credits, and is in a position to threaten the stability of the federation.";
            $news = $db->Execute("INSERT INTO {$db->prefix}news (headline, newstext, user_id, date, news_type) VALUES (?, ?, ?, NOW(), 'credit_3');", array($headline, $l_news, $row['owner']));
            db_op_result ($db, $news, __LINE__, __FILE__, $db_logging);
        }
    }
    $sql->MoveNext();
} // while

$multiplier = 0; // No need to run this again
?>

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
// File: includes/load_languages.php

if (preg_match("/getLanguageVars.php/i", $_SERVER['PHP_SELF'])) {
      echo "You can not access this file directly!";
      die();
}

function load_languages ($db = NULL, $language = NULL, $categories = NULL, &$langvars = NULL)
{

    // Check if all supplied args are valid, if not return false.
    if (is_null($db) || is_null($language) || !is_array($categories))
    {
        return false;
    }

    // Populate the $langvars array
    foreach ($categories as $category)
    {
        $result = $db->CacheExecute(7200, "SELECT name, value FROM {$db->prefix}languages WHERE category=? AND language=?;", array($category, $language));
        db_op_result ($db, $result, __LINE__, __FILE__);

        while ($result && !$result->EOF)
        {
            $row = $result->fields;
            global $$row['name'];
            $$row['name'] = $row['value'];
            $langvars[$row['name']] = $row['value'];
            $result->MoveNext();
        }
    }

    return true; // Results were added into array, signal that we were successful.
}
?>

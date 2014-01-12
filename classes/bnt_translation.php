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
// File: classes/lang.php



class bnt_translation
{
    public function get($langvar = null)
    {
        // Reach out and grab the global db and language variables (so we don't have to pass them in every language string echo)
        global $db, $lang;

        // Sanity checking to ensure everything exists before we waste an SQL call
        if (is_null ($db) || is_null ($lang) || is_null ($langvar) )
        {
            return false;
        }

        // Do a cached select from the database and return the value of the language variable requested
        $result = $db->CacheExecute(7200, "SELECT name, value FROM {$db->prefix}languages WHERE name=? AND language=?;", array($langvar, $lang));
        db_op_result ($db, $result, __LINE__, __FILE__);
        if ($result && !$result->EOF)
        {
            $row = $result->fields;
            return $row['value'];
        }
    }
}
?>

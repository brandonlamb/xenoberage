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
// File: inclues/tags_to_db.php
//
// Function for importing values from an INI file into the database.

function tags_to_db ($db, $ini_file, $ini_table)
{
    // This is a loop, that reads a ini file, of the type variable = value.
    // It will loop thru the list of the ini variables, and push them into the db.
    $ini_keys = parse_ini_file ($ini_file, true);

    $status = true; // This variable allows us to track the inserts into the databse. If one fails, the whole process is considered failed.

    $db->StartTrans(); // We enclose the inserts in a transaction as it is roughly 30 times faster

    foreach ($ini_keys as $config_category=>$config_line)
    {
        var_dump($config_line);
        foreach ($config_line as $config_key=>$config_value)
        {
            // explode here and then loop through each of the values as a new config value (tag entry), but with the same config_key (l_var)
            $values = explode('|', $config_value);
            var_dump($values);
/*            $debug_query = $db->Execute("INSERT into $ini_table (name, category, value) VALUES (?,?,?)", array($config_key, $config_category, $config_value));
            if (!$debug_query)
            {
                $status = false;
            }*/
        }
    }

    $trans_status = $db->CompleteTrans(); // Complete the transaction

    if ($trans_status && $status)
    {
        return true;
    }
    else
    {
        return false;
    }
}
?>

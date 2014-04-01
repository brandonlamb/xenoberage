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
// File: db_config.php

// The ADOdb db module is now required to run BNT. You
// can find it at http://php.weblogs.com/ADODB. Enter the
// path where it is installed here. We suggest putting
// ADOdb into a subdirectory (adodb) under a subdirectory of BNT called backends.
$ADOdbpath = "backends/adodb";

// Port to connect to database on. Note : if you do not know the port, set this to "" for default. Ex, MySQL default is 3306
$dbport = "";

/*
Hostname
*/
$ADODB_SESSION_CONNECT = "";
/*
Username + Password
*/
$ADODB_SESSION_USER = "";
$ADODB_SESSION_PWD = "";
/*
Database Name
*/
$ADODB_SESSION_DB = "";

// Define a random crypto key for ADOdb to use for encrypted sessions.
$ADODB_CRYPT_KEY = "";

// Type of the SQL database. This can be anything supported by ADOdb. Here are a few:
// "access" for MS Access databases. You need to create an ODBC DSN.
// "ado" for ADO databases
// "ibase" for Interbase 6 or earlier
// "borland_ibase" for Borland Interbase 6.5 or up
// "mssql" for Microsoft SQL
// "mysql" for MySQL - please don't use this one, it doesn't support transactions, which we now use
// "mysqlt" for MySQLi - needed for transaction support
// "oci8" for Oracle8/9
// "odbc" for a generic ODBC database
// "postgres" for PostgreSQL ver < 7
// "postgres7" for PostgreSQL ver 7 and up
// "sybase" for a SyBase database
// NOTE: only mysqlt works as of this release.
/*
Need to convert this to strict pdo statements...
*/
$ADODB_SESSION_DRIVER = "mysqlt";

// Set this to 1 to use db persistent connections, 0 otherwise - persistent connections can cause load problems!
$db_persistent = 0;

/*
Table prefix for each server
*/
$db_prefix = "";

$website_domain_default = "";

// The following two settings are now set automatically in global_cleanups.
// If it does not work, you'll need to comment them out, and uncomment and set the variables listed below.

// Domain & path of the game on your webserver (used to validate login cookie)
// This is the domain name part of the URL people enter to access your game.
// So if your game is at www.blah.com you would have:
// $gamedomain = "www.blah.com";
// Do not enter slashes for $gamedomain or anything that would come after a slash
// if you get weird errors with cookies then make sure the game domain has TWO dots
// i.e. if you reside your game on http://www.blacknova.net put .blacknova.net as $gamedomain.
// If your game is on http://www.some.site.net put .some.site.net as your game domain. Do not put port numbers in $gamedomain.
 $gamedomain = "";

// This is the trailing part of the URL, that is not part of the domain.
// If you enter www.blah.com/blacknova to access the game, you would leave the line as it is.
// If you do not need to specify blacknova, just enter a single slash eg:
// $gamepath = "/bnt/";


?>

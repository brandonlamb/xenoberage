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
// File: global_includes.php

// Define needed variables for the database

require_once($_SERVER['DOCUMENT_ROOT']."/config/db_config.php");

include_once($_SERVER['DOCUMENT_ROOT']."/global_defines.php");
include_once($_SERVER['DOCUMENT_ROOT']."/includes/timer.php");
include_once($_SERVER['DOCUMENT_ROOT']."/includes/adminlog.php");
include_once($_SERVER['DOCUMENT_ROOT']."/includes/bigtitle.php");
include_once($_SERVER['DOCUMENT_ROOT']."/includes/bnt_autoload.php");
include_once($_SERVER['DOCUMENT_ROOT']."/includes/calc_ownership.php");
include_once($_SERVER['DOCUMENT_ROOT']."/includes/checklogin.php");
include_once($_SERVER['DOCUMENT_ROOT']."/includes/collect_bounty.php");
include_once($_SERVER['DOCUMENT_ROOT']."/includes/connectdb.php");
include_once($_SERVER['DOCUMENT_ROOT']."/includes/db_kill_player.php");
include_once($_SERVER['DOCUMENT_ROOT']."/includes/gen_score.php");
include_once($_SERVER['DOCUMENT_ROOT']."/includes/get_avg_tech.php");
include_once($_SERVER['DOCUMENT_ROOT']."/includes/load_languages.php");
include_once($_SERVER['DOCUMENT_ROOT']."/includes/get_planet_owner.php");
include_once($_SERVER['DOCUMENT_ROOT']."/includes/log_move.php");
include_once($_SERVER['DOCUMENT_ROOT']."/includes/message_defence_owner.php");
include_once($_SERVER['DOCUMENT_ROOT']."/includes/num_armor.php");
include_once($_SERVER['DOCUMENT_ROOT']."/includes/num_beams.php");
include_once($_SERVER['DOCUMENT_ROOT']."/includes/number.php");
include_once($_SERVER['DOCUMENT_ROOT']."/includes/num_energy.php");
include_once($_SERVER['DOCUMENT_ROOT']."/includes/num_fighters.php");
include_once($_SERVER['DOCUMENT_ROOT']."/includes/num_holds.php");
include_once($_SERVER['DOCUMENT_ROOT']."/includes/num_shields.php");
include_once($_SERVER['DOCUMENT_ROOT']."/includes/num_torpedoes.php");
include_once($_SERVER['DOCUMENT_ROOT']."/includes/player_insignia_name.php");
include_once($_SERVER['DOCUMENT_ROOT']."/includes/playerlog.php");
include_once($_SERVER['DOCUMENT_ROOT']."/includes/scan_error.php");
include_once($_SERVER['DOCUMENT_ROOT']."/includes/scan_success.php");
include_once($_SERVER['DOCUMENT_ROOT']."/includes/stripnum.php");
include_once($_SERVER['DOCUMENT_ROOT']."/includes/text_gotologin.php");
include_once($_SERVER['DOCUMENT_ROOT']."/includes/text_gotomain.php");
include_once($_SERVER['DOCUMENT_ROOT']."/includes/t_port.php");
include_once($_SERVER['DOCUMENT_ROOT']."/includes/updatecookie.php");
include_once($_SERVER['DOCUMENT_ROOT']."/includes/db_op_result.php");
// Adodb handles database abstraction. We also use clob sessions, so that pgsql can be
// supported in the future, and cryptsessions, so the session data itself is encrypted.
require_once "backends/adodb/adodb.inc.php";
include_once "backends/adodb/adodb-perf.inc.php";
include_once "backends/adodb/session/adodb-session.php";


spl_autoload_register('bnt_autoload');
require_once($_SERVER['DOCUMENT_ROOT']."/global_cleanups.php");
?>

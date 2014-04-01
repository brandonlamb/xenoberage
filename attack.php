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
// File: attack.php

include "config/config.php";
updatecookie ();
if (checklogin ())
{
    die();
}

$sql_manager = new manage_table();
$ship_manager = new manage_ship();
$player_manager = new manage_player();
$title = $l_att_title;
include "header.php";
?>
<div class="tablecell content both-border">
	<div class="pad">
<?
bigtitle ();

// Kami Multi Browser Window Attack Fix
if (array_key_exists('ship_selected', $_SESSION) == false || $_SESSION['ship_selected'] != $ship_id)
{
    echo "You need to Click on the ship first.<BR><BR>";
    TEXT_GOTOMAIN();
	?>
    </div></div>
    <?
    include("footer.php");
    die();
}
unset($_SESSION['ship_selected']);

//$target_data = $sql_manager->playerinfo($victim_id,"");
echo $ship_manager->attack_target_ship($ship_id,$user_ship_id);

TEXT_GOTOMAIN();
?>
</div></div>
<?
include "footer.php";
?>

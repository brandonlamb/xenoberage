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
// File: log.php

include "config/config.php";
updatecookie ();
$title = $l_log_titlet;
if (checklogin () )
{
    die();
}

include "header3.php";
$log_manager = new manage_log();
?>
<div class="tablecell content both-border">
	<div class="pad">
<?
	/*New load up the players logs*/
	# check player not banned #
	# check user owns the logs they wish to view #
?>
<div class="player_log_page">
<a href=main.php><font color=#00ff00>Main Page</font></a>
			<table> 
				<tr>  
                    <td id="player_log_page_header"></td>
                    <td id="player_log_page_header">Priority</td>
                    <td id="player_log_page_header">Information</td>
				</tr>
			<tbody id="results">
				<div style="display:none" align="center">Please wait while you logs are loaded...</div>
			</tbody> 
			<tr class="edited_by">
            	<td colspan="5">Total Records: <? echo $total_records; ?></td>
            </tr>
			</table>
            </div>
			<p class="log-scroll">Scroll To Load More Logs.</p>
            <a href=main.php><font color=#00ff00>Main Page</font></a>


	</div>
</div>
<?
include "footer.php";
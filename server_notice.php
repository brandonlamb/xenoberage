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
// File: server_notice.php

if (preg_match("/server_notice.php/i", $_SERVER['PHP_SELF']))
{
    die("You can not access this file directly!");
}
?>
<div style="width:900px; margin:auto; border:1px solid #fff; background-color:#000011; background:URL(images/bg2_alpha.png) repeat; padding:8px;">

<div style="text-align:center; font-size:36px; text-decoration:underline; color:#f00;">Code Update Notice</div>
<div style="height:32px; font-size:24px; text-align:center;">Please Read Now...</div>
<div style="font-size:16px">
We have made the following updates to the game that will affect how you used to play the game.<br>
This is enabled for this game.<br>
<br>
These changes mostly affect how you use to use planets. Previous versions had an exploit bug in the code that allowed you to not need to produce organics to feed your colonists, which gave the player an extra 10% of free production.<br>
<br>
<span style="font-weight:bold; text-decoration:underline;">Players are now required to set on all their planets a minimal of 10% to produce organics.</span><br>
<br>
This is so that your colonists have food to eat.<br>
<br>
Colonists now Die and Reproduce as intended.<br>
<br>
Planet interest rate are now more accurately calculated.<br>
Previously, the interest rate was calculated more than once (repeat bug) which has now been fixed.<br>
<br>
To produce 1 fighter @ 10% fighter production you will need at least 200,000 colonists.<br>
To produce 1 Torpedo @ 10% torpedo production you will need at least 80,000 colonists.<br>
To produce 1 Credit @ 90% free production (just 10% for organic production and all others set to 0%) you will need roughly 75 colonists.<br>
<br>
The Planets Interest Rates are now calculated with the credits that are on the planet (which doesn't include the produced credits).<br>
<br>
Only Planets that are owned by players are updated, planets that are flagged as unowned will not receive an update.<br>
<br>
Thanks.<br>
<br>
<div style="height:1px; background-color:#000; padding:0px;"></div><div style="font-size:10px; font-weight:bold;">Blacknova Development</div>
</div>
</div>

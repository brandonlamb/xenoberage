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
// File: footer.php

global $sched_ticks, $footer_show_time, $db, $l;
$res = $db->Execute("SELECT COUNT(*) AS loggedin FROM {$db->prefix}ships WHERE (UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP({$db->prefix}ships.last_login)) / 60 <= 5 AND email NOT LIKE '%@xenobe'");
db_op_result ($db, $res, __LINE__, __FILE__);

$row = $res->fields;
$online = $row['loggedin'];

global $BenchmarkTimer;
if (is_object ($BenchmarkTimer) )
{
    $stoptime = $BenchmarkTimer->stop();
    $elapsed = $BenchmarkTimer->elapsed();
    $elapsed = substr ($elapsed, 0, 5);
}
else
{
    $elapsed = 999;
}

echo '
             </div>
        </div>
		</div>
	<div class="table-footer"> 
		<div class="tablerow"> 
            <div class="tableOther footer"><div class="footer-con">
				<div class="ar-news">';
// Suppress the news ticker on the IGB and index pages
if (!(preg_match("/index.php/i", $_SERVER['PHP_SELF']) || preg_match("/igb.php/i", $_SERVER['PHP_SELF'])))
{
    echo "<script src='backends/javascript/newsticker.js'></script>\n";
    echo "<div id='news_ticker' class='news-feed-scroller'></div>\n";
    include "fader.php";
}
				echo '</div><div class="ar-time-to-update">';
// Update counter

$res = $db->Execute("SELECT last_run FROM {$db->prefix}scheduler LIMIT 1");
db_op_result ($db, $res, __LINE__, __FILE__);
$result = $res->fields;
$mySEC = ($sched_ticks * 60) - (TIME () - $result['last_run']);
echo "<script src='backends/javascript/updateticker.js.php?mySEC={$mySEC}&amp;sched_ticks={$sched_ticks}'></script>";
echo "  <strong><span id=myx>$mySEC</span></strong> " . $l->get('l_footer_until_update') . " <br>\n";
// End update counter
				echo '</div><div class="ar-total-users-online">';
if ($online == 1)
{
    echo "  ";
    echo $l->get('l_footer_one_player_on');
}
else
{
    echo "There are " . $online . " players online.";
}
				echo '</div></div>

				
				
                    <div class="github"><a href="https://github.com/xgermz/xenoberage"><div class="logo-github"></div></a></div>
					<div class="copyright"><span class="bolder">Xenobe Rage</span> &copy;2012 - 2014 David Dawson. All rights reserved.<br /><span class="bolder">Blacknova Traders</span> &copy;2000-2012 Ron Harwood &amp; the BNT Dev team. All rights reserved.</div>
               
			</div> 
        </div>
	</div>
';

?>


</body>
</html>
<?php ob_end_flush();?>

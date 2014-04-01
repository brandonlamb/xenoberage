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
// File: scheduler.php

/******************************************************************
* Explanation of the scheduler                                    *
*                                                                 *
* Here are the scheduler DB fields, and what they are used for :  *
*  - sched_id : Unique ID. Before calling the file responsible    *
*    for the event, the variable $sched_var_id will be set to     *
*    this value, so the called file can modify the triggering     *
*    scheduler entry if it needs to.                              *
*                                                                 *
*  - repeate : Set this to 'Y' if you want the event to be        *
*    repeated endlessly. If this value is set to 'Y', the 'spawn' *
*    field is not used.                                           *
*                                                                 *
*  - ticks_left : Used internally by the scheduler. It represents *
*    the number of mins elapsed since the last call. ALWAYS set   *
*    this to 0 when scheduling a new event.                       *
*                                                                 *
*  - ticks_full : This is the interval in minutes between         *
*    different runs of your event. Set this to the frenquency     *
*    you wish the event to happen. For example, if you want your  *
*    event to be run every three minutes, set this to 3.          *
*                                                                 *
*  - spawn : If you want your event to be run a certain number of *
*    times only, set this to the number of times. For this to     *
*    work, loop must be set to 'N'. When the event has been run   *
*    spawn number of times, it is deleted from the scheduler.     *
*                                                                 *
*  - sched_file : This is the file that will be called when an    *
*    event has been trigerred.                                    *
*                                                                 *
*  - extra_info : This is a text variable that can be used to     *
*    store any extra information concerning the event triggered.  *
*    It will be made available to the called file through the     *
*    variable $sched_var_extrainfo.                               *
*                                                                 *
* If you are including files in your trigger file, it is important*
* to use include_once instead of include, as your file might      *
* be called multiple times in a single execution. If you need to  *
* define functions, you can put them in the sched_funcs.php file  *
* that is included by the scheduler. Else put them in your own    *
* include file, with an include statement. THEY CANNOT BE         *
* DEFINED IN YOUR MAIN FILE BODY. This would cause PHP to issue a *
* multiple function declaration error.                            *
*                                                                 *
* End of scheduler explanation                                    *
******************************************************************/

require_once "config/config.php";
global $l_sys_update;
$title = $l_sys_update;

//global $default_lang;

// New database driven language entries
load_languages($db, $lang, array('admin', 'common', 'global_includes', 'global_funcs', 'footer', 'news'), $langvars, $db_logging);

include "header.php";
?>
<div class="tablecell content both-border">
	<div class="pad">
<?
connectdb();

bigtitle();

require_once "sched_funcs.php";

#echo "<pre>[REQUEST]\n". print_r($_REQUEST, true) ."</pre>\n";

if (isset($_REQUEST['swordfish']))
{
    $swordfish = $_REQUEST['swordfish'];
}
else
{
    $swordfish = '';
}

if ($swordfish != $adminpass)
{
    echo "<form action='scheduler.php' method='post'>";
    echo "Password: <input type='password' name='swordfish' size='20' maxlength='20'><br><br>";
    echo "<input type='submit' value='Submit'><input type='reset' value='Reset'>";
    echo "</form>";
}
else
{
    $starttime = time();
    $lastRun = 0;
    $schedCount = 0;
    $lastrunList = null;
    $sched_res = $db->Execute("SELECT * FROM {$db->prefix}scheduler");
    db_op_result ($db, $sched_res, __LINE__, __FILE__, $db_logging);
    if ($sched_res)
    {
        while (!$sched_res->EOF)
        {
            $event = $sched_res->fields;
            $multiplier = ($sched_ticks / $event['ticks_full']) + ($event['ticks_left'] / $event['ticks_full']);
            $multiplier = (int) $multiplier;
            $ticks_left = ($sched_ticks + $event['ticks_left']) % $event['ticks_full'];

$lastRun += $event['last_run'];
$schedCount += 1;

# Store the last time the individual schedule was last run.
$lastrunList[$event['sched_file']] = $event['last_run'];

            if ($event['repeate'] == 'N')
            {
                if ($multiplier > $event['spawn'])
                {
                    $multiplier = $event['spawn'];
                }

                if ($event[spawn] - $multiplier == 0)
                {
                    $resx = $db->Execute("DELETE FROM {$db->prefix}scheduler WHERE sched_id=$event[sched_id]");
                    db_op_result ($db, $resx, __LINE__, __FILE__, $db_logging);
                }
                else
                {
                    $resy = $db->Execute("UPDATE {$db->prefix}scheduler SET ticks_left=$ticks_left, spawn=spawn-$multiplier WHERE sched_id=$event[sched_id]");
                    db_op_result ($db, $resy, __LINE__, __FILE__, $db_logging);
                }
            }
            else
            {
                $resz = $db->Execute("UPDATE {$db->prefix}scheduler SET ticks_left=$ticks_left WHERE sched_id=$event[sched_id]");
                db_op_result ($db, $resz, __LINE__, __FILE__, $db_logging);
            }

            $sched_var_id = $event['sched_id'];
            $sched_var_extrainfo = $event['extra_info'];

            $sched_i = 0;
            while ($sched_i < $multiplier)
            {
                include $event['sched_file'];
                $sched_i++;
            }
            $sched_res->MoveNext();
        }
        $lastRun /= $schedCount;
    }

    # Calculate the difference in time when the last good update happened.
    $schedDiff = ($lastRun - ( time() - ($sched_ticks * 60) ));
    if ( abs($schedDiff) > ($sched_ticks * 60) )
    {
        # Hmmm, seems that we have missed at least 1 update, so log it to the admin.
        adminlog($db, 2468, "Detected Scheduler Issue|{$lastRun}|". time() ."|". (time() - ($sched_ticks * 60)) ."|{$schedDiff}|". serialize($lastrunList));
    }

    $runtime = time() - $starttime;
    echo "<p>The scheduler took $runtime seconds to execute.<p>";

    $res = $db->Execute("UPDATE {$db->prefix}scheduler SET last_run=". TIME());
    db_op_result ($db, $res, __LINE__, __FILE__, $db_logging);
}

TEXT_GOTOMAIN ();
?>
</div></div>
<?
include "footer.php";
?>

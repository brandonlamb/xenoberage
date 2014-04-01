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

// Hack for log bug issue - this really needs to be fixed
$LOG_LIST = array(null,
        'LOG_LOGIN', 'LOG_LOGOUT', 'LOG_ATTACK_OUTMAN', 'LOG_ATTACK_OUTSCAN', 'LOG_ATTACK_EWD','LOG_ATTACK_EWDFAIL', 'LOG_ATTACK_LOSE', 'LOG_ATTACKED_WIN', 'LOG_TOLL_PAID', 'LOG_HIT_MINES',
        'LOG_SHIP_DESTROYED_MINES', 'LOG_PLANET_DEFEATED_D', 'LOG_PLANET_DEFEATED', 'LOG_PLANET_NOT_DEFEATED', 'LOG_RAW', 'LOG_TOLL_RECV', 'LOG_DEFS_DESTROYED', 'LOG_PLANET_EJECT', 'LOG_BADLOGIN', 'LOG_PLANET_SCAN',
        'LOG_PLANET_SCAN_FAIL', 'LOG_PLANET_CAPTURE', 'LOG_SHIP_SCAN', 'LOG_SHIP_SCAN_FAIL', 'LOG_Xenobe_ATTACK', 'LOG_STARVATION', 'LOG_TOW', 'LOG_DEFS_DESTROYED_F', 'LOG_DEFS_KABOOM', 'LOG_HARAKIRI',
        'LOG_TEAM_REJECT', 'LOG_TEAM_RENAME', 'LOG_TEAM_M_RENAME', 'LOG_TEAM_KICK', 'LOG_TEAM_CREATE', 'LOG_TEAM_LEAVE', 'LOG_TEAM_NEWLEAD', 'LOG_TEAM_LEAD', 'LOG_TEAM_JOIN', 'LOG_TEAM_NEWMEMBER',
        'LOG_TEAM_INVITE', 'LOG_TEAM_NOT_LEAVE', 'LOG_ADMIN_HARAKIRI', 'LOG_ADMIN_PLANETDEL', 'LOG_DEFENCE_DEGRADE', 'LOG_PLANET_CAPTURED', 'LOG_BOUNTY_CLAIMED', 'LOG_BOUNTY_PAID', 'LOG_BOUNTY_CANCELLED', 'LOG_SPACE_PLAGUE',
        'LOG_PLASMA_STORM', 'LOG_BOUNTY_FEDBOUNTY', 'LOG_PLANET_BOMBED', 'LOG_ADMIN_ILLEGVALUE'
                );

// New database driven language entries
load_languages($db, $lang, array('log', 'common', 'global_includes', 'global_funcs', 'footer', 'planet_report'), $langvars, $db_logging);

$title = $l_log_titlet;
$body_class = 'log';

if (checklogin () )
{
    die();
}

include "header.php";
?>
<div class="tablecell content both-border">
	<div class="pad">
<?
$res = $db->Execute("SELECT character_name, ship_id FROM {$db->prefix}ships WHERE ship_id='$user_ship_id'");
db_op_result ($db, $res, __LINE__, __FILE__, $db_logging);
$playerinfo = $res->fields;

if (!isset($_REQUEST['swordfish']))
{
    $_REQUEST['swordfish'] = '';
}

$swordfish = $_REQUEST['swordfish'];

if ($swordfish == $adminpass) // Check if called by admin script
{
    $playerinfo['ship_id'] = $player;
    if ($player == 0)
    {
        $playerinfo['character_name'] = 'Administrator';
    }
    else
    {
        $res = $db->Execute("SELECT character_name FROM {$db->prefix}ships WHERE ship_id=$player");
        db_op_result ($db, $res, __LINE__, __FILE__, $db_logging);
        $targetname = $res->fields;
        $playerinfo['character_name'] = $targetname['character_name'];
    }
}

$mode = 'compat';
$yres = 558;

if ($mode == 'full')
{
    echo "#divScroller1 {position:relative; overflow:hidden; overflow-y:scroll; z-index:9; left:0px; top:0px; width:100%; height:{$yres}px; visbility:visible; border-width:1px 1px 1px 1px; border-color:#C6D6E7; border-style:solid; scrollbar-track-color: #DEDEEF; scrollbar-face-color:#040658; scrollbar-arrow-color:#DEDEEF}";
}
elseif ($mode == 'moz')
{
    echo "#divScroller1 {position:relative; overflow:visible; overflow-y:scroll; z-index:9; left:0px; top:0px; width:100%; height:{$yres}px; visbility:visible; scrollbar-track-color: #DEDEEF; scrollbar-face-color:#040658; scrollbar-arrow-color:#DEDEEF}";
}

echo '<center>';
echo "<table width=80% border=0 cellspacing=0 cellpadding=0>";

$logline = str_replace("[player]", "$playerinfo[character_name]", $l_log_log);
?>

<tr><td><td width=100%><td></tr>
<tr><td><td align='left' height=20 style="background-image: url(images/top_panel.png); background-repeat:no-repeat">
<font size=2 color=#040658><strong>&nbsp;&nbsp;&nbsp;<?php echo $logline; ?></strong></font>
</td><td><td></tr>
<tr><td valign=bottom>

<?php
if ($mode == 'moz')
{
    echo '<td colspan=2 style="border-width:1px 1px 1px 1px; border-color:#C6D6E7; border-style:solid;" bgcolor=#63639C>';
}
elseif ($mode == 'full')
{
    echo '<td colspan=2 bgcolor=#63639C>';
}
else
{
    echo "<td colspan=2><table border=1 width=100%><tr><td  bgcolor=#63639C>";
}

if (empty($startdate))
{
    $startdate = date ("Y-m-d");
}


$res = $db->Execute("SELECT * FROM {$db->prefix}logs WHERE ship_id=$playerinfo[ship_id] AND time LIKE '$startdate%' ORDER BY time DESC, type DESC");
db_op_result ($db, $res, __LINE__, __FILE__, $db_logging);
while (!$res->EOF)
{
    $logs[] = $res->fields;
    $res->MoveNext();
}

$l_log_months_temp = "l_log_months_" . (int)(substr ($startdate, 5, 2) );
$entry = $$l_log_months_temp . " " . substr ($startdate, 8, 2) . " " . substr ($startdate, 0, 4);

echo "<div id=\"divScroller1\">" .
     "\n<div id=\"dynPage0\" class=\"dynPage\">" .
     "<center>" .
     "<br>" .
     "<font size=2 color=#DEDEEF><strong>$l_log_start $entry<strong></font>" .
     "<p>" .
     "<hr width=80% size=1 NOSHADE style=\"color: #040658\">" .
     "</center>\n";

if (!empty ($logs) )
{
    foreach ($logs as $log)
    {
        $event = log_parse($log);
        $log_months_temp = "l_log_months_" . (int)(substr ($log['time'], 5, 2) );
        $time = $$l_log_months_temp . " " . substr ($log['time'], 8, 2) . " " . substr ($log['time'], 0, 4) . " " . substr ($log['time'], 11);

        echo "<table border=0 cellspacing=5 width=100%>\n" .
             "  <tr>\n" .
             "    <td style='text-align:left; font-size:12px; color:#040658; font-weight:bold;'>{$event['title']}</td>\n" .
             "    <td style='text-align:right; font-size:12px; color:#040658; font-weight:bold;'>{$time}</td>\n" .
             "  </tr>\n" .
             "  <tr>\n".
             "    <td colspan=2 style='text-align:left; font-size:12px; color:#DEDEEF;'>{$event['text']}</td>\n".
             "  </tr>\n" .
             "</table>\n" .
             "<center><hr width='80%' size='1' NOSHADE style='color: #040658;'></center>\n";
    }
}

echo "<center>" .
     "<br>" .
     "<font size=2 color=#DEDEEF><strong>$l_log_end $entry<strong></font>" .
     "<p>" .
     "</center>" .
     "</div>\n";

$month = substr ($startdate, 5, 2);
$day = substr ($startdate, 8, 2) - 1;
$year = substr ($startdate, 0, 4);

$yesterday = mktime (0,0,0,$month,$day,$year);
$yesterday = date ("Y-m-d", $yesterday);

$day = substr ($startdate, 8, 2) - 2;

$yesterday2 = mktime (0,0,0,$month,$day,$year);
$yesterday2 = date ("Y-m-d", $yesterday2);

if ($mode == 'compat')
{
    echo "</td></tr></table>";
}

if ($mode != 'compat')
{
    $log_months_temp = "l_log_months_" . (int)(substr ($yesterday, 5, 2));
    $entry = $$l_log_months_temp . " " . substr ($yesterday, 8, 2) . " " . substr ($yesterday, 0, 4);

    unset ($logs);
    $res = $db->Execute("SELECT * FROM {$db->prefix}logs WHERE ship_id=$playerinfo[ship_id] AND time LIKE '$yesterday%' ORDER BY time DESC, type DESC");
    db_op_result ($db, $res, __LINE__, __FILE__, $db_logging);
    while (!$res->EOF)
    {
        $logs[] = $res->fields;
        $res->MoveNext();
    }

    echo "<div id=\"dynPage1\" class=\"dynPage\">" .
         "<center>" .
         "<br>" .
         "<font size=2 color=#DEDEEF><strong>$l_log_start $entry<strong></font>" .
         "<p>" .
         "</center>" .
         "<hr width=80% size=1 NOSHADE style=\"color: #040658\">";

    if (!empty ($logs) )
    {
        foreach ($logs as $log)
        {
            $event = log_parse($log);
            $log_months_temp = "l_log_months_" . (int)(substr ($log['time'], 5, 2) );
            $time = $$l_log_months_temp . " " . substr ($log['time'], 8, 2) . " " . substr ($log['time'], 0, 4) . " " . substr ($log['time'], 11);

            echo "<table border=0 cellspacing=5 width=100%>\n" .
                 "  <tr>\n" .
                 "    <td align='left'><font size='2' color='#040658'><strong>{$event['title']}</strong></td>\n" .
                 "    <td align='right'><font size='2' color='#040658'><strong>{$time}</strong></td>\n" .
                 "  <tr><td colspan='2' align='left'><font size='2' color='#DEDEEF'>{$event['text']}</td></tr>\n" .
                 "</table>\n" .
                 "<hr width='80%' size='1' NOSHADE style='color: #040658;'>\n";
        }
    }

    echo "<center>" .
         "<br>" .
         "<font size=2 color=#DEDEEF><strong>$l_log_end $entry<strong></font>" .
         "<p>" .
         "</center>" .
         "</div>\n";

    $log_months_temp = "l_log_months_" . (int)(substr ($yesterday2, 5, 2));
    $entry = $$l_log_months_temp . " " . substr ($yesterday2, 8, 2) . " " . substr ($yesterday2, 0, 4);

    unset ($logs);
    $res = $db->Execute("SELECT * FROM {$db->prefix}logs WHERE ship_id=$playerinfo[ship_id] AND time LIKE '$yesterday2%' ORDER BY time DESC, type DESC");
    db_op_result ($db, $res, __LINE__, __FILE__, $db_logging);
    while (!$res->EOF)
    {
        $logs[] = $res->fields;
        $res->MoveNext();
    }

    echo "<div id=\"dynPage2\" class=\"dynPage\">" .
         "<center>" .
         "<br>" .
         "<font size=2 color=#DEDEEF><strong>$l_log_start $entry<strong></font>" .
         "<p>" .
         "</center>" .
         "<hr width=80% size=1 NOSHADE style=\"color: #040658\">";

    if (!empty($logs))
    {
        foreach ($logs as $log)
        {
            $event = log_parse($log);
            $log_months_temp = "l_log_months_" . (int)(substr ($log['time'], 5, 2) );
            $time = $$l_log_months_temp . " " . substr ($log['time'], 8, 2) . " " . substr ($log['time'], 0, 4) . " " . substr ($log['time'], 11);

            echo "<table border=0 cellspacing=5 width=100%>\n" .
                 "<tr>\n" .
                 "<td style='text-align:left;'><font size=2 color=#040658><strong>$event[title]</strong></td>\n" .
                 "<td align=right><font size=2 color=#040658><strong>$time</strong></td>\n" .
                 "</tr>\n".
                 "<tr>\n<td colspan=2 align=left>\n" .
                 "<font size=2 color=#DEDEEF>" .
                 "$event[text]" .
                 "</td>\n</tr>\n" .
                 "</table>\n" .
                 "<hr width=80% size=1 NOSHADE style=\"color: #040658\">";
        }
    }

    echo "<center>" .
         "<br>" .
         "<font size=2 color=#DEDEEF><strong>$l_log_end $entry<strong></font>" .
         "<p>" .
         "</center>" .
         "</div>";

}

echo "</div>";

$l_log_months_short_temp = "l_log_months_short_" . (int)(substr ($startdate, 5, 2) );
$date1 = $$l_log_months_short_temp . " " . substr ($startdate, 8, 2);

$l_log_months_short_temp = "l_log_months_short_" . (int)(substr ($startdate, 5, 2));
$date2 = $$l_log_months_short_temp . " " . substr ($yesterday, 8, 2);

$l_log_months_short_temp = "l_log_months_short_" . (int)(substr ($startdate, 5, 2));
$date3 = $$l_log_months_short_temp . " " . substr ($yesterday2, 8, 2);

$month = substr ($startdate, 5, 2);
$day = substr ($startdate, 8, 2) - 3;
$year = substr ($startdate, 0, 4);

$backlink = mktime (0,0,0,$month,$day,$year);
$backlink = date ("Y-m-d", $backlink);

$day = substr ($startdate, 8, 2) + 3;

$nextlink = mktime (0,0,0,$month,$day,$year);
if ($nextlink > time ())
{
    $nextlink = time ();
}

$nextlink = date ("Y-m-d", $nextlink);

$nonext = 0;
if ($startdate == date ("Y-m-d"))
{
    $nonext = 1;
}

if ($swordfish == $adminpass) // Fix for admin log view
{
    $postlink = "&swordfish=" . urlencode ($swordfish) . "&player=$player";
}
else
{
    $postlink = "";
}

if ($mode != 'compat')
{
    echo "<td valign=bottom>" .
         "<tr><td><td align=right>" .
         "<img src=images/bottom_panel.png>" .
         "<br>" .
         "<div style=\"position:relative; top:-23px;\">" .
         "<font size=2><strong>" .
         "<a href=log.php?startdate={$backlink}$postlink><<</a>&nbsp;&nbsp;&nbsp;" .
         "<a href=\"#\" onclick=\"activate(2); return false;\" onfocus=\"if (this.blur)this.blur()\">$date3</a>" .
         " | " .
         "<a href=\"#\" onclick=\"activate(1); return false;\" onfocus=\"if (this.blur)this.blur()\">$date2</a>" .
         " | " .
         "<a href=\"#\" onclick=\"activate(0); return false;\" onfocus=\"if (this.blur)this.blur()\">$date1</a>";

    if ($nonext != 1)
    {
        echo "&nbsp;&nbsp;&nbsp;<a href=log.php?startdate={$nextlink}$postlink>>>></a>";
    }

    echo "&nbsp;&nbsp;&nbsp;";
}
else
{
    echo "<tr><td><td align=right>" .
         "<a href=log.php?startdate={$backlink}$postlink><font color=white size =3><strong><<</strong></font></a>&nbsp;&nbsp;&nbsp;" .
         "<a href=log.php?startdate={$yesterday2}$postlink><font color=white size=3><strong>$date3</strong></font></a>" .
         "&nbsp;|&nbsp;" .
         "<a href=log.php?startdate={$yesterday}$postlink><font color=white size=3><strong>$date2</strong></font></a>" .
         " | " .
         "<a href=log.php?startdate={$startdate}$postlink><font color=white size=3><strong>$date1</strong></font></a>";

    if ($nonext != 1)
    {
        echo "&nbsp;&nbsp;&nbsp;<a href=log.php?startdate={$nextlink}$postlink><font color=white size=3><strong>>></strong></font></a>";
    }

    echo "&nbsp;&nbsp;&nbsp;";
}

if ($swordfish == $adminpass)
{
    echo "<tr><td><td>" .
         "<FORM action=admin.php method=POST>" .
        "<input type=hidden name=swordfish value=\"$swordfish\">" .
         "<input type=hidden name=menu value=logview>" .
         "<input type=submit value=\"Return to Admin\"></td></tr>";
}
else
{
    $l_log_click = str_replace("[here]", "<a href=main.php><font color=#00ff00>" . $l_here . "</font></a>", $l_log_click);
    echo "<tr><td><td style='text-align:left;'><p style='font-size:2;'>$l_log_click</p></td></tr>";
}

if ($mode != 'compat')
{
    $l_log_note = str_replace("[disable them]", "<a href=options.php><font color=#00FF00>" . $l_log_note_disable . "</font></a>", $l_log_note);
    echo "<tr><td><td align=center><br><font size=2 color=white>$l_log_note</td></tr>";
}

echo "</table></center>";
?>
</div></div>
<?
include "footer.php";

function log_parse($entry)
{
  global $l_log_title;
  global $l_log_text;
  global $l_log_pod;
  global $l_log_nopod;
  $l_log_nopod = "<font color=yellow><strong>" . $l_log_nopod . "</strong></font>"; // This should be done better, but I needed it moved out of the language file.

#    $texttemp = "l_log_text_" . $entry['type'];
#    global $$texttemp;
#    $titletemp = "l_log_title_" . $entry['type'];
#    global $$titletemp;
    getLogInfo($entry['type'], $titletemp, $texttemp);

  switch ($entry['type'])
  {
    case LOG_LOGIN: //data args are : [ip]
    case LOG_LOGOUT:
    case LOG_BADLOGIN:
    case LOG_HARAKIRI:
    $retvalue['text'] = str_replace("[ip]", "<font color=white><strong>$entry[data]</strong></font>", $texttemp);
    $retvalue['title'] = $titletemp;
    $retvalue['title'] = "<font color=red>" . $retvalue['title'] . "</font>";
    break;

    case LOG_ATTACK_OUTMAN: //data args are : [player]
    case LOG_ATTACK_OUTSCAN:
    case LOG_ATTACK_EWD:
    case LOG_ATTACK_EWDFAIL:
    case LOG_SHIP_SCAN:
    case LOG_SHIP_SCAN_FAIL:
    case LOG_Xenobe_ATTACK:
    case LOG_TEAM_NOT_LEAVE:
    $retvalue['text'] = str_replace("[player]", "<font color=white><strong>$entry[data]</strong></font>", $texttemp);
    $retvalue['title'] = $titletemp;
    $retvalue['title'] = "<font color=red>" . $retvalue['title'] . "</font>";
    break;

    case LOG_ATTACK_LOSE: //data args are : [player] [pod]
    list($name,$pod) = explode ("|", $entry['data']);

    $retvalue['text'] = str_replace("[player]", "<font color=white><strong>$name</strong></font>", $texttemp);
    $retvalue['title'] = $titletemp;
    $retvalue['title'] = "<font color=red>" . $retvalue['title'] . "</font>";
    if ($pod == 'Y')
      $retvalue['text'] = $retvalue['text'] . $l_log_pod;
    else
      $retvalue['text'] = $retvalue['text'] . $l_log_nopod;
    break;

    case LOG_ATTACKED_WIN: //data args for text are : [player] [armor] [fighters]
    list($name, $armor, $fighters)= explode ("|", $entry['data']);
    $retvalue['text'] = str_replace("[player]", "<font color=white><strong>$name</strong></font>", $texttemp);
    $retvalue['text'] = str_replace("[armor]", "<font color=white><strong>$armor</strong></font>", $retvalue['text']);
    $retvalue['text'] = str_replace("[fighters]", "<font color=white><strong>$fighters</strong></font>", $retvalue['text']);
    $retvalue['title'] = $titletemp;
    $retvalue['title'] = "<font color=yellow>" . $retvalue['title'] . "</font>";
    break;

    case LOG_TOLL_PAID: //data args are : [toll] [sector]
    case LOG_TOLL_RECV:
    list($toll, $sector)= explode ("|", $entry['data']);
    $retvalue['text'] = str_replace("[toll]", "<font color=white><strong>$toll</strong></font>", $texttemp);
    $retvalue['text'] = str_replace("[sector]", "<font color=white><strong>$sector</strong></font>", $retvalue['text']);
    $retvalue['title'] = $titletemp;
    break;

    case LOG_HIT_MINES: //data args are : [mines] [sector]
    list($mines, $sector)= explode ("|", $entry['data']);
    $retvalue['text'] = str_replace("[mines]", "<font color=white><strong>$mines</strong></font>", $texttemp);
    $retvalue['text'] = str_replace("[sector]", "<font color=white><strong>$sector</strong></font>", $retvalue['text']);
    $retvalue['title'] = $titletemp;
    $retvalue['title'] = "<font color=yellow>" . $retvalue['title'] . "</font>";
    break;

    case LOG_SHIP_DESTROYED_MINES: //data args are : [sector] [pod]
    list($sector, $pod)= explode ("|", $entry['data']);
    $retvalue['text'] = str_replace("[sector]", "<font color=white><strong>$sector</strong></font>", $texttemp);
    $retvalue['title'] = $titletemp;
    $retvalue['title'] = "<font color=red>" . $retvalue['title'] . "</font>";
    if ($pod == 'Y')
      $retvalue['text'] = $retvalue['text'] . $l_log_pod;
    else
      $retvalue['text'] = $retvalue['text'] . $l_log_nopod;
    break;

    case LOG_DEFS_KABOOM: //data args are : [sector] [pod]
    list($sector, $pod)= explode ("|", $entry['data']);
    $retvalue['text'] = str_replace("[sector]", "<font color=white><strong>$sector</strong></font>", $texttemp);
    $retvalue['title'] = $titletemp;
    $retvalue['title'] = "<font color=red>" . $retvalue['title'] . "</font>";
    if ($pod == 'Y')
      $retvalue['text'] = $retvalue['text'] . $l_log_pod;
    else
      $retvalue['text'] = $retvalue['text'] . $l_log_nopod;
    break;

    case LOG_PLANET_DEFEATED_D: //data args are :[planet_name] [sector] [name]
    list($planet_name, $sector, $name)= explode ("|", $entry['data']);
    $retvalue['text'] = str_replace("[planet_name]", "<font color=white><strong>$planet_name</strong></font>", $texttemp);
    $retvalue['text'] = str_replace("[sector]", "<font color=white><strong>$sector</strong></font>", $retvalue['text']);
    $retvalue['text'] = str_replace("[name]", "<font color=white><strong>$name</strong></font>", $retvalue['text']);
    $retvalue['title'] = $titletemp;
    $retvalue['title'] = "<font color=yellow>" . $retvalue['title'] . "</font>";
    break;

    case LOG_PLANET_DEFEATED:
    list($planet_name, $sector, $name)= explode ("|", $entry['data']);
    $retvalue['text'] = str_replace("[planet_name]", "<font color=white><strong>$planet_name</strong></font>", $texttemp);
    $retvalue['text'] = str_replace("[sector]", "<font color=white><strong>$sector</strong></font>", $retvalue['text']);
    $retvalue['text'] = str_replace("[name]", "<font color=white><strong>$name</strong></font>", $retvalue['text']);
    $retvalue['title'] = $titletemp;
    $retvalue['title'] = "<font color=red>" . $retvalue['title'] . "</font>";
    break;

    case LOG_PLANET_SCAN:
    case LOG_PLANET_SCAN_FAIL:
    list($planet_name, $sector, $name)= explode ("|", $entry['data']);
    $retvalue['text'] = str_replace("[planet_name]", "<font color=white><strong>$planet_name</strong></font>", $texttemp);
    $retvalue['text'] = str_replace("[sector]", "<font color=white><strong>$sector</strong></font>", $retvalue['text']);
    $retvalue['text'] = str_replace("[name]", "<font color=white><strong>$name</strong></font>", $retvalue['text']);
    $retvalue['title'] = $titletemp;
    break;

    case LOG_PLANET_NOT_DEFEATED: //data args are : [planet_name] [sector] [name] [ore] [organics] [goods] [salvage] [credits]
    list($planet_name, $sector, $name, $ore, $organics, $goods, $salvage, $credits)= explode ("|", $entry['data']);
    $retvalue['text'] = str_replace("[planet_name]", "<font color=white><strong>$planet_name</strong></font>", $texttemp);
    $retvalue['text'] = str_replace("[sector]", "<font color=white><strong>$sector</strong></font>", $retvalue['text']);
    $retvalue['text'] = str_replace("[name]", "<font color=white><strong>$name</strong></font>", $retvalue['text']);
    $retvalue['text'] = str_replace("[ore]", "<font color=white><strong>$ore</strong></font>", $retvalue['text']);
    $retvalue['text'] = str_replace("[goods]", "<font color=white><strong>$goods</strong></font>", $retvalue['text']);
    $retvalue['text'] = str_replace("[organics]", "<font color=white><strong>$organics</strong></font>", $retvalue['text']);
    $retvalue['text'] = str_replace("[salvage]", "<font color=white><strong>$salvage</strong></font>", $retvalue['text']);
    $retvalue['text'] = str_replace("[credits]", "<font color=white><strong>$credits</strong></font>", $retvalue['text']);
    $retvalue['title'] = $titletemp;
    break;

    case LOG_RAW: //data is stored as a message
    $retvalue['title'] = $titletemp;
    $retvalue['text'] = $entry['data'];
    break;

    case LOG_DEFS_DESTROYED: //data args are : [quantity] [type] [sector]
    list($quantity, $type, $sector)= explode ("|", $entry['data']);
    $retvalue['text'] = str_replace("[quantity]", "<font color=white><strong>$quantity</strong></font>", $texttemp);
    $retvalue['text'] = str_replace("[type]", "<font color=white><strong>$type</strong></font>", $retvalue['text']);
    $retvalue['text'] = str_replace("[sector]", "<font color=white><strong>$sector</strong></font>", $retvalue['text']);
    $retvalue['title'] = $titletemp;
    break;

    case LOG_PLANET_EJECT: //data args are : [sector] [player]
    list($sector, $name)= explode ("|", $entry['data']);
    $retvalue['text'] = str_replace("[sector]", "<font color=white><strong>$sector</strong></font>", $texttemp);
    $retvalue['text'] = str_replace("[name]", "<font color=white><strong>$name</strong></font>", $retvalue['text']);
    $retvalue['title'] = $titletemp;
    break;

    case LOG_STARVATION: //data args are : [sector] [starvation]
    list($sector, $starvation)= explode ("|", $entry['data']);
    $retvalue['text'] = str_replace("[sector]", "<font color=white><strong>$sector</strong></font>", $texttemp);
    $retvalue['text'] = str_replace("[starvation]", "<font color=white><strong>$starvation</strong></font>", $retvalue['text']);
    $retvalue['title'] = $titletemp;
    $retvalue['title'] = "<font color=yellow>" . $retvalue['title'] . "</font>";
    break;

    case LOG_TOW: //data args are : [sector] [newsector] [hull]
    list($sector, $newsector, $hull)= explode ("|", $entry['data']);
    $retvalue['text'] = str_replace("[sector]", "<font color=white><strong>$sector</strong></font>", $texttemp);
    $retvalue['text'] = str_replace("[newsector]", "<font color=white><strong>$newsector</strong></font>", $retvalue['text']);
    $retvalue['text'] = str_replace("[hull]", "<font color=white><strong>$hull</strong></font>", $retvalue['text']);
    $retvalue['title'] = $titletemp;
    break;

    case LOG_DEFS_DESTROYED_F: //data args are : [fighters] [sector]
    list($fighters, $sector)= explode ("|", $entry['data']);
    $retvalue['text'] = str_replace("[sector]", "<font color=white><strong>$sector</strong></font>", $texttemp);
    $retvalue['text'] = str_replace("[fighters]", "<font color=white><strong>$fighters</strong></font>", $retvalue['text']);
    $retvalue['title'] = $titletemp;
    break;

    case LOG_TEAM_REJECT: //data args are : [player] [teamname]
    list($player, $teamname)= explode ("|", $entry['data']);
    $retvalue['text'] = str_replace("[player]", "<font color=white><strong>$player</strong></font>", $texttemp);
    $retvalue['text'] = str_replace("[teamname]", "<font color=white><strong>$teamname</strong></font>", $retvalue['text']);
    $retvalue['title'] = $titletemp;
    break;

    case LOG_TEAM_RENAME: //data args are : [team]
    case LOG_TEAM_M_RENAME:
    case LOG_TEAM_KICK:
    case LOG_TEAM_CREATE:
    case LOG_TEAM_LEAVE:
    case LOG_TEAM_LEAD:
    case LOG_TEAM_JOIN:
    case LOG_TEAM_INVITE:
    $retvalue['text'] = str_replace("[team]", "<font color=white><strong>$entry[data]</strong></font>", $texttemp);
    $retvalue['title'] = $titletemp;
    break;

    case LOG_TEAM_NEWLEAD: //data args are : [team] [name]
    case LOG_TEAM_NEWMEMBER:
    list($team, $name)= explode ("|", $entry['data']);
    $retvalue['text'] = str_replace("[team]", "<font color=white><strong>$team</strong></font>", $texttemp);
    $retvalue['text'] = str_replace("[name]", "<font color=white><strong>$name</strong></font>", $retvalue['text']);
    $retvalue['title'] = $titletemp;
    break;

    case LOG_ADMIN_HARAKIRI: //data args are : [player] [ip]
    list($player, $ip)= explode ("|", $entry['data']);
    $retvalue['text'] = str_replace("[player]", "<font color=white><strong>$player</strong></font>", $texttemp);
    $retvalue['text'] = str_replace("[ip]", "<font color=white><strong>$ip</strong></font>", $retvalue['text']);
    $retvalue['title'] = $titletemp;
    break;

    case LOG_ADMIN_ILLEGVALUE: //data args are : [player] [quantity] [type] [holds]
    list($player, $quantity, $type, $holds)= explode ("|", $entry['data']);
    $retvalue['text'] = str_replace("[player]", "<font color=white><strong>$player</strong></font>", $texttemp);
    $retvalue['text'] = str_replace("[quantity]", "<font color=white><strong>$quantity</strong></font>", $retvalue['text']);
    $retvalue['text'] = str_replace("[type]", "<font color=white><strong>$type</strong></font>", $retvalue['text']);
    $retvalue['text'] = str_replace("[holds]", "<font color=white><strong>$holds</strong></font>", $retvalue['text']);
    $retvalue['title'] = $titletemp;
    break;

    case LOG_ADMIN_PLANETDEL: //data args are : [attacker] [defender] [sector]
    list($attacker, $defender, $sector)= explode ("|", $entry['data']);
    $retvalue['text'] = str_replace("[attacker]", "<font color=white><strong>$attacker</strong></font>", $texttemp);
    $retvalue['text'] = str_replace("[defender]", "<font color=white><strong>$defender</strong></font>", $retvalue['text']);
    $retvalue['text'] = str_replace("[sector]", "<font color=white><strong>$sector</strong></font>", $retvalue['text']);
    $retvalue['title'] = $titletemp;
    break;

    case LOG_DEFENCE_DEGRADE: //data args are : [sector] [degrade]
    list($sector, $degrade)= explode ("|", $entry['data']);
    $retvalue['text'] = str_replace("[sector]", "<font color=white><strong>$sector</strong></font>", $texttemp);
    $retvalue['text'] = str_replace("[degrade]", "<font color=white><strong>$degrade</strong></font>", $retvalue['text']);
    $retvalue['title'] = $titletemp;
    break;

    case LOG_PLANET_CAPTURED: //data args are : [cols] [credits] [owner]
    list($cols, $credits, $owner)= explode ("|", $entry['data']);
    $retvalue['text'] = str_replace("[cols]", "<font color=white><strong>$cols</strong></font>", $texttemp);
    $retvalue['text'] = str_replace("[credits]", "<font color=white><strong>$credits</strong></font>", $retvalue['text']);
    $retvalue['text'] = str_replace("[owner]", "<font color=white><strong>$owner</strong></font>", $retvalue['text']);
    $retvalue['title'] = $titletemp;
    break;
    case LOG_BOUNTY_CLAIMED:
    list($amount,$bounty_on,$placed_by) = explode ("|", $entry['data']);
    $retvalue['text'] = str_replace("[amount]", "<font color=white><strong>$amount</strong></font>", $texttemp);
    $retvalue['text'] = str_replace("[bounty_on]", "<font color=white><strong>$bounty_on</strong></font>", $retvalue['text']);
    $retvalue['text'] = str_replace("[placed_by]", "<font color=white><strong>$placed_by</strong></font>", $retvalue['text']);
    $retvalue['title'] = $titletemp;
    break;
 case LOG_BOUNTY_PAID:
    list($amount,$bounty_on) = explode ("|", $entry['data']);
    $retvalue['text'] = str_replace("[amount]", "<font color=white><strong>$amount</strong></font>", $texttemp);
    $retvalue['text'] = str_replace("[bounty_on]", "<font color=white><strong>$bounty_on</strong></font>", $retvalue['text']);
    $retvalue['title'] = $titletemp;
    break;
 case LOG_BOUNTY_CANCELLED:
    list($amount,$bounty_on) = explode ("|", $entry['data']);
    $retvalue['text'] = str_replace("[amount]", "<font color=white><strong>$amount</strong></font>", $texttemp);
    $retvalue['text'] = str_replace("[bounty_on]", "<font color=white><strong>$bounty_on</strong></font>", $retvalue['text']);
    $retvalue['title'] = $titletemp;
    break;
case LOG_BOUNTY_FEDBOUNTY:
    $retvalue['text'] = str_replace("[amount]", "<font color=white><strong>$entry[data]</strong></font>", $texttemp);
    $retvalue['title'] = $titletemp;
    break;
 case LOG_SPACE_PLAGUE:
    list($name, $sector, $percentage) = explode ("|", $entry['data']);
    $retvalue['text'] = str_replace("[name]", "<font color=white><strong>$name</strong></font>", $texttemp);
    $retvalue['text'] = str_replace("[sector]", "<font color=white><strong>$sector</strong></font>", $retvalue['text']);
    $retvalue['text'] = str_replace("[percentage]", "<font color=white><strong>$percentage</strong></font>", $retvalue['text']);
    $retvalue['title'] = $titletemp;
    break;
 case LOG_PLASMA_STORM:
    list($name,$sector) = explode ("|", $entry['data']);
    $retvalue['text'] = str_replace("[name]", "<font color=white><strong>$name</strong></font>", $texttemp);
    $retvalue['text'] = str_replace("[sector]", "<font color=white><strong>$sector</strong></font>", $retvalue['text']);
    $retvalue['title'] = $titletemp;
    break;
 case LOG_PLANET_BOMBED:
    list($planet_name, $sector, $name, $beams, $torps, $figs)= explode ("|", $entry['data']);
    $retvalue['text'] = str_replace("[planet_name]", "<font color=white><strong>$planet_name</strong></font>", $texttemp);
    $retvalue['text'] = str_replace("[sector]", "<font color=white><strong>$sector</strong></font>", $retvalue['text']);
    $retvalue['text'] = str_replace("[name]", "<font color=white><strong>$name</strong></font>", $retvalue['text']);
    $retvalue['text'] = str_replace("[beams]", "<font color=white><strong>$beams</strong></font>", $retvalue['text']);
    $retvalue['text'] = str_replace("[torps]", "<font color=white><strong>$torps</strong></font>", $retvalue['text']);
    $retvalue['text'] = str_replace("[figs]", "<font color=white><strong>$figs</strong></font>", $retvalue['text']);
    $retvalue['title'] = $titletemp;
    $retvalue['title'] = "<font color=red>" . $retvalue['title'] . "</font>";
    break;

 case 57:
    // Multi Browser Logs.
    list($ship_ip, $ship_id, $info)= explode ("|", $entry['data']);
    $retvalue['text'] = "Account: <span style='color:#ff0;'>{$ship_id}</span> with IP: '<span style='color:#ff0;'>{$ship_ip}</span>' <span style='color:#fff;'>{$info}</span>";
    $retvalue['title'] = "Possible Multi Browser Attempt.";
    break;

 case 950:
    // Attack logs debug info
    if (count(explode ("|", $entry['data'])) == 7)
    {
        list($step, $attacker_armor, $target_armor, $attacker_fighters, $target_fighters, $attacker_id, $target_id)= explode ("|", $entry['data']);
        $retvalue['text']  = "Attacker Ship: {$attacker_id}, Armor: {$attacker_armor}, Fighters: {$attacker_fighters}<br>\n";
        $retvalue['text'] .= "Target Ship: {$target_id}, Armor: {$target_armor}, Fighters: {$target_fighters}\n";
    }
    else
    {
        list($step, $attacker_id, $target_id, $info)= explode ("|", $entry['data']);
        $retvalue['text']  = "Attacker Ship: {$attacker_id}, Target Ship: {$target_id}, Target Ship: {$info}\n";
    }
    $retvalue['title'] = "Attack Logs Stage: {$step} [Debug].";

    break;

 case 1019:
    // Invalid login try (wrong password etc)
    if (count(explode ("|", $entry['data'])) == 3)
    {
        list($ship_ip, $ship_email, $used_password)= explode ("|", $entry['data']);
        $retvalue['text'] = "Someone using IP: <span style='color:#ff0;'>{$ship_ip}</span> tried to login into Account: '<span style='color:#ff0;'>{$ship_email}</span>' with Password: '<span style='color:#ff0;'>{$used_password}</span>'";
    }
    else
    {
        list($ship_ip, $ship_email)= explode ("|", $entry['data']);
        $retvalue['text'] = "Someone using IP: <span style='color:#ff0;'>{$ship_ip}</span> tried to login into Account: '<span style='color:#ff0;'>{$ship_email}</span>' with a blank Password.'";
    }
    $retvalue['title'] = "Invalid Login Attempt.";
    break;

 default:
    $retvalue['text'] = $entry['data'];
    $retvalue['title'] = $entry['type'];
    break;
  }

  return $retvalue;
}

function getLogInfo($id = null, &$title = null, &$text = null)
{
    global $LOG_LIST;
    $title = null;
    $text = null;

    if ($id < count($LOG_LIST))
    {
        if (array_key_exists("l_log_title_". $LOG_LIST[$id], $GLOBALS))
        {
            $title = $GLOBALS["l_log_title_". $LOG_LIST[$id]];
        }

        if (array_key_exists("l_log_text_". $LOG_LIST[$id], $GLOBALS))
        {
            $text = $GLOBALS["l_log_text_". $LOG_LIST[$id]];
        }
    }
}

?>

<?php
// Xenobe Rage Copyright (C) 2012-2013 David Dawson
// Blacknova Traders -  Copyright (C) 2001-2012 Ron Harwood and the BNT development team
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
// File: igb.php

include "config/config.php";
updatecookie ();

// New database driven language entries
load_languages($db, $lang, array('igb', 'common', 'global_includes', 'global_funcs', 'footer', 'news'), $langvars, $db_logging);

$title = $l_igb_title;
$body_class = 'igb';
include "header.php";

if (checklogin () )
{
    die ();
}
?>
<div class="tablecell content both-border">
	<div class="pad">
<?
$result = $db->Execute("SELECT * FROM {$db->prefix}ships WHERE ship_id='$user_ship_id'");
db_op_result ($db, $result, __LINE__, __FILE__, $db_logging);
$playerinfo = $result->fields;

$result = $db->Execute("SELECT * FROM {$db->prefix}ibank_accounts WHERE ship_id=$playerinfo[ship_id]");
db_op_result ($db, $result, __LINE__, __FILE__, $db_logging);
$account = $result->fields;

?>
<center>
<img src="images/div1.png" alt="">
<div style="width:600px; max-width:600px;" class="igb">
<table style="width:600px; height:350px;" border=0>
<tr><td style="background-image:URL(images/igbscreen.png); background-repeat:no-repeat;" align="center">
<table style="width:550px; height:300px;" border=0>

<?php

if (!$allow_ibank)
  IGB_error($l_igb_malfunction, "main.php");

if (!isset($_REQUEST['command']))
{
    $_REQUEST['command'] = '';
    $command = '';
}
else
{
    $command = $_REQUEST['command'];
}

if ($command == 'login') //main menu
  IGB_login();
elseif ($command == 'withdraw') //withdraw menu
  IGB_withdraw();
elseif ($command == 'withdraw2') //withdraw operation
  IGB_withdraw2();
elseif ($command == 'deposit') //deposit menu
  IGB_deposit();
elseif ($command == 'deposit2') //deposit operation
  IGB_deposit2();
elseif ($command == 'transfer') //main transfer menu
  IGB_transfer();
elseif ($command == 'transfer2') //specific transfer menu (ship or planet)
  IGB_transfer2();
elseif ($command == 'transfer3') //transfer operation
  IGB_transfer3();
elseif ($command == 'loans') //loans menu
  IGB_loans();
elseif ($command == 'borrow') //borrow operation
  IGB_borrow();
elseif ($command == 'repay') //repay operation
  IGB_repay();
elseif ($command == 'consolidate') //consolidate menu
  IGB_consolidate();
elseif ($command == 'consolidate2') //consolidate compute
  IGB_consolidate2();
elseif ($command == 'consolidate3') //consolidate operation
  IGB_consolidate3();
else
{
  echo "
  <tr>
    <td width='25%' valign='bottom' align='left'><a href=\"main.php\">$l_igb_quit</a></td>
    <td width='50%' style='text-align:left;'>
  <pre style='text-align:left;' class='term'>
  IIIIIIIIII          GGGGGGGGGGGGG    BBBBBBBBBBBBBBBBB
  I::::::::I       GGG::::::::::::G    B::::::::::::::::B
  I::::::::I     GG:::::::::::::::G    B::::::BBBBBB:::::B
  II::::::II    G:::::GGGGGGGG::::G    BB:::::B     B:::::B
    I::::I     G:::::G       GGGGGG      B::::B     B:::::B
    I::::I    G:::::G                    B::::B     B:::::B
    I::::I    G:::::G                    B::::BBBBBB:::::B
    I::::I    G:::::G    GGGGGGGGGG      B:::::::::::::BB
    I::::I    G:::::G    G::::::::G      B::::BBBBBB:::::B
    I::::I    G:::::G    GGGGG::::G      B::::B     B:::::B
    I::::I    G:::::G        G::::G      B::::B     B:::::B
    I::::I     G:::::G       G::::G      B::::B     B:::::B
  II::::::II    G:::::GGGGGGGG::::G    BB:::::BBBBBB::::::B
  I::::::::I     GG:::::::::::::::G    B:::::::::::::::::B
  I::::::::I       GGG::::::GGG:::G    B::::::::::::::::B
  IIIIIIIIII          GGGGGG   GGGG    BBBBBBBBBBBBBBBBB
  </pre>
  <center class='term'>
  ";
  echo $l_igb_title;
  echo "(tm)<br>";
  echo $l_igb_humor;
  echo "<br>&nbsp;
  </center>
  </td>
  <td width='25%' valign='bottom' align='right'><a href=\"igb.php?command=login\">$l_igb_login</a></td>
  ";
}

?>

</table>
</td></tr>
</table>
</div>
<img src="images/div2.png" alt="">
</center>

<?php
?>
</div></div>
<?
include "footer.php";

function IGB_login()
{
  global $playerinfo;
  global $account;
  global $l_igb_welcometoigb, $l_igb_accountholder, $l_igb_back, $l_igb_logout;
  global $l_igb_igbaccount, $l_igb_shipaccount, $l_igb_withdraw, $l_igb_transfer;
  global $l_igb_deposit, $l_igb_credit_symbol, $l_igb_operations, $l_igb_loans;

  echo "<tr><td colspan=2 align=center valign=top>$l_igb_welcometoigb<br>---------------------------------</td></tr>" .
       "<tr valign=top>" .
       "<td width=150 align=right>$l_igb_accountholder :<br><br>$l_igb_shipaccount :<br>$l_igb_igbaccount&nbsp;&nbsp;:</td>" .
       "<td style='max-width:550px; padding-right:4px;' align=right>$playerinfo[character_name]&nbsp;&nbsp;<br><br>".NUMBER($playerinfo['credits']) . " $l_igb_credit_symbol<br>" . NUMBER($account['balance']) . " $l_igb_credit_symbol<br></td>" .
       "</tr>" .
       "<tr><td colspan=2 align=center>$l_igb_operations<br>---------------------------------<br><br><a href=\"igb.php?command=withdraw\">$l_igb_withdraw</a><br><a href=\"igb.php?command=deposit\">$l_igb_deposit</a><br><a href=\"igb.php?command=transfer\">$l_igb_transfer</a><br><a href=\"igb.php?command=loans\">$l_igb_loans</a><br>&nbsp;</td></tr>" .
       "<tr valign=bottom>" .
       "<td align='left'><a href='igb.php'>$l_igb_back</a></td><td align=right>&nbsp;<br><a href=\"main.php\">$l_igb_logout</a></td>" .
       "</tr>";
}

function IGB_withdraw()
{
  global $playerinfo;
  global $account;
  global $l_igb_withdrawfunds, $l_igb_fundsavailable, $l_igb_selwithdrawamount;
  global $l_igb_withdraw, $l_igb_back, $l_igb_logout;

  echo "<tr><td colspan=2 align=center valign=top>$l_igb_withdrawfunds<br>---------------------------------</td></tr>" .
       "<tr valign=top><td>Funds available :</td><td align=right>" . NUMBER($account['balance']) ." C<br></td></tr>" .
	   "<tr><td colspan=\"2\">Warning A 10% Federation Processing Fee Will Be Deducted From Any Withdrawals!</td></tr>".
       "<tr valign=top><td>$l_igb_selwithdrawamount :</td><td align=right>" .
       "<form action='igb.php?command=withdraw2' method=POST>" .
       "<input class=term type=text size=15 maxlength=20 name=amount value=0>" .
       "<br><br><input class=term type=submit value=$l_igb_withdraw>" .
       "</form></td></tr>" .
       "<tr valign=bottom>" .
       "<td><a href='igb.php?command=login'>$l_igb_back</a></td><td align=right>&nbsp;<br><a href=\"main.php\">$l_igb_logout</a></td>" .
       "</tr>";

}

function IGB_deposit()
{
  global $playerinfo;
  global $account;
  global $l_igb_depositfunds, $l_igb_fundsavailable, $l_igb_seldepositamount;
  global $l_igb_deposit, $l_igb_back, $l_igb_logout;
  global $max_credits_allowed;

  $credit_space = ($max_credits_allowed - $account['balance']);

  if ($credit_space > $playerinfo['credits'])
  {
    $credit_space = ($playerinfo['credits']);
  }

  if ($credit_space <0)
  $credit_space = 0;


  echo "<tr><td height=53 colspan=2 align=center valign=top>$l_igb_depositfunds<br>---------------------------------</td></tr>" .
       "<tr valign=top>" .
       "<td height=30>$l_igb_fundsavailable :</td>" .
       "<td align=right>" . NUMBER($playerinfo['credits']) ." C<br></td>" .
       "</tr>".
	   "<tr><td colspan=\"2\">Warning A 10% Federation Processing Fee Will Be Deducted From Any Deposits!</td></tr>".
	   "<tr valign=top>" .
       "<td height=90>$l_igb_seldepositamount :</td><td align=right>" .
       "<form action='igb.php?command=deposit2' method=POST>" .
       "<input class=term type=text size=15 maxlength=20 name=amount value=0>" .
       "<br><br><input class=term type=submit value=$l_igb_deposit>" .
       "</form>" .
       "</td></tr>" .
       "<tr>" .
       "  <td height=30  colspan=2 align=left>" .
       "    <span style='color:\"#00ff00\";'>You can deposit only ". NUMBER($credit_space)." credits.</span><br>" .
       "  </td>" .
       "</tr>" .
       "<tr valign=bottom>" .
       "<td><a href='igb.php?command=login'>$l_igb_back</a></td><td align=right>&nbsp;<br><a href=\"main.php\">$l_igb_logout</a></td>" .
       "</tr>";

}

function IGB_transfer()
{
  global $playerinfo;
  global $account;
  global $IGB_min_turns;
  global $l_igb_transfertype, $l_igb_toanothership, $l_igb_shiptransfer, $l_igb_fromplanet, $l_igb_source, $l_igb_consolidate;
  global $l_igb_unnamed, $l_igb_in, $l_igb_none, $l_igb_planettransfer, $l_igb_back, $l_igb_logout, $l_igb_destination, $l_igb_conspl;
  global $db, $db_logging;

  $res = $db->Execute("SELECT character_name, ship_id FROM {$db->prefix}ships WHERE email not like '%@xenobe' AND ship_destroyed ='N' AND turns_used > $IGB_min_turns ORDER BY character_name ASC");
  db_op_result ($db, $res, __LINE__, __FILE__, $db_logging);
  while (!$res->EOF)
  {
    $ships[]=$res->fields;
    $res->MoveNext();
  }

  $res = $db->Execute("SELECT name, planet_id, sector_id FROM {$db->prefix}planets WHERE owner=$playerinfo[ship_id] ORDER BY sector_id ASC");
  db_op_result ($db, $res, __LINE__, __FILE__, $db_logging);
  while (!$res->EOF)
  {
    $planets[]=$res->fields;
    $res->MoveNext();
  }


  echo "<tr><td colspan=2 align=center valign=top>$l_igb_transfertype<br>---------------------------------</td></tr>" .
       "<tr valign=top>" .
       "<form action='igb.php?command=transfer2' method=POST>" .
       "<td>$l_igb_toanothership :<br><br>" .
       "<select class=term name=ship_id style='width:200px;'>";

  foreach ($ships as $ship)
  {
    echo "<option value=$ship[ship_id]>$ship[character_name]</option>";
  }

  echo "</select></td><td valign=center align=right>" .
       "<input class=term type=submit name=shipt value=\" $l_igb_shiptransfer \">" .
       "</form>" .
       "</td></tr>" .
       "<tr valign=top>" .
       "<td><br>$l_igb_fromplanet :<br><br>" .
       "<form action='igb.php?command=transfer2' method=POST>" .
       "$l_igb_source&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<select class=term name=splanet_id>";

  if (isset($planets))
  {
    foreach ($planets as $planet)
    {
      if (empty($planet['name']))
        $planet['name'] = $l_igb_unnamed;
      echo "<option value=$planet[planet_id]>$planet[name] $l_igb_in $planet[sector_id]</option>";
    }
  }
  else
  {
     echo "<option value=none>$l_igb_none</option>";
  }

  echo "</select><br>$l_igb_destination <select class=term name=dplanet_id>";

  if (isset($planets))
  {
    foreach ($planets as $planet)
    {
      if (empty($planet['name']))
        $planet['name'] = $l_igb_unnamed;
      echo "<option value=$planet[planet_id]>$planet[name] $l_igb_in $planet[sector_id]</option>";
    }
  }
  else
  {
     echo "<option value=none>$l_igb_none</option>";
  }


  echo "</select></td><td valign=center align=right>" .
       "<br><input class=term type=submit name=planett value=\"$l_igb_planettransfer\">" .
       "</td></tr>" .
       "</form>";

// ---- begin Consol Credits form    // ---- added by Torr
  echo "<tr valign=top>" .
       "<td><br>$l_igb_conspl :<br><br>" .
       "<form action='igb.php?command=consolidate' method=POST>" .
       "$l_igb_destination <select class=term name=dplanet_id>";

  if (isset($planets))
  {
    foreach ($planets as $planet)
    {
      if (empty($planet['name']))
        $planet['name'] = $l_igb_unnamed;
      echo "<option value=$planet[planet_id]>$planet[name] $l_igb_in $planet[sector_id]</option>";
    }
  }
  else
  {
     echo "<option value=none>$l_igb_none</option>";
  }

  echo "</select></td><td valign=top align=right>" .
       "<br><input class=term type=submit name=planetc value=\"  $l_igb_consolidate  \">" .
       "</td></tr>" .
       "</form>";
// ---- End Consol Credits form ---

  echo "</form><tr valign=bottom>" .
       "<td><a href='igb.php?command=login'>$l_igb_back</a></td><td align=right>&nbsp;<br><a href=\"main.php\">$l_igb_logout</a></td>" .
       "</tr>";
}

function IGB_transfer2()
{
  global $playerinfo;
  global $account;
  global $ship_id;
  global $splanet_id;
  global $dplanet_id;
  global $IGB_min_turns;
  global $IGB_svalue;
  global $ibank_paymentfee;
  global $IGB_trate;
  global $l_igb_sendyourself, $l_igb_unknowntargetship, $l_igb_min_turns, $l_igb_min_turns2;
  global $l_igb_mustwait, $l_igb_shiptransfer, $l_igb_igbaccount, $l_igb_maxtransfer;
  global $l_igb_unlimited, $l_igb_maxtransferpercent, $l_igb_transferrate, $l_igb_recipient;
  global $l_igb_seltransferamount, $l_igb_transfer, $l_igb_back, $l_igb_logout, $l_igb_in;
  global $l_igb_errplanetsrcanddest, $l_igb_errunknownplanet, $l_igb_unnamed;
  global $l_igb_errnotyourplanet, $l_igb_planettransfer, $l_igb_srcplanet, $l_igb_destplanet;
  global $l_igb_transferrate2, $l_igb_seltransferamount, $l_igb_errnobase;
  global $db, $db_logging;

  if (isset($ship_id)) //ship transfer
  {
    $res = $db->Execute("SELECT * FROM {$db->prefix}ships WHERE ship_id=$ship_id AND ship_destroyed ='N' AND turns_used > $IGB_min_turns;");
    db_op_result ($db, $res, __LINE__, __FILE__, $db_logging);

    if ($playerinfo['ship_id'] == $ship_id)
      IGB_error($l_igb_sendyourself, "igb.php?command=transfer");

    if (!$res instanceof ADORecordSet || $res->EOF)
      IGB_error($l_igb_unknowntargetship, "igb.php?command=transfer");

    $target = $res->fields;

    if ($target['turns_used'] < $IGB_min_turns)
    {
      $l_igb_min_turns = str_replace("[igb_min_turns]", $IGB_min_turns, $l_igb_min_turns);
      $l_igb_min_turns = str_replace("[igb_target_char_name]", $target['character_name'], $l_igb_min_turns);
      IGB_error($l_igb_min_turns, "igb.php?command=transfer");
    }

    if ($playerinfo['turns_used'] < $IGB_min_turns)
    {
      $l_igb_min_turns2 = str_replace("[igb_min_turns]", $IGB_min_turns, $l_igb_min_turns2);
      IGB_error($l_igb_min_turns2, "igb.php?command=transfer");
    }

    if ($IGB_trate > 0)
    {
      $curtime = time();
      $curtime -= $IGB_trate * 60;
      $res = $db->Execute("SELECT UNIX_TIMESTAMP(time) as time FROM {$db->prefix}IGB_transfers WHERE UNIX_TIMESTAMP(time) > $curtime AND source_id=$playerinfo[ship_id] AND dest_id=$target[ship_id]");
      db_op_result ($db, $res, __LINE__, __FILE__, $db_logging);
      if (!$res->EOF)
      {
        $time = $res->fields;
        $difftime = ($time['time'] - $curtime) / 60;
        $l_igb_mustwait = str_replace("[igb_target_char_name]", $target['character_name'], $l_igb_mustwait);
        $l_igb_mustwait = str_replace("[igb_trate]", NUMBER($IGB_trate), $l_igb_mustwait);
        $l_igb_mustwait = str_replace("[igb_difftime]", NUMBER($difftime), $l_igb_mustwait);
        IGB_error($l_igb_mustwait, "igb.php?command=transfer");
      }
    }

    echo "<tr><td colspan=2 align=center valign=top>$l_igb_shiptransfer<br>---------------------------------</td></tr>" .
         "<tr valign=top><td>$l_igb_igbaccount :</td><td align=right>" . NUMBER($account['balance']) . " C</td></tr>";

    if ($IGB_svalue == 0)
      echo "<tr valign=top><td>$l_igb_maxtransfer :</td><td align=right>$l_igb_unlimited</td></tr>";
    else
    {
      $percent = $IGB_svalue * 100;
      $score = gen_score($playerinfo['ship_id']);
      $maxtrans = $score * $score * $IGB_svalue;

      $l_igb_maxtransferpercent = str_replace("[igb_percent]", $percent, $l_igb_maxtransferpercent);
      echo "<tr valign=top><td nowrap>$l_igb_maxtransferpercent :</td><td align=right>" . NUMBER($maxtrans) . " C</td></tr>";
    }

    $percent = $ibank_paymentfee * 100;

    $l_igb_transferrate = str_replace("[igb_num_percent]", NUMBER($percent,1), $l_igb_transferrate);
    echo "<tr valign=top><td>$l_igb_recipient :</td><td align=right>$target[character_name]&nbsp;&nbsp;</td></tr>" .
         "<form action='igb.php?command=transfer3' method=POST>" .
         "<tr valign=top>" .
         "<td><br>$l_igb_seltransferamount :</td>" .
         "<td align=right><br><input class=term type=text size=15 maxlength=20 name=amount value=0><br>" .
         "<br><input class=term type=submit value=$l_igb_transfer></td>" .
         "<input type=hidden name=ship_id value=$ship_id>" .
         "</form>" .
         "<tr><td colspan=2 align=center>" .
         "$l_igb_transferrate" .
         "<tr valign=bottom>" .
         "<td><a href='igb.php?command=transfer'>$l_igb_back</a></td><td align=right>&nbsp;<br><a href=\"main.php\">$l_igb_logout</a></td>" .
         "</tr>";
  }
  else
  {
    if ($splanet_id == $dplanet_id)
      IGB_error($l_igb_errplanetsrcanddest, "igb.php?command=transfer");

    $res = $db->Execute("SELECT name, credits, owner, sector_id FROM {$db->prefix}planets WHERE planet_id=$splanet_id");
    db_op_result ($db, $res, __LINE__, __FILE__, $db_logging);
    if (!$res || $res->EOF)
      IGB_error($l_igb_errunknownplanet, "igb.php?command=transfer");
    $source = $res->fields;


    if (empty($source['name']))
      $source['name']=$l_igb_unnamed;

    $res = $db->Execute("SELECT name, credits, owner, sector_id, base FROM {$db->prefix}planets WHERE planet_id=$dplanet_id");
    db_op_result ($db, $res, __LINE__, __FILE__, $db_logging);
    if (!$res || $res->EOF)
      IGB_error($l_igb_errunknownplanet, "igb.php?command=transfer");
    $dest = $res->fields;

    if (empty($dest['name']))
      $dest['name']=$l_igb_unnamed;
    if ($dest['base'] == 'N')
      IGB_error($l_igb_errnobase, "igb.php?command=transfer");



    if ($source['owner'] != $playerinfo['ship_id'] || $dest['owner'] != $playerinfo['ship_id'])
      IGB_error($l_igb_errnotyourplanet, "igb.php?command=transfer");

    $percent = $ibank_paymentfee * 100;

    $l_igb_transferrate2 = str_replace("[igb_num_percent]", NUMBER($percent,1), $l_igb_transferrate2);
    echo "<tr><td colspan=2 align=center valign=top>$l_igb_planettransfer<br>---------------------------------</td></tr>" .
         "<tr valign=top>" .
         "<td>$l_igb_srcplanet $source[name] $l_igb_in $source[sector_id] :" .
         "<td align=right>" . NUMBER($source['credits']) . " C" .
         "<tr valign=top>" .
         "<td>$l_igb_destplanet $dest[name] $l_igb_in $dest[sector_id] :" .
         "<td align=right>" . NUMBER($dest['credits']) . " C" .
         "<form action='igb.php?command=transfer3' method=POST>" .
         "<tr valign=top>" .
         "<td><br>$l_igb_seltransferamount :</td>" .
         "<td align=right><br><input class=term type=text size=15 maxlength=20 name=amount value=0><br>" .
         "<br><input class=term type=submit value=$l_igb_transfer></td>" .
         "<input type=hidden name=splanet_id value=$splanet_id>" .
         "<input type=hidden name=dplanet_id value=$dplanet_id>" .
         "</form>" .
         "<tr><td colspan=2 align=center>" .
         "$l_igb_transferrate2" .
         "<tr valign=bottom>" .
         "<td><a href='igb.php?command=transfer'>$l_igb_back</a></td><td align=right>&nbsp;<br><a href=\"main.php\">$l_igb_logout</a></td>" .
         "</tr>";
  }

}

function IGB_transfer3()
{
  global $playerinfo;
  global $account;
  global $ship_id;
  global $splanet_id;
  global $dplanet_id;
  global $IGB_min_turns;
  global $IGB_svalue;
  global $ibank_paymentfee;
  global $amount;
  global $IGB_trate;
  global $l_igb_errsendyourself, $l_igb_unknowntargetship, $l_igb_min_turns3, $l_igb_min_turns4, $l_igb_mustwait2;
  global $l_igb_invalidtransferinput, $l_igb_nozeroamount, $l_igb_notenoughcredits, $l_igb_notenoughcredits2, $l_igb_in, $l_igb_to;
  global $l_igb_amounttoogreat, $l_igb_transfersuccessful, $l_igb_creditsto, $l_igb_transferamount, $l_igb_amounttransferred;
  global $l_igb_transferfee, $l_igb_igbaccount, $l_igb_back, $l_igb_logout, $l_igb_errplanetsrcanddest, $l_igb_errnotyourplanet;
  global $l_igb_errunknownplanet, $l_igb_unnamed, $l_igb_ctransferredfrom, $l_igb_srcplanet, $l_igb_destplanet;
  global $db, $db_logging;

  $amount = StripNonNum($amount);

  if ($amount < 0)
    $amount = 0;


  if (isset($ship_id)) //ship transfer
  {
    // Need to check again to prevent cheating by manual posts

    $res = $db->Execute("SELECT * FROM {$db->prefix}ships WHERE ship_id=$ship_id AND ship_destroyed ='N' AND turns_used > $IGB_min_turns;");
    db_op_result ($db, $res, __LINE__, __FILE__, $db_logging);

    if ($playerinfo['ship_id'] == $ship_id)
      IGB_error($l_igb_errsendyourself, "igb.php?command=transfer");

    if (!$res || $res->EOF)
      IGB_error($l_igb_unknowntargetship, "igb.php?command=transfer");

    $target = $res->fields;

    if ($target['turns_used'] < $IGB_min_turns)
    {
      $l_igb_min_turns3 = str_replace("[igb_min_turns]", $IGB_min_turns, $l_igb_min_turns3);
      $l_igb_min_turns3 = str_replace("[igb_target_char_name]", $target['character_name'], $l_igb_min_turns3);
      IGB_error($l_igb_min_turns3, "igb.php?command=transfer");
    }

    if ($playerinfo['turns_used'] < $IGB_min_turns)
    {
      $l_igb_min_turns4 = str_replace("[igb_min_turns]", $IGB_min_turns, $l_igb_min_turns4);
      IGB_error($l_igb_min_turns4, "igb.php?command=transfer");
    }

    if ($IGB_trate > 0)
    {
      $curtime = time();
      $curtime -= $IGB_trate * 60;
      $res = $db->Execute("SELECT UNIX_TIMESTAMP(time) as time FROM {$db->prefix}IGB_transfers WHERE UNIX_TIMESTAMP(time) > $curtime AND source_id=$playerinfo[ship_id] AND dest_id=$target[ship_id]");
      db_op_result ($db, $res, __LINE__, __FILE__, $db_logging);
      if (!$res->EOF)
      {
        $time = $res->fields;
        $difftime = ($time['time'] - $curtime) / 60;
        $l_igb_mustwait2 = str_replace("[igb_target_char_name]", $target['character_name'], $l_igb_mustwait2);
        $l_igb_mustwait2 = str_replace("[igb_trate]", NUMBER($IGB_trate), $l_igb_mustwait2);
        $l_igb_mustwait2 = str_replace("[igb_difftime]", NUMBER($difftime), $l_igb_mustwait2);
        IGB_error($l_igb_mustwait2, "igb.php?command=transfer");
      }
    }

    if (($amount * 1) != $amount)
      IGB_error($l_igb_invalidtransferinput, "igb.php?command=transfer");

    if ($amount == 0)
      IGB_error($l_igb_nozeroamount, "igb.php?command=transfer");

    if ($amount > $account['balance'])
      IGB_error($l_igb_notenoughcredits, "igb.php?command=transfer");

    if ($IGB_svalue != 0)
    {
      $percent = $IGB_svalue * 100;
      $score = gen_score($playerinfo['ship_id']);
      $maxtrans = $score * $score * $IGB_svalue;

      if ($amount > $maxtrans)
        IGB_error($l_igb_amounttoogreat, "igb.php?command=transfer");
    }

    $account['balance'] -= $amount;
    $amount2 = $amount * $ibank_paymentfee;
    $transfer = $amount - $amount2;

    echo "<tr><td colspan=2 align=center valign=top>$l_igb_transfersuccessful<br>---------------------------------</td></tr>" .
         "<tr valign=top><td colspan=2 align=center>" . NUMBER($transfer) . " $l_igb_creditsto $target[character_name].</tr>" .
         "<tr valign=top>" .
         "<td>$l_igb_transferamount :</td><td align=right>" . NUMBER($amount) . " C<br>" .
         "<tr valign=top>" .
         "<td>$l_igb_transferfee :</td><td align=right>" . NUMBER($amount2) . " C<br>" .
         "<tr valign=top>" .
         "<td>$l_igb_amounttransferred :</td><td align=right>" . NUMBER($transfer) . " C<br>" .
         "<tr valign=top>" .
         "<td>$l_igb_igbaccount :</td><td align=right>" . NUMBER($account['balance']) . " C<br>" .
         "<tr valign=bottom>" .
         "<td><a href='igb.php?command=login'>$l_igb_back</a></td><td align=right>&nbsp;<br><a href=\"main.php\">$l_igb_logout</a></td>" .
         "</tr>";

    $resx = $db->Execute("UPDATE {$db->prefix}ibank_accounts SET balance=balance-$amount WHERE ship_id=$playerinfo[ship_id]");
    db_op_result ($db, $resx, __LINE__, __FILE__, $db_logging);
    $resx = $db->Execute("UPDATE {$db->prefix}ibank_accounts SET balance=balance+$transfer WHERE ship_id=$target[ship_id]");
    db_op_result ($db, $resx, __LINE__, __FILE__, $db_logging);

    $resx = $db->Execute("INSERT INTO {$db->prefix}IGB_transfers VALUES(NULL, $playerinfo[ship_id], $target[ship_id], NOW(), $transfer)");
    db_op_result ($db, $resx, __LINE__, __FILE__, $db_logging);
    echo $db->ErrorMsg();
  }
  else
  {
    if ($splanet_id == $dplanet_id)
      IGB_error($l_igb_errplanetsrcanddest, "igb.php?command=transfer");

    $res = $db->Execute("SELECT name, credits, owner, sector_id FROM {$db->prefix}planets WHERE planet_id=$splanet_id");
    db_op_result ($db, $res, __LINE__, __FILE__, $db_logging);
    if (!$res || $res->EOF)
      IGB_error($l_igb_errunknownplanet, "igb.php?command=transfer");
    $source = $res->fields;

    if (empty($source['name']))
      $source['name']=$l_igb_unnamed;

    $res = $db->Execute("SELECT name, credits, owner, sector_id FROM {$db->prefix}planets WHERE planet_id=$dplanet_id");
    db_op_result ($db, $res, __LINE__, __FILE__, $db_logging);
    if (!$res || $res->EOF)
      IGB_error($l_igb_errunknownplanet, "igb.php?command=transfer");
    $dest = $res->fields;

    if (empty($dest['name']))
      $dest['name']=$l_igb_unnamed;

    if ($source['owner'] != $playerinfo['ship_id'] || $dest['owner'] != $playerinfo['ship_id'])
      IGB_error($l_igb_errnotyourplanet, "igb.php?command=transfer");

    if ($amount > $source['credits'])
      IGB_error($l_igb_notenoughcredits2, "igb.php?command=transfer");

    $percent = $ibank_paymentfee * 100;

    $source['credits'] -= $amount;
    $amount2 = $amount * $ibank_paymentfee;
    $transfer = $amount - $amount2;
    $dest['credits'] += $transfer;

    echo "<tr><td colspan=2 align=center valign=top>$l_igb_transfersuccessful<br>---------------------------------</td></tr>" .
         "<tr valign=top><td colspan=2 align=center>" . NUMBER($transfer) . " $l_igb_ctransferredfrom $source[name] $l_igb_to $dest[name].</tr>" .
         "<tr valign=top>" .
         "<td>$l_igb_transferamount :</td><td align=right>" . NUMBER($amount) . " C<br>" .
         "<tr valign=top>" .
         "<td>$l_igb_transferfee :</td><td align=right>" . NUMBER($amount2) . " C<br>" .
         "<tr valign=top>" .
         "<td>$l_igb_amounttransferred :</td><td align=right>" . NUMBER($transfer) . " C<br>" .
         "<tr valign=top>" .
         "<td>$l_igb_srcplanet $source[name] $l_igb_in $source[sector_id] :</td><td align=right>" . NUMBER($source['credits']) . " C<br>" .
         "<tr valign=top>" .
         "<td>$l_igb_destplanet $dest[name] $l_igb_in $dest[sector_id] :</td><td align=right>" . NUMBER($dest['credits']) . " C<br>" .
         "<tr valign=bottom>" .
         "<td><a href='igb.php?command=login'>$l_igb_back</a></td><td align=right>&nbsp;<br><a href=\"main.php\">$l_igb_logout</a></td>" .
         "</tr>";

    $resx = $db->Execute("UPDATE {$db->prefix}planets SET credits=credits-$amount WHERE planet_id=$splanet_id");
    db_op_result ($db, $resx, __LINE__, __FILE__, $db_logging);
    $resx = $db->Execute("UPDATE {$db->prefix}planets SET credits=credits+$transfer WHERE planet_id=$dplanet_id");
    db_op_result ($db, $resx, __LINE__, __FILE__, $db_logging);
  }
}

function IGB_deposit2()
{
  global $playerinfo;
  global $amount;
  global $account;
  global $l_igb_invaliddepositinput, $l_igb_nozeroamount2, $l_igb_notenoughcredits, $l_igb_accounts, $l_igb_logout;
  global $l_igb_operationsuccessful, $l_igb_creditstoyou, $l_igb_igbaccount, $l_igb_shipaccount, $l_igb_back;
  global $db, $db_logging;
  global $max_credits_allowed;

  $amount = StripNonNum($amount);
  if (($amount * 1) != $amount)
    IGB_error($l_igb_invaliddepositinput, "igb.php?command=deposit");

  if ($amount == 0)
    IGB_error($l_igb_nozeroamount2, "igb.php?command=deposit");

  if ($amount > $playerinfo['credits'])
    IGB_error($l_igb_notenoughcredits, "igb.php?command=deposit");

// temp credits
  $tmpcredits = $max_credits_allowed - $account['balance'];
  if ($tmpcredits <0)
  {
    $tmpcredits = 0;
  }
  if ($amount > $tmpcredits)
    IGB_error("<center>Error You cannot deposit that much into your bank,<br> (Max Credits Reached)</center>", "igb.php?command=deposit");
	$amount2 = $amount;
	$fee = ($amount/100)*10;
	$amount = $amount - $fee;
  	$player_igb_balance = $account['balance'] + $amount;
  	$player_ship_balance = $playerinfo['credits'] - $amount2;
  echo "<tr><td colspan=2 align=center valign=top>$l_igb_operationsuccessful<br>---------------------------------</td></tr>" .
       "<tr valign=top><td colspan=2 align=center>" . NUMBER($amount) ." $l_igb_creditstoyou</td></tr>" .
	   "<tr valign=top><td colspan=2 align=center>" . NUMBER($fee) ." Credit Processing Fee (10%) Deducted.</td></tr>" .
       "<tr><td colspan=2 align=center>$l_igb_accounts<br>---------------------------------</td></tr>" .
       "<tr valign=top>" .
       "<td>$l_igb_shipaccount :<br>$l_igb_igbaccount :</td>" .
       "<td align=right>" . NUMBER($player_ship_balance) . " C<br>" . NUMBER($player_igb_balance) . " C</tr>" .
       "<tr valign=bottom>" .
       "<td><a href='igb.php?command=login'>$l_igb_back</a></td><td align=right>&nbsp;<br><a href=\"main.php\">$l_igb_logout</a></td>" .
       "</tr>";

  $resx = $db->Execute("UPDATE {$db->prefix}ibank_accounts SET balance=".$player_igb_balance." WHERE ship_id=$playerinfo[ship_id]");
  db_op_result ($db, $resx, __LINE__, __FILE__, $db_logging);
  $resx = $db->Execute("UPDATE {$db->prefix}ships SET credits=".$player_ship_balance." WHERE ship_id=$playerinfo[ship_id]");
  db_op_result ($db, $resx, __LINE__, __FILE__, $db_logging);
}

function IGB_withdraw2()
{
  global $playerinfo;
  global $amount;
  global $account;
  global $l_igb_invalidwithdrawinput, $l_igb_nozeroamount3, $l_igb_notenoughcredits, $l_igb_accounts;
  global $l_igb_operationsuccessful, $l_igb_creditstoyourship, $l_igb_igbaccount, $l_igb_back, $l_igb_logout;
  global $db, $db_logging;

  $amount = StripNonNum($amount);
  if (($amount * 1) != $amount)
    IGB_error($l_igb_invalidwithdrawinput, "igb.php?command=withdraw");

  if ($amount == 0)
    IGB_error($l_igb_nozeroamount3, "igb.php?command=withdraw");

  if ($amount > $account['balance'])
    IGB_error($l_igb_notenoughcredits, "igb.php?command=withdraw");
	$amount2 = $amount;
	$fee = ($amount/100)*10;
	/*New amount minus fee*/
	$amount = $amount - $fee;
  	$player_igb_balance = $account['balance'] - $amount2;
  	$player_ship_balance = $playerinfo['credits'] + $amount;
 	echo "<tr><td colspan=2 align=center valign=top>$l_igb_operationsuccessful<br>---------------------------------</td></tr>" .
       "<tr valign=top><td colspan=2 align=center>" . NUMBER($amount) ." Credits have been transfered to your ship account.</td></tr>" .
	   "<tr valign=top><td colspan=2 align=center>" . NUMBER($fee) ." Credit Processing Fee (10%) Deducted.</td></tr>" .
       "<tr><td colspan=2 align=center>$l_igb_accounts<br>---------------------------------</td></tr>" .
       "<tr valign=top>" .
       "<td>Ship Account :<br>$l_igb_igbaccount :</td>" .
       "<td align=right>" . NUMBER($player_ship_balance) . " C<br/>" . NUMBER($player_igb_balance) . " C</tr>" .
       "<tr valign=bottom>" .
       "<td><a href='igb.php?command=login'>$l_igb_back</a></td><td align=right>&nbsp;<br><a href=\"main.php\">$l_igb_logout</a></td>" .
       "</tr>";

  $resx = $db->Execute("UPDATE {$db->prefix}ibank_accounts SET balance=".$player_igb_balance." WHERE ship_id=$playerinfo[ship_id]");
  db_op_result ($db, $resx, __LINE__, __FILE__, $db_logging);
  $resx = $db->Execute("UPDATE {$db->prefix}ships SET credits=".$player_ship_balance." WHERE ship_id=$playerinfo[ship_id]");
  db_op_result ($db, $resx, __LINE__, __FILE__, $db_logging);
}

function IGB_loans()
{
  global $playerinfo, $account;
  global $ibank_loanlimit, $ibank_loanfactor, $ibank_loaninterest;
  global $l_igb_loanstatus,$l_igb_shipaccount, $l_igb_currentloan, $l_igb_repay;
  global $l_igb_maxloanpercent, $l_igb_loanamount, $l_igb_borrow, $l_igb_loanrates;
  global $l_igb_back, $l_igb_logout, $IGB_lrate, $l_igb_loantimeleft, $l_igb_loanlate, $l_igb_repayamount;
  global $db, $db_logging;

  echo "<tr><td colspan=2 align=center valign=top>$l_igb_loanstatus<br>---------------------------------</td></tr>" .
       "<tr valign=top><td>$l_igb_shipaccount :</td><td align=right>" . NUMBER($playerinfo['credits']) . " C</td></tr>" .
       "<tr valign=top><td>$l_igb_currentloan :</td><td align=right>" . NUMBER($account['loan']) . " C</td></tr>";

  if ($account['loan'] != 0)
  {
    $curtime = time();
    $res = $db->Execute("SELECT UNIX_TIMESTAMP(loantime) as time FROM {$db->prefix}ibank_accounts WHERE ship_id=$playerinfo[ship_id]");
    db_op_result ($db, $res, __LINE__, __FILE__, $db_logging);
    if (!$res->EOF)
    {
      $time = $res->fields;
    }

    $difftime = ($curtime - $time['time']) / 60;

    echo "<tr valign=top><td nowrap>$l_igb_loantimeleft :</td>";

    if ($difftime > $IGB_lrate)
      echo "<td align=right>$l_igb_loanlate</td></tr>";
    else
    {
      $difftime=$IGB_lrate - $difftime;
      $hours = $difftime / 60;
      $hours = (int) $hours;
      $mins = $difftime % 60;
      echo "<td align=right>{$hours}h {$mins}m</td></tr>";
    }

    $factor = $ibank_loanfactor *=100;
    $interest = $ibank_loaninterest *=100;

    $l_igb_loanrates = str_replace("[factor]", $factor, $l_igb_loanrates);
    $l_igb_loanrates = str_replace("[interest]", $interest, $l_igb_loanrates);

    echo "<form action='igb.php?command=repay' method=POST>" .
         "<tr valign=top>" .
         "<td><br>$l_igb_repayamount :</td>" .
         "<td align=right><br><input class=term type=text size=15 maxlength=20 name=amount value=0><br>" .
         "<br><input class=term type=submit value=$l_igb_repay></td>" .
         "</form>" .
         "<tr><td colspan=2 align=center>" .
         "$l_igb_loanrates";
  }
  else
  {
    $percent = $ibank_loanlimit * 100;
    $score = gen_score($playerinfo['ship_id']);
    $maxloan = $score * $score * $ibank_loanlimit;

    $l_igb_maxloanpercent = str_replace("[igb_percent]", $percent, $l_igb_maxloanpercent);
    echo "<tr valign=top><td nowrap>$l_igb_maxloanpercent :</td><td align=right>" . NUMBER($maxloan) . " C</td></tr>";

    $factor = $ibank_loanfactor *=100;
    $interest = $ibank_loaninterest *=100;

    $l_igb_loanrates = str_replace("[factor]", $factor, $l_igb_loanrates);
    $l_igb_loanrates = str_replace("[interest]", $interest, $l_igb_loanrates);

    echo "<form action='igb.php?command=borrow' method=POST>" .
         "<tr valign=top>" .
         "<td><br>$l_igb_loanamount :</td>" .
         "<td align=right><br><input class=term type=text size=15 maxlength=20 name=amount value=0><br>" .
         "<br><input class=term type=submit value=$l_igb_borrow></td>" .
         "</form>" .
         "<tr><td colspan=2 align=center>" .
         "$l_igb_loanrates";
  }

  echo "<tr valign=bottom>" .
       "<td><a href='igb.php?command=login'>$l_igb_back</a></td><td align=right>&nbsp;<br><a href=\"main.php\">$l_igb_logout</a></td>" .
       "</tr>";
}

function IGB_borrow()
{
  global $playerinfo, $account, $amount, $ibank_loanlimit, $ibank_loanfactor;
  global $l_igb_invalidamount,$l_igb_notwoloans, $l_igb_loantoobig;
  global $l_igb_takenaloan, $l_igb_loancongrats, $l_igb_loantransferred;
  global $l_igb_loanfee, $l_igb_amountowned, $IGB_lrate, $l_igb_loanreminder, $l_igb_loanreminder2;
  global $db, $db_logging, $l_igb_back, $l_igb_logout;

  $amount = StripNonNum($amount);
  if (($amount * 1) != $amount)
    IGB_error($l_igb_invalidamount, "igb.php?command=loans");

  if ($amount <= 0)
    IGB_error($l_igb_invalidamount, "igb.php?command=loans");

  if ($account['loan'] != 0)
    IGB_error($l_igb_notwoloans, "igb.php?command=loans");

  $score = gen_score($playerinfo['ship_id']);
  $maxtrans = $score * $score * $ibank_loanlimit;

  if ($amount > $maxtrans)
    IGB_error($l_igb_loantoobig, "igb.php?command=loans");

  $amount2 = $amount * $ibank_loanfactor;
  $amount3= $amount + $amount2;

  $hours = $IGB_lrate / 60;
  $mins = $IGB_lrate % 60;

  $l_igb_loanreminder = str_replace("[hours]", $hours, $l_igb_loanreminder);
  $l_igb_loanreminder = str_replace("[mins]", $mins, $l_igb_loanreminder);

  echo "<tr><td colspan=2 align=center valign=top>$l_igb_takenaloan<br>---------------------------------</td></tr>" .
       "<tr valign=top><td colspan=2 align=center>$l_igb_loancongrats<br><br></tr>" .
       "<tr valign=top>" .
       "<td>$l_igb_loantransferred :</td><td nowrap align=right>" . NUMBER($amount) . " C<br>" .
       "<tr valign=top>" .
       "<td>$l_igb_loanfee :</td><td nowrap align=right>" . NUMBER($amount2) . " C<br>" .
       "<tr valign=top>" .
       "<td>$l_igb_amountowned :</td><td nowrap align=right>" . NUMBER($amount3) . " C<br>" .
       "<tr valign=top>" .
       "<td colspan=2 align=center>---------------------------------<br><br>$l_igb_loanreminder<br><br>\"" . $l_igb_loanreminder2 ."\"</td>" .
       "<tr valign=top>" .
       "<td nowrap><a href='igb.php?command=login'>$l_igb_back</a></td><td nowrap align=right>&nbsp;<a href=\"main.php\">$l_igb_logout</a></td>" .
       "</tr>";

  $resx = $db->Execute("UPDATE {$db->prefix}ibank_accounts SET loan=$amount3, loantime=NOW() WHERE ship_id=$playerinfo[ship_id]");
  db_op_result ($db, $resx, __LINE__, __FILE__, $db_logging);

  $resx = $db->Execute("UPDATE {$db->prefix}ships SET credits=credits+$amount WHERE ship_id=$playerinfo[ship_id]");
  db_op_result ($db, $resx, __LINE__, __FILE__, $db_logging);
}

function IGB_repay()
{
  global $playerinfo, $account, $amount;
  global $l_igb_notrepay, $l_igb_notenoughrepay,$l_igb_payloan;
  global $l_igb_shipaccount, $l_igb_currentloan, $l_igb_loanthanks;
  global $l_igb_invalidamount;
  global $db, $l_igb_back, $l_igb_logout, $db_logging;

  $amount = StripNonNum($amount);
  if (($amount * 1) != $amount)
    IGB_error($l_igb_invalidamount, "igb.php?command=loans");

  if ($amount == 0)
    IGB_error($l_igb_invalidamount, "igb.php?command=loans");

  if ($account['loan'] == 0)
    IGB_error($l_igb_notrepay, "igb.php?command=loans");

  if ($amount > $account['loan'])
    $amount = $account['loan'];

  if ($amount > $playerinfo['credits'])
    IGB_error($l_igb_notenoughrepay, "igb.php?command=loans");

  $playerinfo['credits']-=$amount;
  $account['loan']-=$amount;

  echo "<tr><td colspan=2 align=center valign=top>$l_igb_payloan<br>---------------------------------</td></tr>" .
       "<tr valign=top>" .
       "<td colspan=2 align=center>$l_igb_loanthanks</td>" .
       "<tr valign=top>" .
       "<td colspan=2 align=center>---------------------------------</td>" .
       "<tr valign=top>" .
       "<td>$l_igb_shipaccount :</td><td nowrap align=right>" . NUMBER($playerinfo['credits']) . " C<br>" .
       "<tr valign=top>" .
       "<td>$l_igb_payloan :</td><td nowrap align=right>" . NUMBER($amount) . " C<br>" .
       "<tr valign=top>" .
       "<td>$l_igb_currentloan :</td><td nowrap align=right>" . NUMBER($account['loan']) . " C<br>" .
       "<tr valign=top>" .
       "<td colspan=2 align=center>---------------------------------</td>" .
       "<tr valign=top>" .
       "<td nowrap><a href='igb.php?command=login'>$l_igb_back</a></td><td nowrap align=right>&nbsp;<a href=\"main.php\">$l_igb_logout</a></td>" .
       "</tr>";

  $resx = $db->Execute("UPDATE {$db->prefix}ibank_accounts SET loan=loan-$amount,loantime='$account[loantime]' WHERE ship_id=$playerinfo[ship_id]");
  db_op_result ($db, $resx, __LINE__, __FILE__, $db_logging);
  $resx = $db->Execute("UPDATE {$db->prefix}ships SET credits=credits-$amount WHERE ship_id=$playerinfo[ship_id]");
  db_op_result ($db, $resx, __LINE__, __FILE__, $db_logging);
}

function IGB_consolidate()
{
  global $playerinfo, $account;
  global $db, $db_logging;
  global $l_igb_errunknownplanet, $l_igb_errnotyourplanet, $l_igb_transferrate3;
  global $l_igb_planettransfer, $l_igb_destplanet, $l_igb_in, $IGB_tconsolidate;
  global $dplanet_id, $l_igb_unnamed, $l_igb_currentpl, $l_igb_consolrates;
  global $l_igb_minimum, $l_igb_maximum, $l_igb_back, $l_igb_logout;
  global $l_igb_planetconsolidate, $l_igb_compute, $ibank_paymentfee;

  $percent = $ibank_paymentfee * 100;

  $l_igb_transferrate3 = str_replace("[igb_num_percent]", NUMBER($percent,1), $l_igb_transferrate3);
  $l_igb_transferrate3 = str_replace("[nbplanets]", $IGB_tconsolidate, $l_igb_transferrate3);

// Why this line is here I have no clue, its not used in this file as far as I can see.
// But just in case, leve it commented out but keep it.
//  $destplanetcreds  = $dest['credits'];

  echo "<tr><td colspan=2 align=center valign=top>$l_igb_planetconsolidate<br>---------------------------------</td></tr>" .
       "<form action='igb.php?command=consolidate2' method=POST>" .
       "<tr valign=top>" .
       "<td colspan=2>$l_igb_consolrates :</td>" .
       "<tr valign=top>" .
       "<td>$l_igb_minimum :<br>" .
       "<br>$l_igb_maximum :</td>" .
       "<td align=right>" .
       "<input class=term type=text size=15 maxlength=20 name=minimum value=0><br><br>" .
       "<input class=term type=text size=15 maxlength=20 name=maximum value=0><br><br>" .
       "<input class=term type=submit value=\"$l_igb_compute\"></td>" .
       "<input type=hidden name=dplanet_id value=$dplanet_id>" .
       "</form>" .
       "<tr><td colspan=2 align=center>" .
       "$l_igb_transferrate3" .
       "<tr valign=bottom>" .
       "<td><a href='igb.php?command=transfer'>$l_igb_back</a></td><td align=right>&nbsp;<br><a href=\"main.php\">$l_igb_logout</a></td>" .
       "</tr>";
}

function IGB_consolidate2()
{
  global $playerinfo, $account;
  global $db, $db_logging;
  global $dplanet_id, $minimum, $maximum, $IGB_tconsolidate, $ibank_paymentfee;
  global $l_igb_planetconsolidate, $l_igb_back, $l_igb_logout;
  global $l_igb_errunknownplanet, $l_igb_unnamed, $l_igb_errnotyourplanet;
  global $l_igb_currentpl, $l_igb_in, $l_igb_transferamount, $l_igb_plaffected;
  global $l_igb_transferfee, $l_igb_turncost, $l_igb_amounttransferred;
  global $l_igb_consolidate;

  $res = $db->Execute("SELECT name, credits, owner, sector_id FROM {$db->prefix}planets WHERE planet_id=$dplanet_id");
  db_op_result ($db, $res, __LINE__, __FILE__, $db_logging);
  if (!$res || $res->EOF)
      IGB_error($l_igb_errunknownplanet, "igb.php?command=transfer");
  $dest = $res->fields;

  if (empty($dest['name']))
    $dest['name']=$l_igb_unnamed;

  if ($dest['owner'] != $playerinfo['ship_id'])
    IGB_error($l_igb_errnotyourplanet, "igb.php?command=transfer");

  $minimum = StripNonNum($minimum);
  $maximum = StripNonNum($maximum);

  $query = "SELECT SUM(credits) AS total, COUNT(*) AS count FROM {$db->prefix}planets WHERE owner=$playerinfo[ship_id] AND credits != 0";

  if ($minimum != 0)
    $query .= " AND credits >= $minimum";

  if ($maximum != 0)
    $query .= " AND credits <= $maximum";

  $query .= " AND planet_id != $dplanet_id";

  $res = $db->Execute($query);
  db_op_result ($db, $res, __LINE__, __FILE__, $db_logging);
  $amount = $res->fields;

  $fee = $ibank_paymentfee * $amount['total'];

  $tcost = ceil($amount['count'] / $IGB_tconsolidate);
  $transfer = $amount['total'] - $fee;

  echo "<tr><td colspan=2 align=center valign=top>$l_igb_planetconsolidate<br>---------------------------------</td></tr>" .
       "<tr valign=top>" .
       "<td>$l_igb_currentpl $dest[name] $l_igb_in $dest[sector_id] :</td>" .
       "<td align=right>" . NUMBER($dest['credits']) . " C</td>" .
       "<tr valign=top>" .
       "<td>$l_igb_transferamount :</td>" .
       "<td align=right>" . NUMBER($amount['total']) . " C</td>" .
       "<tr valign=top>" .
       "<td>$l_igb_transferfee :</td>" .
       "<td align=right>" . NUMBER($fee) . " C </td>" .
       "<tr valign=top>" .
       "<td>$l_igb_plaffected :</td>" .
       "<td align=right>" . NUMBER($amount['count']) . "</td>" .
       "<tr valign=top>" .
       "<td>$l_igb_turncost :</td>" .
       "<td align=right>" . NUMBER($tcost) . "</td>" .
       "<tr valign=top>" .
       "<td>$l_igb_amounttransferred :</td>" .
       "<td align=right>" . NUMBER($transfer) . " C</td>" .
       "<tr valign=top><td colspan=2 align=right>" .
       "<form action='igb.php?command=consolidate3' method=POST>" .
       "<input type=hidden name=minimum value=$minimum><br>" .
       "<input type=hidden name=maximum value=$maximum><br>" .
       "<input type=hidden name=dplanet_id value=$dplanet_id>" .
       "<input class=term type=submit value=\"$l_igb_consolidate\"></td>" .
       "</form>" .
       "<tr valign=bottom>" .
       "<td><a href='igb.php?command=transfer'>$l_igb_back</a></td><td align=right>&nbsp;<br><a href=\"main.php\">$l_igb_logout</a></td>" .
       "</tr>";
}

function IGB_consolidate3()
{
  global $playerinfo;
  global $db, $db_logging;
  global $dplanet_id, $minimum, $maximum, $IGB_tconsolidate, $ibank_paymentfee;
  global $l_igb_notenturns, $l_igb_back, $l_igb_logout, $l_igb_transfersuccessful;
  global $l_igb_currentpl, $l_igb_in, $l_igb_turncost, $l_igb_unnamed;

  $res = $db->Execute("SELECT name, credits, owner, sector_id FROM {$db->prefix}planets WHERE planet_id=$dplanet_id");
  db_op_result ($db, $res, __LINE__, __FILE__, $db_logging);
  if (!$res || $res->EOF)
      IGB_error($l_igb_errunknownplanet, "igb.php?command=transfer");
  $dest = $res->fields;

  if (empty($dest['name']))
    $dest['name']=$l_igb_unnamed;

  if ($dest['owner'] != $playerinfo['ship_id'])
    IGB_error($l_igb_errnotyourplanet, "igb.php?command=transfer");

  $minimum = StripNonNum($minimum);
  $maximum = StripNonNum($maximum);

  $query = "SELECT SUM(credits) as total, COUNT(*) AS count FROM {$db->prefix}planets WHERE owner=$playerinfo[ship_id] AND credits != 0";

  if ($minimum != 0)
    $query .= " AND credits >= $minimum";

  if ($maximum != 0)
    $query .= " AND credits <= $maximum";

  $query .= " AND planet_id != $dplanet_id";

  $res = $db->Execute($query);
  db_op_result ($db, $res, __LINE__, __FILE__, $db_logging);
  $amount = $res->fields;

  $fee = $ibank_paymentfee * $amount['total'];

  $tcost = ceil($amount['count'] / $IGB_tconsolidate);
  $transfer = $amount['total'] - $fee;

  $cplanet = $transfer + $dest['credits'];

  if ($tcost > $playerinfo['turns'])
    IGB_error($l_igb_notenturns, "igb.php?command=transfer");

  echo "<tr><td colspan=2 align=center valign=top>$l_igb_transfersuccessful<br>---------------------------------</td></tr>" .
       "<tr valign=top>" .
       "<td>$l_igb_currentpl $dest[name] $l_igb_in $dest[sector_id] :<br><br>" .
       "$l_igb_turncost :</td>" .
       "<td align=right>" . NUMBER($cplanet) . " C<br><br>" .
       NUMBER($tcost) . "</td>" .
       "<tr valign=bottom>" .
       "<td><a href='igb.php?command=login'>$l_igb_back</a></td><td align=right>&nbsp;<br><a href=\"main.php\">$l_igb_logout</a></td>" .
       "</tr>";

  $query = "UPDATE {$db->prefix}planets SET credits=0 WHERE owner=$playerinfo[ship_id] AND credits != 0";

  if ($minimum != 0)
    $query .= " AND credits >= $minimum";

  if ($maximum != 0)
    $query .= " AND credits <= $maximum";

  $query .= " AND planet_id != $dplanet_id";

  $res = $db->Execute($query);
  db_op_result ($db, $res, __LINE__, __FILE__, $db_logging);
  $res = $db->Execute("UPDATE {$db->prefix}planets SET credits=credits + $transfer WHERE planet_id=$dplanet_id");
  db_op_result ($db, $res, __LINE__, __FILE__, $db_logging);
  $res = $db->Execute("UPDATE {$db->prefix}ships SET turns=turns - $tcost WHERE ship_id = $playerinfo[ship_id]");
  db_op_result ($db, $res, __LINE__, __FILE__, $db_logging);
}

function IGB_error($errmsg, $backlink, $title="Error!")
{
  global $l_igb_igberrreport, $l_igb_back, $l_igb_logout;

  $title=$l_igb_igberrreport;
  echo "<tr><td colspan=2 align=center valign=top>$title<br>---------------------------------</td></tr>" .
       "<tr valign=top>" .
       "<td colspan=2 align=center>$errmsg</td>" .
       "</tr>" .
       "<tr valign=bottom>" .
       "<td><a href=$backlink>$l_igb_back</a></td><td align=right>&nbsp;<br><a href=\"main.php\">$l_igb_logout</a></td>" .
       "</tr>" .
       "</table>" .
       "</td></tr>" .
       "</table>" .
       "<img src=images/div2.png>" .
       "</center>";

  include "footer.php";
  die();
}

function StripNonNum($str)
{
  $str=(string)$str;
  $output = preg_replace("/[^0-9]/","",$str);

  return $output;
}
?>

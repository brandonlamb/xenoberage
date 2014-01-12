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
// File: includes/xenobetrade.php

function xenobetrade()
{
  //
  // SETUP GENERAL VARIABLES
  //
  global $playerinfo;
  global $inventory_factor;
  global $ore_price;
  global $ore_delta;
  global $ore_limit;
  global $goods_price;
  global $goods_delta;
  global $goods_limit;
  global $organics_price;
  global $organics_delta;
  global $organics_limit;
  global $xenobeisdead;
  global $db, $db_logging;
  // We need to get rid of this.. the bug causing it needs to be identified and squashed. In the meantime, we want functional xen's. :)
    $ore_price = 11;
    $organics_price = 5;
    $goods_price = 15;

  // OBTAIN SECTOR INFORMATION
  $sectres = $db->Execute ("SELECT * FROM {$db->prefix}universe WHERE sector_id='$playerinfo[sector]'");
  db_op_result ($db, $sectres, __LINE__, __FILE__, $db_logging);
  $sectorinfo = $sectres->fields;

  // OBTAIN ZONE INFORMATION
  $zoneres = $db->Execute ("SELECT zone_id,allow_attack,allow_trade FROM {$db->prefix}zones WHERE zone_id='$sectorinfo[zone_id]'");
  db_op_result ($db, $zoneres, __LINE__, __FILE__, $db_logging);
  $zonerow = $zoneres->fields;

  // Debug info
  //playerlog ($db, $playerinfo[ship_id], LOG_RAW, "PORT $sectorinfo[port_type] ALLOW_TRADE $zonerow[allow_trade] PORE $sectorinfo[port_ore] PORG $sectorinfo[port_organics] PGOO $sectorinfo[port_goods] ORE $playerinfo[ship_ore] ORG $playerinfo[ship_organics] GOO $playerinfo[ship_goods] CREDITS $playerinfo[credits] ");

  //
  //  MAKE SURE WE CAN TRADE HERE
  //
  if ($zonerow[allow_trade]=="N") return;

  //
  //  CHECK FOR A PORT WE CAN USE
  //
  if ($sectorinfo[port_type] == "none") return;
  // Xenobe DO NOT TRADE AT ENERGY PORTS SINCE THEY REGEN ENERGY
  if ($sectorinfo[port_type] == "energy") return;

  //
  //  CHECK FOR NEG CREDIT/CARGO
  //
  if ($playerinfo[ship_ore]<0) $playerinfo[ship_ore]=$shipore=0;
  if ($playerinfo[ship_organics]<0) $playerinfo[ship_organics]=$shiporganics=0;
  if ($playerinfo[ship_goods]<0) $playerinfo[ship_goods]=$shipgoods=0;
  if ($playerinfo[credits]<0) $playerinfo[credits]=$shipcredits=0;
  if ($sectorinfo[port_ore] <= 0) return;
  if ($sectorinfo[port_organics] <= 0) return;
  if ($sectorinfo[port_goods] <= 0) return;

  //
  //  CHECK Xenobe CREDIT/CARGO
  //
  if ($playerinfo[ship_ore]>0) $shipore=$playerinfo[ship_ore];
  if ($playerinfo[ship_organics]>0) $shiporganics=$playerinfo[ship_organics];
  if ($playerinfo[ship_goods]>0) $shipgoods=$playerinfo[ship_goods];
  if ($playerinfo[credits]>0) $shipcredits=$playerinfo[credits];
  // MAKE SURE WE HAVE CARGO OR CREDITS
  if (!$playerinfo[credits]>0 && !$playerinfo[ship_ore]>0 && !$playerinfo[ship_goods]>0 && !$playerinfo[ship_organics]>0) return;

  //
  //  MAKE SURE CARGOS COMPATABLE
  //
  if ($sectorinfo[port_type]=="ore" && $shipore>0) return;
  if ($sectorinfo[port_type]=="organics" && $shiporganics>0) return;
  if ($sectorinfo[port_type]=="goods" && $shipgoods>0) return;

  //
  // LETS TRADE SOME CARGO *
  //
  if ($sectorinfo[port_type]=="ore")
  //
  // PORT ORE
  //
  {
    // SET THE PRICES
    $ore_price = $ore_price - $ore_delta * $sectorinfo[port_ore] / $ore_limit * $inventory_factor;
    $organics_price = $organics_price + $organics_delta * $sectorinfo[port_organics] / $organics_limit * $inventory_factor;
    $goods_price = $goods_price + $goods_delta * $sectorinfo[port_goods] / $goods_limit * $inventory_factor;
    //  SET CARGO BUY/SELL
    $amount_organics = $playerinfo[ship_organics];
    $amount_goods = $playerinfo[ship_goods];
    // SINCE WE SELL ALL OTHER HOLDS WE SET AMOUNT TO BE OUR TOTAL HOLD LIMIT
    $amount_ore = NUM_HOLDS($playerinfo[hull]);
    // WE ADJUST THIS TO MAKE SURE IT DOES NOT EXCEED WHAT THE PORT HAS TO SELL
    $amount_ore = min($amount_ore, $sectorinfo[port_ore]);
    // WE ADJUST THIS TO MAKE SURE IT DOES NOT EXCEES WHAT WE CAN AFFORD TO BUY
    $amount_ore = min($amount_ore, floor(($playerinfo[credits] + $amount_organics * $organics_price + $amount_goods * $goods_price) / $ore_price));
    // BUY/SELL CARGO
    $total_cost = round(($amount_ore * $ore_price) - ($amount_organics * $organics_price + $amount_goods * $goods_price));
    $newcredits = max(0,$playerinfo[credits]-$total_cost);
    $newore = $playerinfo[ship_ore]+$amount_ore;
    $neworganics = max(0,$playerinfo[ship_organics]-$amount_organics);
    $newgoods = max(0,$playerinfo[ship_goods]-$amount_goods);
    $trade_result = $db->Execute("UPDATE {$db->prefix}ships SET rating=rating+1, credits=$newcredits, ship_ore=$newore, ship_organics=$neworganics, ship_goods=$newgoods WHERE ship_id=$playerinfo[ship_id]");
    db_op_result ($db, $trade_result, __LINE__, __FILE__, $db_logging);
    $trade_result2 = $db->Execute("UPDATE {$db->prefix}universe SET port_ore=port_ore-$amount_ore, port_organics=port_organics+$amount_organics, port_goods=port_goods+$amount_goods WHERE sector_id=$sectorinfo[sector_id]");
    db_op_result ($db, $trade_result2, __LINE__, __FILE__, $db_logging);
    playerlog ($db, $playerinfo[ship_id], LOG_RAW, "Xenobe Trade Results: Sold $amount_organics Organics Sold $amount_goods Goods Bought $amount_ore Ore Cost $total_cost");
  }
  if ($sectorinfo[port_type]=="organics")
  //
  // PORT ORGANICS
  //
  {
    // SET THE PRICES
    $organics_price = $organics_price - $organics_delta * $sectorinfo[port_organics] / $organics_limit * $inventory_factor;
    $ore_price = $ore_price + $ore_delta * $sectorinfo[port_ore] / $ore_limit * $inventory_factor;
    $goods_price = $goods_price + $goods_delta * $sectorinfo[port_goods] / $goods_limit * $inventory_factor;
    //
    //  SET CARGO BUY/SELL
    //
    $amount_ore = $playerinfo[ship_ore];
    $amount_goods = $playerinfo[ship_goods];
    // SINCE WE SELL ALL OTHER HOLDS WE SET AMOUNT TO BE OUR TOTAL HOLD LIMIT
    $amount_organics = NUM_HOLDS($playerinfo[hull]);
    // WE ADJUST THIS TO MAKE SURE IT DOES NOT EXCEED WHAT THE PORT HAS TO SELL
    $amount_organics = min($amount_organics, $sectorinfo[port_organics]);
    // WE ADJUST THIS TO MAKE SURE IT DOES NOT EXCEES WHAT WE CAN AFFORD TO BUY
    $amount_organics = min($amount_organics, floor(($playerinfo[credits] + $amount_ore * $ore_price + $amount_goods * $goods_price) / $organics_price));
    //
    // BUY/SELL CARGO
    //
    $total_cost = round(($amount_organics * $organics_price) - ($amount_ore * $ore_price + $amount_goods * $goods_price));
    $newcredits = max(0,$playerinfo[credits]-$total_cost);
    $newore = max(0,$playerinfo[ship_ore]-$amount_ore);
    $neworganics = $playerinfo[ship_organics]+$amount_organics;
    $newgoods = max(0,$playerinfo[ship_goods]-$amount_goods);
    $trade_result = $db->Execute("UPDATE {$db->prefix}ships SET rating=rating+1, credits=$newcredits, ship_ore=$newore, ship_organics=$neworganics, ship_goods=$newgoods WHERE ship_id=$playerinfo[ship_id]");
    db_op_result ($db, $trade_result, __LINE__, __FILE__, $db_logging);
    $trade_result2 = $db->Execute("UPDATE {$db->prefix}universe SET port_ore=port_ore+$amount_ore, port_organics=port_organics-$amount_organics, port_goods=port_goods+$amount_goods WHERE sector_id=$sectorinfo[sector_id]");
    db_op_result ($db, $trade_result2, __LINE__, __FILE__, $db_logging);
    playerlog ($db, $playerinfo[ship_id], LOG_RAW, "Xenobe Trade Results: Sold $amount_goods Goods Sold $amount_ore Ore Bought $amount_organics Organics Cost $total_cost");
  }
  if ($sectorinfo[port_type]=="goods")
  //
  // PORT GOODS *
  //
  {
    //
    // SET THE PRICES
    //
    $goods_price = $goods_price - $goods_delta * $sectorinfo[port_goods] / $goods_limit * $inventory_factor;
    $ore_price = $ore_price + $ore_delta * $sectorinfo[port_ore] / $ore_limit * $inventory_factor;
    $organics_price = $organics_price + $organics_delta * $sectorinfo[port_organics] / $organics_limit * $inventory_factor;
    //
    //  SET CARGO BUY/SELL
    //
    $amount_ore = $playerinfo[ship_ore];
    $amount_organics = $playerinfo[ship_organics];
    // SINCE WE SELL ALL OTHER HOLDS WE SET AMOUNT TO BE OUR TOTAL HOLD LIMIT
    $amount_goods = NUM_HOLDS($playerinfo[hull]);
    // WE ADJUST THIS TO MAKE SURE IT DOES NOT EXCEED WHAT THE PORT HAS TO SELL
    $amount_goods = min($amount_goods, $sectorinfo[port_goods]);
    // WE ADJUST THIS TO MAKE SURE IT DOES NOT EXCEES WHAT WE CAN AFFORD TO BUY
    $amount_goods = min($amount_goods, floor(($playerinfo[credits] + $amount_ore * $ore_price + $amount_organics * $organics_price) / $goods_price));
    //
    // BUY/SELL CARGO
    //
    $total_cost = round(($amount_goods * $goods_price) - ($amount_organics * $organics_price + $amount_ore * $ore_price));
    $newcredits = max(0,$playerinfo[credits]-$total_cost);
    $newore = max(0,$playerinfo[ship_ore]-$amount_ore);
    $neworganics = max(0,$playerinfo[ship_organics]-$amount_organics);
    $newgoods = $playerinfo[ship_goods]+$amount_goods;
    $trade_result = $db->Execute("UPDATE {$db->prefix}ships SET rating=rating+1, credits=$newcredits, ship_ore=$newore, ship_organics=$neworganics, ship_goods=$newgoods WHERE ship_id=$playerinfo[ship_id]");
    db_op_result ($db, $trade_result, __LINE__, __FILE__, $db_logging);
    $trade_result2 = $db->Execute("UPDATE {$db->prefix}universe SET port_ore=port_ore+$amount_ore, port_organics=port_organics+$amount_organics, port_goods=port_goods-$amount_goods WHERE sector_id=$sectorinfo[sector_id]");
    db_op_result ($db, $trade_result2, __LINE__, __FILE__, $db_logging);
    playerlog ($db, $playerinfo[ship_id], LOG_RAW, "Xenobe Trade Results: Sold $amount_ore Ore Sold $amount_organics Organics Bought $amount_goods Goods Cost $total_cost");
  }

}
?>

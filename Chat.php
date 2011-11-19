<?php

/*
* Date: 20-09-2004
*
* A Simpel Chat Bot Script That I want to make more complex later on
* It utilizes sockets to connect and parse commands
*
*/
#:RaNDoM!random@blabber-506C62DC.telkomadsl.co.za PRIVMSG #asylum ::D

include "functions/function.php";

Global $server;
Global $port;
Global $channel;
Global $botnick;
Global $master;
Global $fp;

$server = "irc.server.net";
$port = "6667";
$channel = "#channel";
$botnick = "botnick";
$master = "masternick";
fork();

if (!$fp) {
  $fp = connect($server,$port,$channel,$botnick,$master);
  main_bot($fp,$master,$channel,$botnick);
} else { 
  echo "Connected";
  main_bot($fp,$master,$channel,$botnick);
}




?>



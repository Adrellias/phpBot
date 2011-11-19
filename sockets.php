<?php
error_reporting(E_ALL);
set_time_limit(0);
ob_implicit_flush();
$address = '127.0.0.1';
$port = 8888;

function handle_client($allclient, $socket, $buf, $bytes) {
    foreach($allclient as $client) {
        socket_write($client, "$socket wrote: $buf");
    }
}

if (($master = socket_create(AF_INET, SOCK_STREAM, SOL_TCP)) < 0) {
    echo "socket_create() failed: reason: " . socket_strerror($master) . "\n";
}

socket_set_option($master, SOL_SOCKET,SO_REUSEADDR, 1);

if (($ret = socket_bind($master, $address, $port)) < 0) {
    echo "socket_bind() failed: reason: " . socket_strerror($ret) . "\n";
}

if (($ret = socket_listen($master, 5)) < 0) {
    echo "socket_listen() failed: reason: " . socket_strerror($ret) . "\n";
}

$read_sockets = array($master);

while (true) {
    $changed_sockets = $read_sockets;
    $num_changed_sockets = socket_select($changed_sockets, $write = NULL, $except = NULL, NULL);
    foreach($changed_sockets as $socket) {
        if ($socket == $master) {
            if (($client = socket_accept($master)) < 0) {
                echo "socket_accept() failed: reason: " . socket_strerror($msgsock) . "\n";
                continue;
            } else {
                array_push($read_sockets, $client);
            }
        } else {
            $bytes = socket_recv($socket, $buffer, 2048, 0);
            if ($bytes == 0) {
                $index = array_search($socket, $read_sockets);
                unset($read_sockets[$index]);
                socket_close($socket);
            } else {
                $allclients = $read_sockets;
                array_shift($allclients);    // remove master
                handle_client($allclients, $socket, $buffer, $bytes);
            }
        }
       
    }
}

?>

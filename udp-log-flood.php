#!/usr/bin/env php
<?php

// Creating socket
$socket = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);

// Initializing increment variable
$i = 0;
while (true) {
    if (++$i % 100000 === 0) {
        // Outputting each 100000-th iteration
        echo $i, PHP_EOL;
    }
    
    // Dummy token
    $message = "token\n";
    // Logger with 5 different random keys
    $message .= "log://logger.test@cli:super:" . mt_rand(1, 5) ."\n";
    // Log message
    $message .= "This is test message #{$i} value " . mt_rand(1, 10000000000);

    // Sending to default EOS port
    socket_sendto($socket, $message, strlen($message), 0, '127.0.0.1', 8087);
}


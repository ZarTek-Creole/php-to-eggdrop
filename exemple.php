<?php
// Create a new EggdropController object
$eggdrop = new EggdropController('localhost', 3333, 'myusername', 'mypassword', '+chan #mychannel');

// Send a command to the eggdrop
$eggdrop->sendCommand();


// Create a new EggdropController object
$eggdrop = new EggdropController('localhost', 3333, 'myusername', 'mypassword', '#mychannel', 'Hello, world!');

// Send a message to the eggdrop
$eggdrop->sendMessage();

$eggdrop = new EggdropController('hostname', 12345, 'username', 'password', '#channel', '');
$eggdrop->joinChannel();
$eggdrop->closeConnection();


// Close the connection to the eggdrop
$eggdrop->closeConnection();
<?php

// Fill array with all possible target url's
$ads = array();
$ads[] = 'http://www.pornshanty.com/4103/DoubletheTrouble-15.html';
$ads[] = 'http://www.humorninja.com/friends.php';
$ads[] = 'http://www.humorninja.com/friends.php';






// Select one url randomly
$random = array_rand($ads);

// Redirect initial request to target url
header("Location: {$ads[$random]}");

?>




<?php
$raw = $_SERVER['argv'][1];
$encypt = password_hash($raw, PASSWORD_BCRYPT);
echo "$encypt\n";
?>

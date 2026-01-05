<?php 

declare(strict_types=1);

$databaseLocation = "sqlite:".__DIR__.'/database.db';
$database = new PDO($databaseLocation);

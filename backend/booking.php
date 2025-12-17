<?php


$databaseLocation = "sqlite:".__DIR__.'/database.db';
$database = new PDO($databaseLocation);

if (isset($_POST['nameInput'],$_POST['roomInput'])) {
    $nameInput = trim($_POST['nameInput']);
    $codeInput = trim($_POST['codeInput']);
    $roomInput = trim($_POST['roomInput']);
    $arrivalInput = trim($_POST['arrivalInput']);
    $departureInput = trim($_POST['departureInput']);
    $checkbox = trim($_POST['checkbox']);

    $query = 'INSERT INTO guests (name) VALUES (:nameInput)';

    $statement = $database->prepare($query);

    $statement->bindParam(':nameInput', $nameInput, PDO::PARAM_STR);
   //  $statement->bindParam(':codeInput', $codeInput, PDO::PARAM_STR);

   /* $statement->bindParam(':arrivalInput', $arrivalInput, PDO::PARAM_STR);
    $statement->bindParam(':departureInput', $departureInput, PDO::PARAM_STR);
    $statement->bindParam(':checkbox', $checkbox, PDO::PARAM_INT); */



    $statement->execute();
    
    $last_user_id = $database->lastInsertId();
    echo "New record created successfully. Last inserted ID is: " . $last_user_id;

    var_dump($statement);
// ---------------------------------------------------

    $query = 'INSERT INTO rooms (class) VALUES (:roomInput)';

    $statement = $database->prepare($query);

    $statement->bindParam(':roomInput', $roomInput, PDO::PARAM_STR);

    $statement->execute();

//--------------------------------------------------------
    
    $query = 'INSERT INTO visits (guest_id, arrival, departure) VALUES (:guest_id, :arrivalInput, :departureInput)';

    $statement = $database->prepare($query);

    $statement->bindParam(':guest_id', $last_user_id, PDO::PARAM_INT);

    $statement->bindParam(':arrivalInput', $arrivalInput, PDO::PARAM_STR);

    $statement->bindParam(':departureInput', $departureInput, PDO::PARAM_STR);

    $statement->execute();

}

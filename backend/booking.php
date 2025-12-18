<?php
declare(strict_types=1);

$databaseLocation = "sqlite:".__DIR__.'/database.db';
$database = new PDO($databaseLocation);

if (isset($_POST['nameInput'],$_POST['roomInput'],$_POST['arrivalInput'],$_POST['departureInput'], $_POST['checkbox'])) {
    $nameInput = trim($_POST['nameInput']);
    $codeInput = trim($_POST['codeInput']);
    $roomInput = trim($_POST['roomInput']);
    $arrivalInput = trim($_POST['arrivalInput']);
    $departureInput = trim($_POST['departureInput']);

    $checkboxes = ($_POST['checkbox']);
  /*  foreach($checkboxes as &$checkbox){
        $checkbox = trim($checkbox);
    } */

   // $checkbox = trim($_POST['checkbox']);

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

    /* $query = 'SELECT id FROM rooms WHERE class IS (:roomInput)';

    $statement = $database->prepare($query);

    $statement->bindParam(':roomInput', $roomInput, PDO::PARAM_STR);

    $statement->execute();

    $last_room_id = $database->lastSelectedId(); */

//--------------------------------------------------------
    
    $query = 'INSERT INTO visits (guest_id, room_id, arrival, departure) VALUES (:guest_id, :room_id, :arrivalInput, :departureInput)';

    $statement = $database->prepare($query);

    $statement->bindParam(':guest_id', $last_user_id, PDO::PARAM_INT);

    $statement->bindParam(':room_id', $roomInput, PDO::PARAM_INT);

    $statement->bindParam(':arrivalInput', $arrivalInput, PDO::PARAM_STR);

    $statement->bindParam(':departureInput', $departureInput, PDO::PARAM_STR);

    $statement->execute();
    
    $last_visit_id = $database->lastInsertId();
    echo "New record created successfully. Last inserted ID is: " . $last_visit_id;

//----------------------------------------------------------------

    var_dump($checkboxes);

    foreach($checkboxes as $checkbox){

        $query = 'INSERT INTO visit_activities (visit_id, activity_id) VALUES (:visit_id, :activity_id)'; 

        $statement = $database->prepare($query);

        $statement->bindParam(':visit_id', $last_visit_id, PDO::PARAM_INT);

        $statement->bindParam(':activity_id', $checkbox, PDO::PARAM_INT);

        //TODO: Gör checkbox till array.

        $statement->execute();
    };



}
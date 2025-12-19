<?php

require(__DIR__ . '/../vendor/autoload.php');

use GuzzleHttp\Client;




$databaseLocation = "sqlite:".__DIR__.'/database.db';
$database = new PDO($databaseLocation);

if (isset($_POST['nameInput'],$_POST['roomInput'],$_POST['arrivalInput'],$_POST['departureInput'], $_POST['checkbox'])) {
    $nameInput = trim($_POST['nameInput']);
    $codeInput = trim($_POST['codeInput']);
    $roomInput = trim($_POST['roomInput']);
    $arrivalInput = trim($_POST['arrivalInput']);
    $departureInput = trim($_POST['departureInput']);

    $checkboxes = ($_POST['checkbox']);

    if(empty($codeInput)){
        echo 'No code :(';
    } else {

        // -----------------------------------------------------

        $query = 'INSERT INTO guests (name) VALUES (:nameInput)';

        $statement = $database->prepare($query);

        $statement->bindParam(':nameInput', $nameInput, PDO::PARAM_STR);

        $statement->execute();

        $last_user_id = $database->lastInsertId();
        echo "New record created successfully. Last inserted ID is: " . $last_user_id;

        var_dump($statement);

        // ---------------------------------------------------

        //TODO: Calculate cost: Room Price X Days + Activities
        
        //TODO: Check transferCode
        $client = new Client([
            'base_uri' => 'https://www.yrgopelag.se/centralbank/transferCode',
        ]);

        $r = $client->request('POST', 'https://www.yrgopelag.se/centralbank/transferCode', [
            'form_params' => [
                'transferCode' => $codeInput, 
                'totalCost' => '5'
                ]
        ]);

        var_dump($r->getBody()->getContents()); 
    
        //------------------------------------------------------------

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

            $statement->execute();
        };


    }
}
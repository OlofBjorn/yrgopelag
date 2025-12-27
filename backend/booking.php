<?php

declare(strict_types=1);

require __DIR__ . '/functions.php';

require(__DIR__ . '/../vendor/autoload.php');

use GuzzleHttp\Client;




$databaseLocation = "sqlite:".__DIR__.'/database.db';
$database = new PDO($databaseLocation);
// CODE ONLY CHECKS IF SET, NOT IF EMPTY STRING
//TODO: MAKE SURE ARRIVAL AND DEPARTURE HAVE AN ACTUAL VALUE AND THAT ARRIVAL MUST BE SMALLER THAN DEPARTURE
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

        //PERHAPS CHECK ARRIVAL AND DEPATURE HERE?

        if($arrivalInput>$departureInput){
            echo 'No time traveling!';
        }else{

            // ---------------------------------------------------

            //TODO: Calculate cost: Room Price X Days + Activities
            
            //TODO: Check transferCode
            $client = new Client([
                'base_uri' => 'https://www.yrgopelag.se/centralbank/transferCode',
            ]);

            try {
                $response = $client->request('POST', 'https://www.yrgopelag.se/centralbank/transferCode', [
                'form_params' => [
                    'transferCode' => $codeInput,
                    'totalCost' => 5
                ]
            ]);

                $body = json_decode($response->getBody()->getContents(), true);
                var_dump($body);

            } catch (\GuzzleHttp\Exception\ClientException $codeError) {
                //Rejected code
                $errorBody = json_decode($codeError->getResponse()->getBody()->getContents(), true);
                //echo $errorBody['error'] ?? 'Invalid transferCode >:(';
                exit;
            }

            /*
            catch (\Exception $codeError){
                $response = "Error, invalid or used code";
                echo $response;
            }

            //TODO: FIX GETBODY ERROR ON INVALID CODE
            var_dump($response->getBody()->getContents()); */


            try {
                $totalCost = calculateTotalPrice(
                    (int) $roomInput,
                    $arrivalInput,
                    $departureInput,
                    $checkboxes ?? []
                );
            } catch (Exception $e) {
                echo $e->getMessage();
                exit;
            }

            //'totalCost' => $totalCost

            // -----------------------------------------------------

            $query = 'INSERT INTO guests (name) VALUES (:nameInput)';

            $statement = $database->prepare($query);

            $statement->bindParam(':nameInput', $nameInput, PDO::PARAM_STR);

            $statement->execute();

            $last_user_id = $database->lastInsertId();
            echo "New record created successfully. Last inserted ID is: " . $last_user_id;

            var_dump($statement);
        
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

            //ENSURING TOTAL COST IS WORKING
            echo "total cost is $totalCost";
        }
    }
}
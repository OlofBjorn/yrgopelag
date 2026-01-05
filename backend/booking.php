<?php

declare(strict_types=1);

require(__DIR__ . '/../vendor/autoload.php');
require __DIR__ . '/functions.php';

use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

//var_dump($_ENV['API_KEY']);
//exit;

use GuzzleHttp\Client;

/*var_dump($_POST);

if (isset($_POST['nameInput'])) {
    var_dump($_POST['nameInput']);  // Print raw POST value
} else {
    echo 'nameInput is not set!';
}

error_log('Received nameInput: ' . $_POST['nameInput']);*/

// CODE ONLY CHECKS IF SET, NOT IF EMPTY STRING
//TODO: MAKE SURE ARRIVAL AND DEPARTURE HAVE AN ACTUAL VALUE AND THAT ARRIVAL MUST BE SMALLER THAN DEPARTURE
if (isset($_POST['nameInput'],$_POST['roomInput'],$_POST['arrivalInput'],$_POST['departureInput'],$_POST['activities'])) {
    //var_dump($_POST['nameInput']);
    //$nameInput = trim(htmlspecialchars($_POST['nameInput']));
    //$nameInput = trim(htmlspecialchars($_POST['nameInput'], ENT_QUOTES, 'UTF-8'));
    //var_dump($nameInput);
    $nameInput = trim($_POST['nameInput']);
    $codeInput = trim($_POST['codeInput']);
    $roomInput = trim($_POST['roomInput']);
    $arrivalInput = trim($_POST['arrivalInput']);
    $departureInput = trim($_POST['departureInput']);

    $activities = array_map(
        'intval',
        $_POST['activities'] ?? []
    );

    //echo htmlspecialchars($nameInput, ENT_QUOTES, 'UTF-8');

//    echo trim(htmlspecialchars($_POST['nameInput']));

    if(empty($codeInput)){
        echo 'Error: No transferCode.';
    } else {

        //PERHAPS CHECK ARRIVAL AND DEPATURE HERE?

        if($arrivalInput>$departureInput){
            echo 'Error: Time travel without licence detected';
        }else{

            if (!isRoomAvailable(
                $database,
                (int)$roomInput,
                $arrivalInput,
                $departureInput
            )) {
                echo "Error: Room preoccupied for selected date.";
                exit;
            }

            if(empty($nameInput)){
                echo "Error: No name.";
            }
            else{

            


            // ---------------------------------------------------

            //TODO: Calculate cost: Room Price X Days + Activities
            
            

            /*
            catch (\Exception $codeError){
                $response = "Error, invalid or used code";
                echo $response;
            }

            //TODO: FIX GETBODY ERROR ON INVALID CODE
            var_dump($response->getBody()->getContents()); */

            //if(isset($_POST['checkbox'])){
/*
                $activities = array_map(function($value) {
                return trim($value, '/');  // Removes any leading or trailing slashes
            }, $_POST['activity']);
*/
            //}
            //else{
            //    $checkboxes = 1;
            //}

            try {
                $totalCost = calculateTotalPrice(
                    $database,
                    (int) $roomInput,
                    $arrivalInput,
                    $departureInput,
                    $activities ?? []
                );
            } catch (Exception $e) {
                echo htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
                exit;
            }

            echo "price registered";

            //TODO: Check transferCode
            $client = new Client([
                'base_uri' => 'https://www.yrgopelag.se/centralbank/transferCode',
            ]);

            try {
                $response = $client->request('POST', 'https://www.yrgopelag.se/centralbank/transferCode', [
                'form_params' => [
                    'transferCode' => $codeInput,
                    'totalCost' => $totalCost
                ]
            ]);

                $body = json_decode($response->getBody()->getContents(), true);
                var_dump($body);

            } catch (\GuzzleHttp\Exception\ClientException $codeError) {
                //Rejected code
                $errorBody = json_decode($codeError->getResponse()->getBody()->getContents(), true);
                echo $errorBody['error'] ?? 'Invalid transferCode >:(';
                exit;
            }

            echo "code validated";

            // --------------------------------------------------


            //echo $_ENV['API_KEY'];
            echo "prereceipt";

            $receiptClient = new Client([
                'base_uri' => 'https://www.yrgopelag.se/centralbank/',
                'headers' => [
                    'Content-Type' => 'application/json'
                ]
            ]);

            try {
                $receiptResponse = $receiptClient->request('POST', 'receipt', [
                    'json' => [
                        'user' => 'Olof',
                        'api_key' => $_ENV['API_KEY'],
                        'guest_name' => $nameInput,
                        'arrival_date' => $arrivalInput,
                        'departure_date' => $departureInput,
                        /*'features_used' => array_map(
                            fn ($activityId) => mapActivityIdToReceiptFormat((int)$activityId),
                            $activities
                        ),*/
                        'features_used' => getActivitiesForReceipt($database, $activities),
                        'star_rating' => 1
                    ]
                ]);
            } catch (\GuzzleHttp\Exception\ClientException $e) {
                $errorBody = json_decode($e->getResponse()->getBody()->getContents(), true);
                echo $errorBody['error'] ?? 'Receipt request failed.';
                exit;
            }

            $receiptBody = json_decode($receiptResponse->getBody()->getContents(), true);

            if (($receiptBody['status'] ?? null) !== 'success') {
                echo 'Error: Receipt rejected.';
                exit;
            }

            var_dump($receiptBody);
            //exit;

            //'totalCost' => $totalCost

            // -----------------------------------------------------

            var_dump(htmlspecialchars($nameInput, ENT_QUOTES, 'UTF-8'));  // See if htmlspecialchars works here

            $query = 'INSERT INTO guests (name) VALUES (:nameInput)';

            $statement = $database->prepare($query);

            $statement->bindParam(':nameInput', $nameInput, PDO::PARAM_STR);

            $statement->execute();

            $last_user_id = $database->lastInsertId();

            echo "Name registered";
            //echo "New record created successfully. Last inserted ID is: " . $last_user_id;

        
            //------------------------------------------------------------

            $query = 'INSERT INTO visits (guest_id, room_id, arrival, departure) VALUES (:guest_id, :room_id, :arrivalInput, :departureInput)';

            $statement = $database->prepare($query);

            $statement->bindParam(':guest_id', $last_user_id, PDO::PARAM_INT);

            $statement->bindParam(':room_id', $roomInput, PDO::PARAM_INT);

            $statement->bindParam(':arrivalInput', $arrivalInput, PDO::PARAM_STR);

            $statement->bindParam(':departureInput', $departureInput, PDO::PARAM_STR);

            $statement->execute();
            
            $last_visit_id = $database->lastInsertId();

            echo "visit registered";
            //echo "New record created successfully. Last inserted ID is: " . $last_visit_id;

            //----------------------------------------------------------------

            //var_dump($checkboxes);

            foreach($activities as $activity){

                $query = 'INSERT INTO visit_activities (visit_id, activity_id) VALUES (:visit_id, :activity_id)'; 

                $statement = $database->prepare($query);

                $statement->bindParam(':visit_id', $last_visit_id, PDO::PARAM_INT);

                $statement->bindParam(':activity_id', $activity, PDO::PARAM_INT);

                $statement->execute();

                echo "activity registered";
            };

            //ENSURING TOTAL COST IS WORKING
            echo "Success!";

            echo "total cost is $totalCost";


            //htmlspecialchars is not working, must determine cause

            //var_dump(htmlspecialchars("BOOKING COMPLETE! Customer name = $nameInput"));

            //echo htmlspecialchars("BOOKING COMPLETE! Customer name = $nameInput", ENT_QUOTES, 'UTF-8');

            echo "BOOKING COMPLETE! Customer name = " .
                htmlspecialchars($nameInput, ENT_QUOTES, 'UTF-8');
            //echo htmlspecialchars("BOOKING COMPLETE! Customer name = " . $nameInput, ENT_QUOTES, 'UTF-8');
            //echo htmlspecialchars("BOOKING COMPLETE! Customer name = $nameInput", ENT_QUOTES, 'UTF-8');

            //echo htmlspecialchars($nameInput, ENT_QUOTES, 'UTF-8');

            //echo "Hang on";

            //$testInput = "<script>alert('XSS')</script>";
            //echo htmlspecialchars($testInput, ENT_QUOTES, 'UTF-8');

            //echo htmlspecialchars("BOOKING COMPLETE! Customer name = $nameInput", ENT_QUOTES, 'UTF-8');

            //echo ini_get('default_charset');

            //$testingInput = "<script>alert('XSS')</script>";

            //echo "Raw Input: " . $testingInput . "<br>"; // Raw input for comparison
            //echo "After htmlspecialchars: " . htmlspecialchars($testingInput, ENT_QUOTES, 'UTF-8') . "<br>";

            //---------------------------------------------------

            $depositResponse = $client->request('POST', 'https://www.yrgopelag.se/centralbank/deposit', [
                'form_params' => [
                    'user' => 'Olof',
                    'transferCode' => $codeInput
                ]
            ]);

            $depositBody = json_decode($depositResponse->getBody()->getContents(), true);

            if (($depositBody['status'] ?? null) !== 'success') {
                echo 'Error: Payment failed.';
                exit;
            }

            }
        }
    }
}
else{
    echo "You need to fill the form completely.";
}
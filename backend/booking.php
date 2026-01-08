<?php

declare(strict_types=1);

require(__DIR__ . '/../vendor/autoload.php');
require __DIR__ . '/functions.php';
require_once(__DIR__ . '/../assets/header.php');

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

$errorMessage = '';
$success = false;

// CODE ONLY CHECKS IF SET, NOT IF EMPTY STRING
//TODO: MAKE SURE ARRIVAL AND DEPARTURE HAVE AN ACTUAL VALUE AND THAT ARRIVAL MUST BE SMALLER THAN DEPARTURE
if (isset($_POST['nameInput'],$_POST['roomInput'],$_POST['arrivalInput'],$_POST['departureInput'])) {
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

    do{

        

        //echo htmlspecialchars($nameInput, ENT_QUOTES, 'UTF-8');

        //    echo trim(htmlspecialchars($_POST['nameInput']));

        if(empty($codeInput)){
                $errorMessage = "No transferCode.";
                $success = false;
                break;
        } else {

            //PERHAPS CHECK ARRIVAL AND DEPATURE HERE?

            if($arrivalInput>$departureInput){
                $errorMessage = "Illegal time travel.";
                $success = false;
                break;
            }else{

                if (!isRoomAvailable(
                    $database,
                    (int)$roomInput,
                    $arrivalInput,
                    $departureInput
                )) {
                    $errorMessage = "Room preoccupied.";
                    $success = false;
                    break;
                }

                if(empty($nameInput)){
                    $errorMessage = "Error: No name.";
                    $success = false;
                    break;
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
                    //echo htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
                    $errorMessage = $e->getMessage();
                    $success = false;
                    break;
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
                    //var_dump($body);

                } catch (\GuzzleHttp\Exception\ClientException $codeError) {
                    //Rejected code
                    $errorBody = json_decode($codeError->getResponse()->getBody()->getContents(), true);
                    //echo $errorBody['error'] ?? 'Invalid transferCode >:(';
                    $errorMessage = $errorBody['error'] ?? 'Invalid transferCode >:(';
                    $success = false;
                    break;
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
                    //echo $errorBody['error'] ?? 'Receipt request failed.';
                    $errorMessage = $errorBody['error'] ?? 'Receipt request failed.';
                    $success = false;
                    break;
                }

                $receiptBody = json_decode($receiptResponse->getBody()->getContents(), true);

                if (($receiptBody['status'] ?? null) !== 'success') {
                    $errorMessage = "Receipt rejected";
                    $success = false;
                    break;
                }

                //var_dump($receiptBody);
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
                    $errorMessage = "Payment failed";
                    $success = false;
                    exit;
                }

                $statement = $database->prepare("SELECT class FROM rooms WHERE id = ?");
                $statement->execute([$roomInput]);
                $roomName = $statement->fetchColumn();
                
                $bookingSummary = [
                    'guest_name' => $nameInput,
                    'room_id' => (int)$roomInput,
                    'arrival' => $arrivalInput,
                    'departure' => $departureInput,
                    'nights' => calculateNights($arrivalInput, $departureInput),
                    'activities' => $activities,
                    'total_cost' => $totalCost,
                    'transfer_code' => $codeInput
                ];

                $success = true;

                }
            }
        }
    } while(false);
}
else{
    $errorMessage = 'Incomplete form';
    $success = false;
}

?>
<div class="bookingHtml">
    <section class="bookingConfirmation">
        <?php if ($success): ?>
                <div class="bookingInfo">
                <h2>Booking Confirmed</h2>

                <p><strong>Guest:</strong> <?= htmlspecialchars($nameInput) ?></p>
                <p><strong>Room:</strong> <?= htmlspecialchars($roomName) ?></p>
                <p><strong>Arrival:</strong> <?= htmlspecialchars($arrivalInput) ?></p>
                <p><strong>Departure:</strong> <?= htmlspecialchars($departureInput) ?></p>
                <p><strong>Nights:</strong> <?= calculateNights($arrivalInput, $departureInput) ?></p>

                <h3>Activities</h3>
                <?php if (empty($activities)): ?>
                    <p>No activities selected.</p>
                <?php else: ?>
                    <ul>
                        <?php
                        $activityStatement = $database->prepare(
                            "SELECT name FROM activities WHERE id IN (" .
                            implode(',', array_fill(0, count($activities), '?')) . ")"
                        );
                        $activityStatement->execute($activities);
                        foreach ($activityStatement->fetchAll(PDO::FETCH_COLUMN) as $activityName):
                        ?>
                            <li><?= htmlspecialchars($activityName) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <p><strong>Total cost:</strong> $<?= number_format($totalCost, 2) ?></p>
            <h3>Receipt</h3>

            <p><strong>Reciept ID:</strong> <?= $receiptBody['receipt_id']; ?></p> 
            <p><strong>Island ID:</strong> <?= $receiptBody['island_id']; ?></p> 
        <?php else: ?>
            <div class="bookingInfo">
                <h2>Booking Failed</h2>
                <p><strong>Error:</strong> <?= htmlspecialchars($errorMessage) ?></p>
            </div>
        <?php endif; ?>
    </section>
</div>
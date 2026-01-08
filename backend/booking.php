<?php

declare(strict_types=1);

require(__DIR__ . '/../vendor/autoload.php');
require __DIR__ . '/functions.php';
require_once(__DIR__ . '/../assets/header.php');

use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

use GuzzleHttp\Client;

$errorMessage = '';
$success = false;

if (isset($_POST['nameInput'],$_POST['roomInput'],$_POST['arrivalInput'],$_POST['departureInput'])) {
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

        if(empty($codeInput)){
                $errorMessage = "No transferCode.";
                $success = false;
                break;
        } else {


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
                }else{
  
                    // ---------------------------------------------------

                    try {
                        $totalCost = calculateTotalPrice(
                            $database,
                            (int) $roomInput,
                            $arrivalInput,
                            $departureInput,
                            $activities ?? []
                        );
                    } catch (Exception $e) {
                        $errorMessage = $e->getMessage();
                        $success = false;
                        break;
                    }

                    //---------------------------------------------------------------

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

                    } catch (\GuzzleHttp\Exception\ClientException $codeError) {
                        $errorBody = json_decode($codeError->getResponse()->getBody()->getContents(), true);
                        $errorMessage = $errorBody['error'] ?? 'Invalid transferCode >:(';
                        $success = false;
                        break;
                    }

                    // --------------------------------------------------

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
                                'features_used' => getActivitiesForReceipt($database, $activities),
                                'star_rating' => 1
                            ]
                        ]);
                    } catch (\GuzzleHttp\Exception\ClientException $e) {
                        $errorBody = json_decode($e->getResponse()->getBody()->getContents(), true);
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

                    // -----------------------------------------------------

                    $query = 'INSERT INTO guests (name) VALUES (:nameInput)';

                    $statement = $database->prepare($query);

                    $statement->bindParam(':nameInput', $nameInput, PDO::PARAM_STR);

                    $statement->execute();

                    $last_user_id = $database->lastInsertId();
                
                    //------------------------------------------------------------

                    $query = 'INSERT INTO visits (guest_id, room_id, arrival, departure) VALUES (:guest_id, :room_id, :arrivalInput, :departureInput)';

                    $statement = $database->prepare($query);

                    $statement->bindParam(':guest_id', $last_user_id, PDO::PARAM_INT);

                    $statement->bindParam(':room_id', $roomInput, PDO::PARAM_INT);

                    $statement->bindParam(':arrivalInput', $arrivalInput, PDO::PARAM_STR);

                    $statement->bindParam(':departureInput', $departureInput, PDO::PARAM_STR);

                    $statement->execute();
                    
                    $last_visit_id = $database->lastInsertId();

                    //----------------------------------------------------------------


                    foreach($activities as $activity){

                        $query = 'INSERT INTO visit_activities (visit_id, activity_id) VALUES (:visit_id, :activity_id)'; 

                        $statement = $database->prepare($query);

                        $statement->bindParam(':visit_id', $last_visit_id, PDO::PARAM_INT);

                        $statement->bindParam(':activity_id', $activity, PDO::PARAM_INT);

                        $statement->execute();

                    };

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

//---------------------------------------------------------

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
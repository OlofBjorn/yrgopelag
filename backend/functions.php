<?php

//TODO: Calculate prices and send to booking.php 

/*require __DIR__."booking.php";

$activityPrices = array(
    'actEconomic' => 0.5,
    'actBasic' => 1.25,
    'actPremium' => 2.5,
    'actSupreme' => 3.5
);

$activitySum = array();
$getkeys = array_keys($_GET);

foreach($activityPrices as $key => $value)
{
    if(in_array($key, $getkeys)) $activitySum[] = $value;
}*/

declare(strict_types=1);

$databaseLocation = "sqlite:".__DIR__.'/database.db';
$database = new PDO($databaseLocation);

//require(__DIR__ . '/../vendor/autoload.php');



//echo $_ENV['API_KEY'];

const ROOM_PRICES = [
    1 => 1.0, // ECONOMY
    2 => 2.0, // STANDARD
    3 => 4.0, // LUXURY
];

const ACTIVITY_PRICES = [
    //WATER
    1 => 0.5,  // ECONOMY
    //GAME  
    2 => 1.25, // BASIC
    //WHEEL
    3 => 2.5,  // PREMIUM
    //DINO  
    4 => 0.5,  // ECONOMY
    5 => 1.25, // BASIC
    6 => 2.5,  // PREMIUM
    7 => 3.5,  // SUPERIOR
];

//-------------------------------------------------------------------

function calculateNights(string $arrival, string $departure): int
{
    $arrivalDate = new DateTime($arrival);
    $departureDate = new DateTime($departure);

    return (int) $arrivalDate->diff($departureDate)->days;
}

//-----------------------------------------------------------------

function calculateRoomCost(int $roomId, int $nights): float
{
    if (!isset(ROOM_PRICES[$roomId])) {
        throw new InvalidArgumentException('Error: Invalid room type');
    }

    return ROOM_PRICES[$roomId] * $nights;
}

function calculateActivityCost(array $activities): float
{
    $priceSum = 0.0;

    foreach ($activities as $activityId) {
        if (isset(ACTIVITY_PRICES[$activityId])) {
            $priceSum += ACTIVITY_PRICES[$activityId];
        }
    }

    return $priceSum;
}

// -------------------------------------------------------

function calculateTotalPrice(
    int $roomId,
    string $arrival,
    string $departure,
    array $activities
): float {
    $nights = calculateNights($arrival, $departure);

    if ($nights < 1) {
        throw new InvalidArgumentException("Error: Stay must be at least one night.");
    }

    $roomCost = calculateRoomCost($roomId, $nights);
    $activityCost = calculateActivityCost($activities);

    return $roomCost + $activityCost;
}

//--------------------------------------------------------------

function isRoomAvailable(
    PDO $database,
    int $roomId,
    string $arrival,
    string $departure
): bool {
    $query = "SELECT COUNT(*) FROM visits WHERE room_id = :room_id AND arrival < :departure AND departure > :arrival";

    $statement = $database->prepare($query);
    $statement->bindValue(':room_id', $roomId, PDO::PARAM_INT);
    $statement->bindValue(':arrival', $arrival, PDO::PARAM_STR);
    $statement->bindValue(':departure', $departure, PDO::PARAM_STR);
    $statement->execute();
    return (int) $statement->fetchColumn() === 0;
}

//---------------------------------------------------------------

function mapActivityIdToReceiptFormat(int $id): array
{
    return match ($id) {
        1 => ['activity' => 'water', 'tier' => 'economy'],
        2 => ['activity' => 'games', 'tier' => 'basic'],
        3 => ['activity' => 'wheels', 'tier' => 'premium'],
        4 => ['activity' => 'hotel-specific', 'tier' => 'economy'],
        5 => ['activity' => 'hotel-specific', 'tier' => 'basic'],
        6 => ['activity' => 'hotel-specific', 'tier' => 'premium'],
        7 => ['activity' => 'hotel-specific', 'tier' => 'superior'],
        default => throw new InvalidArgumentException('Invalid activity ID')
    };
}


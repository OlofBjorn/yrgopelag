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

require_once(__DIR__ . "/database.php");

//require(__DIR__ . '/../vendor/autoload.php');



//echo $_ENV['API_KEY'];

function getAllRooms(PDO $database): array
{
    $statement = $database->query(
        'SELECT id, class, description, image, price_per_night FROM rooms ORDER BY id'
    );

    return $statement->fetchAll(PDO::FETCH_ASSOC);
}

//-----------------------------------------------------------

function getAllActivities(PDO $database): array
{
    $stmt = $database->query(
        'SELECT id, name, category, description, image, price
         FROM activities
         ORDER BY category, id'
    );

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getActivityPrices(PDO $database): array
{
    $stmt = $database->query(
        'SELECT id, price FROM activities'
    );

    return $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
}


/*const ROOM_PRICES = [
    1 => 1.0, // ECONOMY
    2 => 2.0, // STANDARD
    3 => 4.0, // LUXURY
];*/

function getRoomPrices(PDO $database): array
{
    $rooms = getAllRooms($database);
    $prices = [];
    foreach ($rooms as $room) {
        if (is_array($room) && isset($room['id'], $room['price_per_night'])) {
            $prices[(int)$room['id']] = (float)$room['price_per_night'];
        }
    }
    return $prices;
}

function getRoomPrice(PDO $database, int $roomId): float
{
    $stmt = $database->prepare(
        'SELECT price_per_night FROM rooms WHERE id = :id'
    );
    $stmt->execute([':id' => $roomId]);

    $price = $stmt->fetchColumn();

    if ($price === false) {
        throw new InvalidArgumentException('Invalid room ID');
    }

    return (float) $price;
}

/*const ACTIVITY_PRICES = [
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
];*/

//-------------------------------------------------------------------

function calculateNights(string $arrival, string $departure): int
{
    $arrivalDate = new DateTime($arrival);
    $departureDate = new DateTime($departure);

    return (int) $arrivalDate->diff($departureDate)->days;
}

//-----------------------------------------------------------------

/*function calculateRoomCost(int $roomId, int $nights): float
{
    if (!isset(ROOM_PRICES[$roomId])) {
        throw new InvalidArgumentException('Error: Invalid room type');
    }

    return ROOM_PRICES[$roomId] * $nights;
}*/

function calculateRoomCostPHP(array $roomPrices, int $roomId, int $nights): float {
    return $roomPrices[$roomId] ?? throw new InvalidArgumentException('Invalid room ID');
}

/*function calculateActivityCost(array $activities): float
{
    $priceSum = 0.0;

    foreach ($activities as $activityId) {
        if (isset(ACTIVITY_PRICES[$activityId])) {
            $priceSum += ACTIVITY_PRICES[$activityId];
        }
    }

    return $priceSum;
}*/

function calculateActivityCost(PDO $database, array $activities): float
{
    if (empty($activities)) {
        return 0.0;
    }

    $placeholders = implode(',', array_fill(0, count($activities), '?'));

    $stmt = $database->prepare(
        "SELECT SUM(price) FROM activities WHERE id IN ($placeholders)"
    );

    $stmt->execute(array_map('intval', $activities));

    return (float) $stmt->fetchColumn();
}

// -------------------------------------------------------

function calculateTotalPrice(
    PDO $database,
    int $roomId,
    string $arrival,
    string $departure,
    array $activities
): float {
    $nights = calculateNights($arrival, $departure);

    if ($nights < 1) {
        throw new InvalidArgumentException("Error: Stay must be at least one night.");
    }

    $roomCost = getRoomPrice($database, $roomId) * $nights;
    $activityCost = calculateActivityCost($database, $activities);

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

/*function mapActivityIdToReceiptFormat(int $id): array
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
}*/

/*foreach($activities as $activity){
    $query = 'INSERT INTO visit_activities (visit_id, activity_id) VALUES (:visit_id, :activity_id)'; 
    $statement = $database->prepare($query);
    $statement->bindParam(':visit_id', $last_visit_id, PDO::PARAM_INT);
    $statement->bindParam(':activity_id', $activity, PDO::PARAM_INT);
    $statement->execute();
}*/

function getActivitiesForReceipt(PDO $database, array $activityIds): array
{
    if (empty($activityIds)) return [];

    $placeholders = implode(',', array_fill(0, count($activityIds), '?'));
    $statement = $database->prepare(
        "SELECT id, name, category, tier, price FROM activities WHERE id IN ($placeholders)"
    );
    $statement->execute($activityIds);

    $tierMap = [
    1 => 'economy',
    2 => 'basic',
    3 => 'premium',
    4 => 'superior'
    ];

    $activities = [];
    foreach ($statement->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $activities[] = [
            'activity' => strtolower($row['category']),
            'tier' => $tierMap[(int)$row['tier']] ?? 'unknown'
        ];
    }

    return $activities;
}


//-------------------------------------------------------------------

function getBookedDaysForRoom(
    PDO $database,
    int $roomId,
    string $month // format: YYYY-MM
): array {
    $query = "SELECT arrival, departure FROM visits WHERE room_id = :room_id";
    $statement = $database->prepare($query);
    $statement->execute([':room_id' => $roomId]);

    $bookedDays = [];

    foreach ($statement->fetchAll(PDO::FETCH_ASSOC) as $visit) {
        $start = new DateTime($visit['arrival']);
        $end   = new DateTime($visit['departure']);

        while ($start < $end) {
            if ($start->format('Y-m') === $month) {
                $bookedDays[] = (int)$start->format('j');
            }
            $start->modify('+1 day');
        }
    }

    return array_unique($bookedDays);
}
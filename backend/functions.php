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

const ROOM_PRICES = [
    1 => 1.0, // ECONOMY
    2 => 2.0, // STANDARD
    3 => 4.0, // LUXURY
];

const ACTIVITY_PRICES = [
    1 => 0.5,   // ECONOMY
    2 => 1.25,  // BASIC
    3 => 2.5,   // PREMIUM
    4 => 3.5,   // SUPERIOR
];

function calculateNights(string $arrival, string $departure): int
{
    $arrivalDate = new DateTime($arrival);
    $departureDate = new DateTime($departure);

    return (int) $arrivalDate->diff($departureDate)->days;
}

function calculateRoomCost(int $roomId, int $nights): float
{
    if (!isset(ROOM_PRICES[$roomId])) {
        throw new InvalidArgumentException('Invalid room type');
    }

    return ROOM_PRICES[$roomId] * $nights;
}

function calculateActivityCost(array $activities): float
{
    $sum = 0.0;

    foreach ($activities as $activityId) {
        if (isset(ACTIVITY_PRICES[$activityId])) {
            $priceSum += ACTIVITY_PRICES[$activityId];
        }
    }

    return $priceSum;
}

function calculateTotalPrice(
    int $roomId,
    string $arrival,
    string $departure,
    array $activities
): float {
    $nights = calculateNights($arrival, $departure);

    if ($nights < 1) {
        throw new InvalidArgumentException("Stay must be at least one night.");
    }

    $roomCost = calculateRoomCost($roomId, $nights);
    $activityCost = calculateActivityCost($activities);

    return $roomCost + $activityCost;
}
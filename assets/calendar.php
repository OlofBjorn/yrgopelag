<?php

// Days when the room is booked
//require (__DIR__ . "/backend/functions.php");

/*$query = "SELECT arrival, departure FROM visits WHERE room_id = :room_id";

$statement->execute();
$visits = $statement->fetchAll(PDO::FETCH_ASSOC);

$bookedDays = [];


foreach ($visits as $visit) {
    $start = new DateTime($visit['arrival']);
    $end   = new DateTime($visit['departure']);

    // Loop through nights (arrival inclusive, departure exclusive)
    while ($start < $end) {
        if ($start->format('Y-m') === '2026-01') {
            $bookedDays[] = (int)$start->format('j');
        }
        $start->modify('+1 day');
    }
}*/

declare(strict_types=1);

$database = new PDO("sqlite:" . __DIR__ . "/../backend/database.db");
$database->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// TODO: ALTERNATE VERSION FOR DIFFERENT ROOMS
$query = "SELECT arrival, departure FROM visits WHERE room_id = 1";
//MAYBE REPLACE 1 WITH ROOMINPUT?

$statement = $database->prepare($query);
$statement->execute();
$visits = $statement->fetchAll(PDO::FETCH_ASSOC);

$bookedDays = [];

foreach ($visits as $visit) {
    $start = new DateTime($visit['arrival']);
    $end   = new DateTime($visit['departure']);
 
    while ($start < $end) {
        if ($start->format('Y-m') === '2026-01') {
            $bookedDays[] = (int)$start->format('j');
        }
        $start->modify('+1 day');
    }
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/styles/calendar.css">
</head>

<body>

</body>

</html>
    <section class="calendar">
        <?php for ($day = 1; $day <= 31; $day++): 
            $isBooked = in_array($day, $bookedDays, true);
        ?>
            <div class="day <?= $isBooked ? 'booked' : '' ?>">
                <?= $day ?>
            </div>
        <?php endfor; ?>
    </section>
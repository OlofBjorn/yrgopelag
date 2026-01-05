<?php


declare(strict_types=1);


require_once (__DIR__ . "/../backend/functions.php");
//$database = new PDO("sqlite:" . __DIR__ . "/../backend/database.db");
$database->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$month = '2026-01';

$calendarRooms = [
    1 => 'ecoCalendar',
    2 => 'standardCalendar',
    3 => 'luxuryCalendar',
];

$calendars = [];

foreach ($calendarRooms as $roomId => $domId) {
    $calendars[$domId] = getBookedDaysForRoom($database, $roomId, $month);
}

$leadingBlankDays = 4;

?>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles/calendar.css">

    <div class="calendarContainer">

        <?php foreach ($calendars as $calendarId => $bookedDays): ?>
            <section class="calendar" id="<?= htmlspecialchars($calendarId) ?>">

                <?php for ($i = 0; $i < $leadingBlankDays; $i++): ?>
                    <div class="day empty"></div>
                <?php endfor; ?>

                <?php for ($day = 1; $day <= 31; $day++): ?>
                    <div class="day <?= in_array($day, $bookedDays, true) ? 'booked' : '' ?>">
                        <?= $day ?>
                    </div>
                <?php endfor; ?>
            </section>
        <?php endforeach; ?>

    </div>

</html>
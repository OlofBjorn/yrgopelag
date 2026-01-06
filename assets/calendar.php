<?php
declare(strict_types=1);

require_once __DIR__ . "/../backend/functions.php";

$database->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$month = '2026-01';

if (!isset($roomId)) {
    throw new RuntimeException('calendar.php requires $roomId');
}

$bookedDays = getBookedDaysForRoom($database, (int)$roomId, $month);

$leadingBlankDays = 4;
?>

<section class="calendar">
    <?php for ($i = 0; $i < $leadingBlankDays; $i++): ?>
        <div class="day empty"></div>
    <?php endfor; ?>

    <?php for ($day = 1; $day <= 31; $day++): ?>
        <div class="day <?= in_array($day, $bookedDays, true) ? 'booked' : '' ?>">
            <?= $day ?>
        </div>
    <?php endfor; ?>
</section>

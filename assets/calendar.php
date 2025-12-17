<?php

// Days when the room is booked
$booked = [2, 6, 19, 27, 28];

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Calendar</title>
    <link rel="stylesheet" href="/styles/calendar.css">
</head>

<body>

</body>

</html>
<section class="calendar">
    <?php
    for ($i = 1; $i <= 31; $i++) :       
        ?>
        <div class="day"><?= $i; ?></div>
    <?php endfor; ?>
</section>
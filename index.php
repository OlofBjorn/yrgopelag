<?php
declare(strict_types=1);

require_once __DIR__."/assets/header.php";

//KOLLA PHP22 UPPGIFT 3 ASAP

?>

<html>
    <form action='backend/booking.php' method="post">
        <label for="nameInput">name</label>
        <input name="nameInput" id="nameInput" type="text" placeholder="name"/>

        <label for="codeInput">transferCode</label>
        <input name="codeInput" id="codeInput" type="text" placeholder="code"/>

        <label for="roomInput">room</label>
        <select name="roomInput" id="roomInput" type="text" placeholder="room">
            <option value="Economy">Economy</option>
            <option value="Standard">Standard</option>
            <option value="Luxury">Luxury</option>
        </select>

        <label for="arrivalInput">arrival</label>
        <input name="arrivalInput" id="arrivalInput" type="date" placeholder="arrival"  min="2026-01-01" max="2026-01-31"/>

        <label for="departureInput">departure</label>
        <input name="departureInput" id="departureInput" type="date" placeholder="departure"  min="2026-01-01" max="2026-01-31"/>

        <p>
            Attraction 1
        </p>

        <input type="radio" label="cheap1"/>
        
        <input type="radio" label="medium1"/>

        <input type="radio" label="expensive1"/>

        <input type="radio" label="superexpensive1"/>

        <br>

        <input type="submit" value="submit"/>
    </form>

</html>

<?php

require_once __DIR__."/assets/calendar.php";

require_once __DIR__."/assets/footer.php";
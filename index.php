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
        <input name="codeInput" id="codeInput" type="password" placeholder="code"/>

        <label for="roomInput">room</label>
        <select name="roomInput" id="roomInput" type="text" placeholder="room">
            <option value="1">Economy</option>
            <option value="2">Standard</option>
            <option value="3">Luxury</option>
        </select>

        <label for="arrivalInput">arrival</label>
        <input name="arrivalInput" id="arrivalInput" type="date" placeholder="arrival"  min="2026-01-01" max="2026-01-31"/>

        <label for="departureInput">departure</label>
        <input name="departureInput" id="departureInput" type="date" placeholder="departure"  min="2026-01-01" max="2026-01-31"/>

        <p>
            Attraction 1
        </p>
        <fieldset>
            <legend>Water Activities: Please select your tier</legend>
                <div>
                    <input type="checkbox" name="checkbox[]" label="cheap1" value=1 />
                    <label for="cheap1">Pool</label>
                    <input type="checkbox" name="checkbox[]" label="medium1" value=2 />
                    <label for="medium1">Scuba Diving</label>
                    <input type="checkbox" name="checkbox[]" label="expensive1" value=3 />
                    <label for="expensive1">Olympic Pool</label>
                    <input type="checkbox" name="checkbox[]" label="superexpensive1" value=4 />
                    <label for="superexpensive1">Waterpark with Fire and Minibar</label>
                </div>
        </fieldset>
        <br>

        <input type="submit" value="submit"/>
    </form>

</html>

<?php

require_once __DIR__."/assets/calendar.php";

require_once __DIR__."/assets/footer.php";
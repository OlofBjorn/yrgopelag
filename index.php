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
        <input name="roomInput" id="roomInput" type="text" placeholder="room"/>

        <label for="arrivalInput">arrival</label>
        <input name="arrivalInput" id="arrivalInput" type="text" placeholder="arrival"/>

        <label for="departureInput">departure</label>
        <input name="departureInput" id="departureInput" type="text" placeholder="departure"/>

        <p>
            Attraction 1
        </p>

        <input type="checkbox" label="cheap1"/>
        
        <input type="checkbox" label="medium1"/>

        <input type="checkbox" label="expensive1"/>

        <input type="checkbox" label="superexpensive1"/>

        <br>

        <input type="submit" value="submit"/>
    </form>

</html>

<?php

require_once __DIR__."/assets/footer.php";
<?php

require_once __DIR__."/assets/header.php";

$database = __DIR__.'/assets/database.db';


?>

<html>
    <form>
        <label for="nameInput">name</label>
        <input id="nameInput" type="text" placeholder="name"/>

        <label for="codeInput">transferCode</label>
        <input id="codeInput" type="text" placeholder="code"/>

        <label for="roomInput">room</label>
        <input id="roomInput" type="text" placeholder="room"/>

        <label for="arrivalInput">arrival</label>
        <input id="arrivalInput" type="text" placeholder="arrival"/>

        <label for="departureInput">departure</label>
        <input id="departureInput" type="text" placeholder="departure"/>

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
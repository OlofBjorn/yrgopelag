<?php
declare(strict_types=1);

require_once __DIR__."/assets/header.php";

require __DIR__."/backend/functions.php";

//KOLLA PHP22 UPPGIFT 3 ASAP

?>

<html>
    <meta charset="UTF-8">
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
            <legend>Activities:</legend>
            <legend>Water Activities: Please select your tier</legend>
                <div>
                    <input type="checkbox" name="checkbox[]" id="cheapwater" value=1 />
                    <label for="cheapwater">Pool - Cost: 0.5</label>
                </div>
            <legend>Game Activities: Please select your tier</legend>
                <div>
                    <input type="checkbox" name="checkbox[]" id="mediumgame" value=2 />
                    <label for="mediumgame">Ping Pong - Cost: 1.25</label>
                </div>
            <legend>Wheel Activities: Please select your tier</legend>
                <div>
                    <input type="checkbox" name="checkbox[]" id="highwheel" value=3 />
                    <label for="highwheel">Trike - Cost: 2.5</label>
                </div>
            <legend>Dinowatching: Please select your tier</legend>
                <div>
                    <input type="checkbox" name="checkbox[]" id="cheapdino" value=4 />
                    <label for="cheapdino">Velociraptor Pen - Cost: 0.5</label>
                </div>
                <div>
                    <input type="checkbox" name="checkbox[]" id="mediumdino" value=5 />
                    <label for="mediumdino">Triceratops Field - Cost: 1.25</label>
                </div>
                <div>
                    <input type="checkbox" name="checkbox[]" id="highdino" value=6 />
                    <label for="highdino">T. rex Sightseeing - Cost: 2.5</label>
                </div>
                <div>
                    <input type="checkbox" name="checkbox[]" id="superdino" value=7 />
                    <label for="superdino">Brachiosaurus Safari - Cost: 3.5</label>
                </div>
        </fieldset>
        <br>

         <div id="priceDisplay">Current price: $0</div>

        <input type="submit" value="submit"/>
    </form>
        <script>

        const ROOM_PRICES = {
            1: 1.0, // Economy
            2: 2.0, // Standard
            3: 4.0  // Luxury
        };

        const ACTIVITY_PRICES = {
            //WATER
            1: 0.5, 
            //GAME  
            2: 1.25, 
            //WHEEL
            3: 2.5,
            //DINO  
            4: 0.5,
            5: 1.25,
            6: 2.5,
            7: 3.5
        };

        function calculateTotalPrice() {
            const roomInput = document.getElementById('roomInput').value;
            const arrivalInput = document.getElementById('arrivalInput').value;
            const departureInput = document.getElementById('departureInput').value;

            if (!arrivalInput || !departureInput) return;

            const nights = calculateNights(arrivalInput, departureInput);
            const roomCost = calculateRoomCost(roomInput, nights);

            const selectedActivities = Array.from(document.querySelectorAll('input[name="checkbox[]"]:checked'))
                .map(checkbox => parseInt(checkbox.value));

            const activityCost = calculateActivityCost(selectedActivities);

            const totalCost = roomCost + activityCost;

            document.getElementById('priceDisplay').innerText = `Current price: $${totalCost.toFixed(2)}`;
        }

        function calculateNights(arrival, departure) {
            const arrivalDate = new Date(arrival);
            const departureDate = new Date(departure);
            const timeDiff = departureDate - arrivalDate;
            return timeDiff / (1000 * 3600 * 24);
        }

        function calculateRoomCost(roomId, nights) {
            const roomPrice = ROOM_PRICES[roomId];
            return roomPrice * nights;
        }

        function calculateActivityCost(activities) {
            return activities.reduce((sum, activityId) => sum + ACTIVITY_PRICES[activityId], 0);
        }

        document.getElementById('roomInput').addEventListener('change', calculateTotalPrice);
        document.getElementById('arrivalInput').addEventListener('change', calculateTotalPrice);
        document.getElementById('departureInput').addEventListener('change', calculateTotalPrice);
        document.querySelectorAll('input[name="checkbox[]"]').forEach(checkbox => {
            checkbox.addEventListener('change', calculateTotalPrice);
        });

        calculateTotalPrice();
    </script>
</html>

<?php

require_once __DIR__."/assets/calendar.php";

require_once __DIR__."/assets/footer.php";
<?php
declare(strict_types=1);

require_once __DIR__."/assets/header.php";

require_once __DIR__."/backend/functions.php";

//KOLLA PHP22 UPPGIFT 3 ASAP

$rooms = getAllRooms($database);
$activities = getAllActivities($database);


?>
<div id="titleContainer">
        <div id="hotelTitle">
            <h1> DINOSAUR HOTEL </h1>
        </div>
        <div id="starContainer">
            <?php
            $rating = 1;      
            $maxStars = 5;

            for ($i = 1; $i <= $maxStars; $i++):
                $star = $i <= $rating
                    ? 'images/ratedstar.png'
                    : 'images/unratedstar.png';
            ?>
                <img class="star" src="<?= $star ?>" alt="">
            <?php endfor; ?>
        </div>
    </div>
<div id="contentWrapper">

    <div id="headImages">
        <img src="images/building.png" alt="building" class="image">
        <img src="images/spine.png" alt="building" class="image">
    </div>

    <p>
        Fancy warm climates? Intrigued in what came before?
        <br>
        <br>
        Dinosaur Hotel is a tropical resort with themes around dinosaurs, the great reptiles that once roamed the Earth. While we couldn’t achieve the heights of Spielberg’s Jurassic Park, we’ve done our best to make an unforgettable experience all about the world before the meteor came crashing down, and then some!
    </p>

    <p id="activityLabel" class="label">
        ACTIVITIES
    </p>
    <div class="activities">
       <?php foreach ($activities as $activity): ?>
            <section class="activity">
                <img
                    src="images/<?= htmlspecialchars($activity['image']) ?>"
                    alt="<?= htmlspecialchars($activity['name']) ?>"
                >
                <h3><?= htmlspecialchars($activity['name']) ?></h3>
                <p><?= htmlspecialchars($activity['description']) ?></p>
                <p>Price: $<?= number_format((float)$activity['price'], 2) ?></p>
            </section>
        <?php endforeach; ?>
    </div>

    <p id="roomLabel" class="label">
        ROOMS
    </p>

        <div id="roomDisplay">
            <div id="roomsAndCalendars">
                <?php foreach ($rooms as $room): ?>
                    <div class="roomRow">
                        <section class="room">
                            <img src="images/<?= htmlspecialchars($room['image']) ?>"
                                class="roomImage">
                            <h2 class="roomLabel"><?= htmlspecialchars($room['class']) ?></h2>
                            <p class="roomText">
                                Price per night: $<?= number_format($room['price_per_night'], 2) ?>
                            </p>
                            <p class="roomText"><?= htmlspecialchars($room['description']) ?></p>
                        </section>

                        <?php 
                            $roomId = (int)$room['id'];
                            require __DIR__ . "/assets/calendar.php"; 
                        ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

    <div id="roomsAndBooking">
        
        <div id="submissionForm">
            <form action='backend/booking.php' method="post">
                <label for="nameInput">name</label>
                <input name="nameInput" id="nameInput" type="text" placeholder="name"/>

                <label for="codeInput">transferCode</label>
                <input name="codeInput" id="codeInput" type="password" placeholder="code"/>

                <label for="roomInput">room</label>
                <select name="roomInput" id="roomInput" required>
                    <option value="" disabled selected>Choose a room</option>
                    <?php foreach ($rooms as $room): ?>
                        <option value="<?= (int)$room['id'] ?>">
                            <?= htmlspecialchars($room['class'], ENT_QUOTES, 'UTF-8') ?>
                            ($<?= number_format((float)$room['price_per_night'], 2) ?>/night)
                        </option>
                    <?php endforeach; ?>
                </select>

                <label for="arrivalInput">arrival</label>
                <input name="arrivalInput" id="arrivalInput" type="date" placeholder="arrival"  min="2026-01-01" max="2026-01-31"/>

                <label for="departureInput">departure</label>
                <input name="departureInput" id="departureInput" type="date" placeholder="departure"  min="2026-01-01" max="2026-01-31"/>

                <?php
                $activities = getAllActivities($database);
                $currentCategory = null;
                ?>

                <fieldset>
                    <legend>Activities</legend>

                    <?php foreach ($activities as $activity): ?>
                        <?php if ($activity['category'] !== $currentCategory): ?>
                            <?php $currentCategory = $activity['category']; ?>
                            <p><?= htmlspecialchars($currentCategory) ?> Activities</p>
                        <?php endif; ?>

                        <div>
                            <input
                                type="checkbox"
                                name="activities[]"
                                id="activity<?= (int)$activity['id'] ?>"
                                value="<?= (int)$activity['id'] ?>"
                            >
                            <label for="activity<?= (int)$activity['id'] ?>">
                                <?= htmlspecialchars($activity['name']) ?>
                                ($<?= number_format((float)$activity['price'], 2) ?>)
                            </label>
                        </div>
                    <?php endforeach; ?>
                </fieldset>
                <br>

                <div id="priceDisplay">Current price: $0</div>

                <input type="submit" value="submit"/>
            </form>
        </div>
        
    </div>
</div>

    <?php 
    $roomPrices = getRoomPrices($database); 
    $activityPrices = getActivityPrices($database);
    ?>  

    <script>
        
        
        const ROOM_PRICES = <?= json_encode($roomPrices, JSON_THROW_ON_ERROR) ?>;

        const ACTIVITY_PRICES = <?= json_encode($activityPrices, JSON_THROW_ON_ERROR) ?>;

        function calculateTotalPrice() {
            const roomInput = document.getElementById('roomInput').value;
            const arrivalInput = document.getElementById('arrivalInput').value;
            const departureInput = document.getElementById('departureInput').value;

            if (!arrivalInput || !departureInput) return;

            const nights = calculateNights(arrivalInput, departureInput);
            const roomCost = calculateRoomCost(roomInput, nights);

            const selectedActivities = Array.from(document.querySelectorAll('input[name="activities[]"]:checked'))
                .map(activity => parseInt(activity.value));

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
        document.querySelectorAll('input[name="activities[]"]').forEach(activity => {
            activity.addEventListener('change', calculateTotalPrice);
        });

        calculateTotalPrice();
    </script>


<?php



require_once __DIR__."/assets/footer.php";
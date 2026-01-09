# yrgopelag
My yrgopelag, WIP throughout Dec 2025-early Jan 2026

## URL
https://olofvb.se/yrgopelag/

## Database

CREATE TABLE rooms(
id INTEGER PRIMARY KEY AUTOINCREMENT,
class VARCHAR(10),
image VARCHAR(50),
description VARCHAR(250),
price_per_night FLOAT
);

CREATE TABLE activities(
id INTEGER PRIMARY KEY AUTOINCREMENT,
name VARCHAR(35),
category VARCHAR(35),
tier INTEGER,
description VARCHAR(250),
image VARCHAR(50),
price FLOAT
);

CREATE TABLE guests(
id INTEGER PRIMARY KEY AUTOINCREMENT,
name VARCHAR(75)
);

CREATE TABLE visits(
id INTEGER PRIMARY KEY AUTOINCREMENT,
guest_id INTEGER,
room_id INTEGER,
arrival VARCHAR(10),
departure VARCHAR(10),
FOREIGN KEY (guest_id) REFERENCES guests(id),
FOREIGN KEY (room_id) REFERENCES rooms(id)
);

CREATE TABLE visit_activities(
id INTEGER PRIMARY KEY AUTOINCREMENT,
visit_id INTEGER,
activity_id INTEGER,
FOREIGN KEY (visit_id) REFERENCES visits(id),
FOREIGN KEY (activity_id) REFERENCES activities(id)
);
## Code Review
1. Booking.php:14 you made a bool variable called succes that is set to false. In your code you type "$success = false;" each time you bring up the variable you set it to false. There is no need for you to be repeating yourself and writing "$success = false;" if you've already created the variable as false. Only if you were to change this variable later on would you want to write "$success = false" to change it back otherwise just type "$success;".
2. Booking.php:30. Instead of making it so that you get an error when the transferCode is empty you should've just made it required to type in the transferCode. This also applies to the other input fields where you haven't set them to required so they can be submitted empty.
3. Functions.php:80. You made a funciton called calculateRoomCostPHP where you included the parameter "$nights" but the never use it. You should either multiply price by $nights or remove it.
4. Functions.php:152. The tiermap array should be at the top of the file to avoid repeating it on every call.
5. Index.php:9 and index.php:152. You've created a variable for the function "getAllActivities($database)" called $activities. On row 152 you type "$activities = getAllActivities($database);" instead of using the variable again.
6. Index.php:197-256. You should move all the JS code to a separate JavaScript file. It's much simpler to maintain that way and also makes your project look better.
7. Index.php:185. You should change type submit to "<button type="submit">Submit</button>". This is for better symantics and also easier to change the styling in css afterwards.
 

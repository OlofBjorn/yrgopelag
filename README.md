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
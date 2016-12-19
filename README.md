# generate_milions_record

Chalange: create script wchich working less than 20 seconds.
Best working time is 5 seconds.

Generate 5 milions records as Name, Surname and Age.

Name and Surname is string length 15 chars
age is number from 1 to 99

Put into PostgreSQL database.

Relase 1.0.0

My PC:
- system: Ubuntu Desktop 16.10
- memory: 7,5GB
- processor: Intel® Core™ i5-3470 CPU @ 3.20GHz × 4
- hard drive: SSD
- mounted RAM DISK in /tmp/ram with 512MB
- localhost postgresql server

test running time: 16 s - 21 s

If You can test my script on your computer run test.php

IMPORTANT! before run this script exec once in cmd bottom command
mkdir /tmp/ram
mount -t tmpfs -o size=512m tmpfs /tmp/ram
# raspi_gauge

## http access

copy everything under www/ to root folder of your webserver (php has to be enabled), default for raspberry /var/www/html/

there i no security settins to make it easier to read and understand

### index.php 

start page for browser access
loads a gauge and history diagramm

### api.php

data for it will be loaded as json
data will be read from data access files described below

## data access

start python readdata.py

this will read the start value from ADC and set this as "fresh air" value
this will write data (see file for more details) every 0.5 sec to 

> smbus_A0.current
> smbus_A0.ppb
> smbus_A0.ugpm3
> smbus_A0.csv

and to the console

## MQ-131 Module

since most board do not come with a datasheet/description this is what i used:

data sheet for the heart piece:
https://aqicn.org/air/view/sensor/spec/o3.winsen-mq131.pdf

```
    VCC
     |
    +++
    | | RS
    +++
     |
     +------- AOUTDC
     |
    +++
    | | RL (My board used 1kOhm)
    +++
     O
     |
    GND

```
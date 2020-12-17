# copy of simpletest.py
# and cleanup for better readability
#
# Simple demo of reading each analog input from the ADS1x15 and printing it to
# the screen.
# Author: Tony DiCola
# License: Public Domain
import time
import datetime

from ADS1x15 import ADS1015

adc = ADS1015()

GAIN = 1

smbus_analog_0= open("/var/www/html/smbus_A0.csv","a+")

print('Reading ADS1x15 values, press Ctrl-C to quit...')

while True:
    value = adc.read_adc(0, gain=GAIN)
    date = datetime.datetime.now()
    print('| {} | {}'.format(value, date))

    smbus_analog_0.write('{},{}\n'.format(date, value))

    smbus_analog_0_curr= open("/var/www/html/smbus_A0.current","w")
    smbus_analog_0_curr.write('{}\n'.format(value))
    smbus_analog_0_curr.close()

    time.sleep(0.5)

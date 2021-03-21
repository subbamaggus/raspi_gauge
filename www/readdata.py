# copy of simpletest.py
# and cleanup for better readability
#
# Simple demo of reading each analog input from the ADS1x15 and printing it to
# the screen.
# Author: Tony DiCola
# License: Public Domain
import time
import datetime
import traceback
import sys

from ADS1x15 import ADS1115
from mq import *
import Adafruit_DHT

try:
    print('Reading ADS1x15 values, press Ctrl-C to quit...')
    
    adc = ADS1115()
    mq = MQ();
    
    while True:
        date = datetime.datetime.now()
    
        value = adc.read_adc(0, 1)
        perc = mq.MQPercentage()
        humidity, temperature = Adafruit_DHT.read_retry(11, 4)
    
        print('| {} | {} | {} | {} | {}'.format(date, value, humidity, temperature, perc["OZONE"]))

        smbus_analog_0_curr= open("data.csv","w")
        smbus_analog_0_curr.write('{},{},{},{},{}\n'.format(date, value, humidity, temperature, int(perc["OZONE"])))
        smbus_analog_0_curr.close()
        
        time.sleep(0.5)

except:
    print("\nAbort by user")
    traceback.print_exc() 
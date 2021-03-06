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
    
    smbus_analog_0= open("smbus_A0.csv","a+")    
    
    while True:
        date = datetime.datetime.now()
    
        value = adc.read_adc(0, 1)
        perc = mq.MQPercentage()
        humidity, temperature = Adafruit_DHT.read_retry(11, 4)
    
        print('| {} | {} | {} | {} | {} | {}'.format(date, value, value/4, humidity, temperature, perc["OZONE"]))

        smbus_analog_0.write('{},{},{},{},{},{}\n'.format(date, value, value/4, humidity, temperature, int(perc["OZONE"])))
    
        smbus_analog_0_curr= open("smbus_A0.current","w")
        smbus_analog_0_curr.write('{}\n'.format(value))
        smbus_analog_0_curr.close()
        

        smbus_analog_0_curr= open("smbus_A0.ppb","w")
        smbus_analog_0_curr.write('{}\n'.format(int(perc["OZONE"] * 1000)))
        smbus_analog_0_curr.close()
    
        # ppm to ugpm3 >> 200ug/m3 ~ 0.1 ppm
        # this sensor is from 2PPB to 10PPM >> from 0.4 ug/m3 to 20000ug/m3
        smbus_analog_0_curr= open("smbus_A0.ugpm3","w")
        smbus_analog_0_curr.write('{}\n'.format(int(perc["OZONE"] * 2000)))
        smbus_analog_0_curr.close()
    
        time.sleep(0.5)

except:
    print("\nAbort by user")
    traceback.print_exc() 
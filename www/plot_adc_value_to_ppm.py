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

from mq import *
#from ADS1x15 import ADS1015

try:
    print('generating DataCharts for all types ...')
    print('value,LPG,OZONE')
    mq = MQ();
    
    print('start output')
    
    for value in range(1, 900, 10):
        
        rs = mq.MQResistanceCalculation(value)
        perc = mq.MQPercentageValueToArray(rs)

        # raw value, ppm lpg, ppm ozone, calculated rs value, ug/m3
        print('{},{},{},{},{}'.format(value, perc["GAS_LPG"], perc["OZONE"], rs, (perc["OZONE"]*2000)))

    mq.MQPrintCurveAndP1P2()
    
except:
    print("\nAbort by user")
    traceback.print_exc() 
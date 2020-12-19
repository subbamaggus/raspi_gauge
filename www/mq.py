
# adapted from sandboxelectronics.com/?p=165

import time
import math
from MCP3008 import MCP3008
from ADS1x15 import ADS1015

class MQ():

    ######################### Hardware Related Macros #########################
    MQ_PIN                       = 0        # define which analog input channel you are going to use (MCP3008)
    RL_VALUE                     = 5        # define the load resistance on the board, in kilo ohms
    RO_CLEAN_AIR_FACTOR          = 9.83     # RO_CLEAR_AIR_FACTOR=(Sensor resistance in clean air)/RO,
                                            # which is derived from the chart in datasheet

    ######################### Software Related Macros #########################
    CALIBARAION_SAMPLE_TIMES     = 5       # define how many samples you are going to take in the calibration phase
    CALIBRATION_SAMPLE_INTERVAL  = 500      # define the time interval(in milisecond) between each samples in the
                                            # cablibration phase
    READ_SAMPLE_INTERVAL         = 50       # define the time interval(in milisecond) between each samples in
    READ_SAMPLE_TIMES            = 5        # define how many samples you are going to take in normal operation
                                            # normal operation

    ######################### Application Related Macros ######################
    GAS_LPG                      = 0
    GAS_CO                       = 1
    GAS_SMOKE                    = 2
    GAS_OZONE                    = 3

    ######################### ADC Settins #####################################
    ADC_TYPE                     = "ADS1015" # current options: ADS1015, MCP3008
    ADC_MAX_VALUE                = 2047.0    # ADS1015 is 16 bit (but has 2047), MCP3008 is 10 bit (so has 1023)

    def __init__(self, Ro=None, analogPin=0):
        self.Ro = Ro
        self.MQ_PIN = analogPin
        self.adc = self.MQLoadAdc()

        self.LPG_P1P2 = [200, 1.62, 10000, 0.25]  # data format: {x1, y1, x2, y2 }
        self.LPGCurve = [2.3,0.21,-0.47]    # two points are taken from the curve.
                                            # with these two points, a line is formed which is "approximately equivalent"
                                            # to the original curve.
                                            # data format:{ x, y, slope}; point1: (lg200, 0.21), point2: (lg10000, -0.59)
        self.CO_P1P2 = [200, 5.24, 10000, 1.41]  # data format: {x1, y1, x2, y2 }
        self.COCurve = [2.3,0.72,-0.34]     # two points are taken from the curve.
                                            # with these two points, a line is formed which is "approximately equivalent"
                                            # to the original curve.
                                            # data format:[ x, y, slope]; point1: (lg200, 0.72), point2: (lg10000,  0.15)
        self.Smoke_P1P2 = [200, 3.39, 10000, 0.6025]  # data format: {x1, y1, x2, y2 }
        self.SmokeCurve = [2.3,0.53,-0.44]   # two points are taken from the curve.
                                            # with these two points, a line is formed which is "approximately equivalent"
                                            # to the original curve.
                                            # data format:[ x, y, slope]; point1: (lg200, 0.53), point2: (lg10000,  -0.22)
        # https://forum.arduino.cc/index.php?topic=469459.0
        self.Ozone_P1P2 = [200, 4000, 10000, 0.25]  # data format: {x1, y1, x2, y2 }
        self.OzoneCurve = [2.3,3.6,0.45] # rough estimate for first shot
        # https://datasheetspdf.com/pdf/770517/ETC/MQ-131/1
        #self.Ozone_P1P2 = [200, 1.62, 10000, 0.25]  # data format: {x1, y1, x2, y2 }
        #self.OzoneCurve = [1,4.22,-0.0009]

        print("Calibrating...")
        if (Ro == None):
            self.Ro = self.MQCalibration(self.MQ_PIN)
        print("Calibration is done...\n")
        print("Ro=%f kohm" % self.Ro)

    def MQLoadAdc(self):
        if (self.ADC_TYPE == "ADS1015"):
            ADC_MAX_VALUE = 2047.0
            return ADS1015()
        else:
            ADC_MAX_VALUE = 1023.0
            return MCP3008()

    def MQPercentageValueToArray(self, data):
        val = {}
        val["GAS_LPG"]  = self.MQGetGasPercentage(data/self.Ro, self.GAS_LPG)
        val["CO"]       = self.MQGetGasPercentage(data/self.Ro, self.GAS_CO)
        val["SMOKE"]    = self.MQGetGasPercentage(data/self.Ro, self.GAS_SMOKE)
        val["OZONE"]    = self.MQGetGasPercentage(data/self.Ro, self.GAS_OZONE)
        return val

    def MQPercentage(self):
        read = self.MQRead(self.MQ_PIN)
        return self.MQPercentageValueToArray(read)

    ######################### MQResistanceCalculation #########################
    # Input:   raw_adc - raw value read from adc, which represents the voltage
    # Output:  the calculated sensor resistance
    # Remarks: The sensor and the load resistor forms a voltage divider. Given the voltage
    #          across the load resistor and its resistance, the resistance of the sensor
    #          could be derived.
    ############################################################################
    def MQResistanceCalculation(self, raw_adc):
        return float(self.RL_VALUE*(self.ADC_MAX_VALUE - raw_adc)/float(raw_adc));


    ######################### MQCalibration ####################################
    # Input:   mq_pin - analog channel
    # Output:  Ro of the sensor
    # Remarks: This function assumes that the sensor is in clean air. It use
    #          MQResistanceCalculation to calculates the sensor resistance in clean air
    #          and then divides it with RO_CLEAN_AIR_FACTOR. RO_CLEAN_AIR_FACTOR is about
    #          10, which differs slightly between different sensors.
    ############################################################################
    def MQCalibration(self, mq_pin):
        val = 0.0
        for i in range(self.CALIBARAION_SAMPLE_TIMES):          # take multiple samples
            print('sample {} ...'.format(i))
            raw_value = self.MQReadRaw(mq_pin)
            val += self.MQResistanceCalculation(raw_value)
            time.sleep(self.CALIBRATION_SAMPLE_INTERVAL/1000.0)

        val = val/self.CALIBARAION_SAMPLE_TIMES                 # calculate the average value

        val = val/self.RO_CLEAN_AIR_FACTOR                      # divided by RO_CLEAN_AIR_FACTOR yields the Ro
                                                                # according to the chart in the datasheet

        return val;

    #########################  MQReadRaw ##########################################
    # Input:   mq_pin - analog channel
    # Output:  raw value of the adc
    ############################################################################
    def MQReadRaw(self, mq_pin):
        if (self.ADC_TYPE == "ADS1015"):
            gain = 1
            return self.adc.read_adc(mq_pin, gain)
        else:
            return self.adc.read(mq_pin)

    #########################  MQRead ##########################################
    # Input:   mq_pin - analog channel
    # Output:  Rs of the sensor
    # Remarks: This function use MQResistanceCalculation to caculate the sensor resistenc (Rs).
    #          The Rs changes as the sensor is in the different consentration of the target
    #          gas. The sample times and the time interval between samples could be configured
    #          by changing the definition of the macros.
    ############################################################################
    def MQRead(self, mq_pin):
        rs = 0.0

        for i in range(self.READ_SAMPLE_TIMES):
            raw_value = self.MQReadRaw(mq_pin)
            rs += self.MQResistanceCalculation(raw_value)
            time.sleep(self.READ_SAMPLE_INTERVAL/1000.0)

        rs = rs/self.READ_SAMPLE_TIMES

        return rs

    #########################  MQGetGasPercentage ##############################
    # Input:   rs_ro_ratio - Rs divided by Ro
    #          gas_id      - target gas type
    # Output:  ppm of the target gas
    # Remarks: This function passes different curves to the MQGetPercentage function which
    #          calculates the ppm (parts per million) of the target gas.
    ############################################################################
    def MQGetGasPercentage(self, rs_ro_ratio, gas_id):
        if ( gas_id == self.GAS_LPG ):
            return self.MQGetPercentage(rs_ro_ratio, self.LPGCurve)
        elif ( gas_id == self.GAS_CO ):
            return self.MQGetPercentage(rs_ro_ratio, self.COCurve)
        elif ( gas_id == self.GAS_SMOKE ):
            return self.MQGetPercentage(rs_ro_ratio, self.SmokeCurve)
        elif ( gas_id == self.GAS_OZONE ):
            return self.MQGetPercentage(rs_ro_ratio, self.OzoneCurve)
        return 0

    #########################  MQGetPercentage #################################
    # Input:   rs_ro_ratio - Rs divided by Ro
    #          pcurve      - pointer to the curve of the target gas
    # Output:  ppm of the target gas
    # Remarks: By using the slope and a point of the line. The x(logarithmic value of ppm)
    #          of the line could be derived if y(rs_ro_ratio) is provided. As it is a
    #          logarithmic coordinate, power of 10 is used to convert the result to non-logarithmic
    #          value.
    ############################################################################
    def MQGetPercentage(self, rs_ro_ratio, pcurve):
        return (math.pow(10,( ((math.log(rs_ro_ratio)-pcurve[1])/ pcurve[2]) + pcurve[0])))


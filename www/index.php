<?php

require("config.php");

?><html>
  <head>
    <meta name="viewport" content="initial-scale=1, maximum-scale=2">
    <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
    <style>
        label {
            width:100px;
            text-align: right;
            font-family: Arial, Helvetica, sans-serif;
            font-size: xx-small;
        }
    </style>

    <script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    <script type="text/javascript">
      google.charts.load('current', {'packages':['corechart','gauge']});
      google.charts.setOnLoadCallback(drawChart);

      var history_length = <?php echo $DEFAULT_HISTORY_LENGTH; ?>;
      var pollingRate = <?php echo $POLLING_RATE; ?>;
      

      var ring_buffer = initRingBuffer(history_length);
        
      var history_chart;
      var history_data;
      var history_options;
        
      var gauge_chart;
      var gauge_data;
      var gauge_options;
		
      var intervalID;
        
      function initRingBuffer(length) {
        var ring_buffer_header = ['Time',  'Data'];
        var ring_buffer_l = [ring_buffer_header];

        for(var i = length; i > 0; i--) {
            ring_buffer_l.push(['' + i, 0]);
        }
        return ring_buffer_l;
      }

      function addToRingBuffer(data, length, last_value) {
        for(var i = 0; i < (length - 1); i++) {
          data.setValue(i, 1, data.getValue(i+1, 1));
        }

        data.setValue((length - 1), 1, last_value);

        return data;
      }
        
      function changeRingBufferSize(diff) {
        history_length = history_length + diff;
        ring_buffer = initRingBuffer(history_length);
        history_data = google.visualization.arrayToDataTable(ring_buffer);

        console.log('changeRingBufferSize:' + history_length);
      }
        
      function pollDataSource() {

        var jsonData = $.ajax({
          url: "api.php",
          dataType: "json",
          async: false
          }).responseText;
        var DataObject = JSON.parse(jsonData);

        history_data = addToRingBuffer(history_data, history_length, DataObject.ugpm3);
        history_chart.draw(history_data, history_options);

        gauge_data.setValue(0, 1, DataObject.ugpm3);
        gauge_chart.draw(gauge_data, gauge_options);
      }

      function changePollRate(diff) {
        clearInterval(intervalID);
        pollingRate = pollingRate + diff;
        intervalID = setInterval(pollDataSource, pollingRate);

        console.log('changePollRate:' + pollingRate);
        document.getElementById("refresh_rate").innerHTML = "RefreshRate: " + pollingRate;
      }

      function drawChart() {

        gauge_data = google.visualization.arrayToDataTable([
          ['Label', 'Value'],
          ['O3 µg/m³', 0]
        ]);

        // source for yellow and red: https://www.lfu.bayern.de/luft/doc/ozoninfo.pdf
        gauge_options = {
          width: 300, 
          height: 200,
          max: <?php echo $MAX_CHART_VALUE; ?>,
          greenFrom:    0, greenTo:   40,
          yellowFrom: 180, yellowTo: 240,
          redFrom:    240, redTo:    <?php echo $MAX_CHART_VALUE; ?>,
          minorTicks: 5
        };

        gauge_chart = new google.visualization.Gauge(document.getElementById('gauge_chart'));
        gauge_chart.draw(gauge_data, gauge_options);

        // second chart
        history_options = {
          title: 'O3 µg/m³',
          legend: { position: 'bottom' },
          vAxis: {
            minValue: 0,
            maxValue: <?php echo $MAX_CHART_VALUE; ?> 
          },
          isStacked: true
        };

        history_chart = new google.visualization.SteppedAreaChart(document.getElementById('history_chart'));
        history_data = google.visualization.arrayToDataTable(ring_buffer);
        history_chart.draw(history_data, history_options);

        document.getElementById("refresh_rate").innerHTML = "RefreshRate: " + pollingRate;
        intervalID = setInterval(pollDataSource, pollingRate);
      }
    </script>
  </head>
  <body>
    <center>
      <div id="gauge_chart" style="width: 300px; height: 200px;"></div>
      <div id="history_chart" style="width: 400px; height: 250px;"></div>
      <input type="image" width="12" height="12" src="icon/plus.png" onclick="changeRingBufferSize(30)" />
      <input type="image" width="12" height="12" src="icon/time.png" />
      <input type="image" width="12" height="12" src="icon/minus.png" onclick="changeRingBufferSize(-30)" />
      |
      <input type="image" width="12" height="12" src="icon/slower.png" onClick="changePollRate(500)" />
      <input type="image" width="12" height="12" src="icon/faster.png" onClick="changePollRate(-500)" />
      <label id="refresh_rate"></label>
    </center>
  </body>
</html>
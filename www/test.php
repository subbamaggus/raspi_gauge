<?php

require("config.php");

?><html>
  <head>
    <meta name="viewport" content="initial-scale=1, maximum-scale=2">
    <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
    <script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    <script type="text/javascript">
      google.charts.load('current', {'packages':['corechart','gauge']});
      google.charts.setOnLoadCallback(drawChart);

      var history_length = <?php echo $DEFAULT_HISTORY_LENGTH; ?>;
      var pollingRate = <?php echo $POLLING_RATE; ?>;

      var data_body = initRollingDataArray(history_length);
        
      var data_history;
      var chart_history;
      var options_history;
        
      var data_gauge;
      var chart_gauge;
      var options_gauge;
		
      var intervalID;
        
      function initRollingDataArray(length) {
        var data_head = ['Time',  'Raw'];
        var data_body_l = [data_head];

        for(var i = length; i > 0; i--) {
            data_body_l.push(['' + i, 0]);
        }
        return data_body_l;
      }

      function appendRollingValue(data, length, last_value) {
        for(var i = 0; i < (length - 1); i++) {
          data.setValue(i, 1, data.getValue(i+1, 1));
        }

        data.setValue((length - 1), 1, last_value);

        return data;
      }
        
      function changeBufferSize(diff) {
        history_length = history_length + diff;
        data_body = initRollingDataArray(history_length);
        data_history = google.visualization.arrayToDataTable(data_body);

        console.log('changeBufferSize:' + history_length);
      }
        
      function pollDataSource() {

        var jsonData = $.ajax({
          url: "api.php",
          dataType: "json",
          async: false
          }).responseText;
        var myObj = JSON.parse(jsonData);

        data_history = appendRollingValue(data_history, history_length, myObj.raw);
        chart_history.draw(data_history, options_history);

        data_gauge.setValue(0, 1, myObj.ugpm3);
        chart_gauge.draw(data_gauge, options_gauge);
      }

      function changePollRate(diff) {
        clearInterval(intervalID);
        pollingRate = pollingRate + diff;
        intervalID = setInterval(pollDataSource, pollingRate);

        console.log('changePollRate:' + pollingRate);
      }

      function drawChart() {

        data_gauge = google.visualization.arrayToDataTable([
          ['Label', 'Value'],
          ['O3 µg/m³', 80]
        ]);

        options_gauge = {
          width: 300, 
          height: 200,
          max: 400,
          yellowFrom:180, yellowTo: 240,
          redFrom: 240, redTo: 400,
          minorTicks: 5
        };

        chart_gauge = new google.visualization.Gauge(document.getElementById('chart_gauge'));
        chart_gauge.draw(data_gauge, options_gauge);

        // second chart
        options_history = {
          title: 'Ozon Concentration',
          legend: { position: 'bottom' },
          vAxis: {
            minValue: 0,
            maxValue: <?php echo $MAX_ADC_VALUE; ?> 
          },
          isStacked: true
        };

        chart_history = new google.visualization.SteppedAreaChart(document.getElementById('chart_history'));
        data_history = google.visualization.arrayToDataTable(data_body);
        chart_history.draw(data_history, options_history);

        intervalID = setInterval(pollDataSource, pollingRate);
      }
    </script>
  </head>
  <body>
    <center>
      <div id="chart_gauge" style="width: 300px; height: 200px;"></div>
      <div id="chart_history" style="width: 400px; height: 250px;"></div>
      <input type="image" width="12" height="12" src="icon/plus.png" onclick="changeBufferSize(30)" />
      <input type="image" width="12" height="12" src="icon/time.png" />
      <input type="image" width="12" height="12" src="icon/minus.png" onclick="changeBufferSize(-30)" />
      |
      <input type="image" width="12" height="12" src="icon/slower.png" onClick="changePollRate(500)" />
      <input type="image" width="12" height="12" src="icon/faster.png" onClick="changePollRate(-500)" />
    </center>
  </body>
</html>
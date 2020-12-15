<?php

require("config.php");

?><html>
  <head>
    <meta name="viewport" content="initial-scale=1, maximum-scale=2">
    <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
    <script src="https://code.jquery.com/jquery-3.2.1.min.js"></script>
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    <script type="text/javascript">
      google.charts.load('current', {'packages':['corechart','gauge']});
      google.charts.setOnLoadCallback(drawChart);

      var history_length = <?php echo $DEFAULT_HISTORY_LENGTH; ?>;
      var pollingRate = <?php echo $POLLING_RATE; ?>;

      var myRawValue = 0;
      var myPercentValue = 0;
      
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
      }
        
      function pollDataSource() {

        $.getJSON('http://<?php echo $_SERVER['SERVER_ADDR']; ?>/api.php', function(data) {
          myRawValue = data.raw;
          myPercentValue = data.linear * 100;
        });

        data_history = appendRollingValue(data_history, history_length, myRawValue);
        chart_history.draw(data_history, options_history);

        data_gauge.setValue(0, 1, myPercentValue);
        chart_gauge.draw(data_gauge, options_gauge);
      }
        
      function drawChart() {

        data_gauge = google.visualization.arrayToDataTable([
          ['Label', 'Value'],
          ['Ozon', 80]
        ]);

        options_gauge = {
          width: 300, 
          height: 200,
          minorTicks: 5
        };

        chart_gauge = new google.visualization.Gauge(document.getElementById('chart_gauge'));
        chart_gauge.draw(data_gauge, options_gauge);

        // second chart
        options_history = {
          title: 'Ozon Concentration',
          vAxis: {
            title: 'Accumulated Rating',
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
      <div id="chart_history" style="width: 300px; height: 300px;"></div>
      <input type="button" value="more" onclick="changeBufferSize(30)" />
      <input type="button" value="less" onclick="changeBufferSize(-30)" />
      </br>
      <input type="button" value="slower" onClick="changePollRate(500)" />
      <input type="button" value="faster" onClick="changePollRate)-500)" />
    </center>
  </body>
</html>
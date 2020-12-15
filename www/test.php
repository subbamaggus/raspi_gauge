<?php

require("config.php");

?><html>
  <head>
    <meta name="viewport" content="initial-scale=1, maximum-scale=2">
    <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
    <script src="https://code.jquery.com/jquery-3.2.1.min.js"></script>
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    <script type="text/javascript">
      google.charts.load('current', {'packages':['gauge']});
      google.charts.setOnLoadCallback(drawChart);

      google.charts.load('current', {'packages':['corechart']});
      google.charts.setOnLoadCallback(drawChart);

      var myRawValue = 0;
      var myPercentValue = 0;

      function appendRollingValue(data, length, last_value) {
        for(var i = 0; i < (length - 1); i++) {
          data.setValue(i, 1, data.getValue(i+1, 1));
        }

        data.setValue((length - 1), 1, last_value);

        return data;
      }

      function drawChart() {


        var data = google.visualization.arrayToDataTable([
          ['Label', 'Value'],
          ['Ozon', 80]
        ]);

        var options = {
          width: 400, height: 240,
          minorTicks: 5
        };

        var chart = new google.visualization.Gauge(document.getElementById('chart_div'));

        chart.draw(data, options);


        // second chart
        var history_length = <?php echo $DEFAULT_HISTORY_LENGTH; ?>;

        var options2 = {
          title: 'Ozon Concentration',
          vAxis: {title: 'Accumulated Rating'},
          isStacked: true
        };

        var chart2 = new google.visualization.SteppedAreaChart(document.getElementById('chart_div_step'));

        var data_head = ['Time',  'Raw'];
        var data_body = [data_head];

        for(var i = history_length; i > 0; i--) {
            data_body.push(['' + i, 0]);
        }

        var data2 = google.visualization.arrayToDataTable(data_body);

        chart2.draw(data2, options2);


        setInterval(function() {

          $.getJSON('http://<?php echo $_SERVER['SERVER_ADDR']; ?>/api.php', function(data) {
            myRawValue = data.raw;
            myPercentValue = data.linear * 100;
          });

          data2 = appendRollingValue(data2, history_length, myRawValue);
          chart2.draw(data2, options2);

          data.setValue(0, 1, myPercentValue);
          chart.draw(data, options);
        }, 1000);
      }
    </script>
  </head>
  <body>
    <center>
      <div id="chart_div" style="width: 400px; height: 240px;"></div>
      <div id="chart_div_step" style="width: 400px; height: 300px;"></div>
    </center>
  </body>
</html>
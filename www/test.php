<html>
  <head>
    <meta name="viewport" content="initial-scale=2, maximum-scale=2">
    <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
    <script src="https://code.jquery.com/jquery-3.2.1.min.js"></script>
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    <script type="text/javascript">
      google.charts.load('current', {'packages':['gauge']});
      google.charts.setOnLoadCallback(drawChart);

      google.charts.load('current', {'packages':['corechart']});
      google.charts.setOnLoadCallback(drawChart);

      var myValue = 0;

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
          width: 400, height: 120,
          redFrom: 90, redTo: 100,
          yellowFrom:75, yellowTo: 90,
          minorTicks: 5
        };

        var chart = new google.visualization.Gauge(document.getElementById('chart_div'));

        chart.draw(data, options);


        // second chart
        var history_length = 40;

        var options2 = {
          title: 'Ozon Concentration',
          vAxis: {title: 'Accumulated Rating'},
          isStacked: true
        };

        var chart2 = new google.visualization.SteppedAreaChart(document.getElementById('chart_div_step'));

        var data_head = ['Time',  'Ozon Concentration'];
        var data_body = [data_head];

        for(var i = history_length; i > 0; i--) {
            data_body.push(['' + i, 0]);
        }

        var data2 = google.visualization.arrayToDataTable(data_body);

        chart2.draw(data2, options2);


        setInterval(function() {

          $.getJSON('http://<?php echo $_SERVER['SERVER_ADDR']; ?>/api.php', function(data) {
            myValue = data.value;
          });

          data2 = appendRollingValue(data2, history_length, myValue);
          chart2.draw(data2, options2);

          data.setValue(0, 1, myValue);
          chart.draw(data, options);
        }, 1000);
      }
    </script>
  </head>
  <body>
    <center>
      <div id="chart_div" style="width: 200px; height: 120px;"></div>
      <div id="chart_div_step" style="width: 200px; height: 150px;"></div>
    </center>
  </body>
</html>
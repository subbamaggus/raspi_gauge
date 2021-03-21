google.charts.load('current', {'packages':['corechart','gauge']});
google.charts.setOnLoadCallback(drawChart);

var history_length = 180;
var pollingRate = 1000;

var ring_buffer = initRingBuffer(history_length);
var min_ring_buffer_size = 1;
  
var history_chart;
var history_data;
var history_options;
  
var gauge_chart;
var gauge_data;
var gauge_options;

var intervalID;
  
function initRingBuffer(length) {
  if(length < min_ring_buffer_size)
    length = min_ring_buffer_size;
    
  var ring_buffer_header = ['Time',  'Data'];
  var ring_buffer_l = [ring_buffer_header];

  for(var i = length; i > 0; i--) {
      ring_buffer_l.push(['' + i, 0]);
  }
  return ring_buffer_l;
}

function addToRingBuffer(data, length, last_value) {
  if(length < min_ring_buffer_size)
    length = min_ring_buffer_size;
    
  for(var i = 0; i < (length - 1); i++) {
    data.setValue(i, 1, data.getValue(i+1, 1));
  }

  data.setValue((length - 1), 1, last_value);

  return data;
}
  
function changeRingBufferSize(diff) {
  history_length = history_length + diff;
  
  if (history_length < min_ring_buffer_size) {
    history_length = 0;
  }
  
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

  history_data = addToRingBuffer(history_data, history_length, DataObject.raw);
  history_chart.draw(history_data, history_options);

  gauge_data.setValue(0, 1, DataObject.raw);
  gauge_chart.draw(gauge_data, gauge_options);
}

function changePollRate(diff) {
  clearInterval(intervalID);
  pollingRate = pollingRate + diff;
  
  if(pollingRate < 500)
    pollingRate = 500;
    
  intervalID = setInterval(pollDataSource, pollingRate);

  console.log('changePollRate:' + pollingRate);
  document.getElementById("refresh_rate").innerHTML = "RefreshRate: " + pollingRate;
}

function drawChart() {

  gauge_data = google.visualization.arrayToDataTable([
    ['Label', 'Value'],
    ['RAW', 0]
  ]);

  // source for yellow and red: https://www.lfu.bayern.de/luft/doc/ozoninfo.pdf
  gauge_options = {
    width: 300, 
    height: 200,
    max: 32752,
    minorTicks: 5
  };

  gauge_chart = new google.visualization.Gauge(document.getElementById('gauge_chart'));
  gauge_chart.draw(gauge_data, gauge_options);

  // second chart
  history_options = {
    title: 'RAW',
    legend: { position: 'bottom' },
    vAxis: {
      minValue: 0,
      maxValue: 32752
    },
    isStacked: true
  };

  history_chart = new google.visualization.SteppedAreaChart(document.getElementById('history_chart'));
  history_data = google.visualization.arrayToDataTable(ring_buffer);
  history_chart.draw(history_data, history_options);

  document.getElementById("refresh_rate").innerHTML = "RefreshRate: " + pollingRate;
  intervalID = setInterval(pollDataSource, pollingRate);
}

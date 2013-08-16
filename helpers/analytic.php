<html>
  <head>
      <link rel="stylesheet" type="text/css" href="https://visapi-gadgets.googlecode.com/svn/trunk/wordcloud/wc.css"/>
    <script type="text/javascript" src="https://visapi-gadgets.googlecode.com/svn/trunk/wordcloud/wc.js"></script>
    <link rel="stylesheet" type="text/css" href="/stylesheets/tc.css"/>
    <link rel="stylesheet" type="text/css" href="/stylesheets/base.css"/>
    <script type="text/javascript" src="/javascript/tc.js"></script>
    <script type='text/javascript' src='https://www.google.com/jsapi'></script>
    <script type="text/javascript" src="/javascript/jquery-1.7.1.min.js"></script>
    <script type='text/javascript'>
        LoadGoogle();
        LoadTc();
        function LoadGoogle()
        {
            if(typeof google != 'undefined' && google && google.load) {
                google.load('visualization', '1', {'packages':['annotatedtimeline', 'corechart', 'table']});
            }
        else
        {
            // Retry later...
                setTimeout(LoadGoogle, 5);
            }
        }
        function LoadTc()
        {
            if(typeof TermCloud == 'undefined' && !TermCloud)
            {
                setTimeout(LoadTc, 5);
            }
        }
        function objectLength(obj) {
                  var result = 0;
                  for(var prop in obj) {
                    if (obj.hasOwnProperty(prop)) {
                    // or Object.prototype.hasOwnProperty.call(obj, prop)
                      result++;
                    }
                  }
                  return result;
        }
          function drawChart(str) {
            var jsonData = $.ajax({
                url: "getData.php",
                data: {"str": str},
                dataType:"json",
                async: false
                }).responseText;
              // Create our data table out of JSON data loaded from server.
              var ajsn = $.parseJSON(jsonData);
              var size = objectLength(ajsn) - 2; // because the last 2 elements is for words_count_array and master_string_array (see getData.php)
              var data = new google.visualization.DataTable();
              var data2 = new google.visualization.DataTable();
              data.addColumn('datetime', 'Date');
              data.addColumn('number', 'Facebook');
              data.addColumn('string', 'title1');
              data.addColumn('string', 'text1');
              data.addColumn('number', 'Twitter');
              data.addColumn('string', 'title2');
              data.addColumn('string', 'text2');
              
              data2.addColumn('string', 'Social media');
              data2.addColumn('number', 'Mentions');
              var totalFb = 0;
              var totalTw = 0;
              for (var i=0; i < size; i++) {
                  //console.log(ajsn[i]);
              //console.log(ajsn[i][0]);
              //console.log(ajsn[i][1]);
              //console.log(ajsn[i][2]);
              var t = ajsn[i];
              var arr_date = t[0].split(",");
              var colA = null;
              var colAtxt = null;
              var colB = null;
              var colBtxt = null;
              if (i == 0) {
                  colA = "Start";
                colAtxt = "Date that starts collecting data";
              } else if (i == size - 1) {
                  colB = "Finish";
              colBtxt = "Date that ends collecting data";
              }
              data.addRows([
                [new Date(arr_date[0], arr_date[1], arr_date[2], arr_date[3], 0, 0), t[1], colA, colAtxt, t[2], colB, colBtxt]
              ]);
                 totalFb += t[1];
                 totalTw += t[2];                   
              }
              data2.addRows([["Facebook",totalFb], ["Twitter", totalTw]]);
              var annotatedtimeline = new google.visualization.AnnotatedTimeLine(
                  document.getElementById('div_timeline'));
              var options = {   'title':'Total Social Media Mentions', 
            //'legend':'left',
            'is3D':false,
            'displayAnnotations': true,
            'showRowNumber': true};
              annotatedtimeline.draw(data, options);
              var piechart = new google.visualization.PieChart(
                  document.getElementById('div_pie'));
              piechart.draw(data2, options);
              
              // Word clouds
              var words = ajsn[size];
              var contents = ajsn[size+1];
              //var data3 = new google.visualization.DataTable(); // TermCloud
              var data4 = new google.visualization.DataTable();
              var data5 = new google.visualization.DataTable();
              //data3.addColumn('string', 'Label');
              //data3.addColumn('number', 'Value');
              //data3.addColumn('string', 'Link');
              data4.addColumn('string', 'Word');
              data4.addColumn('number', 'Repeat times');
              data5.addColumn('string', 'data');
              for (var key in words) {
                    //data3.addRows([[key, words[key] , null]]);
                    data4.addRows([[key, words[key]]]);
                }
              var dataSize = objectLength(contents.data);
                for (var i = 0; i < dataSize; i++) {
                    if(contents.data[i] && typeof contents.data[i] != 'undefined') {
                        data5.addRows([[contents.data[i]]]);
                    }
                }
                
              //var termcloud = new TermCloud(document.getElementById("div_tcloud"));
              //termcloud.draw(data3, null);
              var wordcloud = new WordCloud(document.getElementById("div_wcloud"));
              wordcloud.draw(data5, {stopWords: 'a an and is or the of for to www i it href http his her them their your yours what this why then how who which whose whom when than whatever any'});
              var table = new google.visualization.Table(document.getElementById('div_table'));
              table.draw(data4, options);
         }
    </script>
  </head>
  <body onload="drawChart('wws');">
    <h1>Old archived keyword data:</h1>
    <p>
        These data are real data, collected by W&S Market Research using the internal tool, VinaSocial.<br/>
        All usage must be informed to W&S and able to publish upon permission.
    </p>
    <form id="frmtest" name="frmtest" method="post">
        Choose keyword: <select size="1" onchange="drawChart(this.value);">
            <option value="wws">WWS</option>
            <option value="evernote">evernote</option>
            <option value="nielsen">Nielsen</option>
            <option value="starbuck+vn">Starbuck VN</option>
        </select>
    </form>
    <div id='div_graph' class="clearfix">
        <div id='div_mention' class="ana-mention">
            <h3>Some detailed mentions:</h3>
            <p>Tada</p>
        </div>
        <div id='div_timeline' style='width: 800px; height: 320px;'></div>
        <br/>
        <div id='div_pie' style='width: 720px; height: 320px;'></div>
        <br/>
        <h1>Most repeated words in social mentions by users:</h1>
        <div id='div_table' style='width: 720px; height: 500px;'></div>
        <!--<div id='div_tcloud' style='width: 720px; height: 100px;'></div> -->
        <br/>
        <div id='div_wcloud' style='width: 720px; height: 100px;'></div>
    </div>
  </body>
</html>
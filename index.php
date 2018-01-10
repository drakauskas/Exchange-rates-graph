<?php require_once("pdo.inc.php"); ?>

<!DOCTYPE html>
<html >
<head>
  <meta charset="UTF-8">
  <title> Šveicarijos franko kurso grafikas </title>
  
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/normalize/5.0.0/normalize.min.css">

  
  <style>
      /* NOTE: The styles were added inline because Prefixfree needs access to your styles and they must be inlined if they are on local disk! */
      @import url("http://fonts.googleapis.com/css?family=Open+Sans:300,400,700");
      @import url("http://netdna.bootstrapcdn.com/font-awesome/4.0.3/css/font-awesome.css");
* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

:before, :after {
  content: '';
  display: block;
  position: absolute;
  box-sizing: border-box;
}

html, body {
  height: 100%;
}

body {
  padding: 50px 0;
  font: 14px/1 'Open Sans', sans-serif;
  color: #777;
  background: #ddd;
}

p {
  line-height: 1.8;
}

.wrapper {
  width: 600px;
  margin: 0 auto;
  border-radius: 4px;
  background: #fff;
  box-shadow: 0 1px 2px rgba(0, 0, 0, 0.3);
}

.title {
  height: 60px;
  padding: 0 30px;
  font-size: 18px;
  line-height: 60px;
}

.chart {
  height: 500px;
  padding: 20px;
  background: linear-gradient(to bottom, #345 0%, #234 100%);
}

.footer {
  padding: 30px;
}

    </style>

  <script src="https://cdnjs.cloudflare.com/ajax/libs/prefixfree/1.0.7/prefixfree.min.js"></script>

</head>

<body>

  <div class='wrapper'>
  <div class='title'>
    Šveicarijos franko kurso grafikas
  </div>
  <div class='chart' id='p1'>
    <canvas id='c1'></canvas>
  </div>
  <div class='footer'>
    <p>
      Šveicarijos franko kurso grafikas.
    </p>
  </div>
</div>
<?php

class mycULR
{        
        public $urldata;

        protected function curlUsingPost($url)
        {
            //open connection
            $ch = curl_init();

            //set the url, number of POST vars, POST data
            curl_setopt($ch,CURLOPT_URL,$url);

            curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,10); # timeout after 10 seconds, you can increase it

            curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);  # Set curl to return the data instead of printing it to the browser.
            curl_setopt($ch,  CURLOPT_USERAGENT , "Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 6.1)"); # Some server may refuse your request if you dont pass user agent

            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

            //execute post
            $result = curl_exec($ch);

            //close connection
            curl_close($ch);
            return $result;
        }
        
        public function getdataurl ($myurl)
        {
            $this->urldata = $this->curlUsingPost($myurl);
        }
};


class VK extends mycULR
{ 

  public $vkinfofromurl;

  public $stringjs = '';
  public $stringdate = '';

  public function getdata ($urllink)
  {
    $this->getdataurl($urllink);
  }

  public function getvkstring()
  {
    
    $stringdata = $this->urldata;

    preg_match('/CHF(.*);/U', $stringdata, $vkinfo);

    $this->vkinfofromurl = $vkinfo;
  }

  public function getvkvalues_and_insert ()
  {
    $chf = explode(",", $this->vkinfofromurl[0]);

    $dbh = new PDO ("mysql:host=".DB_HOST, "dbname=".DB_NAME, DB_USER, DB_PASS);
    $stmt = $dbh->prepare("INSERT INTO vk SET grynaisiais_pd = :grynaisiais_pd, grynaisiais_pk = :grynaisiais_pk, negrynais_pd = :negrynais_pd, negrynais_pk = :negrynais_pk, data = :data");
    
    $chf[6] = rtrim($chf[6], ');'); $mysqltime = date ("Y-m-d");
    $stmt ->bindParam (':grynaisiais_pd', $chf[6]);
    $stmt->bindParam (':grynaisiais_pk', $chf[5]);
    $stmt->bindParam (':negrynais_pd',   $chf[4]);
    $stmt->bindParam (':negrynais_pk',   $chf[3]);
    $stmt->bindParam (':data',   $mysqltime);
    $stmt->execute();
    echo $mysqltime;
  }

  public function explode_values(){
    try {
          $dbh = new PDO("mysql:host=localhost;dbname=drakausk_valiutukursai", "drakausk_vkuser", "Vkuserpass7");

         }
    catch (PDOException $exception) {
          
          printf("Failed to obtain database handle %s", $exception->getMessage()); 
        
        };

      $stmt = $dbh->query("SELECT * FROM vk");
    
      $resultset = $stmt->fetchAll(PDO::FETCH_ASSOC);

      echo sizeof($resultset);
      $i=0;
      foreach ($resultset as $row) 
      {
          $i++;
          if ($i == sizeof($resultset)) 
          { 
            $this->stringjs   .=  $row['grynaisiais_pd'];
            $this->stringdate   .=  "\"" . $row['data'] . "\"";
          }
          else 
          {
              $this->stringjs   .=  $row['grynaisiais_pd'].",";
              $this->stringdate   .=  "\"" . $row['data'] . "\",";
          }  
      }
  }
};

$valkur = new VK ();
$valkur->getdata('https://e.seb.lt/mainib/web.p?act=currencyrates&lang=LIT');
$valkur->getvkstring();
$valkur->explode_values();

?>
<script src='http://cdnjs.cloudflare.com/ajax/libs/Chart.js/0.2.0/Chart.min.js'></script>
<script>

var c1 = document.getElementById("c1");
var parent = document.getElementById("p1");

c1.width = parent.offsetWidth; - 40;
c1.height = parent.offsetHeight - 40;

var data1 = {
  labels : [<?php echo $valkur->stringdate; ?>],
  datasets : [
    {
      fillColor : "rgba(255,255,255,.1)",
      strokeColor : "rgba(255,255,255,1)",
      pointColor : "#123",
      pointStrokeColor : "rgba(255,255,255,1)",
      data : [<?php echo $valkur->stringjs ?>]
    }
  ]
}

var options1 = {
  scaleFontColor : "rgba(255,255,255,1)",
  scaleLineColor : "rgba(255,255,255,1)",
  scaleGridLineColor : "transparent",
  bezierCurve : false,
  scaleOverride : true,
  scaleSteps : 40,
  scaleStepWidth : 0.001,
  scaleStartValue : 1.110
}

new Chart(c1.getContext("2d")).Line(data1,options1)

</script>
</body>
</html>
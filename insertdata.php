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


        /*public function show_date ($data)
        {
            preg_match_all('/<p class="ora">(.*)<\/p>/',            $data, $oras);
            preg_match_all('/<p class="val">(.*)<\/p>/',            $data, $laikas);
            preg_match_all('/<p class="val_day">(.*)<\/p>/',        $data, $diena);
            preg_match_all('/Vėjas:(.*)<\/p>/',        $data, $vejas);
            preg_match_all('/<p class="temp"><strong>(.*)<\/p>/',   $data, $tempe);
        }*/
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
    //echo $stringdata;

    preg_match('/CHF(.*);/U', $stringdata, $vkinfo);
    
    /*
    echo "<pre>";
    //print_r($vkinfo);
    echo"</pre>";
    */

    $this->vkinfofromurl = $vkinfo;
  }

  public function getvkvalues_and_insert ()
  {
    $chf = explode(",", $this->vkinfofromurl[0]);

    $dbh = new PDO("mysql:host=localhost;dbname=drakausk_valiutukursai", "drakausk_vkuser", "Vkuserpass7");
    $stmt = $dbh->prepare("INSERT INTO vk SET grynaisiais_pd = :grynaisiais_pd, grynaisiais_pk = :grynaisiais_pk, negrynais_pd = :negrynais_pd, negrynais_pk = :negrynais_pk, data = :data");
    
    $chf[6] = rtrim($chf[6], ');'); $mysqltime = date ("Y-m-d");
    $stmt ->bindParam (':grynaisiais_pd', $chf[6]);
    $stmt->bindParam (':grynaisiais_pk', $chf[5]);
    $stmt->bindParam (':negrynais_pd',   $chf[4]);
    $stmt->bindParam (':negrynais_pk',   $chf[3]);
    $stmt->bindParam (':data',   $mysqltime);
    $stmt->execute();
    //echo $mysqltime;
    /* echo "chf 1 " . $chf[2] . "<br>"; 
    echo "chf 1 " . $chf[3] . "<br>";
    echo "chf 1 " . $chf[4] . "<br>";
    echo "chf 1 " . $chf[5] . "<br>";
    echo "chf 1 " . $chf[6] . "<br>";
    echo "chf 1 " . $chf[6] . "<br>";
    */
    
    /* 
    for ($i=2; $i<sizeof($chf); $i++)
    {
      echo $chf[$i] . "<br/>";

    };
    */
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
    
    /*
      echo "stringjs " . $this->stringjs . "<br>";
      echo "stringdate " .$this ->stringdate . "<br>";
    */

    /*
    echo " <pre>";
    print_r($resultset);
    echo " </pre>";

    */
  }
};

$valkur = new VK ();
$valkur->getdata('https://e.seb.lt/mainib/web.p?act=currencyrates&lang=LIT');
$valkur->getvkstring();
$valkur->getvkvalues_and_insert();

?>

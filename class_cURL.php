<?php

class cULR
{
        
        public $urldata;

        public getdataurl ($myurl)
        {
            this->urldata = curlUsingPost($myurl);
        };

        private function curlUsingPost($url)
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
        };

         public function show_date ($data)
        {
            preg_match_all('/<p class="ora">(.*)<\/p>/',            $data, $oras);
            preg_match_all('/<p class="val">(.*)<\/p>/',            $data, $laikas);
            preg_match_all('/<p class="val_day">(.*)<\/p>/',        $data, $diena);
            preg_match_all('/Vėjas:(.*)<\/p>/',        $data, $vejas);
            preg_match_all('/<p class="temp"><strong>(.*)<\/p>/',   $data, $tempe);
        

      };
};

?>
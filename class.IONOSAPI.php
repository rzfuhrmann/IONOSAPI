<?php
    /**
     * Simple PHP class to call the IONOS Hosting API / IONOS Developer API
     * 
     * @author      Sebastian Fuhrmann <sebastian.fuhrmann@rz-fuhrmann.de>
     * @copyright   (C) 2021 Rechenzentrum Fuhrmann Inh. Sebastian Fuhrmann
     * @version     2021-02-03
     * 
     * 
     * @TODO
     * - add inheritance logic to support further APIs IONOSAPI => IONOS_DNS_API
     * - add object orientation
     * - add all functions
     * - improve caching functionality
     * - add possibility to retreive usage limits/used tokens
     */

    class IONOSAPI {
        private $secret; 
        private $publicprefix; 

        private $endpoint = 'https://api.hosting.ionos.com/dns';

        public function __construct($publicprefix, $secret){
            // @TODO: Error handling!
            $this->secret = $secret; 
            $this->publicprefix = $publicprefix;
        }

        private function doRequest($method, $path, $body = null, $config = array()){

            $url = $this->endpoint.$path; 
            $cache_fn = __DIR__.'/cache/'.md5($this->secret.$url.$method).'.json';

            // simple caching: Generate checksum based on request; find a file with that name. 
            if (
                // writing actions can't be cached
                strtoupper($method) == 'GET' 
                // cache file must exists ... 
                && file_exists($cache_fn) 
                // ... and needs to be new enough
                // @todo variable caching time
                && filemtime($cache_fn) > time()-60*60*1

                // && isCacheable
            ){
                // todo: check for valid content
                $res = json_decode(file_get_contents($cache_fn), true);
                $info = array("http_code" => 200); 
            } else {
                $ch = curl_init(); 

                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, strtoupper($method)); 
                curl_setopt($ch, CURLOPT_URL, $url); 

                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                    // API will return HTTP 503 and HTML code if we don't provide an user name
                    'User-Agent: IONOSAPI PHP class - https://github.com/rzfuhrmann/IONOSAPI/',
                    'accept: application/json',
                    'X-API-Key: '.$this->publicprefix.'.'.$this->secret 
                ));

                $rawres = curl_exec($ch); 
                $info = curl_getinfo($ch); 

                curl_close($ch); 

                if ($info["http_code"] == 200 && ($res = json_decode($rawres, true))){
                    // if response seems to be correct => cache it!
                    file_put_contents($cache_fn, $rawres);
                } else {
                    throw new Exception("Error in communication with IONOS API: ".$rawres, 1);
                }
            }

            return $res; 
        }

        /**
         * Retrieves a list of all DNS zones and their records
         */
        public function getZones(){
            /**
             * array(3) {
             *   ["name"] => string() "domainname.de"
             *   ["id"]   => string() "avcd123e-9876-12ab-123a-1a12345678a4"
             *   ["type"] => string() "NATIVE"
             * }
             */
            $zones = $this->doRequest("GET", "/v1/zones");

            foreach ($zones as $z => $zone){
                $zoneDetails = $this->doRequest("GET", "/v1/zones/".$zone["id"]);
                $zones[$z] = $zoneDetails;
            }

            return $zones; 
        }
    }
?>
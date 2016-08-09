<?php
/**
 * Created by PhpStorm.
 * User: robert
 * Date: 07.08.2016
 * Time: 13:25
 */
class SupportAPI extends Object {

    private static $_instance = null;
    private static $cache_lifetime = 300;
    private static $cache_dir = '/tmp';
    private static $api_endpoint = null;
    private static $web_endpoint = null;
    private static $auth = null;

    private $_client = null;


    public function __construct() {
        $cfg = $this->config();
        if( ! $cfg->api_endpoint ) {
            user_error('API endpoint for codebasehq is not defined', E_USER_NOTICE);
        }
        if( ! $cfg->web_endpoint ) {
            user_error('Web endpoint for codebasehq is not defined', E_USER_NOTICE);
        }
        if( ! $cfg->auth['username'] ) {
            user_error('u8sername for codebasehq is not defined', E_USER_NOTICE);
        }
        if( ! $cfg->auth['token'] ) {
            user_error('token for codebasehq is not defined', E_USER_NOTICE);
        }
        $this->_client = new Zend_Http_Client($cfg->api_endpoint, array(

        ));
    }
    private function __clone() {

    }

    public static function getInstance() {
        if (null === self::$_instance) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    protected function cacheKey($path, $query=null) {
        $str[] = $path;
        if( $query ) {
            $str[] = preg_replace('/[^0-9A-Z]/i', '_', $query);
        }
        $cacheKey = implode("_", $str);
        return $cacheKey;
    }

    private function _request($path, $returnArrayKey=null, $query=null) {
        $uri = $this->_client->getUri();
        $uri->setPath( implode('/', array($uri->getPath(), $path)) );

        if( $query ) {
            $uri->setQuery(array("query"=>$query));
        }
        $this->_client->setUri($uri);
        $this->_client->setHeaders(array(
            'Accept' => 'application/xml',
            'Content-type' => 'application/xml',
        ));

        $this->_client->setAuth($this->config()->auth['username'], $this->config()->auth['token']);
        $frontendOptions = array(
            'lifetime' => $this->config()->cache_lifetime, // Lebensdauer des Caches
            'automatic_serialization' => true
        );

        $backendOptions = array(
            'cache_dir' => $this->config()->cache_dir // Verzeichnis, in welches die Cache Dateien kommen
        );

        $cache = Zend_Cache::factory('Core',
            'File',
            $frontendOptions,
            $backendOptions);
        $cacheKey = $this->cacheKey($path,$query);

        if(!$result = $cache->load($cacheKey)) {


            $response = $this->_client->request();
            $body = $response->getBody();
            $cache->save($body, $cacheKey);

        } else {

            // Cache hit! Ausgeben, damit wir es wissen
            //echo "Der ist vom Cache!<hr>\n\n";
            $body = $result;

        }

        $myxml = simplexml_load_string($body);

        $xmlArray = self::toArray($myxml);

        return $returnArrayKey && array_key_exists($returnArrayKey, $xmlArray)? $xmlArray[$returnArrayKey] : $xmlArray ;
    }

    private static function toArray($xml, $nodeName = null) {
        $array = array();
        if( $nodeName ) {
            if( $xml->$nodeName ) foreach( $xml->$nodeName as $el ) {
                $array[] = self::toArray($el);
            }
        } else {
            $array = json_decode(json_encode($xml), TRUE);
            foreach (array_slice($array, 0) as $key => $value) {
                if (empty($value)) $array[$key] = NULL;
                elseif (is_array($value)) $array[$key] = self::toArray($value);
            }
        }
        return $array;
    }

    public function tickets($query=null) {
        return $this->_request('tickets', 'ticket', $query);
    }


    public static function toArrayList( $array ) {
        $ret = ArrayList::create();
        // Hack
        // when the response has only 1 item, it is not an indexed array
        if( array_keys($array) !== range(0, count($array) - 1) ) {
            $new_array = array();
            $new_array[0] = $array;
            $array=$new_array;
        }
        if( $array ) foreach( $array as $a ) {
            if( is_array($a) ) $ret->push( ViewableTicket::create($a) );
        }
        return $ret;
    }

}
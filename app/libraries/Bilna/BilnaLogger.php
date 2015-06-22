<?php

namespace Bilna\Libraries;

/**
 * Creator: Akbar
 * Date: 5/18/2015
 * Time: 5:01 PM
 */
use Phalcon\Logger\Adapter\File as FileAdapter;
use Phalcon\Logger\Formatter\Line as LineFormatter;

if (!function_exists('getallheaders')) {

    function getallheaders() {
        $headers = '';
        foreach ($_SERVER as $name => $value) {
            if (substr($name, 0, 5) == 'HTTP_') {
                $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
            }
        }
        return $headers;
    }

}

class BilnaLogger {

    protected static $__startTime;

    /**
     * set the $__startTime
     */
    public static function start() {
        if (!static::$__startTime) {
            static::$__startTime = static::microtime_float();
            error_reporting(E_ALL);
            set_error_handler(function ($severity, $message, $filename, $lineno)//exceptions_error_handler
            {
                if (error_reporting() == 0) {
                    return;
                }
                if (error_reporting() & $severity) {
//		$msg = 'Error (line ' . $lineno . ' on ' . $filename . ') => ' . $message;
//		logme($msg);

                    throw new \ErrorException('Error (line ' . $lineno . ' on ' . $filename . ') => ' . $message, 0, $severity, $filename, $lineno);
                }
            });
        }
    }

    /**
     * get time elapsed from $__startTime if $__startTime exist, else return -1
     * @return float|int
     */
    public static function getElapseTime() {
        return empty(static::$__startTime) ? -1 : static::microtime_float() - static::$__startTime;
    }

    /**
     * get system current timestamp in microtime format
     * @return float
     */
    public static function microtime_float() {
        list($usec, $sec) = explode(" ", microtime());
        return ((float) $usec + (float) $sec);
    }

    public static function url_origin($s, $use_forwarded_host = false) {
        $ssl = (!empty($s['HTTPS']) && $s['HTTPS'] == 'on') ? true : false;
        $sp = strtolower($s['SERVER_PROTOCOL']);
        $protocol = substr($sp, 0, strpos($sp, '/')) . (($ssl) ? 's' : '');
        $port = $s['SERVER_PORT'];
        $port = ((!$ssl && $port == '80') || ($ssl && $port == '443')) ? '' : ':' . $port;
        $host = ($use_forwarded_host && isset($s['HTTP_X_FORWARDED_HOST'])) ? $s['HTTP_X_FORWARDED_HOST'] : (isset($s['HTTP_HOST']) ? $s['HTTP_HOST'] : null);
        $host = isset($host) ? $host : $s['SERVER_NAME'] . $port;
        return $protocol . '://' . $host;
    }

    public static function full_url($s, $use_forwarded_host = false) {
        return static::url_origin($s, $use_forwarded_host) . $s['REQUEST_URI'];
    }

    public static function get_server_load() {
        $load = -1;
        if (stristr(PHP_OS, 'win') && class_exists('\COM')) {

            $wmi = new \COM("Winmgmts://");
            $server = $wmi->execquery("SELECT LoadPercentage FROM Win32_Processor");

            $cpu_num = 0;
            $load_total = 0;

            foreach ($server as $cpu) {
                $cpu_num++;
                $load_total += $cpu->loadpercentage;
            }

            $load = round($load_total / $cpu_num);
        } elseif (function_exists('sys_getloadavg')) {

            $sys_load = sys_getloadavg();
            $load = $sys_load[0];
        }

        return (int) $load;
    }

    /**
     * logger me
     * @param string $data json
     * @param string $error
     * @param \Api\Core\Controllers\CoreController $container
     *
     * @return array|null
     */
    public static function logme($data = null, $error = null, $container = null) {
        flush();
        if ($container && is_a($container, '\Api\Core\Controllers\CoreController')) {
            try {
                $config = $container->config;
            } catch (\Exception $e) {
                
            }
        } else
            $container = null;

        if (!isset($config)) {
            try {
                $config = include __DIR__ . '/../apps/core/config/config.php';
            } catch (\Exception $e) {
                die();
//				die($e->getMessage());
            }
        }

//		$body = urldecode(file_get_contents('php://input'));
//		return static::iologger($config->logger->path, $data, $error);

        $initconfig = array_intersect_key($config->elastica->toArray(), array_flip(['host', 'port', 'url', 'proxy', 'transport', 'curl',
            'persistent', 'timeout', 'connections', 'roundRobin', 'log', 'retryOnConflict']));


        try {
            $elasticaIndex = (new \Elastica\Client($initconfig))->getIndex($config->elastica->index);
            $isExist = $elasticaIndex->exists();
            if (!$isExist)
                $elasticaIndex->create();
            $elasticaType = $elasticaIndex
                    ->getType($config->elastica->type);

//			die(var_dump(isset($elasticaIndex->getMapping()[$config->elastica->type])));
//			die(var_dump($elasticaIndex->getMapping()));

            if (!isset($elasticaIndex->getMapping()[$config->elastica->type])) {//!$isExist){
                $mapping = new \Elastica\Type\Mapping();
                $mapping->setType($elasticaType);
//				$mapping->setParam('index_analyzer', 'indexAnalyzer');
//				$mapping->setParam('search_analyzer', 'searchAnalyzer');
// Define boost field
//				$mapping->setParam('_boost', array('name' => '_boost', 'null_value' => 1.0));
//				$mapping->setParam('dynamic_date_formats', array('yyyy-MM-dd HH:mm:ssZ','yyyy-MM-dd HH:mm:ss'));
                $mapping->setParam('_timestamp', array('enabled' => true, "store" => true,
                    "path" => $config->elastica->type . '.created_date',
//													   "format" => "yyyy-MM-dd HH:mm:ssZ",
//					"ignore_missing" => false//, "default" => '2015-02-01 01:01:01+07:00'
                ));

// Set mapping
                $mapping->setProperties(array(
                    'created_date' => array('type' => 'date', "format" => "YYYY-MM-dd HH:mm:ssZ",
                        'include_in_all' => TRUE),
                    'flags' => array('type' => 'string', 'include_in_all' => TRUE),
//					'id'      => array('type' => 'integer', 'include_in_all' => FALSE),
//					'user'    => array(
//						'type' => 'object',
//						'properties' => array(
//							'name'      => array('type' => 'string', 'include_in_all' => TRUE),
//							'fullName'  => array('type' => 'string', 'include_in_all' => TRUE)
//						),
//					),
//					'msg'     => array('type' => 'string', 'include_in_all' => TRUE),
//					'tstamp'  => array('type' => 'date', 'include_in_all' => FALSE),
//					'_boost'  => array('type' => 'float', 'include_in_all' => FALSE)
                ));

// Send mapping to type
                $mapping->send();
            }

            /**
             * logger
             *
             * request
             * response code
             * response
             * request time
             *
             * ip
             * url
             * time
             *
             */
            $ip = $_SERVER['REMOTE_ADDR'];
            $body = urldecode(file_get_contents('php://input'));
            $method = $_SERVER['REQUEST_METHOD'];
            $url = static::full_url($_SERVER);
            //			$referer = $this->request->getHTTPReferer ();
            $arrheaders = getallheaders();

            $datetime = (new \Datetime('now', new \DateTimeZone('Asia/Jakarta')))->format('Y-m-d H:i:sP');

            if ($error !== null && trim($error)) {
                $datalog = ['memorypeak' => memory_get_peak_usage(1), 'cpuglobal' => static::get_server_load(), 'microtime' => static::microtime_float()];
                $datalog = array_merge($datalog, ['flags' => 'ioapi', 'created_date' => $datetime, 'log' => 'error', 'ip' => $ip, 'url' => "$method {$url}", 'headers' => $arrheaders,
                    'body' => $body, 'output' => (!is_array($error) && !is_object($error)) ? preg_replace('[\t\r\n\0]', ' ', $error) : json_encode($error), 'ptime' => static::getElapseTime()]
                );

                if ($container) {
                    if (isset($container->data->clientId))
                        $datalog['clientId'] = (string) $container->data->clientId;
                    if (isset($container->data->clientToken))
                        $datalog['clientToken'] = (string) $container->data->clientToken;
                }

                $elasticaType->addDocument(new \Elastica\Document('', $datalog));
            }

            if ($data !== null && (!is_object($data) || !is_array($data) || trim($data))) {
                $datalog = ['memorypeak' => memory_get_peak_usage(1), 'cpuglobal' => static::get_server_load(), 'microtime' => static::microtime_float()];

                $json = new \stdClass;
                if (is_scalar($data))
                    $json = json_decode($data); // [$referer]\t
                else
                    $json = (object) $data;
                if (isset($json->responseCodeDescription) && is_array($json->responseCodeDescription))
                    $json->responseCodeDescription = implode(', ', $json->responseCodeDescription);



                $datalog = array_merge($datalog, ['flags' => 'ioapi', 'created_date' => $datetime, 'log' => 'info',
                    'ip' => $ip, 'url' => "$method {$url}", 'headers' => $arrheaders,
                    'body' => $body,
                    'output' => (!is_array($data) && !is_object($data)) ? preg_replace('[\t\r\n\0]', ' ', $data) : json_encode($data),
                    'ptime' => static::getElapseTime()]);

                if ($container) {
                    if (isset($container->data->clientId))
                        $datalog['clientId'] = (string) $container->data->clientId;
                    if (isset($container->data->clientToken))
                        $datalog['clientToken'] = (string) $container->data->clientToken;
                }
                if (isset($json->requestId))
                    $datalog['requestId'] = $json->requestId;
                if (isset($json->responseCode))
                    $datalog['responseCode'] = $json->responseCode;
                if (isset($json->responseCodeDescription))
                    $datalog['responseCodeDescription'] = $json->responseCodeDescription;

                $elasticaType->addDocument(new \Elastica\Document('', $datalog));
            }

            $elasticaType->getIndex()->refresh();
            return isset($datalog) ? $datalog : null;
        } catch (\Exception $e) {

//			var_dump($datalog);
//			die(var_dump($e->getMessage()));
            return static::iologger($config->logger->path, $data, $error);
        }
        return null;
    }

    /**
     * fallback from ES if timeout
     * 		or use this to bypass big data dump limit
     * @param string $path to file dump
     * @param string $data json
     * @param string $error
     *
     * @return null
     */
    public static function iologger($path, $data = null, $error = null) {
//		$path = $this->config->logger->path;
        if (!is_dir($path)) {
            mkdir($path, 0777, TRUE);
        }
        if (!is_writable($path)) {
            $old = umask(0);
            @chmod($path, 0777);
            umask($old);
        }

        $logger = new FileAdapter($path . 'io_' . date("Ymd") . '.log'); //, array('mode' => 'w+')
        $logger->setFormatter(new LineFormatter("[%date%] [%type%] %message%"));
        $logger->begin();
        /**
         * logger
         *
         * request
         * response code
         * response
         * request time
         *
         * ip
         * url
         * time
         *
         */
        $ip = $_SERVER['REMOTE_ADDR'];
        $body = urldecode(file_get_contents('php://input'));
        $method = $_SERVER['REQUEST_METHOD'];
        $url = static::full_url($_SERVER);



        $json = new \stdClass;
        if (strpos(trim($body), 'data') === 0 && (list($strdata, $strjson) = explode('=', $body, 2)) && trim($strdata) == 'data' && $jsonInput = json_decode($strjson))
            ;
//			$referer = $this->request->getHTTPReferer ();
//		die(var_dump($body));

        $arrheaders = getallheaders();
        if ($error != null && trim($error)) {// [$referer]\t
            $logger->error("[" . (isset($jsonInput, $jsonInput->clientId) ? $jsonInput->clientId : '') . "] [" . (isset($jsonInput, $jsonInput->clientToken) ? $jsonInput->clientToken : '') . "] [$ip] [$method {$url}] " . json_encode(json_encode($arrheaders)) . "\t" . json_encode(strlen($body) > 200 ? json_encode($data) : $body) . "\t" . json_encode(preg_replace('[\t\r\n\0]', ' ', $error)) . "\t" . static::getElapseTime());
        }
        if ($data !== null && (!is_object($data) || !is_array($data) || trim($data))) {

            if (is_scalar($data))
                $json = json_decode($data); // [$referer]\t
            else
                $json = json_decode(json_encode($data));

            if (isset($json->responseCodeDescription) && is_array($json->responseCodeDescription))
                $json->responseCodeDescription = implode(', ', $json->responseCodeDescription);
            $logger->notice("[" . (isset($jsonInput, $jsonInput->clientId) ? $jsonInput->clientId : '') . "] [" . (isset($jsonInput, $jsonInput->clientToken) ? $jsonInput->clientToken : '') . "] [$ip] [$method {$url}] " . json_encode(json_encode($arrheaders)) . "\t" . json_encode(strlen($body) > 200 ? json_encode($data) : $body) . "\t" . json_encode(preg_replace('[\t\r\n\0]', ' ', ((!is_array($data) && !is_object($data)) ? $data : json_encode($data)))) . "\t" . (is_object($json) ? "[$json->responseCode] [" . json_encode(json_encode($json->responseCodeDescription)) . "]" : '') . "\t" . static::getElapseTime());
        }
        $logger->commit();

//		die('return');
        return null;
    }

    /**
     * global createKey for caching
     * @param array $parameters
     *
     * @return string
     */
    public function createKey(array $parameters) {
        $uniqueKey = [];

        unset($parameters['columns']);

        foreach ($parameters as $key => $value) {
            if (is_scalar($value)) {
                $uniqueKey[] = $key . ':' . $value;
            } else {
                if (is_array($value)) {
                    $uniqueKey[] = $key . ':[' . self::createKey($value) . ']';
                }
            }
        }

        return join(',', $uniqueKey);
    }

}

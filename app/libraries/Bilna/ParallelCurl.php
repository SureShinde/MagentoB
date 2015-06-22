<?php
/**
 * User: Akbar
 * Date: 6/18/2015
 * Time: 10:07 AM
 */

namespace Bilna\Libraries;


class ParallelCurl {
    public $max_requests;
    public $options;
    public $default_options;
    public $outstanding_requests;
    public $multi_handle;

    public function __construct($in_max_requests = 10, $in_options = array()) {
        $this->max_requests = $in_max_requests;
        $this->default_options = $in_options;
        $this->options = $in_options;

        $this->outstanding_requests = array();
        $this->multi_handle = curl_multi_init();
    }

    //Ensure all the requests finish nicely
    public function __destruct() {
        $this->finishAllRequests();
    }
    // Sets how many requests can be outstanding at once before we block and wait for one to
    // finish before starting the next one
    public function setMaxRequests($in_max_requests) {
        $this->max_requests = $in_max_requests;
    }

    // Sets the options to pass to curl, using the format of curl_setopt_array()
    public function setOptions($in_options) {
        $this->options = $in_options;
    }

    public function setOption($option, $value)
    {
        $this->options = array_merge($this->options,[$option=> $value]);
    }

    public function setDefaultOption($option, $value)
    {
        $this->default_options = array_merge($this->options,[$option=> $value]);
        $this->options = array_merge($this->options,[$option=> $value]);
    }

    public function setProxy($host, $port = 80, $user = null, $pass = null)
    {
        $this->setDefaultOption(CURLOPT_PROXY, $host);
        $this->setDefaultOption(CURLOPT_PROXYPORT, $port);

        if (!empty($user) && is_string($user)) {
            $pair = $user;
            if (!empty($pass) && is_string($pass)) {
                $pair .= ':' . $pass;
            }
            $this->setDefaultOption(CURLOPT_PROXYUSERPWD, $pair);
        }
        return $this;
    }

    // Start a fetch from the $url address, calling the $callback function passing the optional
    // $user_data value. The callback should accept 3 arguments, the url, curl handle and user
    // data, eg on_request_done($url, $ch, $user_data);
    public function startRequest($url, callable $callback, &$user_data = array(), $post_fields=null) {
        if( $this->max_requests > 0 )
            $this->waitForOutstandingRequestsToDropBelow($this->max_requests);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt_array($ch, $this->options);
        $this->options = $this->default_options;
        curl_setopt($ch, CURLOPT_URL, $url);
        if (isset($post_fields)) {
            curl_setopt($ch, CURLOPT_POST, TRUE);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post_fields);
        }

        curl_multi_add_handle($this->multi_handle, $ch);

        $ch_array_key = (int)$ch;
        $this->outstanding_requests[$ch_array_key] = array(
            'url' => $url,
            'callback' => $callback,
            'user_data' => $user_data,
        );

        $this->checkForCompletedRequests();
        return $this;
    }

    private function initPostFields($params, $useEncoding = true)
    {
        if (is_array($params)) {
            foreach ($params as $param) {
                if (is_string($param) && preg_match('/^@/', $param)) {
                    $useEncoding = false;
                    break;
                }
            }

            if ($useEncoding) {
                $params = http_build_query($params);
            }
        }

        if (!empty($params)) {
            $this->setOption(CURLOPT_POSTFIELDS, $params);
        }
    }

    public function get($uri, array $params = array(), array $customHeader = array(), &$user_data = array(), callable $callback)
    {
        if(count($params)){
            array_walk($params, function(&$item1, $key){$item1 = urlencode($key).'='.urlencode($item1);});
            $uri = $uri.(strpos($uri,'?')?'':'?').implode('&',$params);
        }
        $customHeader['Accept'] = 'application/json';
        //if(count($customHeader))
        array_walk($customHeader, function(&$item1, $key){$item1 = $key.': '.$item1;});
        $this->setOptions([CURLOPT_HTTPHEADER=>$customHeader]);
        return $this->startRequest($uri, $callback, $user_data);
    }

    public function post($uri, array $params = array(), array $customHeader = array(), &$user_data = array(), callable $callback, $useEncoding = true){
        $customHeader['Content-Type'] = 'application/json';
        //if(count($customHeader))
        array_walk($customHeader, function(&$item1, $key){$item1 = $key.': '.$item1;});
            $this->setOptions([CURLOPT_HTTPHEADER, $customHeader]);
        $this->initPostFields($params, $useEncoding);
        return $this->startRequest($uri, $callback, $user_data,$params);
    }

    public function delete($uri, array $params = array(), array $customHeader = array(), &$user_data = array(), callable $callback)
    {
        if(count($params)){
            array_walk($params, function(&$item1, $key){$item1 = urlencode($key).'='.urlencode($item1);});
            $uri = $uri.(strpos($uri,'?')?'':'?').implode('&',$params);
        }
        $customHeader['Content-Type'] = 'application/json';
        //if(count($customHeader))
        array_walk($customHeader, function(&$item1, $key){$item1 = $key.': '.$item1;});
        $this->setOptions([CURLOPT_HTTPHEADER, $customHeader]);

        $this->setOption(CURLOPT_HTTPGET, true);
        $this->setOption(CURLOPT_CUSTOMREQUEST, 'DELETE');

        return $this->startRequest($uri, $callback, $user_data);
    }

    // You *MUST* call this function at the end of your script. It waits for any running requests
    // to complete, and calls their callback functions
    public function finishAllRequests() {
        $this->waitForOutstandingRequestsToDropBelow(1);
    }
    // Checks to see if any of the outstanding requests have finished
    private function checkForCompletedRequests() {
        /*
            // Call select to see if anything is waiting for us
            if (curl_multi_select($this->multi_handle, 0.0) === -1)
                return;

            // Since something's waiting, give curl a chance to process it
            do {
                $mrc = curl_multi_exec($this->multi_handle, $active);
            } while ($mrc == CURLM_CALL_MULTI_PERFORM);
            */
        // fix for https://bugs.php.net/bug.php?id=63411
        do {
            $mrc = curl_multi_exec($this->multi_handle, $active);
        } while ($mrc == CURLM_CALL_MULTI_PERFORM);
        while ($active && $mrc == CURLM_OK) {
            if (curl_multi_select($this->multi_handle) != -1) {
                do {
                    $mrc = curl_multi_exec($this->multi_handle, $active);
                } while ($mrc == CURLM_CALL_MULTI_PERFORM);
            }
            else
                return;
        }

        // Now grab the information about the completed requests
        while ($info = curl_multi_info_read($this->multi_handle)) {

            $ch = $info['handle'];
            $ch_array_key = (int)$ch;

            if (!isset($this->outstanding_requests[$ch_array_key])) {
                die("Error - handle wasn't found in requests: '$ch' in ".
                    print_r($this->outstanding_requests, true));
            }

            $request = $this->outstanding_requests[$ch_array_key];
            $url = $request['url'];
            $content = curl_multi_getcontent($ch);
            $callback = $request['callback'];
            $user_data = $request['user_data'];

            call_user_func($callback, $content, $url, $ch, $user_data);

            unset($this->outstanding_requests[$ch_array_key]);

            curl_multi_remove_handle($this->multi_handle, $ch);
        }

    }


    // Blocks until there's less than the specified number of requests outstanding
    private function waitForOutstandingRequestsToDropBelow($max)
    {
        while (1) {
            $this->checkForCompletedRequests();
            if (count($this->outstanding_requests)<$max)
                break;

            usleep(10000);
        }
    }
} 
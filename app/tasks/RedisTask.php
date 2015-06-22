<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of RedisTask
 *
 * @author mariovalentino
 */
class RedisTask {
    
    /**
     * same with index
     */
    public function mainAction() {
         echo "\nThis is the default task and the default action \n";
    }
    
    /**
     * delete redis via console.
     */
    public function deleteAction($params = array()){
        try{
            if(count($params) == 0 || count($params) > 2){
                throw new \Exception("wrong input parameter. Please input right parameter (eg. --hash=hashname [--param=params]).\n");
            }else{
                $hashname = NULL;
                $hashkey  = NULL;

                $hashnameparams = explode('=',$params[0]);
                if($hashnameparams[0] != '--hash'){
                    throw new \Exception("wrong input parameter. Please input right parameter (eg. --hash=hashname [--param=params]).\n");
                }
                $hashname = $hashnameparams[1];

                if(isset($params[1])){
                    $hashkeyparams  = explode("=",$params[1]);     
                    if($hashkeyparams[0] != '--param'){
                        throw new \Exception("wrong input parameter. Please input right parameter (eg. --hash=hashname [--param=params]).\n");
                    }
                    $hashkey = $hashkeyparams[1];
                }

                $redis = new BilnaRedis();
                $redisHashList = $redis->getHashListByName('*'.$hashname.'*');
                foreach($redisHashList as $key => $value){
                    if(is_numeric($hashkey)){
                        $redis->deleteHash($value, $hashkey);
                    }else{
                        if(preg_match("/".$hashkey."/", $value))
                            $redis->deleteHash($value);
                        else
                            continue;
                    }
                }
            }
        }catch (\Exception $e){
            echo $e->getMessage();
        }
    }
}

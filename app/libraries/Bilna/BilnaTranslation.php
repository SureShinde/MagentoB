<?php
/**
 * Description of BilnaTranslation
 *
 * @author Bilna Development Team <development@bilna.com>
 */
namespace Bilna\Libraries;

use Phalcon\Translate\AdapterInterface as AdapterInterface;
use Phalcon\Translate\Adapter\NativeArray as NativeArray;
class BilnaTranslation extends NativeArray implements AdapterInterface {
    protected $type = 'lang';
    protected $language;
    protected $messages;
    protected $redisHashName;
    /**
     * Adapter constructor
     *
     * @param array $options
     */
    public function __construct(array $options) {
        $this->language = $options['language'];
        $this->messages = $options['content'];
        $this->redisHashName = sprintf("%s/%s", $this->type, $this->language);
        
        return parent::__construct($options);
    }
    /**
     * Returns the translation string of the given key
     *
     * @param   string $translateKey
     * @param   array $placeholders
     * @return  string
     */
    public function _($translateKey, $placeholders = null) {
        if ($this->exists($translateKey) === false) {
            $redis = new BilnaRedis($this->type);
            $redis->setRedisData($this->redisHashName, $translateKey, $translateKey);
        }
        
        return parent::_($translateKey, $placeholders);
    }
    /**
     * Returns the translation related to the given key
     *
     * @param   string $index
     * @param   array $placeholders
     * @return  string
     */
    public function query($index, $placeholders = null) {
        return parent::query($index, $placeholders);
    }
    /**
     * Check whether is defined a translation key in the internal array
     *
     * @param   string $index
     * @return  bool
     */
    public function exists($index) {
        if (isset ($this->messages[$index])) {
            return true;
        }
        
        return false;
    }
    
    /**
     * for Javascript Validation
     */
    public static $validationJavascript = array (
        'required',
        'invalid_email',
    );
}
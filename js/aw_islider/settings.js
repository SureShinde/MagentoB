/**
 * aheadWorks Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://ecommerce.aheadworks.com/LICENSE-M1.txt
 *
 * @category   AW
 * @package    AW_Islider
 * @copyright  Copyright (c) 2011 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/LICENSE-M1.txt
 */

var AWOptions = Class.create({
    initialize: function() {
        this.options = {};
        this.callbacks = {};
    },

    getOption: function(name, callback) {
        if(typeof callback == 'function') {
            this.callbacks[name] = callback;
        }
        return typeof name == 'undefined' ? this.options : this.options[name];
    },

    setOption: function(name, value) {
        this.options[name] = value;
        if(typeof(this.callbacks[name]) != 'undefined') {
            this.callbacks[name](value);
            delete this.callbacks[name];
        }
    }
});

var awISSettings = new AWOptions();

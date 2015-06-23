
var BilnaOptions = Class.create({
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

var bilnaISSettings = new BilnaOptions();

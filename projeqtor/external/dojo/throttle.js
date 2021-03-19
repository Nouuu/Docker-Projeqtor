//>>built
define("dojo/throttle",[],function(){return function(cb,_1){var _2=true;return function(){if(!_2){return;}_2=false;cb.apply(this,arguments);setTimeout(function(){_2=true;},_1);};};});
/**
 * 
 */
;(function($){
	
	$.fn.FA_VideoPlayer = $.fn.fa_video = function( options ){
		
		if( 0 == this.length ){			
			return false; 
		}
		// support multiple elements
	   	if (this.length > 1){
	        this.each(function( i, e ) {	        	
				$(e).fa_video(options);				
			});
	        return this;
	   	}
		
	   	var defaults = {
	   		onLoad : function(){},
	   		onPlay : function(){},
	   		onStop : function(){},
	   		onPause: function(){},
	   		// backwards compatible actions
	   		onFinish: function(){},
	   		stateChange: function(){}
	   	};
	   	
	   	var self 	= this,
	   		options = $.extend({}, defaults, options),
	   		player	= false,
	   		status;
	   	
	   	var init = function(){
	   		if( !player ){	   		
		   		switch( $(self).data( 'source' ) ){
		   			case 'youtube':
		   				player = $(self).youtubeVideo({
		   					onLoad 	: on_load,
		   					onPlay	: on_play,
		   					onStop 	: on_stop,
		   					onPause : on_pause
		   				});
		   			break;
		   			case 'vimeo':
	   					player = $(self).vimeoVideo({
		   					onLoad 	: on_load,
		   					onPlay	: on_play,
		   					onStop 	: on_stop,
		   					onPause : on_pause
		   				});
		   			break;
		   			default:
		   				var func = $(self).data('source') + 'Video';
		   				if( $.fn[ func ] ){
		   					player = $.fn[func].call( self, {
		   						onLoad 	: on_load,
			   					onPlay	: on_play,
			   					onStop 	: on_stop,
			   					onPause : on_pause
		   					});		   					
		   				}else{
		   					if( console ){
		   						console.warn( 'No implementation for video source "' + self.data('source') + '".' );
		   					}
		   				}
		   			break;	
		   		}
	   		}	
	   		
	   		// set the video size
	   		__resize();
	   		$(window).resize( __resize );
	   		
	   		return self;
	   	}
	   	
	   	/**
         * Calculates ratio for responsive videos
         * @private
         */
        var __resize = function () {
        	var width = $(self).width(),
				height;
			
			switch( $(self).data('aspect') ){
				case '16x9':
				default:
					height = (width *  9) / 16;
				break;
				case '4x3':
				height = (width *  3) / 4;
				break;
			}
			$(self).css({ height : Math.floor( height ) } );
        }
	   	
        var __change_status = function( s ){
        	status = s;
        	options.stateChange.call( self, status );        	
        };
        
        var __get_status = function(){
        	return status;
        }
        
	   	// events
	   	var on_load = function(){
	   		__change_status( 1 );
	   		options.onLoad.call( self, status );	   		
	   	};
	   	var on_play = function(){
	   		__change_status( 2 );
	   		options.onPlay.call( self, status );	   		
	   	};
	   	var on_stop = function(){
	   		__change_status( 4 );
	   		options.onStop.call( self, status );
	   		options.onFinish.call( self, status );
	   	};
	   	var on_pause = function(){
	   		__change_status( 3 );
	   		options.onPause.call( self, status );
	   	};
	   	
	   	// actions
	   	var play = function(){
	   		player.play();	   		
	   		__change_status( 2 );
	   		
	   	};
	   	var pause = function(){
	   		player.pause();
	   		__change_status( 3 );
	   		
	   	};
	   	var stop = function(){
	   		player.stop();
	   		__change_status( 4 );
	   	};
	   	
	   	// methods
	   	this.play = function(){
	   		play();
	   	};
	   	this.pause = function(){
	   		pause();
	   	};
	   	this.stop = function(){
	   		stop();
	   	}
	   	this.getStatus = function(){
	   		return __get_status();
	   	}
	   	this.resize = function(){
	   		__resize();
	   	}
	   	
	   	return init();
	}
	
})(jQuery);

/**
 * YouTube video embed
 */
;(function($){
	
	var yt_api_loaded = false;
	
	$.fn.youtubeVideo = function( options ){
		if( 0 == this.length ){ 
			return false; 
		}
		// support multiple elements
	   	if (this.length > 1){
	        this.each(function( i, e ) {
	        	$(e).youtubeVideo(options);				
			});
	        return this;
	   	}
		
	   	var defaults = {
	   		onLoad : function(){},
	   		onPlay : function(){},
	   		onStop : function(){},
	   		onPause: function(){}
	   	};
		
		var self 	= this,
	   		options = $.extend({}, defaults, options),
	   		player = false,
	   		status,
	   		player_id;
		
		
		var init = function(){ 	
			if( yt_api_loaded ){
				__load_video();
			}else{
				$(window).on( 'youtubeapiready', function(){
					__load_video();								
				})
			}
						
			__load_yt_api();
			return self;
		}
		
		var __load_video = function(){
			self.append('<div/>');
			
			var params = {
				'enablejsapi'	: 1,
				'rel'			: 0, // show related
				'showinfo'		: 0, // show info
				'showsearch'	: 0, // show search	
				// optional	
				'modestbranding' : self.data('modestbranding') || 0,
				'iv_load_policy' : self.data('iv_load_policy') || 0,
				'autohide' 		 : self.data('autohide') || 0,
				'controls'		 : self.data('controls') || 0,
				'fs'	 		 : self.data('fullscreen') || 0,
				'loop'			 : self.data('loop') || 0
			};
			
			player = new YT.Player(self.children(':first')[0], {
                height		: '100%',
                width		: '100%',
                videoId		: self.data('video_id'),
                playerVars	: params,
                events: {
                	'onReady': function( event ){
   						options.onLoad.call( self );
                		set_volume();
                		 //player = event.target;
   						// player.setVolume(options.volume);
   						// self.updateStatus(1); 
   					 },
                    'onStateChange': function (data) {
                        switch ( window.parseInt(data.data, 10) ) {
                        case 0:
			    			options.onStop.call( self );
                        break;
                        case 1:
                        	options.onPlay.call( self );
                        break;
                        case 2:
			    			options.onPause.call( self );
			    		break;
                        }
                    } 
                }
            });			
		};
		
		var __load_yt_api = function(){
			if( yt_api_loaded ){
				return;
			}
			
			yt_api_loaded = true;
			
			var element = document.createElement('script'),
	            scriptTag = document.getElementsByTagName('script')[0];
	
	        element.async = true;
	        element.src = document.location.protocol + "//www.youtube.com/iframe_api";
	        scriptTag.parentNode.insertBefore(element, scriptTag);
	        window.onYouTubeIframeAPIReady = function () {
	            $(window).trigger('youtubeapiready');	           
	        };
			
		};
		
		var set_volume = function(){			
			player.setVolume( self.data('volume') );
		};
		
		this.play = function(){
			player.playVideo();			
		};
		this.pause = function(){
			player.pauseVideo();			
		};
		this.stop = function(){
			player.stopVideo();
		};
		
		return init();
	}
	
})(jQuery);

/**
 * Vimeo video embed
 */
;(function($){
	
	$.fn.vimeoVideo = function( options ){
		if( 0 == this.length ){ 
			return false; 
		}
		// support multiple elements
	   	if (this.length > 1){
	        this.each(function( i, e ) {
	        	$(e).vimeoVideo(options);				
			});
	        return this;
	   	}
		
	   	var defaults = {
	   		onLoad : function(){},
	   		onPlay : function(){},
	   		onStop : function(){},
	   		onPause: function(){}
	   	};
		
		var self 	= this,
	   		options = $.extend({}, defaults, options),
	   		player,
	   		status,
	   		player_id;
	   	
	   	var init = function(){ 		
	   		
	   		var timestamp = new Date().getTime();
	   		player_id = 'vimeo' + $(self).data('video_id') + timestamp;
	   		
	   		var params = {
	   			'title' 	: self.data('title') || 0,
	   			'byline' 	: self.data('byline') || 0,
	   			'portrait' 	: self.data('portrait') || 0,
	   			'color' 	: self.data('color').replace('#', ''),
	   			'fullscreen': self.data('fullscreen') || 0,
	   			'loop' 		: self.data('loop') || 0
	   		};
			
	   		var iframe = '<iframe src="https://player.vimeo.com/video/' + $(self).data('video_id') + '?api=1&player_id=' + player_id + '&' + $.param(params) + '" id="' + player_id + '" width="100%" height="100%" frameborder="0" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe>';
	   		$(self).append( iframe );
            
            try{
            	player = Froogaloop( self.children(":first")[0] ).addEvent( 'ready', ready );
            }catch( err ){
            	// fall silent
            }	
            
            return self;
	   	}
	   	
	   	var ready = function(e){
	   		
	   		options.onLoad.call( self, { 'player' : player, 'status' : 1 } );
	   		
	   		set_volume();
	   		
	   		$f(e).addEvent( 'pause', function(){
        		options.onPause.call( self, { 'player' : player, 'status' : 3 } );
        	});
	   		$f(e).addEvent( 'finish', function(){
        		options.onStop.call( self, { 'player' : player, 'status' : 4 } );
        	});
	   		$f(e).addEvent( 'play', function(){
        		options.onPlay.call( self, { 'player' : player, 'status' : 2 } );
        	});	   		
	   	}
	   	
		var set_volume = function(){
			var volume = window.parseInt( self.data('volume'), 10 ) / 100;
	   		$f(player_id).api( 'setVolume', volume );
	   	};
	   	
	   	this.play = function(){
	   		$f(player_id).api('play');
	   	};
	   	this.pause = function(){
	   		$f(player_id).api('pause');
	   	};
	   	this.stop = function(){
	   		$f(player_id).api('unload');
	   	};
	   	
	   	return init();
	}
	
})(jQuery);

//Init style shamelessly stolen from jQuery http://jquery.com
var Froogaloop = (function(){
    // Define a local copy of Froogaloop
    function Froogaloop(iframe) {
        // The Froogaloop object is actually just the init constructor
        return new Froogaloop.fn.init(iframe);
    }

    var eventCallbacks = {},
        hasWindowEvent = false,
        isReady = false,
        slice = Array.prototype.slice,
        playerDomain = '';

    Froogaloop.fn = Froogaloop.prototype = {
        element: null,

        init: function(iframe) {
            if (typeof iframe === "string") {
                iframe = document.getElementById(iframe);
            }

            this.element = iframe;

            // Register message event listeners
            playerDomain = getDomainFromUrl(this.element.getAttribute('src'));

            return this;
        },

        /*
         * Calls a function to act upon the player.
         *
         * @param {string} method The name of the Javascript API method to call. Eg: 'play'.
         * @param {Array|Function} valueOrCallback params Array of parameters to pass when calling an API method
         *                                or callback function when the method returns a value.
         */
        api: function(method, valueOrCallback) {
            if (!this.element || !method) {
                return false;
            }

            var self = this,
                element = self.element,
                target_id = element.id !== '' ? element.id : null,
                params = !isFunction(valueOrCallback) ? valueOrCallback : null,
                callback = isFunction(valueOrCallback) ? valueOrCallback : null;

            // Store the callback for get functions
            if (callback) {
                storeCallback(method, callback, target_id);
            }

            postMessage(method, params, element);
            return self;
        },

        /*
         * Registers an event listener and a callback function that gets called when the event fires.
         *
         * @param eventName (String): Name of the event to listen for.
         * @param callback (Function): Function that should be called when the event fires.
         */
        addEvent: function(eventName, callback) {
            if (!this.element) {
                return false;
            }

            var self = this,
                element = self.element,
                target_id = element.id !== '' ? element.id : null;


            storeCallback(eventName, callback, target_id);

            // The ready event is not registered via postMessage. It fires regardless.
            if (eventName != 'ready') {
                postMessage('addEventListener', eventName, element);
            }
            else if (eventName == 'ready' && isReady) {
                callback.call(null, target_id);
            }

            return self;
        },

        /*
         * Unregisters an event listener that gets called when the event fires.
         *
         * @param eventName (String): Name of the event to stop listening for.
         */
        removeEvent: function(eventName) {
            if (!this.element) {
                return false;
            }

            var self = this,
                element = self.element,
                target_id = element.id !== '' ? element.id : null,
                removed = removeCallback(eventName, target_id);

            // The ready event is not registered
            if (eventName != 'ready' && removed) {
                postMessage('removeEventListener', eventName, element);
            }
        }
    };

    /**
     * Handles posting a message to the parent window.
     *
     * @param method (String): name of the method to call inside the player. For api calls
     * this is the name of the api method (api_play or api_pause) while for events this method
     * is api_addEventListener.
     * @param params (Object or Array): List of parameters to submit to the method. Can be either
     * a single param or an array list of parameters.
     * @param target (HTMLElement): Target iframe to post the message to.
     */
    function postMessage(method, params, target) {
        if (!target.contentWindow.postMessage) {
            return false;
        }

        var url = target.getAttribute('src').split('?')[0],
            data = JSON.stringify({
                method: method,
                value: params
            });

        if (url.substr(0, 2) === '//') {
            url = window.location.protocol + url;
        }

        target.contentWindow.postMessage(data, url);
    }

    /**
     * Event that fires whenever the window receives a message from its parent
     * via window.postMessage.
     */
    function onMessageReceived(event) {
        var data, method;

        try {
            data = JSON.parse(event.data);
            method = data.event || data.method;
        }
        catch(e)  {
            //fail silently... like a ninja!
        }

        if (method == 'ready' && !isReady) {
            isReady = true;
        }

        // Handles messages from moogaloop only
        if (event.origin != playerDomain) {
            return false;
        }

        var value = data.value,
            eventData = data.data,
            target_id = target_id === '' ? null : data.player_id,

            callback = getCallback(method, target_id),
            params = [];

        if (!callback) {
            return false;
        }

        if (value !== undefined) {
            params.push(value);
        }

        if (eventData) {
            params.push(eventData);
        }

        if (target_id) {
            params.push(target_id);
        }

        return params.length > 0 ? callback.apply(null, params) : callback.call();
    }


    /**
     * Stores submitted callbacks for each iframe being tracked and each
     * event for that iframe.
     *
     * @param eventName (String): Name of the event. Eg. api_onPlay
     * @param callback (Function): Function that should get executed when the
     * event is fired.
     * @param target_id (String) [Optional]: If handling more than one iframe then
     * it stores the different callbacks for different iframes based on the iframe's
     * id.
     */
    function storeCallback(eventName, callback, target_id) {
        if (target_id) {
            if (!eventCallbacks[target_id]) {
                eventCallbacks[target_id] = {};
            }
            eventCallbacks[target_id][eventName] = callback;
        }
        else {
            eventCallbacks[eventName] = callback;
        }
    }

    /**
     * Retrieves stored callbacks.
     */
    function getCallback(eventName, target_id) {
        if (target_id) {
            return eventCallbacks[target_id][eventName];
        }
        else {
            return eventCallbacks[eventName];
        }
    }

    function removeCallback(eventName, target_id) {
        if (target_id && eventCallbacks[target_id]) {
            if (!eventCallbacks[target_id][eventName]) {
                return false;
            }
            eventCallbacks[target_id][eventName] = null;
        }
        else {
            if (!eventCallbacks[eventName]) {
                return false;
            }
            eventCallbacks[eventName] = null;
        }

        return true;
    }

    /**
     * Returns a domain's root domain.
     * Eg. returns http://vimeo.com when http://vimeo.com/channels is sbumitted
     *
     * @param url (String): Url to test against.
     * @return url (String): Root domain of submitted url
     */
    function getDomainFromUrl(url) {
        if (url.substr(0, 2) === '//') {
            url = window.location.protocol + url;
        }

        var url_pieces = url.split('/'),
            domain_str = '';

        for(var i = 0, length = url_pieces.length; i < length; i++) {
            if(i<3) {domain_str += url_pieces[i];}
            else {break;}
            if(i<2) {domain_str += '/';}
        }

        return domain_str;
    }

    function isFunction(obj) {
        return !!(obj && obj.constructor && obj.call && obj.apply);
    }

    function isArray(obj) {
        return toString.call(obj) === '[object Array]';
    }

    // Give the init function the Froogaloop prototype for later instantiation
    Froogaloop.fn.init.prototype = Froogaloop.fn;

    // Listens for the message event.
    // W3C
    if (window.addEventListener) {
        window.addEventListener('message', onMessageReceived, false);
    }
    // IE
    else {
        window.attachEvent('onmessage', onMessageReceived);
    }

    // Expose froogaloop to the global object
    return (window.Froogaloop = window.$f = Froogaloop);

})();
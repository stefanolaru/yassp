(function ( $ ) {

    $.fn.yassp = function() {
    
        
        var Yassp = {
        	el: null,
        	url: null,
        	init: function(el) {
        	
        		var self = this;
        		this.el = $(el);
        		this.url = $(el).data('url');
        		
        		if(!this.url) return false;
        		
        		// listen for events
        		$('a', this.el).click(function(e){
					
					var p = $(this).parent();
					
					if(p.hasClass('facebook')) {
						self.openWindow('https://www.facebook.com/sharer/sharer.php?u='+self.url);
					}
					if(p.hasClass('twitter')) {
						self.openWindow('https://twitter.com/home?status='+self.el.data('title')+' '+self.url);
					}
					if(p.hasClass('linkedin')) {
						self.openWindow('https://www.linkedin.com/shareArticle?mini=true&url='+self.url+'&title='+self.el.data('title'));
					}
					if(p.hasClass('googleplus')) {
						self.openWindow('https://plus.google.com/share?url='+self.url);
					}
					if(p.hasClass('pinterest')) {
						self.openWindow('https://pinterest.com/pin/create/button/?url='+self.url+'&media='+self.el.data('media')+'&description='+self.el.data('title'));
					}
					if(p.hasClass('tumblr')) {
						self.openWindow('https://www.tumblr.com/widgets/share/tool/preview?shareSource=legacy&url='+self.url+'&title='+self.el.data('title'));
					}
					
        			//
        			e.preventDefault();
        		});
        		
        		this.getCounters();
        		
        	},
        	getCounters: function() {
        	
        		var self = this;
        		var networks = [];
        		
        		$('li', this.el).each(function(){
        			networks.push($(this).attr('class'));
        		});
        		
        		// get json        		
        		jQuery.getJSON(yassp.api_url, {
        			action: 'yassp_likes',
        			url: this.url,
        			networks: networks
        		}, function(response) {
        			if(response) {
        			
        				jQuery.each(response, function(k,v) {
        					jQuery('.'+k+' a .count', self.el).text(v);
//        					self.totals += parseInt(v);
        				});
        				
        				// create a new li item with total shares
        				/*
        				jQuery(self.el).prepend('<li class="totals"><strong>'+self.totals+'</strong> shares</li>');
        				*/
        			}
        		});
        	},
        	openWindow: function(src) {
        		
        		window.open(src, 'yasspIframe', 'height=450, width=550, top=' + (jQuery(window).height() / 2 - 275) + ', left=' + (jQuery(window).width() / 2 - 225) + ', toolbar=0, location=0, menubar=0, directories=0, scrollbars=0');
        		
        	}
        }
        
        Yassp.init(this);
        
        
        
    };
 
}( jQuery ));
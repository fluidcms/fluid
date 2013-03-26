define(['backbone', 'ejs'], function (Backbone, EJS) {
	var ContextMenu = Backbone.View.extend({
		tagName: 'div',
		
		className: "context-menu",
		
		events: {
			'click [data-action]': 'click'
		},
		
		initialize: function ( config ) {
			this.event = config.event;			
			this.parent = config.parent;
			this.template = new EJS({
				url: config.url+'?'+(new Date()).getTime() // !! Remove for production
			});
		},
		
		render: function() {
			this.$el.html(this.template.render());
			this.$el.css({left: this.event.pageX, top: this.event.pageY});
			$(document.body).append(this.$el);
			
			var obj = this;
			setTimeout(function() {
				$(document.body).bind('click contextmenu', function() {
					obj.remove();
				});
				$(document).keyup(function(e) {
					if (e.keyCode == 27) obj.remove();
				});
				
			}, 1);
			return this;
		},
		
		click: function(e) {
			this.remove();
			var action = $(e.currentTarget).attr('data-action');
			this.parent[action](this.event.target);
		}
	});
	
	return ContextMenu;
});
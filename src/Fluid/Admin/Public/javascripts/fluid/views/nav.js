define(['backbone', 'ejs'], function (Backbone, EJS) {
	var View = Backbone.View.extend({
		id: "nav",
		
		template: new EJS({url: 'javascripts/fluid/templates/nav.ejs'}), 
		
		initialize: function( attrs ) {
			this.items = [
				{name: 'Structure', className: 'structure'},
				{name: 'Files', className: 'files'}
			];
			
			this.router = attrs.router;
			this.router.on("route", this.changeRoute, this);
		},
		
		render: function() {		
			this.$el.html(this.template.render({items: this.items, current: this.router.current}));
			$("#main #nav").remove();
			$('#main').prepend(this.$el);
			return this;
		},
		
		changeRoute: function() {
			$("#main #nav a").removeClass('current');
			$("#main #content>div").hide();
			$("#main #content>div."+this.router.current).show();
			$("#main #nav a."+this.router.current).addClass('current');
		}
	});
	
	return View;
});
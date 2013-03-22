Fluid.View.Nav = Backbone.View.extend({
	template: new EJS({url: 'javascripts/fluid/templates/nav.ejs'}), 
	
	initialize: function() {
		this.items = [
			{name: 'Structure', className: 'structure'},
			{name: 'Files', className: 'files'}
		];
		
		Fluid.router.on("route", this.render, this);
	},
	
	render: function() {		
		this.$el.html(this.template.render({items: this.items, current: Fluid.router.current}));
		$('#main').append(this.$el);
		return this;
	}
});

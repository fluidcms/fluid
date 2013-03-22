Fluid.Structure.View = Backbone.View.extend({
	className: 'structure',
	
	template: new EJS({url: 'javascripts/fluid/templates/structure/structure.ejs'}), 

	initialize: function() {
		this.collection.on('change reset add remove', this.render, this);
	},
	
	render: function() {
		this.$el.html(this.template.render({pages: this.collection.models}));
		
		$("#main").append(this.$el);
		
		return this;
	}
});
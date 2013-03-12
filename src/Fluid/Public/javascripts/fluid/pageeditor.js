//
// Page Editor models
//
var PageEditor = Backbone.Model.extend({
		
	initialize: function(){
		this.fetch();
	},
	
	urlRoot: function() {
		var page = $("#website").get(0).contentWindow.location.toString();
		page = page.split($("#website").attr('src')).pop();
		if (page == '' || page.substr(page.length - 1) == '/') page = page+'index';		
		return "page/" + page + ".json";
	}
});

//
// Page Editor view
//
var PageEditorView = Backbone.View.extend({
	render: function() {
		console.log('yo');
		//this.$el.html(this.template.render());
		return this;
	}
});
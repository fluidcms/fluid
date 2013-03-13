//
// Page Editor models
//
var PageEditor = Backbone.Model.extend({
	url: 'page.json',
	
	initialize: function(){
		this.set("page", {});
		this.getPageContent();
	},
	
	getPageContent: function() {
		var obj = this;
		var url = app.page.get('url') + '?fluidtoken=' + app.page.get('token');
		var pageData = $.ajax(url).done(function(data) { obj.fetch({ data: $.param({ content: data, url: url }), type: 'POST' }); });
	}
});

//
// Page Editor view
//
var PageEditorView = Backbone.View.extend({
	className: 'page-editor',
	
	template: new EJS({url: 'templates/pageeditor/editor.ejs'}), 

	initialize: function () {
		this.model.bind('change', this.render, this);
	},

	render: function() {
		console.log(this.model.get('page'));
		
		this.$el.html(this.template.render({page: this.model.get('page')}));
		$("#website").after(this.$el);
		return this;
	}
});
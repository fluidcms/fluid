//
// Toolbar views
//
var ToolbarView = Backbone.View.extend({
	events: {  
		'click a[data-action=preview]': 'previewPage',
		'click a[data-action=edit]': 'editPage'
	},
			
	template: new EJS({url: 'templates/toolbar/toolbar.ejs'}), 
		
	render: function() {
		this.$el.html(this.template.render());
		return this;
	},
	
	previewPage: function(e) {
		$(e.target).parent().find('.toolbar-button').removeClass('current');
		$(e.target).addClass('current');
	},
	
	editPage: function(e) {
		$(e.target).parent().find('.toolbar-button').removeClass('current');
		$(e.target).addClass('current');
		new PageEditorView(new PageEditor()).render();
	}
});


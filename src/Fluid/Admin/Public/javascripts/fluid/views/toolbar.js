Fluid.View.Toolbar = Backbone.View.extend({
	language: '',
	
	events: {  
		'click a[data-action=preview]': 'previewPage',
		'click a[data-action=edit]': 'editPage'
	},
			
	template: new EJS({url: 'javascripts/fluid/templates/toolbar.ejs'}), 
	
	initialize: function() {
		Fluid.page.on('change:language', this.changeLanguage, this);
	},
	
	render: function() {
		this.$el.html(this.template.render({language: this.language}));
		$('#toolbar').append(this.$el);
		return this;
	},
	
	previewPage: function(e) {
		$(e.target).parent().find('.toolbar-button').removeClass('current');
		$(e.target).addClass('current');
	},
	
	editPage: function(e) {
		$(e.target).parent().find('.toolbar-button').removeClass('current');
		$(e.target).addClass('current');
		new PageEditorView({model: new PageEditor()}).render();
	},
	
	changeLanguage: function(model) {
		if (model.get('language') !== undefined && model.get('language') !== this.language) {
			this.language = model.get('language');
			this.render();
		}
	}
});


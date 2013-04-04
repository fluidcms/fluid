define(['backbone', 'ejs'], function (Backbone, EJS) {
	var View = Backbone.View.extend({
		language: '',
		
		events: {  
			'click a[data-action=preview]': 'previewPage',
			'click a[data-action=edit]': 'editPage'
		},
				
		template: new EJS({url: 'javascripts/fluid/templates/toolbar.ejs'}), 
		
		initialize: function( attrs ) {
			this.site = attrs.site;
			this.page = attrs.page;
			this.page.on('change:language', this.changeLanguage, this);
		},
		
		render: function() {
			this.$el.html(this.template.render({language: this.language}));
			$('#toolbar').append(this.$el);
			return this;
		},
		
		previewPage: function(e) {
			this.$el.find('.toolbar-button').removeClass('current');
			$("a[data-action=preview]", this.$el).addClass('current');
			
			if (typeof this.pageEditor !== 'undefined') {
				this.pageEditor.remove();
			}
		},
		
		editPage: function(e) {
			var root = this;
			
			$(e.target).parent().find('.toolbar-button').removeClass('current');
			$(e.target).addClass('current');
			
			require(['views/pageeditor'], function(PageEditorView) {
				root.pageEditor = new PageEditorView({page: root.page, site: root.site, toolbar: root}).render();
			});
		},
		
		changeLanguage: function(model) {
			if (model.get('language') !== undefined && model.get('language') !== this.language) {
				this.language = model.get('language');
				this.render();
			}
		}
	});
	
    return View;
});
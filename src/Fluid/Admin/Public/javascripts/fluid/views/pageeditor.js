define(['backbone', 'ejs'], function (Backbone, EJS) {
	var View = Backbone.View.extend({
		events: {
			'change form': 'change',	
			'submit form': 'submit',
			'click [data-action="cancel"]': 'cancel'
		},
		
		className: 'page-editor',
		
		template: new EJS({url: 'javascripts/fluid/templates/pageeditor/editor.ejs?'+(new Date()).getTime()}),  // !! Remove for production
		
		initialize: function( attrs ) {
			this.model = attrs.page;
			this.toolbar = attrs.toolbar;
		},
		
		render: function() {		
			this.$el.html(this.template.render({page: this.model}));
			$("#website").after(this.$el);
			return this;
		},
		
		change: function(e) {			
			var data = {};
			$.each($(e.currentTarget).serializeArray(), function(key, item) {
				if (item.name.match(/[\[\]]/)) {
					var keys = item.name.split('[');
					var itemKey = '';
					for (var i = 0; i < keys.length; i++) {
						keys[i] = keys[i].replace(/[\]'"]/g, "");
						if (/^\d+$/.test(keys[i])) {
							itemKey += "["+keys[i]+"]";
						} else {
							itemKey += "['"+keys[i]+"']";
						}
						
						eval("if (typeof data"+itemKey+" == 'undefined') { data"+itemKey+" = {}; }");
					}
					eval("data"+itemKey+" = item.value;");
				} else {
					data[item.name] = item.value;
				}
			});			
			
			this.model.set('data', data);
		},
		
		submit: function(e) {
			e.preventDefault();
			var root = this;
			this.model.save(null, {success: function() {
				$("#website")[0].contentWindow.location.reload();
				root.toolbar.previewPage();
			}});
		},
		
		cancel: function(e) {
			this.toolbar.previewPage();
		}
	});
	
	return View;
});

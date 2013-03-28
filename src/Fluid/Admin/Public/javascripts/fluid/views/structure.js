define(['backbone', 'ejs', 'jquery-ui', 'views/modal', 'views/contextmenu'], function (Backbone, EJS, jUI, Modal, ContextMenu) {	
	var View = Backbone.View.extend({
		events: {  
			'click a[data-action=addPage]': 'addPage',
			'click a[data-action=cancelChanges]': 'cancelChanges',
			'click a[data-action=saveChanges]': 'saveChanges',
			'contextmenu li a': 'contextmenu'
		},
	
		className: 'structure',
		
		dropbox: {},
		
		template: new EJS({url: 'javascripts/fluid/templates/structure/structure.ejs?'+(new Date()).getTime()}),  // !! Remove for production
	
		initialize: function( attrs ) {
			this.render();
			this.collection.on('reset add remove update', this.render, this);
			this.collection.on('change update', this.enableControls, this);
			this.collection.on('saved', this.disableControls, this);
			this.collection.on('saved', attrs.page.reload, this);
			this.languages = attrs.languages;
			this.layouts = attrs.layouts;
		},
		
		render: function() {
			this.$el.html(this.template.render({pages: this.collection}));
			$("#main").append(this.$el);
			this.sortable();
			return this;
		},
		
		sortable: function() {
			var obj = this;
			this.$el.find('ul.structure, ul.pages').sortable({
				update: function(event, ui) {
					var item = ui.item.attr('data-id');
					var receiver = $(event.target).parents('li').attr('data-id');
					if (typeof receiver == 'undefined') {
						receiver = '';
					}
					obj.dropbox.position = ui.item.index();
					obj.dropbox.item = item;
					obj.dropbox.receiver = receiver;
					clearTimeout(obj.dropbox.timeout);
					obj.dropbox.timeout = setTimeout(function() { obj.sort() }, 10);
				},
				axis: "y",
				connectWith: ".structureSortable",
				placeholder: "sortable-placeholder"
			});
		},
		
		sort: function() {
			this.collection.sort(this.dropbox.item, this.dropbox.receiver, this.dropbox.position);
		},
		
		enableControls: function() {
			this.$el.find('[data-action=cancelChanges],[data-action=saveChanges]').removeAttr('disabled');
		},
		
		disableControls: function() {
			this.$el.find('[data-action=cancelChanges],[data-action=saveChanges]').attr('disabled', 'true');
		},
		
		saveChanges: function(e) {
			if(!$(e.target).is("[disabled]")) {
				this.collection.save();
			}
		},
			
		cancelChanges: function(e) {
			if(!$(e.target).is("[disabled]") && confirm('Are you sure you want to cancel changes?')) {
				this.collection.fetch({reset: true});
				this.disableControls();
			}
		},
		
		contextmenu: function(e) {
			e.preventDefault();
			var contextMenu = new ContextMenu({url: 'javascripts/fluid/templates/structure/contextmenu.ejs', parent: this, event: e}).render();
		},
		
		addPage: function() {
			new Page({ model: new this.collection.__proto__.model({parent: this.collection}), languages: this.languages, layouts: this.layouts, newPage: true }).render();
		},
		
		editPage: function(page) {
			new Page({ model: this.collection.findItem($(page).parents('li').attr('data-id')), languages: this.languages, layouts: this.layouts }).render();
		},
		
		deletePage: function(page) {
			this.collection.removeItem($(page).parents('li').attr('data-id'));
		}
	});
	
	var Page = Modal.extend({
		events: _.extend({}, Modal.prototype.events),
		
		template: new EJS({url: 'javascripts/fluid/templates/structure/page.ejs?'+(new Date()).getTime()}),  // !! Remove for production
		
		initialize: function( attrs ) {
			this.languages = attrs.languages;
			this.layouts = attrs.layouts;
			if (typeof attrs.newPage !== 'undefined') {
				this.newPage = true;
			} else {
				this.newPage = false;
			}
		},
		
		renderData: function() {
			return {
				languages: this.languages,
				layouts: this.layouts
			};
		},
		
		submit: function() {
			if (this.newPage) {
				this.model.parent.add(this.model);
			}
		}
	});
	
	return View;
});
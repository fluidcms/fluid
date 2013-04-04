define(['backbone', 'models/site', 'models/page', 'models/language', 'models/layout', 'views/nav', 'views/toolbar'],
	function (Backbone, Site, Page, Language, Layout, Nav, Toolbar) {
		var run = function () {
			var FluidRouter = Backbone.Router.extend({
				root: "/fluidcms/",
				current: "",
	
				initialize: function () {
					this.site = new Site.Model();
					this.page = new Page.Model({site: this.site});
					this.languages = new Language;
					this.layouts = new Layout;
					
					this.nav = new Nav({router: this}).render();
					this.toolbar = new Toolbar({page: this.page, site: this.site}).render();
				},
				
				routes: {
					"*method": "make"
				},
				
				make: function(method) {
					method = method ? method : 'structure';
					if (typeof this[method] == 'function') {
						this.current = method;
						this[method]();
					}
				},
				
				structure: function () {
					var root = this;
					var view;
					if (typeof root.structureView === 'undefined') {
						require(['models/structure', 'views/structure'], function(Structure, StructureView) {
							root.structureView = new StructureView({collection: new Structure.Pages(), page: root.page, languages: root.languages, layouts: root.layouts});
						});
					}
				},
				
				files: function () {
					var root = this;
					if (typeof root.fileView === 'undefined') {
						require(['models/file', 'views/file'], function(File, FileView) {
							root.fileView = new FileView({collection: new File.Collection()});
						});
					}
				}
			});
	 
			//boot up the app:
			var fluidRouter = new FluidRouter();
						
			// Block default link behavior
			Backbone.history.start({pushState: true, root: fluidRouter.root});
			
			$(document).on('click', "a[href]", function(e) {
				var href = { prop: $(this).prop("href"), attr: $(this).attr("href") };
				var root = location.protocol + "//" + location.host + fluidRouter.root;
					
				if (href.prop && href.prop.slice(0, root.length) === root) {
					e.preventDefault();
					Backbone.history.navigate(href.attr, true);
				}
			});
		}
		
		return {
			run: run
		};
	}
);

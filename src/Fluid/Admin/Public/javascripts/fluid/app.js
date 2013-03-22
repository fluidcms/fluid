define(['backbone', 'models/site', 'models/page', 'views/nav', 'views/toolbar'],
	function (Backbone, Site, Page, Nav, Toolbar) {
		var run = function () {
			var FluidRouter = Backbone.Router.extend({
				root: "/fluidcms/",
				current: "",
	
				initialize: function () {
					this.site = new Site.Model();
					this.page = new Page.Model({site: this.site});
					this.nav = new Nav({router: this}).render();
					this.toolbar = new Toolbar({page: this.page}).render();
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
					require(['models/structure', 'views/structure'], function(Structure, StructureView) {
						new StructureView({collection: new Structure.Pages()});
					});					
				},
				
				files: function () {
					
				}
			});
	 
			//boot up the app:
			var fluidRouter = new FluidRouter();
						
			// Track mouse
			var mousePos = {};
			$(document).mousemove( function(e) {
				mousePos.x = e.pageX; 
				mousePos.y = e.pageY;
			});
			
			// Block default context menu
			$(document).contextmenu(function(e) {
				e.preventDefault();
			});
			
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

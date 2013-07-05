define(['backbone', 'views/loader', 'models/socket', 'views/nav'],
    function (Backbone, LoaderView, Socket, Nav) {
        var run = function () {
            var FluidRouter = Backbone.Router.extend({
                root: "/fluidcms/",
                current: "",
                ready: false,

                initialize: function () {
                    var root = this;

                    this.loader = new LoaderView();

                    //this.site = new Site.Model();
                    //this.page = new Page.Model({site: this.site});
                    //this.languages = new Language;
                    //this.layouts = new Layout;

                    this.nav = new Nav({router: this}).render();
                    //this.toolbar = new Toolbar({page: this.page, site: this.site}).render();
                    //this.version = new Version();

                    this.socket = new Socket({
                        loader: this.loader,
                        //version: this.version
                    });
                    this.socket.on('ready', function() {
                        root.ready = true;
                        root.loader.remove();
                    });
                    this.socket.connection();
                },

                routes: {
                    "*method": "make"
                },

                make: function (method) {
                    var root = this;
                    method = method ? method : 'map';
                    if (typeof this[method] == 'function') {
                        if (!this.ready) {
                            setTimeout(function() { root.make(method) }, 10);
                        } else {
                            this[method]();
                            this.current = method;
                            this.trigger('change');
                        }
                    }
                },

                map: function () {
                    var root = this;
                    if (this.current !== 'map') {
                        require(['models/map', 'views/map'], function (Map, MapView) {
                            var map = new Map.Pages(null, {socket: root.socket});
                            new MapView({
                                collection: map,
                                page: root.page,
                                languages: root.languages,
                                layouts: root.layouts
                            });
                        });
                    }
                },

                files: function () {
                    /*var root = this;
                    if (typeof root.fileView === 'undefined') {
                        require(['models/file', 'views/file'], function (File, FileView) {
                            root.fileView = new FileView({collection: new File.Collection()});
                        });
                    }*/
                }
            });

            //boot up the app:
            var fluidRouter = new FluidRouter();

            // Block default link behavior
            Backbone.history.start({pushState: true, root: fluidRouter.root});

            $(document).on('click', "a[href]", function (e) {
                var href = { prop: $(this).prop("href"), attr: $(this).attr("href") };
                var root = location.protocol + "//" + location.host + fluidRouter.root;

                if (href.prop && href.prop.slice(0, root.length) === root) {
                    e.preventDefault();
                    //Backbone.history.navigate(href.attr, true);
                }
            });
        }

        return {
            run: run
        };
    }
);

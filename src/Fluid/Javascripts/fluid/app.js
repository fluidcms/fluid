define(['backbone', 'views/loader', 'models/socket', 'models/language', 'models/layout', 'views/nav'],
    function (Backbone, LoaderView, Socket, Language, Layout, Nav) {
        var run = function () {
            var FluidRouter = Backbone.Router.extend({
                root: "/fluidcms/",
                current: "",
                main: null,
                ready: false,

                initialize: function () {
                    var root = this;

                    this.loader = new LoaderView();

                    //this.site = new Site.Model();
                    //this.page = new Page.Model({site: this.site});

                    this.nav = new Nav({router: this}).render();
                    //this.toolbar = new Toolbar({page: this.page, site: this.site}).render();
                    //this.version = new Version();

                    this.socket = new Socket({
                        loader: this.loader,
                        //version: this.version
                    });

                    this.languages = new Language.Languages(null, {socket: root.socket});
                    this.layouts = new Layout.Layouts(null, {socket: root.socket});

                    this.socket.on('ready', function() {
                        root.ready = true;
                        root.loader.remove();
                        root.languages.fetch();
                        root.layouts.fetch();
                    });

                    this.socket.connection();

                    // Control + Z or Command + Z events
                    $(document).keydown(function (e) {
                        if ((e.ctrlKey || e.metaKey) && e.keyCode == 90) {
                            //root.cancelChange();
                            console.log('Control Z');
                        }
                    });
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
                            if (this.main !== null) {
                                this.main.remove();
                            }
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
                            root.main = new MapView({
                                collection: map,
                                page: root.page,
                                languages: root.languages,
                                layouts: root.layouts
                            });
                        });
                    }
                },

                components: function () {
                    var root = this;
                },

                files: function () {
                    /*var root = this;
                    if (typeof root.fileView === 'undefined') {
                        require(['models/file', 'views/file'], function (File, FileView) {
                            root.fileView = new FileView({collection: new File.Collection()});
                        });
                    }*/
                },

                history: function () {
                    var root = this;
                    if (this.current !== 'history') {
                        require(['models/history', 'views/history'], function (History, HistoryView) {
                            var history = new History(null, {socket: root.socket});
                            root.main = new HistoryView({collection: history});
                            history.fetch();
                        });
                    }
                }
            });

            //boot up the app:
            var fluidRouter = new FluidRouter();
            fluidRouter.make("");

            // Block default link behavior
            $(document).on('click', "a[href]", function (e) {
                var href = { prop: $(this).prop("href"), attr: $(this).attr("href") };
                var root = location.protocol + "//" + location.host + fluidRouter.root;

                if (href.prop && href.prop.slice(0, root.length) === root) {
                    e.preventDefault();
                    fluidRouter.make(href.attr);
                }
            });
        }

        return {
            run: run
        };
    }
);

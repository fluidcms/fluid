define(['backbone', 'views/loader', 'models/socket', 'models/map', 'models/language', 'models/layout', 'models/preview', 'models/history', 'views/nav', 'views/toolbar'],
    function (Backbone, LoaderView, Socket, Map, Language, Layout, Preview, History, Nav, Toolbar) {
        var run = function () {
            var FluidRouter = Backbone.Router.extend({
                root: "/fluidcms/",
                current: "",
                main: null,
                ready: false,

                initialize: function () {
                    var root = this;

                    this.models = {};
                    this.views = {};
                    this.loader = new LoaderView();

                    this.socket = new Socket({loader: this.loader});

                    this.nav = new Nav({router: this}).render();
                    //this.version = new Version();

                    this.models.languages = this.languages = new Language.Languages(null, {socket: this.socket});
                    this.socket.models['language'] = this.models.languages;

                    this.models.preview = new Preview({socket: this.socket, languages: this.languages});

                    this.models.layouts = this.layouts = new Layout.Layouts(null, {socket: this.socket});

                    this.models.map = new Map.Pages(null, {socket: this.socket, languages: this.models.languages, preview: this.models.preview});
                    this.socket.models['map'] = this.models.map;

                    this.models.history = new History(null, {socket: this.socket});

                    this.views.toolbar = this.toolbar = new Toolbar({
                        languages: this.languages,
                        preview: this.models.preview,
                        map: this.models.map
                    }).render();

                    this.socket.on('ready', function() {
                        root.ready = true;
                        root.loader.remove();
                        root.languages.fetch();
                        root.layouts.fetch();
                        root.models.preview.loadPage();
                        root.models.map.fetch();
                    });

                    this.socket.connection();

                    // Control + Z or Command + Z events
                    $(document).keydown(function (e) {
                        if ((e.ctrlKey || e.metaKey) && e.keyCode == 90) {
                            //root.cancelChange();
                            console.log('Control Z');
                        }
                    });

                    // Control + S or Command + S events
                    $(document).keydown(function (e) {
                        return !((e.ctrlKey || e.metaKey) && e.keyCode === 83);
                    });

                    // Control + R or Command + R events
                    $(document).keydown(function (e) {
                        if ((e.ctrlKey || e.metaKey) && e.keyCode == 82) {
                            console.log('Block refresh');
                        }
                        return true;
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
                                this.main.hide();
                            }
                            this[method]();
                            this.current = method;
                            this.trigger('change');
                        }
                    }
                },

                map: function () {
                    var root = this;
                    if (this.current !== 'map' && typeof this.views.map === 'undefined') {
                        require(['views/map'], function (MapView) {
                            root.views.map = root.main = new MapView({
                                collection: root.models.map,
                                page: root.page,
                                languages: root.languages,
                                layouts: root.layouts
                            });
                        });
                    } else if (this.current !== 'map') {
                        this.views.map.show();
                        this.main = this.views.map;
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
                    if (this.current !== 'history' && typeof this.views.history === 'undefined') {
                        require(['views/history'], function (HistoryView) {
                            root.views.history = root.main = new HistoryView({collection: root.models.history});
                            root.models.history.fetch();
                        });
                    } else if (this.current !== 'history') {
                        this.views.history.show();
                        this.main = this.views.history;
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

            // Block delete button from navigating
            $(document).keydown(function (e) {
                if (e.keyCode == 8) {
                    if (
                        (document.activeElement.getAttribute('contenteditable') !== "true") &&
                            (document.activeElement.tagName !== "INPUT")
                        ) {
                        return false;
                    }
                }
                return true;
            });
        };

        return {
            run: run
        };
    }
);

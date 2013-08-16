define(
    [
        'backbone',
        'views/helpers/loader',
        'models/socket/socket',
        'models/map/map',
        'models/language/language',
        'models/layout/layout',
        'models/preview/preview',
        'models/history/history',
        'models/component/component',
        'models/file/file',
        'views/nav/nav',
        'views/toolbar/toolbar',
        'views/tools/tools'
    ],
    function (
        Backbone,
        LoaderView,
        Socket,
        Map,
        Language,
        Layout,
        Preview,
        History,
        Components,
        Files,
        Nav,
        Toolbar,
        ToolsView
        ) {
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

                    // Socket
                    this.socket = new Socket({loader: this.loader});

                    // Models
                    this.models.languages = this.languages = new Language.Languages(null, {socket: this.socket}); // TODO: remove this.languages var
                    this.socket.models['language'] = this.models.languages;

                    this.models.preview = new Preview({socket: this.socket, languages: this.languages});

                    this.models.layouts = this.layouts = new Layout.Layouts(null, {socket: this.socket}); // TODO: remove this.layouts var

                    this.models.components = new Components(null, {socket: this.socket});

                    this.models.map = new Map.Pages(null, {
                        app: this,
                        socket: this.socket,
                        languages: this.models.languages,
                        preview: this.models.preview,
                        components: this.models.components
                    });
                    this.socket.models['map'] = this.models.map;

                    this.models.history = new History(null, {socket: this.socket});

                    this.models.files = new Files(null, {socket: this.socket});

                    // Views
                    this.nav = new Nav({router: this}).render();
                    //this.version = new Version();

                    this.views.toolbar = this.toolbar = new Toolbar({
                        languages: this.languages,
                        preview: this.models.preview,
                        map: this.models.map
                    }).render();

                    this.views.tools = new ToolsView({
                        map: this.models.map
                    });

                    // Socket event
                    this.socket.on('ready', function() {
                        root.ready = true;
                        root.loader.remove();
                        root.models.languages.fetch();
                        root.models.layouts.fetch();
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
                            if (this.main !== null && this.current !== method) {
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
                        require(['views/map/map'], function (MapView) {
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
                    if (this.current !== 'components' && typeof this.views.components === 'undefined') {
                        require(['views/components/components'], function (ComponentsView) {
                            root.views.components = root.main = new ComponentsView({collection: root.models.components});
                            root.models.components.fetch();
                        });
                    } else if (this.current !== 'components') {
                        this.views.components.show();
                        this.main = this.views.components;
                    }
                },

                files: function () {
                    var root = this;
                    if (this.current !== 'files' && typeof this.views.files === 'undefined') {
                        require(['views/files/files'], function (FilesView) {
                            root.views.files = root.main = new FilesView({collection: root.models.files});
                            root.models.files.fetch();
                        });
                    } else if (this.current !== 'files') {
                        this.views.files.show();
                        this.main = this.views.files;
                    }
                },

                tools: function() {
                    var root = this;
                    if (this.current !== 'tools') {
                        this.views.tools.show();
                        this.main = this.views.tools;
                    }
                },

                history: function () {
                    var root = this;
                    if (this.current !== 'history' && typeof this.views.history === 'undefined') {
                        require(['views/history/history'], function (HistoryView) {
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

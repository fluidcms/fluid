define(
    [
        'backbone',
        'marionette',
        'views/helpers/loader',
        'lib/socket',
        'models/map/map',
        'models/language/language',
        'models/layout/layout',
        'models/website/website',
        'models/history/history',
        'models/component/component',
        'models/file/file',
        'views/website/website',
        'views/nav/nav',
        'views/map/map-layout',
        'views/toolbar/toolbar',
        'views/tools/tools',
        'views/helpers/error',
        'views/page/page'
    ],
    function (Backbone, Marionette, LoaderView, Socket, Map, Language, Layout, Website, History, Components, Files, WebsiteView, NavView, MapLayoutView, Toolbar, ToolsView, ErrorView,
        PageView) {
        return Marionette.Controller.extend({
            current: "",
            main: null,
            ready: false,
            mapView: null,
            editors: {},

            initialize: function (options) {
                var root = this;
                this.session = options.session;
                this.user = options.user;
                this.app = options.app;

                $.ajax({url: "server"}).done(function (response) {
                    if (response == 'true') {
                        root.load();
                    } else {
                        new ErrorView({msg: 'Could not start the server, please contact the administrator.'});
                    }
                }).error(function() {
                    new ErrorView({msg: 'Could not start the server, please contact the administrator.'});
                });
            },

            load: function () {
                var root = this;

                this.models = {};
                this.views = {};
                this.loader = new LoaderView();

                this.socket = new Socket;
                this.socket.initialize({
                    loader: this.loader,
                    session: this.session,
                    user: this.user,
                    app: this.app
                });

                this.models.languages = this.languages = new Language.Languages(null, {socket: this.socket}); // TODO: remove this.languages var
                this.socket.models['language'] = this.models.languages;

                //this.models.preview = new Preview({socket: this.socket, languages: this.languages});
                this.website = new Website({});
                this.websiteView = new WebsiteView({model: this.website});

                /*this.models.layouts = this.layouts = new Layout.Layouts(null, {socket: this.socket}); // TODO: remove this.layouts var

                this.models.components = new Components(null, {socket: this.socket});

                this.models.files = new Files(null, {socket: this.socket});*/

                this.map = new Map(null, {
                    socket: this.socket
/*
                    languages: this.models.languages,
                    preview: this.models.preview,
                    components: this.models.components,
                    files: this.models.files
*/
                });
                this.socket.models['map'] = this.map;

                //this.models.history = new History(null, {socket: this.socket});

                // Views
                this.nav = new NavView({controller: this}).render();
                //this.version = new Version();

                /*this.views.toolbar = this.toolbar = new Toolbar({
                    languages: this.languages,
                    preview: this.models.preview,
                    map: this.models.map
                }).render();

                this.views.tools = new ToolsView({
                    map: this.models.map
                });
                this.models.map.tools = this.views.tools;*/

                // Socket event
                this.socket.on('ready', function () {
                    if (root.ready !== true) {
                        root.ready = true;
                        root.loader.remove();
                        /*root.models.components.fetch();
                        root.models.languages.fetch();
                        root.models.layouts.fetch();
                        root.models.preview.loadPage();
                        root.models.components.fetch();
                        root.models.map.fetch();*/

                        root.map.pages.fetch();
                        //root.models.preview.loadPage();
                        root.app.websiteRegion.show(root.websiteView);
                    }
                });

                this.mapPannel();
                this.socket.connect();

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
                        e.preventDefault();
                        root.websiteView.reload();
                        return false;
                    }
                    return true;
                });
            },

            mapPannel: function () {
                var root = this;
                if (this.current !== 'map' && typeof this.mapView === 'undefined' || this.mapView === null) {
                    this.app.pannelRegion.show(new MapLayoutView({
                        controller: this,
                        model: this.map
                        //page: root.page,
                        //languages: root.languages,
                        //layouts: root.layouts
                    }));
                } else if (this.current !== 'map') {
                    //this.views.map.show();
                    //this.main = this.views.map;
                }
            },

            pageEditor: function(page) {
                this.app.mainRegion.show(new PageView({model: page}));
                this.app.mainRegion.$el.show();
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

            tools: function () {
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
    }
);
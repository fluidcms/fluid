define(
    [
        'backbone',
        'marionette',
        'views/helpers/loader',
        'lib/socket',
        'controllers/pannel',
        'controllers/website',
        'models/map/map',
        'models/language/language-collection',
        'models/component/component-collection',
        'models/file/file-collection',
        'views/toolbar/toolbar',
        'views/helpers/error',
        'views/page/page',
        'views/editor/string/string',
        'views/editor/content/content'
    ],
    function (Backbone, Marionette, LoaderView, Socket, PannelController, WebsiteController, Map, LanguageCollection, ComponentCollection,
              FileCollection, Toolbar, ErrorView, PageView, EditorStringView, EditorContentView) {
        return Marionette.Controller.extend({
            current: "",
            baseUrl: "",
            main: null,
            ready: false,
            mapView: null,
            editors: {},

            initialize: function (options) {
                var root = this;
                this.keyEvents();
                this.baseUrl = window.location.rootpath;
                this.session = options.session;
                this.user = options.user;
                this.app = options.app;
                this.socket = new Socket;
                this.loaderView = new LoaderView();
                this.languageCollection = new LanguageCollection(options.languages);
                this.componentCollection = new ComponentCollection(null, {socket: this.socket});
                this.fileCollection = new FileCollection(null, {socket: this.socket});
                this.map = new Map(null, {socket: this.socket});

                this.pannelController = new PannelController({
                    baseUrl: this.baseUrl,
                    app: this.app,
                    fluidController: this,
                    map: this.map,
                    languageCollection: this.languageCollection,
                    componentCollection: this.componentCollection,
                    fileCollection: this.fileCollection
                });

                this.websiteController = new WebsiteController({
                    app: this.app,
                    fluidController: this,
                    languageCollection: this.languageCollection
                });

                var errorFunction = function () {
                    new ErrorView({msg: 'Could not start the server, please contact the administrator.'});
                };
                $.ajax({url: "server"}).done(function (response) {
                    if (response == 'true') {
                        root.load();
                    } else {
                        errorFunction();
                    }
                }).error(function() {
                    errorFunction();
                });
            },

            keyEvents: function () {
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
            },

            load: function () {
                var root = this;

                this.socket.initialize({
                    loader: this.loader,
                    session: this.session,
                    user: this.user,
                    app: this.app
                });

                //this.socket.models['language'] = this.models.languages;

                //this.models.preview = new Preview({socket: this.socket, languages: this.languages});


                /*this.models.layouts = this.layouts = new Layout.Layouts(null, {socket: this.socket}); // TODO: remove this.layouts var
*/

                //this.models.history = new History(null, {socket: this.socket});

                // Views
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
                        root.loaderView.remove();
                        /*root.models.components.fetch();
                        root.models.languages.fetch();
                        root.models.layouts.fetch();
                        root.models.preview.loadPage();
                        root.models.components.fetch();
                        root.models.map.fetch();*/

                        root.map.pages.fetch();
                        root.componentCollection.fetch();
                        root.fileCollection.fetch();
                        //root.models.preview.loadPage();
                        root.trigger('ready');
                    }
                });

                this.socket.connect();
            },

            pageEditor: function(page) {
                this.app.mainRegion.show(new PageView({model: page, controller: this}));
                this.app.mainRegion.$el.show();
            },

            editor: function(type, model, variableView) {
                var view;
                switch (type) {
                    case 'string':
                        view = new EditorStringView({model: model, controller: this, variableView: variableView});
                        break;
                    case 'content':
                        view = new EditorContentView({model: model, controller: this, variableView: variableView});
                        break;
                }
                if (typeof view !== 'undefined') {
                    this.app.editorRegion.show(view);
                    this.app.editorRegion.$el.show();
                }
            }
        });
    }
);
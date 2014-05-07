define(
    [
        'backbone',
        'marionette',
        'views/nav/nav',
        'views/map/map-layout',
        'views/tool-pannel/tool-pannel-view',
        'views/file/file-composite-view',
        'views/component/component-collection'
    ],
    function (Backbone, Marionette, NavView, MapLayoutView, ToolPannelView, FileCompositeView, ComponentCollectionView) {
        return Marionette.Controller.extend({
            initialize: function (options) {
                this.navView = new NavView({controller: this}).render();
                this.mapPannel();
            },

            mapPannel: function () {
                this.app.pannelRegion.show(new MapLayoutView({
                    controller: this,
                    model: this.map
                    //page: root.page,
                    //languages: root.languages,
                    //layouts: root.layouts
                }));
            },

            componentsPannel: function () {
                this.app.pannelRegion.show(new ComponentCollectionView({
                    controller: this,
                    collection: this.components
                    //model: this.map
                    //page: root.page,
                    //languages: root.languages,
                    //layouts: root.layouts
                }));

                return;
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

            filesPannel: function () {

                this.app.pannelRegion.show(new FileCompositeView({
                    controller: this,
                    collection: this.files
                    //model: this.map
                    //page: root.page,
                    //languages: root.languages,
                    //layouts: root.layouts
                }));

                return;
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

            toolsPanel: function () {
                this.app.pannelRegion.show(new ToolPannelView({
                    controller: this
                    //model: this.map
                    //page: root.page,
                    //languages: root.languages,
                    //layouts: root.layouts
                }));


                return;
                var root = this;
                if (this.current !== 'tools') {
                    this.views.tools.show();
                    this.main = this.views.tools;
                }
            },

            historyPannel: function () {
                return;
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
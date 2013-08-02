define(['backbone', 'ejs'], function (Backbone, EJS) {
    var View = Backbone.View.extend({
        events: {
            'click a[data-action=preview]': 'previewPage',
            'click a[data-action=edit]': 'editPage',
            'click a[data-action=reload]': 'reloadPage',
            'change select': 'changeLanguage'
        },

        template: new EJS({url: 'javascripts/fluid/templates/toolbar.ejs?' + (new Date()).getTime()}),  // !! Remove for production

        initialize: function (attrs) {
            this.languages = attrs.languages;
            this.preview = attrs.preview;
            this.map = attrs.map;

            this.languages.on('change', this.render, this);
            this.map.on('editing', this.render, this);
        },

        render: function () {
            var language;
            if (typeof this.languages !== 'undefined' && this.languages.current !== null) {
                language = this.languages.current.get('language');
            }

            var currentPage;
            if (typeof this.map.current === 'undefined') {
                currentPage = null;
            } else {
                currentPage = this.map.current;
            }

            this.$el.html(this.template.render({
                languages: this.languages,
                language: language,
                currentPage: currentPage,
                editing: this.map.editing
            }));
            $('#toolbar').append(this.$el);
            return this;
        },

        previewPage: function (e) {
            this.$el.find('.toolbar-button').removeClass('current');
            $("a[data-action=preview]", this.$el).addClass('current');

            this.map.stopEditing();
        },

        editPage: function (e) {
            this.map.startEditing(this.map.current);
        },

        reloadPage: function(e) {
            //this.page.reload();
        },

        changeLanguage: function (model) {
            if (model.target) {
                var root = this;
                var language = $(model.target).val();

                $.ajax({url: "changelanguage.json", dataType: 'JSON', type: "GET", data: {
                    url: this.preview.getUrl(),
                    language: language
                }}).done(function(response) {
                        root.preview.loadPage(response)
                });
            }

            else if (model.get('language') !== undefined && model.get('language') !== this.language) {
                this.language = model.get('language');
                this.render();
            }
        }
    });

    return View;
});
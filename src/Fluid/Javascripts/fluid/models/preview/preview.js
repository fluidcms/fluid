define(['backbone'], function (Backbone) {
    return Backbone.Model.extend({
        initialize: function (attrs) {
            var root = this;

            this.socket = attrs.socket;
            this.languages = attrs.languages;

            // Track iframe location change
            $("#website").on('load', function(e) { root.verifyFrameStatus(e) });

            // Block normal link behavior of the iframe
            this.overrideIframeBehavior();
        },

        overrideIframeBehavior: function() {
            var root = this;
            var iframe = $("#website")[0].contentWindow.document;

            // Append the token and branch to local links
            $('[href]', $(iframe.body)).on('click', function(e) {
                var target = $(e.currentTarget)[0];

                if (target.hostname == document.location.hostname && $(target).attr('href').charAt(0) !== '#') {
                    e.preventDefault();
                    root.getToken(function(response) {
                        var url = target.href;
                        url = updateQueryStringParameter(url, 'fluidbranch', fluidBranch);
                        url = updateQueryStringParameter(url, 'fluidtoken', response.token);
                        url = updateQueryStringParameter(url, 'fluidsession', fluidSession);
                        iframe.location = url;
                    });
                }
            });
        },

        getUrl: function() {
            var url = $("#website")[0].contentWindow.location.toString();
            url = RemoveUrlParameter(url, 'fluidbranch');
            url = RemoveUrlParameter(url, 'fluidtoken');
            url = RemoveUrlParameter(url, 'fluidsession');

            return url.replace(/^https?:\/\/[^\/]*/g, '');
        },

        verifyFrameStatus: function(e) {
            var url = $(e.target).get(0).contentWindow.location.toString();
            var session = getParameterByName(url, 'fluidsession');
            var token = getParameterByName(url, 'fluidtoken');
            var branch = getParameterByName(url, 'fluidbranch');

            if (session === '' || token === '' || branch === '') {
                this.loadPage(this.getUrl());
            } else {
                this.overrideIframeBehavior();
            }
        },

        loadPage: function(url) {
            var root = this;

            if (typeof url === 'undefined' || url === null) {
                url = "/";
            }

            var language;
            if (typeof this.languages.current !== 'undefined' && this.languages.current !== null) {
                language = this.languages.current.get('language');
            }

            if (typeof language === 'undefined') {
                root.getToken(function(response) {
                    url = updateQueryStringParameter(url, 'fluidbranch', fluidBranch);
                    url = updateQueryStringParameter(url, 'fluidtoken', response.token);
                    url = updateQueryStringParameter(url, 'fluidsession', fluidSession);
                    $("#website")[0].contentWindow.location = url;
                });
            } else {
                $.ajax({url: "changepage.json", dataType: 'JSON', type: "GET", data: {
                    url: url,
                    language: language
                }}).done(
                    function(url) {
                        root.getToken(function(response) {
                            url = updateQueryStringParameter(url, 'fluidbranch', fluidBranch);
                            url = updateQueryStringParameter(url, 'fluidtoken', response.token);
                            url = updateQueryStringParameter(url, 'fluidsession', fluidSession);
                            $("#website")[0].contentWindow.location = url;
                        });
                    }
                );
            }
        },

        getToken: function(callback) {
            this.socket.send('GET', 'token', {}, function(response) {
                callback(response);
            });
        },

        reload: function () {
            $("#website")[0].contentWindow.location.reload();

        }
    });
});

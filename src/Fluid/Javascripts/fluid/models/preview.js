define(['backbone'], function (Backbone) {
    return Backbone.Model.extend({
        initialize: function (attrs) {
            var root = this;

            this.socket = attrs.socket;
            this.languages = attrs.languages;

            // Track iframe location change
            $("#website").on('load', function(e) { root.verifyFrameStatus(e) });

        },

        getUrl: function() {
            var url = $("#website")[0].contentWindow.location.toString();
            url = RemoveUrlParameter(url, 'fluidbranch');
            url = RemoveUrlParameter(url, 'fluidtoken');
            url = RemoveUrlParameter(url, 'fluidsession');

            return url;
        },

        verifyFrameStatus: function(e) {
            var url = $(e.target).get(0).contentWindow.location.toString();
            var session = getParameterByName(url, 'fluidsession');
            var token = getParameterByName(url, 'fluidtoken');
            var branch = getParameterByName(url, 'fluidbranch');

            if (session === '' || token === '' || branch === '') {
                this.loadPage(url);
            }
        },

        loadPage: function(url) {
            if (typeof url === 'undefined' || url === null) {
                url = "/";
            }

            this.getToken(function(response) {
                url = updateQueryStringParameter(url, 'fluidbranch', fluidBranch);
                url = updateQueryStringParameter(url, 'fluidtoken', response.token);
                url = updateQueryStringParameter(url, 'fluidsession', fluidSession);
                $("#website")[0].contentWindow.location = url;
            });
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

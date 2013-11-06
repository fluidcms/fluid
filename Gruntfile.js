/*global module:false*/
'use strict';

module.exports = function(grunt) {
    // Project configuration.
    grunt.initConfig({

        clean: {
            release: 'public/javascripts'
        },

        requirejs: {
            compile: {
                options: {
                    baseUrl: 'src/FluidJS',
                    mainConfigFile: 'src/FluidJS/bootstrap.js',
                    paths: {
                        "jquery": "../../components/jquery/jquery",
                        "backbone": "../../components/backbone/backbone",
                        "underscore": "../../components/underscore/underscore",
                        "async": "../../components/async/lib/async",
                        "ejs": "../../components/ejs/ejs",
                        "qtip": "../../components/qtip2/jquery.qtip",
                        "when": "../../components/when/when",
                        "text": "../../components/text/text"
                    },
                    findNestedDependencies: true,
                    removeCombined: false,
                    name: 'bootstrap',
                    exclude: [
                        'jquery',
                        'backbone',
                        'underscore',
                        'async',
                        'ejs',
                        'qtip',
                        'text',
                        'when'
                    ],
                    out: 'public/javascripts/fluid-0.0.1.min'
                }
            }
        },

        uglify: {
            options: {
                mangle: false
            },
            my_target: {
                files: {
                    'public/javascripts/vendor/jquery-2.0.3.min.js': ['components/jquery/jquery.js'],
                    'public/javascripts/vendor/backbone-1.1.0.min.js': ['components/backbone/backbone.js'],
                    'public/javascripts/vendor/underscore-1.5.2.min.js': ['components/underscore/underscore.js'],
                    'public/javascripts/vendor/async-0.2.5.min.js': ['components/async/lib/async.js'],
                    'public/javascripts/vendor/autobahnjs-0.8.0.min.js': ['components/autobahnjs/autobahn/autobahn.js'],
                    'public/javascripts/vendor/ejs-0.8.4.min.js': ['components/ejs/ejs.js'],
                    'public/javascripts/vendor/jquery-qtip-2.1.1.min.js': ['components/qtip2/jquery.qtip.js'],
                    'public/javascripts/vendor/requirejs-2.1.9.min.js': ['components/requirejs/require.js'],
                    'public/javascripts/vendor/when-2.5.1.min.js': ['components/when/when.js'],
                    'public/javascripts/vendor/text-2.0.10.min.js': ['components/text/text.js']
                }
            }
        }
    });

    // Load tasks from NPM
    grunt.loadNpmTasks('grunt-contrib-clean');
    grunt.loadNpmTasks('grunt-contrib-requirejs');
    grunt.loadNpmTasks('grunt-contrib-uglify');

    // Default task.
    grunt.registerTask('default', ['clean', 'requirejs', 'uglify']);
};
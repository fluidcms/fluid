/*global module:false*/
'use strict';

var opt = require('./src/FluidJS/options');

module.exports = function(grunt) {
    // Project configuration.
    grunt.initConfig({

        clean: {
            release: 'public/javascripts'
        },

        requirejs: {
            compile: {
                options: opt
            }
        }
    });

    // Load tasks from NPM
    grunt.loadNpmTasks('grunt-contrib-clean');
    grunt.loadNpmTasks('grunt-contrib-requirejs');

    // Default task.
    grunt.registerTask('default', ['clean', 'requirejs']);
};
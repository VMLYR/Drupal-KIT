// Main Gulp file

// The gulp file has been broken down into smaller task files. Each task has its own js file.
// These files can be found in gulp/tasks.
// If you wish to add a new task, simply create a new js file in that directory.

var requireDir = require('require-dir');

// Require all tasks in gulp/tasks, including subfolders
requireDir('./gulp/tasks', { recurse: true });

// 'use strict';
//
// var gulp = require('gulp');
// var HubRegistry = require('gulp-hub');
//
// /* load some files into the registry */
// var hub = new HubRegistry(['tasks/*.js']);
//
// /* tell gulp to use the tasks just loaded */
// gulp.registry(hub);

/* jshint node:true */
module.exports = function( grunt ){
	'use strict';

	grunt.initConfig({
		// setting folder templates
		dirs: {
			js: 'assets/js'
		},

		// Minify .js files.
		uglify: {
			options: {
				preserveComments: 'some'
			},
			vendor: {
				files: {
					'<%= dirs.js %>/jquery.blockUI.min.js': ['<%= dirs.js %>/jquery.blockUI.js'],
					'<%= dirs.js %>/sidebar-login.min.js': ['<%= dirs.js %>/sidebar-login.js']
				}
			}
		},

		// Watch changes for assets
		watch: {
			js: {
				files: [
					'<%= dirs.js %>/*.js',
					'!<%= dirs.js %>/*.min.js',
				],
				tasks: ['uglify']
			}
		},

		shell: {
			options: {
				stdout: true,
				stderr: true
			},
			generatepot: {
				command: [
					'makepot'
				].join( '&&' )
			}
		},

	});

	// Load NPM tasks to be used here
	grunt.loadNpmTasks( 'grunt-shell' );
	grunt.loadNpmTasks( 'grunt-contrib-uglify' );
	grunt.loadNpmTasks( 'grunt-contrib-watch' );

	// Register tasks
	grunt.registerTask( 'default', [
		'uglify'
	]);

	// Just an alias for pot file generation
	grunt.registerTask( 'pot', [
		'shell:generatepot'
	]);

};

module.exports = function (grunt) {

	// Project configuration.
	grunt.initConfig({
		pkg: grunt.file.readJSON('package.json'),
		html2js: {
			options: {
				// custom options, see below
				base: 'templates',
				quoteChar: '\'',
				useStrict: true,
				htmlmin: {
					collapseBooleanAttributes: true,
					collapseWhitespace: true,
					removeAttributeQuotes: false,
					removeComments: true,
					removeEmptyAttributes: false,
					removeRedundantAttributes: true,
					removeScriptTypeAttributes: false,
					removeStyleLinkTypeAttributes: false
				}
			},
			main: {
				src: ['templates/views/**/*.html'],
				dest: 'js/templates.js'
			}
		},

		sass: {
			dist: {
				files:  [
					{
						expand: true,
						cwd: "sass",
						src: ["**/app.scss"],
						dest: "css",
						ext: ".css"
					}
				]
			}
		},

		//@TODO JSHint, comile sass
		watch: {
			scripts: {
				files: ['Gruntfile.js', 'views/*.html'],
				tasks: ['html2js','sass'],
				options: {
					spawn: false,
					interrupt: true,
					reload: true
				}
			}
		},
		// uglify: {
		// 	options: {
		// 		banner: '/*! <%= pkg.name %> <%= grunt.template.today("yyyy-mm-dd") %> */\n'
		// 	},
		// 	build: {
		// 		src: 'src/<%= pkg.name %>.js',
		// 		dest: 'build/<%= pkg.name %>.min.js'
		// 	}
		// }
	});

	// Load the plugin that provides the "uglify" task.
	grunt.loadNpmTasks('grunt-contrib-sass');
	grunt.loadNpmTasks('grunt-contrib-uglify');
	grunt.loadNpmTasks('grunt-html2js');
	grunt.loadNpmTasks('grunt-contrib-watch');
	// Default task(s).
	grunt.registerTask('default', ['html2js', 'sass']);

};
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
					collapseBooleanAttributes: false,
					collapseWhitespace: true,
					removeAttributeQuotes: false,
					removeComments: true,
					removeEmptyAttributes: false,
					removeRedundantAttributes: false,
					removeScriptTypeAttributes: false,
					removeStyleLinkTypeAttributes: false
				}
			},
			main: {
				src: ['templates/views/**/*.html'],
				dest: 'js/templates.js'
			}
		},
		jshint: {
			options: {
				curly: false,
				eqeqeq: true,
				eqnull: true,
				browser: true,
				globals: {
					"angular": true,
					"PassmanImporter": true,
					"OC": true,
					"window": true,
					"console": true,
					"CRYPTO": true,
					"C_Promise": true,
					"forge": true,
					"sjcl": true,
					"jQuery": true,
					"$": true,
					"_": true,
					"oc_requesttoken": true
				}
			},
			all: ['Gruntfile.js', 'js/app/**/*.js']
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
					},
					{
						expand: true,
						cwd: "sass",
						src: ["**/bookmarklet.scss"],
						dest: "css",
						ext: ".css"
					},
					{
						expand: true,
						cwd: "sass",
						src: ["**/public-page.scss"],
						dest: "css",
						ext: ".css"
					}
				]
			}
		},

		//@TODO JSHint
		watch: {
			scripts: {
				files: ['Gruntfile.js', 'templates/views/{,*/}{,*/}{,*/}*.html', 'templates/views/*.html','sass/*','sass/partials/*'],
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
	grunt.loadNpmTasks('grunt-contrib-jshint');
	// Default task(s).
	grunt.registerTask('default', ['html2js', 'sass']);
	grunt.registerTask('hint', ['jshint']);

};
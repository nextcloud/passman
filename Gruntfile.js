module.exports = function (grunt) {
	var jsResources = [];
	// Project configuration.
	grunt.initConfig({
		jsResources: [],
		cssResources: [],
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
				reporter: require('jshint-stylish'),
				curly: false,
				eqeqeq: true,
				eqnull: true,
				browser: true,
				globals: {
					"angular": true,
					"PassmanImporter": true,
					"PassmanExporter": true,
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
			all: ['js/app/**/*.js']
		},
		sass: {
			dist: {
				files: [
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
					},
					{
						expand: true,
						cwd: "sass",
						src: ["**/admin.scss"],
						dest: "css",
						ext: ".css"
					}
				]
			}
		},

		karma: {
			unit: {
				configFile: './karma.conf.js',
				background: false
			}
		},

		//@TODO JSHint
		watch: {
			scripts: {
				files: ['Gruntfile.js', 'templates/views/{,*/}{,*/}{,*/}*.html', 'templates/views/*.html', 'sass/*', 'sass/partials/*'],
				tasks: ['html2js', 'sass'],
				options: {
					spawn: false,
					interrupt: true,
					reload: true
				}
			}
		},
		/**
		 * Build commands
		 */
		mkdir: {
			dist: {
				options: {
					mode: 0700,
					create: ['dist']
				}
			}
		},

		copy: {
			dist: {
				files: [
					// includes files within path
					{
						expand: true,
						src: [
							'**',
							'!templates/*.php',
							'!templates/views/*',
							'!templates/views/*/**',
							'!templates/views',
							'!js/*',
							'!js/*/**',
							'!node_modules/*/**',
							'!node_modules',
							'!css/**/*',
							'!css/*.map',
							'!css/app.*',
							'css/bookmarklet.css',
							'css/public-page.css',
							'css/admin.css',
							'!dist/*',
							'!dist/*/**',
							'!dist',
							'!tests/*/**',
							'!tests/*',
							'!tests', '' +
							'!sass/*/**',
							'!sass/*',
							'!sass',
							'!.drone.yml',
							'!.gitignore',
							'!.jshintrc',
							'!.scrutinizer.yml',
							'!.travis.yml',
							'!Gruntfile.js',
							'!karma.conf.js',
							'!launch_phpunit.sh',
							'!Makefile',
							'!package.json',
							'!phpunit.*',
							'!Dockerfile',
							'!swagger.yaml'
						],
						dest: 'dist/'
					}
				]
			},
			fonts: {
				files: [
					{
						expand: true,
						flatten: false,
						src: ['css/vendor/font-awesome/*'],
						dest: 'dist/'
					}

				]

			},
			settingsJs: {
				files: [
					{
						expand: true,
						flatten: true,
						src: ['js/settings-admin.js'],
						dest: 'dist/js/'
					}

				]
			}
		},


		uglify: {
			options: {
				mangle: false,
				screwIE8: true,
				banner: '/*! <%= pkg.name %> <%= grunt.template.today("yyyy-mm-dd") %> */\n',
			},
			build: {
				old_files_array: [
					'js/vendor/angular/angular.min.js',
					'js/vendor/angular-animate/angular-animate.min.js',
					'js/vendor/angular-cookies/angular-cookies.min.js',
					'js/vendor/angular-resource/angular-resource.min.js',
					'js/vendor/angular-route/angular-route.min.js',
					'js/vendor/angular-sanitize/angular-sanitize.min.js',
					'js/vendor/angular-touch/angular-touch.min.js',
					'js/vendor/angular-local-storage/angular-local-storage.min.js',
					'js/vendor/angular-off-click/angular-off-click.min.js',
					'js/vendor/angularjs-datetime-picker/angularjs-datetime-picker.min.js',
					'js/vendor/angular-translate/angular-translate.min.js',
					'js/vendor/angular-translate/angular-translate-loader-url.min.js',
					'js/vendor/ng-password-meter/ng-password-meter.js',
					'js/vendor/sjcl/sjcl.js',
					'js/vendor/zxcvbn/zxcvbn.js',
					'js/vendor/ng-clipboard/clipboard.min.js',
					'js/vendor/ng-clipboard/ngclipboard.js',
					'js/vendor/ng-tags-input/ng-tags-input.min.js',
					'js/vendor/angular-xeditable/xeditable.min.js',
					'js/vendor/sha/sha.js',
					'js/vendor/llqrcode/llqrcode.js',
					'js/vendor/download.js',
					'js/vendor/ui-sortable/sortable.js', 'js/lib/promise.js',
					'js/lib/crypto_wrap.js',
					'js/app/app.js',
					'js/app/filters/*.js',
					'js/app/services/*.js',
					'js/app/factory/*.js',
					'js/app/directives/*.js',
					'js/importers/import-main.js',
					'js/importers/*.js',
					'js/exporters/exporter-main.js',
					'js/exporters/*.js',
					'js/app/controllers/*.js',
					'js/templates.js'
				],
				files: {
					'dist/js/passman.min.js': [
						'js/vendor/angular/angular.min.js',
						'js/vendor/angular-animate/angular-animate.min.js',
						'js/vendor/angular-cookies/angular-cookies.min.js',
						'js/vendor/angular-resource/angular-resource.min.js',
						'js/vendor/angular-route/angular-route.min.js',
						'js/vendor/angular-sanitize/angular-sanitize.min.js',
						'js/vendor/angular-touch/angular-touch.min.js',
						'js/vendor/angular-local-storage/angular-local-storage.min.js',
						'js/vendor/angular-off-click/angular-off-click.min.js',
						'js/vendor/angularjs-datetime-picker/angularjs-datetime-picker.min.js',
						'js/vendor/angular-translate/angular-translate.min.js',
						'js/vendor/angular-translate/angular-translate-loader-url.min.js',
						'js/vendor/ng-password-meter/ng-password-meter.js',
						'js/vendor/sjcl/sjcl.js',
						'js/vendor/zxcvbn/zxcvbn.js',
						'js/vendor/forge.0.6.9.min.js',
						'js/vendor/download.js',
						'js/vendor/ng-clipboard/clipboard.min.js',
						'js/vendor/ng-clipboard/ngclipboard.js',
						'js/vendor/ng-tags-input/ng-tags-input.min.js',
						'js/vendor/angular-xeditable/xeditable.min.js',
						'js/vendor/sha/sha.js',
						'js/vendor/llqrcode/llqrcode.js',
						'js/vendor/download.js',
						'js/vendor/ui-sortable/sortable.js',
						'js/vendor/papa-parse/papaparse.min.js',
						'js/lib/promise.js',
						'js/lib/crypto_wrap.js',
						'js/app/app.js',
						'js/app/filters/*.js',
						'js/app/services/*.js',
						'js/app/factory/*.js',
						'js/app/directives/*.js',
						'js/importers/import-main.js',
						'js/importers/*.js',
						'js/exporters/exporter-main.js',
						'js/exporters/*.js',
						'js/app/controllers/*.js',
						'js/templates.js',
						'js/settings-admin.js'
					]
				}
			}
		},
		concat: {
			css: {
				src: ['css/vendor/**/*.css', 'css/app.css'],
				dest: 'dist/css/passman.css'
			}
		},
		cssmin: {
			options: {
				shorthandCompacting: false,
				roundingPrecision: -1
			},
			target: {
				files: [
					{
						expand: true,
						cwd: 'dist/css',
						src: ['passman.css'],
						dest: 'dist/css',
						ext: '.min.css'
					},
					{
						expand: true,
						cwd: 'dist/css',
						src: ['bookmarklet.css', 'public-page.css'],
						dest: 'dist/css',
						ext: '.css'
					}
				]
			}
		},
		clean: {
			css: ['dist/css/passman.css']
		},
		replace: {
			dist: {
				files: [
					{
						cwd: 'templates',
						dest: 'dist/templates',
						expand: true,
						src: ['*.php']
					}
				],
				options: {
					patterns: [
						{
							//Grab the /*build-js-start*/ and /*build-js-end*/ comments and everything in-between
							match: /\/\s?\*build\-js\-start[\s\S]*build\-js\-end+\*\//,
							replacement: function (matchedString) {
								jsResources = [];

								var jsArray = matchedString.match(/script\([A-z']+,\s?'([\/A-z.-]+)'\);/g);
								jsArray.forEach(function (file) {
									var regex = /script\([A-z']+,\s?'([\/A-z.-]+)'\);/g;
									var matches = regex.exec(file);
									if (matches) {
										jsResources.push("'js/" + matches[1] + ".js'");

									}
								});
								//Replace the entire build-js-start to build-js-end block with this <script> tag

								return "script('passman', 'passman.min');";
							}
						},
						{
							//Grab the /*build-css-start*/ and /*build-css-end*/ comments and everything in-between
							match: /\/\s?\*build\-css\-start[\s\S]*build\-css\-end+\*\//,
							replacement: function (matchedString) {
								//Replace the entire build-css-start to build-css-end block with this <link> tag
								return "style('passman', 'passman.min');"
							}
						}
					]
				}
			},
			strict: {
				files: [
					{
						cwd: 'dist/js',
						dest: 'dist/js',
						expand: true,
						src: ['*.js']
					}
				],
				options: {
					patterns: [
						{
							//Grab the <!--build-js-start--> and <!--build-js-end--> comments and everything in-between
							match: /"use strict";/,
							replacement: function (matchedString) {
								//Replace the entire build-js-start to build-js-end block with this <script> tag
								return '';
							}
						}
					]
				}
			}
		}

	});

	// Load the plugin that provides the "uglify" task.
	grunt.loadNpmTasks('grunt-contrib-sass');
	grunt.loadNpmTasks('grunt-contrib-uglify');
	grunt.loadNpmTasks('grunt-html2js');
	grunt.loadNpmTasks('grunt-contrib-watch');
	grunt.loadNpmTasks('grunt-contrib-jshint');
	grunt.loadNpmTasks('grunt-karma');
	grunt.loadNpmTasks('grunt-mkdir');
	grunt.loadNpmTasks('grunt-contrib-copy');
	grunt.loadNpmTasks('grunt-contrib-cssmin');
	grunt.loadNpmTasks('grunt-contrib-concat');
	grunt.loadNpmTasks('grunt-contrib-clean');
	grunt.loadNpmTasks('grunt-replace');


	// Default task(s).
	grunt.registerTask('default', ['html2js', 'sass']);
	grunt.registerTask('hint', ['jshint']);
	grunt.registerTask('build', ['sass', 'jshint', 'html2js', 'mkdir:dist', 'copy:dist', 'copy:fonts', 'replace:dist', 'uglify', 'concat:css', 'cssmin', 'clean:css', 'replace:strict', 'copy:settingsJs']);

};
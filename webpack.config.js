const Terser = require( 'terser-webpack-plugin' );
const config = {
	mode: process.env.NODE_ENV,
	context: __dirname + '/assets/js/src',
	module: {
		rules: [
			{
				test: /\.js$/,
				loader: 'babel-loader',
				exclude: /node_modules\/(?!(@google\/markerclusterer)\/).*/,
			},
		],
	},
	optimization: {
		minimizer: [
			new Terser(
				{
					extractComments: false,
					terserOptions: {
						output: {
							comments: false,
							preamble: [
								'/*!',
								'* EDD Fix Order Numbers',
								'*',
								'* @package   EDD - Fix Order Numbers',
								'* @author    Loïc Blascos',
								'* @copyright 2019-2021 Loïc Blascos',
								'*',
								'*/',
							].join( '\n' ),
						},
					},
				},
			),
		],
	},
};

module.exports = [
	{
		...config,
		...{
			entry: './index.js',
			output: {
				path: __dirname + '/assets/js',
				filename: 'build.js',
			},
		},
	},
];

module.exports = function( api ) {

	api.cache( true );

	return {
		plugins: [
			[
				'@babel/plugin-transform-runtime',
				{
					useESModules: true,
					version: '^7.9.0', // Prevent Babel to inline (https://github.com/babel/babel/issues/11013)
				},
			],
		],
		presets: [ '@babel/preset-env' ],
		sourceType: 'unambiguous',
		env: {
			production: {
				plugins: [
					[
						'@wordpress/babel-plugin-makepot',
						{
							output: 'languages/edd-fix-order-numbers.pot',
						},
					],
				],
			},
		},
	};
};

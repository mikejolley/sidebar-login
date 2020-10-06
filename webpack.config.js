// Require path.
const path = require('path');
const DependencyExtractionWebpackPlugin = require('@wordpress/dependency-extraction-webpack-plugin');
const MinifyPlugin = require('babel-minify-webpack-plugin');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');
const config = {
	mode: 'production',
	entry: {
		frontend: './assets/js/frontend.js',
	},
	output: {
		filename: '[name].js',
		path: path.resolve(__dirname, 'build'),
	},
	module: {
		rules: [
			{
				test: /\.js$/,
				include: [path.resolve(__dirname, 'assets/js')],
				use: {
					loader: 'babel-loader?cacheDirectory',
					options: {
						presets: [
							[
								'@babel/preset-env',
								{
									modules: false,
									targets: {
										browsers: [
											'extends @wordpress/browserslist-config',
										],
									},
								},
							],
						],
					},
				},
			},
		],
	},
	plugins: [
		new DependencyExtractionWebpackPlugin({
			injectPolyfill: true,
		}),
		new MinifyPlugin(),
	],
};
const styleConfig = {
	mode: 'production',
	entry: {
		'sidebar-login': './assets/css/sidebar-login.scss',
	},
	output: {
		path: path.resolve(__dirname, 'build'),
		filename: `[name]-style.js`,
	},
	plugins: [
		new MiniCssExtractPlugin({
			filename: `[name].css`,
		}),
	],
	module: {
		rules: [
			{
				test: /\.s[ac]ss$/i,
				use: [
					MiniCssExtractPlugin.loader,
					{ loader: 'css-loader', options: { importLoaders: 1 } },
					'postcss-loader',
					'sass-loader',
				],
			},
		],
	},
};

module.exports = [config, styleConfig];

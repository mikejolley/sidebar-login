// Require path.
const path = require('path');
const ProgressBarPlugin = require('progress-bar-webpack-plugin');
const DependencyExtractionWebpackPlugin = require('@wordpress/dependency-extraction-webpack-plugin');
const chalk = require('chalk');

const getProgressBarPluginConfig = (name) => {
	return {
		format:
			chalk.blue(`Building ${name}`) +
			' [:bar] ' +
			chalk.green(':percent') +
			' :msg (:elapsed seconds)',
		summary: false,
		customSummary: (time) => {
			console.log(
				chalk.green.bold(`${name} assets build completed (${time})`)
			);
		},
	};
};

const config = {
	entry: {
		frontend: './assets/src/js/frontend.js',
	},
	output: {
		filename: '[name].js',
		path: path.resolve(__dirname, 'assets/js'),
	},
	module: {
		rules: [
			{
				test: /\.js$/,
				exclude: /node_modules/,
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
						plugins: [
							require.resolve('@babel/plugin-transform-runtime'),
						].filter(Boolean),
					},
				},
			},
		],
	},
	plugins: [
		new ProgressBarPlugin(getProgressBarPluginConfig('Frontend')),
		new DependencyExtractionWebpackPlugin({
			injectPolyfill: true,
		}),
	],
};

module.exports = config;

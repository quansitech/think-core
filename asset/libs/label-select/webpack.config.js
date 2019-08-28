var webpack = require('webpack');
var path = require('path');
var dir = path.resolve(__dirname);
// const ExtractTextPlugin = require('extract-text-webpack-plugin');
// const CommonsChunkPlugin = require('webpack/lib/optimize/CommonsChunkPlugin');
// const HtmlWebpackPlugin = require('html-webpack-plugin');

module.exports = function() {
    return {
    	mode: 'production',
    	// mode: 'development',
        entry: {
            'label-select': './label-select.js',
        },
        output: {
            filename: '[name].min.js',
            path: path.resolve(dir, 'dist/'),
            libraryTarget: 'umd',
        },
        module: {  
	        rules: [  
	            {  
	                test: /\.less$/,  
	                use: ['style-loader','css-loader','less-loader']
	            },
                {  
                    test: /\.css$/,  
                    use: ['style-loader','css-loader']
                } 
	        ]  
	    },
        externals: [
            'jquery'
        ]
    };
}
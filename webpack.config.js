const path = require('path');
const autoprefixer = require('autoprefixer');
const UglifyJsPlugin = require("uglifyjs-webpack-plugin");
const MiniCssExtractPlugin = require("mini-css-extract-plugin");
const OptimizeCSSAssetsPlugin = require("optimize-css-assets-webpack-plugin");
const WebpackAssetsManifest = require('webpack-assets-manifest');
const LodashPlugin = require('lodash-webpack-plugin');

const postcssLoader = {
  loader: "postcss-loader",
  options: {
    sourceMap: true,
    config: {
      ctx: {
        autoprefixer: {
          browsers: '> 2%'
        }
      }
    },
    plugins: [
      autoprefixer
    ]
  }
};

const cssLoader = {
  loader: "css-loader",
  options: {
    sourceMap: true
  }
};

const sassLoader = {
  loader: "sass-loader",
  options: {
    sourceMap: true
  }
};

const config = {
  context: path.resolve(__dirname),
  entry: {
    app: "./resources/assets/js/app.ts"
  },
  output: {
    filename: "js/[name].js?[contenthash]",
    path: path.resolve(__dirname, "public"),
    publicPath: "/"
  },
  resolve: {
    extensions: [".ts", ".tsx", ".js", ".jsx", ".vue"],
    alias: {
      CSS: path.resolve(__dirname, 'resources/assets/css'),
      JS: path.resolve(__dirname, 'resources/assets/js'),
    },
  },
  module: {
    rules: [
      {
        test: /.jsx?$/,
        exclude: /node_modules/,
        use: [
          'babel-loader'
        ]
      },
      {
        test: /.tsx?$/,
        exclude: /node_modules/,
        use: [
          'babel-loader',
          {
            loader: 'ts-loader',
            options: {
              appendTsSuffixTo: [/\.vue$/],
              reportFiles: [
                '!node_modules/**/*'
              ]
            }
          }
        ]
      },
      {
        test: /\.css$/,
        use: [
          MiniCssExtractPlugin.loader,
          cssLoader,
          postcssLoader,
        ]
      },
      {
        test: /\.scss$/,
        use: [
          MiniCssExtractPlugin.loader,
          cssLoader,
          postcssLoader,
          sassLoader,
        ]
      },
      {
        test: /\.vue$/,
        use: [
          'vue-loader'
        ]
      },
      {
        test: /\.svg$/,
        use: [
          'svg-sprite-loader'
        ]
      },
    ],
  },
  optimization: {
    minimizer: [
      new UglifyJsPlugin({
        cache: true,
        parallel: true,
        sourceMap: false
      }),
      new OptimizeCSSAssetsPlugin()
    ]
  },
  plugins: [
    new MiniCssExtractPlugin({
      filename: "css/[name].css?[contenthash]",
      chunkFilename: "css/[id].css?[contenthash]"
    }),
    new WebpackAssetsManifest(),
    new LodashPlugin()
  ],
};

module.exports = config;
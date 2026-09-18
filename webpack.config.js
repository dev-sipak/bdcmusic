const path = require('path');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');
const CssMinimizerPlugin = require('css-minimizer-webpack-plugin');

class RemoveJsPlugin {
  apply(compiler) {
    compiler.hooks.thisCompilation.tap('RemoveJsPlugin', (compilation) => {
      compilation.hooks.processAssets.tap(
        {
          name: 'RemoveJsPlugin',
          stage: compiler.webpack.Compilation.PROCESS_ASSETS_STAGE_SUMMARIZE,
        },
        (assets) => {
          Object.keys(assets)
            .filter((assetName) => assetName.endsWith('.js') || assetName.endsWith('.js.map'))
            .forEach((assetName) => {
              compilation.deleteAsset(assetName);
            });
        }
      );
    });
  }
}

module.exports = (env, argv) => {
  const isProduction = argv.mode === 'production';

  return {
    entry: path.resolve(__dirname, 'assets/src/scss-entry.js'),
    output: {
      path: path.resolve(__dirname, 'assets/dist'),
      clean: true,
    },
    devtool: isProduction ? false : 'source-map',
    watchOptions: {
      ignored: /node_modules/,
    },
    module: {
      rules: [
        {
          test: /\.scss$/,
          use: [
            MiniCssExtractPlugin.loader,
            {
              loader: 'css-loader',
              options: {
                sourceMap: !isProduction,
                url: false,
              },
            },
            {
              loader: 'sass-loader',
              options: {
                sourceMap: !isProduction,
                sassOptions: {
                  silenceDeprecations: ['import', 'global-builtin'],
                },
              },
            },
          ],
        },
      ],
    },
    plugins: [
      new MiniCssExtractPlugin({
        filename: 'main.css',
      }),
      new RemoveJsPlugin(),
    ],
    optimization: {
      minimize: isProduction,
      minimizer: ['...', new CssMinimizerPlugin()],
    },
    performance: {
      hints: false,
    },
    stats: 'minimal',
  };
};

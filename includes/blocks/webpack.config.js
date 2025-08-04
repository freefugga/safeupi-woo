const WooCommerceDEP = require("@woocommerce/dependency-extraction-webpack-plugin");

module.exports = {
  ...require("@wordpress/scripts/config/webpack.config"),
  plugins: [
    new WooCommerceDEP(), // pulls in wc.* globals instead of bundling
  ],
};

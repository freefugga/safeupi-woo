const settings = window.wc.wcSettings.getSetting("safeupi_data", {});
const label =
  window.wp.htmlEntities.decodeEntities(settings.title) ||
  window.wp.i18n.__("SafeUPi", "safeupi-gateway");
const Content = () => {
  return window.wp.htmlEntities.decodeEntities(settings.description || "");
};
const iconUrl = "/wp-content/plugins/safeupi-woo/assets/safeupi.png";
const Block_Gateway = {
  name: "safeupi",
  label: label,
  content: Object(window.wp.element.createElement)(Content, null),
  edit: Object(window.wp.element.createElement)(Content, null),
  canMakePayment: () => true,
  ariaLabel: label,
  supports: {
    features: ["products"],
  },
  icons: (
    <img
      src={iconUrl}
      alt={label}
      style={{ maxHeight: "24px", maxWidth: "auto" }}
    />
  ),
};
window.wc.wcBlocksRegistry.registerPaymentMethod(Block_Gateway);

# Magento plugin for Paylike

This plugin is *not* developed or maintained by Paylike but kindly made
available by the community.

Released under the MIT license: https://opensource.org/licenses/MIT

You can also find information about the plugin here: https://paylike.io/plugins/magento-1.9

## Supported Magento versions

* The plugin has been tested with most versions of Magento at every iteration. We recommend using the latest version of Magento, but if that is not possible for some reason, test the plugin with your Magento version and it would probably function properly. 
* Magento version last tested on: *1.9.2.3*

## Installation

1. Log in as administrator and upload the tgz file using the magento connect manager
    * If you have installed the plugin before, you may encounter issues as it has been renamed. If this happens please contact [support](https://paylike.io/contact) and we will assist on fixing the issues.
2. After the plugin is installed go to the configuration screen : System-> Configuration (top menu)  -> Sales -> Payment Methods (sidebar menu) -> Paylike payment
3. In this settings screen you need to  add the Public and App key that you can find in your Paylike account.

## Updating settings

Under the extension settings, you can:
 * Update the payment method text in the payment gateways list
 * Update the payment method description in the payment gateways list
 * Update the credit card logos that you want to show (you can change which one you accept under the paylike account).
 * Update the title that shows up in the payment popup 
 * Update the popup description, choose whether you want to show the popup  (the cart contents will show up instead)
 * Add test/live keys
 * Set payment mode (test/live)
 * Change the capture type (Instant/Manual via Paylike Tool)
 
 ## Paylike tool
 
 The paylike tool is located under orders view screen, in the information tab, on the Process Paylike Payment section. 
 You can use this to capture payments in "Delayed" mode, or to refund/void transactions that have been captured/authorized. 
 
  

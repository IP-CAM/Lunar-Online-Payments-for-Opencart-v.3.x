# Lunar Online Payments for Opencart 3.x

Released under the MIT license: https://opensource.org/licenses/MIT

You can also find information about the plugin here: https://lunar.app

*The plugin has been tested with most versions of Opencart at every iteration. We recommend using the latest version of Opencart 3, but if that is not possible for some reason, test the plugin with your OpenCart version and it would probably function properly.*

## Installation

Once you have installed OpenCart, follow these simple steps:
1. Signup at [lunar.app](https://lunar.app) (it’s free)
1. Create an account
1. Create an app key for your OpenCart website
1. Upload the lunar.ocmod.zip file from the Github release in the extensions uploader.
1. Log in as administrator and click  "Extensions" from the top menu then "extension" then "payments" and install the Lunar plugin by clicking the `Install` link listed there.
1. Click the Edit Lunar button
1. Select a store for your configuration
1. Add the Public and App key that you can find in your Lunar account and enable the plugin
1. Save the settings

## Updating settings

Under the extension settings, you can:
 * Choose the OpenCart store to make settings for
 * Update the payment method text in the payment gateways list
 * Update the payment method description in the payment gateways list
 * Update the title that shows up in the payment popup
 * Add public & app keys
 * Change the capture type (Instant/Delayed)
 * Change the order statuses that the orders will get after a certain payment action is done (authorization/capture/refund/cancel)

 ## How to capture / manage transactions

  In Delayed mode you can make transactions (full capture, refund, cancel) from admin panel, for each order info page, adding a history to the order. 
  The `Order Status` that is wanted to be set for specific transaction must  be identical with that set in Lunar extension page (Advanced section/tab). By default it is `Completed` for capture, `Refunded` for refund and `Canceled Reversal` for cancel an order.

1. Capture
    * In Instant mode, the orders are captured automatically
    * In Delayed mode you can do this in admin panel, order info page, adding **`Completed`** order status history to the order.

2. Refund
    * In Delayed mode you can do this in admin panel, order info page, adding **`Refunded`** order status history to the order.

3. Cancel
    * In Delayed mode you can do this in admin panel, order info page, adding **`Canceled Reversal`** order status history to the order.


## Available features

### Multistore support
* The Lunar multi-store functionality allows the merchant to have different sets of keys for each store.
* You need to have a separate merchant account for a single store to keep Lunar transactions for each store independently.

### Transactions
1. Capture
    * Opencart admin panel: full capture
    * Lunar admin panel: full/partial capture
2. Refund
    * Opencart admin panel: full refund
    * Lunar admin panel: full/partial refund
3. Cancel
    * Opencart admin panel: full cancel
    * Lunar admin panel: full/partial cancel

## Changelog

#### 1.0.0:
* Initial version

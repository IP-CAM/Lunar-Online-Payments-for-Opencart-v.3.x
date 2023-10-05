# Lunar Online Payments for Opencart 3.x and 2.3+

Released under the MIT license: https://opensource.org/licenses/MIT

You can also find information about the plugin here: https://lunar.app

*The plugin has been tested with most versions of Opencart at every iteration. We recommend using the latest version of Opencart 3, but if that is not possible for some reason, test the plugin with your OpenCart version and it would probably function properly.*

## Prerequisites

- The plugin works with vQmod, but also with OCMOD, no need to install vQmod if you don't already need it.

## Installation

Once you have installed OpenCart, follow these simple steps:
1. Signup at [lunar.app](https://lunar.app) (it’s free)
1. Create an account
1. Create an app key for your OpenCart website
1. Upload the lunar.ocmod.zip file from the release in the extensions uploader.
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
 * Change the order statuses that the orders will get after a certain payment action is done (authorization/capture/refund/void)

 ## How to capture / manage transactions

  The transactions will show up under **`Sales -> Lunar Payments`** side menu. Here you can see capture/refund/void transactions depending on their status. Alternatively Lunar payments can be accessed from `SITE_URL/admin/index.php?route=extension/payment/lunar/payments` and they can be reached by clicking the green button at the top right of the extension settings page

  In Delayed mode you can do transactions (full capture, refund, void) from admin panel, for each order info page, adding a history to the order. The `Order Status` that is wanted to be set for specific transaction must  be identical with that set in Lunar extension page (Advanced section/tab). By default it is `Completed` for capture, `Refunded` for refund and `Voided` for void an order.

1. Capture
    * In Instant mode, the orders are captured automatically
    * In Delayed mode you can do this in admin panel, order info page, adding **`Completed`** order status history to the order.
    * OR
    * In Delayed mode you can do this in admin panel Lunar Payments in Action section in the table.
2. Refund
    * In Delayed mode you can do this in admin panel, order info page, adding **`Refunded`** order status history to the order.
    * OR
    * To Refund an order you can do this in admin panel Lunar Payments in Action section in the table.
3. Void
    * In Delayed mode you can do this in admin panel, order info page, adding **`Voided`** order status history to the order.
    * OR
    * To Void an order you can do this in admin panel Lunar Payments in Action section in the table.

## Available features

### Multistore support
    * The Lunar multi-store functionality allows the merchant to have different sets of keys for each store.
    * You need to have a separate merchant account for a single store to keep Lunar transactions for each store independently.

### Transactions
    1. Capture
        * Opencart admin panel: full capture
        * Lunar admin panel: full/partial capture
    2. Refund
        * Opencart admin panel: full/partial refund (only full refund from order view page)
        * Lunar admin panel: full/partial refund
    3. Void
        * Opencart admin panel: full void
        * Lunar admin panel: full/partial void

## Changelog

#### 1.0.0:
* Initial version

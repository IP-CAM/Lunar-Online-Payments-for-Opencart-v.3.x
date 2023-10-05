/// <reference types="cypress" />

'use strict';

import { PluginTestHelper } from './test_helper.js';

export var TestMethods = {

    /** Admin & frontend user credentials. */
    StoreUrl: (Cypress.env('ENV_ADMIN_URL').match(/^(?:http(?:s?):\/\/)?(?:[^@\n]+@)?(?:www\.)?([^:\/\n?]+)/im))[0],
    AdminUrl: Cypress.env('ENV_ADMIN_URL'),
    RemoteVersionLogUrl: Cypress.env('REMOTE_LOG_URL'),

    /** Construct some variables to be used bellow. */
    ShopName: 'opencart3',
    VendorName: 'lunar',
    UserTokenFromUrl: '',
    PaymentMethodsAdminUrl: '/index.php?route=extension/payment/lunar',
    OrdersPageAdminUrl: '/index.php?route=sale/order',

    /**
     * Login to admin backend account
     */
    loginIntoAdminBackend() {
        cy.loginIntoAccount('input[name=username]', 'input[name=password]', 'admin');
        /**
         * Get the token from the URL
         * to use when accessing administrator URLs.
         */
         cy.url().then($url => {
            TestMethods.UserTokenFromUrl = $url.split('user_token=')[1];
        })
    },
    /**
     * Login to client|user frontend account
     */
    loginIntoClientAccount() {
        cy.loginIntoAccount('input[name=email]', 'input[name=password]', 'client');
    },

    /**
     * Modify plugin settings
     * @param {String} captureMode
     */
    changeCaptureMode(captureMode) {
        /** Go to payment method. */
        this.goToAdminPage(this.PaymentMethodsAdminUrl);

        /** Select capture mode. */
        cy.selectOptionContaining('#module_capture_mode', captureMode)

        /** Save. */
        cy.get('button[form=module-config-form]').click();
    },

    /**
     * Make payment with specified currency and process order
     *
     * @param {String} currency
     * @param {String} paymentAction
     * @param {Boolean} partialAmount
     */
     payWithSelectedCurrency(currency, paymentAction, partialAmount = false) {
        /** Make an instant payment. */
        it(`makes a payment with "${currency}"`, () => {
            this.makePaymentFromFrontend(currency);
        });

        /** Process last order from admin panel. */
        it(`process (${paymentAction}) an order from admin panel`, () => {
            this.processOrderFromAdmin(paymentAction, partialAmount);
        });
    },

    /**
     * Make an instant payment
     * @param {String} currency
     */
    makePaymentFromFrontend(currency) {
        /** Go to store frontend. */
        cy.goToPage(this.StoreUrl);

        /** Change currency. */
        this.changeShopCurrency(currency);

        cy.wait(500);

        /**
         * Select specific product.
         */
        var randomInt = PluginTestHelper.getRandomInt(/*max*/ 1);
        if (0 === randomInt) {
            cy.goToPage(this.StoreUrl + '/index.php?route=product/product&product_id=40');
        } else {
            cy.goToPage(this.StoreUrl + '/index.php?route=product/product&product_id=43');
        }

        cy.get('#button-cart').click();

        /** Wait the product to add to cart. */
        cy.wait(1000);

        /** Go to checkout. */
        cy.goToPage(this.StoreUrl + '/index.php?route=checkout/checkout');

        /** Continue. */
        cy.wait(1000);
        cy.get('#button-payment-address').click();
        cy.wait(500);
        cy.get('#button-shipping-address').click();
        cy.wait(500);
        cy.get('#button-shipping-method').click();
        cy.wait(500);

        /** Choose payment method. */
        cy.get(`input[value=${this.VendorName}]`).click();

        /** Agree Terms & Conditions. */
        cy.get('input[name=agree]').click();

        /** Continue. */
        cy.get('#button-payment-method').click();

        /** Wait to load javascript SDK. */
        cy.wait(2000);

        /** Check amount. */
        cy.get('tfoot> tr:nth-child(3) > td:nth-child(2)').then($grandTotal => {
            var expectedAmount = PluginTestHelper.filterAndGetAmountInMinor($grandTotal, currency);
            cy.get('#module-payment-widget').invoke('attr', 'data-amount').then(amount => {
                expect(expectedAmount).to.eq(Number(amount));
            });
        });

        /** Show payment popup. */
        cy.get('#button-confirm').click();

        /**
         * Fill in popup input.
         */
         PluginTestHelper.fillAndSubmitPopup();

        cy.wait(2000);

        cy.get('h1').should('contain', 'Your order has been placed!');
    },

    /**
     * Process last order from admin panel
     * @param {String} paymentAction
     * @param {Boolean} partialAmount
     */
    processOrderFromAdmin(paymentAction, partialAmount = false) {
        /** Go to admin & get order statuses to be globally used. */
        this.getOrderStatuses();

        /** Go to admin orders page. */
        this.goToAdminPage(this.OrdersPageAdminUrl);

        /** Click on first (latest in time) order from orders table. */
        cy.get('i.fa.fa-eye').first().click();

        /**
         * Take specific action on order
         */
        this.paymentActionOnOrderAmount(paymentAction, partialAmount);
    },

    /**
     * Capture an order amount
     * @param {String} paymentAction
     * @param {Boolean} partialAmount
     */
     paymentActionOnOrderAmount(paymentAction, partialAmount = false) {
        switch (paymentAction) {
            case 'capture':
                cy.get('@orderStatusForCapture').then(orderStatusForCapture => {
                    cy.selectOptionContaining('#input-order-status', orderStatusForCapture);
                });
                break;
            case 'refund':
                if (partialAmount) {
                    /**
                     * Put 8 major units to be refunded.
                     * Premise: any product must have price >= 8.
                     */
                    this.goToAdminPage('/index.php?route=extension/payment/lunar/payments');
                    cy.get('.btn.btn-default.dropdown-toggle').first().click();
                    cy.get('a[data-type=Refund]').first().click();
                    cy.get('#plt-amount').clear().type(8);
                    cy.get('.runtransaction').click();
                    /** Check if success message. */
                    cy.get('.alert.alert-success').should('be.visible');
                    /** No further action. */
                    return;
                } else {
                    cy.get('@orderStatusForRefund').then(orderStatusForRefund => {
                        cy.selectOptionContaining('#input-order-status', orderStatusForRefund);
                    });
                }
                break;
            case 'void':
                cy.get('@orderStatusForVoid').then(orderStatusForVoid => {
                    cy.selectOptionContaining('#input-order-status', orderStatusForVoid);
                });
                break;
        }

        cy.get('#button-history').click();

        cy.wait(300);

        /** Check if success message. */
        cy.get('.alert.alert-success').should('be.visible');
    },

    /**
     * Change shop currency in frontend
     */
    changeShopCurrency(currency) {
        cy.get('.btn.btn-link.dropdown-toggle').click();
        cy.get(`button[name=${currency}]`).click();
    },

    /**
     * Get order statuses from plugin settings
     */
     getOrderStatuses() {
        /** Go to payment method settings page. */
        this.goToAdminPage(this.PaymentMethodsAdminUrl);

        /** Select advanced tab. */
        cy.get('a[href="#tab-advanced_settings"]').click();

        /** Get order statuses for capture, refund & void. */
        cy.get('#module_capture_status_id > option[selected=selected]').then($captureStatus => {
            cy.wrap($captureStatus.text()).as('orderStatusForCapture');
        });
        cy.get('#module_refund_status_id > option[selected=selected]').then($refundStatus => {
            cy.wrap($refundStatus.text()).as('orderStatusForRefund');
        });
        cy.get('#module_void_status_id > option[selected=selected]').then($voidStatus => {
            cy.wrap($voidStatus.text()).as('orderStatusForVoid');
        });
    },

    /**
     * Get Shop & plugin versions and send log data.
     */
    logVersions() {
        /** Get framework version. */
        cy.get('#footer').then($frameworkVersion => {
            var frameworkVersion = ($frameworkVersion.text()).replace(/.*[^0-9.]/g, '');
            cy.wrap(frameworkVersion).as('frameworkVersion');
        });

        this.goToAdminPage(this.PaymentMethodsAdminUrl);

        /** Get plugin version. */
        cy.get('.panel-title').invoke('attr', 'data-module-version').then($pluginVersion => {
            cy.wrap($pluginVersion).as('pluginVersion');
        });

        /** Get global variables and make log data request to remote url. */
        cy.get('@frameworkVersion').then(frameworkVersion => {
            cy.get('@pluginVersion').then(pluginVersion => {

                cy.request('GET', this.RemoteVersionLogUrl, {
                    key: frameworkVersion,
                    tag: this.ShopName,
                    view: 'html',
                    ecommerce: frameworkVersion,
                    plugin: pluginVersion
                }).then((resp) => {
                    expect(resp.status).to.eq(200);
                });
            });
        });
    },

    /**
     * Get and add token to admin pages url
     * @param {String} url
     */
    goToAdminPage(url) {
        cy.goToPage(url + `&user_token=${this.UserTokenFromUrl}`);
    },
}
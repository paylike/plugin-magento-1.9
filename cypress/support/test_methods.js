/// <reference types="cypress" />

'use strict';

import { PaylikeTestHelper } from './test_helper.js';

export var TestMethods = {

    /** Admin & frontend user credentials. */
    StoreUrl: (Cypress.env('ENV_ADMIN_URL').match(/^(?:http(?:s?):\/\/)?(?:[^@\n]+@)?(?:www\.)?([^:\/\n?]+)/im))[0],
    AdminUrl: Cypress.env('ENV_ADMIN_URL'),
    RemoteVersionLogUrl: Cypress.env('REMOTE_LOG_URL'),

    /** Construct some variables to be used bellow. */
    ShopName: 'magento19',
    PaylikeName: 'paylike',
    PaymentMethodsAdminUrl: '/system_config/edit/section/payment/',
    OrdersPageAdminUrl: '/sales_order',
    ConfigCurrencyAdminUrl: '/system_config/edit/section/currency/#currency_options-link',

    /**
     * Login to admin backend account
     */
    loginIntoAdminBackend() {
        cy.goToPage(this.AdminUrl);
        cy.loginIntoAccount('input[name="login[username]"]', 'input[name="login[password]"]', 'admin');
    },
    /**
     * Login to client|user frontend account
     */
    loginIntoClientAccount() {
        cy.goToPage(this.StoreUrl + '/customer/account/login');
        cy.loginIntoAccount('input[name="login[username]"]', 'input[name="login[password]"]', 'client');
    },

    /**
     * Modify Paylike settings
     * @param {String} captureMode
     */
    changePaylikeCaptureMode(captureMode) {
        /** Go to Paylike payment method. */
        cy.goToPage(this.PaymentMethodsAdminUrl);

        PaylikeTestHelper.setPositionRelativeOn('.content-header-floating');

        /** Select capture mode. */
        cy.get('#payment_paylike_payment_action').select(captureMode);

        /** Save. */
        cy.get('.scalable.save').first().click();
    },

    /**
     * Make payment with specified currency and process order
     *
     * @param {String} currency
     * @param {String} paylikeAction
     * @param {Boolean} partialAmount
     */
     payWithSelectedCurrency(currency, paylikeAction, partialAmount = false) {
        /** Make an instant payment. */
        it(`makes a Paylike payment with "${currency}"`, () => {
            this.makePaymentFromFrontend(currency);
        });

        /** Process last order from admin panel. */
        it(`process (${paylikeAction}) an order from admin panel`, () => {
            this.processOrderFromAdmin(paylikeAction, partialAmount);
        });
    },

    /**
     * Make an instant payment
     * @param {String} currency
     */
    makePaymentFromFrontend(currency) {
        // /** Clear local storage to show currency change. */
        // cy.clearLocalStorage();
        /**
         * Go to specific product page.
         */
        // cy.goToPage(this.StoreUrl, {timeout: 20000});
        // cy.goToPage(this.StoreUrl + '/elizabeth-knit-top-596.html', {timeout: 20000});
        cy.goToPage(this.StoreUrl + '/elizabeth-knit-top-596.html');

        // this.changeShopCurrency(currency);

        // var randomInt = PaylikeTestHelper.getRandomInt(/*max*/ 4);
        // cy.get('.products-grid .item.last li > a', {timeout: 20000}).eq(randomInt).click();

        /** Show selects for attribute and choose first option. */
        // cy.get('.required-entry.super-attribute-select').first().invoke('show');
        // cy.get('.required-entry.super-attribute-select').last().invoke('show');

        // cy.wait(5000);
        // cy.get('.required-entry.super-attribute-select').first().select(1);
        // cy.get('.required-entry.super-attribute-select').last().select(1);
        cy.get('#swatch21 > .swatch-label > img').click();
        cy.get('#swatch80 > .swatch-label').click();




        /** Add to cart. */
        cy.get('.add-to-cart-buttons .button.btn-cart').click();


        /** Go to onepage checkout. */
        cy.goToPage(this.StoreUrl + '/checkout/onepage');

        /** Continue. */
        cy.get('#billing-buttons-container > button', {timeout: 20000}).click();
        cy.get('#s_method_flatrate_flatrate', {timeout: 20000}).click();
        cy.get('#shipping-method-buttons-container > button', {timeout: 20000}).click();
        // cy.get('#billing-buttons-container > button', {timeout: 6000}).click();
        // cy.get('#shipping-method-buttons-container > button', {timeout: 6000}).click();


        /** Choose Paylike. */
        cy.get(`input[value*=${this.PaylikeName}]`, {timeout: 20000}).click();
        // cy.get(`input[value*=${this.PaylikeName}]`, {timeout: 6000}).click();

        /** Continue. */
        cy.get('#payment-buttons-container > button', {timeout: 20000}).click();

        /** Get amount. */
        cy.get('strong > .price', {timeout: 20000}).then($grandTotal => {
            var expectedAmount = PaylikeTestHelper.filterAndGetAmountInMinor($grandTotal, currency);
            // cy.wrap(expectedAmount).as('expectedAmount');
            cy.window().then($win => {
                expect(expectedAmount).to.eq(Number($win.paylikeminoramount));
            });
        });

        /** Show paylike popup. */
        cy.get('#review-buttons-container > button').click();

        /**
         * Fill in Paylike popup.
         */
         PaylikeTestHelper.fillAndSubmitPaylikePopup();

        cy.get('.page-title > h1', {timeout: 20000}).should('contain', 'Your order has been received.');
    },

    /**
     * Process last order from admin panel
     * @param {String} paylikeAction
     * @param {Boolean} partialAmount
     */
    processOrderFromAdmin(paylikeAction, partialAmount = false) {
        /** Go to admin orders page. */
        cy.goToPage(this.OrdersPageAdminUrl);

        // PaylikeTestHelper.setPositionRelativeOn('.content-header-floating');

        /** Remove fixed header. */
        cy.get('.content-header-floating').then(($fixedHeader) => {
            $fixedHeader.remove();
        });

        // /** Wait to load orders. */
        // cy.wait(5000);

        // /** Remove spinner elements from dom. */
        // cy.get('div.sticky-header').then(($stickyHeader) => {
        //     $stickyHeader.remove();
        // });
        // cy.get('div[data-role="spinner"]').then(($spinner) => {
        //     $spinner.remove();
        // });

        // /** Set position relative on toolbars. */
        // PaylikeTestHelper.setPositionRelativeOn('header.page-header.row');
        // PaylikeTestHelper.setPositionRelativeOn('tr[data-bind="foreach: {data: getVisible(), as: \'$col\'}"]');
        // PaylikeTestHelper.setPositionRelativeOn('.admin__data-grid-header');
        // PaylikeTestHelper.setPositionRelativeOn('.page-main-actions');
        // PaylikeTestHelper.setPositionRelativeOn('div[data-ui-id="page-actions-toolbar-content-header"]');

        /** Click on first (latest in time) order from orders table. */
        // cy.get('#sales_order_grid_table > tbody > tr', {timeout: 30000}).first().click();
        cy.get('#sales_order_grid_table > tbody > tr', {timeout: 6000}).first().click();

        /**
         * Take specific action on order
         */
        this.paylikeActionOnOrderAmount(paylikeAction, partialAmount);
    },

    /**
     * Capture an order amount
     * @param {String} paylikeAction
     * @param {Boolean} partialAmount
     */
     paylikeActionOnOrderAmount(paylikeAction, partialAmount = false) {

        /** Remove fixed header. */
        cy.get('.content-header-floating').then(($fixedHeader) => {
            $fixedHeader.remove();
        });

        switch (paylikeAction) {
            case 'capture':
                cy.get('button[onclick*="sales_order_invoice"]').click();
                cy.get('button.scalable.save.submit-button').click();
                break;
            case 'refund':
                /** Access invoices table by removing display:none from it. */
                cy.get('#sales_order_view_tabs_order_invoices_content').invoke('show');
                cy.get('#order_invoices_table tbody tr', {timeout: 30000}).first().click();
                cy.get('button[onclick*="sales_order_creditmemo"]').first().click();

                /** Keep partial amount. */
                if (partialAmount) {
                    /**
                     * Put 2 major units to be subtracted from amount.
                     * Premise: any product must have price >= 2.
                     * *** Press enter after changing input to activate update button
                     */
                    cy.get('input[name="creditmemo[adjustment_negative]"]').clear().type(`${2}{enter}`);
                }
                /** Submit. */
                cy.get('button[onclick*="submitCreditMemo\("]').click();
                break;
            case 'void':
                cy.get('button[onclick*="voidPayment"]').click();
                cy.on('window:alert',($alert)=>{
                    expect($alert).to.contains('Are you sure you want to void the payment?');
                 });
                break;
        }

        /** Check if success message. */
        // cy.get('#messages .success-msg', {timeout: 20000}).should('be.visible');
        cy.get('#messages .success-msg', {timeout: 6000}).should('be.visible');
    },

    /**
     * Change shop currency in frontend
     */
    changeShopCurrency(currency) {
        cy.selectOptionContaining('#select-currency', currency);
    },

    /**
     * Get Shop & Paylike versions and send log data.
     */
    logVersions() {
        /** Go to payment methods page. */
        cy.goToPage(this.StoreUrl + '/downloader');

        cy.loginIntoAccount('input[name="username"]', 'input[name="password"]', 'admin');

        /** Get framework version. */
        cy.get('#connect_packages_0 table tbody tr .a-center').first().then($frameworkVersion => {
            var frameworkVersion = ($frameworkVersion.text()).replace(/[^0-9.]/g, '');
            cy.wrap(frameworkVersion).as('frameworkVersion');
        });

        /** Get Paylike version. */
        cy.get('#connect_packages_0 table tr td').contains('Paylike_Payment').parent().then($pluginVersion => {
            var pluginVersion = ($pluginVersion.text()).replace(/[^0-9.]/g, '');
            cy.wrap(pluginVersion).as('pluginVersion');
        });


        /** Get global variables and make log data request to remote url. */
        cy.get('@frameworkVersion').then(frameworkVersion => {
            cy.get('@pluginVersion').then(pluginVersion => {

                // cy.request('GET', this.RemoteVersionLogUrl, {
                //     key: frameworkVersion,
                //     tag: this.ShopName,
                //     view: 'html',
                //     ecommerce: frameworkVersion,
                //     plugin: pluginVersion
                // }).then((resp) => {
                //     expect(resp.status).to.eq(200);
                // });
            });
        });
    },
}
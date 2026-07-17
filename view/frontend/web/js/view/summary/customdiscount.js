define([
    'Magento_Checkout/js/view/summary/abstract-total',
    'Magento_Checkout/js/model/quote'
], function (Component, quote) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Ict_LoyaltyTier/summary/customdiscount'
        },
        totals: quote.getTotals(),

        isDisplayed: function () {
            return this.isFullMode() && this.getPureValue() !== 0;
        },

        getTitle: function () {
            var segment = this.getSegment();

            return segment ? segment.title : 'Tire Discount';
        },

        getPureValue: function () {
            var segment = this.getSegment();

            return segment ? parseFloat(segment.value) : 0;
        },

        getValue: function () {
            return this.getFormattedPrice(this.getPureValue());
        },

        getSegment: function () {
            var totalSegments;

            if (!this.totals() || !this.totals()['total_segments']) {
                return null;
            }

            totalSegments = this.totals()['total_segments'];

            return totalSegments.find(function (segment) {
                return segment.code === 'customer_discount';
            }) || null;
        }
    });
});

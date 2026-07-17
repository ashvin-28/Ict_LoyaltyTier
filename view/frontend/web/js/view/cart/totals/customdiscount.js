define([
    'Ict_LoyaltyTier/js/view/summary/customdiscount'
], function (Component) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Ict_LoyaltyTier/cart/totals/customdiscount'
        },

        isDisplayed: function () {
            return this.getPureValue() !== 0;
        }
    });
});

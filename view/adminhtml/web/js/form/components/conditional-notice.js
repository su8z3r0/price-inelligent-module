define([
    'Magento_Ui/js/form/components/html'
], function (Component) {
    'use strict';

    return Component.extend({
        defaults: {
            sourceTypeValue: '',
            expectedValue: '',
            imports: {
                sourceTypeValue: '${ $.provider }:data.source_type'
            },
            listens: {
                sourceTypeValue: 'onSourceTypeChange'
            }
        },

        /**
         * Update visibility based on source type
         */
        onSourceTypeChange: function (value) {
            this.visible(value === this.expectedValue);
        }
    });
});

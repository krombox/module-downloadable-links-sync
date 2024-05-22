define([
    'jquery',
    'Magento_Ui/js/modal/confirm',
    'mage/translate'
], function ($, confirm) {
    'use strict';

    /**
     * @param {String} url
     * @returns {Object}
     */
    function getForm(url)
    {
        return $('<form>', {
            'action': url,
            'method': 'POST'
        }).append($('<input>', {
            'name': 'form_key',
            'value': window.FORM_KEY,
            'type': 'hidden'
        }));
    }

    $('#product-edit-sync-links-button').on('click', function () {
        let msg = $.mage.__('Are you sure you want to do this?'),
            url = $('#product-edit-sync-links-button').data('url');

        confirm({
            'content': msg,
            'actions': {

                /**
                 * 'Confirm' action handler.
                 */
                confirm: function () {
                    $('body').trigger('processStart');
                    getForm(url).appendTo('body').trigger('submit');
                }
            }
        });

        return false;
    });
});

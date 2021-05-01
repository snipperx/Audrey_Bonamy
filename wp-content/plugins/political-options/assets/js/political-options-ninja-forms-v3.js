var SubmitController = Marionette.Object.extend({

    initialize: function () {
        this.listenTo(Backbone.Radio.channel('forms'), 'submit:response', this.checkSubmitResponse);
    },

    checkSubmitResponse: function (response) {
        if ('undefined' !== response.data && 'undefined' !== typeof response.data.redirect) {
            window.location = response.data.redirect;
        }
    }

});

jQuery(document).ready(function ($) {

    new SubmitController();

});

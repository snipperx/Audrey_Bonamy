(function ($) {

    $(document).ready(function () {

        new NF3FieldController();

    });

    var NF3FieldController = Marionette.Object.extend({

        initialize: function () {
            this.listenTo(Backbone.Radio.channel('fields'), 'render:view', this.checkField);
        },

        checkField: function (field) {
            var $el = $(field.el);

            setTimeout(function () {
                checkStyles($el.closest('nf-field'));
            }, 0);

        }

    });

    function checkStyles($nf_field) {

        // label-inside
        var $label_inside = $nf_field.has('.label-inside');

        $label_inside.each(function (index, element) {
            var $el = $(element);
            var $label = $el.find('label');
            var $input = $el.find('input, textarea, select');
            var $labelText = $.trim($label.text());

            $input
                .val($labelText)
                .on({
                    'focus': function () {
                        if ($.trim($input.val()) == $labelText) {
                            $input.val('');
                        }
                    },
                    'blur': function () {
                        if ($.trim($input.val()).length === 0) {
                            $input.val($labelText);
                        }
                    }
                });

        });

        // half-width class
        if ($nf_field.find('.field-half-width').length > 0) {
            $nf_field.addClass('field-half-width-wrap');
        }

    }

})(jQuery);
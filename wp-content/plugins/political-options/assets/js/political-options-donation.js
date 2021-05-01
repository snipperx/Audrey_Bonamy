jQuery(document).ready(function($) {
    if($("input[name='ninja_forms_integration']:checked").val() == 'disabled') {
        $('#ninja-forms-integration-area').hide();
        $('#paypal-area').show();
        $('#paypal-details-area').show();
        $('#paypal-on-ninja-forms-area').hide();
    }
    if($("input[name='ninja_forms_integration']:checked").val() == 'enabled') {
        $('#ninja-forms-integration-area').show();
        $('#paypal-area').hide();
        $('#paypal-on-ninja-forms-area').show();
    }

    $('.ninja_forms_integration').on('change', function() {
        if($(this).val() == 'disabled') {
            $('#ninja-forms-integration-area').hide();
            $('#paypal-area').show();
            $('#paypal-on-ninja-forms-area').hide();
            if($("input[name='paypal_mode']:checked").val() == 'disabled') {
                $('#paypal-details-area').hide();
            } else {
                $('#paypal-details-area').show();
            }
        } else {
            $('#ninja-forms-integration-area').show();
            $('#paypal-area').hide();
            $('#paypal-on-ninja-forms-area').show();
        }
    });

    if($("input[name='paypal_mode']:checked").val() == 'disabled')
        $('#paypal-details-area').hide();

    $('.paypal_mode').on('change', function() {
        if($(this).val() == 'disabled')
            $('#paypal-details-area').hide();
        else
            $('#paypal-details-area').show();
    });

    $('#ninja_form_name').on('change', function() {
        $.ajax({
            type: 'POST',
            url: ajaxurl,
            data: {
                action: 'get_ninja_fields_by_form_id',
                id: parseInt($(this).val())
            }
        }) .done(function( data ) {
            var obj = $.parseJSON(data);
            var fields = $('select#ninja_donation_field');
            fields.empty();
            fields.append($('<option></option>').attr('value', 0).text('- Select a field -'));
            for (row in obj) {
              fields.append($('<option></option>').attr('value', row).text(obj[row]));
            }
        });
    });
});

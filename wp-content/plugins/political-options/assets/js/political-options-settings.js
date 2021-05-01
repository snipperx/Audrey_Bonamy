jQuery(document).ready(function($) {

    // Show/hide landing page options
    landingToggle = 'input[name="landing_enabled"]';
    $landingOptions = $('.landing-page-options');
    if($(landingToggle+":checked").val() == 'disabled') {
        $landingOptions.hide();
    }

    $(landingToggle).on('change', function() {
        if($(this).val() == 'disabled') {
            $landingOptions.hide();
        } else {
            $landingOptions.show();
        }
    });

    // Show/hide landing page custom URL field
    $landingUrlToggle = $('#landing_redirect_id');
    $landingUrlField = $('#landing-custom-url-field');
    if($landingUrlToggle.val() !== 'custom') {
        $landingUrlField.hide();
    }

    $landingUrlToggle.on('change', function() {
        if($(this).val() == 'custom') {
            $landingUrlField.slideDown();
        } else {
            $landingUrlField.slideUp();
        }
    });

});

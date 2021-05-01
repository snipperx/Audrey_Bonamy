<?php
/*
    Extension Name: Theme Utilities
    Extension URI: http://runwaywp.com
    Version: 1.0
    Description: Add on functionality for enhanced theme options, meta options, etc...
    Author: Parallelus
    Author URI: http://para.llel.us
*/

// Do this first
if (is_admin()) {
	require('demo-data.php');
	require('hooks-filters-theme-options.php');
}

// Now load everything else
require('class-color.php');
require('class-minify-js.php');
require('custom-css.php');
require('custom-js.php');
require('default-sidebars.php');
require('functions-helpers.php');
require('functions-pagination.php');
require('functions-template.php');
if (!function_exists('the_static_block')) {
	require('post-type-static-block.php');
}
require('meta-boxes.php');
require('hooks-actions.php');
require('hooks-filters.php');

// require('beaver_builder_modules.php');
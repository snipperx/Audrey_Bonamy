<?php
/**
 * The home page file. Called for blog posts set to static page.
 */

$home_page = (function_exists('theme_is_custom_home_page')) ? theme_is_custom_home_page() : is_front_page();

// out($home_page);

if ($home_page) {
	get_template_part('front-page');
} else {
	get_template_part('index');
}


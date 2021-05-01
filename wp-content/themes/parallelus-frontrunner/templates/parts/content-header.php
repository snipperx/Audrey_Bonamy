<?php
/**
 * The content of the header
 */
?>


<?php

$title = get_the_title();
$content = apply_filters('theme_header_subtitle', '');

// Clean up
if (isset($content) && !empty($content)) {
	$content = html_entity_decode($content);
	$content = '<p>'.stripslashes($content).'</p>';
}

// Filter
$title   = apply_filters('theme_header_title', $title);
$content = apply_filters('theme_header_content', $content);

if (!empty($title) || !empty($content)) :
	?>

	<div class="header-inner">
		<div class="top-header-inner">
			<div class="inner-content">
				<div class="container">
					<div class="row">
						<div class="col-md-12">
						<?php

						do_action('before_header_title'); // make accessible to add custom content before title

						// Output the title and content text
						if (!empty($title)) {
							?>
							<header class="page-header intro-wrap">
								<h1 class="page-title"><?php echo wp_kses_post($title); ?></h1>
							</header>
							<?php
						}

						do_action('after_header_title'); // make accessible to add custom content after title

						if (!empty($content)) {
							?>
							<div class="intro-text">
								<?php echo wp_kses_post($content); ?>
							</div>
							<?php
						}

						do_action('after_header_intro_text'); // make accessible to add custom content after intro text
						?>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

	<?php
endif;
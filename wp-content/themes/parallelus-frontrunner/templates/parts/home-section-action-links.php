<?php
/**
 * Template part: Home Page Section - Header Bottom Action Links
 */

// Number of links in theme options
$link_items = 4;
$link_items_fields = array(
	'title',
	'icon',
	'url',
	'color',
	'bg',
);

$action_links = array();
for ($i = 1; $i <= $link_items; $i++) {
	// Loop through data for all action links
	if ( get_options_data('home-page', 'home-action-link-'.$i, 'show') == 'show' ) {
		foreach ($link_items_fields as $field) {
			$action_links[$i][$field] = get_options_data('home-page', 'home-action-link-'.$i.'-'.$field);
		}
	}
}

// Container column class
$column_class = '';
$column_offset = '';
$columns = count($action_links);
switch ($columns) {
	case 1:
		$column_class = 'col-lg-6 col-md-8 col-sm-10 col-xs-12';
		$column_offset = 'col-lg-offset-3 col-md-offset-2 col-sm-offset-1';
		break;
	case 2:
		$column_class = 'col-md-5 col-xs-6';
		$column_offset = 'col-md-offset-1';
		break;
	case 3:
		$column_class = 'col-sm-4 col-xs-12';
		break;
	default:
		$column_class = 'col-md-3 col-xs-6'; // 4 columns
		break;
}

?>

<!-- Header Bottom Links
================================================== -->

<div class="header-links">
	<div class="container">
		<div class="header-links-wrapper">

		<?php
		$loop = 1;
		foreach ($action_links as $link) {
			// wrapper class
			$class  = $column_class;
			$class .= ($loop == 1) ? ' '.$column_offset : '';
			// item class
			$item_class  = 'header-links-item';
			$item_class .= (empty($link['bg'])) ? ' solid' : '';

			$solid_bg = '';
			if (empty($link['bg']) && !empty($link['color']) && $link['color'] !== '#') {
				$solidColor = new Color($link['color']);
				$asHSL = $solidColor->hexToHsl($link['color']);
				$asHSL['S'] = 1; // fully saturate
				$bgColor = $solidColor->hslToHex($asHSL);
				$solid_bg = 'background-color: #'. esc_attr($bgColor) .';';
			}
			?>
			<div class="<?php echo esc_attr($class) ?> no-padding">
				<div class="<?php echo esc_attr($item_class) ?>" style="background-image: url('<?php echo esc_attr($link['bg']) ?>'); <?php echo ' '. $solid_bg; // escaped above ?>">
					<a href="<?php echo esc_url($link['url']) ?>">
						<article>
							<h3 class="entry-title">
								<?php
								// Icon
								if ( !empty($link['icon']) ) {
									if (substr($link['icon'], 0, 2) === "fa") {
										// font awesome icon
										echo '<i class="fa '. esc_attr($link['icon']) .'"></i>';
									} else {
										// image icon
										echo '<img src="'.esc_url($link['icon']).'" class="icon" height="40" alt="'.esc_attr($link['title']).'">';
									}
								}
								// Title
								if ( !empty($link['title']) ) {
									echo '<span>'. esc_attr($link['title']) .'</span>';
								}
								?>
							</h3>
						</article>
						<div class="overlay" style="background-color: <?php echo esc_attr($link['color']) ?>;"></div>
					</a>
				</div>
			</div>
			<?php

			$loop++;
		}
		?>
		</div> <!-- end header-links-wrapper -->
	</div> <!-- end container -->
</div> <!-- end header-links -->

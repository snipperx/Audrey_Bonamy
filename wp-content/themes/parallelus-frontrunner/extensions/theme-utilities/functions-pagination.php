<?php

#-----------------------------------------------------------------
# Pagination function (<< 1 2 3 >>)
#-----------------------------------------------------------------

// Template for pagination:
/*
<div class="row paging">
	<div class="col-xs-12">
		<ul class="pagination">
			<li><a href="#">«</a></li>
			<li><a href="#">1</a></li>
			<li><a href="#">2</a></li>
			<li><a href="#">3</a></li>
			<li><a href="#">4</a></li>
			<li><a href="#">5</a></li>
			<li><a href="#">»</a></li>
		</ul>
	</div>
</div>
*/

function rf_get_pagination($query = false, $range = 4) {

	// $paged - number of the current page
	global $paged, $wp_query, $portfolio_query, $postIndex;


	// set the query variable (default $wp_query)
	$q = ($query) ? $query : $wp_query;

	// How many pages do we have?
	if ( !isset($max_page) ) {
		$max_page = $q->max_num_pages;
	}

	// We need the pagination only if there are more than 1 page
	if($max_page > 1) {

		// doesn't quite work for next/prev links without $wp_query setting so...
		$temp_q = $wp_query;	// save a temporary copy
		$wp_query = $q;			// overwrite with our query

		echo '<div class="paging clearfix"><ul class="pagination">';

		if (!$paged){ $paged = 1;}

			// To the previous page
			$prev = get_previous_posts_link('<i class="fa fa-angle-left"></i>');
			if (!empty($prev)) {
				echo '<li class="prev-post">'. $prev .'</li>';
			}

			// We need the sliding effect only if there are more pages than is the sliding range
			if ($max_page > $range) {

			  // When closer to the beginning
				if ($paged < $range) {
					for($i = 1; $i <= ($range + 1); $i++) {
						echo "<li";
						if($i==$paged) echo " class='active'";
						echo "><a href='" . get_pagenum_link($i) ."'>$i</a></li>";
					}
				} elseif($paged >= ($max_page - ceil(($range/2)))){
					// When closer to the end
					for($i = $max_page - $range; $i <= $max_page; $i++){
						echo "<li";
						if($i==$paged) echo " class='active'";
						echo "><a href='" . get_pagenum_link($i) ."'>$i</a></li>";
					}
				} elseif($paged >= $range && $paged < ($max_page - ceil(($range/2)))){
					// Somewhere in the middle
					for($i = ($paged - ceil($range/2)); $i <= ($paged + ceil(($range/2))); $i++){
						echo "<li";
						if($i==$paged) echo " class='active'";
						echo "><a href='" . get_pagenum_link($i) ."'>$i</a></li>";
					}
				}
			} else{
				// Less pages than the range, no sliding effect needed
				for($i = 1; $i <= $max_page; $i++){
					echo "<li";
					if($i==$paged) echo " class='active'";
					echo "><a href='" . get_pagenum_link($i) ."'>$i</a></li>";
				}
			}

			// Next page
			$next = get_next_posts_link('<i class="fa fa-angle-right"></i>');
			if (!empty($next)) {
				echo '<li class="next-post">'. $next .'</li>';
			}

			$wp_query = $temp_q;

		echo '</ul></div>';
		//echo '<div style="clear:both"></div>';
	}
}

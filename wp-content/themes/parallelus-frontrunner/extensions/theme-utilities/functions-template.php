<?php
/**
 * Template Functions
 * ................................................................
 *
 * Functions to perform template actions and output, such as
 * comments, post navigation, etc. These will typically have some
 * form of content output to a template file.
 */


/**
 * Display navigation to next/previous pages when applicable
 */
if ( ! function_exists( 'rf_next_prev_post_nav' ) ) :
function rf_next_prev_post_nav( $nav_id ) {
	global $wp_query, $post;

	// Don't print empty markup on single pages if there's nowhere to navigate.
	if ( is_single() ) {
		$previous = ( is_attachment() ) ? get_post( $post->post_parent ) : get_adjacent_post( false, '', true );
		$next = get_adjacent_post( false, '', false );

		if ( ! $next && ! $previous )
			return;
	}

	// Don't print empty markup in archives if there's only one page.
	if ( $wp_query->max_num_pages < 2 && ( is_home() || is_archive() || is_search() ) )
		return;

	$nav_class = ( is_single() ) ? 'post-navigation' : 'paging-navigation';

	?>
	<nav role="navigation" id="<?php echo esc_attr( $nav_id ); ?>" class="<?php echo  $nav_class; ?>">
		<h2 class="screen-reader-text"><?php _e( 'Post navigation', 'framework' ); ?></h2>
		<ul class="pager">

		<?php

		// navigation links for single posts
		if ( is_single() ) :

			$prev_img = '';
			$next_img = '';
			$prev_img_class = '';
			$next_img_class = '';

			// Look up images for next/previous
			$previous = ( is_attachment() ) ? get_post( get_post()->post_parent ) : get_adjacent_post( false, '', true );
			$next     = get_adjacent_post( false, '', false );

			if ( is_attachment() && 'attachment' == $previous->post_type ) {
				return;
			}

			if ( $previous &&  has_post_thumbnail( $previous->ID ) ) {
				$prevthumb = wp_get_attachment_image_src( get_post_thumbnail_id( $previous->ID ), 'blog' );
				$prev_img = esc_url( $prevthumb[0] );
				$prev_img_class = 'w-image';
			}

			if ( $next && has_post_thumbnail( $next->ID ) ) {
				$nextthumb = wp_get_attachment_image_src( get_post_thumbnail_id( $next->ID ), 'blog' );
				$next_img = esc_url( $nextthumb[0] );
				$next_img_class = 'w-image';
			}

			// Show the navigation
			previous_post_link( '<li class="nav-previous previous '.$prev_img_class.'">%link</li>', '<span class="meta-nav"><i class="fa fa-angle-left"></i></span><span class="meta-nav-title">%title</span><span class="meta-nav-img">'.$prev_img.'</span>' );
			next_post_link( '<li class="nav-next next '.$next_img_class.'">%link</li>', '<span class="meta-nav-title">%title</span><span class="meta-nav-img">'.$next_img.'</span><span class="meta-nav"><i class="fa fa-angle-right"></i></span>' );
			?>

		<?php elseif ( $wp_query->max_num_pages > 1 && ( is_home() || is_archive() || is_search() ) ) : // navigation links for home, archive, and search pages ?>

			<?php if ( get_next_posts_link() ) : ?>
			<li class="nav-previous previous"><?php next_posts_link( __( '<span class="meta-nav">&larr;</span> Older posts', 'framework' ) ); ?></li>
			<?php endif; ?>

			<?php if ( get_previous_posts_link() ) : ?>
			<li class="nav-next next"><?php previous_posts_link( __( 'Newer posts <span class="meta-nav">&rarr;</span>', 'framework' ) ); ?></li>
			<?php endif; ?>

		<?php endif; ?>

		</ul>
	</nav><!-- #<?php echo esc_html( $nav_id ); ?> -->
	<?php
}
endif; // rf_next_prev_post_nav


/**
 * Template for comments and pingbacks.
 *
 * Used as a callback by wp_list_comments() for displaying the comments.
 */
if ( ! function_exists( 'rf_list_comment' ) ) :
function rf_list_comment( $comment, $args, $depth ) {
	$GLOBALS['comment'] = $comment;

	if ( 'pingback' == $comment->comment_type || 'trackback' == $comment->comment_type ) : ?>

	<li id="comment-<?php comment_ID(); ?>" <?php comment_class( 'media' ); ?>>
		<div class="comment-body">
			<?php _e( 'Pingback:', 'framework' ); ?> <?php comment_author_link(); ?> <?php edit_comment_link( __( 'Edit', 'framework' ), '<span class="edit-link">', '</span>' ); ?>
		</div>

	<?php else : ?>

	<li id="comment-<?php comment_ID(); ?>" <?php comment_class( empty( $args['has_children'] ) ? '' : 'parent' ); ?>>
		<article id="div-comment-<?php comment_ID(); ?>" class="comment-body media">

			<div class="media-body">
				<div class="media-body-wrap panel panel-default">

					<div class="panel-heading clearfix">
						<a class="pull-left" href="#">
							<?php if ( 0 != $args['avatar_size'] ) echo get_avatar( $comment, $args['avatar_size'] ); ?>
						</a>
						<h5 class="media-heading"><?php printf( __( '%s <span class="says">says:</span>', 'framework' ), sprintf( '<cite class="fn">%s</cite>', get_comment_author_link() ) ); ?></h5>
						<div class="comment-meta">
							<a href="<?php echo esc_url( get_comment_link( $comment->comment_ID ) ); ?>">
								<time datetime="<?php comment_time( 'c' ); ?>">
									<?php printf( _x( '%1$s', '1: date, 2: time', 'framework' ), get_comment_date(), get_comment_time() ); ?>
								</time>
							</a>
							<?php edit_comment_link( __( '<span class="glyphicon glyphicon-edit"></span> Edit', 'framework' ), '<span class="edit-link">', '</span>' ); ?>
						</div>
					</div>

					<?php if ( '0' == $comment->comment_approved ) : ?>
						<p class="comment-awaiting-moderation"><?php _e( 'Your comment is awaiting moderation.', 'framework' ); ?></p>
					<?php endif; ?>

					<div class="comment-content panel-body">
						<?php comment_text(); ?>
					</div><!-- .comment-content -->

					<?php comment_reply_link(
						array_merge(
							$args, array(
								'add_below' => 'div-comment',
								'depth' 	=> $depth,
								'max_depth' => $args['max_depth'],
								'before' 	=> '<footer class="reply comment-reply panel-footer">',
								'after' 	=> '</footer><!-- .reply -->'
							)
						)
					); ?>

				</div>
			</div><!-- .media-body -->

		</article><!-- .comment-body -->

	<?php
	endif;
}
endif; // ends check for rf_list_comment()


/**
 * Pages/Posts - Header title show/hide
 */
if ( ! function_exists( 'rf_show_page_title' ) ) :
function rf_show_page_title( $return = true ) {

	$show = true;

	if ( is_page() || is_single() ) {

		// Title in meta options
		$meta_options = get_post_custom( get_queried_object_id() );
		if ( isset($meta_options['theme_custom_layout_metabox_options_title']) ) {
			$title_setting = $meta_options['theme_custom_layout_metabox_options_title'][0];

			if ($return === 'meta-value') // return the setting
				return $title_setting;

			if ( $title_setting === 'hide' || $title_setting === 'in-header' ) {
				$show = false;
			}
		}
	}

	return $show;
}
endif;

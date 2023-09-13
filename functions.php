<?php
/**
 * Understrap Child Theme functions and definitions
 *
 * @package UnderstrapChild
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;


/**
 * Removes the parent themes stylesheet and scripts from inc/enqueue.php
 */
function understrap_remove_scripts() {
	wp_dequeue_style( 'understrap-styles' );
	wp_deregister_style( 'understrap-styles' );

	wp_dequeue_script( 'understrap-scripts' );
	wp_deregister_script( 'understrap-scripts' );
}
add_action( 'wp_enqueue_scripts', 'understrap_remove_scripts', 20 );


/**
 * Enqueue our stylesheet and javascript file
 */
function theme_enqueue_styles() {

	// Get the theme data.
	$the_theme = wp_get_theme();

	$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
	// Grab asset urls.
	$theme_styles  = "/css/child-theme{$suffix}.css";
	$theme_scripts = "/js/child-theme{$suffix}.js";

	wp_enqueue_style( 'child-understrap-styles', get_stylesheet_directory_uri() . $theme_styles, array(), $the_theme->get( 'Version' ) );
	wp_enqueue_script( 'jquery' );
	wp_enqueue_script( 'child-understrap-scripts', get_stylesheet_directory_uri() . $theme_scripts, array(), $the_theme->get( 'Version' ), true );
	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
}
add_action( 'wp_enqueue_scripts', 'theme_enqueue_styles' );


/**
 * Load the child theme's text domain
 */
function add_child_theme_textdomain() {
	load_child_theme_textdomain( 'understrap-child', get_stylesheet_directory() . '/languages' );
}
add_action( 'after_setup_theme', 'add_child_theme_textdomain' );


/**
 * Overrides the theme_mod to default to Bootstrap 5
 *
 * This function uses the `theme_mod_{$name}` hook and
 * can be duplicated to override other theme settings.
 *
 * @return string
 */
function understrap_default_bootstrap_version() {
	return 'bootstrap5';
}
add_filter( 'theme_mod_understrap_bootstrap_version', 'understrap_default_bootstrap_version', 20 );


/**
 * Loads javascript for showing customizer warning dialog.
 */
function understrap_child_customize_controls_js() {
	wp_enqueue_script(
		'understrap_child_customizer',
		get_stylesheet_directory_uri() . '/js/customizer-controls.js',
		array( 'customize-preview' ),
		'20130508',
		true
	);
}
add_action( 'customize_controls_enqueue_scripts', 'understrap_child_customize_controls_js' );


/**
 * Display site info (e.g. for the footer).
 */
function understrap_site_info() {
	echo 'Small Screen Superman is a Superman fansite by Talia Joy DeGisi Hatfield. Superman &copy; DC Comics. Site text &copy; Talia Hatfield.<br /><a href="/">Site Home</a> - <a href="/blog">Blog Home</a>';
}


/**
 * Post nav - removing font awesome icons
 */
function understrap_post_nav() {
	global $post;
	if ( ! $post ) {
		return;
	}

	// Don't print empty markup if there's nowhere to navigate.
	$previous = ( is_attachment() ) ? get_post( $post->post_parent ) : get_adjacent_post( false, '', true );
	$next     = get_adjacent_post( false, '', false );
	if ( ! $next && ! $previous ) {
		return;
	}
	?>
	<nav class="container navigation post-navigation">
		<h2 class="screen-reader-text"><?php esc_html_e( 'Post navigation', 'understrap' ); ?></h2>
		<div class="d-flex nav-links justify-content-between">
			<?php
			if ( get_previous_post_link() ) {
				previous_post_link( '<span class="nav-previous">%link</span>', _x( '%title', 'Previous post link', 'understrap' ) );
			}
			if ( get_next_post_link() ) {
				next_post_link( '<span class="nav-next">%link</span>', _x( '%title', 'Next post link', 'understrap' ) );
			}
			?>
		</div><!-- .nav-links -->
	</nav><!-- .post-navigation -->
	<?php
}


/**
 * Change presentation of date a post is edited
 */
function understrap_posted_on() {
	$post = get_post();
	if ( ! $post ) {
		return;
	}

	$time_string = '<time class="entry-date published updated" datetime="%1$s">%2$s</time>';

	if ( get_the_time( 'U' ) !== get_the_modified_time( 'U' ) ) {
		$time_string = '<time class="entry-date published" datetime="%1$s">%2$s</time><time class="updated" datetime="%3$s"> <i>(edited %4$s)</i></time>';
	}

	$time_string = sprintf(
		$time_string,
		esc_attr( get_the_date( 'c' ) ), // @phpstan-ignore-line -- post exists
		esc_html( get_the_date() ), // @phpstan-ignore-line -- post exists
		esc_attr( get_the_modified_date( 'c' ) ), // @phpstan-ignore-line -- post exists
		esc_html( get_the_modified_date() ) // @phpstan-ignore-line -- post exists
	);

	$posted_on = apply_filters(
		'understrap_posted_on',
		sprintf(
			'<span class="posted-on">%1$s <a href="%2$s" rel="bookmark">%3$s</a></span>',
			esc_html_x( 'Posted on', 'post date', 'understrap' ),
			esc_url( get_permalink() ), // @phpstan-ignore-line -- post exists
			apply_filters( 'understrap_posted_on_time', $time_string )
		)
	);

	$author_id = (int) get_the_author_meta( 'ID' );
	if ( 0 === $author_id ) {
		$byline = '';
	} else {
		$byline = apply_filters(
			'understrap_posted_by',
			sprintf(
				'<span class="byline"> %1$s<span class="author vcard"> <a class="url fn n" href="%2$s">%3$s</a></span></span>',
				$posted_on ? esc_html_x( 'by', 'post author', 'understrap' ) : esc_html_x( 'Posted by', 'post author', 'understrap' ),
				esc_url( get_author_posts_url( $author_id ) ),
				esc_html( get_the_author_meta( 'display_name', $author_id ) )
			)
		);
	}

	echo $posted_on . $byline; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}


/**
 * Remove some attributes from featured images so they will display at full size.
 */
function understrapsss_simplify_fimage($html) {
	if ($html) {
		$dom = new DOMDocument;
		$dom->loadHTML($html);
		$img = $dom->getElementsByTagName('img')[0];
		if ($img->hasAttribute('srcset')) {
			$img->removeAttribute('srcset');
		}
		if ($img->hasAttribute('sizes')) {
			$img->removeAttribute('sizes');
		}
		if ($img->hasAttribute('width')) {
			$img->removeAttribute('width');
		}
		if ($img->hasAttribute('height')) {
			$img->removeAttribute('height');
		}
		$html = $dom->saveHTML($img);
		return $html;
	}
}
add_filter( 'post_thumbnail_html', 'understrapsss_simplify_fimage' );


/**
 * Make search button widget use secondary color, not primary.
 */
function understrap_add_block_widget_search_classes( $block_content, $block ) {
	$search  = array(
		'wp-block-search__input ',
		'wp-block-search__input"',
		'wp-block-search__button ',
	);
	$replace = array(
		'wp-block-search__input form-control ',
		'wp-block-search__input form-control"',
		'wp-block-search__button btn btn-secondary ',
	);

	if ( isset( $block['attrs']['buttonPosition'] ) && 'button-inside' === $block['attrs']['buttonPosition'] ) {
		$search[]  = 'wp-block-search__inside-wrapper';
		$replace[] = 'wp-block-search__inside-wrapper input-group';

		if ( 'bootstrap4' === get_theme_mod( 'understrap_bootstrap_version', 'bootstrap4' ) ) {
			$search[]  = '<button';
			$search[]  = '</button>';
			$replace[] = '<div class="input-group-append"><button';
			$replace[] = '</button></div>';
		}
	}

	return str_replace( $search, $replace, $block_content );
}


/**
 * Remove "..." in Read More link
 */
function understrap_all_excerpts_get_more_link( $post_excerpt ) {
	if ( is_admin() || ! get_the_ID() ) {
		return $post_excerpt;
	}

	$permalink = esc_url( get_permalink( (int) get_the_ID() ) ); // @phpstan-ignore-line -- post exists

	return $post_excerpt . ' [...]<p><a class="btn btn-secondary understrap-read-more-link" href="' . $permalink . '">' . __(
		'Read More',
		'understrap'
	) . '<span class="screen-reader-text"> from ' . get_the_title( get_the_ID() ) . '</span></a></p>';

}


/**
 * Literally just fixing a typo in the original function - remove when it gets fixed
 */
function understrap_bootstrap_comment_form_fields( $fields ) {

	$replace = array(
		'<p class="' => '<div class="form-group mb-3 ',
		'<input'     => '<input class="form-control" ',
		'</p>'       => '</div>',
	);

	if ( isset( $fields['author'] ) ) {
		$fields['author'] = strtr( $fields['author'], $replace );
	}
	if ( isset( $fields['email'] ) ) {
		$fields['email'] = strtr( $fields['email'], $replace );
	}
	if ( isset( $fields['url'] ) ) {
		$fields['url'] = strtr( $fields['url'], $replace );
	}

	$replace = array(
		'<p class="' => '<div class="form-group mb-3 form-check ',
		'<input'     => '<input class="form-check-input" ',
		'<label'     => '<label class="form-check-label" ',
		'</p>'       => '</div>',
	);
	if ( isset( $fields['cookies'] ) ) {
		$fields['cookies'] = strtr( $fields['cookies'], $replace );
	}

	return $fields;
}

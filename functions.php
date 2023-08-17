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
 * Literaly just fixing a typo in the original function - remove when it gets fixed
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

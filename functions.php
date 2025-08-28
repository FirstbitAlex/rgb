<?php

define("THEME_WPML_SLUG", "lead-form");
define("THEME_SLUG", "lead-form");

if (!defined('_S_VERSION')) {
	// Replace the version number of the theme on each release.
	define('_S_VERSION', '1.0.0');
}

if (!function_exists('theme_setup__setup')) {
	/**
	 * Sets up theme defaults and registers support for various WordPress features.
	 *
	 * Note that this function is hooked into the after_setup_theme hook, which
	 * runs before the init hook. The init hook is too late for some features, such
	 * as indicating support for post thumbnails.
	 */
	function theme_setup__setup()
	{
		/*
		 * Switch default core markup for search form, comment form, and comments
		 * to output valid HTML5.
		 */
		add_theme_support(
			'html5',
			array(
				'search-form',
				'comment-form',
				'comment-list',
				'gallery',
				'caption',
				'style',
				'script',
			)
		);

		/** ==============================================
		 *  Add image sizes ==============================
		 * ===============================================
		 */
		add_image_size('photo-big', 114, 114, true);
		add_image_size('photo-small', 86, 86, true);
	}
}
;
add_action('after_setup_theme', 'theme_setup__setup');

/**
 * Enqueue scripts and styles.
 */
function theme_setup__scripts()
{
	wp_enqueue_style('css-style', get_template_directory_uri() . '/style.min.css', [], filemtime(get_template_directory() . '/style.min.css'));
	wp_enqueue_style('css-phone-iti', 'https://cdn.jsdelivr.net/npm/intl-tel-input@19.5.6/build/css/intlTelInput.css');

	wp_enqueue_script('jquery');
	wp_enqueue_script('js-phone-iti', 'https://cdn.jsdelivr.net/npm/intl-tel-input@19.5.6/build/js/intlTelInput.min.js', [], null, true);
	wp_enqueue_script('js-scripts', get_template_directory_uri() . '/js/site.min.js', ['jquery', 'js-phone-iti'], filemtime(get_template_directory() . '/js/site.min.js'), true);
}
add_action('wp_enqueue_scripts', 'theme_setup__scripts');

/**
 * All functions for customizing and designing the admin area
 */
require get_template_directory() . '/inc/admin-customize.php';

/**
 * Custom functions which enhance the theme by hooking into WordPress
 */
require get_template_directory() . '/inc/template-functions.php';

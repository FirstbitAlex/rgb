<?php
/**
 * All functions for customizing and designing the admin area
 *
 */
if (!defined('ICL_LANGUAGE_CODE')) {
	// Replace the ICL_LANGUAGE_CODE if not defined.
	define('ICL_LANGUAGE_CODE', 'uk');
}

add_action('wp_head', 'form_ajaxurl');
function form_ajaxurl()
{
	echo '<script type="text/javascript">
		   var ajaxurl = "' . admin_url('admin-ajax.php') . '";
		   var lang = "' . ICL_LANGUAGE_CODE . '";
		 </script>';
}

function login_logo()
{ ?>
		<style type="text/css">
			#login h1 a, .login h1 a {
				background-image: url(<?php echo get_stylesheet_directory_uri(); ?>/img/logo.svg);
				background-size: contain;
				background-position: center;
				width: 81px;
				height: 80px;
			}
		</style>
<?php }
add_action('login_enqueue_scripts', 'login_logo');

function login_logo_url()
{
	return home_url();
}
add_filter('login_headerurl', 'login_logo_url');

/**
 * Remove comments and default posts
 */
function my_remove_admin_menus()
{
	remove_menu_page('edit-comments.php');
	remove_menu_page('edit.php');
}
add_action('admin_menu', 'my_remove_admin_menus');

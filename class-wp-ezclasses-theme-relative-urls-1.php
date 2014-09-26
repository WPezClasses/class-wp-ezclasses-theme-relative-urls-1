<?php
/** 
 * Makes your WordPress URLs relative (so WP is harder to find). (@link https://github.com/WPezClasses/class-wp-ezclasses-theme-relative-urls-1)
 *
 * From the Roots Theme framework, as GitHub Gist'ed here: (@link https://gist.github.com/wycks/2315279)
 *
 * PHP version 5.3
 *
 * LICENSE: TODO
 *
 * @package WPezClasses
 * @author Mark Simchock <mark.simchock@alchemyunited.com>
 * @since 0.5.0
 * @license TODO
 */
 
/*
* == Change Log == 
*
* --- 26 September 2014 - Ready
*
*/


// No WP? Die! Now!!
if (!defined('ABSPATH')) {
	header( 'HTTP/1.0 403 Forbidden' );
    die();
}

if (! class_exists('Class_WP_ezClasses_Theme_Relative_URLs_1') ) {
  class Class_WP_ezClasses_Theme_Relative_URLs_1 extends Class_WP_ezClasses_Master_Singleton {
  
    protected $_arr_init;
	
	protected function __construct() {
	  parent::__construct();
	}
	
	/**
	 *
	 */
	public function ezc_init($arr_args = ''){
	
	//  $arr_init_defaults = $this->init_defaults();
	  $this->_arr_init = WP_ezMethods::ez_array_merge(array($this->init_defaults(), $arr_args));
	  
	  add_action('pre_get_posts', array($this, 'relative_url_attachment') );
	  
	  $this->relative_url_filters();
	  
	}
		
	
    public function init_defaults(){
	
	  $arr_defaults = array(
	  
	  	// 'relative_url_attachment' - start
	    'option_relative_urls'		=> true,
		// 'relative_url_attachment' - end

		// 'relative_url' - start
		'bloginfo_url'				=> true,
		'theme_root_uri'			=> true,
		'stylesheet_directory_uri'	=> true,
		'template_directory_uri'	=> true,
		'plugins_url'				=> true,
		'the_permalink'				=> true,
		'wp_list_pages'				=> true,
		'wp_list_categories'		=> true,
		'wp_nav_menu'				=> true,
		'the_content_more_link'		=> true,
		'the_tags'					=> true,
		'get_pagenum_link'			=> true,
		'get_comment_link'			=> true,
		'month_link'				=> true,
		'day_link'					=> true,
		'year_link'					=> true,
		'tag_link'					=> true,
		'the_author_posts_link'		=> true,
		// 'relative_url' - end

		// fix_duplicate_subfolder_urls - start		
		'script_loader_src'			=> true,
		'style_loader_src'			=> true,
		// fix_duplicate_subfolder_urls - start		

        ); 
	  return $arr_defaults;
	}
	
	protected function relative_url_filters(){
	
	  if ( ! is_admin() && ! in_array($GLOBALS['pagenow'], array('wp-login.php', 'wp-register.php'))) {
	   
	    $arr_relative_url_filters = $this->_arr_init;
		
		if ( isset($arr_relative_url_filters['script_loader_src']) && $arr_relative_url_filters['script_loader_src'] === true ){
		  add_filter('script_loader_src', array($this, 'fix_duplicate_subfolder_urls') );	
		}
		if ( isset($arr_relative_url_filters['style_loader_src']) && $arr_relative_url_filters['style_loader_src'] === true ){
		  add_filter('style_loader_src', array($this, 'fix_duplicate_subfolder_urls') ) ;		
		}
		// get rid of the three we don't need
		unset($arr_relative_url_filters['option_relative_urls']);
		unset($arr_relative_url_filters['script_loader_src']);
		unset($arr_relative_url_filters['style_loader_src']);
		
		// bang out what remains
	    foreach ( $arr_relative_url_filters as $str_key => $bool_var ){
		  if ( $bool_var === true ){
		     add_filter( $str_key, array($this, 'relative_url') );
		  }
		}
	  		
	  }
	}
	
	
	public function relative_url($str_input) {
	
	  $str_output = preg_replace_callback(
	    '!(https?://[^/|"]+)([^"]+)?!',
		create_function(
		  '$matches',
		  // if full URL is site_url, return a slash for relative root
		  'if (isset($matches[0]) && $matches[0] === site_url()) { return "/";' .
		  // if domain is equal to site_url, then make URL relative
		  '} elseif (isset($matches[0]) && strpos($matches[0], site_url()) !== false) { return $matches[2];' .
		  // if domain is not equal to site_url, do not make external link relative
		  '} else { return $matches[0]; };'
		),
	  $str_input
	  );
	  return $str_output;
	}
	
	/**
	 * workaround to remove the duplicate subfolder in the src of JS/CSS tags
	 * example: /subfolder/subfolder/css/style.css
	 */
	public function fix_duplicate_subfolder_urls($str_input) {
	
	  $str_output = $this->relative_url($str_input);
	  preg_match_all('!([^/]+)/([^/]+)!', $str_output, $arr_matches);
	  if (isset($arr_matches[1]) && isset($arr_matches[2])) {
	    if ($arr_matches[1][0] === $arr_matches[2][0]) {
		  $str_output = substr($arr_output, strlen($arr_matches[1][0]) + 1);
		}
	  }
	  return $str_output;
	}

    /**
	 * remove root relative URLs on any attachments in the feed
	 */
    function relative_url_attachment() {

      if ( ! is_feed() && isset($this->_arr_init['option_relative_urls']) && $this->_arr_init['option_relative_urls'] === true ) {
        add_filter('wp_get_attachment_url', array($this, 'relative_url') );
        add_filter('wp_get_attachment_link', array($this, 'relative_url') );
      }
    }
	
  }
}
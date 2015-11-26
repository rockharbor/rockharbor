<?php
/**
 * Includes
 */
require_once 'acf.php';
require_once 'basics.php';
require_once 'html_helper.php';
require_once 'shortcodes.php';
require_once 'post_type.php';
require_once 'twitter.php';
require_once 'widget.php';

/**
 * ROCKHARBOR Theme base class. All child themes should extend this base class
 * to make use of the overall site functionality
 *
 * @package rockharbor
 */
class RockharborThemeBase {

/**
 * List of options for this theme (all required by subsites)
 *
 * ### Options
 * - `$short_name` The short name for this campus, i.e., without RH preceding
 * - `$supports` An array of supported features for this particular site. See
 * the README for more information about the features
 * - `$hide_name_in_global_nav` Boolean whether or not to *hide* the campus name
 * in the global navigation bar (negative for backwards compatibility)
 *
 * @var array
 */
	protected $themeOptions = array(
		'short_name' => 'Central',
		'hide_name_in_global_nav' => false,
		'supports' => array()
	);

/**
 * Post types to disable commenting by default
 *
 * @var array
 */
	public $disableComments = array(
		'page'
	);

/**
 * Directory path to current theme
 *
 * @var string
 */
	protected $themePath = null;

/**
 * Uri path to current theme
 *
 * @var string
 */
	protected $themeUrl = null;

/**
 * Directory path to base theme
 *
 * @var string
 */
	protected $basePath = null;

/**
 * Uri path to base theme
 *
 * @var string
 */
	protected $baseUrl = null;

/**
 * Blog title
 *
 * @var string
 */
	protected $name = null;

/**
 * Vars set for the next render
 *
 * @var array
 */
	protected $_vars = array();

/**
 * The blog id
 *
 * @var integer
 */
	protected $id = null;

/**
 * Array of actions that are allowed to be called via POSTing the `$action` var
 *
 * @var array
 */
	public $allowedActions = array(
		'email',
		'oauth'
	);

/**
 * Array of messages
 *
 * @var array
 */
	public $messages = array();

/**
 * List of possible features for child themes
 *
 * @var array
 */
	public $features = array(
		'staff' => 'Staff',
		'message' => 'Message',
		'curriculum' => 'Curriculum'
	);

/**
 * Sets up the theme
 */
	public function __construct() {
		global $wpdb;

		$this->themePath = rtrim(get_stylesheet_directory(), DS);
		$this->themeUrl = rtrim(get_stylesheet_directory_uri(), '/');
		$this->basePath = rtrim(get_template_directory(), DS);
		$this->baseUrl = rtrim(get_template_directory_uri(), '/');
		$this->name = get_bloginfo('name');
		$this->id = $wpdb->blogid;

		if (is_admin()) {
			require_once $this->basePath . DS . 'libs' . DS . 'admin.php';
			$this->Admin = new Admin($this);
			require_once $this->basePath . DS . 'libs' . DS . 'roles.php';
			$this->Roles = new Roles($this);
		}

		// social comment plugin css
		if (!defined('SOCIAL_COMMENTS_CSS')) {
			define('SOCIAL_COMMENTS_CSS', $this->baseUrl.'/css/comments.css');
		}

		// start session
		if (!session_id()) {
			session_cache_limiter('public');
			session_start();
		}
	}

/**
 * Initializes the site
 */
	public function init() {
		$this->addHooks();
		$this->addFeatures();
		$this->loadHelpers();
		$this->after();
	}

/**
 * Adds all hooks for the theme
 */
	protected function addHooks() {
		add_action('wp_enqueue_scripts', array($this, 'setupAssets'));
		add_action('wp_enqueue_scripts', array($this, 'compressAssets'), 100);

		add_filter('the_content', array($this, 'filterContent'));
		add_filter('wp_title', array($this, 'archiveTitle'));
		remove_action('wp_head', 'wp_generator');

		// theme settings
		add_filter('wp_get_nav_menu_items', array($this, 'getNavMenu'), 1, 3);
		add_action('widgets_init', array($this, 'registerSidebars'));

		// forced gallery settings
		add_filter('use_default_gallery_style', array($this, 'removeCss'));
		add_filter('img_caption_shortcode', array($this, 'wrapAttachment'), 1, 3);

		// other
		add_filter('pre_get_posts', array($this, 'aggregateArchives'));
		add_filter('wp_get_attachment_url', array($this, 's3Url'));
		add_action('get_header', array($this, 'sendHeaders'), 10, 1);
		add_filter( 'excerpt_length', array($this, 'custom_excerpt_length'), 999 );
		add_filter( 'excerpt_more', array($this, 'new_excerpt_more'), 999 );
		add_action('wp_login', array($this, 'fail2ban'), 10, 1);
		add_action('wp_login_failed', array($this, 'fail2ban'), 10, 1);
		add_action('redirect_canonical', array($this, 'blockUserEnum'), 10, 0);

		//We've disabled XML-RPC, so don't link to it in the header
		remove_action( 'wp_head', 'rsd_link' );
	}

/**
 * Loads helpers
 */
	protected function loadHelpers() {
		$this->Html = new HtmlHelper($this);
		$this->Shortcodes = new Shortcodes($this);
		$this->Twitter = new Twitter($this);
	}


/**
 * Filter content before outputting it
 *
 * @param string $content
 * @return string
 */
	public function filterContent($content = '') {
		$count = preg_match_all('/<!--column-->/', $content, $matches);

		if ($count) {
			$columns = explode('<p><!--column--></p>', $content);
			// grab content before and after
			$contentBefore = array_shift($columns);
			$contentAfter = array_pop($columns);
			// everything else in between are columns
			$content = implode('<!--column-->', $columns);

			// set up columns
			$colCount = substr_count($content, '<!--column-->');
			$colSize = floor(100 / ($count - 1)) - 1;
			$columnDiv = "<div style=\"float:left;width:$colSize%;margin-right: 1%;\">";

			// reassemble
			$content = preg_replace('/<!--column-->/', '</div>'.$columnDiv, $content);
			$content = $contentBefore.'<div class="columns clearfix">'.$columnDiv.$content.'</div></div>'.$contentAfter;
		}
		return $content;
	}

/**
* Set the Excerpt Length
*
* @param string $length Length
*/
	public function custom_excerpt_length( $length ) {
		return 150;
	}

/**
* Set the Excerpt Length
*
* @param string $more More
*/
	public function new_excerpt_more( $more ) {
		return '...<a class="moretag" href="'. get_permalink() . '">Continue Reading &rarr;</a>';
	}

/**
 * Filter title before outputting it
 *
 * @param string $title
 * @return string
 */
	public function archiveTitle($title = '') {
		global $wp_query;
		if (is_tax()) {
			$term = $wp_query->get_queried_object();
			$title = $term->name;
		}
		return $title;
	}

/**
 * Compresses assets
 *
 * Tricks WordPress into computing the dependency order for us, then grabs that ordered list,
 * concatenates the files, and resets the WP queue
 *
 * @TODO Find hooks for both script and style so we don't have to monkey around with WP internals
 *
 * @return void
 */
	public function compressAssets() {
		$cachePath = WP_CONTENT_DIR . DS . 'cache';
		$cacheUrl = WP_CONTENT_URL . '/cache';
		if (!$this->options('compress_assets') || !is_writable($cachePath)) {
			// if compression is disabled or we can't save the files bail
			return;
		}
		global $wp_scripts, $wp_styles;
		ob_start(); // we're gonna pretend run the wp_print_scripts code and dump the output
		$wp_scripts->do_items(); // generates a list of scripts in dependency order
		$wp_styles->do_items();
		$orderedScripts = $wp_scripts->done; // grab the ordered list
		$orderedStyles = $wp_styles->done;
		foreach (array('wp_scripts', 'wp_styles') as $depObj) { // reset the objects
			${$depObj}->to_do = array();
			${$depObj}->done = array();
			${$depObj}->groups = array();
			${$depObj}->group = 0;
		}
		ob_end_clean(); // dump our unneeded <head></head> output

		// names of files
		$scriptFile = 'scripts' . $this->_key($orderedScripts, $wp_scripts).'.js';
		$stylesFile = 'styles' . $this->_key($orderedStyles, $wp_styles).'.css';

		if (WP_DEBUG) {
			// in debug  mode, create the cache file each request but be quiet about it
			@unlink($cachePath . DS . $scriptFile);
			@unlink($cachePath . DS . $stylesFile);
		}

		if (!file_exists($cachePath . DS . $scriptFile)) {
			$out = $this->_concat($orderedScripts, $wp_scripts);
			if (!file_put_contents($cachePath . DS . $scriptFile, $out)) {
				return;
			}
		}

		// now that we've got a file, clear out the queue
		$wp_scripts->queue = array();

		if (!file_exists($cachePath . DS . $stylesFile)) {
			$out = $this->_concat($orderedStyles, $wp_styles);
			if (!file_put_contents($cachePath . DS . $stylesFile, $out)) {
				return;
			}
		}

		// now that we've got a file, clear out the queue
		$wp_styles->queue = array();

		// queue it up as the only script
		wp_deregister_script('scripts');
		wp_register_script('scripts', "$cacheUrl/$scriptFile");
		wp_enqueue_script('scripts');

		// queue it up as the only style
		wp_deregister_style('styles');
		wp_register_style('styles', "$cacheUrl/$stylesFile");
		wp_enqueue_style('styles');
	}

/**
 * Concatenates files and dependencies
 *
 * @param WP_Scripts|WP_Styles $object Queue to iterate
 * @return string Concatenated file
 */
	private function _concat($queue, $object) {
		$out = '';
		foreach ($queue as $q) {
			$out .= $this->_process($object->registered[$q]);
		}
		return $out;
	}

/**
 * Processes a file
 *
 * - Changes relative CSS url paths to absolute
 *
 * @param _WP_Dependency $object Object to process
 * @return string New file contents
 */
	private function _process($object) {
		// Handles protocol-agnostic URLs (e.g. JS CDN links) like //cdn.example.com/file.js
		// remember to check for those virtual items with no source
		$filename = $object->src;
		if (empty($filename)) {
			return '';
		}
		if (preg_match("/^\/\//", $filename)) {
			$filename = is_ssl() ? 'https:' : 'http:' . $filename;
		} else {
			$filename = ltrim($filename, '/');
		}
		$filename = ltrim($object->src, '/');

		$contents =  file_get_contents($filename);

		// check for relative css urls
		//matches url('/some/url/here') where the quotes are optional and the url can't start with http or data
		if (preg_match_all("/url\((')?(?!http|data)(.*?)(?(1)\1|)\)/", $contents, $matches)) {
			$matches[2] = array_unique($matches[2]);
			$url = parse_url($filename);
			$localPath = explode('/', $url['path']);
			array_pop($localPath);
			$localPath = implode('/', $localPath);
			foreach ($matches[2] as $match) {
				$path = trim($match, "'");
				// make sure it's not absolute to webroot
				if (stripos($path, '/') !== 0) {
					$path = '/'.trim($localPath, '/').'/'.$path;
				}
				$contents = str_replace($match, "$path", $contents);
			}
		}

		return "\n/*$filename*/\n$contents";
	}

/**
 * Creates a hash from a list of scripts or styles
 *
 * @param _WP_Dependency $object Object to key
 * @return string
 */
	private function _key($queue, $object) {
		$key = '';
		foreach ($queue as $q) {
			// some queued items are virtual (i.e. they have dependencies but no source, like jquery => (jquery-core, jquery-migrate))
			$key .= isset($object->registered[$q]->src) ? $object->registered[$q]->src : '';
		}
		return md5($key);
	}

/**
 * Registers and queues assets
 */
	public function setupAssets() {
		// register assets
		$base = $this->info('base_url');

		// if debug, load un-minified versions of files
		$min = '.min';
		if (WP_DEBUG) {
			$min = '';
		}

		wp_register_script('lightbox', "{$base}/js/jquery.lightbox-1.4.7{$min}.js", array('jquery-core'));
		wp_register_script('media', "$base/js/mediaelement-and-player-2.18.2{$min}.js");
		wp_register_script('mediaCheck', "$base/js/mediaCheck-0.4.6{$min}.js");
		wp_register_script('initScripts', "$base/js/scripts{$min}.js", array(
			'jquery-core',
			'sidr',
			'slick',
			'lightbox',
			'media',
			'mediaCheck',
			'touch',
			'fastclick'
		));
		wp_register_script('fastclick', "$base/js/fastclick-1.0.6{$min}.js");
		wp_register_script('touch', "$base/js/touch{$min}.js", array('jquery-core'));
		wp_register_script('slick', "$base/js/slick-1.5.8{$min}.js", array('jquery-core'));
		wp_register_script('sidr', "$base/js/sidr-1.2.1{$min}.js", array('jquery-core'));

		wp_register_style('reset', "$base/css/reset{$min}.css");
		wp_register_style('fonts', "$base/css/fonts{$min}.css", array('reset'));
		wp_register_style('lightbox', "$base/css/lightbox-1.4.7{$min}.css", array('reset'));
		wp_deregister_style('media');
		wp_register_style('media', "$base/css/mediaelementplayer-2.18.2{$min}.css", array('reset'));
		wp_register_style('media-customizations', "$base/css/media-customizations{$min}.css", array('media'));
		wp_register_style('base', "$base/style{$min}.css", array('reset'));
		wp_register_style('mobile', "$base/css/mobile{$min}.css", array('base'));
		wp_register_style('tablet', "$base/css/tablet{$min}.css", array('base'));
		wp_register_style('calendar', "$base/css/calendar{$min}.css", array('base'));
		wp_register_style('slick', "$base/css/slick-1.5.8{$min}.css", array('base'));
		wp_register_style('sidebarStyles', "$base/css/sidebar-menu{$min}.css", array('base'));
		wp_register_style('comments', "$base/css/comments{$min}.css", array('base'));
		$base = $this->info('url');
		wp_register_style('child_base', "$base/style.css", array('base'));

		// queue them
		wp_enqueue_style('reset');
		wp_enqueue_style('fonts');
		wp_enqueue_style('lightbox');
		wp_enqueue_style('media');
		wp_enqueue_style('media-customizations');
		wp_enqueue_style('base');
		wp_enqueue_style('tablet');
		wp_enqueue_style('mobile');
		wp_enqueue_style('slick');
		wp_enqueue_style('sidebarStyles');

		wp_enqueue_script('jquery-core');
		if (WP_DEBUG) {
			wp_enqueue_script('jquery-migrate');
		}
		wp_enqueue_script('slick');
		wp_enqueue_script('sidr');
		wp_enqueue_script('lightbox');
		wp_enqueue_script('media');
		wp_enqueue_script('mediaCheck');
		wp_enqueue_script('initScripts');
		wp_enqueue_script('fastclick');
		wp_enqueue_script('touch');
		wp_enqueue_script('calendar');

		// dequeue stuff we don't need
		wp_dequeue_script('thickbox');
		wp_dequeue_style('thickbox');

		// conditional assets
		if (is_singular() && get_option('thread_comments')) {
			wp_enqueue_script('comment-reply');
			wp_enqueue_style('comments');
		}
		if ($this->isChildTheme()) {
			wp_enqueue_style('child_base');
		}
	}

/**
 * Adds features if they are supported by the child theme
 *
 * If you create custom post types outside of the `init` action, WordPress seems
 * to erase the list of taxonomies you told it to include. It will include all
 * ones specifically assigned to the post type, but skip existing ones. #YAWPH?
 *
 * @return void
 */
	protected function addFeatures() {
		foreach ($this->features as $postType => $className) {
			if ($this->supports($postType)) {
				require_once LIBS . DS . str_replace('-', '_', $postType).'.php';
				$this->{$className} = new $className($this);
			}
		}
	}

/**
 * Handles oauth flow
 */
	public function oauth() {
		// redirect back to theme options
		$url = '/wp-admin/themes.php?page=theme_options';
		if (!is_user_logged_in()) {
			$url = '/';
		}

		if (isset($_GET['clear'])) {
			$this->Twitter->clearOauthTokens();
		} elseif (isset($_GET['oauth_verifier'])) {
			$accessTokens = $this->Twitter->oauthAccessToken($_GET['oauth_verifier']);
			if (empty($accessTokens)) {
				$_SESSION['message'] = 'Error getting access tokens.';
			}
		}

		header("Location: $url");
	}

/**
 * Constructs and sends an email to a predefined option. Passing `$_POST['type']`
 * will look up an option `$_POST['type'].'_email'` to email to. If none is found
 * the function will exit.
 *
 * @return mixed `false` on failure, an array of what was sent on success. Error
 *	messages are stored in `$errors`
 */
	public function email() {
		if (!$this->Html->validateCaptcha()) {
			$this->messages[] = __('You entered in the wrong CAPTCHA phrase.', 'rockharbor');
			return false;
		}
		if (!isset($_POST['type'])) {
			$_POST['type'] = 'story';
		}
		$to = $this->options($_POST['type'].'_email');
		if (empty($to)) {
			$this->messages[] = 'To address not defined in CMS.';
			return false;
		}
		$from = $this->info('email');
		$subject = '['.$this->name.'] '.ucfirst($_POST['type']).' Email';
		$body = $this->Html->tag('h1', ucfirst($_POST['type']).' Email');
		$body .= '<table>';
		unset($_POST['type'], $_POST['action']);
		foreach ($_POST as $post => $value) {
			$body .= $this->Html->tag('tr',
				$this->Html->tag('td', $this->Html->tag('strong', $post))
				. $this->Html->tag('td', '&nbsp;&nbsp;')
				. $this->Html->tag('td', $value)
			);
		}
		$body .= '</table>';
		$headers = array(
			'From' => $from,
			'X-Mailer' => 'PHP/' . phpversion(),
			'Content-type' => 'text/html; charset=utf-8'
		);
		if (!empty($_POST['email'])) {
			$headers['Reply-To'] = $_POST['email'];
		}
		foreach ($headers as $name => &$value) {
			$value = $name.': '.$value;
		}
		$headers = implode("\r\n", $headers);

		if ($this->_mail($to, $subject, $body, $headers)) {
			$this->messages[] = 'Thanks for your message!';
			return compact('to', 'subject', 'body', 'headers');
		}
		$this->messages[] = 'Failed sending email.';
		return false;
	}

/**
 * Sends an email
 *
 * @param string $to
 * @param string $subject
 * @param string $body
 * @param string $headers
 */
	protected function _mail($to, $subject, $body, $headers) {
		return mail($to, $subject, $body, $headers);
	}

/**
 * Brings in all of the post types when showing an archive page
 *
 * @param WP_Query $query
 * @return WP_Query
 */
	public function aggregateArchives($query) {
		if (is_archive()) {
			$query->set('post_type', get_post_types());
		}
		return $query;
	}

/**
 * Aggregates posts from all sites that have the meta 'cross_post_<THISBLOGID>'
 * and includes them in The Loop with this blog's posts
 */
	public function aggregatePosts($count = null) {
		// save page count before we overwrite WP_Query
		$page = get_query_var('page');
		unset($GLOBALS['wp_query']);
        // $GLOBALS['wp_query'] =& new WP_Query(); // FOR < PHP5
		$GLOBALS['wp_query'] = new WP_Query();

		global $wpdb, $wp_query, $table_prefix;

		$blogs = $this->getBlogs();

		$query = get_transient('RockharborThemeBase::aggregatePosts');

		if ($query === false) {
			$fields = '`ID`, `post_author`, `post_date`, `post_date_gmt`, `post_content`, `post_title`, `post_excerpt`, `post_status`, `post_name`, `guid`, `post_type`, `blog_id`, `comment_status`, `ping_status`';
			$query = "SELECT SQL_CALC_FOUND_ROWS $fields FROM (";
			// primary table - this blog
			$query .= "SELECT DISTINCT $fields FROM $wpdb->posts LEFT JOIN $wpdb->postmeta ON (post_id = ID) LEFT JOIN $wpdb->blogs ON (blog_id = $this->id)";
			$query .= "WHERE post_type = 'post' AND post_status = 'publish'";
			foreach ($blogs as $blog) {
				if ($blog['blog_id'] == $this->id) {
					continue;
				}

				$whitelist = $this->networkOptions('cross_post_whitelist_'.$blog['blog_id']);
				if (!isset($whitelist[$this->id]) || !$whitelist[$this->id]) {
					continue;
				}

				// other blogs merged into the query
				$query .= " UNION DISTINCT (SELECT $fields FROM";
				$wpdb->set_blog_id($blog['blog_id']);
				$query .= " $wpdb->posts LEFT JOIN $wpdb->postmeta ON (post_id = ID AND meta_key = 'cross_post_$this->id')";
				$query .= " LEFT JOIN $wpdb->blogs ON (blog_id = {$blog['blog_id']})";
				$query .= " WHERE meta_value = 1)";
			}

			$wpdb->set_blog_id($this->id);

			set_transient('RockharborThemeBase::aggregatePosts', $query, WEEK_IN_SECONDS);
		}

		// conditions affecting all queries
		$query .= ") AS q WHERE post_type = 'post' AND post_status = 'publish'";
		$query .= " ORDER BY post_date DESC";
		$offset = ($page ? $page-1 : 0) * get_option('posts_per_page');
		if (!$count) {
			$count = get_option('posts_per_page');
		}
		$query .= " LIMIT $offset, $count";

		$wp_query->posts = $wpdb->get_results($query);
		// for pagination
		$wp_query->query_vars['paged'] = $page;
		$wp_query->post_count = count($wp_query->posts);
		$wp_query->found_posts = $wpdb->get_var('SELECT FOUND_ROWS()');
		$wp_query->max_num_pages = ceil($wp_query->found_posts / get_option('posts_per_page'));
	}

/**
 * After callback. Called after theme setup
 */
	protected function after() {
		// set up thumbnails
		add_theme_support('post-thumbnails');

		add_theme_support('automatic-feed-links');
		load_theme_textdomain('rockharbor', $this->basePath.'/languages');

		register_nav_menus(array(
			'main' => __('Main Navigation', 'rockharbor'),
			'featured' => __('Featured Stories', 'rockharbor'),
			'footer' => __('Footer Navigation', 'rockharbor'),
			'footer2' => __('Footer Column 2', 'rockharbor'),
		));
	}

/**
 * Returns all theme info. If `$var` is null, all options are returned. If an
 * option is missing, `null` is returned.
 *
 * @param string $var Option to fetch
 * @return array
 */
	public function info($var = null) {
		$vars = array(
			'path' => $this->themePath,
			'url' => $this->themeUrl,
			'base_path' => $this->basePath,
			'base_url' => $this->baseUrl,
			'name' => $this->options('campus_name'),
			'short_name' => $this->options('campus_short_name'),
			'id' => $this->id,
			'email' => get_bloginfo('admin_email')
		);
		$vars += $this->themeOptions;
		if ($var === null) {
			return $vars;
		}
		if (!isset($vars[$var])) {
			return null;
		}
		return $vars[$var];
	}

/**
 * Gets a menu name #YAWPH
 *
 * @param string $location Menu location
 * @return mixed The menu name, or `null` if it can't be found
 */
	public function getMenuName($location = null) {
		$locations = get_nav_menu_locations();
		if (!isset($locations[$location])) {
			return null;
		}
		$menu = get_term($locations[$location], 'nav_menu');
		if (!$menu || !isset($menu->name)) {
			return null;
		}
		return $menu->name;
	}

/**
 * Checks if this is a child theme
 *
 * @return boolean
 */
	public function isChildTheme() {
		return get_parent_class($this) !== false;
	}

/**
 * Sets a var to use when the view is loaded
 *
 * @param string $var The var name
 * @param mixed $value Value
 */
	public function set($var, $value = null) {
		if (is_array($var)) {
			foreach ($var as $name => $val) {
				$this->_vars[$name] = $val;
			}
		} else {
			$this->_vars[$var] = $value;
		}
	}

/**
 * Adds variables to the view found in `<template_base>/elements` and returns it.
 * It looks for a child version of the view first. If it can't find it, it looks
 * for the parent version.
 *
 * @param string $view The view name
 * @param boolean $clearVars Whether or not to remove view vars after rendering
 * @return string Rendered view
 */
	public function render($view, $clearVars = true) {
		global $theme;
		extract($this->_vars);
		$file = $this->themePath.DS.'elements'.DS.$view.'.tpl';
		if (!file_exists($file)) {
			$file = str_replace($this->themePath, $this->basePath, $file);
		}
		ob_start();
		include $file;
		$out = ob_get_clean();
		if ($clearVars === true) {
			$this->_vars = array();
		}
		return $out;
	}

/**
 * Gets a file from an enclosure
 *
 * @param string $type The type of enclosure to get (audio or video)
 * @param integer $postId The post id. Default is current post
 * @return string
 */
	public function getEnclosure($type = 'video', $postId = null) {
		global $post;
		if (empty($postId)) {
			$postId = $post->ID;
		}
		$file = get_post_meta($postId, $type . '_url');
		if (!empty($file)) {
			return $file[0];
		}
		return null;
	}

/**
 * Called when generating a menu via `wp_nav_menu`
 *
 * You can have submenus automatically generated by passing 'auto_show_children'
 * in the menu args.
 *
 * ```
 * wp_get_nav_menu_items(
 *   $locations['main'],
 *   array('auto_show_children' => true)
 * );
 * ```
 *
 * @param array $items Items from `wp_get_nav_menu_items`
 * @param array $menu Menu object
 * @param array $args Args used in getting menu items
 * @return array
 * @see `wp_get_nav_menu_items`
 */
	public function getNavMenu($items = array(), $menu = null, $args = array()) {
		if (isset($args['auto_show_children']) && $args['auto_show_children']) {
			$subMenus = array();
			foreach ($items as $index => $item) {
				if ($item->menu_item_parent) {
					unset($items[$index]);
					continue;
				}
				$children = get_children(array(
					'post_parent' => $item->object_id,
					'post_status' => 'publish',
					'post_type' => 'page',
					'orderby' => 'menu_order',
					'order' => 'ASC'
				));
				foreach ($children as &$child) {
					$child = wp_setup_nav_menu_item($child);
					$child->menu_item_parent = $item->ID;
					$child->post_type = 'nav_menu_item';
					$subMenus[] = $child;
				}
			}
			$items = array_merge($items, $subMenus);
			// restructure the menu based on the new items
			foreach ($items as $index => &$item) {
				$item->menu_order = $index;
			}
		}
		return $items;
	}

/**
 * Registers sidebar/widget/whatevertheyare areas. Also registers the widgets
 * included in this theme
 */
	public function registerSidebars() {
		register_sidebar(array(
			'name' => __('Left Widgets', 'rockharbor'),
			'id' => 'sidebar-subnav',
			'description' => __('Widgets for sub pages.', 'rockharbor'),
			'before_widget' => '<aside id="%1$s" class="widget %2$s">',
			'after_widget' => "</div></aside>",
			'before_title' => '<header><h1>',
			'after_title' => '</h1></header><div class="widget-body">',
		));

		if (class_exists('CCBPress_Upcoming_Events')) {
	        include_once 'widgets' . DS . 'myrh_upcoming_events.php';
	        register_widget('myRH_Upcoming_Events');
	    }
		include_once 'widgets' . DS . 'social_widget.php';
		register_widget('SocialWidget');
		include_once 'widgets' . DS . 'image_grid_widget.php';
		register_widget('ImageGridWidget');
		include_once 'widgets' . DS . 'instagram_widget.php';
		register_widget('InstagramWidget');

		add_action('in_widget_form', array($this, 'addWidgetOptions'), 10, 3);
		add_filter('dynamic_sidebar_params', array($this, 'addWidgetClasses'));
	}

/**
 * Adds global widget options
 *
 * @param WP_Widget $widget The widget class instance
 * @param string $formHtml The current form HTML
 * @param array $data Instance data
 */
	public function addWidgetOptions(&$widget, &$formHtml, $data) {
		$_defaults = array(
			'title' => 'Widget',
			'hide_on_mobile' => 0,
			'hide_on_tablet' => 0,
			'hide_on_desktop' => 0
		);
		$data = array_merge($_defaults, $data);

		$this->set('data', $data);
		$this->set('widget', $widget);
		echo $this->render('admin' . DS . 'widgets' . DS . 'widget_options');
	}

/**
 * Adds widget-specific classes
 *
 * @param array $params Widget parameter array (good luck)
 * @return array
 */
	public function addWidgetClasses($params) {
		global $wp_registered_widgets;

		$args = &$params[0];

		// the widget instance
		$widget = $wp_registered_widgets[$args['widget_id']];
		// get widget combined data
		$allData = get_option($widget['callback'][0]->option_name);
		// get *this* widget's data
		$data = $allData[$widget['params'][0]['number']];

		$extraClasses = array();
		if (!empty($data['hide_on_mobile'])) {
			$extraClasses[] = 'mobile-hide';
		}
		if (!empty($data['hide_on_tablet'])) {
			$extraClasses[] = 'tablet-hide';
		}
		if (!empty($data['hide_on_desktop'])) {
			$extraClasses[] = 'desktop-hide';
		}

		$args['before_widget'] = str_replace('class="widget ', 'class="widget '.implode(' ', $extraClasses).' ', $args['before_widget']);

		return $params;
	}

/**
 * Overrides the default WordPress functionality that adds a fixed width to the
 * div that wraps the image
 *
 * @param string $randomParam Always ''
 * @param array $attr Attribute array
 * @param string $content The image
 * @return string
 * @see img_caption_shortcode()
 */
	public function wrapAttachment($randomParam, $attr, $content = null) {
		$_defaults = array(
			'id'	=> uniqid('attachment_'),
			'align'	=> 'alignnone',
			'caption' => ''
		);
		$attr = array_merge($_defaults, (array)$attr);
		return $this->Html->tag('div', do_shortcode($content), array(
			'align' => esc_attr($attr['align']),
			'id' => $attr['id']
		));
	}

/**
 * Filter to make sure and exclude built-in WP CSS when styling galleries
 *
 * @return boolean False
 */
	public function removeCss() {
		return false;
	}

/**
 * Gets/sets theme options. If `$var` is false, acts as a getter. If `$var` is
 * null, it will delete the option
 *
 * @param string $option An option to get. If `null`, all options are returned
 * @param mixed $var The value to set.
 * @return mixed
 */
	public function options($option = null, $var = false, $blog = null) {
		if ($blog === null) {
			$blog = $this->id;
		}

		if ($blog !== $this->id) {
			switch_to_blog($blog);
		}

		$options = get_option('rockharbor_options');

		if ($blog !== $this->id) {
			restore_current_blog();
		}

		if ($options === false) {
			$options = array();
		}

		if (!is_null($option) && $var !== false) {
			$options[$option] = $var;
			update_option('rockharbor_options', $options);
		}

		if (!is_null($option) && is_null($var)) {
			unset($options[$option]);
			update_option('rockharbor_options', $options);
		}

		if (!is_null($option)) {
			return isset($options[$option]) ? $options[$option] : null;
		}
		return $options;
	}

/**
 * Gets/sets theme options. If `$var` is false, acts as a getter. If `$var` is
 * null, it will delete the option
 *
 * @param string $option An option to get. If `null`, all options are returned
 * @param mixed $var The value to set. If `false` the action acts as a getter
 * @return mixed
 */
	public function networkOptions($option = null, $var = false) {
		switch_to_blog(1);

		$options = get_option('rockharbor_network_options');

		if ($options === false) {
			$options = array();
		}

		if (!is_null($option) && $var !== false) {
			$options[$option] = $var;
			update_option('rockharbor_network_options', $options);
		}

		if (!is_null($option) && is_null($var)) {
			unset($options[$option]);
			update_option('rockharbor_network_options', $options);
		}

		restore_current_blog();

		if (!is_null($option)) {
			return isset($options[$option]) ? $options[$option] : null;
		}
		return $options;
	}

/**
 * Converts garbagy output from get_post_custom to a useable data array
 *
 * @param integer $postId The post to get meta from
 * @return array
 */
	public function metaToData($postId) {
		$meta = get_post_custom($postId);
		$data = array();
		foreach ($meta as $name => $value) {
			$data[$name] = maybe_unserialize($value[0]);
		}
		return $data;
	}

/**
 * Converts user data to a data array for the HtmlHelper
 *
 * @param int $userId The user id
 * @return array
 */
	public function userMetaToData($userId) {
		$meta = get_userdata($userId);
		$data = array();
		foreach ($meta as $name => $value) {
			$data[$name] = $value;
		}
		return $data;
	}

/**
 * Returns a list of blogs in this network
 *
 * @return array
 */
	public function getBlogs() {
		global $wpdb;
		return $wpdb->get_results("SELECT * FROM $wpdb->blogs WHERE archived = '0' AND deleted = '0'", ARRAY_A);
	}

/**
 * Checks or sets if this theme or childtheme supports a feature
 *
 * To enable or disable a feature on a site, use the `$set` parameter. This must
 * be done before the theme is initialized.
 *
 *     $theme->supports('staff', true); // turn on staff post type
 *     $theme->supports('staff'); // returns `true`
 *     $theme->supports('staff', false); // turn off staff post type
 *     $theme->supports('staff'); // returns `false`
 *
 * @see RockharborThemeBase::init()
 * @param string $feature
 * @return boolean
 */
	public function supports($feature = null, $set = null) {
		if (!array_key_exists($feature, $this->features)) {
			return false;
		}
		if (is_bool($set)) {
			if ($set) {
				$this->themeOptions['supports'][] = $feature;
			} else {
				$this->themeOptions['supports'] = array_diff($this->themeOptions['supports'], array($feature));
			}
		}
		if (!array_key_exists('supports', $this->themeOptions)) {
			return false;
		}
		$this->themeOptions['supports'] = array_unique($this->themeOptions['supports']);
		return in_array($feature, $this->themeOptions['supports']);
	}

/**
 * Replaces an attachment url with its S3 counterpart
 *
 * @param string $url File url
 */
	public function s3Url($url) {
		global $blog_id;

		$subsitePath = 'wp-content/uploads';
		if ($blog_id > 1) {
			$details = get_blog_details($blog_id);
			list($sub, $domain, $tld) = explode('.', $details->domain);
			// the s3 plugin that is currently used stores files under the domain
			$subsitePath = $sub.'/files';
		}

		$uploadpaths = wp_upload_dir();
		$path = $subsitePath . str_replace($uploadpaths['baseurl'], '', $url);

		$downloadDistribution = $this->options('s3_download', false, $blog_id);
		$bucket = $this->options('s3_bucket', false, $blog_id);

		$url = 'http://'.$bucket.'.s3.amazonaws.com/'.$path;
		if (!empty($downloadDistribution) && !is_admin()) {
			$url = "http://$downloadDistribution/$path";
		}

		return $url;
	}

/**
 * Sends headers
 *
 * Called during the `get_header` action because WordPress doesn't set up the
 * query and post globals before the `send_headers` action, which are needed to
 * grab modified dates to determine if the page should be cached. #YAWPH
 *
 * @return void
 */
	public function sendHeaders() {
		global $wp, $post;

		// the following situations prevent us from caching
		if (
			// don't cache when debugging
			WP_DEBUG
			// no need to cache for editors
			|| is_user_logged_in()
			// unfortunately, comment modified dates aren't kept so we can't
			// adjust a post's Last-Modified header based on them
			|| comments_open($post->ID)
			// don't cache the password form
			|| post_password_required($post->ID)
		) {
			nocache_headers();
			return;
		}

		$status = '200';
		$cacheLength = 30 * DAY_IN_SECONDS;

		// get last modified
		$lastModified = mysql2date('D, d M Y H:i:s', get_lastpostmodified('GMT'), 0).' GMT';

		// remove default headers
		header_remove();

		// is the client requesting a newer version?
		if (!empty($_SERVER['HTTP_IF_MODIFIED_SINCE']) && strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) !== false) {
			if (strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) >= strtotime($lastModified)) {
				$status = '304';
			}
		}

		// is this a bad request?
		if (!empty($wp->query_vars['error'])) {
			$status = (int)$wp->query_vars['error'];
		}

		// set headers
		$this->_status($status);
		$this->_header("Cache-Control: public; max-age: $cacheLength");
		$this->_header("Last-Modified: $lastModified");
		$this->_header('X-Pingback: '.get_bloginfo('pingback_url'));

		// set content type
		if (!empty($wp->query_vars['feed'])) {
			$this->_header('Content-type: application/rss+xml');
		} else {
			$this->_header('Content-type: '.get_option('html_type').'; charset='.get_option('blog_charset'));
		}

		if ($status === '304') {
			do_action('wp_enqueue_scripts');
			// don't send content on not-modified statuses
			exit();
		}
	}

/**
 * Sets a header
 *
 * @param string $header Header
 */
	protected function _header($header) {
		return header($header);
	}


/**
 * Sets the HTTP protocol and status header
 *
 * @param mixed $status String or integer HTTP status code
 * @return void
 */
	public function _status($status = '404') {
		return status_header((int)$status);
	}

/**
 * Fail2ban logs authentication requests (success and failure) to syslog auth log
 *
 * @param string $username The username presented to the login form
 * @return void
 */

	public function fail2ban($username) {
		// open syslog connection to auth log
		openlog('wordpress(' . $_SERVER['HTTP_HOST'] . ')', LOG_NDELAY | LOG_PID, LOG_AUTH);

		$currentAction = current_filter();
		if ($currentAction == 'wp_login') {
			// login success
			syslog(LOG_INFO, "Accepted password for $username from $_SERVER[REMOTE_ADDR]");
		} else if ($currentAction == 'wp_login_failed') {
			// login failure
			syslog(LOG_NOTICE, "Authentication failure for $username from $_SERVER[REMOTE_ADDR]");
		}

		// close syslog connection
		closelog();

		return;
	}

/**
 * Prevent malicious entities from enumerating usernames using author archive
 * redirection request.
 *
 * @return void
 * @see <a href="http://www.acunetix.com/blog/articles/wordpress-username-enumeration-using-http-fuzzer/">Vulnerability</a>
 */

	public function blockUserEnum() {
		if (intval(@$_GET['author'])) {
			// log attempt
			openlog('wordpress(' . $_SERVER['HTTP_HOST'] . ')', LOG_NDELAY | LOG_PID, LOG_AUTH);
			syslog(LOG_NOTICE, "Blocked user enumeration attempt from $_SERVER[REMOTE_ADDR]");
			closelog();

			// bail
			ob_end_clean();
			header('HTTP/1.1 403 Forbidden');
			header('Content-Type: text/plain');
			die('Forbidden');
		}
	}
}

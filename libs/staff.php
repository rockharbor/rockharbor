<?php
/**
 * Staff
 *
 * Handles everything needed to create a Staff post type.
 *
 * @package rockharbor
 * @subpackage rockharbor.libs
 */
class Staff extends PostType {

/**
 * Post type options
 *
 * @var array
 */
	public $options = array(
		'name' => 'Staff',
		'plural' => 'Staff',
		'slug' => 'staff',
		'archive' => true,
		'supports' => array(
			'editor',
			'thumbnail'
		)
	);

/**
 * Default archive query
 *
 * @var array
 */
	public $archiveQuery = array(
		'posts_per_page' => -1,
		'orderby' => 'title',
		'order' => 'ASC'
	);

/**
 * Sets the theme object for use in this class and instantiates the Staff post
 * type and related needs
 *
 * @param RockharborThemeBase $theme
 */
	public function __construct($theme = null) {
		parent::__construct($theme);

		register_taxonomy('department', $this->name, array(
			'label' => __('Department', 'rockharbor'),
			'sort' => true,
			'rewrite' => array('slug' => 'department')
		));
	}

/**
 * Automatically adds a title for this post based on the meta
 *
 * @param integer $data Post data
 * @return array Modified post data
 */
	public function beforeSave($data) {
		if (!empty($_POST)) {
			foreach ($_POST['meta'] as $key => $value) {
				$_POST['meta'][$key] = trim($value);
			}
			$fname = str_replace(' ', '-', $_POST['meta']['first_name']);
			$lname = str_replace(' ', '-', $_POST['meta']['last_name']);
			$data['post_name'] = strtolower($fname.'-'.$lname);
			$data['post_title'] = $_POST['meta']['first_name'].' '.$_POST['meta']['last_name'];
		}
		return $data;
	}

/**
 * Inits extra admin goodies
 */
	public function adminInit() {
		add_meta_box('staff_details', 'Details', array($this, 'detailsMetaBox'), $this->name, 'normal');
		remove_meta_box('tagsdiv-department', $this->name, 'side');
	}

/**
 * Renders the meta box for core events on pages
 */
	public function detailsMetaBox() {
		global $post;
		$departments = array();
		$the_term_query = new WP_Term_Query(array(
			'taxonomy' => 'department',
			'hide_empty' => false
		));
		foreach ($the_term_query->get_terms() as $term) {
			$departments[$term->slug] = $term->name;
		}
		$data = $this->theme->metaToData($post->ID);
		$selectedDepartment = wp_get_post_terms($post->ID, 'department');
		if (!empty($selectedDepartment)) {
			$data['tax_input']['department'] = $selectedDepartment[0]->slug;
		}
		$this->theme->set('departments', $departments);
		$this->theme->set('data', $data);
		echo $this->theme->render('staff'.DS.'staff_details_meta_box');
	}
}

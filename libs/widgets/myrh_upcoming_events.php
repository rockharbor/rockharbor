<?php

/**
 * myRH Upcoming Events Widget
 *
 * @package rockharbor
 * @subpackage rockharbor.libs.widgets
 * @version 0.1
 */

class myRH_Upcoming_Events extends Widget {
    public $settings = array(
        'base_id' => 'myrh_upcoming_events',
        'name' => 'myRH Upcoming Events',
        'description' => 'Displays a list of upcoming events pulled from myRH'
    );

/**
 * Renders the frontend widget
 *
 * @param  array $args Options provided at registration
 * @param  array $data Database values provided in backend
 * @return void
 */
    public function widget($args, $data) {
        // Need access to current post/page (if any)
        global $post;

        // Include CCB functions
        include_once(get_template_directory() . '/../../plugins/ccbpress/lib/ccb/ccb.php');

        // Global default settings
        $defaults = array(
            'title' => 'Upcoming Events',
            'campusid' => array('all'),
            'filterby' => 'group',
            'groupid' => array('ccbpress_all_groups'),
            'grouptype' => array('ccbpress_all_grouptypes'),
            'department' => array('ccbpress_all_departments'),
            'widgettheme' => 'graphical',
            'daterange' => '4 weeks',
            'howmany' => 5,
            'showcalendarlink' => 'hide'
        );

        // Merge defaults with settings from Widget
        $data = array_replace_recursive($defaults, $data);

        $postMetaSettings = get_post_meta($post->ID, 'myrh_upcoming_events');
        if (!isset($postMetaSettings) || empty($postMetaSettings)) {
            // Use the default/global site settings
            $data['title'] = apply_filters('widget_title', $data['title']);
            $this->theme->set('myrh_upcoming_events', $data);
            parent::widget($args, $data);
            return;
        }

        // Overwrite default settings with page specific ones
        // We're relying on the front-end to resolve the ccbpress_all_* options
        foreach ($postMetaSettings as $option => $value) {
            if (isset($value) && !empty($value)) {
                if (is_array($value)) {
                    $data[$option] = array_merge($data[$option], $value);
                } else {
                    $data[$option] = $value;
                }
            }
        }

        $data['title'] = apply_filters('widget_title', $data['title']);
        $this->theme->set('myrh_upcoming_events', $data);
        parent::widget($args, $data);
    }

/**
 * Renders the form for the widget
 *
 * @param  array $data Database values
 * @return void
 */
    public function form($data) {
        // Include CCB functions
        include_once(get_template_directory() . '/../../plugins/ccbpress/lib/ccb/ccb.php');

        $defaults = array(
            'title' => '',
            'campusid' => array('all'),
            'filterby' => 'group',
            'groupid' => array('ccbpress_all_groups'),
            'grouptype' => array('ccbpress_all_grouptypes'),
            'department' => array('ccbpress_all_departments'),
            'widgettheme' => 'graphical',
            'daterange' => '4 weeks',
            'howmany' => 5,
            'showcalendarlink' => 'hide'
        );

        $data = array_replace_recursive($defaults, $data);
        parent::form($data);
    }
}
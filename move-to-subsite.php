<?php

/*
  Plugin Name: Move to Subsite
  Description: Move posts in a category and/or a page hierarchy to a new subsite, with seamless redirects.
  Version: 0.1
  Author: Matt Wiebe
  Author URI: http://mattwiebe.wordpress.com/
 */

class MW_Move_Base {

    const POST_TYPE = 'mw_redirected';
    const NONCE = 'mw_move_nonce';

    public static function init() {
        if (!is_multisite())
            return;

        self::register_post_type();
        self::add_actions();
    }

    protected static function add_actions() {
        add_action('admin_menu', array(__CLASS__, 'add_admin_menu'));
        add_action('tools_page_move-to-subsite', array(__CLASS__, 'form_submit_listen'));
        add_filter('template_redirect', array(__CLASS__, 'maybe_redirect'), 10, 2);
    }

    public static function maybe_redirect() {
        if (is_404())
            return;

        global $wpdb;
        $requested_url = is_ssl() ? 'https://' : 'http://';
        $requested_url .= $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        $requested_url = user_trailingslashit($requested_url);

        $row = $wpdb->get_row($wpdb->prepare("SELECT guid, post_excerpt as redirect_to FROM {$wpdb->posts} WHERE guid = %s AND post_type = %s ", $requested_url, self::POST_TYPE));
        if ($row && isset($row->redirect_to)) {
            wp_redirect($row->redirect_to, 301);
            exit;
        }
    }

    public static function blogs_dropdown($name = 'existing_blog') {
        global $wpdb;
        $query = $wpdb->prepare("SELECT blog_id, domain, path FROM $wpdb->blogs WHERE site_id = %d AND public = '1' AND blog_id <> %d ORDER BY registered DESC LIMIT 0, 100", $wpdb->siteid, $wpdb->siteid);
        $blogs = $wpdb->get_results($query, ARRAY_A);
        $dropdown = '<option value="0">' . __('Create New Blog') . '</option>';

        foreach ($blogs as $blog) {
            $id = $blog['blog_id'];
            $title = get_blog_option($id, 'blogname');
            $title .= ' - ' . get_home_url($id);
            $dropdown .= "<option value='{$id}'>{$title}</option>";
        }
        $dropdown = "<select name='{$name}'>{$dropdown}</select>";
        echo $dropdown;
    }

    public static function form_submit_listen() {
        if (is_super_admin() && isset($_POST[self::NONCE]) && wp_verify_nonce($_POST[self::NONCE], self::NONCE)) {
            self::move_posts();
        }
    }

    protected static function register_post_type() {
        $args = array(
            'public' => false,
            'show_ui' => false
        );
        register_post_type(self::POST_TYPE, $args);
    }

    public static function add_admin_menu() {
        if (is_super_admin())
            add_management_page('Move to Subsite', 'Move to Subsite', 'install_plugins', 'move-to-subsite', array(__CLASS__, 'admin_page'));
    }

    public static function admin_page() {
        include 'views/admin.php';
    }

    public static function nonce_field() {
        wp_nonce_field(self::NONCE, self::NONCE, false);
    }

    protected function move_posts() {
        extract($_POST);
        require_once 'mw-move.class.php';
        MW_Move_Posts::do_move($cat, $blog_path, $blog_name, $user_id, $page_id, (int) $existing_blog);
    }

}

add_action('init', array('MW_Move_Base', 'init'));
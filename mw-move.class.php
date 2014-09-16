<?php

class MW_Move_Posts {

    const POST_TYPE = 'mw_redirected';

    protected static $new_blog_id;
    protected static $new_blog_url;
    protected static $count;
    protected static $cat;
    protected static $page_id;
    protected static $user_id;
    protected static $cat_map = array();
    protected static $posts_map = array();
    protected static $comment_map = array(0 => 0);

    public static function do_move($cat, $blog_path, $blog_name, $user_id, $page_id, $blog_id = 0) {

        if ($blog_id > 0) {
            self::$new_blog_id = $blog_id;
        } else if (!empty($blog_path) && !empty($blog_name)) {
            self::$user_id = $user_id;
            self::$new_blog_id = self::create_blog($blog_path, $blog_name);
            self::virgin_install(self::$new_blog_id);
        } else {
            return;
        }

        self::$new_blog_url = get_blogaddress_by_id(self::$new_blog_id);
        self::$cat = $cat;
        self::$page_id = $page_id;

        self::move_posts($cat);
        self::move_pages($page_id);
        add_action('admin_notices', array(__CLASS__, 'notice'));
    }

    public static function notice() {
        printf(__('You moved %s posts to the new site <b>%s</b>. Visit the <a href="%s">dashboard</a>.', 'mw-move-to-subsite'), self::$count, get_blog_option(self::$new_blog_id, 'blogname'), get_admin_url(self::$new_blog_id));
    }

    protected static function move_pages($page_id) {
        if (!( $page_id && $page = get_post($page_id) )) {
            return;
        }

        $query = new WP_Query(array(
            'nopaging' => true,
            'post_type' => 'page',
            'post_parent' => $page_id
        ));

        foreach ($query->posts as $post) {
            self::move_page($post);
        }
    }

    protected static function move_posts($cat) {
        $query = new WP_Query(array(
            'nopaging' => true,
            'post_type' => 'post',
            'cat' => (int) $cat
                ));

        foreach ($query->posts as $post) {
            self::move_post($post);
        }

        self::$count = count($query->posts);
    }

    protected function move_post($post) {


        // this is shit but simply works
        $oldpost = $post;

        global $wpdb;
        $IDs = array();
        //1. get old attachments
        $attachments = $wpdb->get_results("SELECT * FROM $wpdb->posts WHERE post_parent = $oldpost->ID AND post_type = 'attachment'");

        $metas_by_id = array();
        foreach ($attachments as $att) {
            $metas_by_id[$att->ID] = $wpdb->get_results("SELECT * FROM $wpdb->postmeta WHERE post_id = ".$att->ID);
        }

        //end shit

        $old_link = trailingslashit(get_permalink($post->ID));
        $cats = get_the_terms($post->ID, 'category');
        $tags = get_the_terms($post->ID, 'post_tag');
        $comments = get_comments(array('post_id' => $post->ID));
        $meta = get_post_custom($post->ID);

        $thumb_id = get_post_meta($post->ID, '_thumbnail_id', true);
        $thumb_post = get_post($thumb_id);
        $thumb_metas = get_post_meta($thumb_id);

        wp_trash_post($post->ID);
        switch_to_blog(self::$new_blog_id);

        // allow a natural ID on the new blog
        $post = (array) $post;
        unset($post['ID']);
        $post['post_category'] = self::map_cats($cats);
        $post['tags_input'] = self::comma_tags($tags);
        $new_id = wp_insert_post($post);

        $thumb_post->post_parent = $new_id;
        unset($thumb_post->ID);
        $thumb_new_id = wp_insert_attachment($thumb_post, false, $new_id);


        self::do_comments($new_id, $comments);
        self::migrate_meta($new_id, $meta);
        self::migrate_meta($thumb_new_id, $thumb_metas);

        // this is shit but simply works

        //2. duplicate old attachments data
        foreach($attachments as $att):
            $wpdb->query("INSERT INTO $wpdb->posts (post_title) VALUES ('')");
            $newID = $wpdb->insert_id;
            $IDs[] = array( 'old' => $att->ID, 'new' => $newID );
            $query = "UPDATE $wpdb->posts SET ";
            foreach( $att as $key=>$val ){
                if( $key == 'post_name'):
                    $query .= $key.' = "'.str_replace('"','\"',$val).'-2", ';
                elseif( $key == 'post_parent' ):
                    $query .= $key.' = "'.$new_id.'", ';
                elseif ($key != 'ID'):
                    $query .= $key.' = "'.str_replace('"','\"',$val).'", ';
                endif;
            }
            $query = substr($query,0,strlen($query)-2); # lop off the extra trailing comma
            $query .= " WHERE ID=$newID";
            if( $wpdb->query($query) ){}else{echo $query; exit;}
            $query = false;
        endforeach;
        // duplicate attachment meta data
        foreach($IDs as $id):
            $meta = $metas_by_id[$id['old']];
            foreach( $meta as $mt ){
                $query = "INSERT INTO $wpdb->postmeta (post_id, meta_key, meta_value) VALUES ('".$id['new']."', '".$mt->meta_key."', '".str_replace("'","\'",$mt->meta_value)."')";
                if( $wpdb->query($query) ){}else{echo $query;exit;}
                $query = false;
            }
        endforeach;

        // end shit

        update_post_meta( $new_id, '_thumbnail_id', $thumb_new_id );

        // Doing it ?p=ID style to allow the new blog to change permalinks
        $new_link = trailingslashit(self::$new_blog_url) . '?p=' . $new_id;

        restore_current_blog();

        self::make_redirect_post($old_link, $new_link);

    }

    protected function move_page($post) {
        $old_link = trailingslashit(get_permalink($post->ID));
        $comments = get_comments(array('post_id' => $post->ID));
        $meta = get_post_custom($post->ID);
        wp_trash_post($post->ID);
        switch_to_blog(self::$new_blog_id);

        // allow a natural ID on the new blog
        $post = (array) $post;
        unset($post['ID']);
        // Former children are now top-level
        if (self::$page_id == $post['post_parent']) {
            $post['post_parent'] = 0;
        }
        $new_id = wp_insert_post($post);

        self::do_comments($new_id, $comments);
        self::migrate_meta($new_id, $meta);

        // Doing it ?p=ID style to allow the new blog to change permalinks
        $new_link = trailingslashit(self::$new_blog_url) . '?p=' . $new_id;

        restore_current_blog();
        self::make_redirect_post($old_link, $new_link);
    }

    protected static function migrate_meta($post_id, $meta) {
        if (empty($meta))
            return;

        foreach ($meta as $meta_key => $meta_values) {
            foreach ($meta_values as $meta_value) {
                if (is_serialized($meta_value)) {
                    $meta_value = maybe_unserialize($meta_value);
                }
                add_post_meta($post_id, $meta_key, $meta_value);
            }
        }
    }

    protected static function do_comments($post_id, $comments) {
        if (empty($comments))
            return;
        rsort($comments); // this will get "parent" comments in first
        foreach ($comments as $comment) {
            $old_id = $comment->comment_ID;
            unset($comment->comment_ID);
            $comment->comment_post_ID = $post_id;

            // parentage
            if ($comment->comment_parent > 0 && isset(self::$comment_map[$comment->comment_parent])) {
                $comment->comment_parent = self::$comment_map[$comment->comment_parent];
            }

            $new_id = wp_insert_comment((array) $comment);
            // store refs for mapping
            self::$comment_map[$old_id] = $new_id;
        }
    }

    protected static function comma_tags($tags) {
        $tag_array = array();
        if (empty($tags)) {
            return $tags;
        }

        foreach ((array) $tags as $tag) {
            $tag_array[] = $tag->name;
        }
        return join(',', $tag_array);
    }

    protected static function map_cats($cats) {
        $cat_map = self::$cat_map;
        $mapped = array();
        foreach ($cats as $cat) {
            // don't bother with the cat we left behind.
            if ($cat->term_id == self::$cat)
                continue;
            if (isset($cat_map[$cat->term_id])) {
                $mapped[] = $cat_map[$cat->term_id];
            } else {
                $new_term = wp_insert_term($cat->name, 'category');
                if ( !is_a($new_term, 'WP_Error') )
                    $mapped[] = $cat_map[$cat->term_id] = $new_term['term_id'];
            }
        }
        self::$cat_map = $cat_map;
        return $mapped;
    }

    protected static function make_redirect_post($old_link, $new_link) {
        $post = array(
            'post_type' => self::POST_TYPE,
            'post_status' => 'publish',
            'guid' => $old_link,
            'post_excerpt' => $new_link
        );
        wp_insert_post($post);
    }

    protected static function create_blog($blog_path, $blog_name) {
        global $current_site;
        $domain = $current_site->domain;
        $path = '/';
        $blog_path = trim($blog_path, '/');
        if (is_subdomain_install()) {
            $domain = $blog_path . '.' . $domain;
        } else {
            $path = "/{$blog_path}/";
        }
        $user_id = (int) self::$user_id;

        if ($user_id && $user_id > 0) {
            $user = new WP_User($user_id);
            $user_id = $user->ID;
        } else {
            $user_id = get_current_user_id();
        }
        return wpmu_create_blog($domain, $path, $blog_name, $user_id);
    }

    /**
     * Cleans out the junk that wp_install_defaults() puts in.
     *
     * This is brute force, but WP doesn't allow proper hooks there.
     */
    protected static function virgin_install($blog_id) {
        $blog_id = (int) $blog_id;
        // make sure that we've got a blog and we're not on the main site
        if (!$blog_id || $blog_id == SITE_ID_CURRENT_SITE)
            return;

        global $wpdb;
        switch_to_blog($blog_id);
        $wpdb->query("TRUNCATE TABLE {$wpdb->posts};");
        $wpdb->query("TRUNCATE TABLE {$wpdb->comments};");
        delete_option('sidebars_widgets');
        restore_current_blog();
    }

}
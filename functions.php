<?php
include_once("snippets/woocommerce.php");


add_action('wp_enqueue_scripts', 'fxp_assets');
function fxp_assets() {
    wp_enqueue_style('parent-style', get_template_directory_uri() . '/style.css');
    wp_enqueue_style('blog1', get_bloginfo('stylesheet_directory') . '/css/module-blog1.css');
    wp_enqueue_style('person1', get_bloginfo('stylesheet_directory') . '/css/fxp-person-one.css');
    wp_enqueue_script('fxp-app', get_bloginfo('stylesheet_directory') . '/js/app.js', array(
        'jquery'
    ), '1.0.7.' . strtotime("now"), true);
    wp_enqueue_script('fxp-addtocart', get_bloginfo('stylesheet_directory') . '/js/addtocart.js', array(
        'jquery'
    ), '1.0.7.' . strtotime("now"), true);
    wp_localize_script( 'fxp-addtocart', 'my_ajax_object',
            array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ) );
}



add_filter( 'et_builder_post_types', 'my_et_builder_post_types' );
function my_et_builder_post_types( $post_types ) {
    $post_types[] = 'popup';     
    return $post_types;
}






//Adding custom module
function house_menu_module()
{
    if (class_exists("ET_Builder_Module")) {
        include("module-blog1.php");
        include("fxp-person-one.php");
    }
}

function Prep_DS_Custom_Modules()
{
    global $pagenow;
    
    $is_admin                     = is_admin();
    $action_hook                  = $is_admin ? 'wp_loaded' : 'wp';
    $required_admin_pages         = array(
        'edit.php',
        'post.php',
        'post-new.php',
        'admin.php',
        'customize.php',
        'edit-tags.php',
        'admin-ajax.php',
        'export.php'
    ); // list of admin pages where we need to load builder files
    $specific_filter_pages        = array(
        'edit.php',
        'admin.php',
        'edit-tags.php'
    );
    $is_edit_library_page         = 'edit.php' === $pagenow && isset($_GET['post_type']) && 'et_pb_layout' === $_GET['post_type'];
    $is_role_editor_page          = 'admin.php' === $pagenow && isset($_GET['page']) && 'et_divi_role_editor' === $_GET['page'];
    $is_import_page               = 'admin.php' === $pagenow && isset($_GET['import']) && 'wordpress' === $_GET['import'];
    $is_edit_layout_category_page = 'edit-tags.php' === $pagenow && isset($_GET['taxonomy']) && 'layout_category' === $_GET['taxonomy'];
    
    if (!$is_admin || ($is_admin && in_array($pagenow, $required_admin_pages) && (!in_array($pagenow, $specific_filter_pages) || $is_edit_library_page || $is_role_editor_page || $is_edit_layout_category_page || $is_import_page))) {
        add_action($action_hook, 'house_menu_module', 9789);
    }
}
Prep_DS_Custom_Modules();

//end



//design for the module
add_action('admin_head', 'fxp_modules_styles');

function fxp_modules_styles()
{
    echo '<style>
        li.et_pb_fxp_module_blog1, 
        li.et_pb_fxp_module_blog1:hover,
        li.et_pb_fxp_person_two, 
        li.et_pb_fxp_person_two:hover {
            background: #0065cb;
            color: #fff;
            font-weight: 500;
        }
        li.et_pb_fxp_module_blog1:hover,
        li.et_pb_fxp_person_two:hover {
            opacity: 0.9;
        }
      </style>';
}
//end




function _remove_script_version($src)
{
    $parts = explode('?ver', $src);
    return $parts[0];
}
//add_filter('script_loader_src', '_remove_script_version', 15, 1);
//add_filter('style_loader_src', '_remove_script_version', 15, 1);



function disable_wp_emojicons()
{
    
    // all actions related to emojis
    remove_action('admin_print_styles', 'print_emoji_styles');
    remove_action('wp_head', 'print_emoji_detection_script', 7);
    remove_action('admin_print_scripts', 'print_emoji_detection_script');
    remove_action('wp_print_styles', 'print_emoji_styles');
    remove_filter('wp_mail', 'wp_staticize_emoji_for_email');
    remove_filter('the_content_feed', 'wp_staticize_emoji');
    remove_filter('comment_text_rss', 'wp_staticize_emoji');
    
    // filter to remove TinyMCE emojis
    add_filter('tiny_mce_plugins', 'disable_emojicons_tinymce');
}
add_action('init', 'disable_wp_emojicons');

function disable_emojicons_tinymce($plugins)
{
    if (is_array($plugins)) {
        return array_diff($plugins, array(
            'wpemoji'
        ));
    } else {
        return array();
    }
}

add_filter('emoji_svg_url', '__return_false');







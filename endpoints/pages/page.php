<?php
require_once(plugin_dir_path(__FILE__) . 'bs_register_page_route.php');
require_once(ABSPATH . 'wp-admin/includes/media.php');
require_once(ABSPATH . 'wp-admin/includes/file.php');
require_once(ABSPATH . 'wp-admin/includes/image.php');

// POST endpoint for creating a new page or sub page
// Include parent_page_id for child_page creation
function blogstorm_get_or_create_page($request)
{
    $page_id = $request['page_id'];
    $title = sanitize_text_field($request['title']);
    $content = wp_kses_post($request['content']);
    $meta_description = sanitize_text_field($request['excerpt']);
    $parent_page_id = $request['parent_page_id']; // ID of parent page for subpages
    $post_status = $request['post_status'];
    $post_slug = $request['post_slug'];
    $publish_date = $request['publish_date'];
    $full_slug = $request['full_slug'];
    $b_page = get_page_by_path($full_slug, OBJECT, 'page');

    if ($b_page) {
        $updated_page = array(
            'ID' => $b_page->ID,
            'post_title' => $title,
            'post_name' => $post_slug,
            'post_parent' => $parent_page_id,
            'post_content' => $content,
            'post_excerpt' => $meta_description,
            'post_status' => $post_status ?: 'publish',
            'post_date' => $publish_date,
            'post_date_gmt' => $publish_date,
        );

        $page_id = wp_update_post($updated_page);

        if (is_wp_error($page_id)) {
            return new WP_Error('error', 'Failed to update the existing page');
        }

        return array(
            'message' => 'Page updated successfully',
            'page_id' => $page_id,
            'page_url' => get_permalink($page_id),
            'page_status' => get_post_status($page_id)
        );
    }

    // If no existing page with the provided slug, create a new page
    $new_page = array(
        'post_title' => $title,
        'post_name' => $post_slug,
        'post_content' => $content,
        'post_excerpt' => $meta_description,
        'post_type' => 'page',
        'post_status' => $post_status ?: 'publish',
        'post_parent' => $parent_page_id ?: 0, // Optional parent page ID
    );

    $page_id = wp_insert_post($new_page);

    if (is_wp_error($page_id)) {
        return new WP_Error('error', 'Failed to create a new page');
    }

    return array(
        'message' => 'Page created successfully',
        'page_id' => $page_id,
        'page_url' => get_permalink($page_id),
        'page_status' => get_post_status($page_id)
    );
}

?>
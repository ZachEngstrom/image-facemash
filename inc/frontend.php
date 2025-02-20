<?php

/**
 * Frontend functionality for the Image Facemash plugin.
 *
 * Defines shortcodes and rendering logic for the image comparison interface and results table.
 *
 * @package ImageFacemash
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
  exit; // Exit if accessed directly
}

/**
 * Enqueues the necessary styles and scripts for the Image Facemash plugin.
 *
 * @return void
 */
add_action('wp_enqueue_scripts', 'image_facemash_enqueue');
function image_facemash_enqueue(): void {
  // Register and enqueue the plugin's stylesheet
  wp_enqueue_style(
    'image-facemash-style',
    plugins_url('../image-facemash.css', __FILE__)
  );

  // Ensure jQuery is available
  wp_enqueue_script('jquery');

  // Register and enqueue the plugin's JavaScript with jQuery dependency
  wp_enqueue_script(
    'image_facemash-script',
    plugins_url('../image-facemash.js', __FILE__),
    array('jquery'),
    '1.0',
    true
  );

  // Pass PHP variables to JavaScript
  wp_localize_script('image_facemash-script', 'facemashAjax', array(
    'ajaxurl' => admin_url('admin-ajax.php'),
    'nonce'   => wp_create_nonce('facemash_vote_nonce')
  ));
}

/**
 * Shortcode handler to display the Image Facemash comparison interface.
 *
 * @return string HTML output of the Facemash interface
 */
add_shortcode('image_facemash', 'image_facemash_shortcode');
function image_facemash_shortcode(): string {
  $images = image_facemash_get_random_images();

  // Check if we have enough images to compare
  if (count($images) <= 1) {
    return '<p>Not enough images to compare.</p>';
  }

  $output = '<div id="facemash-container">';

  // Generate HTML for first image
  $output .= '<div class="facemash-image" data-image-id="' . esc_attr($images[0]['id']) . '" data-rating="' . esc_attr($images[0]['rating']) . '">';
  // $output .= '<div class="facemash-rating">Rating: ' . esc_html($images[0]['rating']) . '</div>'; // Commented out rating display
  $output .= '<div class="facemash-title">' . esc_html($images[0]['title']) . '</div>';
  $output .= '<img src="' . esc_url($images[0]['url']) . '" alt="' . esc_attr($images[0]['title']) . '">';
  $output .= '<div class="facemash-description">' . wp_kses_post($images[0]['description']) . '</div>';
  $output .= '</div>';

  // Generate HTML for second image
  $output .= '<div class="facemash-image" data-image-id="' . esc_attr($images[1]['id']) . '" data-rating="' . esc_attr($images[1]['rating']) . '">';
  // $output .= '<div class="facemash-rating">Rating: ' . esc_html($images[1]['rating']) . '</div>'; // Commented out rating display
  $output .= '<div class="facemash-title">' . esc_html($images[1]['title']) . '</div>';
  $output .= '<img src="' . esc_url($images[1]['url']) . '" alt="' . esc_attr($images[1]['title']) . '">';
  $output .= '<div class="facemash-description">' . wp_kses_post($images[1]['description']) . '</div>';
  $output .= '</div>';

  // Add skip button
  $output .= '<button id="facemash-skip" class="facemash-skip-button">Skip</button>';
  $output .= '</div>';

  return $output;
}

/**
 * Converts an Elo rating to a star rating representation.
 *
 * @param int $rating The Elo rating to convert
 * @return string Star rating representation (★☆☆☆☆ to ★★★★★)
 */
function elo_to_stars(int $rating): string {
  if ($rating <= 1200) {
    return '★☆☆☆☆'; // 1 star
  } elseif ($rating <= 1400) {
    return '★★☆☆☆'; // 2 stars
  } elseif ($rating <= 1600) {
    return '★★★☆☆'; // 3 stars
  } elseif ($rating <= 1800) {
    return '★★★★☆'; // 4 stars
  }
  return '★★★★★'; // 5 stars
}

/**
 * Shortcode handler to display the Image Facemash results table with pagination.
 *
 * @return string HTML output of the results table
 */
add_shortcode('image_facemash_results', 'image_facemash_results_shortcode');
function image_facemash_results_shortcode(): string {
  global $wpdb;
  $table_name = $wpdb->prefix . 'image_facemash_ratings';

  // Pagination settings
  $items_per_page = image_facemash_get_option('items_per_page', 10); // Use setting or default to 10
  $current_page = isset($_GET['fmpage']) ? max(1, intval($_GET['fmpage'])) : 1;
  $offset = ($current_page - 1) * $items_per_page;

  // Get total number of items for pagination
  $total_items = $wpdb->get_var("
        SELECT COUNT(*) 
        FROM $table_name ifr
        JOIN {$wpdb->posts} p ON p.ID = ifr.image_id
        WHERE p.post_type = 'attachment'
    ");
  $total_pages = ceil($total_items / $items_per_page);

  // Get paginated results
  $results = $wpdb->get_results($wpdb->prepare("
        SELECT ifr.image_id, ifr.rating, p.post_title, p.post_content
        FROM $table_name ifr
        JOIN {$wpdb->posts} p ON p.ID = ifr.image_id
        WHERE p.post_type = 'attachment'
        ORDER BY ifr.rating DESC
        LIMIT %d OFFSET %d
    ", $items_per_page, $offset));

  if (empty($results)) {
    return '<p>No rated images found.</p>';
  }

  // Build results table
  $output = '<div id="facemash-results-container">';
  $output .= '<div class="facemash-results-row">';
  $output .= '<div class="facemash-results-cell facemash-results-th-thumbnail">Thumbnail</div>';
  $output .= '<div class="facemash-results-cell facemash-results-th-description">Description</div>';
  $output .= '<div class="facemash-results-cell facemash-results-th-rating">Rating</div>';
  $output .= '</div>';

  foreach ($results as $result) {
    $thumbnail = wp_get_attachment_image_src($result->image_id, 'thumbnail');
    $full_image_url = wp_get_attachment_url($result->image_id);

    if (!$thumbnail || !$full_image_url) {
      continue; // Skip if no thumbnail or full image available
    }

    $star_rating = elo_to_stars($result->rating);

    $output .= '<div class="facemash-results-row">';
    $output .= '<div class="facemash-results-cell facemash-results-td-thumbnail">';
    $output .= '<a href="' . esc_url($full_image_url) . '" target="_blank">';
    $output .= '<img src="' . esc_url($thumbnail[0]) . '" alt="' . esc_attr($result->post_title) . '" title="' . esc_attr($result->post_title) . '">';
    $output .= '</a>';
    $output .= '</div>';
    $output .= '<div class="facemash-results-cell facemash-results-td-description">' . esc_attr($result->post_title) . '<br>' . wp_kses_post($result->post_content) . '</div>';
    $output .= '<div class="facemash-results-cell facemash-results-td-rating">' . $star_rating . ' <span class="small">(' . esc_html($result->rating) . ')</span></div>';
    $output .= '</div>';
  }

  $output .= '</div>';

  // Add pagination controls if needed
  if ($total_pages > 1) {
    $output .= '<div class="facemash-pagination">';
    $base_url = remove_query_arg('fmpage');

    if ($current_page > 1) {
      $prev_page = $current_page - 1;
      $output .= '<a href="' . esc_url(add_query_arg('fmpage', $prev_page, $base_url)) . '" class="facemash-page-link">« Previous</a>';
    }

    for ($i = 1; $i <= $total_pages; $i++) {
      if ($i == $current_page) {
        $output .= '<span class="facemash-page-current">' . $i . '</span>';
      } else {
        $output .= '<a href="' . esc_url(add_query_arg('fmpage', $i, $base_url)) . '" class="facemash-page-link">' . $i . '</a>';
      }
    }

    if ($current_page < $total_pages) {
      $next_page = $current_page + 1;
      $output .= '<a href="' . esc_url(add_query_arg('fmpage', $next_page, $base_url)) . '" class="facemash-page-link">Next »</a>';
    }

    $output .= '</div>';
  }

  return $output;
}

/**
 * Retrieves two random images from the media library with their ratings and metadata.
 *
 * @return array Array of image data (id, url, rating, title, description)
 */
function image_facemash_get_random_images(): array {
  global $wpdb;
  $table_name = $wpdb->prefix . 'image_facemash_ratings';

  // Fetch all image attachments
  $attachments = get_posts(array(
    'post_type'      => 'attachment',
    'post_mime_type' => 'image',
    'posts_per_page' => -1,
    'post_status'    => 'inherit'
  ));

  if (empty($attachments)) {
    return array();
  }

  // Initialize ratings for new images (default 1500)
  foreach ($attachments as $attachment) {
    $rating = $wpdb->get_var($wpdb->prepare(
      "SELECT rating FROM $table_name WHERE image_id = %d",
      $attachment->ID
    ));
    if (is_null($rating)) {
      $wpdb->insert(
        $table_name,
        array('image_id' => $attachment->ID, 'rating' => 1500),
        array('%d', '%d')
      );
    }
  }

  // Select two random images with their metadata
  $results = $wpdb->get_results("
        SELECT ifr.image_id, ifr.rating, p.post_title, p.post_content
        FROM $table_name ifr
        JOIN {$wpdb->posts} p ON p.ID = ifr.image_id
        WHERE p.post_type = 'attachment'
        ORDER BY RAND() 
        LIMIT 2
    ");

  if (count($results) < 2) {
    return array();
  }

  $images = array();
  foreach ($results as $result) {
    $url = wp_get_attachment_url($result->image_id);
    if ($url) {
      $images[] = array(
        'id'          => $result->image_id,
        'url'         => $url,
        'rating'      => $result->rating,
        'title'       => $result->post_title,
        'description' => $result->post_content
      );
    }
  }

  return $images;
}

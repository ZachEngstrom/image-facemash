<?php
/*
Plugin Name: Image Facemash
Description: Compare images from the media library using an Elo rating system.
Version: 1.0
Author: Zach Engstrom
License: GPL2
*/

// Prevent direct access
if (!defined('ABSPATH')) {
  exit;
}

// Activation hook to create database table
register_activation_hook(__FILE__, 'image_facemash_install');
function image_facemash_install() {
  global $wpdb;
  $table_name = $wpdb->prefix . 'image_facemash_ratings';
  $charset_collate = $wpdb->get_charset_collate();

  $sql = "CREATE TABLE $table_name (
        id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        image_id BIGINT(20) UNSIGNED NOT NULL,
        rating INT NOT NULL DEFAULT 1500,
        PRIMARY KEY (id),
        UNIQUE KEY image_id (image_id)
    ) $charset_collate;";

  require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
  dbDelta($sql);
}

// Enqueue scripts and styles
add_action('wp_enqueue_scripts', 'image_facemash_enqueue');
function image_facemash_enqueue() {
  wp_enqueue_style('image-facemash-style', plugins_url('image-facemash.css', __FILE__));
  wp_enqueue_script('jquery');
  wp_enqueue_script('image_facemash-script', plugins_url('image-facemash.js', __FILE__), array('jquery'), '1.0', true);
  wp_localize_script('image_facemash-script', 'facemashAjax', array(
    'ajaxurl' => admin_url('admin-ajax.php'),
    'nonce' => wp_create_nonce('facemash_vote_nonce')
  ));
}

// Shortcode to display the Facemash interface
add_shortcode('image_facemash', 'image_facemash_shortcode');
function image_facemash_shortcode() {
  $images = image_facemash_get_random_images();
  if (count($images) <= 1) {
    return '<p>Not enough images to compare.</p>';
  }

  $output = '<div id="facemash-container">';

  $output .= '<div class="facemash-image" data-image-id="' . esc_attr($images[0]['id']) . '" data-rating="' . esc_attr($images[0]['rating']) . '">';
  // $output .= '<div class="facemash-rating">Rating: ' . esc_html($images[0]['rating']) . '</div>';
  $output .= '<div class="facemash-title">' . esc_html($images[0]['title']) . '</div>';
  $output .= '<img src="' . esc_url($images[0]['url']) . '" alt="' . esc_attr($images[0]['title']) . '">';
  $output .= '<div class="facemash-description">' . wp_kses_post($images[0]['description']) . '</div>';
  $output .= '</div>';

  $output .= '<div class="facemash-image" data-image-id="' . esc_attr($images[1]['id']) . '" data-rating="' . esc_attr($images[1]['rating']) . '">';
  // $output .= '<div class="facemash-rating">Rating: ' . esc_html($images[1]['rating']) . '</div>';
  $output .= '<div class="facemash-title">' . esc_html($images[1]['title']) . '</div>';
  $output .= '<img src="' . esc_url($images[1]['url']) . '" alt="' . esc_attr($images[1]['title']) . '">';
  $output .= '<div class="facemash-description">' . wp_kses_post($images[1]['description']) . '</div>';
  $output .= '</div>';

  $output .= '<button id="facemash-skip" class="facemash-skip-button">Skip</button>';
  $output .= '</div>';

  return $output;
}

// Function to convert Elo rating to star rating
function elo_to_stars($rating) {
  if ($rating <= 1200) {
    return '★☆☆☆☆'; // 1 star
  } elseif ($rating <= 1400) {
    return '★★☆☆☆'; // 2 stars
  } elseif ($rating <= 1600) {
    return '★★★☆☆'; // 3 stars
  } elseif ($rating <= 1800) {
    return '★★★★☆'; // 4 stars
  } else {
    return '★★★★★'; // 5 stars
  }
}

// New results shortcode
add_shortcode('image_facemash_results', 'image_facemash_results_shortcode');
function image_facemash_results_shortcode() {
  global $wpdb;
  $table_name = $wpdb->prefix . 'image_facemash_ratings';

  // Pagination settings
  $items_per_page = 10;
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

  $output = '<div id="facemash-results-container">';
  $output .= '<div class="facemash-results-row">';
  $output .= '<div class="facemash-results-cell facemash-results-th-thumbnail">Thumbnail</div>';
  $output .= '<div class="facemash-results-cell facemash-results-th-description">Description</div>';
  $output .= '<div class="facemash-results-cell facemash-results-th-rating">Rating</div>';
  $output .= '</div>';

  foreach ($results as $result) {
    $thumbnail = wp_get_attachment_image_src($result->image_id, 'thumbnail');
    $full_image_url = wp_get_attachment_url($result->image_id); // Get full-size image URL
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

  // Pagination controls
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

// Get two random images from the media library
function image_facemash_get_random_images() {
  global $wpdb;
  $table_name = $wpdb->prefix . 'image_facemash_ratings';

  // Get all image attachments
  $attachments = get_posts(array(
    'post_type' => 'attachment',
    'post_mime_type' => 'image',
    'posts_per_page' => -1,
    'post_status' => 'inherit'
  ));

  if (empty($attachments)) {
    return array();
  }

  // Initialize ratings for new images
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

  // Select two random images with their ratings, titles, and descriptions
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
        'id' => $result->image_id,
        'url' => $url,
        'rating' => $result->rating,
        'title' => $result->post_title,
        'description' => $result->post_content
      );
    }
  }

  return $images;
}

// AJAX handler for voting
add_action('wp_ajax_facemash_vote', 'image_facemash_vote');
add_action('wp_ajax_nopriv_facemash_vote', 'image_facemash_vote');
function image_facemash_vote() {
  check_ajax_referer('facemash_vote_nonce', 'nonce');

  $winner_id = isset($_POST['winner']) ? intval($_POST['winner']) : 0;
  $loser_id = isset($_POST['loser']) ? intval($_POST['loser']) : 0;

  if (!$winner_id || !$loser_id) {
    wp_send_json_error('Invalid image IDs');
  }

  global $wpdb;
  $table_name = $wpdb->prefix . 'image_facemash_ratings';

  // Get current ratings
  $winner_rating = $wpdb->get_var($wpdb->prepare("SELECT rating FROM $table_name WHERE image_id = %d", $winner_id));
  $loser_rating = $wpdb->get_var($wpdb->prepare("SELECT rating FROM $table_name WHERE image_id = %d", $loser_id));

  if (is_null($winner_rating) || is_null($loser_rating)) {
    wp_send_json_error('Ratings not found');
  }

  // Elo rating calculation
  $K = 32;
  $expected_winner = 1 / (1 + pow(10, ($loser_rating - $winner_rating) / 400));
  $expected_loser = 1 / (1 + pow(10, ($winner_rating - $loser_rating) / 400));

  $new_winner_rating = round($winner_rating + $K * (1 - $expected_winner));
  $new_loser_rating = round($loser_rating + $K * (0 - $expected_loser));

  // Update ratings
  $wpdb->update($table_name, array('rating' => $new_winner_rating), array('image_id' => $winner_id), array('%d'), array('%d'));
  $wpdb->update($table_name, array('rating' => $new_loser_rating), array('image_id' => $loser_id), array('%d'), array('%d'));

  // Get new random images
  $new_images = image_facemash_get_random_images();
  if (count($new_images) < 2) {
    wp_send_json_error('Not enough images');
  }

  // Modified response to include ratings, title, and description
  wp_send_json_success(array(
    'image1' => array(
      'id' => $new_images[0]['id'],
      'url' => $new_images[0]['url'],
      'rating' => $new_images[0]['rating'],
      'title' => $new_images[0]['title'],
      'description' => $new_images[0]['description']
    ),
    'image2' => array(
      'id' => $new_images[1]['id'],
      'url' => $new_images[1]['url'],
      'rating' => $new_images[1]['rating'],
      'title' => $new_images[1]['title'],
      'description' => $new_images[1]['description']
    )
  ));
}

// New AJAX handler for skipping
add_action('wp_ajax_facemash_skip', 'image_facemash_skip');
add_action('wp_ajax_nopriv_facemash_skip', 'image_facemash_skip');
function image_facemash_skip() {
  check_ajax_referer('facemash_vote_nonce', 'nonce'); // Reuse the same nonce for simplicity

  // Get new random images without updating ratings
  $new_images = image_facemash_get_random_images();
  if (count($new_images) < 2) {
    wp_send_json_error('Not enough images');
  }

  // Return new images in the same format as the vote handler
  wp_send_json_success(array(
    'image1' => array(
      'id' => $new_images[0]['id'],
      'url' => $new_images[0]['url'],
      'rating' => $new_images[0]['rating'],
      'title' => $new_images[0]['title'],
      'description' => $new_images[0]['description']
    ),
    'image2' => array(
      'id' => $new_images[1]['id'],
      'url' => $new_images[1]['url'],
      'rating' => $new_images[1]['rating'],
      'title' => $new_images[1]['title'],
      'description' => $new_images[1]['description']
    )
  ));
}

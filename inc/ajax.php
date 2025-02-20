<?php

/**
 * Registers AJAX handlers for voting functionality.
 * Handles both logged-in (wp_ajax_) and non-logged-in (wp_ajax_nopriv_) users.
 */
add_action('wp_ajax_facemash_vote', 'image_facemash_vote');
add_action('wp_ajax_nopriv_facemash_vote', 'image_facemash_vote');

/**
 * Handles the AJAX voting process for Image Facemash.
 * Updates Elo ratings based on user votes and returns new images.
 *
 * @return void Outputs JSON response and terminates execution
 */
function image_facemash_vote(): void {
  // Verify the AJAX request with nonce check
  check_ajax_referer('facemash_vote_nonce', 'nonce');

  // Sanitize and validate input IDs
  $winner_id = isset($_POST['winner']) ? intval($_POST['winner']) : 0;
  $loser_id = isset($_POST['loser']) ? intval($_POST['loser']) : 0;

  if (!$winner_id || !$loser_id) {
    wp_send_json_error('Invalid image IDs provided in the request');
    return;
  }

  global $wpdb;
  $table_name = $wpdb->prefix . 'image_facemash_ratings';

  // Fetch current ratings for both images
  $winner_rating = $wpdb->get_var($wpdb->prepare(
    "SELECT rating FROM $table_name WHERE image_id = %d",
    $winner_id
  ));
  $loser_rating = $wpdb->get_var($wpdb->prepare(
    "SELECT rating FROM $table_name WHERE image_id = %d",
    $loser_id
  ));

  // Check if ratings exist in the database
  if (is_null($winner_rating) || is_null($loser_rating)) {
    wp_send_json_error('Ratings not found for one or both images');
    return;
  }

  // Elo rating calculation constants and formulas
  $K = 32; // Elo rating adjustment factor
  $expected_winner = 1 / (1 + pow(10, ($loser_rating - $winner_rating) / 400));
  $expected_loser = 1 / (1 + pow(10, ($winner_rating - $loser_rating) / 400));

  // Calculate new ratings
  $new_winner_rating = round($winner_rating + $K * (1 - $expected_winner));
  $new_loser_rating = round($loser_rating + $K * (0 - $expected_loser));

  // Update ratings in the database
  $wpdb->update(
    $table_name,
    array('rating' => $new_winner_rating),
    array('image_id' => $winner_id),
    array('%d'),
    array('%d')
  );
  $wpdb->update(
    $table_name,
    array('rating' => $new_loser_rating),
    array('image_id' => $loser_id),
    array('%d'),
    array('%d')
  );

  // Fetch new pair of random images for the next comparison
  $new_images = image_facemash_get_random_images();
  if (count($new_images) < 2) {
    wp_send_json_error('Not enough images available for comparison');
    return;
  }

  // Prepare and send success response with new image data
  wp_send_json_success(array(
    'image1' => array(
      'id'          => $new_images[0]['id'],
      'url'         => $new_images[0]['url'],
      'rating'      => $new_images[0]['rating'],
      'title'       => $new_images[0]['title'],
      'description' => $new_images[0]['description']
    ),
    'image2' => array(
      'id'          => $new_images[1]['id'],
      'url'         => $new_images[1]['url'],
      'rating'      => $new_images[1]['rating'],
      'title'       => $new_images[1]['title'],
      'description' => $new_images[1]['description']
    )
  ));
}

/**
 * Registers AJAX handlers for skipping functionality.
 * Handles both logged-in (wp_ajax_) and non-logged-in (wp_ajax_nopriv_) users.
 */
add_action('wp_ajax_facemash_skip', 'image_facemash_skip');
add_action('wp_ajax_nopriv_facemash_skip', 'image_facemash_skip');

/**
 * Handles the AJAX skip action for Image Facemash.
 * Returns a new pair of random images without modifying ratings.
 *
 * @return void Outputs JSON response and terminates execution
 */
function image_facemash_skip(): void {
  // Verify the AJAX request using the same nonce as voting
  check_ajax_referer('facemash_vote_nonce', 'nonce');

  // Get new random images without changing ratings
  $new_images = image_facemash_get_random_images();
  if (count($new_images) < 2) {
    wp_send_json_error('Not enough images available to skip to');
    return;
  }

  // Prepare and send success response with new image data
  wp_send_json_success(array(
    'image1' => array(
      'id'          => $new_images[0]['id'],
      'url'         => $new_images[0]['url'],
      'rating'      => $new_images[0]['rating'],
      'title'       => $new_images[0]['title'],
      'description' => $new_images[0]['description']
    ),
    'image2' => array(
      'id'          => $new_images[1]['id'],
      'url'         => $new_images[1]['url'],
      'rating'      => $new_images[1]['rating'],
      'title'       => $new_images[1]['title'],
      'description' => $new_images[1]['description']
    )
  ));
}

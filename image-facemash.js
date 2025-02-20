jQuery(document).ready(function ($) {
  // Existing click handler for voting
  $('#facemash-container').on('click', '.facemash-image', function () {
    var winnerId = $(this).data('image-id');
    var loserId = $(this).siblings('.facemash-image').data('image-id');

    $.ajax({
      url: facemashAjax.ajaxurl,
      type: 'POST',
      data: {
        action: 'facemash_vote',
        winner: winnerId,
        loser: loserId,
        nonce: facemashAjax.nonce
      },
      success: function (response) {
        if (response.success) {
          $('#facemash-container').html(
            '<div class="facemash-image" data-image-id="' + response.data.image1.id + '" data-rating="' + response.data.image1.rating + '">' +
            // '<div class="facemash-rating">Rating: ' + response.data.image1.rating + '</div>' +
            '<div class="facemash-title">' + response.data.image1.title + '</div>' +
            '<img src="' + response.data.image1.url + '" alt="' + response.data.image1.title + '">' +
            '<div class="facemash-description">' + response.data.image1.description + '</div>' +
            '</div>' +
            '<div class="facemash-image" data-image-id="' + response.data.image2.id + '" data-rating="' + response.data.image2.rating + '">' +
            // '<div class="facemash-rating">Rating: ' + response.data.image2.rating + '</div>' +
            '<div class="facemash-title">' + response.data.image2.title + '</div>' +
            '<img src="' + response.data.image2.url + '" alt="' + response.data.image2.title + '">' +
            '<div class="facemash-description">' + response.data.image2.description + '</div>' +
            '</div>' +
            '<button id="facemash-skip" class="facemash-skip-button">Skip</button>'
          );
        } else {
          console.error('Error:', response.data);
        }
      },
      error: function (xhr, status, error) {
        console.error('AJAX Error:', error);
      }
    });
  });

  // New click handler for skip button
  $('#facemash-container').on('click', '#facemash-skip', function () {
    $.ajax({
      url: facemashAjax.ajaxurl,
      type: 'POST',
      data: {
        action: 'facemash_skip',
        nonce: facemashAjax.nonce
      },
      success: function (response) {
        if (response.success) {
          $('#facemash-container').html(
            '<div class="facemash-image" data-image-id="' + response.data.image1.id + '" data-image-id="' + response.data.image1.rating + '">' +
            // '<div class="facemash-rating">Rating: ' + response.data.image1.rating + '</div>' +
            '<div class="facemash-title">' + response.data.image1.title + '</div>' +
            '<img src="' + response.data.image1.url + '" alt="' + response.data.image1.title + '">' +
            '<div class="facemash-description">' + response.data.image1.description + '</div>' +
            '</div>' +
            '<div class="facemash-image" data-image-id="' + response.data.image2.id + '" data-image-id="' + response.data.image2.rating + '">' +
            // '<div class="facemash-rating">Rating: ' + response.data.image2.rating + '</div>' +
            '<div class="facemash-title">' + response.data.image2.title + '</div>' +
            '<img src="' + response.data.image2.url + '" alt="' + response.data.image2.title + '">' +
            '<div class="facemash-description">' + response.data.image2.description + '</div>' +
            '</div>' +
            '<button id="facemash-skip" class="facemash-skip-button">Skip</button>'
          );
        } else {
          console.error('Error:', response.data);
        }
      },
      error: function (xhr, status, error) {
        console.error('AJAX Error:', error);
      }
    });
  });
});
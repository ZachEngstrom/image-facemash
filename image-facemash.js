/**
 * Initializes Image Facemash functionality when the document is ready.
 * @param {jQuery} $ - The jQuery object
 */
jQuery(document).ready(function ($) {
  /**
   * Handles voting by clicking on an image in the Facemash container.
   * Sends an AJAX request to record the vote and updates the UI with new images.
   */
  $('#facemash-container').on('click', '.facemash-image', function () {
    // Get the winner and loser image IDs from data attributes
    const winnerId = $(this).data('image-id');
    const loserId = $(this).siblings('.facemash-image').data('image-id');

    // Perform AJAX request to submit the vote
    $.ajax({
      url: facemashAjax.ajaxurl, // WordPress AJAX URL from localized script
      type: 'POST',
      data: {
        action: 'facemash_vote', // AJAX action name
        winner: winnerId,
        loser: loserId,
        nonce: facemashAjax.nonce // Security nonce
      },
      /**
       * Updates the UI with new images on successful vote.
       * @param {Object} response - AJAX response object
       */
      success: function (response) {
        if (response.success) {
          // Build HTML for new image pair
          const newImagesHtml = `
                      <div class="facemash-image" data-image-id="${response.data.image1.id}" data-rating="${response.data.image1.rating}">
                          <!-- Rating display is optional: <div class="facemash-rating">Rating: ${response.data.image1.rating}</div> -->
                          <div class="facemash-title">${escapeHtml(response.data.image1.title)}</div>
                          <img src="${response.data.image1.url}" alt="${escapeHtml(response.data.image1.title)}">
                          <div class="facemash-description">${response.data.image1.description}</div>
                      </div>
                      <div class="facemash-image" data-image-id="${response.data.image2.id}" data-rating="${response.data.image2.rating}">
                          <!-- Rating display is optional: <div class="facemash-rating">Rating: ${response.data.image2.rating}</div> -->
                          <div class="facemash-title">${escapeHtml(response.data.image2.title)}</div>
                          <img src="${response.data.image2.url}" alt="${escapeHtml(response.data.image2.title)}">
                          <div class="facemash-description">${response.data.image2.description}</div>
                      </div>
                      <button id="facemash-skip" class="facemash-skip-button">Skip</button>
                  `;
          $('#facemash-container').html(newImagesHtml);
        } else {
          console.error('Voting Error:', response.data);
        }
      },
      /**
       * Logs AJAX errors to the console.
       * @param {jqXHR} xhr - jQuery XMLHttpRequest object
       * @param {string} status - HTTP status
       * @param {string} error - Error message
       */
      error: function (xhr, status, error) {
        console.error('AJAX Voting Error:', error, status, xhr);
      }
    });
  });

  /**
   * Handles skipping the current image pair via the skip button.
   * Sends an AJAX request to fetch new images without recording a vote.
   */
  $('#facemash-container').on('click', '#facemash-skip', function () {
    // Perform AJAX request to skip current images
    $.ajax({
      url: facemashAjax.ajaxurl, // WordPress AJAX URL from localized script
      type: 'POST',
      data: {
        action: 'facemash_skip', // AJAX action name
        nonce: facemashAjax.nonce // Security nonce
      },
      /**
       * Updates the UI with new images on successful skip.
       * @param {Object} response - AJAX response object
       */
      success: function (response) {
        if (response.success) {
          // Build HTML for new image pair
          const newImagesHtml = `
                      <div class="facemash-image" data-image-id="${response.data.image1.id}" data-rating="${response.data.image1.rating}">
                          <!-- Rating display is optional: <div class="facemash-rating">Rating: ${response.data.image1.rating}</div> -->
                          <div class="facemash-title">${escapeHtml(response.data.image1.title)}</div>
                          <img src="${response.data.image1.url}" alt="${escapeHtml(response.data.image1.title)}">
                          <div class="facemash-description">${response.data.image1.description}</div>
                      </div>
                      <div class="facemash-image" data-image-id="${response.data.image2.id}" data-rating="${response.data.image2.rating}">
                          <!-- Rating display is optional: <div class="facemash-rating">Rating: ${response.data.image2.rating}</div> -->
                          <div class="facemash-title">${escapeHtml(response.data.image2.title)}</div>
                          <img src="${response.data.image2.url}" alt="${escapeHtml(response.data.image2.title)}">
                          <div class="facemash-description">${response.data.image2.description}</div>
                      </div>
                      <button id="facemash-skip" class="facemash-skip-button">Skip</button>
                  `;
          $('#facemash-container').html(newImagesHtml);
        } else {
          console.error('Skip Error:', response.data);
        }
      },
      /**
       * Logs AJAX errors to the console.
       * @param {jqXHR} xhr - jQuery XMLHttpRequest object
       * @param {string} status - HTTP status
       * @param {string} error - Error message
       */
      error: function (xhr, status, error) {
        console.error('AJAX Skip Error:', error, status, xhr);
      }
    });
  });

  /**
   * Escapes HTML special characters to prevent XSS.
   * @param {string} unsafe - The string to escape
   * @returns {string} Escaped string
   */
  function escapeHtml(unsafe) {
    return unsafe
      .replace(/&/g, "&amp;")
      .replace(/</g, "&lt;")
      .replace(/>/g, "&gt;")
      .replace(/"/g, "&quot;")
      .replace(/'/g, "&#039;");
  }
});
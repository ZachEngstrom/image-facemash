# WP Plugin Facemash

![Plugin Version](https://img.shields.io/badge/version-1.0.0-blue.svg)
![License](https://img.shields.io/badge/license-GPLv2-green.svg)
![WordPress](https://img.shields.io/badge/WordPress-5.0%2B-blue.svg)

The **Image Facemash** plugin is a WordPress plugin that enables users to compare and rank images from the media library using an Elo rating system. It offers an interactive image comparison interface and a paginated results table.

## Features

- **Image Comparison**: Displays two random images for users to vote on or skip.
- **Elo Rating System**: Updates image ratings based on votes with a configurable K-Factor.
- **Results Table**: Shows ranked images with pagination, thumbnails, and star ratings.
- **Admin Settings**: Allows customization of items per page and Elo K-Factor.
- **Responsive Design**: Adapts to desktop and mobile devices.

## Installation

1. **Download**: Clone or download this repository:
   ```bash
   git clone https://gitlab.com/engza/image-facemash.git
   ```
2. **Upload**: Upload the `image-facemash` folder to your WordPress plugins directory (`/wp-content/plugins/`).
3. **Activate**: Go to **Plugins** in your WordPress admin dashboard and activate "Image Facemash".
4. **Configure**: (Optional) Visit **Settings > Image Facemash** to adjust settings.

Alternatively, package it as a `.zip` file and install via **Plugins > Add New > Upload Plugin**.

## Usage

### Shortcodes

- **`[image_facemash]`**: Renders the image comparison interface for voting or skipping.

  - Example: Add `[image_facemash]` to a page or post.

- **`[image_facemash_results]`**: Displays a paginated table of ranked images.
  - Example: Use `[image_facemash_results]` to show the leaderboard.

### Admin Settings

- Go to **Settings > Image Facemash** to configure:
  - **Items per Page**: Set the number of results per page (default: 10).
  - **Elo K-Factor**: Adjust the Elo rating sensitivity (default: 32).

### Requirements

- WordPress 5.0 or higher.
- PHP 7.0 or higher.
- Images in the media library (uses `post_type = 'attachment'`).

## File Structure

```
image-facemash/
├── image-facemash.php      # Main plugin file (entry point, enqueues assets)
├── inc/
│   ├── admin.php          # Admin settings page logic
│   ├── ajax.php           # AJAX handlers for voting and skipping
│   ├── db.php             # Database interactions (e.g., image retrieval, rating updates)
│   ├── frontend.php       # Frontend shortcode handlers and display logic
├── image-facemash.css      # Styles for frontend display
├── image-facemash.js       # JavaScript for interactive functionality
└── README.md              # This file
```

## Development

### Key Components

- **PHP**:

  - `image-facemash.php`: Main entry point, includes other files, and enqueues CSS/JS.
  - `inc/admin.php`: Manages the admin settings page for pagination and Elo K-Factor.
  - `inc/ajax.php`: Handles AJAX requests for voting (`facemash_vote`) and skipping (`facemash_skip`).
  - `inc/db.php`: Contains database logic, such as fetching random images and updating ratings.
  - `inc/frontend.php`: Defines shortcodes (`[image_facemash]`, `[image_facemash_results]`) and frontend rendering.

- **JavaScript** (jQuery):

  - `image-facemash.js`: Manages voting and skipping interactions via AJAX, updates the DOM dynamically.

- **CSS**:
  - `image-facemash.css`: Styles the comparison interface, results table, and pagination with responsive design.

### Contributing

1. Fork the repository.
2. Create a feature branch: `git checkout -b feature/your-feature`.
3. Commit your changes: `git commit -m "Add your feature"`.
4. Push to your branch: `git push origin feature/your-feature`.
5. Open a pull request.

Please follow WordPress coding standards and include comments for maintainability.

### Hooks and Filters

- Uses WordPress hooks like `wp_enqueue_scripts`, `admin_menu`, `wp_ajax_*`.
- Add custom hooks/filters in relevant `inc/` files for extensibility.

## License

Licensed under the [GNU General Public License v2.0](https://www.gnu.org/licenses/gpl-2.0.html) or later.

## Credits

Developed by [Your Name]. Built with assistance from Grok (xAI).

## Issues

Report bugs or suggest features via the [Gitlab Issues page](https://gitlab.com/engza/image-facemash/issues).

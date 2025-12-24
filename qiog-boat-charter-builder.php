<?php
/*
Plugin Name: QIOG Boat Charter Builder
Plugin URI: https://qiog.com
Description: A complete boat charter booking builder with draggable stops, dynamic pricing, checkout flow, and email notifications.
Version: 1.4.0
Author: QIOG Cayman
Author URI: https://qiog.com
Requires at least: 6.0
Tested up to: 6.5
Requires PHP: 7.4
Text Domain: qiog-boat-charter
*/

if (!defined('ABSPATH')) {
    exit;
}

/*--------------------------------------------------------------
# Constants
--------------------------------------------------------------*/
define('QIOG_CHARTER_PATH', plugin_dir_path(__FILE__));
define('QIOG_CHARTER_URL', plugin_dir_url(__FILE__));
define('QIOG_CHARTER_VERSION', '1.4.0');

/*--------------------------------------------------------------
# Core Includes
--------------------------------------------------------------*/
require_once QIOG_CHARTER_PATH . 'includes/activation.php';
require_once QIOG_CHARTER_PATH . 'includes/database.php';
require_once QIOG_CHARTER_PATH . 'includes/required-pages.php';

/* AJAX */
require_once QIOG_CHARTER_PATH . 'includes/ajax-stops-addons.php';
require_once QIOG_CHARTER_PATH . 'includes/ajax-packages.php';
require_once QIOG_CHARTER_PATH . 'includes/ajax-checkout.php';

/* Shortcodes */
require_once QIOG_CHARTER_PATH . 'includes/shortcodes/builder.php';
require_once QIOG_CHARTER_PATH . 'includes/shortcodes/builder-new.php';
require_once QIOG_CHARTER_PATH . 'includes/shortcodes/checkout.php';

/* Enqueues */
require_once QIOG_CHARTER_PATH . 'includes/enqueue-frontend.php';

/* Admin */
require_once QIOG_CHARTER_PATH . 'admin/menu.php';
require_once QIOG_CHARTER_PATH . 'admin/dashboard.php';
require_once QIOG_CHARTER_PATH . 'admin/stops.php';
require_once QIOG_CHARTER_PATH . 'admin/addons.php';
require_once QIOG_CHARTER_PATH . 'admin/packages.php';
require_once QIOG_CHARTER_PATH . 'admin/customization.php';
require_once QIOG_CHARTER_PATH . 'admin/email.php';
require_once QIOG_CHARTER_PATH . 'admin/import-export.php';
require_once QIOG_CHARTER_PATH . 'admin/enqueue-admin.php';

/*--------------------------------------------------------------
# Activation Hooks
--------------------------------------------------------------*/
register_activation_hook(__FILE__, 'qiog_plugin_activate');

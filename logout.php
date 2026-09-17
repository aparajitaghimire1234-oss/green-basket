<?php
/**
 * Customer Logout
 */

require_once 'includes/functions.php';

// Destroy session
session_unset();
session_destroy();

set_flash_message('success', 'You have been logged out successfully');
redirect(SITE_URL . '/index.php');

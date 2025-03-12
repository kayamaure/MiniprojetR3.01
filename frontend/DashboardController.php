<?php
// DashboardController.php - Updated to use JWT token authentication

// We no longer use sessions, as authentication is handled via JWT tokens
// The actual token verification will happen in the dashboard.php via JavaScript

// Include the dashboard view
include 'views/dashboard.php';
?>

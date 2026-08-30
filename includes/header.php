<?php
// includes/header.php
// Common HTML head and layout start. Automatically includes session management and helpers.

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/functions.php';

$page_title = isset($page_title) ? $page_title : 'Healthcare Appointment Booking System';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Secure, fast, and easy medical appointment booking for patients and clinical management for doctors.">
    <title><?php echo $page_title; ?></title>
    <!-- Google Fonts - Inter & Outfit -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <!-- Custom Style Sheet -->
    <link href="<?php echo BASE_URL; ?>assets/css/style.css" rel="stylesheet">
</head>
<body class="bg-light">
    
    <!-- Top Navigation Bar -->
    <?php 
    if (!isset($hide_nav) || !$hide_nav) {
        include_once __DIR__ . '/navbar.php';
    } 
    ?>

    <!-- Main Container -->
    <div class="container-fluid">
        <div class="row">
            <?php 
            if (!isset($hide_nav) || !$hide_nav) {
                // Render sidebar for logged-in portal pages
                if (isset($_SESSION['role'])) {
                    include_once __DIR__ . '/sidebar.php';
                    echo '<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">';
                } else {
                    echo '<main class="col-12 px-md-4 py-4">';
                }
            } else {
                echo '<main class="col-12">';
            }
            ?>

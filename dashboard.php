<?php
// Dashboard Router - Redirects users to their role-specific dashboard
require_once 'config/database.php';
require_once 'includes/functions.php';

requireLogin();
requireApproval();

// Redirect based on user role
switch ($_SESSION['role']) {
    case 'developer':
        header('Location: admin/dashboard.php');
        break;
    case 'coordinator':
        header('Location: coordinator/dashboard.php');
        break;
    case 'fellowship_leader':
        header('Location: fellowship-leaders/dashboard.php');
        break;
    case 'section_leader':
        header('Location: section-leaders/dashboard.php');
        break;
    case 'family_leader':
        header('Location: family-leaders/dashboard.php');
        break;
    case 'member':
        header('Location: members/dashboard.php');
        break;
    default:
        header('Location: home.php');
}
exit();
?>

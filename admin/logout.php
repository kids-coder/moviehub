<?php
// BDMovieHub - Admin Logout
require_once __DIR__ . '/../config.php';
logout();
adminRedirect('login.php');

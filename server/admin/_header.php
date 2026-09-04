<?php
require_once dirname(__DIR__) . '/lib/bootstrap.php';
require_admin();
?>
<!doctype html>
<html lang="hi">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>My App Admin</title>
<link rel="stylesheet" href="assets/style.css">
</head>
<body>
<div class="topbar">
  <div class="brand">My App Admin</div>
  <div class="nav">
    <a href="index.php">Dashboard</a>
    <a href="categories.php">Categories</a>
    <a href="links.php">Links</a>
    <a href="ads.php">Ads</a>
    <a href="notifications.php">Notifications</a>
    <a href="users.php">Users</a>
    <a href="import_links.php">Import</a>
    <a href="settings.php">Settings</a>
    <a href="change_password.php">Password</a>
    <a href="logout.php">Logout</a>
  </div>
</div>
<div class="wrap">

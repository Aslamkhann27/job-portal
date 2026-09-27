<?php
require_once __DIR__ . '/includes/auth.php';

cc_logout_user();
header('Location: index.php');
exit;

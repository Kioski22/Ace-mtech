<?php
require_once __DIR__ . "/../includes/admin-auth.php";

admin_logout();

header("Location: /admin2/login.php");
exit;

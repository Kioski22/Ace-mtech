<?php
require_once __DIR__ . "/../includes/admin-auth.php";

admin_logout();

header("Location: /Ace-mtech/admin2/login.php");
exit;

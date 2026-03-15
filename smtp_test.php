<?php
$connection = fsockopen("smtp.gmail.com", 587, $errno, $errstr, 10);

if (!$connection) {
    echo "SMTP blocked: $errstr ($errno)";
} else {
    echo "SMTP connection successful!";
    fclose($connection);
}
?>
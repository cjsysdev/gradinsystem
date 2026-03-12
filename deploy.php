<?php
$secret = "mysecret123";

$payload = file_get_contents("php://input");
$signature = $_SERVER['HTTP_X_HUB_SIGNATURE'] ?? '';

if (!$payload) {
    http_response_code(400);
    exit("No payload");
}

exec("git pull origin main 2>&1", $output);

echo "<pre>";
print_r($output);
echo "</pre>";
?>
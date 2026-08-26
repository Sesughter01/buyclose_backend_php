<?php

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: PUT, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER["REQUEST_METHOD"] === "OPTIONS") {
    http_response_code(204);
    exit;
}

require_once __DIR__ . "/../config/database.php";
require_once __DIR__ . "/../middleware/auth.php";

$user = authenticate();

$data = json_decode(
    file_get_contents("php://input"),
    true
);

$full_name = trim($data["full_name"] ?? "");

if (empty($full_name)) {
    http_response_code(400);

    echo json_encode([
        "success" => false,
        "message" => "Full name is required"
    ]);

    exit;
}

$user_id = $user->data->id;

$stmt = $pdo->prepare(
    "UPDATE users 
     SET full_name = ?
     WHERE id = ?"
);

$stmt->execute([
    $full_name,
    $user_id
]);

echo json_encode([
    "success" => true,
    "message" => "Profile updated successfully"
]);
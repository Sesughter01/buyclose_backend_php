<?php

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER["REQUEST_METHOD"] === "OPTIONS") {
    http_response_code(204);
    exit;
}

require_once __DIR__ . "/../config/database.php";

$vendorId = $_GET["id"] ?? null;

if (!$vendorId || !is_numeric($vendorId)) {
    http_response_code(400);

    echo json_encode([
        "success" => false,
        "message" => "Valid vendor ID is required"
    ]);

    exit;
}

try {

    $stmt = $pdo->prepare("
        SELECT
            v.id,
            v.business_name,
            v.phone,
            v.address,
            v.area_id,
            a.name AS area_name,
            a.state,
            v.verification_status,
            v.active
        FROM vendors v
        INNER JOIN areas a
            ON a.id = v.area_id
        WHERE v.id = ?
        AND v.verification_status = 'approved'
        AND v.active = TRUE
        AND a.active = TRUE
    ");

    $stmt->execute([$vendorId]);

    $vendor = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$vendor) {
        http_response_code(404);

        echo json_encode([
            "success" => false,
            "message" => "Vendor not found"
        ]);

        exit;
    }

    echo json_encode([
        "success" => true,
        "vendor" => $vendor
    ]);

} catch (PDOException $e) {

    http_response_code(500);

    echo json_encode([
        "success" => false,
        "message" => "Failed to retrieve vendor"
    ]);

    exit;
}

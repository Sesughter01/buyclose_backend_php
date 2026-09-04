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

    // Check that the vendor exists and is active
    $vendorStmt = $pdo->prepare("
        SELECT
            id,
            business_name,
            verification_status,
            active
        FROM vendors
        WHERE id = ?
        AND verification_status = 'approved'
        AND active = TRUE
    ");

    $vendorStmt->execute([$vendorId]);

    $vendor = $vendorStmt->fetch(PDO::FETCH_ASSOC);

    if (!$vendor) {
        http_response_code(404);

        echo json_encode([
            "success" => false,
            "message" => "Vendor not found"
        ]);

        exit;
    }

    // Get the vendor's active products
    $productStmt = $pdo->prepare("
        SELECT
            p.id,
            p.name,
            p.description,
            p.price,
            p.stock_status,
            p.image_url,
            c.id AS category_id,
            c.name AS category_name,
            c.slug AS category_slug
        FROM products p
        INNER JOIN categories c
            ON c.id = p.category_id
        WHERE p.vendor_id = ?
        AND p.active = TRUE
        AND c.active = TRUE
        ORDER BY p.id ASC
    ");

    $productStmt->execute([$vendorId]);

    $products = $productStmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        "success" => true,
        "vendor" => [
            "id" => $vendor["id"],
            "business_name" => $vendor["business_name"],
            "verification_status" => $vendor["verification_status"],
            "active" => $vendor["active"]
        ],
        "products" => $products
    ]);

} catch (PDOException $e) {

    http_response_code(500);

    echo json_encode([
        "success" => false,
        "message" => "Failed to retrieve vendor products"
    ]);

    exit;
}

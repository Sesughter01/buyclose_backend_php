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

try {

    $area = trim($_GET["area"] ?? "");
    $category = trim($_GET["category"] ?? "");

    $sql = "
        SELECT DISTINCT
            v.id,
            v.business_name,
            v.phone,
            v.address,
            v.area_id,
            a.name AS area_name,
            a.state
        FROM vendors v
        INNER JOIN areas a
            ON a.id = v.area_id
        LEFT JOIN products p
            ON p.vendor_id = v.id
            AND p.active = TRUE
        LEFT JOIN categories c
            ON c.id = p.category_id
            AND c.active = TRUE
        WHERE v.verification_status = 'approved'
        AND v.active = TRUE
        AND a.active = TRUE
    ";

    $params = [];

    if ($area !== "") {
        $sql .= " AND v.area_id = ?";
        $params[] = $area;
    }

    if ($category !== "") {
        $sql .= " AND c.slug = ?";
        $params[] = $category;
    }

    $sql .= " ORDER BY v.id ASC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    $vendors = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        "success" => true,
        "vendors" => $vendors
    ]);

} catch (PDOException $e) {

    http_response_code(500);

    echo json_encode([
        "success" => false,
        "message" => "Failed to retrieve vendors"
    ]);
}

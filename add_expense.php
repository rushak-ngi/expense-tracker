<?php
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "Only POST allowed"]);
    exit();
}

$data = json_decode(file_get_contents("php://input"), true);
if (!$data) {
    echo json_encode(["success" => false, "message" => "Invalid JSON"]);
    exit();
}

$file = fopen("expenses.csv", "a");
$id = time();
$desc = $data['description'];
$amount = floatval($data['amount']);
$date = $data['date'];
$paid_by = $data['paid_by'];
$people = $data['people'];
$extras = array_map('floatval', $data['extras']);
$settled = array_map(fn($name) => trim($name) . ":Unpaid", $people);

fputcsv($file, [$id, $desc, $amount, $date, $paid_by, implode(";", $people), implode(";", $settled), implode(";", $extras)]);
fclose($file);

echo json_encode(["success" => true, "message" => "Expense added"]);

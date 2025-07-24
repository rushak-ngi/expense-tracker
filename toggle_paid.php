<?php
if (!isset($_GET['id'], $_GET['name'])) {
    http_response_code(400);
    exit("Missing parameters");
}

$id = $_GET['id'];
$name = trim($_GET['name']);
$path = 'expenses.csv';
$rows = array_map('str_getcsv', file($path));
$updated = false;

foreach ($rows as $index => $row) {
    if ($row[0] === $id) {
        [$rid, $desc, $amount, $date, $paid_by, $people_raw, $settled_raw, $extras_raw] = $row;
        $settled_arr = explode(';', $settled_raw);
        $new_settled = [];

        foreach ($settled_arr as $entry) {
            [$person, $status] = explode(':', $entry);
            $person = trim($person);
            if ($person === $name) {
                $status = $status === "Paid" ? "Unpaid" : "Paid";
            }
            $new_settled[] = "$person:$status";
        }

        $rows[$index] = [$rid, $desc, $amount, $date, $paid_by, $people_raw, implode(';', $new_settled), $extras_raw];
        $updated = true;
        break;
    }
}

if ($updated) {
    $fp = fopen($path, 'w');
    foreach ($rows as $r) fputcsv($fp, $r);
    fclose($fp);
    echo json_encode(["success" => true]);
} else {
    http_response_code(404);
    echo json_encode(["success" => false, "message" => "Expense not found"]);
}

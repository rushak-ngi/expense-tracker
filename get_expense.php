<?php
header("Content-Type: application/json");

$rows = file_exists("expenses.csv") ? array_map('str_getcsv', file("expenses.csv")) : [];
$expenses = [];

foreach ($rows as $row) {
    [$id, $desc, $amount, $date, $paid_by, $people_raw, $settled_raw, $extras_raw] = $row;

    $people = explode(';', $people_raw);
    $extras = array_map('floatval', explode(';', $extras_raw));
    $settled_map = [];

    foreach (explode(';', $settled_raw) as $s) {
        [$name, $status] = explode(':', $s);
        $settled_map[trim($name)] = $status;
    }

    $share = round($amount / count($people), 2);
    $debts = [];
    foreach ($people as $i => $p) {
        $p = trim($p);
        if ($p !== $paid_by) {
            $extra = $extras[$i] ?? 0;
            $totalOwed = round($share + $extra, 2);
            $debts[] = [
                "name" => $p,
                "amount" => $totalOwed,
                "status" => $settled_map[$p] ?? "Unpaid"
            ];
        }
    }

    $expenses[] = [
        "id" => $id,
        "description" => $desc,
        "amount" => $amount,
        "date" => $date,
        "paid_by" => $paid_by,
        "debts" => $debts
    ];
}

echo json_encode($expenses);

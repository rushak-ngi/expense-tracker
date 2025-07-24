<?php
$path = 'expenses.csv';
$expenses = [];

if (file_exists($path)) {
    $rows = array_map('str_getcsv', file($path));
    foreach ($rows as $row) {
        if (count($row) >= 8) {
            $expenses[] = $row;
        }
    }
}

$from = $_GET['from'] ?? null;
$to = $_GET['to'] ?? null;

$expenses = array_filter($expenses, function ($row) use ($from, $to) {
    $date = $row[3];
    return (!$from || $date >= $from) && (!$to || $date <= $to);
});
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <title>View Expenses</title>
    <link rel="stylesheet" href="style.css" />
</head>

<body>
    <div class="container">
        <div class="header">
            <span class="title">All Expenses</span>
            <button class="dark-toggle" onclick="toggleMode()" id="modeToggle" title="Toggle Dark Mode">🌙</button>
        </div>

        <form method="GET" class="filter-form">
            <input type="date" name="from" value="<?= htmlspecialchars($_GET['from'] ?? '') ?>">
            <input type="date" name="to" value="<?= htmlspecialchars($_GET['to'] ?? '') ?>">
            <button type="submit">Apply</button>
            <a href="view_expenses.php" class="button-link">Clear</a>
        </form>

        <?php if (empty($expenses)): ?>
            <div class="card">No expenses found for the selected filter.</div>
        <?php else: ?>
            <?php foreach (array_reverse($expenses) as $expense):
                [$id, $desc, $amount, $date, $paid_by, $participants_raw, $settled_raw, $extras_raw] = $expense;

                $participants = explode(';', $participants_raw);
                $settled_entries = explode(';', $settled_raw);
                $settled_map = [];

                foreach ($settled_entries as $entry) {
                    [$name, $status] = explode(':', $entry) + [null, 'false'];
                    if ($name) {
                        $settled_map[$name] = $status;
                    }
                }

                $extras = explode(';', $extras_raw);
                $extra_map = [];
                foreach ($participants as $i => $name) {
                    $extra_map[$name] = $extras[$i] ?? 0;
                }

                $share = count($participants) ? round($amount / count($participants), 2) : 0;
            ?>
                <div class="card expense">
                    <h3><?= htmlspecialchars($desc) ?></h3>
                    <p><strong>Amount:</strong> ₹<?= htmlspecialchars($amount) ?></p>
                    <p><strong>Date:</strong> <?= htmlspecialchars($date) ?></p>
                    <p><strong>Paid by:</strong> <?= htmlspecialchars($paid_by) ?></p>

                    <ul>
                        <?php foreach ($participants as $name):
                            if ($name === $paid_by) continue;
                            $totalOwed = $share + floatval($extra_map[$name] ?? 0);
                            $status = $settled_map[$name] ?? 'false';
                            $class = $status === 'Paid' ? 'paid' : 'not-paid';
                            $label = $status === 'Paid' ? 'Paid' : 'Not Paid';

                        ?>
                            <li>
                                <span class="debtor"><?= htmlspecialchars($name) ?></span>
                                <span class="amount">₹<?= number_format($totalOwed, 2) ?></span>
                                <button
                                    class="<?= $status === 'Paid' ? 'paid' : 'not-paid' ?>"
                                    onclick="togglePaid('<?= htmlspecialchars($id) ?>', '<?= htmlspecialchars($name) ?>')">
                                    <?= $status === 'Paid' ? 'Paid' : 'Not Paid' ?>
                                </button>
                            </li>


                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>

        <a href="index.php" class="button-link">← Back</a>
    </div>

    <script>
        function toggleMode() {
            document.body.classList.toggle("dark");
            const icon = document.getElementById("modeToggle");
            icon.textContent = document.body.classList.contains("dark") ? "☀️" : "🌙";
            localStorage.setItem("theme", document.body.classList.contains("dark") ? "dark" : "light");
        }

        window.addEventListener("DOMContentLoaded", () => {
            const prefersDark = localStorage.getItem("theme") === "dark";
            if (prefersDark) {
                document.body.classList.add("dark");
                document.getElementById("modeToggle").textContent = "☀️";
            }
        });

        function togglePaid(expenseId, participant) {
            fetch("toggle_paid.php", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/x-www-form-urlencoded"
                    },
                    body: `expense_id=${encodeURIComponent(expenseId)}&participant=${encodeURIComponent(participant)}`
                })
                .then(res => res.json())
                .then(json => {
                    if (json.success) {
                        location.reload();
                    } else {
                        alert("Toggle failed: " + json.message);
                    }
                })
                .catch(err => alert("Request error: " + err));
        }
    </script>
</body>

</html>
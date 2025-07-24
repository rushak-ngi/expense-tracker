<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Expense Tracker SPA</title>
    <link rel="stylesheet" href="style.css" />
</head>

<body>
    <div class="container">
        <div class="header">
            <span class="title">Expense Tracker</span>
            <button class="dark-toggle" onclick="toggleMode()" id="modeToggle" title="Toggle Dark Mode">🌙</button>
        </div>

        <form id="expenseForm" class="card">
            <label>Description:
                <input type="text" name="description" required />
            </label>
            <label>Amount:
                <input type="number" name="amount" required />
            </label>
            <label>Date:
                <input type="date" name="date" value="<?= date('Y-m-d') ?>" required />
            </label>
            <label>Paid By:
                <input type="text" name="paid_by" required />
            </label>

            <div id="person-wrapper" class="card">
                <label>People:</label>
                <div id="person-fields">
                    <div class="person-group">
                        <input type="text" name="people[]" placeholder="Person 1" required />
                        <input type="number" name="extras[]" placeholder="Extra ₹" value="0" required />
                        <button type="button" class="remove-person" onclick="this.parentElement.remove()">×</button>
                    </div>
                    <div class="person-group">
                        <input type="text" name="people[]" placeholder="Person 2" required />
                        <input type="number" name="extras[]" placeholder="Extra ₹" value="0" required />
                        <button type="button" class="remove-person" onclick="this.parentElement.remove()">×</button>
                    </div>
                </div>
                <button type="button" onclick="addPerson()">+ Add Person</button>
            </div>

            <div class="form-actions">
                <button type="submit">Add Expense</button>
            </div>
        </form>

        <div id="expensesList">
            <a href="view_expenses.php" class="button-link">View Expenses</a>
        </div>
    </div>

    <script>
        let personCount = 2;

        function addPerson() {
            personCount++;
            const container = document.getElementById("person-fields");
            const group = document.createElement("div");
            group.className = "person-group";
            group.innerHTML = `
        <input type="text" name="people[]" placeholder="Person ${personCount}" required>
        <input type="number" name="extras[]" placeholder="Extra ₹" value="0" required>
        <button type="button" class="remove-person" onclick="this.parentElement.remove()">×</button>
      `;
            container.appendChild(group);
        }

        async function loadExpenses() {
            const res = await fetch("get_expenses.php");
            const data = await res.json();
            const container = document.getElementById("expensesList");
            container.innerHTML = "";
            data.forEach(exp => {
                const card = document.createElement("div");
                card.className = "expense card";
                card.innerHTML = `
          <h3>${exp.description}</h3>
          <p><strong>Date:</strong> ${exp.date} — ₹${exp.amount} paid by <strong>${exp.paid_by}</strong></p>
          <ul>
            ${exp.debts.map(debt => `
              <li>
                <span class="debtor">${debt.name}</span>
                <span class="amount">₹${debt.amount}</span>
                <button class="${debt.status === 'Paid' ? 'paid' : 'not-paid'}" onclick="togglePaid('${exp.id}', '${debt.name}')">
                  ${debt.status}
                </button>
              </li>
            `).join("")}
          </ul>
        `;
                container.appendChild(card);
            });
        }

        async function togglePaid(id, name) {
            await fetch(`toggle_paid.php?id=${id}&name=${name}`);
            loadExpenses();
        }

        const form = document.getElementById("expenseForm");
        form.addEventListener("submit", async function(e) {
            e.preventDefault();

            const amount = parseFloat(form.amount.value);
            if (isNaN(amount) || amount <= 0) {
                alert("Amount must be greater than zero.");
                return;
            }

            const nameInputs = form.querySelectorAll('input[name="people[]"]');
            const extraInputs = form.querySelectorAll('input[name="extras[]"]');
            const names = [];

            for (let i = 0; i < nameInputs.length; i++) {
                const name = nameInputs[i].value.trim();
                const extra = parseFloat(extraInputs[i].value || 0);

                if (!name) {
                    alert(`Person ${i + 1} name is required.`);
                    return;
                }

                if (names.includes(name)) {
                    alert(`Duplicate person name: "${name}". Names must be unique.`);
                    return;
                }

                if (extra < 0) {
                    alert(`Extra amount for ${name || "person " + (i + 1)} cannot be negative.`);
                    return;
                }

                names.push(name);
            }

            const formData = new FormData(this);
            const data = {};
            formData.forEach((value, key) => {
                if (key.endsWith("[]")) {
                    const cleanKey = key.replace("[]", "");
                    if (!data[cleanKey]) data[cleanKey] = [];
                    data[cleanKey].push(value);
                } else {
                    data[key] = value;
                }
            });

            const res = await fetch("add_expense.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json"
                },
                body: JSON.stringify(data),
            });

            const json = await res.json();
            if (json.success) {
                this.reset();
                document.getElementById("person-fields").innerHTML = "";
                addPerson();
                addPerson();
                loadExpenses();
            } else {
                alert("Error: " + json.message);
            }
        });

        loadExpenses();

        function toggleMode() {
            document.body.classList.toggle("dark");
            const icon = document.getElementById("modeToggle");
            icon.textContent = document.body.classList.contains("dark") ? "☀️" : "🌙";
        }

        window.addEventListener("DOMContentLoaded", () => {
            const prefersDark = localStorage.getItem("theme") === "dark";
            if (prefersDark) {
                document.body.classList.add("dark");
                document.getElementById("modeToggle").textContent = "☀️";
            }
        });

        document.getElementById("modeToggle").addEventListener("click", () => {
            const isDark = document.body.classList.contains("dark");
            localStorage.setItem("theme", isDark ? "dark" : "light");
        });
    </script>
</body>

</html>
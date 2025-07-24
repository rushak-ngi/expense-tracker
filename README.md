# 💰 Expense Tracker SPA (PHP + CSV)

This is a simple Single Page Application (SPA) for tracking shared expenses. It uses:

- PHP for backend logic
- CSV file for data storage (no database)
- Vanilla JS for interactivity
- Dark/light mode
- Responsive modern design

---

## 🚀 Features

- Add expenses with amount, description, paid by, and list of people
- Track how much each person owes
- Toggle payment status (Paid / Unpaid)
- Automatically calculates extra amounts per person
- Filter expenses by date
- Mobile-friendly & dark mode compatible

---

## 📁 Folder Structure

```bash
/
├── index.php              # Main SPA interface
├── view_expenses.php      # All expenses in card layout
├── add_expense.php        # Adds expenses to CSV
├── get_expenses.php       # Loads expenses for frontend
├── toggle_paid.php        # Toggles Paid/Unpaid status
├── style.css              # All styles
├── expenses.csv           # CSV storage (gitignored)
└── README.md              # This file

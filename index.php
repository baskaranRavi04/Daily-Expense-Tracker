
<?php
include 'db.php';

$message = "";

if (isset($_POST['add_expense'])) {

    $title = trim($_POST['title']);
    $amount = $_POST['amount'];
    $category = $_POST['category'];
    $date = $_POST['expense_date'];

    $categories = ['Food', 'Travel', 'Shopping', 'Bills', 'Other'];

    if (
        $title !== "" &&
        is_numeric($amount) &&
        $amount > 0 &&
        in_array($category, $categories) &&
        $date !== ""
    ) {

        $stmt = mysqli_prepare(
            $conn,
            "INSERT INTO expenses (title, amount, category, expense_date)
             VALUES (?, ?, ?, ?)"
        );

        mysqli_stmt_bind_param(
            $stmt,
            "sdss",
            $title,
            $amount,
            $category,
            $date
        );

        if (mysqli_stmt_execute($stmt)) {
            $message = "Expense added successfully!";
        } else {
            $message = "Unable to add expense.";
        }

        mysqli_stmt_close($stmt);

    } else {
        $message = "Please enter valid expense details.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Daily Expense Tracker</title>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }

        body {
            background-color: #f4f6f9;
            color: #333;
        }

        header {
            background-color: #253858;
            color: white;
            padding: 25px;
            text-align: center;
        }

        header h1 {
            font-size: 28px;
            margin-bottom: 8px;
        }

        header p {
            font-size: 14px;
            color: #dce4f2;
        }

        .container {
            width: 90%;
            max-width: 650px;
            margin: 40px auto;
        }

        .form-card {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 3px 12px rgba(0,0,0,0.08);
        }

        .form-card h2 {
            color: #253858;
            margin-bottom: 25px;
            text-align: center;
        }

        .form-group {
            margin-bottom: 18px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            font-size: 14px;
        }

        input, select {
            width: 100%;
            padding: 12px;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 15px;
            outline: none;
        }

        input:focus, select:focus {
            border-color: #253858;
        }

        button {
            width: 100%;
            padding: 13px;
            background-color: #253858;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            cursor: pointer;
        }

        button:hover {
            background-color: #172b4d;
        }

        .message {
            background-color: #e4f7e9;
            color: #218838;
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 20px;
            text-align: center;
        }

        footer {
            text-align: center;
            padding: 20px;
            color: #777;
            font-size: 13px;
        }

        @media (max-width: 600px) {
            .container {
                width: 94%;
                margin: 25px auto;
            }

            .form-card {
                padding: 20px;
            }
        }
    </style>
</head>

<body>

    <header>
        <h1>Daily Expense Tracker</h1>
        <p>Manage your daily expenses easily</p>
    </header>

    <div class="container">

        <div class="form-card">

            <h2>Add New Expense</h2>

            <?php if ($message != "") { ?>
                <div class="message">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php } ?>

            <form method="POST">

                <div class="form-group">
                    <label>Expense Title</label>
                    <input
                        type="text"
                        name="title"
                        placeholder="Enter expense title"
                        maxlength="100"
                        required
                    >
                </div>

                <div class="form-group">
                    <label>Amount (₹)</label>
                    <input
                        type="number"
                        name="amount"
                        placeholder="Enter amount"
                        min="0.01"
                        step="0.01"
                        required
                    >
                </div>

                <div class="form-group">
                    <label>Category</label>

                    <select name="category" required>
                        <option value="">Select Category</option>
                        <option value="Food">Food</option>
                        <option value="Travel">Travel</option>
                        <option value="Shopping">Shopping</option>
                        <option value="Bills">Bills</option>
                        <option value="Other">Other</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Expense Date</label>
                    <input
                        type="date"
                        name="expense_date"
                        value="<?php echo date('Y-m-d'); ?>"
                        required
                    >
                </div>

                <button type="submit" name="add_expense">
                    + Add Expense
                </button>

            </form>

        </div>

    </div>

    <footer>
        Daily Expense Tracker | PHP & MySQL Project
    </footer>

</body>
</html>
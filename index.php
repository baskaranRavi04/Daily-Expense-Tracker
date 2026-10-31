<?php
include 'db.php';

// Add Expense
if (isset($_POST['add'])) {

    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $amount = $_POST['amount'];
    $category = mysqli_real_escape_string($conn, $_POST['category']);
    $date = $_POST['expense_date'];

    $sql = "INSERT INTO expenses (title, amount, category, expense_date)
            VALUES ('$title', '$amount', '$category', '$date')";

    if (mysqli_query($conn, $sql)) {
        header("Location: index.php?success=added");
        exit();
    }
}

// Search Expense
$search = "";

if (isset($_GET['search'])) {

    $search = mysqli_real_escape_string($conn, $_GET['search']);

    $result = mysqli_query($conn,
        "SELECT * FROM expenses
         WHERE title LIKE '%$search%'
         OR category LIKE '%$search%'
         ORDER BY id DESC"
    );

} else {

    $result = mysqli_query($conn,
        "SELECT * FROM expenses ORDER BY id DESC"
    );
}

// Calculate Total Expenses
$total_result = mysqli_query($conn,
    "SELECT SUM(amount) AS total FROM expenses"
);

$total = mysqli_fetch_assoc($total_result);
$total_expense = $total['total'] ?? 0;

// Calculate Monthly Expenses
$month_result = mysqli_query($conn,
    "SELECT SUM(amount) AS monthly_total
     FROM expenses
     WHERE MONTH(expense_date) = MONTH(CURRENT_DATE())
     AND YEAR(expense_date) = YEAR(CURRENT_DATE())"
);

$month_data = mysqli_fetch_assoc($month_result);
$monthly_expense = $month_data['monthly_total'] ?? 0;

?>

<!DOCTYPE html>
<html>
<head>

    <title>Daily Expense Tracker</title>

    <style>

        body {
            font-family: Arial, sans-serif;
            background: #f2f5f9;
            margin: 0;
            padding: 20px;
        }

        .container {
            width: 85%;
            margin: auto;
        }

        h1 {
            text-align: center;
            color: #263238;
        }

        .form-box, .table-box, .total-box {
            background: white;
            padding: 20px;
            margin-top: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 8px #ddd;
        }

        input, select {
            width: 100%;
            padding: 10px;
            margin: 8px 0 15px;
            box-sizing: border-box;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        button {
            background: #2878d0;
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 5px;
            cursor: pointer;
        }

        button:hover {
            background: #145ca8;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        th {
            background: #2878d0;
            color: white;
        }

        th, td {
            padding: 12px;
            text-align: center;
            border: 1px solid #ddd;
        }

        .edit {
            background: #168447;
            color: white;
            padding: 7px 12px;
            text-decoration: none;
            border-radius: 4px;
        }

        .delete {
            background: #e53935;
            color: white;
            padding: 7px 12px;
            text-decoration: none;
            border-radius: 4px;
        }

        .total-box {
            text-align: center;
            color: #168447;
            font-size: 20px;
            font-weight: bold;
        }

        .success {
            color: green;
            text-align: center;
        }

        .summary {
            display: flex;
            gap: 20px;
        }

        .summary .total-box {
            flex: 1;
        }

        .search-form {
            display: flex;
            gap: 10px;
            align-items: center;
        }

        .search-form input {
            flex: 1;
            margin: 0;
        }

        .search-form a {
            background: #777;
            color: white;
            padding: 12px 15px;
            text-decoration: none;
            border-radius: 5px;
        }

        @media(max-width: 700px) {

            .container {
                width: 100%;
            }

            .summary {
                flex-direction: column;
                gap: 0;
            }

            .table-box {
                overflow-x: auto;
            }

        }

    </style>

</head>

<body>

<div class="container">

    <h1>Daily Expense Tracker</h1>

    <!-- Success Message -->

    <?php if (isset($_GET['success'])) { ?>

        <p class="success">

            <?php
            if ($_GET['success'] == 'added') {
                echo "Expense Added Successfully!";
            }
            elseif ($_GET['success'] == 'updated') {
                echo "Expense Updated Successfully!";
            }
            elseif ($_GET['success'] == 'deleted') {
                echo "Expense Deleted Successfully!";
            }
            ?>

        </p>

    <?php } ?>


    <!-- Add Expense Form -->

    <div class="form-box">

        <h2>Add New Expense</h2>

        <form method="POST">

            <label>Expense Title</label>

            <input type="text"
                   name="title"
                   placeholder="Enter expense title"
                   required>

            <label>Amount (₹)</label>

            <input type="number"
                   name="amount"
                   placeholder="Enter amount"
                   min="0.01"
                   step="0.01"
                   required>

            <label>Category</label>

            <select name="category" required>

                <option value="">Select Category</option>
                <option value="Food">Food</option>
                <option value="Travel">Travel</option>
                <option value="Shopping">Shopping</option>
                <option value="Education">Education</option>
                <option value="Other">Other</option>

            </select>

            <label>Expense Date</label>

            <input type="date"
                   name="expense_date"
                   required>

            <button type="submit" name="add">
                Add Expense
            </button>

        </form>

    </div>


    <!-- Expense Summary -->

    <div class="summary">

        <div class="total-box">

            <h2>Total Expenses</h2>

            <h3>
                ₹<?php echo number_format($total_expense, 2); ?>
            </h3>

        </div>

        <div class="total-box">

            <h2>This Month's Expenses</h2>

            <h3>
                ₹<?php echo number_format($monthly_expense, 2); ?>
            </h3>

        </div>

    </div>


    <!-- Search Expense -->

    <div class="form-box">

        <h2>Search Expenses</h2>

        <form method="GET" class="search-form">

            <input type="text"
                   name="search"
                   placeholder="Search by title or category"
                   value="<?php echo htmlspecialchars($search); ?>">

            <button type="submit">Search</button>

            <a href="index.php">Show All</a>

        </form>

    </div>


    <!-- Expense Records -->

    <div class="table-box">

        <h2>All Expenses</h2>

        <table>

            <tr>

                <th>ID</th>
                <th>Title</th>
                <th>Amount</th>
                <th>Category</th>
                <th>Date</th>
                <th>Action</th>

            </tr>

            <?php if (mysqli_num_rows($result) > 0) { ?>

                <?php while ($row = mysqli_fetch_assoc($result)) { ?>

                    <tr>

                        <td>
                            <?php echo $row['id']; ?>
                        </td>

                        <td>
                            <?php echo htmlspecialchars($row['title']); ?>
                        </td>

                        <td>
                            ₹<?php echo number_format($row['amount'], 2); ?>
                        </td>

                        <td>
                            <?php echo htmlspecialchars($row['category']); ?>
                        </td>

                        <td>
                            <?php echo $row['expense_date']; ?>
                        </td>

                        <td>

                            <a class="edit"
                               href="edit.php?id=<?php echo $row['id']; ?>">
                               Edit
                            </a>

                            <a class="delete"
                               href="delete.php?id=<?php echo $row['id']; ?>"
                               onclick="return confirm('Are you sure you want to delete this expense?');">
                               Delete
                            </a>

                        </td>

                    </tr>

                <?php } ?>

            <?php } else { ?>

                <tr>

                    <td colspan="6">
                        No Expenses Found
                    </td>

                </tr>

            <?php } ?>

        </table>

    </div>

</div>

</body>
</html>

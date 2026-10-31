
<?php
include 'db.php';

$id = (int)$_GET['id'];

$result = mysqli_query($conn, "SELECT * FROM expenses WHERE id=$id");
$row = mysqli_fetch_assoc($result);

if (!$row) {
    die("Expense not found!");
}

if (isset($_POST['update'])) {

    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $amount = $_POST['amount'];
    $category = mysqli_real_escape_string($conn, $_POST['category']);
    $date = $_POST['expense_date'];

    $sql = "UPDATE expenses SET
            title='$title',
            amount='$amount',
            category='$category',
            expense_date='$date'
            WHERE id=$id";

    if (mysqli_query($conn, $sql)) {
        header("Location: index.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Expense</title>

    <style>
        body {
            font-family: Arial;
            background: #f2f5f9;
            padding: 30px;
        }

        .box {
            background: white;
            width: 400px;
            margin: auto;
            padding: 25px;
            border-radius: 10px;
        }

        input, select {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            box-sizing: border-box;
        }

        button {
            background: green;
            color: white;
            padding: 10px 20px;
            border: none;
            cursor: pointer;
        }

        a {
            text-decoration: none;
            color: blue;
        }
    </style>
</head>

<body>

<div class="box">

    <h2>Edit Expense</h2>

    <form method="POST">

        <label>Expense Title</label>
        <input type="text" name="title"
               value="<?php echo htmlspecialchars($row['title']); ?>" required>

        <label>Amount</label>
        <input type="number" name="amount"
               value="<?php echo $row['amount']; ?>"
               min="0.01" step="0.01" required>

        <label>Category</label>
        <select name="category" required>
            <?php
            $categories = ['Food', 'Travel', 'Shopping', 'Education', 'Other'];

            foreach ($categories as $cat) {
                $selected = ($row['category'] == $cat) ? 'selected' : '';
                echo "<option value='$cat' $selected>$cat</option>";
            }
            ?>
        </select>

        <label>Date</label>
        <input type="date" name="expense_date"
               value="<?php echo $row['expense_date']; ?>" required>

        <button type="submit" name="update">Update Expense</button>

    </form>

    <br>
    <a href="index.php">Back to Home</a>

</div>

</body>
</html>

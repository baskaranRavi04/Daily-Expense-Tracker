<?php
include 'db.php';

if (isset($_GET['id'])) {

    $id = (int)$_GET['id'];

    $sql = "DELETE FROM expenses WHERE id=$id";

    if (mysqli_query($conn, $sql)) {
        header("Location: index.php?success=deleted");
        exit();
    } else {
        echo "Error deleting expense!";
    }

} else {
    header("Location: index.php");
    exit();
}
?>

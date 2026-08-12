<?php 
include '../includes/customer_auth.php';
include '../config/db.php';

$id = $_SESSION['customer_id'];

$result = $conn->query("SELECT * FROM accounts WHERE customer_id = $id");
?>

<h2>Customer Dashboard</h2>

<a href="deposite.php">Deposite</a>
<a href="withdraw.php">Withdraw</a>
<a href="transfer.php">Transfer</a>
<a href="transaction.php">Transaction</a>
<a href="../logout.php">Logout</a>

<hr>

<?php while($row = $result->fetch_assoc()){ ?>
    <p>Account Number: <?php echo $row['account_number']; ?></p>
    <p>Balance: $<?php echo $row['balance']; ?></p>
    <hr>
<?php } ?>
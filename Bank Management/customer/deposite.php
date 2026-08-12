<?php 
include '../includes/customer_auth.php';
include '../config/db.php';

if($_POST){
    $account_id = $_POST['account_id'];
    $amount = $_POST['amount'];

    $conn->begin_transaction();

    $conn->query("UPDATE accounts SET balance = balance + $amount WHERE id=$account_id");
    $conn->query("INSERT INTO transactions (account_id, type, amount) VALUES ($account_id, 'deposite', $amount)");

    $conn->commit();
    echo "Deposite Successful";
}
?>
<?php
require 'db.php';

$id = intval($_GET['id']);

$conn->query("UPDATE tickets SET status='fixed' WHERE id=$id");

header("Location: admin.php");

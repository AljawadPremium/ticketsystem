<?php
require 'db.php';

$stmt = $conn->prepare("
    INSERT INTO tickets (name, phone, location, problem_type, other_problem)
    VALUES (?, ?, ?, ?, ?)
");

$stmt->bind_param(
    "sssss",
    $_POST['name'],
    $_POST['phone'],
    $_POST['location'],
    $_POST['problem_type'],
    $_POST['other_problem']
);

$stmt->execute();

header("Location: index.php?success=1");

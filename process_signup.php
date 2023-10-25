<?php
// Start a session to manage user login state
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get user input from the signup form
    $username = $_POST["username"];
    $email = $_POST["email"];
    $password = $_POST["password"];

    // Establish a database connection
    $conn = new mysqli("localhost", "bytelord", "Chickennuggets#11269", "bytelore");

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Hash the password before storing it in the database
    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    // Check if the username or email is already in use
    $checkQuery = "SELECT id FROM users WHERE username = ? OR email = ?";
    $checkStmt = $conn->prepare($checkQuery);
    $checkStmt->bind_param("ss", $username, $email);
    $checkStmt->execute();
    $checkStmt->store_result();

    if ($checkStmt->num_rows > 0) {
        // Username or email is already in use
        header("Location: signup.php?error=1"); // Redirect back to the signup page with an error message
        exit();
    }

    // Insert the user into the database
    $insertQuery = "INSERT INTO users (username, email, password_hash) VALUES (?, ?, ?)";
    $insertStmt = $conn->prepare($insertQuery);
    $insertStmt->bind_param("sss", $username, $email, $password_hash);

    if ($insertStmt->execute()) {
        // Registration was successful
        $_SESSION["user_id"] = $insertStmt->insert_id; // Set a session variable to indicate the user is logged in
        header("Location: /"); // Redirect to the homepage or another page
    } else {
        // Registration failed
        $error_message = "Registration failed: " . $insertStmt->error;
        header("Location: signup.php?error=" . urlencode($error_message));
    }

    // Close database connections
    $insertStmt->close();
    $checkStmt->close();
    $conn->close();
}

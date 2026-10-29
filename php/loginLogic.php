<?php 

include 'configure.php';
session_start();

if (isset($_SESSION['username'])) {
    $destination = ($_SESSION['role'] ?? '') === 'farmer'
        ? 'home-farmer.php'
        : '../html/index.html';
    header("Location: $destination");
    exit();
}

if (isset($_POST['submit'])) {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ? AND password = ?");
    $stmt->bind_param("ss", $email, $password);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = mysqli_fetch_assoc($result);
        session_regenerate_id(true);
        $_SESSION['username'] = $row['username'];
        $_SESSION['role'] = 'user';
        
        header("Location: ../html/index.html");
        exit();
    } else {
        header("Location: ../LoginIndex.html?error=invalid_credentials");
        exit();
    }
}
?>
<?php 

include 'configure.php';

session_start();

if (isset($_SESSION['username']) && ($_SESSION['role'] ?? '') === 'farmer') {
    header("Location: home-farmer.php");
    exit();
}

if (isset($_SESSION['username']) && ($_SESSION['role'] ?? '') === 'user') {
    header("Location: ../html/index.html");
    exit();
}

if (isset($_POST['submit'])) {
	$email = trim($_POST['email'] ?? '');
	$password = $_POST['password'] ?? '';

	$stmt = $conn->prepare("SELECT username FROM users WHERE email = ? AND password = ?");
	$stmt->bind_param("ss", $email, $password);
	$stmt->execute();
	$result = $stmt->get_result();

	if ($row = $result->fetch_assoc()) {
		session_regenerate_id(true);
		$_SESSION['username'] = $row['username'];
		$_SESSION['role'] = 'farmer';
		header("Location: home-farmer.php");
		exit();
	} 
	else {
		header("Location: ../html/farmer-login.html?error=invalid_credentials");
		exit();
	}
}

?>
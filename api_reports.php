<?php
session_start();
require 'db_connect.php';
header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];

// GET: Fetch all reports
if ($method === 'GET') {
    // Select all columns, including the new 'intensity' column
    $stmt = $pdo->query("SELECT * FROM reports ORDER BY created_at DESC");
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    exit;
}

// POST: Create a new report
if ($method === 'POST') {
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        exit;
    }

    $input = json_decode(file_get_contents('php://input'), true);
    
    $type = $input['type'] ?? 'user';
    $lat = $input['lat'];
    $lng = $input['lng'];
    $locName = $input['location_name'] ?? 'Unknown';
    // Capture intensity, default to 'low' if missing
    $intensity = $input['intensity'] ?? 'low'; 
    $reporterId = $_SESSION['user_id'];

    // Only admins can create 'system' type reports
    if ($type === 'system' && $_SESSION['role'] !== 'admin') {
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        exit;
    }

    try {
        // Updated INSERT statement to include 'intensity'
        $stmt = $pdo->prepare("INSERT INTO reports (type, lat, lng, location_name, intensity, reporter_id) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$type, $lat, $lng, $locName, $intensity, $reporterId]);

        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
    exit;
}

// DELETE: Delete a report (Admin only)
if ($method === 'DELETE') {
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        exit;
    }

    $id = $_GET['id'];
    $stmt = $pdo->prepare("DELETE FROM reports WHERE id = ?");
    $stmt->execute([$id]);
    echo json_encode(['success' => true]);
    exit;
}
?>
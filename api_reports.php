<?php
session_start();
require 'db_connect.php';
header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        exit;
    }

    if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
        $stmt = $pdo->query("
            SELECT r.*, u.name AS reporter_name 
            FROM reports r 
            LEFT JOIN users u ON r.reporter_id = u.id 
            ORDER BY r.created_at DESC
        ");
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    } else {
        $userId = $_SESSION['user_id'];
        $stmt = $pdo->prepare("
            SELECT r.*, u.name AS reporter_name 
            FROM reports r 
            LEFT JOIN users u ON r.reporter_id = u.id 
            WHERE r.reporter_id = ? 
            ORDER BY r.created_at DESC
        ");
        $stmt->execute([$userId]);
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    }
    exit;
}

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
    $intensity = $input['intensity'] ?? 'low'; 
    $reporterId = $_SESSION['user_id'];

    if ($type === 'system' && $_SESSION['role'] !== 'admin') {
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        exit;
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO reports (type, lat, lng, location_name, intensity, reporter_id) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$type, $lat, $lng, $locName, $intensity, $reporterId]);

        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
    exit;
}

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
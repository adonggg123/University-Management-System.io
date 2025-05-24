<?php
header('Content-Type: application/json');

$servername = "localhost";
$username = "root";
$password = "quest4inno@server";
$dbname = "university_management_system";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Get year from POST request
    $data = json_decode(file_get_contents('php://input'), true);
    $year = isset($data['year']) ? (int)$data['year'] : date('Y');

    // Validate year
    if ($year < 2000 || $year > 2100) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid year']);
        exit;
    }

    // Fetch treatment data
    $stmt = $conn->prepare("
        SELECT 
            MONTH(preferred_date) as month,
            COUNT(DISTINCT CASE WHEN decision = 'accepted' THEN student_id END) as done,
            COUNT(DISTINCT CASE WHEN decision = 'declined' THEN student_id END) as undone
        FROM appointments 
        WHERE YEAR(preferred_date) = :year 
        AND decision IN ('accepted', 'declined')
        AND preferred_date IS NOT NULL
        GROUP BY MONTH(preferred_date)
    ");
    $stmt->execute(['year' => $year]);
    $treatment_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Initialize arrays
    $done_data = array_fill(1, 12, 0);
    $undone_data = array_fill(1, 12, 0);
    foreach ($treatment_data as $row) {
        $done_data[$row['month']] = $row['done'];
        $undone_data[$row['month']] = $row['undone'];
    }

    echo json_encode([
        'status' => 'success',
        'done_data' => array_values($done_data),
        'undone_data' => array_values($undone_data)
    ]);
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
}
$conn = null;
?>
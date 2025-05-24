<?php
header('Content-Type: application/json');

$servername = "localhost";
$username = "root";
$password = "quest4inno@server";
$dbname = "university_management_system";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Helper function to determine status based on quantity
    function determineStatus($quantity) {
        if ($quantity == 0) {
            return 'Out of Stock';
        } elseif ($quantity <= 10) {
            return 'Low Stock';
        } else {
            return 'In Stock';
        }
    }

    $method = $_SERVER['REQUEST_METHOD'];

    switch ($method) {
        case 'GET':
            // Fetch items (with optional search and pagination)
            $searchTerm = isset($_GET['search']) ? $_GET['search'] : '';
            $page = isset($_GET['page']) ? max(1, filter_var($_GET['page'], FILTER_SANITIZE_NUMBER_INT)) : 1;
            $limit = isset($_GET['limit']) ? max(1, filter_var($_GET['limit'], FILTER_SANITIZE_NUMBER_INT)) : 10;
            $offset = ($page - 1) * $limit;

            // Count total items for pagination
            $countQuery = "SELECT COUNT(*) as total FROM inventory";
            $countParams = [];
            if ($searchTerm) {
                $countQuery .= " WHERE name LIKE ? OR category LIKE ?";
                $searchPattern = "%$searchTerm%";
                $countParams = [$searchPattern, $searchPattern];
            }
            $countStmt = $conn->prepare($countQuery);
            $countStmt->execute($countParams);
            $totalItems = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];

            // Fetch paginated items
            $query = "SELECT * FROM inventory";
            $params = [];
            if ($searchTerm) {
                $query .= " WHERE name LIKE ? OR category LIKE ?";
                $searchPattern = "%$searchTerm%";
                $params = [$searchPattern, $searchPattern];
            }
            $query .= " ORDER BY created_at DESC LIMIT ? OFFSET ?";

            $stmt = $conn->prepare($query);
            // Bind parameters
            $paramIndex = 1;
            if ($searchTerm) {
                $stmt->bindValue($paramIndex++, $searchPattern, PDO::PARAM_STR);
                $stmt->bindValue($paramIndex++, $searchPattern, PDO::PARAM_STR);
            }
            $stmt->bindValue($paramIndex++, (int)$limit, PDO::PARAM_INT);
            $stmt->bindValue($paramIndex++, (int)$offset, PDO::PARAM_INT);
            $stmt->execute();
            $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Ensure status is up-to-date
            foreach ($items as &$item) {
                $item['status'] = determineStatus($item['quantity']);
            }

            // Return items and pagination info
            echo json_encode([
                'items' => $items,
                'totalItems' => $totalItems,
                'currentPage' => $page,
                'itemsPerPage' => $limit
            ]);
            break;

        case 'POST':
            // Add a new item
            $data = json_decode(file_get_contents('php://input'), true);

            if (!isset($data['name']) || !isset($data['category']) || !isset($data['quantity']) || !isset($data['date_added'])) {
                echo json_encode(['error' => 'Missing required fields']);
                exit;
            }

            $name = filter_var($data['name'], FILTER_SANITIZE_STRING);
            $category = filter_var($data['category'], FILTER_SANITIZE_STRING);
            $quantity = filter_var($data['quantity'], FILTER_SANITIZE_NUMBER_INT);
            $date_added = filter_var($data['date_added'], FILTER_SANITIZE_STRING);
            $status = determineStatus($quantity);

            $stmt = $conn->prepare("
                INSERT INTO inventory (name, category, quantity, status, date_added)
                VALUES (?, ?, ?, ?, ?)
            ");
            $success = $stmt->execute([$name, $category, $quantity, $status, $date_added]);

            if ($success) {
                echo json_encode(['status' => 'success']);
            } else {
                echo json_encode(['error' => 'Failed to add item']);
            }
            break;

        case 'PUT':
            // Update an existing item
            $data = json_decode(file_get_contents('php://input'), true);

            if (!isset($data['id']) || !isset($data['name']) || !isset($data['category']) || !isset($data['quantity']) || !isset($data['date_added'])) {
                echo json_encode(['error' => 'Missing required fields']);
                exit;
            }

            $id = filter_var($data['id'], FILTER_SANITIZE_NUMBER_INT);
            $name = filter_var($data['name'], FILTER_SANITIZE_STRING);
            $category = filter_var($data['category'], FILTER_SANITIZE_STRING);
            $quantity = filter_var($data['quantity'], FILTER_SANITIZE_NUMBER_INT);
            $date_added = filter_var($data['date_added'], FILTER_SANITIZE_STRING);
            $status = determineStatus($quantity);

            $stmt = $conn->prepare("
                UPDATE inventory 
                SET name = ?, category = ?, quantity = ?, status = ?, date_added = ?
                WHERE id = ?
            ");
            $success = $stmt->execute([$name, $category, $quantity, $status, $date_added, $id]);

            if ($success) {
                echo json_encode(['status' => 'success']);
            } else {
                echo json_encode(['error' => 'Failed to update item']);
            }
            break;

        case 'DELETE':
            // Delete an item
            $data = json_decode(file_get_contents('php://input'), true);

            if (!isset($data['id'])) {
                echo json_encode(['error' => 'Missing item ID']);
                exit;
            }

            $id = filter_var($data['id'], FILTER_SANITIZE_NUMBER_INT);

            $stmt = $conn->prepare("DELETE FROM inventory WHERE id = ?");
            $success = $stmt->execute([$id]);

            if ($success) {
                echo json_encode(['status' => 'success']);
            } else {
                echo json_encode(['error' => 'Failed to delete item']);
            }
            break;

        default:
            echo json_encode(['error' => 'Invalid request method']);
            break;
    }
} catch (PDOException $e) {
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}

$conn = null;
?>
<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "myDB";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

function getInventory() {
    global $conn;

    $sql = "SELECT * FROM inventory";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $inventory = array();
        while($row = $result->fetch_assoc()) {
            $inventory[] = $row;
        }
        return $inventory;
    } else {
        return false;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $inventory = getInventory();
    if ($inventory === false) {
        echo json_encode(array('status' => 'error', 'message' => 'Failed to retrieve inventory.'));
    } else {
        echo json_encode(array('status' => 'success', 'data' => $inventory));
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $postData = file_get_contents('php://input');
    $data = json_decode($postData, true);

    if (!empty($data) && is_array($data)) {
        $combinationName = mysqli_real_escape_string($conn, $data[0]['name']);
        $rowids = [];
        $rawMaterials = [];
        $quantities = [];

        foreach ($data as $item) {
            if (is_array($item) && isset($item['name'], $item['rowid'], $item['rawMaterial'], $item['quantity'])) {
                $rowids[] = mysqli_real_escape_string($conn, $item['rowid']);
                $rawMaterials[] = mysqli_real_escape_string($conn, $item['rawMaterial']);
                $quantities[] = floatval($item['quantity']);
            } else {
                echo json_encode(["error" => "Invalid data structure received"]);
                exit();
            }
        }

        $rowidsStr = implode(",", $rowids);
        $rawMaterialsStr = implode(",", $rawMaterials);
        $quantitiesStr = implode(",", $quantities);

        $sql = "INSERT INTO combinations (name, rowID, rawMaterial, quantity) VALUES (?, ?, ?, ?)";

        $stmt = $conn->prepare($sql);
        if ($stmt === false) {
            die('prepare() failed: ' . htmlspecialchars($conn->error));
        }

        $rc = $stmt->bind_param("ssss", $combinationName, $rowidsStr, $rawMaterialsStr, $quantitiesStr);
        if ($rc === false) {
            die('bind_param() failed: ' . htmlspecialchars($stmt->error));
        }

        $rc = $stmt->execute();
        if ($rc === false) {
            die('execute() failed: ' . htmlspecialchars($stmt->error));
        }

        echo json_encode(["message" => "Combination(s) added successfully"]);
    } else {
        echo json_encode(["error" => "No data received or invalid data format"]);
    }
}

$conn->close();
?>

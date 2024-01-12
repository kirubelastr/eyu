<?php
require 'db_connection.php';

function getInventory() {
    global $conn2;

    $sql = "SELECT * FROM inventory";
    $result = $conn2->query($sql);

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
        $combinationName = mysqli_real_escape_string($conn2, $data[0]['name']);
        $rowids = [];
        $rawMaterials = [];
        $quantities = [];
        $price = [];

        foreach ($data as $item) {
            if (is_array($item) && isset($item['name'], $item['rowid'], $item['rawMaterial'], $item['quantity'], $item['price'])) {
                $rowids[] = mysqli_real_escape_string($conn2, $item['rowid']);
                $rawMaterials[] = mysqli_real_escape_string($conn2, $item['rawMaterial']);
                $quantities[] = floatval($item['quantity']);
                $price[] = floatval($item['price']);
            } else {
                echo json_encode(["error" => "Invalid data structure received"]);
                exit();
            }
        }

        $rowidsStr = implode(",", $rowids);
        $rawMaterialsStr = implode(",", $rawMaterials);
        $quantitiesStr = implode(",", $quantities);
        $priceStr = implode(",", $price);

        $sql = "INSERT INTO combinations (name, rowID, rawMaterial, quantity ,price) VALUES (?, ?, ?, ?, ?)";

        $stmt = $conn2->prepare($sql);
        if ($stmt === false) {
            die('prepare() failed: ' . htmlspecialchars($conn->error));
        }

        $rc = $stmt->bind_param("sssss", $combinationName, $rowidsStr, $rawMaterialsStr, $quantitiesStr, $priceStr);
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

$conn2->close();
?>

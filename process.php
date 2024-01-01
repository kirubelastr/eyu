<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "inventory_db";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Add Raw Material
    if (isset($_POST["action"]) && $_POST["action"] == "addRawMaterial") {
        $name = $_POST["rawMaterialName"];
        $quantity = $_POST["rawMaterialQuantity"];

        $sql = "INSERT INTO raw_materials (name, quantity) VALUES ('$name', $quantity)";
        $conn->query($sql);
    }

    // Create Combination
    elseif (isset($_POST["action"]) && $_POST["action"] == "createCombination") {
        $name = $_POST["combinationName"];
        $ingredients = $_POST["combinationIngredients"];

        $sql = "INSERT INTO combinations (name, ingredients) VALUES ('$name', '$ingredients')";
        $conn->query($sql);
    }

    // Sell Juice/Food
    elseif (isset($_POST["action"]) && $_POST["action"] == "sellProduct") {
        $productId = $_POST["sellProduct"];
        $quantity = $_POST["sellProductQuantity"];
        $price = $_POST["sellProductPrice"];

        $sql = "SELECT * FROM juice_products WHERE id = $productId";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            if ($row["quantity"] >= $quantity) {
                $newQuantity = $row["quantity"] - $quantity;
                $sqlUpdate = "UPDATE juice_products SET quantity = $newQuantity WHERE id = $productId";
                $conn->query($sqlUpdate);

                // Record sale in juice_sells table
                $sqlSale = "INSERT INTO juice_sells (product_id, quantity, price) VALUES ($productId, $quantity, $price)";
                $conn->query($sqlSale);
            }
        }
    }
    // Record Loss
    elseif (isset($_POST["action"]) && $_POST["action"] == "recordLoss") {
        $itemId = $_POST["lossItemId"];
        $quantity = $_POST["lossQuantity"];
        $reason = $_POST["lossReason"];

        $sql = "INSERT INTO losses (item_id, quantity, reason) VALUES ($itemId, $quantity, '$reason')";
        $conn->query($sql);
    }
}

$conn->close();
?>

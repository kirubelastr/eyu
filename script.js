
document.addEventListener('DOMContentLoaded', function () {
    displayInventory();
    loadDropdowns();
    // Get the current page filename (e.g., "fruit.html")
var currentPage = location.pathname.split("/").slice(-1)[0];

// Function to set the background color and disable the link for the current page
function setCurrentPageStyle() {
    var fruitLink = document.getElementById("fruitLink");
    var juiceLink = document.getElementById("juiceLink");
    var expenceLink = document.getElementById("expenceLink");
    var salesLink = document.getElementById("salesLink");
    var Dashboardlink = document.getElementById("Dashboardlink");
    // Reset styles for all links
    Dashboardlink.style.backgroundColor = "";
    Dashboardlink.style.pointerEvents = "auto";

    juiceLink.style.backgroundColor = "";
    juiceLink.style.pointerEvents = "auto";

    salesLink.style.backgroundColor = "";
    salesLink.style.pointerEvents = "auto";

    expenceLink.style.backgroundColor = "";
    expenceLink.style.pointerEvents = "auto";
    // Set styles for the current page
    if (currentPage === "fruit.html") {
        fruitLink.style.backgroundColor = "green";
        fruitLink.style.pointerEvents = "none"; // Disable the link
    } 
}

// Call the function on page load
setCurrentPageStyle();
});
function openNav() {
    document.getElementById("sidebar").style.width = "250px";
    document.getElementById("main").style.marginLeft = "250px";
}

function closeNav() {
    document.getElementById("sidebar").style.width = "0";
    document.getElementById("main").style.marginLeft = "0";
}
function addItem() {
    // Get values from the form
    var itemName = getValueById('itemName');
    var quantity = getValueById('quantity');
    var price = getValueById('price');

    // Validate the values (add more validation as needed)

    // Send the data to the server/database to add the item
    // After successful addition, refresh the displayed inventory, chart, and update summary
    sendData('productSection', { itemName, quantity, price }, function () {
        displayInventory();
        loadDropdowns(); // Refresh dropdowns after adding items
    });
}

function sellItem() {
    var productId = getValueById('sellItemId');
    var quantitySold = getValueById('sellQuantity');
    var sellingprice = getValueById('sellingprice');

    sendData('salesSection', { productId, quantitySold, sellingprice }, function (response) {
        displayInventory();
        resetForms();  // Call the function to reset the form
    });
}

function resetForms() {
    console.log('resetForms function called');
    loadDropdowns();
    
    document.getElementById('sellItemId').value = '';
    document.getElementById('Availablequantity').value = '';
    document.getElementById('stocksellingprice').value = '';
    document.getElementById('sellQuantity').value = '';
    document.getElementById('sellingprice').value = '';
}

function recordLoss() {
    // Get values from the form
    var productId = getValueById('lossItemId');
    var quantityLost = getValueById('lossQuantity');
    var reason = getValueById('lossReason');

    // Validate the values (add more validation as needed)

    // Send the data to the server to record the loss
    // After successful loss recording, refresh inventory, chart, and update summary
    sendData('lossesSection', { productId, quantityLost, reason }, function () {
        displayInventory();
        resetForml();  // Call the function to reset the form
    });
}

function resetForml() {
    console.log('resetForml function called');
    loadDropdowns();
    document.getElementById('lossItemId').value = '';
    document.getElementById('lossQuantity').value = '';
    document.getElementById('lossReason').value = '';
    document.getElementById('Availablequantityloss').value = '';
}
// Add this function definition to your script
function fetchInventoryData(callback) {
    var xhr = new XMLHttpRequest();
    xhr.open('GET', 'backend.php', true);
    xhr.onreadystatechange = function () {
        if (xhr.readyState == 4) {
            console.log(xhr.responseText); // Add this line
            if (xhr.status == 200) {
                try {
                    var inventoryData = JSON.parse(xhr.responseText);
                    callback(inventoryData);
                } catch (error) {
                    //console.error('Error parsing JSON:', error);
                    callback({ error: 'Error parsing JSON' });
                }
            } else {
                console.error('Request failed with status:', xhr.status);
                callback({ error: 'Request failed' });
            }
        }
    };
    
    xhr.send();
}
function loadDropdowns() {
    // Load items into the sell item and loss item dropdowns
    var sellDropdown = document.getElementById('sellItemId');
    var lossDropdown = document.getElementById('lossItemId');
    var Availablequantity = document.getElementById('Availablequantity');
    var Availablequantityloss = document.getElementById('Availablequantityloss');
    var stocksellingprice = document.getElementById('stocksellingprice');

    // Fetch inventory data from the server/database
    fetchInventoryData(function (inventoryData) {
        // Clear existing options before updating
        clearDropdownOptions(sellDropdown);
        clearDropdownOptions(lossDropdown);

        // Add a placeholder option
        var placeholderOption = document.createElement('option');
        placeholderOption.text = 'Please select an item';
        placeholderOption.value = '';
        sellDropdown.add(placeholderOption, 0);
        lossDropdown.add(placeholderOption.cloneNode(true), 0);  // Clone the placeholder option for the second dropdown

        // Update the sell item and loss item dropdowns
        updateDropdownOptions(sellDropdown, inventoryData);
        updateDropdownOptions(lossDropdown, inventoryData);

        // Update available quantity for the selected product
        sellDropdown.addEventListener('change', function () {
            var selectedProductId = this.value;
            var selectedProduct = inventoryData.find(product => product.id == selectedProductId);

            if (selectedProduct) {
                Availablequantity.value = selectedProduct.quantity;
                stocksellingprice.value = selectedProduct.price;
            } else {
                Availablequantity.value = 0;
            }
        });
        lossDropdown.addEventListener('change', function () {
            var selectedProductId = this.value;
            var selectedProduct = inventoryData.find(product => product.id == selectedProductId);

            if (selectedProduct) {
                Availablequantityloss.value = selectedProduct.quantity;
            } else {
                Availablequantityloss.value = 0;
            }
        });
    });
}


function updateDropdownOptions(dropdown, inventoryData) {
    // Update the options of a dropdown based on the provided inventory data
    // The dropdown parameter is the HTML select element, and inventoryData is an array of objects with product information

    // Create an option for each item in the inventoryData array
    inventoryData.forEach(function (item) {
        var option = document.createElement('option');
        option.value = item.id; // Set the value of the option to the product ID
        option.text = item.name; // Set the text content of the option to the product name
        dropdown.appendChild(option); // Append the option to the dropdown
    });
}


function updateAvailableQuantity(dropdown, inventoryData) {
    // Update the available quantity based on the selected item in the dropdown
    var selectedItemId = dropdown.value;
    var selectedProduct = inventoryData.find(item => item.id == selectedItemId);
    var availableQuantityInput = document.getElementById('Availablequantity');

    if (selectedProduct) {
        availableQuantityInput.value = selectedProduct.quantity;
    } else {
        availableQuantityInput.value = ''; // Reset to empty if no item is selected
    }
}


function clearDropdownOptions(dropdown) {
    // Clear existing options in the dropdown
    dropdown.innerHTML = '';
}

function sendData(section, data, callback) {
    // Send data to the server
    var xhr = new XMLHttpRequest();
    xhr.open('POST', 'backend.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.onreadystatechange = function () {
        if (xhr.readyState == 4) {
            if (xhr.status == 200) {
                // Handle the response
                console.log(xhr.responseText);
                alert(xhr.responseText+'!');
                // Update relevant sections based on response
                handleResponse(section);
                // Invoke the callback if provided
                if (typeof callback === 'function') {
                    callback();
                }
            } else {
                alert('Request failed with status: ' + xhr.status);
            }
        }
    };
    var encodedData = encodeData(data);
    xhr.send('section=' + section + '&' + encodedData);
}


function encodeData(data) {
    // Encode data for sending in a POST request
    return Object.keys(data)
        .map(function (key) {
            return encodeURIComponent(key) + '=' + encodeURIComponent(data[key]);
        })
        .join('&');
}

function handleResponse(section) {
    // Update relevant sections based on response
    switch (section) {
        case 'productSection':
            displayInventory();
            loadDropdowns(); // Refresh dropdowns after adding items
            break;
        case 'salesSection':
        case 'lossesSection':
            displayInventory(); // Refresh inventory after sales or losses
            break;
    }
}

function getValueById(id) {
    // Get value from an element by its ID
    return document.getElementById(id).value;
}

function displayInventory() {
    // Fetch inventory data from the server/database
    // Display the data in the #inventoryOutput div
    fetchInventoryData(function (inventoryData) {
        // Update the display with inventory data
        updateInventoryDisplay(inventoryData);
    });
}
function updateInventoryDisplay(data) {
    // Update the display with inventory data
    var inventoryTableBody = document.getElementById('inventoryTableBody');
    inventoryTableBody.innerHTML = ''; // Clear existing content

    for (var i = 0; i < data.length; i++) {
        var item = data[i];

        // Create a table row for each inventory item
        var row = inventoryTableBody.insertRow();
        row.id = 'row' + item.id; // Assign an id to the row

        // Insert cells into the row
        var idCell = row.insertCell(0);
        var nameCell = row.insertCell(1);
        var quantityCell = row.insertCell(2);
        var priceCell = row.insertCell(3);
        var actionCell = row.insertCell(4);

        // Populate the cells with data
        idCell.innerHTML = item.id;
        nameCell.innerHTML = item.name;
        quantityCell.innerHTML = item.quantity + ' units';
        priceCell.innerHTML = '$' + item.price;
        actionCell.innerHTML = '<button onclick="editItem(' + item.id + ')">Edit</button>' + 
                               '<button onclick="deleteItem(' + item.id + ')">Delete</button>';
    }
}

function populateTableRow(data) {
    // Populate the table row with the selected item data
    var row = document.getElementById('row' + data.id);
    if (row) {
        row.cells[1].innerHTML = '<input type="text" id="editItemName" value="' + data.name + '">';
        row.cells[2].innerHTML = '<input type="number" id="editItemQuantity" value="' + data.quantity + '">';
        row.cells[3].innerHTML = '<input type="number" id="editItemPrice" value="' + data.price + '">';
        row.cells[4].innerHTML = '<button onclick="saveEdit(' + data.id + ')">Save</button>' + 
                                 '<button onclick="cancelEdit(' + data.id + ')">Cancel</button>';
    } else {
        console.log('Row with id ' + 'row' + data.id + ' does not exist');
    }
}

function deleteItem(itemId) {
    // Confirm deletion with the user
    var confirmDelete = confirm('Are you sure you want to delete this item?');

    if (confirmDelete) {
        // Send a request to the server to delete the item
        sendData('deleteSection', { itemId }, function () {
            displayInventory();
        });
    }
}

function sendData(action, data, callback) {
    // Send the data to the server/database to update the item
    // After successful update, refresh the displayed inventory
    var xhr = new XMLHttpRequest();
    xhr.open('POST', 'backend.php?action=' + action, true);
    xhr.setRequestHeader('Content-Type', 'application/json');
    xhr.onreadystatechange = function () {
        if (xhr.readyState == 4 && xhr.status == 200) {
            callback();
        }
    };
    xhr.send(JSON.stringify(data));
}

// Add this function to your existing JavaScript
function editItem(itemId) {
    // Fetch the data of the selected item from the server/database
    fetchItemData(itemId, function (itemData) {
        // Populate the modal with the selected item data
        populateEditModal(itemData);
        // Show the modal
        document.getElementById('editModal').style.display = 'block';
    });
}

function populateEditModal(itemData) {
    // Populate the modal input fields with the selected item data
    document.getElementById('editItemName').value = itemData.name;
    document.getElementById('editItemQuantity').value = itemData.quantity;
    document.getElementById('editItemPrice').value = itemData.price;
}

function saveEdit() {
    // Get the modified values from the modal
    var editedItemName = document.getElementById('editItemName').value;
    var editedItemQuantity = document.getElementById('editItemQuantity').value;
    var editedItemPrice = document.getElementById('editItemPrice').value;

    // Update the table with the modified values
    // ...

    // Close the modal after saving
    closeEditModal();
}

function closeEditModal() {
    // Close the modal
    document.getElementById('editModal').style.display = 'none';
}


function fetchItemData(itemId, callback) {
    // Fetch the data of the selected item from the server/database
    var xhr = new XMLHttpRequest();
    xhr.open('GET', 'backend.php?action=edit&id=' + itemId, true);
    xhr.onreadystatechange = function () {
        if (xhr.readyState == 4 && xhr.status == 200) {
            var itemData = JSON.parse(xhr.responseText);
            callback(itemData);
        }
    };
    xhr.send();
}


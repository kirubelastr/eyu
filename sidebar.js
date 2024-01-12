function toggleNav() {
    var sidebar = document.getElementById("sidebar");
    var main = document.getElementById("main");

    if (sidebar.style.width === "250px") {
        sidebar.style.width = "0";
        main.style.marginLeft = "0";
    } else {
        sidebar.style.width = "250px";
        main.style.marginLeft = "250px";
    }
}
function logout() {
// Send an AJAX request to the server to end the session
$.get("logout.php", function() {
// Redirect to login.php
window.location.href = 'login.html';
});
}

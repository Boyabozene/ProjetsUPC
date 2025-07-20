
<?php
function respond($data, $code = 200) {
    http_response_code($code);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

function is_authenticated() {
    session_start();
    return $_SESSION['user_id'] ?? false;
}
?>

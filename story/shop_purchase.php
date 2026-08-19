<?php
// story/shop_purchase.php
// Purchase an in-game item from a shop NPC. Deducts coins and records purchase in player_items.

session_start();
header('Content-Type: application/json');
if (!isset($_SESSION['username'])) {
    http_response_code(401);
    echo json_encode(['success'=>false, 'error'=>'Unauthorized']);
    exit();
}

include_once __DIR__ . '/../db.php';

$username = $_SESSION['username'];
// Fetch user
$stmt = $conn->prepare("SELECT user_id, coins FROM users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();
if (!$user) { http_response_code(404); echo json_encode(['success'=>false,'error'=>'User not found']); exit(); }
$user_id = (int)$user['user_id'];
$currentCoins = (int)$user['coins'];

// Accept POST params
$item_key = trim($_POST['item_key'] ?? '');
$quantity = max(1, (int)($_POST['quantity'] ?? 1));

// Basic server-side shop catalog (hardcoded for vertical slice)
$catalog = [
    'health_potion' => ['price'=>20, 'label'=>'Health Potion'],
    'xp_potion'     => ['price'=>50, 'label'=>'XP Potion'],
    'small_tree'    => ['price'=>30, 'label'=>'Small Decoration'],
];

if (!isset($catalog[$item_key])) {
    echo json_encode(['success'=>false, 'error'=>'Invalid item']);
    exit();
}
$priceEach = (int)$catalog[$item_key]['price'];
$totalPrice = $priceEach * $quantity;

if ($currentCoins < $totalPrice) {
    echo json_encode(['success'=>false, 'error'=>'Insufficient coins']);
    exit();
}

// Deduct coins and insert/update player_items
$newCoins = $currentCoins - $totalPrice;
$ustmt = $conn->prepare("UPDATE users SET coins = ? WHERE user_id = ?");
$ustmt->bind_param("ii", $newCoins, $user_id);
$ustmt->execute();
$ustmt->close();

// Insert or update player_items
$istmt = $conn->prepare(
    "INSERT INTO player_items (user_id, item_key, quantity) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE quantity = quantity + VALUES(quantity)"
);
$istmt->bind_param("isi", $user_id, $item_key, $quantity);
$istmt->execute();
$istmt->close();

echo json_encode(['success'=>true, 'newCoins' => $newCoins, 'item' => $item_key]);
exit();

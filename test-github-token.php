<?php

// Load environment variables from .env.local
$envFile = __DIR__ . '/website/.env.local';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (str_starts_with(trim($line), '#')) continue;
        if (strpos($line, '=') === false) continue;
        [$key, $value] = explode('=', $line, 2);
        $_SERVER[trim($key)] = trim($value);
    }
}

// Connect to SQLite database directly
$db = new PDO('sqlite:' . __DIR__ . '/website/registry.db');
$userId = 2;

// Get user
$stmt = $db->prepare('SELECT * FROM users WHERE id = ?');
$stmt->execute([$userId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    die("❌ User not found\n");
}

echo "User: {$user['registry_username']} (GitHub: {$user['github_username']})\n";

// Decrypt OAuth token manually
$encryptedToken = $user['github_oauth_token'];
if (!$encryptedToken) {
    die("❌ No OAuth token in database\n");
}

// Simple decryption function (mimicking tiny::cypher()->decrypt)
$secret = $_SERVER['CRYPTO_SECRET'] ?? '';
$algo = $_SERVER['CRYPTO_ALGO'] ?? 'aes-256-cbc';

echo "Crypto secret: " . substr($secret, 0, 10) . "...\n";
echo "Crypto algo: {$algo}\n";
echo "Encrypted token length: " . strlen($encryptedToken) . "\n";

// URL-safe base64 decode
$data = str_replace(['-', '_'], ['+', '/'], $encryptedToken);
$mod4 = strlen($data) % 4;
if ($mod4) {
    $data .= substr('====', $mod4);
}
$data = base64_decode($data);

// Key and IV from secret (same as tiny::cypher())
$key = md5($secret);
$iv = substr(strrev($key), 0, 16);

$token = openssl_decrypt($data, $algo, $key, OPENSSL_RAW_DATA, $iv);

if (!$token) {
    die("❌ Failed to decrypt token\n");
}

$tokenPreview = substr($token, 0, 10) . '...' . substr($token, -4);
echo "Token: {$tokenPreview}\n\n";

// Helper function to call GitHub API
function githubRequest($endpoint, $method = 'GET', $token, $data = null) {
    $url = 'https://api.github.com' . $endpoint;
    
    $headers = [
        'Accept: application/vnd.github+json',
        'Authorization: Bearer ' . $token,
        'User-Agent: MUXI-Registry-Test'
    ];
    
    if ($data && in_array($method, ['POST', 'PUT', 'PATCH'])) {
        $headers[] = 'Content-Type: application/json';
    }
    
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => $method,
        CURLOPT_HTTPHEADER => $headers
    ]);
    
    if ($data && in_array($method, ['POST', 'PUT', 'PATCH'])) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    }
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    $json = json_decode($response, true);
    
    return [
        'code' => $httpCode,
        'data' => $json,
        'raw' => $response
    ];
}

// Test 1: Get authenticated user
echo "\nTest 1: GET /user\n";
$result = githubRequest('/user', 'GET', $token);
if ($result['code'] == 200) {
    echo "✅ Authenticated as: {$result['data']['login']}\n";
} else {
    echo "❌ Failed (HTTP {$result['code']}): {$result['data']['message']}\n";
    die();
}

// Test 2: List repos
echo "\nTest 2: GET /user/repos\n";
$result = githubRequest('/user/repos?per_page=1', 'GET', $token);
if ($result['code'] == 200) {
    echo "✅ Can list repos\n";
} else {
    echo "❌ Failed (HTTP {$result['code']}): {$result['data']['message']}\n";
}

// Test 3: Create a test repo
echo "\nTest 3: POST /user/repos (create test repo)\n";
echo "Repo name: test-muxi-registry-delete-me\n";
$result = githubRequest('/user/repos', 'POST', $token, [
    'name' => 'test-muxi-registry-delete-me',
    'description' => 'Test repo from MUXI Registry - safe to delete',
    'private' => false,
    'auto_init' => false
]);

if ($result['code'] == 201) {
    echo "✅ SUCCESS! Created repo: {$result['data']['full_name']}\n";
    echo "🔗 https://github.com/{$result['data']['full_name']}\n";
    echo "\n⚠️  REMEMBER TO DELETE THIS TEST REPO!\n";
} else {
    echo "❌ Failed (HTTP {$result['code']}): {$result['data']['message']}\n";
    if (isset($result['data']['errors'])) {
        print_r($result['data']['errors']);
    }
}

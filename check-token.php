<?php
// check-token.php
require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Laravel\Sanctum\PersonalAccessToken;

$tokenHash = 'qKolot1lXJiZbNdMtBo3OT1D1cSjXIC0A6o9tpera6f8ee67';

echo "🔍 Checking token: " . substr($tokenHash, 0, 20) . "...\n";

$token = PersonalAccessToken::findToken($tokenHash);

if ($token) {
    echo "✅ Token FOUND in database!\n";
    echo "Token ID: " . $token->id . "\n";
    echo "User ID: " . $token->tokenable_id . "\n";
    echo "Token Name: " . $token->name . "\n";
    echo "Created: " . $token->created_at . "\n";
    echo "Last Used: " . $token->last_used_at . "\n";
    
    // Get user
    $user = $token->tokenable;
    if ($user) {
        echo "👤 User: " . $user->nama . " (" . $user->email . ")\n";
    }
} else {
    echo "❌ Token NOT FOUND in database!\n";
    
    // Cek semua tokens
    $allTokens = PersonalAccessToken::all();
    echo "Total tokens in DB: " . $allTokens->count() . "\n";
    
    if ($allTokens->count() > 0) {
        echo "First token sample:\n";
        $sample = $allTokens->first();
        echo "Hash: " . substr($sample->token, 0, 20) . "...\n";
        echo "User ID: " . $sample->tokenable_id . "\n";
    }
}
<?php
require 'vendor/autoload.php';
require 'bootstrap/app.php';

use Illuminate\Database\Capsule\Manager as DB;

// Get Laravel app
$app = require_once 'bootstrap/app.php';
$app['db'];

// Insert rental
\App\Models\Rental::create([
    'user_id' => 5,
    'house_id' => 1,
    'status' => 'active',
    'stay_decision' => 'yes',
    'lease_status' => 'not_requested'
]);

echo "Rental created successfully!";

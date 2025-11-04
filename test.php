<?php

require 'vendor/autoload.php';

try {
    $app = require_once 'bootstrap/app.php';
    echo "Laravel application bootstraps successfully!\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
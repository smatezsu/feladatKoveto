<?php
function getDatabaseConnection() {
    $conn = new mysqli('127.0.0.1', 'prooktat_SMate', 'J9GugveemCs', 'prooktat_SMate');
    return $conn;
}
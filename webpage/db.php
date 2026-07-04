<?php
// app/db.php

$host = isset($_ENV['DB_HOST']) ? $_ENV['DB_HOST'] : 'localhost';
$user = isset($_ENV['DB_USER']) ? $_ENV['DB_USER'] : 'ticketuser';
$pass = isset($_ENV['DB_PASS']) ? $_ENV['DB_PASS'] : 'ticketpass';
$db   = isset($_ENV['DB_NAME']) ? $_ENV['DB_NAME'] : 'tickets';

try {
  $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
  ]);
} catch (PDOException $e) {
  die("Verbindung zur Datenbank fehlgeschlagen: " . $e->getMessage());
}

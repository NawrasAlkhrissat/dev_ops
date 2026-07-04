<?php
// app/index.php

require_once 'db.php';

// Fetch tickets from database
$stmt = $pdo->query("SELECT id, name, price, available FROM tickets");
$tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="de">
<head>
  <meta charset="UTF-8" />
  <title>Konzerttickets - Ticketshop</title>
  <link rel="stylesheet" href="/css/style.css" />
</head>
<body>
<div class="container">
  <h1>Konzerttickets 2026</h1>
  <p>Wählen Sie Ihr Ticket aus:</p>

  <ul class="ticket-list">
    <?php foreach ($tickets as $ticket): ?>
      <li class="ticket-item">
        <strong><?= htmlspecialchars($ticket['name']) ?></strong> –
        <?= number_format($ticket['price'], 2) ?> €
        <?php if ($ticket['available'] > 0): ?>
          <span class="available">✓ Verfügbar (<?= $ticket['available'] ?>)</span>
        <?php else: ?>
          <span class="unavailable">✗ Ausverkauft</span>
        <?php endif; ?>
      </li>
    <?php endforeach; ?>
  </ul>

  <p class="footer">Ticketshop – Powered by PHP & MySQL</p>
</div>
</body>
</html>

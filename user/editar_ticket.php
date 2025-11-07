<?php
include '../config/database.php';
include '../includes/auth.php';
redirectIfNotLoggedIn();

echo "<div class='container mt-4'>";
echo "<div class='card'>";
echo "<div class='card-body text-center py-5'>";
echo "<h3><i class='fas fa-tools'></i> Función en Desarrollo</h3>";
echo "<p class='text-muted'>La edición de tickets estará disponible próximamente.</p>";
echo "<a href='mis_tickets.php' class='btn btn-primary mt-3'>Volver a Mis Tickets</a>";
echo "</div>";
echo "</div>";
echo "</div>";
?>
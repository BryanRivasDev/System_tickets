<?php
include '../config/database.php';
include '../includes/auth.php';
redirectIfNotLoggedIn();
redirectIfNotTecnico();

echo "<div class='container mt-4'>";
echo "<h2>Gestionar Ticket</h2>";
echo "<p>Esta funcionalidad estará disponible pronto.</p>";
echo "<a href='dashboard.php' class='btn btn-primary'>Volver al Dashboard</a>";
echo "</div>";
?>
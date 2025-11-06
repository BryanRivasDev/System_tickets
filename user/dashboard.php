<?php
include '../config/database.php';
include '../includes/auth.php';
redirectIfNotLoggedIn();

// Obtener tickets del usuario
$user_id = $_SESSION['user_id'];
$tickets = $pdo->prepare("SELECT t.*, e.nombre as estado, c.nombre as categoria 
                         FROM tickets t 
                         JOIN estados_ticket e ON t.estado_id = e.id 
                         JOIN categorias_tickets c ON t.categoria_id = c.id 
                         WHERE t.usuario_id = ? 
                         ORDER BY t.fecha_creacion DESC");
$tickets->execute([$user_id]);
$tickets = $tickets->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Dashboard Usuario</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>
                <i class="fas fa-tachometer-alt"></i> Mi Dashboard
                <small class="text-muted">Bienvenido, <?php echo $_SESSION['user_name']; ?></small>
            </h2>
            <a href="../logout.php" class="btn btn-outline-danger btn-sm" 
               onclick="return confirm('¿Estás seguro de que quieres cerrar sesión?')">
                <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
            </a>
        </div>
        
        <div class="mb-3">
            <a href="crear_ticket.php" class="btn btn-primary">
                <i class="fas fa-plus-circle"></i> Nuevo Ticket
            </a>
        </div>

        <div class="card">
            <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-ticket-alt"></i> Mis Tickets</h5>
                <span class="badge bg-light text-dark"><?php echo count($tickets); ?> tickets</span>
            </div>
            <div class="card-body">
                <?php if (count($tickets) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Título</th>
                                <th>Categoría</th>
                                <th>Estado</th>
                                <th>Fecha</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($tickets as $ticket): ?>
                            <tr>
                                <td>#<?php echo $ticket['id']; ?></td>
                                <td><?php echo htmlspecialchars($ticket['titulo']); ?></td>
                                <td><?php echo $ticket['categoria']; ?></td>
                                <td>
                                    <span class="badge bg-<?php 
                                        switch($ticket['estado']) {
                                            case 'Nuevo': echo 'primary'; break;
                                            case 'Asignado': echo 'warning'; break;
                                            case 'Resuelto': echo 'success'; break;
                                            default: echo 'secondary';
                                        }
                                    ?>"><?php echo $ticket['estado']; ?></span>
                                </td>
                                <td><?php echo date('d/m/Y H:i', strtotime($ticket['fecha_creacion'])); ?></td>
                                <td>
                                    <a href="ver_ticket.php?id=<?php echo $ticket['id']; ?>" class="btn btn-sm btn-info">
                                        <i class="fas fa-eye"></i> Ver
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <div class="text-center py-4">
                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                    <p class="text-muted">No tienes tickets creados.</p>
                    <a href="crear_ticket.php" class="btn btn-primary">
                        <i class="fas fa-plus-circle"></i> Crear mi primer ticket
                    </a>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
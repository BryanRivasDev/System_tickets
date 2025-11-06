<?php
include '../config/database.php';
include '../includes/auth.php';
redirectIfNotLoggedIn();
redirectIfNotTecnico();

$tecnico_id = $_SESSION['user_id'];

// Obtener todos los tickets del técnico
$tickets = $pdo->prepare("SELECT t.*, u.nombre as usuario_nombre, c.nombre as categoria, e.nombre as estado 
                         FROM tickets t 
                         JOIN usuarios u ON t.usuario_id = u.id 
                         JOIN categorias_tickets c ON t.categoria_id = c.id 
                         JOIN estados_ticket e ON t.estado_id = e.id 
                         WHERE t.tecnico_asignado = ? 
                         ORDER BY 
                           CASE t.prioridad 
                             WHEN 'Urgente' THEN 1 
                             WHEN 'Alta' THEN 2 
                             WHEN 'Media' THEN 3 
                             WHEN 'Baja' THEN 4 
                           END,
                           t.fecha_creacion DESC");
$tickets->execute([$tecnico_id]);
$tickets = $tickets->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Mis Tickets - Técnico</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="container mt-4">
        <h2><i class="fas fa-tasks"></i> Mis Tickets Asignados</h2>
        
        <div class="card">
            <div class="card-header bg-warning text-dark">
                <h5 class="mb-0">Lista de Tickets</h5>
            </div>
            <div class="card-body">
                <?php if (count($tickets) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Título</th>
                                <th>Usuario</th>
                                <th>Categoría</th>
                                <th>Prioridad</th>
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
                                <td><?php echo htmlspecialchars($ticket['usuario_nombre']); ?></td>
                                <td><?php echo $ticket['categoria']; ?></td>
                                <td>
                                    <span class="badge bg-<?php 
                                        switch($ticket['prioridad']) {
                                            case 'Urgente': echo 'danger'; break;
                                            case 'Alta': echo 'warning'; break;
                                            case 'Media': echo 'info'; break;
                                            case 'Baja': echo 'success'; break;
                                        }
                                    ?>"><?php echo $ticket['prioridad']; ?></span>
                                </td>
                                <td>
                                    <span class="badge bg-<?php 
                                        switch($ticket['estado']) {
                                            case 'Nuevo': echo 'primary'; break;
                                            case 'Asignado': echo 'warning'; break;
                                            case 'En Progreso': echo 'info'; break;
                                            case 'Resuelto': echo 'success'; break;
                                            default: echo 'secondary';
                                        }
                                    ?>"><?php echo $ticket['estado']; ?></span>
                                </td>
                                <td><?php echo date('d/m/Y', strtotime($ticket['fecha_creacion'])); ?></td>
                                <td>
                                    <a href="gestionar_ticket.php?id=<?php echo $ticket['id']; ?>" 
                                       class="btn btn-sm btn-primary">
                                        <i class="fas fa-edit"></i> Gestionar
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
                    <p class="text-muted">No tienes tickets asignados</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
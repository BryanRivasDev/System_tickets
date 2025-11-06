<?php
include '../config/database.php';
include '../includes/auth.php';
redirectIfNotLoggedIn();
redirectIfNotTecnico();

$tecnico_id = $_SESSION['user_id'];

// Obtener estadísticas para técnico - CORREGIDO
$stmt = $pdo->prepare("SELECT COUNT(*) FROM tickets WHERE tecnico_asignado = ? AND estado_id = 2");
$stmt->execute([$tecnico_id]);
$asignados = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM tickets WHERE tecnico_asignado = ? AND estado_id = 3");
$stmt->execute([$tecnico_id]);
$en_progreso = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM tickets WHERE tecnico_asignado = ? AND estado_id = 4");
$stmt->execute([$tecnico_id]);
$resueltos = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM tickets WHERE tecnico_asignado = ?");
$stmt->execute([$tecnico_id]);
$total = $stmt->fetchColumn();

// Tickets asignados al técnico
$tickets_asignados = $pdo->prepare("SELECT t.*, u.nombre as usuario_nombre, c.nombre as categoria, e.nombre as estado 
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
                                     t.fecha_creacion DESC 
                                   LIMIT 10");
$tickets_asignados->execute([$tecnico_id]);
$tickets_asignados = $tickets_asignados->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Dashboard Técnico</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>
                <i class="fas fa-tachometer-alt"></i> 
                Dashboard Técnico
                <small class="text-muted">Bienvenido, <?php echo $_SESSION['user_name']; ?></small>
            </h2>
            <span class="badge bg-warning fs-6">
                <i class="fas fa-tools"></i> <?php echo $_SESSION['rol']; ?>
            </span>
        </div>
        
        <!-- Estadísticas para Técnico -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card text-white bg-warning">
                    <div class="card-body text-center">
                        <h3><?php echo $asignados; ?></h3>
                        <p><i class="fas fa-user-check"></i> Asignados</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-white bg-info">
                    <div class="card-body text-center">
                        <h3><?php echo $en_progreso; ?></h3>
                        <p><i class="fas fa-spinner"></i> En Progreso</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-white bg-success">
                    <div class="card-body text-center">
                        <h3><?php echo $resueltos; ?></h3>
                        <p><i class="fas fa-check-circle"></i> Resueltos</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-white bg-secondary">
                    <div class="card-body text-center">
                        <h3><?php echo $total; ?></h3>
                        <p><i class="fas fa-ticket-alt"></i> Total</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tickets Asignados al Técnico -->
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header bg-warning text-dark">
                        <h5><i class="fas fa-tasks"></i> Mis Tickets Asignados</h5>
                    </div>
                    <div class="card-body">
                        <?php if (count($tickets_asignados) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
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
                                    <?php foreach ($tickets_asignados as $ticket): ?>
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
                            <p class="text-muted small">Los administradores te asignarán tickets próximamente</p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Acciones Rápidas para Técnico -->
        <div class="row mt-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header bg-info text-white">
                        <h5><i class="fas fa-bolt"></i> Acciones Rápidas - Técnico</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <a href="mis_tickets.php" class="btn btn-outline-primary w-100 mb-2">
                                    <i class="fas fa-list"></i> Ver Todos Mis Tickets
                                </a>
                            </div>
                            <div class="col-md-4">
                                <a href="../user/crear_ticket.php" class="btn btn-outline-success w-100 mb-2">
                                    <i class="fas fa-plus"></i> Crear Ticket
                                </a>
                            </div>
                            <div class="col-md-4">
                                <a href="../user/profile.php" class="btn btn-outline-warning w-100 mb-2">
                                    <i class="fas fa-user-cog"></i> Mi Perfil
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
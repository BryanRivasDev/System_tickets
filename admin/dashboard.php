<?php
include '../config/database.php';
include '../includes/auth.php';
redirectIfNotLoggedIn();
if (!isAdmin() && !isTecnico()) {
    header("Location: ../user/dashboard.php");
    exit();
}

// Obtener estadísticas (código anterior se mantiene igual...)
$nuevos = $pdo->query("SELECT COUNT(*) FROM tickets WHERE estado_id = 1")->fetchColumn();
$asignados = $pdo->query("SELECT COUNT(*) FROM tickets WHERE estado_id = 2")->fetchColumn();
$en_progreso = $pdo->query("SELECT COUNT(*) FROM tickets WHERE estado_id = 3")->fetchColumn();
$resueltos = $pdo->query("SELECT COUNT(*) FROM tickets WHERE estado_id = 4")->fetchColumn();
$total = $pdo->query("SELECT COUNT(*) FROM tickets")->fetchColumn();

$prioridades = $pdo->query("SELECT prioridad, COUNT(*) as cantidad FROM tickets GROUP BY prioridad")->fetchAll();

$tickets = $pdo->query("SELECT t.*, u.nombre as usuario_nombre, e.nombre as estado, c.nombre as categoria 
                        FROM tickets t 
                        JOIN usuarios u ON t.usuario_id = u.id 
                        JOIN estados_ticket e ON t.estado_id = e.id 
                        JOIN categorias_tickets c ON t.categoria_id = c.id
                        ORDER BY t.fecha_creacion DESC LIMIT 10")->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Dashboard Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .logout-btn {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 1000;
        }
    </style>
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>
                <i class="fas fa-tachometer-alt"></i> 
                Dashboard Administrativo
                <small class="text-muted">Bienvenido, <?php echo $_SESSION['user_name']; ?></small>
            </h2>
            <div>
                <span class="badge bg-<?php echo isAdmin() ? 'danger' : 'warning'; ?> fs-6 me-2">
                    <i class="fas fa-user-shield"></i> <?php echo $_SESSION['rol']; ?>
                </span>
                <!-- Botón de cerrar sesión en el header del dashboard -->
                <a href="../logout.php" class="btn btn-outline-danger btn-sm" 
                   onclick="return confirm('¿Estás seguro de que quieres cerrar sesión?')">
                    <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
                </a>
            </div>
        </div>
        
        <!-- Estadísticas -->
        <div class="row mb-4">
            <div class="col-md-2">
                <div class="card text-white bg-primary">
                    <div class="card-body text-center">
                        <h3><?php echo $nuevos; ?></h3>
                        <p><i class="fas fa-plus-circle"></i> Nuevos</p>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card text-white bg-warning">
                    <div class="card-body text-center">
                        <h3><?php echo $asignados; ?></h3>
                        <p><i class="fas fa-user-check"></i> Asignados</p>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card text-white bg-info">
                    <div class="card-body text-center">
                        <h3><?php echo $en_progreso; ?></h3>
                        <p><i class="fas fa-spinner"></i> En Progreso</p>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card text-white bg-success">
                    <div class="card-body text-center">
                        <h3><?php echo $resueltos; ?></h3>
                        <p><i class="fas fa-check-circle"></i> Resueltos</p>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card text-white bg-secondary">
                    <div class="card-body text-center">
                        <h3><?php echo $total; ?></h3>
                        <p><i class="fas fa-ticket-alt"></i> Total</p>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card text-white bg-dark">
                    <div class="card-body text-center">
                        <h3>
                            <?php 
                            $tecnicos = $pdo->query("SELECT COUNT(*) FROM usuarios WHERE rol_id IN (1,2) AND activo = 1")->fetchColumn();
                            echo $tecnicos;
                            ?>
                        </h3>
                        <p><i class="fas fa-users"></i> Staff IT</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Tickets Recientes -->
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="fas fa-clock"></i> Tickets Recientes</h5>
                        <a href="../logout.php" class="btn btn-outline-light btn-sm">
                            <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
                        </a>
                    </div>
                    <div class="card-body">
                        <?php if (count($tickets) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Título</th>
                                        <th>Usuario</th>
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
                                        <td><?php echo htmlspecialchars($ticket['usuario_nombre']); ?></td>
                                        <td>
                                            <span class="badge bg-light text-dark"><?php echo $ticket['categoria']; ?></span>
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
                                        <td><?php echo date('d/m/Y H:i', strtotime($ticket['fecha_creacion'])); ?></td>
                                        <td>
                                            <a href="asignar_ticket.php?id=<?php echo $ticket['id']; ?>" 
                                               class="btn btn-sm btn-outline-primary" 
                                               title="Gestionar ticket">
                                                <i class="fas fa-edit"></i>
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
                            <p class="text-muted">No hay tickets en el sistema</p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Panel de Prioridades y Acciones Rápidas -->
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header bg-warning text-dark">
                        <h5><i class="fas fa-exclamation-triangle"></i> Prioridades</h5>
                    </div>
                    <div class="card-body">
                        <?php foreach ($prioridades as $prioridad): ?>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span>
                                <?php 
                                $icon = '';
                                $color = '';
                                switch($prioridad['prioridad']) {
                                    case 'Urgente': 
                                        $icon = 'fa-fire'; 
                                        $color = 'danger';
                                        break;
                                    case 'Alta': 
                                        $icon = 'fa-arrow-up'; 
                                        $color = 'warning';
                                        break;
                                    case 'Media': 
                                        $icon = 'fa-minus'; 
                                        $color = 'info';
                                        break;
                                    case 'Baja': 
                                        $icon = 'fa-arrow-down'; 
                                        $color = 'success';
                                        break;
                                }
                                ?>
                                <i class="fas <?php echo $icon; ?> text-<?php echo $color; ?>"></i>
                                <?php echo $prioridad['prioridad']; ?>
                            </span>
                            <span class="badge bg-<?php echo $color; ?>"><?php echo $prioridad['cantidad']; ?></span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Acciones Rápidas -->
                <div class="card mt-3">
                    <div class="card-header bg-success text-white">
                        <h5><i class="fas fa-bolt"></i> Acciones Rápidas</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="tickets.php" class="btn btn-outline-primary">
                                <i class="fas fa-list"></i> Ver Todos los Tickets
                            </a>
                            <?php if (isAdmin()): ?>
                            <a href="usuarios.php" class="btn btn-outline-success">
                                <i class="fas fa-users"></i> Gestionar Usuarios
                            </a>
                            <?php endif; ?>
                            <a href="../user/crear_ticket.php" class="btn btn-outline-info">
                                <i class="fas fa-plus"></i> Crear Ticket
                            </a>
                            <!-- Botón de cerrar sesión en acciones rápidas -->
                            <a href="../logout.php" class="btn btn-outline-danger mt-3" 
                               onclick="return confirm('¿Estás seguro de que quieres cerrar sesión?')">
                                <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Botón flotante de cerrar sesión -->
    <a href="../logout.php" class="btn btn-danger logout-btn rounded-pill shadow" 
       onclick="return confirm('¿Estás seguro de que quieres cerrar sesión?')"
       title="Cerrar Sesión">
        <i class="fas fa-sign-out-alt"></i>
    </a>
</body>
</html>
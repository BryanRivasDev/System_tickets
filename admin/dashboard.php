<?php
include '../config/database.php';
include '../includes/auth.php';
redirectIfNotLoggedIn();
redirectIfNotAdmin();

// Obtener estadísticas para admin
$nuevos = $pdo->query("SELECT COUNT(*) FROM tickets WHERE estado_id = 1")->fetchColumn();
$asignados = $pdo->query("SELECT COUNT(*) FROM tickets WHERE estado_id = 2")->fetchColumn();
$en_progreso = $pdo->query("SELECT COUNT(*) FROM tickets WHERE estado_id = 3")->fetchColumn();
$resueltos = $pdo->query("SELECT COUNT(*) FROM tickets WHERE estado_id = 4")->fetchColumn();
$total_tecnicos = $pdo->query("SELECT COUNT(*) FROM usuarios WHERE rol_id IN (1,2) AND activo = 1")->fetchColumn();
$total_usuarios = $pdo->query("SELECT COUNT(*) FROM usuarios WHERE rol_id = 3 AND activo = 1")->fetchColumn();

// Tickets nuevos sin asignar
$tickets_nuevos = $pdo->query("SELECT t.*, u.nombre as usuario_nombre, c.nombre as categoria 
                              FROM tickets t 
                              JOIN usuarios u ON t.usuario_id = u.id 
                              JOIN categorias_tickets c ON t.categoria_id = c.id 
                              WHERE t.estado_id = 1 
                              ORDER BY t.fecha_creacion DESC LIMIT 10")->fetchAll();

// Tickets asignados recientemente
$tickets_asignados = $pdo->query("SELECT t.*, u.nombre as usuario_nombre, u2.nombre as tecnico_nombre, c.nombre as categoria 
                                 FROM tickets t 
                                 JOIN usuarios u ON t.usuario_id = u.id 
                                 LEFT JOIN usuarios u2 ON t.tecnico_asignado = u2.id 
                                 JOIN categorias_tickets c ON t.categoria_id = c.id 
                                 WHERE t.estado_id = 2 
                                 ORDER BY t.fecha_actualizacion DESC LIMIT 10")->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Dashboard Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>
                <i class="fas fa-tachometer-alt"></i> 
                Dashboard Administrador
                <small class="text-muted">Bienvenido, <?php echo $_SESSION['user_name']; ?></small>
            </h2>
            <span class="badge bg-danger fs-6">
                <i class="fas fa-user-shield"></i> <?php echo $_SESSION['rol']; ?>
            </span>
        </div>
        
        <!-- Estadísticas para Admin -->
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
                        <h3><?php echo $total_tecnicos; ?></h3>
                        <p><i class="fas fa-users"></i> Técnicos</p>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card text-white bg-dark">
                    <div class="card-body text-center">
                        <h3><?php echo $total_usuarios; ?></h3>
                        <p><i class="fas fa-user-friends"></i> Usuarios</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Tickets Nuevos Sin Asignar -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5><i class="fas fa-clock"></i> Tickets Nuevos Sin Asignar</h5>
                    </div>
                    <div class="card-body">
                        <?php if (count($tickets_nuevos) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-sm table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Título</th>
                                        <th>Usuario</th>
                                        <th>Acción</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($tickets_nuevos as $ticket): ?>
                                    <tr>
                                        <td>#<?php echo $ticket['id']; ?></td>
                                        <td><?php echo htmlspecialchars($ticket['titulo']); ?></td>
                                        <td><?php echo htmlspecialchars($ticket['usuario_nombre']); ?></td>
                                        <td>
                                            <a href="asignar_ticket.php?id=<?php echo $ticket['id']; ?>" 
                                               class="btn btn-sm btn-success">
                                                <i class="fas fa-user-check"></i> Asignar
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php else: ?>
                        <div class="text-center py-3">
                            <i class="fas fa-check-circle fa-2x text-success mb-2"></i>
                            <p class="text-muted">No hay tickets nuevos sin asignar</p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Tickets Asignados Recientemente -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-warning text-dark">
                        <h5><i class="fas fa-user-check"></i> Tickets Asignados Recientemente</h5>
                    </div>
                    <div class="card-body">
                        <?php if (count($tickets_asignados) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-sm table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Título</th>
                                        <th>Técnico</th>
                                        <th>Acción</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($tickets_asignados as $ticket): ?>
                                    <tr>
                                        <td>#<?php echo $ticket['id']; ?></td>
                                        <td><?php echo htmlspecialchars($ticket['titulo']); ?></td>
                                        <td>
                                            <?php if ($ticket['tecnico_nombre']): ?>
                                                <?php echo htmlspecialchars($ticket['tecnico_nombre']); ?>
                                            <?php else: ?>
                                                <span class="text-muted">Sin asignar</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <a href="asignar_ticket.php?id=<?php echo $ticket['id']; ?>" 
                                               class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-edit"></i> Reasignar
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php else: ?>
                        <div class="text-center py-3">
                            <i class="fas fa-user-check fa-2x text-muted mb-2"></i>
                            <p class="text-muted">No hay tickets asignados recientemente</p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Acciones Rápidas para Admin -->
        <div class="row mt-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h5><i class="fas fa-bolt"></i> Acciones Rápidas - Administrador</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3">
                                <a href="tickets.php" class="btn btn-outline-primary w-100 mb-2">
                                    <i class="fas fa-list"></i> Todos los Tickets
                                </a>
                            </div>
                            <div class="col-md-3">
                                <a href="usuarios.php" class="btn btn-outline-success w-100 mb-2">
                                    <i class="fas fa-users"></i> Gestionar Usuarios
                                </a>
                            </div>
                            <div class="col-md-3">
                                <a href="../user/crear_ticket.php" class="btn btn-outline-info w-100 mb-2">
                                    <i class="fas fa-plus"></i> Crear Ticket
                                </a>
                            </div>
                            <div class="col-md-3">
                                <a href="cambiar_rol.php" class="btn btn-outline-warning w-100 mb-2">
                                    <i class="fas fa-user-cog"></i> Cambiar Roles
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
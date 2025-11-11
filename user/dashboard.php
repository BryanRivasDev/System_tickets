<?php
include '../config/database.php';
include '../includes/auth.php';
redirectIfNotLoggedIn();

$user_id = $_SESSION['user_id'];

// Obtener tickets del usuario (solo los más recientes para el dashboard)
$tickets = $pdo->prepare("SELECT t.*, e.nombre as estado, c.nombre as categoria 
                         FROM tickets t 
                         JOIN estados_ticket e ON t.estado_id = e.id 
                         JOIN categorias_tickets c ON t.categoria_id = c.id 
                         WHERE t.usuario_id = ? 
                         ORDER BY t.fecha_creacion DESC 
                         LIMIT 5");
$tickets->execute([$user_id]);
$tickets = $tickets->fetchAll();

// Obtener estadísticas para el dashboard - CORREGIDO
$stmt = $pdo->prepare("SELECT COUNT(*) FROM tickets WHERE usuario_id = ?");
$stmt->execute([$user_id]);
$total_tickets = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM tickets WHERE usuario_id = ? AND estado_id IN (1,2,3)");
$stmt->execute([$user_id]);
$tickets_abiertos = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM tickets WHERE usuario_id = ? AND estado_id = 4");
$stmt->execute([$user_id]);
$tickets_resueltos = $stmt->fetchColumn();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Dashboard Usuario</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --dark-bg: #0f172a;
            --dark-card: #1e293b;
            --dark-card-light: #2d3748;
            --dark-border: #374151;
            --primary: #3b82f6;
            --primary-hover: #2563eb;
            --text-light: #f8fafc;
            --text-muted: #94a3b8;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
            --info: #06b6d4;
        }
        
        body {
            background-color: var(--dark-bg);
            color: var(--text-light);
            font-family: 'Inter', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
        }
        
        .card {
            background: var(--dark-card);
            border: 1px solid var(--dark-border);
            border-radius: 12px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            backdrop-filter: blur(10px);
        }
        
        .card-header {
            background: linear-gradient(135deg, var(--dark-card) 0%, var(--dark-card-light) 100%) !important;
            border-bottom: 1px solid var(--dark-border);
            color: var(--text-light);
            border-radius: 12px 12px 0 0 !important;
            padding: 1.25rem 1.5rem;
        }
        
        .stat-card {
            background: linear-gradient(135deg, var(--dark-card) 0%, var(--dark-card-light) 100%);
            border: 1px solid var(--dark-border);
            border-radius: 12px;
            padding: 1.5rem;
            text-align: center;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, var(--primary), var(--info));
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.2);
        }
        
        .stat-number {
            font-size: 2.5rem;
            font-weight: 800;
            margin-bottom: 0.5rem;
            background: linear-gradient(135deg, var(--text-light), var(--primary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .stat-text {
            color: var(--text-muted);
            font-size: 0.9rem;
            font-weight: 500;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-hover) 100%);
            border: none;
            border-radius: 8px;
            font-weight: 600;
            padding: 0.75rem 1.5rem;
            transition: all 0.3s ease;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(59, 130, 246, 0.3);
        }
        
        .btn-outline-primary {
            color: var(--primary);
            border: 2px solid var(--primary);
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-outline-primary:hover {
            background: var(--primary);
            border-color: var(--primary);
            color: white;
            transform: translateY(-1px);
        }
        
        .table {
            color: var(--text-light);
            border-radius: 8px;
            overflow: hidden;
        }
        
        .table > :not(caption) > * > * {
            background: transparent;
            border-bottom-color: var(--dark-border);
            padding: 1rem 0.75rem;
        }
        
        .table-striped > tbody > tr:nth-of-type(odd) {
            background-color: rgba(30, 41, 59, 0.3);
        }
        
        .table-hover > tbody > tr:hover {
            background-color: rgba(59, 130, 246, 0.1);
            transform: scale(1.01);
            transition: all 0.2s ease;
        }
        
        .badge {
            font-weight: 600;
            padding: 0.5em 0.75em;
            border-radius: 6px;
            font-size: 0.75em;
        }
        
        .action-btn {
            padding: 0.4rem 0.8rem;
            border-radius: 6px;
            font-size: 0.875rem;
            transition: all 0.2s ease;
        }
        
        .action-btn:hover {
            transform: translateY(-1px);
        }
        
        .empty-state {
            text-align: center;
            padding: 3rem 1rem;
            color: var(--text-muted);
        }
        
        .empty-state i {
            font-size: 4rem;
            margin-bottom: 1rem;
            opacity: 0.5;
            background: linear-gradient(135deg, var(--text-muted), var(--primary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .welcome-header {
            background: linear-gradient(135deg, var(--dark-card) 0%, var(--dark-card-light) 100%);
            border-radius: 12px;
            padding: 2rem;
            margin-bottom: 2rem;
            border: 1px solid var(--dark-border);
        }
    </style>
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="container mt-4">
        <!-- Header de Bienvenida -->
        <div class="welcome-header">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1 class="display-6 fw-bold mb-2">
                        <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                    </h1>
                    <p class="lead text-muted mb-0">Bienvenido de vuelta, <?php echo $_SESSION['user_name']; ?></p>
                </div>
                <div class="col-md-4 text-end">
                    <span class="badge bg-info fs-6 px-3 py-2">
                        <i class="fas fa-user me-1"></i> <?php echo $_SESSION['rol']; ?>
                    </span>
                </div>
            </div>
        </div>

        <!-- Botones Principales -->
<div class="d-flex gap-3 mb-4 flex-wrap">
    <a href="crear_ticket.php" class="btn btn-primary">
        <i class="fas fa-plus-circle me-2"></i> Nuevo Ticket
    </a>
    <a href="mis_tickets.php" class="btn btn-outline-primary">
        <i class="fas fa-list me-2"></i> Ver Todos Mis Tickets
    </a>
    <!-- ELIMINAR ESTE BOTÓN -->
    <!-- <a href="profile.php" class="btn btn-outline-info">
        <i class="fas fa-user-cog me-2"></i> Mi Perfil
    </a> -->
</div>

        <!-- Estadísticas -->
        <div class="row mb-4 g-3">
            <div class="col-md-4">
                <div class="stat-card">
                    <div class="stat-number"><?php echo $total_tickets; ?></div>
                    <div class="stat-text">Total de Tickets</div>
                    <i class="fas fa-ticket-alt position-absolute" style="bottom: 1rem; right: 1rem; opacity: 0.1; font-size: 3rem;"></i>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card">
                    <div class="stat-number text-warning"><?php echo $tickets_abiertos; ?></div>
                    <div class="stat-text">Tickets Abiertos</div>
                    <i class="fas fa-clock position-absolute" style="bottom: 1rem; right: 1rem; opacity: 0.1; font-size: 3rem;"></i>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card">
                    <div class="stat-number text-success"><?php echo $tickets_resueltos; ?></div>
                    <div class="stat-text">Tickets Resueltos</div>
                    <i class="fas fa-check-circle position-absolute" style="bottom: 1rem; right: 1rem; opacity: 0.1; font-size: 3rem;"></i>
                </div>
            </div>
        </div>

        <!-- Tickets Recientes -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold">
                    <i class="fas fa-clock me-2"></i> Tickets Recientes
                </h5>
                <a href="mis_tickets.php" class="btn btn-sm btn-outline-primary">
                    Ver Todos <i class="fas fa-arrow-right ms-1"></i>
                </a>
            </div>
            <div class="card-body p-0">
                <?php if (count($tickets) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover mb-0">
                        <thead>
                            <tr>
                                <th class="ps-4">ID</th>
                                <th>Título</th>
                                <th>Categoría</th>
                                <th>Estado</th>
                                <th>Prioridad</th>
                                <th>Fecha</th>
                                <th class="text-center pe-4">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($tickets as $ticket): ?>
                            <tr>
                                <td class="ps-4 fw-bold">#<?php echo $ticket['id']; ?></td>
                                <td>
                                    <div class="fw-semibold"><?php echo htmlspecialchars($ticket['titulo']); ?></div>
                                    <small class="text-muted">
                                        <?php echo substr(htmlspecialchars($ticket['descripcion']), 0, 60); ?>...
                                    </small>
                                </td>
                                <td>
                                    <span class="badge bg-dark"><?php echo $ticket['categoria']; ?></span>
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
                                    ?>">
                                        <i class="fas fa-<?php 
                                            switch($ticket['estado']) {
                                                case 'Nuevo': echo 'plus'; break;
                                                case 'Asignado': echo 'user-check'; break;
                                                case 'En Progreso': echo 'spinner'; break;
                                                case 'Resuelto': echo 'check'; break;
                                                default: echo 'circle';
                                            }
                                        ?> me-1"></i>
                                        <?php echo $ticket['estado']; ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-<?php 
                                        switch($ticket['prioridad']) {
                                            case 'Urgente': echo 'danger'; break;
                                            case 'Alta': echo 'warning'; break;
                                            case 'Media': echo 'info'; break;
                                            case 'Baja': echo 'success'; break;
                                        }
                                    ?>">
                                        <?php echo $ticket['prioridad']; ?>
                                    </span>
                                </td>
                                <td>
                                    <small class="text-muted">
                                        <?php echo date('d/m/Y', strtotime($ticket['fecha_creacion'])); ?>
                                    </small>
                                </td>
                                <td class="text-center pe-4">
                                    <div class="btn-group btn-group-sm">
                                        <!-- Botón VER -->
                                        <a href="ver_ticket.php?id=<?php echo $ticket['id']; ?>" 
                                           class="btn btn-outline-info action-btn"
                                           title="Ver detalles del ticket">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <!-- Botón EDITAR (solo si está en estado Nuevo) -->
                                        <?php if ($ticket['estado_id'] == 1): ?>
                                        <a href="editar_ticket.php?id=<?php echo $ticket['id']; ?>" 
                                           class="btn btn-outline-warning action-btn"
                                           title="Editar ticket">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <?php else: ?>
                                        <button class="btn btn-outline-secondary action-btn" disabled
                                                title="Solo se pueden editar tickets nuevos">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-inbox"></i>
                    <h4>No tienes tickets creados</h4>
                    <p class="text-muted">Crea tu primer ticket para comenzar a recibir soporte técnico</p>
                    <a href="crear_ticket.php" class="btn btn-primary mt-3">
                        <i class="fas fa-plus-circle"></i> Crear mi primer ticket
                    </a>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php
include '../config/database.php';
include '../includes/auth.php';
redirectIfNotLoggedIn();
redirectIfNotTecnico();

$tecnico_id = $_SESSION['user_id'];

// Obtener estadísticas para técnico
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
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Técnico - Sistema de Tickets IT</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary: #1a2a6c;
            --secondary: #3a4a9c;
            --success: #28a745;
            --warning: #ffc107;
            --danger: #dc3545;
            --light: #f8f9fa;
            --dark: #343a40;
        }
        
        body {
            background-color: #f5f5f5;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .dashboard-container {
            min-height: calc(100vh - 76px);
        }
        
        .stat-card {
            border-radius: 10px;
            border-left: 4px solid;
            transition: transform 0.3s;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
        }
        
        .stat-card h3 {
            font-size: 1.8rem;
            margin-bottom: 0;
        }
        
        .stat-card.bg-warning {
            border-left-color: #e0a800;
        }
        
        .stat-card.bg-info {
            border-left-color: #117a8b;
        }
        
        .stat-card.bg-success {
            border-left-color: #1e7e34;
        }
        
        .stat-card.bg-secondary {
            border-left-color: #545b62;
        }
        
        .table-hover tbody tr:hover {
            background-color: rgba(26, 42, 108, 0.05);
        }
        
        .badge-priority {
            font-size: 0.75em;
        }
        
        .action-btn {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
        }
        
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
        }
        
        .card-header {
            border-radius: 10px 10px 0 0 !important;
            font-weight: 600;
        }
        
        .btn-outline-primary {
            border-color: var(--primary);
            color: var(--primary);
        }
        
        .btn-outline-primary:hover {
            background-color: var(--primary);
            color: white;
        }
        
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--primary);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
        }
        
        .nav-tabs .nav-link.active {
            background-color: var(--primary);
            color: white;
            border-color: var(--primary);
        }
        
        .nav-tabs .nav-link {
            color: var(--primary);
        }
    </style>
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="container-fluid dashboard-container">
        <div class="row">
            <!-- Main Content -->
            <div class="col-12 px-4 py-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h2 class="h3 mb-1 text-primary">
                            <i class="fas fa-tachometer-alt"></i> Dashboard Técnico
                        </h2>
                        <p class="text-muted mb-0">Bienvenido, <?php echo $_SESSION['user_name']; ?></p>
                    </div>
                    <div class="d-flex align-items-center">
                        <div class="user-avatar me-2">
                            <?php echo strtoupper(substr($_SESSION['user_name'], 0, 2)); ?>
                        </div>
                        <span class="badge bg-warning fs-6">
                            <i class="fas fa-tools"></i> <?php echo $_SESSION['rol']; ?>
                        </span>
                    </div>
                </div>
                
                <!-- Navegación rápida -->
                <div class="row mb-4">
                    <div class="col-12">
                        <ul class="nav nav-tabs">
                           <!--  <li class="nav-item">
                                <a class="nav-link active" href="#">
                                    <i class="fas fa-tachometer-alt me-1"></i> Dashboard
                                </a>
                            </li> -->
<!--                             <li class="nav-item">
                                <a class="nav-link" href="mis_tickets.php">
                                    <i class="fas fa-ticket-alt me-1"></i> Mis Tickets
                                </a>
                            </li> -->
                          <!--   <li class="nav-item">
                                <a class="nav-link" href="#">
                                    <i class="fas fa-list me-1"></i> Todos los Tickets
                                </a>
                            </li> -->
                           <!--  <li class="nav-item">
                                <a class="nav-link" href="../tecnico/reportes.php">
                                    <i class="fas fa-chart-bar me-1"></i> Reportes
                                </a>
                            </li> -->
                            <!-- <li class="nav-item">
                                <a class="nav-link" href="../user/profile.php">
                                    <i class="fas fa-user-cog me-1"></i> Mi Perfil
                                </a>
                            </li> -->
                        </ul>
                    </div>
                </div>
                
                <!-- Estadísticas para Técnico -->
                <div class="row mb-4">
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card text-white bg-warning stat-card h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h3><?php echo $asignados; ?></h3>
                                        <p class="mb-0"><i class="fas fa-user-check"></i> Asignados</p>
                                    </div>
                                    <div class="align-self-center">
                                        <i class="fas fa-user-check fa-2x opacity-50"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card text-white bg-info stat-card h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h3><?php echo $en_progreso; ?></h3>
                                        <p class="mb-0"><i class="fas fa-spinner"></i> En Progreso</p>
                                    </div>
                                    <div class="align-self-center">
                                        <i class="fas fa-spinner fa-2x opacity-50"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card text-white bg-success stat-card h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h3><?php echo $resueltos; ?></h3>
                                        <p class="mb-0"><i class="fas fa-check-circle"></i> Resueltos</p>
                                    </div>
                                    <div class="align-self-center">
                                        <i class="fas fa-check-circle fa-2x opacity-50"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card text-white bg-secondary stat-card h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h3><?php echo $total; ?></h3>
                                        <p class="mb-0"><i class="fas fa-ticket-alt"></i> Total</p>
                                    </div>
                                    <div class="align-self-center">
                                        <i class="fas fa-ticket-alt fa-2x opacity-50"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tickets Asignados al Técnico -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header bg-warning text-dark d-flex justify-content-between align-items-center">
                                <h5 class="mb-0"><i class="fas fa-tasks"></i> Mis Tickets Asignados</h5>
                                <a href="mis_tickets.php" class="btn btn-sm btn-dark">Ver Todos</a>
                            </div>
                            <div class="card-body">
                                <?php if (count($tickets_asignados) > 0): ?>
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead class="table-light">
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
                                                <td><strong>#<?php echo $ticket['id']; ?></strong></td>
                                                <td><?php echo htmlspecialchars($ticket['titulo']); ?></td>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="user-avatar me-2" style="width: 30px; height: 30px; font-size: 0.8rem;">
                                                            <?php echo strtoupper(substr($ticket['usuario_nombre'], 0, 2)); ?>
                                                        </div>
                                                        <?php echo htmlspecialchars($ticket['usuario_nombre']); ?>
                                                    </div>
                                                </td>
                                                <td><?php echo $ticket['categoria']; ?></td>
                                                <td>
                                                    <span class="badge badge-priority bg-<?php 
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
                                                       class="btn btn-sm btn-primary action-btn">
                                                        <i class="fas fa-edit"></i> Gestionar
                                                    </a>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                                <?php else: ?>
                                <div class="text-center py-5">
                                    <i class="fas fa-inbox fa-4x text-muted mb-3"></i>
                                    <h5 class="text-muted">No tienes tickets asignados</h5>
                                    <p class="text-muted">Los administradores te asignarán tickets próximamente</p>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Acciones Rápidas para Técnico -->
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header bg-primary text-white">
                                <h5 class="mb-0"><i class="fas fa-bolt"></i> Acciones Rápidas</h5>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <a href="mis_tickets.php" class="btn btn-outline-primary w-100 d-flex align-items-center justify-content-center py-3">
                                            <i class="fas fa-list me-2"></i> Ver Todos Mis Tickets
                                        </a>
                                    </div>
                                    <div class="col-md-4">
                                        <a href="../user/crear_ticket.php" class="btn btn-outline-success w-100 d-flex align-items-center justify-content-center py-3">
                                            <i class="fas fa-plus me-2"></i> Crear Ticket
                                        </a>
                                    </div>
                                    <div class="col-md-4">
                                        <a href="../user/profile.php" class="btn btn-outline-warning w-100 d-flex align-items-center justify-content-center py-3">
                                            <i class="fas fa-user-cog me-2"></i> Mi Perfil
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Simple script para mejorar la interactividad
        document.addEventListener('DOMContentLoaded', function() {
            // Agregar efecto hover a las tarjetas de estadísticas
            const statCards = document.querySelectorAll('.stat-card');
            statCards.forEach(card => {
                card.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateY(-5px)';
                });
                card.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateY(0)';
                });
            });
            
            // Actualizar la hora actual
            function updateTime() {
                const now = new Date();
                const timeString = now.toLocaleTimeString('es-ES', { 
                    hour: '2-digit', 
                    minute: '2-digit',
                    second: '2-digit'
                });
                const dateString = now.toLocaleDateString('es-ES', {
                    weekday: 'long',
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric'
                });
                
                // Si tienes un elemento para mostrar la hora, actualízalo aquí
                // document.getElementById('current-time').textContent = timeString;
                // document.getElementById('current-date').textContent = dateString;
            }
            
            // Actualizar cada segundo
            setInterval(updateTime, 1000);
            updateTime();
        });
    </script>
</body>
</html>
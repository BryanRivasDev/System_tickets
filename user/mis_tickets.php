<?php
include '../config/database.php';
include '../includes/auth.php';
redirectIfNotLoggedIn();

$user_id = $_SESSION['user_id'];

// Obtener todos los tickets del usuario
$tickets = $pdo->prepare("SELECT t.*, e.nombre as estado, c.nombre as categoria, 
                          u_tecnico.nombre as tecnico_nombre
                         FROM tickets t 
                         JOIN estados_ticket e ON t.estado_id = e.id 
                         JOIN categorias_tickets c ON t.categoria_id = c.id 
                         LEFT JOIN usuarios u_tecnico ON t.tecnico_asignado = u_tecnico.id 
                         WHERE t.usuario_id = ? 
                         ORDER BY t.fecha_creacion DESC");
$tickets->execute([$user_id]);
$tickets = $tickets->fetchAll();

// Obtener estadísticas del usuario
$total_tickets = count($tickets);
$tickets_abiertos = 0;
$tickets_resueltos = 0;

foreach ($tickets as $ticket) {
    if ($ticket['estado_id'] == 4) { // Resuelto
        $tickets_resueltos++;
    } else {
        $tickets_abiertos++;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Mis Tickets - Usuario</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --dark-bg: #0f172a;
            --dark-card: #1e293b;
            --dark-border: #334155;
            --primary: #3b82f6;
            --primary-hover: #2563eb;
            --text-light: #f8fafc;
            --text-muted: #b89494ff;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
            --info: #06b6d4;
        }
        
        body {
            background-color: var(--dark-bg);
            color: var(--text-light);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .card {
            background: var(--dark-card);
            border: 1px solid var(--dark-border);
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        .card-header {
            background: rgba(30, 41, 59, 0.8) !important;
            border-bottom: 1px solid var(--dark-border);
            color: var(--text-light);
        }
        
        .table {
            color: var(--text-light);
            margin-bottom: 0;
        }
        
       /*  .table-striped > tbody > tr:nth-of-type(odd) {
            background-color: rgba(30, 41, 59, 0.5);
        } */
        
        .table-hover > tbody > tr:hover {
            background-color: rgba(59, 130, 246, 0.1);
            color: var(--text-light);
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--primary) 0%, #1d4ed8 100%);
            border: none;
            border-radius: 6px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-primary:hover {
            background: linear-gradient(135deg, var(--primary-hover) 0%, #1e40af 100%);
            transform: translateY(-1px);
        }
        
        .btn-outline-primary {
            color: var(--primary);
            border-color: var(--primary);
            border-radius: 6px;
        }
        
        .btn-outline-primary:hover {
            background: var(--primary);
            border-color: var(--primary);
            color: white;
        }
        
        .stat-card {
            background: linear-gradient(135deg, var(--dark-card) 0%, #1e293b 100%);
            border: 1px solid var(--dark-border);
            border-radius: 10px;
            padding: 1.5rem;
            text-align: center;
            transition: transform 0.3s ease;
        }
        
        .stat-card:hover {
            transform: translateY(-2px);
        }
        
        .stat-number {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }
        
        .stat-text {
            color: var(--text-muted);
            font-size: 0.9rem;
        }
        
        .badge {
            font-weight: 600;
            padding: 0.5em 0.75em;
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
        }
    </style>
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
    <h2>
        <i class="fas fa-ticket-alt"></i> 
        Mis Tickets
    </h2>
    <a href="crear_ticket.php" class="btn btn-primary">
        <i class="fas fa-plus-circle"></i> Nuevo Ticket
    </a>
    <!-- NO agregues aquí el botón de perfil -->
</div>

        <!-- Estadísticas del Usuario -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="stat-card">
                    <div class="stat-number text-primary"><?php echo $total_tickets; ?></div>
                    <div class="stat-text">Total de Tickets</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card">
                    <div class="stat-number text-warning"><?php echo $tickets_abiertos; ?></div>
                    <div class="stat-text">Tickets Abiertos</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card">
                    <div class="stat-number text-success"><?php echo $tickets_resueltos; ?></div>
                    <div class="stat-text">Tickets Resueltos</div>
                </div>
            </div>
        </div>

        <!-- Lista de Tickets -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="fas fa-list"></i> Lista de Tickets
                </h5>
                <span class="badge bg-dark"><?php echo $total_tickets; ?> tickets</span>
            </div>
            <div class="card-body">
                <?php if (count($tickets) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Título</th>
                                <th>Categoría</th>
                                <th>Prioridad</th>
                                <th>Estado</th>
                                <th>Técnico Asignado</th>
                                <th>Fecha Creación</th>
                                <th>Última Actualización</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($tickets as $ticket): ?>
                            <tr>
                                <td>
                                    <strong>#<?php echo $ticket['id']; ?></strong>
                                </td>
                                <td>
                                    <div class="fw-bold"><?php echo htmlspecialchars($ticket['titulo']); ?></div>
                                    <small class="text-muted">
                                        <?php echo substr(htmlspecialchars($ticket['descripcion']), 0, 50); ?>...
                                    </small>
                                </td>
                                <td>
                                    <span class="badge bg-secondary"><?php echo $ticket['categoria']; ?></span>
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
                                        <i class="fas fa-<?php 
                                            switch($ticket['prioridad']) {
                                                case 'Urgente': echo 'fire'; break;
                                                case 'Alta': echo 'arrow-up'; break;
                                                case 'Media': echo 'minus'; break;
                                                case 'Baja': echo 'arrow-down'; break;
                                            }
                                        ?> me-1"></i>
                                        <?php echo $ticket['prioridad']; ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-<?php 
                                        switch($ticket['estado']) {
                                            case 'Nuevo': echo 'primary'; break;
                                            case 'Asignado': echo 'warning'; break;
                                            case 'En Progreso': echo 'info'; break;
                                            case 'Resuelto': echo 'success'; break;
                                            case 'Cerrado': echo 'secondary'; break;
                                            default: echo 'dark';
                                        }
                                    ?>">
                                        <?php echo $ticket['estado']; ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($ticket['tecnico_nombre']): ?>
                                        <span class="text-success">
                                            <i class="fas fa-user-check me-1"></i>
                                            <?php echo htmlspecialchars($ticket['tecnico_nombre']); ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted">
                                            <i class="fas fa-user-clock me-1"></i>
                                            Pendiente de asignación
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <small>
                                        <?php echo date('d/m/Y', strtotime($ticket['fecha_creacion'])); ?><br>
                                        <span class="text-muted"><?php echo date('H:i', strtotime($ticket['fecha_creacion'])); ?></span>
                                    </small>
                                </td>
                                <td>
                                    <small>
                                        <?php echo date('d/m/Y', strtotime($ticket['fecha_actualizacion'])); ?><br>
                                        <span class="text-muted"><?php echo date('H:i', strtotime($ticket['fecha_actualizacion'])); ?></span>
                                    </small>
                                </td>
                                <td>
                                    <div class="btn-group">
                                        <a href="ver_ticket.php?id=<?php echo $ticket['id']; ?>" 
                                           class="btn btn-sm btn-outline-primary"
                                           title="Ver detalles del ticket">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <?php if ($ticket['estado_id'] == 1): ?>
                                        <a href="editar_ticket.php?id=<?php echo $ticket['id']; ?>" 
                                           class="btn btn-sm btn-outline-warning"
                                           title="Editar ticket">
                                            <i class="fas fa-edit"></i>
                                        </a>
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

        <!-- Información adicional -->
        <div class="row mt-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header bg-info text-white">
                        <h6 class="mb-0"><i class="fas fa-info-circle"></i> Información sobre los estados</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3">
                                <span class="badge bg-primary me-2">Nuevo</span>
                                <small class="text-muted">Ticket creado, pendiente de revisión</small>
                            </div>
                            <div class="col-md-3">
                                <span class="badge bg-warning me-2">Asignado</span>
                                <small class="text-muted">Asignado a un técnico</small>
                            </div>
                            <div class="col-md-3">
                                <span class="badge bg-info me-2">En Progreso</span>
                                <small class="text-muted">El técnico está trabajando</small>
                            </div>
                            <div class="col-md-3">
                                <span class="badge bg-success me-2">Resuelto</span>
                                <small class="text-muted">Problema solucionado</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
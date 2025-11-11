<?php
include '../config/database.php';
include '../includes/auth.php';
redirectIfNotLoggedIn();

if (!isset($_GET['id'])) {
    header("Location: mis_tickets.php");
    exit();
}

$ticket_id = $_GET['id'];
$user_id = $_SESSION['user_id'];

// Obtener información del ticket
$stmt = $pdo->prepare("SELECT t.*, e.nombre as estado, c.nombre as categoria, 
                              u_tecnico.nombre as tecnico_nombre, u_tecnico.email as tecnico_email
                       FROM tickets t 
                       JOIN estados_ticket e ON t.estado_id = e.id 
                       JOIN categorias_tickets c ON t.categoria_id = c.id 
                       LEFT JOIN usuarios u_tecnico ON t.tecnico_asignado = u_tecnico.id 
                       WHERE t.id = ? AND t.usuario_id = ?");
$stmt->execute([$ticket_id, $user_id]);
$ticket = $stmt->fetch();

if (!$ticket) {
    header("Location: mis_tickets.php");
    exit();
}

// Obtener comentarios del ticket
$comentarios = $pdo->prepare("SELECT c.*, u.nombre as usuario_nombre 
                             FROM comentarios_tickets c 
                             JOIN usuarios u ON c.usuario_id = u.id 
                             WHERE c.ticket_id = ? AND c.es_interno = 0
                             ORDER BY c.created_at ASC");
$comentarios->execute([$ticket_id]);
$comentarios = $comentarios->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Ver Ticket #<?php echo $ticket['id']; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --dark-bg: #0f172a;
            --dark-card: #1e293b;
            --dark-card-light: #2d3748;
            --dark-border: #374151;
            --primary: #3b82f6;
            --text-light: #f8fafc;
            --text-muted: #94a3b8;
            --text-dark: #1f2937;
        }
        
        body {
            background-color: var(--dark-bg);
            color: var(--text-light);
            font-family: 'Inter', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .card {
            background: var(--dark-card);
            border: 10px solid var(--dark-border);
            border-radius: 15px;
            box-shadow: 0 4px 6px -1px rgba(188, 19, 19, 0.1);
        }
        
        .card-header {
            background: linear-gradient(135deg, var(--dark-card) 0%, var(--dark-card-light) 100%) !important;
            border-bottom: 1px solid var(--dark-border);
            border-radius: 12px 12px 0 0 !important;
            padding: 1.5rem;
            color: var(--text-light);
        }
        
        .card-body {
            background: #52597A;
        }
        
        .btn-outline-secondary {
            color: var(--text-muted);
            border-color: var(--dark-border);
        }
        
        .btn-outline-secondary:hover {
            background: var(--dark-border);
            color: var(--text-light);
        }
        
        .badge {
            font-weight: 600;
            padding: 0.5em 0.75em;
            border-radius: 6px;
        }
        
        .ticket-title {
            color: var(--text-light);
            font-weight: 700;
            margin-bottom: 1rem;
        }
        
        .ticket-description {
            color: var(--text-light);
            line-height: 1.6;
            background: #2d3748; 
            padding: 1.5rem;
            border-radius: 8px;
            border-left: 4px solid var(--primary);
        }
        
        .info-section {
            background: var(--dark-card-light);
            padding: 1.5rem;
            border-radius: 8px;
            margin-top: 1.5rem;
        }
        
        .info-label {
            color: var(--text-muted);
            font-weight: 600;
            font-size: 0.9rem;
            margin-bottom: 0.25rem;
        }
        
        .info-value {
            color: var(--text-light);
            font-weight: 500;
            font-size: 1rem;
        }
        
        .comment-card {
            background: var(--dark-card-light);
            border: 1px solid var(--dark-border);
            border-radius: 8px;
            padding: 1.25rem;
            margin-bottom: 1rem;
        }
        
        .comment-header {
            display: flex;
            justify-content: between;
            align-items: center;
            margin-bottom: 0.75rem;
        }
        
        .comment-author {
            color: var(--text-light);
            font-weight: 600;
        }
        
        .comment-date {
            color: var(--text-muted);
            font-size: 0.875rem;
        }
        
        .comment-content {
            color: var(--text-light);
            line-height: 1.5;
        }
        
        .status-card {
            background: var(--dark-card-light);
            border-radius: 8px;
            padding: 1.5rem;
        }
        
        .status-item {
            margin-bottom: 1.25rem;
            padding-bottom: 1.25rem;
            border-bottom: 1px solid var(--dark-border);
        }
        
        .status-item:last-child {
            margin-bottom: 0;
            padding-bottom: 0;
            border-bottom: none;
        }
        
        .empty-comments {
            text-align: center;
            padding: 2rem;
            color: var(--text-muted);
        }
        
        .empty-comments i {
            font-size: 3rem;
            margin-bottom: 1rem;
            opacity: 0.5;
        }
    </style>
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="ticket-title">
                <i class="fas fa-ticket-alt me-2"></i> 
                Ticket #<?php echo $ticket['id']; ?>
            </h2>
            <a href="mis_tickets.php" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i> Volver a Mis Tickets
            </a>
        </div>

        <div class="row">
            <div class="col-md-8">
                <!-- Información Principal del Ticket -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Información del Ticket</h5>
                    </div>
                    <div class="card-body">
                        <h4 class="ticket-title"><?php echo htmlspecialchars($ticket['titulo']); ?></h4>
                        <div class="ticket-description">
                            <?php echo nl2br(htmlspecialchars($ticket['descripcion'])); ?>
                        </div>
                        
                        <div class="info-section">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <div class="info-label">Categoría</div>
                                        <div class="info-value">
                                            <span class="badge bg-dark"><?php echo $ticket['categoria']; ?></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <div class="info-label">Prioridad</div>
                                        <div class="info-value">
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
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Comentarios -->
                <div class="card mt-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-comments me-2"></i>Comentarios</h5>
                    </div>
                    <div class="card-body">
                        <?php if (count($comentarios) > 0): ?>
                            <?php foreach ($comentarios as $comentario): ?>
                            <div class="comment-card">
                                <div class="comment-header">
                                    <span class="comment-author">
                                        <i class="fas fa-user me-2 text-primary"></i>
                                        <?php echo htmlspecialchars($comentario['usuario_nombre']); ?>
                                    </span>
                                    <span class="comment-date">
                                        <i class="fas fa-clock me-1"></i>
                                        <?php echo date('d/m/Y H:i', strtotime($comentario['created_at'])); ?>
                                    </span>
                                </div>
                                <div class="comment-content">
                                    <?php echo nl2br(htmlspecialchars($comentario['comentario'])); ?>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="empty-comments">
                                <i class="fas fa-comment-slash"></i>
                                <h5>No hay comentarios aún</h5>
                                <p class="text-muted">Los comentarios aparecerán aquí cuando el equipo de soporte se comunique contigo.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <!-- Información de estado -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-clipboard-check me-2"></i>Estado del Ticket</h5>
                    </div>
                    <div class="card-body">
                        <div class="status-card">
                            <div class="status-item">
                                <div class="info-label">Estado Actual</div>
                                <div class="info-value">
                                    <span class="badge bg-<?php 
                                        switch($ticket['estado']) {
                                            case 'Nuevo': echo 'primary'; break;
                                            case 'Asignado': echo 'warning'; break;
                                            case 'En Progreso': echo 'info'; break;
                                            case 'Resuelto': echo 'success'; break;
                                            default: echo 'secondary';
                                        }
                                    ?> fs-6">
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
                                </div>
                            </div>
                            
                            <?php if ($ticket['tecnico_nombre']): ?>
                            <div class="status-item">
                                <div class="info-label">Técnico Asignado</div>
                                <div class="info-value">
                                    <i class="fas fa-user-check me-2 text-success"></i>
                                    <?php echo htmlspecialchars($ticket['tecnico_nombre']); ?>
                                </div>
                            </div>
                            <?php endif; ?>
                            
                            <div class="status-item">
                                <div class="info-label">Fecha de Creación</div>
                                <div class="info-value">
                                    <i class="fas fa-calendar-plus me-2 text-primary"></i>
                                    <?php echo date('d/m/Y H:i', strtotime($ticket['fecha_creacion'])); ?>
                                </div>
                            </div>
                            
                            <div class="status-item">
                                <div class="info-label">Última Actualización</div>
                                <div class="info-value">
                                    <i class="fas fa-sync-alt me-2 text-primary"></i>
                                    <?php echo date('d/m/Y H:i', strtotime($ticket['fecha_actualizacion'])); ?>
                                </div>
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
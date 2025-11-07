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
        /* Mismos estilos del tema oscuro */
        :root {
            --dark-bg: #0f172a;
            --dark-card: #1e293b;
            --dark-border: #334155;
            --primary: #3b82f6;
            --text-light: #f8fafc;
            --text-muted: #94a3b8;
        }
        
        body {
            background-color: var(--dark-bg);
            color: var(--text-light);
        }
        
        .card {
            background: var(--dark-card);
            border: 1px solid var(--dark-border);
        }
        
        .card-header {
            background: rgba(30, 41, 59, 0.8) !important;
            border-bottom: 1px solid var(--dark-border);
        }
    </style>
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>
                <i class="fas fa-ticket-alt"></i> 
                Ticket #<?php echo $ticket['id']; ?>
            </h2>
            <a href="mis_tickets.php" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Volver a Mis Tickets
            </a>
        </div>

        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Información del Ticket</h5>
                    </div>
                    <div class="card-body">
                        <h4><?php echo htmlspecialchars($ticket['titulo']); ?></h4>
                        <p class="text-muted"><?php echo nl2br(htmlspecialchars($ticket['descripcion'])); ?></p>
                        
                        <div class="row mt-4">
                            <div class="col-md-6">
                                <strong>Categoría:</strong>
                                <span class="badge bg-secondary"><?php echo $ticket['categoria']; ?></span>
                            </div>
                            <div class="col-md-6">
                                <strong>Prioridad:</strong>
                                <span class="badge bg-<?php 
                                    switch($ticket['prioridad']) {
                                        case 'Urgente': echo 'danger'; break;
                                        case 'Alta': echo 'warning'; break;
                                        case 'Media': echo 'info'; break;
                                        case 'Baja': echo 'success'; break;
                                    }
                                ?>"><?php echo $ticket['prioridad']; ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Comentarios -->
                <div class="card mt-4">
                    <div class="card-header">
                        <h5 class="mb-0">Comentarios</h5>
                    </div>
                    <div class="card-body">
                        <?php if (count($comentarios) > 0): ?>
                            <?php foreach ($comentarios as $comentario): ?>
                            <div class="mb-3 p-3 border rounded" style="border-color: var(--dark-border) !important;">
                                <div class="d-flex justify-content-between">
                                    <strong><?php echo htmlspecialchars($comentario['usuario_nombre']); ?></strong>
                                    <small class="text-muted">
                                        <?php echo date('d/m/Y H:i', strtotime($comentario['created_at'])); ?>
                                    </small>
                                </div>
                                <p class="mb-0 mt-2"><?php echo nl2br(htmlspecialchars($comentario['comentario'])); ?></p>
                            </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="text-muted">No hay comentarios aún.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <!-- Información de estado -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Estado del Ticket</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <strong>Estado:</strong><br>
                            <span class="badge bg-<?php 
                                switch($ticket['estado']) {
                                    case 'Nuevo': echo 'primary'; break;
                                    case 'Asignado': echo 'warning'; break;
                                    case 'En Progreso': echo 'info'; break;
                                    case 'Resuelto': echo 'success'; break;
                                    default: echo 'secondary';
                                }
                            ?> fs-6"><?php echo $ticket['estado']; ?></span>
                        </div>
                        
                        <?php if ($ticket['tecnico_nombre']): ?>
                        <div class="mb-3">
                            <strong>Técnico Asignado:</strong><br>
                            <span class="text-success">
                                <i class="fas fa-user-check"></i>
                                <?php echo htmlspecialchars($ticket['tecnico_nombre']); ?>
                            </span>
                        </div>
                        <?php endif; ?>
                        
                        <div class="mb-3">
                            <strong>Fecha de Creación:</strong><br>
                            <span class="text-muted">
                                <?php echo date('d/m/Y H:i', strtotime($ticket['fecha_creacion'])); ?>
                            </span>
                        </div>
                        
                        <div class="mb-3">
                            <strong>Última Actualización:</strong><br>
                            <span class="text-muted">
                                <?php echo date('d/m/Y H:i', strtotime($ticket['fecha_actualizacion'])); ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
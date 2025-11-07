<?php
include '../config/database.php';
include '../includes/auth.php';
redirectIfNotLoggedIn();
redirectIfNotTecnico();

$tecnico_id = $_SESSION['user_id'];
$ticket_id = $_GET['id'] ?? null;

if (!$ticket_id) {
    header('Location: dashboard.php');
    exit;
}

// Obtener información del ticket
$stmt = $pdo->prepare("SELECT t.*, u.nombre as usuario_nombre, u.email as usuario_email, 
                              c.nombre as categoria, e.nombre as estado, e.id as estado_id,
                              tech.nombre as tecnico_nombre
                       FROM tickets t 
                       JOIN usuarios u ON t.usuario_id = u.id 
                       JOIN categorias_tickets c ON t.categoria_id = c.id 
                       JOIN estados_ticket e ON t.estado_id = e.id
                       LEFT JOIN usuarios tech ON t.tecnico_asignado = tech.id
                       WHERE t.id = ? AND (t.tecnico_asignado = ? OR ? = 1)");
$stmt->execute([$ticket_id, $tecnico_id, $_SESSION['es_admin'] ?? 0]);
$ticket = $stmt->fetch();

if (!$ticket) {
    header('Location: dashboard.php');
    exit;
}

// Obtener estados disponibles
$estados = $pdo->query("SELECT * FROM estados_ticket ORDER BY id")->fetchAll();

// Obtener comentarios del ticket
$comentarios = $pdo->prepare("SELECT c.*, u.nombre as usuario_nombre, u.rol_id 
                              FROM comentarios_tickets c 
                              JOIN usuarios u ON c.usuario_id = u.id 
                              WHERE c.ticket_id = ? 
                              ORDER BY c.created_at ASC");
$comentarios->execute([$ticket_id]);
$comentarios = $comentarios->fetchAll();

// Procesar formulario de actualización
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nuevo_estado = $_POST['estado_id'] ?? $ticket['estado_id'];
    $comentario = trim($_POST['comentario'] ?? '');
    
    try {
        $pdo->beginTransaction();
        
        // Actualizar estado del ticket
        if ($nuevo_estado != $ticket['estado_id']) {
            $update_stmt = $pdo->prepare("UPDATE tickets SET estado_id = ?, fecha_actualizacion = NOW() WHERE id = ?");
            $update_stmt->execute([$nuevo_estado, $ticket_id]);
        }
        
        // Agregar comentario si hay texto
        if (!empty($comentario)) {
            $comentario_stmt = $pdo->prepare("INSERT INTO comentarios_tickets (ticket_id, usuario_id, comentario, es_interno, created_at) 
                                             VALUES (?, ?, ?, ?, NOW())");
            $comentario_stmt->execute([$ticket_id, $tecnico_id, $comentario, 0]);
        }
        
        $pdo->commit();
        
        // Redirigir para evitar reenvío del formulario
        header("Location: gestionar_ticket.php?id=$ticket_id&success=1");
        exit;
        
    } catch (Exception $e) {
        $pdo->rollBack();
        $error = "Error al actualizar el ticket: " . $e->getMessage();
    }
}

// Mostrar mensaje de éxito
$success = isset($_GET['success']);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestionar Ticket #<?php echo $ticket_id; ?> - Sistema de Tickets IT</title>
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
        
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
            margin-bottom: 20px;
        }
        
        .card-header {
            border-radius: 10px 10px 0 0 !important;
            font-weight: 600;
            background-color: white;
            border-bottom: 1px solid #e0e0e0;
        }
        
        .ticket-header {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            border-radius: 10px 10px 0 0;
        }
        
        .badge-priority {
            font-size: 0.8em;
            padding: 0.5em 0.8em;
        }
        
        .comment-card {
            border-left: 4px solid var(--primary);
            background-color: #f8f9fa;
        }
        
        .comment-technician {
            border-left-color: var(--warning);
            background-color: #fff3cd;
        }
        
        .comment-user {
            border-left-color: var(--primary);
            background-color: #e7f1ff;
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
            font-size: 0.9rem;
        }
        
        .technician-avatar {
            background: var(--warning);
        }
        
        .detail-label {
            font-weight: 600;
            color: #666;
            min-width: 120px;
        }
        
        .detail-value {
            color: #333;
        }
        
        .status-badge {
            font-size: 0.8em;
            padding: 0.5em 0.8em;
        }
        
        .btn-primary {
            background: var(--primary);
            border-color: var(--primary);
        }
        
        .btn-primary:hover {
            background: var(--secondary);
            border-color: var(--secondary);
        }
        
        .timeline {
            position: relative;
            padding-left: 30px;
        }
        
        .timeline::before {
            content: '';
            position: absolute;
            left: 15px;
            top: 0;
            bottom: 0;
            width: 2px;
            background: #e0e0e0;
        }
        
        .timeline-item {
            position: relative;
            margin-bottom: 20px;
        }
        
        .timeline-item::before {
            content: '';
            position: absolute;
            left: -23px;
            top: 5px;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: var(--primary);
            border: 2px solid white;
        }
        
        .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 0.2rem rgba(26, 42, 108, 0.25);
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
    
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12">
                <!-- Navegación -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <a href="dashboard.php" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-arrow-left"></i> Volver al Dashboard
                        </a>
                    </div>
                    <div>
                        <span class="badge bg-warning fs-6">
                            <i class="fas fa-tools"></i> <?php echo $_SESSION['rol']; ?>
                        </span>
                    </div>
                </div>

                <!-- Alertas -->
                <?php if ($success): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle"></i> Ticket actualizado correctamente.
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($error)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <!-- Header del Ticket -->
                <div class="card ticket-header">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <h1 class="h4 mb-2">
                                    <i class="fas fa-ticket-alt"></i> Ticket #<?php echo $ticket_id; ?>
                                </h1>
                                <h2 class="h5 mb-0"><?php echo htmlspecialchars($ticket['titulo']); ?></h2>
                            </div>
                            <div class="col-md-4 text-md-end">
                                <span class="badge badge-priority bg-<?php 
                                    switch($ticket['prioridad']) {
                                        case 'Urgente': echo 'danger'; break;
                                        case 'Alta': echo 'warning'; break;
                                        case 'Media': echo 'info'; break;
                                        case 'Baja': echo 'success'; break;
                                    }
                                ?> me-2">
                                    <?php echo $ticket['prioridad']; ?>
                                </span>
                                <span class="badge status-badge bg-<?php 
                                    switch($ticket['estado']) {
                                        case 'Nuevo': echo 'primary'; break;
                                        case 'Asignado': echo 'warning'; break;
                                        case 'En Progreso': echo 'info'; break;
                                        case 'Resuelto': echo 'success'; break;
                                        default: echo 'secondary';
                                    }
                                ?>">
                                    <?php echo $ticket['estado']; ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <!-- Columna izquierda - Información y Comentarios -->
                    <div class="col-lg-8">
                        <!-- Información del Ticket -->
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0"><i class="fas fa-info-circle"></i> Información del Ticket</h5>
                            </div>
                            <div class="card-body">
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <div class="d-flex mb-2">
                                            <span class="detail-label">Cliente:</span>
                                            <span class="detail-value"><?php echo htmlspecialchars($ticket['usuario_nombre']); ?></span>
                                        </div>
                                        <div class="d-flex mb-2">
                                            <span class="detail-label">Email:</span>
                                            <span class="detail-value"><?php echo htmlspecialchars($ticket['usuario_email']); ?></span>
                                        </div>
                                        <div class="d-flex mb-2">
                                            <span class="detail-label">Categoría:</span>
                                            <span class="detail-value"><?php echo $ticket['categoria']; ?></span>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="d-flex mb-2">
                                            <span class="detail-label">Fecha creación:</span>
                                            <span class="detail-value"><?php echo date('d/m/Y H:i', strtotime($ticket['fecha_creacion'])); ?></span>
                                        </div>
                                        <div class="d-flex mb-2">
                                            <span class="detail-label">Última actualización:</span>
                                            <span class="detail-value"><?php echo date('d/m/Y H:i', strtotime($ticket['fecha_actualizacion'])); ?></span>
                                        </div>
                                        <div class="d-flex mb-2">
                                            <span class="detail-label">Técnico asignado:</span>
                                            <span class="detail-value"><?php echo $ticket['tecnico_nombre'] ?: 'Sin asignar'; ?></span>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <span class="detail-label">Descripción:</span>
                                    <div class="detail-value mt-1 p-3 bg-light rounded">
                                        <?php echo nl2br(htmlspecialchars($ticket['descripcion'])); ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Historial de Comentarios -->
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0"><i class="fas fa-comments"></i> Historial de Comentarios</h5>
                            </div>
                            <div class="card-body">
                                <?php if (count($comentarios) > 0): ?>
                                    <div class="timeline">
                                        <?php foreach ($comentarios as $comentario): ?>
                                            <div class="timeline-item">
                                                <div class="card comment-card <?php echo $comentario['rol_id'] == 2 ? 'comment-technician' : 'comment-user'; ?>">
                                                    <div class="card-body">
                                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                                            <div class="d-flex align-items-center">
                                                                <div class="user-avatar <?php echo $comentario['rol_id'] == 2 ? 'technician-avatar' : ''; ?> me-2">
                                                                    <?php echo strtoupper(substr($comentario['usuario_nombre'], 0, 2)); ?>
                                                                </div>
                                                                <div>
                                                                    <strong><?php echo htmlspecialchars($comentario['usuario_nombre']); ?></strong>
                                                                    <?php if ($comentario['rol_id'] == 2): ?>
                                                                        <span class="badge bg-warning ms-1">Técnico</span>
                                                                    <?php endif; ?>
                                                                </div>
                                                            </div>
                                                            <small class="text-muted">
                                                                <?php echo date('d/m/Y H:i', strtotime($comentario['created_at'])); ?>
                                                            </small>
                                                        </div>
                                                        <p class="mb-0"><?php echo nl2br(htmlspecialchars($comentario['comentario'])); ?></p>
                                                        <?php if ($comentario['es_interno']): ?>
                                                            <small class="text-muted"><i class="fas fa-eye-slash"></i> Comentario interno</small>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php else: ?>
                                    <div class="text-center py-4">
                                        <i class="fas fa-comments fa-3x text-muted mb-3"></i>
                                        <p class="text-muted">No hay comentarios aún</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Columna derecha - Acciones -->
                    <div class="col-lg-4">
                        <!-- Formulario de Gestión -->
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0"><i class="fas fa-edit"></i> Gestionar Ticket</h5>
                            </div>
                            <div class="card-body">
                                <form method="POST" action="">
                                    <div class="mb-3">
                                        <label for="estado_id" class="form-label">Cambiar Estado</label>
                                        <select class="form-select" id="estado_id" name="estado_id" required>
                                            <?php foreach ($estados as $estado): ?>
                                                <option value="<?php echo $estado['id']; ?>" 
                                                    <?php echo $estado['id'] == $ticket['estado_id'] ? 'selected' : ''; ?>>
                                                    <?php echo $estado['nombre']; ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="comentario" class="form-label">Agregar Comentario</label>
                                        <textarea class="form-control" id="comentario" name="comentario" 
                                                  rows="5" placeholder="Describe las acciones realizadas o el progreso del ticket..."></textarea>
                                        <div class="form-text">
                                            Este comentario será visible para el cliente.
                                        </div>
                                    </div>
                                    
                                    <div class="d-grid gap-2">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-save"></i> Guardar Cambios
                                        </button>
                                        <a href="dashboard.php" class="btn btn-outline-secondary">
                                            <i class="fas fa-times"></i> Cancelar
                                        </a>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <!-- Información Rápida -->
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0"><i class="fas fa-clock"></i> Resumen</h5>
                            </div>
                            <div class="card-body">
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Días abierto:</span>
                                    <strong>
                                        <?php 
                                            $fecha_creacion = new DateTime($ticket['fecha_creacion']);
                                            $hoy = new DateTime();
                                            $dias = $hoy->diff($fecha_creacion)->days;
                                            echo $dias . ' día' . ($dias != 1 ? 's' : '');
                                        ?>
                                    </strong>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Total comentarios:</span>
                                    <strong><?php echo count($comentarios); ?></strong>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span>Última actualización:</span>
                                    <strong><?php echo date('d/m/Y', strtotime($ticket['fecha_actualizacion'])); ?></strong>
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
        document.addEventListener('DOMContentLoaded', function() {
            // Auto-focus en el textarea de comentarios
            document.getElementById('comentario').focus();
            
            // Confirmación antes de salir si hay cambios
            const form = document.querySelector('form');
            let formChanged = false;
            
            form.addEventListener('change', function() {
                formChanged = true;
            });
            
            form.addEventListener('submit', function() {
                formChanged = false;
            });
            
            window.addEventListener('beforeunload', function(e) {
                if (formChanged) {
                    e.preventDefault();
                    e.returnValue = '';
                }
            });
        });
    </script>
</body>
</html>
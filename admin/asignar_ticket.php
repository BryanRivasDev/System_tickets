<?php
include '../config/database.php';
include '../includes/auth.php';
redirectIfNotLoggedIn();
redirectIfNotAdmin();

if (!isset($_GET['id'])) {
    header("Location: tickets.php");
    exit();
}

$ticket_id = $_GET['id'];

// Obtener información del ticket
$stmt = $pdo->prepare("SELECT t.*, u.nombre as usuario_nombre, c.nombre as categoria, e.nombre as estado 
                       FROM tickets t 
                       JOIN usuarios u ON t.usuario_id = u.id 
                       JOIN categorias_tickets c ON t.categoria_id = c.id 
                       JOIN estados_ticket e ON t.estado_id = e.id 
                       WHERE t.id = ?");
$stmt->execute([$ticket_id]);
$ticket = $stmt->fetch();

if (!$ticket) {
    header("Location: tickets.php");
    exit();
}

// Obtener técnicos disponibles
$tecnicos = $pdo->query("SELECT id, nombre, departamento FROM usuarios WHERE rol_id IN (1,2) AND activo = 1 ORDER BY nombre")->fetchAll();

// Asignar ticket
if ($_POST) {
    $tecnico_id = $_POST['tecnico_id'];
    $comentario = $_POST['comentario'];
    
    try {
        $pdo->beginTransaction();
        
        // Actualizar ticket
        $stmt = $pdo->prepare("UPDATE tickets SET tecnico_asignado = ?, estado_id = 2 WHERE id = ?");
        $stmt->execute([$tecnico_id, $ticket_id]);
        
        // Agregar comentario interno si existe
        if (!empty($comentario)) {
            $stmt = $pdo->prepare("INSERT INTO comentarios_tickets (ticket_id, usuario_id, comentario, es_interno) VALUES (?, ?, ?, 1)");
            $stmt->execute([$ticket_id, $_SESSION['user_id'], $comentario]);
        }
        
        // Registrar en historial
        $tecnico_nombre = '';
        foreach ($tecnicos as $tec) {
            if ($tec['id'] == $tecnico_id) {
                $tecnico_nombre = $tec['nombre'];
                break;
            }
        }
        
        $stmt = $pdo->prepare("INSERT INTO historial_tickets (ticket_id, usuario_id, accion, descripcion) VALUES (?, ?, 'Asignación', ?)");
        $stmt->execute([$ticket_id, $_SESSION['user_id'], "Ticket asignado a: $tecnico_nombre"]);
        
        $pdo->commit();
        $success = "Ticket asignado correctamente al técnico";
        
    } catch (Exception $e) {
        $pdo->rollBack();
        $error = "Error al asignar ticket: " . $e->getMessage();
    }
}

// Obtener comentarios del ticket
$comentarios = $pdo->prepare("SELECT c.*, u.nombre as usuario_nombre 
                             FROM comentarios_tickets c 
                             JOIN usuarios u ON c.usuario_id = u.id 
                             WHERE c.ticket_id = ? 
                             ORDER BY c.created_at ASC");
$comentarios->execute([$ticket_id]);
$comentarios = $comentarios->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Asignar Ticket</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="container mt-4">
        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h4><i class="fas fa-ticket-alt"></i> Asignar Ticket #<?php echo $ticket['id']; ?></h4>
                    </div>
                    <div class="card-body">
                        <?php if (isset($success)): ?>
                            <div class="alert alert-success"><?php echo $success; ?></div>
                        <?php endif; ?>
                        
                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>

                        <div class="mb-4">
                            <h5>Información del Ticket</h5>
                            <table class="table table-bordered">
                                <tr>
                                    <th width="30%">Título:</th>
                                    <td><?php echo htmlspecialchars($ticket['titulo']); ?></td>
                                </tr>
                                <tr>
                                    <th>Usuario:</th>
                                    <td><?php echo htmlspecialchars($ticket['usuario_nombre']); ?></td>
                                </tr>
                                <tr>
                                    <th>Categoría:</th>
                                    <td><?php echo $ticket['categoria']; ?></td>
                                </tr>
                                <tr>
                                    <th>Prioridad:</th>
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
                                </tr>
                                <tr>
                                    <th>Estado:</th>
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
                                </tr>
                                <tr>
                                    <th>Descripción:</th>
                                    <td><?php echo nl2br(htmlspecialchars($ticket['descripcion'])); ?></td>
                                </tr>
                            </table>
                        </div>

                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label"><strong>Asignar a Técnico:</strong></label>
                                <select name="tecnico_id" class="form-select" required>
                                    <option value="">Seleccionar técnico...</option>
                                    <?php foreach ($tecnicos as $tecnico): ?>
                                    <option value="<?php echo $tecnico['id']; ?>" 
                                        <?php echo $ticket['tecnico_asignado'] == $tecnico['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($tecnico['nombre']); ?> 
                                        (<?php echo $tecnico['departamento']; ?>)
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Comentario interno (opcional):</label>
                                <textarea name="comentario" class="form-control" rows="3" 
                                          placeholder="Notas internas para el técnico..."></textarea>
                            </div>
                            
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-success">
                                    <i class="fas fa-user-check"></i> Asignar Ticket
                                </button>
                                <a href="tickets.php" class="btn btn-secondary">Volver a Tickets</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <!-- Comentarios existentes -->
                <div class="card">
                    <div class="card-header bg-info text-white">
                        <h5><i class="fas fa-comments"></i> Comentarios</h5>
                    </div>
                    <div class="card-body" style="max-height: 400px; overflow-y: auto;">
                        <?php if (count($comentarios) > 0): ?>
                            <?php foreach ($comentarios as $comentario): ?>
                            <div class="mb-3 p-2 border rounded">
                                <div class="d-flex justify-content-between">
                                    <strong><?php echo htmlspecialchars($comentario['usuario_nombre']); ?></strong>
                                    <small class="text-muted">
                                        <?php echo date('d/m/Y H:i', strtotime($comentario['created_at'])); ?>
                                    </small>
                                </div>
                                <p class="mb-0"><?php echo nl2br(htmlspecialchars($comentario['comentario'])); ?></p>
                                <?php if ($comentario['es_interno']): ?>
                                    <small class="text-warning"><i class="fas fa-eye-slash"></i> Interno</small>
                                <?php endif; ?>
                            </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="text-muted">No hay comentarios aún.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
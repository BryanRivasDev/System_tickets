<?php
include '../config/database.php';
include '../includes/auth.php';
redirectIfNotLoggedIn();

$user_id = $_SESSION['user_id'];

// Obtener datos completos del usuario
$stmt = $pdo->prepare("SELECT u.*, r.nombre as rol_nombre 
                       FROM usuarios u 
                       JOIN roles r ON u.rol_id = r.id 
                       WHERE u.id = ?");
$stmt->execute([$user_id]);
$usuario = $stmt->fetch();

if (!$usuario) {
    header("Location: dashboard.php");
    exit();
}

// Obtener estadísticas del usuario - CORREGIDO
$stmt = $pdo->prepare("SELECT COUNT(*) FROM tickets WHERE usuario_id = ?");
$stmt->execute([$user_id]);
$total_tickets = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM tickets WHERE usuario_id = ? AND estado_id = 4");
$stmt->execute([$user_id]);
$tickets_resueltos = $stmt->fetchColumn();

// Actualizar perfil
if ($_POST) {
    $nombre = $_POST['nombre'];
    $departamento = $_POST['departamento'];
    $telefono = $_POST['telefono'];
    
    $stmt = $pdo->prepare("UPDATE usuarios SET nombre = ?, departamento = ?, telefono = ? WHERE id = ?");
    if ($stmt->execute([$nombre, $departamento, $telefono, $user_id])) {
        $_SESSION['user_name'] = $nombre;
        $success = "Perfil actualizado correctamente";
        
        // Recargar datos del usuario
        $stmt = $pdo->prepare("SELECT u.*, r.nombre as rol_nombre FROM usuarios u JOIN roles r ON u.rol_id = r.id WHERE u.id = ?");
        $stmt->execute([$user_id]);
        $usuario = $stmt->fetch();
    } else {
        $error = "Error al actualizar el perfil";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Mi Perfil</title>
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
        }
        
        body {
            background-color: var(--dark-bg);
            color: var(--text-light);
            font-family: 'Inter', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .card {
            background: var(--dark-card);
            border: 1px solid var(--dark-border);
            border-radius: 12px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }
        
        .card-header {
            background: linear-gradient(135deg, var(--dark-card) 0%, var(--dark-card-light) 100%) !important;
            border-bottom: 1px solid var(--dark-border);
            border-radius: 12px 12px 0 0 !important;
            padding: 1.5rem;
        }
        
        .form-control {
            background: var(--dark-card-light);
            border: 1px solid var(--dark-border);
            color: var(--text-light);
            border-radius: 8px;
            padding: 0.75rem 1rem;
        }
        
        .form-control:focus {
            background: var(--dark-card-light);
            border-color: var(--primary);
            color: var(--text-light);
            box-shadow: 0 0 0 0.2rem rgba(59, 130, 246, 0.25);
        }
        
        .form-label {
            color: var(--text-light);
            font-weight: 600;
            margin-bottom: 0.5rem;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--primary) 0%, #1d4ed8 100%);
            border: none;
            border-radius: 8px;
            font-weight: 600;
            padding: 0.75rem 1.5rem;
        }
        
        .profile-header {
            background: linear-gradient(135deg, var(--dark-card) 0%, var(--dark-card-light) 100%);
            border-radius: 12px;
            padding: 2rem;
            margin-bottom: 2rem;
            border: 1px solid var(--dark-border);
        }
        
        .stat-card {
            background: var(--dark-card);
            border: 1px solid var(--dark-border);
            border-radius: 10px;
            padding: 1.5rem;
            text-align: center;
        }
        
        .stat-number {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }
        
        .user-avatar {
            width: 100px;
            height: 100px;
            background: linear-gradient(135deg, var(--primary), #8b5cf6);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            color: white;
            margin: 0 auto 1rem;
        }
    </style>
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="container mt-4">
        <!-- Header del Perfil -->
        <div class="profile-header">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1 class="display-6 fw-bold mb-2">
                        <i class="fas fa-user-circle me-2"></i>Mi Perfil
                    </h1>
                    <p class="lead text-muted mb-0">Gestiona tu información personal</p>
                </div>
                <div class="col-md-4 text-end">
                    <div class="user-avatar">
                        <i class="fas fa-user"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0 fw-bold"><i class="fas fa-edit me-2"></i>Editar Información</h5>
                    </div>
                    <div class="card-body">
                        <?php if (isset($success)): ?>
                            <div class="alert alert-success border-0 rounded-8">
                                <i class="fas fa-check-circle me-2"></i><?php echo $success; ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger border-0 rounded-8">
                                <i class="fas fa-exclamation-triangle me-2"></i><?php echo $error; ?>
                            </div>
                        <?php endif; ?>
                        
                        <form method="POST">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Nombre completo:</label>
                                        <input type="text" name="nombre" class="form-control" 
                                               value="<?php echo htmlspecialchars($usuario['nombre']); ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Email:</label>
                                        <input type="email" class="form-control" 
                                               value="<?php echo htmlspecialchars($usuario['email']); ?>" disabled>
                                        <small class="text-muted">El email no se puede modificar</small>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Departamento:</label>
                                        <input type="text" name="departamento" class="form-control" 
                                               value="<?php echo htmlspecialchars($usuario['departamento'] ?? ''); ?>"
                                               placeholder="Ej: Ventas, Marketing, IT...">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Teléfono:</label>
                                        <input type="text" name="telefono" class="form-control" 
                                               value="<?php echo htmlspecialchars($usuario['telefono'] ?? ''); ?>"
                                               placeholder="Ej: +1234567890">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Rol:</label>
                                        <input type="text" class="form-control" 
                                               value="<?php echo htmlspecialchars($usuario['rol_nombre']); ?>" disabled>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Estado:</label>
                                        <input type="text" class="form-control" 
                                               value="<?php echo $usuario['activo'] ? 'Activo' : 'Inactivo'; ?>" disabled>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Fecha de registro:</label>
                                <input type="text" class="form-control" 
                                       value="<?php echo date('d/m/Y H:i', strtotime($usuario['created_at'])); ?>" disabled>
                            </div>
                            
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i> Guardar Cambios
                                </button>
                                <a href="dashboard.php" class="btn btn-outline-secondary">
                                    <i class="fas fa-arrow-left me-2"></i> Volver al Dashboard
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <!-- Estadísticas del Usuario -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="mb-0 fw-bold"><i class="fas fa-chart-bar me-2"></i>Estadísticas</h6>
                    </div>
                    <div class="card-body">
                        <div class="stat-card mb-3">
                            <div class="stat-number text-primary"><?php echo $total_tickets; ?></div>
                            <div class="stat-text">Total de Tickets</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-number text-success"><?php echo $tickets_resueltos; ?></div>
                            <div class="stat-text">Tickets Resueltos</div>
                        </div>
                    </div>
                </div>
                
                <!-- Información de la Cuenta -->
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0 fw-bold"><i class="fas fa-info-circle me-2"></i>Información de la Cuenta</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <small class="text-muted">ID de Usuario</small>
                            <div class="fw-semibold">#<?php echo $usuario['id']; ?></div>
                        </div>
                        <div class="mb-3">
                            <small class="text-muted">Última Actualización</small>
                            <div class="fw-semibold">
                                <?php echo date('d/m/Y H:i', strtotime($usuario['updated_at'])); ?>
                            </div>
                        </div>
                        <div class="mb-3">
                            <small class="text-muted">Estado de la Cuenta</small>
                            <div>
                                <span class="badge bg-<?php echo $usuario['activo'] ? 'success' : 'secondary'; ?>">
                                    <?php echo $usuario['activo'] ? 'Activa' : 'Inactiva'; ?>
                                </span>
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
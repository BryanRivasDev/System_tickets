<?php
include '../config/database.php';
include '../includes/auth.php';
redirectIfNotLoggedIn();
redirectIfNotAdmin();

// Obtener todos los usuarios
$usuarios = $pdo->query("SELECT u.*, r.nombre as rol_nombre 
                         FROM usuarios u 
                         JOIN roles r ON u.rol_id = r.id 
                         ORDER BY u.created_at DESC")->fetchAll();

// Activar/Desactivar usuario
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = $_GET['id'];
    $action = $_GET['action'];
    
    if ($action == 'activate') {
        $pdo->prepare("UPDATE usuarios SET activo = 1 WHERE id = ?")->execute([$id]);
    } elseif ($action == 'deactivate') {
        $pdo->prepare("UPDATE usuarios SET activo = 0 WHERE id = ?")->execute([$id]);
    } elseif ($action == 'delete') {
        $pdo->prepare("DELETE FROM usuarios WHERE id = ? AND id != ?")->execute([$id, $_SESSION['user_id']]);
    }
    
    header("Location: usuarios.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Gestión de Usuarios - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Gestión de Usuarios</h2>
            <a href="crear_usuario.php" class="btn btn-primary">Crear Nuevo Usuario</a>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h5>Lista de Usuarios</h5>
            </div>
            <div class="card-body">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Email</th>
                            <th>Rol</th>
                            <th>Departamento</th>
                            <th>Estado</th>
                            <th>Fecha Registro</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($usuarios as $usuario): ?>
                        <tr>
                            <td><?php echo $usuario['id']; ?></td>
                            <td><?php echo $usuario['nombre']; ?></td>
                            <td><?php echo $usuario['email']; ?></td>
                            <td>
                                <span class="badge bg-<?php 
                                    switch($usuario['rol_nombre']) {
                                        case 'Administrador': echo 'danger'; break;
                                        case 'Tecnico': echo 'warning'; break;
                                        case 'Usuario': echo 'info'; break;
                                        default: echo 'secondary';
                                    }
                                ?>"><?php echo $usuario['rol_nombre']; ?></span>
                            </td>
                            <td><?php echo $usuario['departamento'] ?: 'N/A'; ?></td>
                            <td>
                                <span class="badge bg-<?php echo $usuario['activo'] ? 'success' : 'secondary'; ?>">
                                    <?php echo $usuario['activo'] ? 'Activo' : 'Inactivo'; ?>
                                </span>
                            </td>
                            <td><?php echo date('d/m/Y', strtotime($usuario['created_at'])); ?></td>
                            <td>
                                <div class="btn-group">
                                    <?php if ($usuario['activo']): ?>
                                        <a href="usuarios.php?action=deactivate&id=<?php echo $usuario['id']; ?>" 
                                           class="btn btn-sm btn-warning" 
                                           onclick="return confirm('¿Desactivar usuario?')">Desactivar</a>
                                    <?php else: ?>
                                        <a href="usuarios.php?action=activate&id=<?php echo $usuario['id']; ?>" 
                                           class="btn btn-sm btn-success"
                                           onclick="return confirm('¿Activar usuario?')">Activar</a>
                                    <?php endif; ?>
                                    
                                    <?php if ($usuario['id'] != $_SESSION['user_id']): ?>
                                        <a href="usuarios.php?action=delete&id=<?php echo $usuario['id']; ?>" 
                                           class="btn btn-sm btn-danger"
                                           onclick="return confirm('¿Eliminar usuario permanentemente?')">Eliminar</a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
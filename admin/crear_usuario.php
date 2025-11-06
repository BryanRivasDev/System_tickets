<?php
include '../config/database.php';
include '../includes/auth.php';
redirectIfNotLoggedIn();
redirectIfNotAdmin();

if ($_POST) {
    $nombre = $_POST['nombre'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $rol_id = $_POST['rol_id'];
    $departamento = $_POST['departamento'];
    $telefono = $_POST['telefono'];
    
    try {
        $stmt = $pdo->prepare("INSERT INTO usuarios (nombre, email, password, rol_id, departamento, telefono) 
                              VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$nombre, $email, $password, $rol_id, $departamento, $telefono]);
        
        $success = "Usuario creado correctamente";
    } catch (PDOException $e) {
        $error = "Error al crear usuario: " . $e->getMessage();
    }
}

// Obtener roles para el select
$roles = $pdo->query("SELECT * FROM roles")->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Crear Usuario - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="container mt-4">
        <h2>Crear Nuevo Usuario</h2>
        
        <?php if (isset($success)): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label>Nombre completo:</label>
                        <input type="text" name="nombre" class="form-control" required>
                    </div>
                    
                    <div class="mb-3">
                        <label>Email:</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    
                    <div class="mb-3">
                        <label>Contraseña:</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="mb-3">
                        <label>Rol:</label>
                        <select name="rol_id" class="form-control" required>
                            <option value="">Seleccionar rol</option>
                            <?php foreach ($roles as $rol): ?>
                            <option value="<?php echo $rol['id']; ?>"><?php echo $rol['nombre']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label>Departamento:</label>
                        <input type="text" name="departamento" class="form-control">
                    </div>
                    
                    <div class="mb-3">
                        <label>Teléfono:</label>
                        <input type="text" name="telefono" class="form-control">
                    </div>
                </div>
            </div>
            
            <button type="submit" class="btn btn-primary">Crear Usuario</button>
            <a href="usuarios.php" class="btn btn-secondary">Volver a Lista</a>
        </form>
    </div>
</body>
</html>
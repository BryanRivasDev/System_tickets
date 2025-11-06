<?php
include 'config/database.php';
session_start();

// Proceso de Login
if (isset($_POST['login'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];
    
    $stmt = $pdo->prepare("SELECT u.*, r.nombre as rol_nombre FROM usuarios u 
                          JOIN roles r ON u.rol_id = r.id 
                          WHERE u.email = ? AND u.activo = 1");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    if ($user && password_verify($password, $user['password'])) {
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_name'] = $user['nombre'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['rol'] = $user['rol_nombre'];
    
    // REDIRECCIÓN CORREGIDA - PUNTO 5
    if ($user['rol_nombre'] == 'Administrador') {
        header("Location: admin/dashboard.php");
    } elseif ($user['rol_nombre'] == 'Tecnico') {
        header("Location: tecnico/dashboard.php");
    } else {
        header("Location: user/dashboard.php");
    }
    exit();
    } else {
        $login_error = "Credenciales incorrectas";
    }
}

// Proceso de Registro
if (isset($_POST['register'])) {
    $nombre = trim($_POST['nombre']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $departamento = trim($_POST['departamento']);
    $telefono = trim($_POST['telefono']);
    
    // Validaciones
    $errors = [];
    
    if (empty($nombre)) $errors[] = "El nombre es requerido";
    if (empty($email)) $errors[] = "El email es requerido";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "El email no es válido";
    if (strlen($password) < 6) $errors[] = "La contraseña debe tener al menos 6 caracteres";
    if ($password !== $confirm_password) $errors[] = "Las contraseñas no coinciden";
    
    // Verificar si el email ya existe
    $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        $errors[] = "El email ya está registrado";
    }
    
    if (empty($errors)) {
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        
        try {
            $stmt = $pdo->prepare("INSERT INTO usuarios (nombre, email, password, departamento, telefono) 
                                  VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$nombre, $email, $password_hash, $departamento, $telefono]);
            
            $register_success = "¡Registro exitoso! Ahora puedes iniciar sesión.";
            
            // Limpiar el formulario
            $_POST = array();
            
        } catch (PDOException $e) {
            $errors[] = "Error al registrar usuario: " . $e->getMessage();
        }
    }
    
    if (!empty($errors)) {
        $register_error = implode("<br>", $errors);
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login - Sistema de Tickets</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .login-container {
            max-width: 400px;
            margin: 50px auto;
            padding: 20px;
        }
        .form-tabs .nav-link {
            color: #495057;
        }
        .form-tabs .nav-link.active {
            font-weight: bold;
        }
        .tab-pane {
            padding: 20px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="login-container">
            <div class="text-center mb-4">
                <h2>Sistema de Tickets IT</h2>
                <p class="text-muted">Gestión de soporte técnico</p>
            </div>

            <ul class="nav nav-tabs form-tabs" id="authTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="login-tab" data-bs-toggle="tab" data-bs-target="#login" type="button" role="tab">
                        Iniciar Sesión
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="register-tab" data-bs-toggle="tab" data-bs-target="#register" type="button" role="tab">
                        Registrarse
                    </button>
                </li>
            </ul>

            <div class="tab-content">
                <!-- Pestaña de Login -->
                <div class="tab-pane fade show active" id="login" role="tabpanel">
                    <?php if (isset($login_error)): ?>
                        <div class="alert alert-danger"><?php echo $login_error; ?></div>
                    <?php endif; ?>

                    <?php if (isset($register_success)): ?>
                        <div class="alert alert-success"><?php echo $register_success; ?></div>
                    <?php endif; ?>

                    <form method="POST">
                        <input type="hidden" name="login" value="1">
                        <div class="mb-3">
                            <label class="form-label">Email:</label>
                            <input type="email" name="email" class="form-control" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Contraseña:</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Iniciar Sesión</button>
                    </form>
                </div>

                <!-- Pestaña de Registro -->
                <div class="tab-pane fade" id="register" role="tabpanel">
                    <?php if (isset($register_error)): ?>
                        <div class="alert alert-danger"><?php echo $register_error; ?></div>
                    <?php endif; ?>

                    <form method="POST">
                        <input type="hidden" name="register" value="1">
                        <div class="mb-3">
                            <label class="form-label">Nombre completo:</label>
                            <input type="text" name="nombre" class="form-control" value="<?php echo isset($_POST['nombre']) ? htmlspecialchars($_POST['nombre']) : ''; ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email:</label>
                            <input type="email" name="email" class="form-control" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Contraseña:</label>
                                    <input type="password" name="password" class="form-control" required minlength="6">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Confirmar contraseña:</label>
                                    <input type="password" name="confirm_password" class="form-control" required>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Departamento:</label>
                                    <input type="text" name="departamento" class="form-control" value="<?php echo isset($_POST['departamento']) ? htmlspecialchars($_POST['departamento']) : ''; ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Teléfono:</label>
                                    <input type="text" name="telefono" class="form-control" value="<?php echo isset($_POST['telefono']) ? htmlspecialchars($_POST['telefono']) : ''; ?>">
                                </div>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-success w-100">Registrarse</button>
                    </form>
                </div>
            </div>

            <div class="text-center mt-3">
                <small class="text-muted">
                    Al registrarte, aceptas nuestros términos y condiciones.
                    Los usuarios nuevos tendrán rol de "Usuario" por defecto.
                </small>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Mantener la pestaña activa después del envío del formulario
        document.addEventListener('DOMContentLoaded', function() {
            <?php if (isset($_POST['register'])): ?>
                var registerTab = new bootstrap.Tab(document.getElementById('register-tab'));
                registerTab.show();
            <?php endif; ?>
        });
    </script>
</body>
</html>
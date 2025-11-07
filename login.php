<?php
include 'config/database.php';
session_start();

// Mostrar mensaje si se cerró sesión
if (isset($_GET['msg']) && $_GET['msg'] == 'sesion_cerrada') {
    $info = "Sesión cerrada correctamente";
}

// Si ya está logueado, redirigir directamente
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['rol'] == 'Administrador') {
        header("Location: admin/dashboard.php");
    } elseif ($_SESSION['rol'] == 'Tecnico') {
        header("Location: tecnico/dashboard.php");
    } else {
        header("Location: user/dashboard.php");
    }
    exit();
}

// Proceso de Login
if ($_POST) {
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
        
        // Redirección corregida
        if ($user['rol_nombre'] == 'Administrador') {
            header("Location: admin/dashboard.php");
        } elseif ($user['rol_nombre'] == 'Tecnico') {
            header("Location: tecnico/dashboard.php");
        } else {
            header("Location: user/dashboard.php");
        }
        exit();
    } else {
        $error = "Credenciales incorrectas";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistema de Tickets IT</title>
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
            --text-muted: #94a3b8;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
            --info: #06b6d4;
        }
        
        body {
            background: linear-gradient(135deg, var(--dark-bg) 0%, #1e293b 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            font-family: 'Inter', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .login-container {
            max-width: 400px;
            margin: 0 auto;
            padding: 2rem;
        }
        
        .login-card {
            background: var(--dark-card);
            border: 1px solid var(--dark-border);
            border-radius: 16px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
            backdrop-filter: blur(10px);
            overflow: hidden;
            transition: transform 0.3s ease;
        }
        
        .login-card:hover {
            transform: translateY(-5px);
        }
        
        .login-header {
            background: linear-gradient(135deg, var(--primary) 0%, #1d4ed8 100%);
            color: white;
            padding: 2.5rem 2rem;
            text-align: center;
            border-bottom: 1px solid var(--dark-border);
            position: relative;
            overflow: hidden;
        }
        
        .login-header::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: linear-gradient(45deg, transparent, rgba(255,255,255,0.1), transparent);
            transform: rotate(45deg);
            animation: shine 3s infinite;
        }
        
        @keyframes shine {
            0% { transform: translateX(-100%) translateY(-100%) rotate(45deg); }
            100% { transform: translateX(100%) translateY(100%) rotate(45deg); }
        }
        
        .login-body {
            padding: 2.5rem 2rem;
        }
        
        .form-control {
            background: #1e293b;
            border: 1px solid #334155;
            color: var(--text-light);
            padding: 0.75rem 1rem;
            border-radius: 10px;
            font-size: 0.95rem;
            transition: all 0.3s ease;
        }
        
        .form-control:focus {
            background: #1e293b;
            border-color: var(--primary);
            color: var(--text-light);
            box-shadow: 0 0 0 0.2rem rgba(59, 130, 246, 0.25);
            transform: translateY(-2px);
        }
        
        .form-label {
            color: var(--text-light);
            font-weight: 600;
            margin-bottom: 0.5rem;
            font-size: 0.9rem;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--primary) 0%, #1d4ed8 100%);
            border: none;
            padding: 0.875rem;
            border-radius: 10px;
            font-weight: 600;
            font-size: 1rem;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .btn-primary:hover {
            background: linear-gradient(135deg, var(--primary-hover) 0%, #1e40af 100%);
            transform: translateY(-3px);
            box-shadow: 0 15px 30px rgba(59, 130, 246, 0.4);
        }
        
        .btn-primary:active {
            transform: translateY(-1px);
        }
        
        .alert {
            border-radius: 10px;
            border: none;
            padding: 1rem 1.25rem;
            font-weight: 500;
        }
        
        .alert-info {
            background: rgba(6, 182, 212, 0.1);
            color: #06b6d4;
            border: 1px solid rgba(6, 182, 212, 0.2);
        }
        
        .alert-danger {
            background: rgba(239, 68, 68, 0.1);
            color: #ef4444;
            border: 1px solid rgba(239, 68, 68, 0.2);
        }
        
        .system-info {
            text-align: center;
            margin-top: 2.5rem;
            color: var(--text-muted);
        }
        
        .system-info h5 {
            color: var(--text-light);
            margin-bottom: 1rem;
            font-weight: 600;
        }
        
        .feature-list {
            list-style: none;
            padding: 0;
            margin: 1.5rem 0;
        }
        
        .feature-list li {
            padding: 0.5rem 0;
            color: var(--text-muted);
            font-size: 0.9rem;
        }
        
        .feature-list li i {
            color: var(--primary);
            margin-right: 0.75rem;
            font-size: 0.8rem;
        }
        
        .logo {
            font-size: 3rem;
            margin-bottom: 1rem;
            background: linear-gradient(135deg, #fff 0%, #cbd5e1 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            display: inline-block;
        }
        
        .input-group-text {
            background: #1e293b;
            border: 1px solid #334155;
            border-right: none;
            color: var(--text-muted);
        }
        
        .form-control:focus + .input-group-text {
            border-color: var(--primary);
            color: var(--primary);
        }
        
        .password-toggle {
            background: #1e293b;
            border: 1px solid #334155;
            border-left: none;
            color: var(--text-muted);
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .password-toggle:hover {
            color: var(--primary);
        }
        
        .floating-label {
            position: relative;
            margin-bottom: 1.5rem;
        }
        
        .floating-label .form-control {
            padding-top: 1.5rem;
        }
        
        .floating-label .form-label {
            position: absolute;
            top: 0.75rem;
            left: 1rem;
            font-size: 0.8rem;
            opacity: 0.7;
            transition: all 0.3s ease;
            pointer-events: none;
        }
        
        .floating-label .form-control:focus + .form-label,
        .floating-label .form-control:not(:placeholder-shown) + .form-label {
            top: 0.25rem;
            left: 0.75rem;
            font-size: 0.7rem;
            opacity: 1;
            color: var(--primary);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="login-container">
            <div class="login-card">
                <div class="login-header">
                    <div class="logo">
                        <i class="fas fa-ticket-alt"></i>
                    </div>
                    <h3 class="fw-bold">Sistema de Tickets IT</h3>
                    <p class="mb-0 opacity-85">Gestión de soporte técnico</p>
                </div>
                
                <div class="login-body">
                    <?php if (isset($info)): ?>
                        <div class="alert alert-info mb-4">
                            <i class="fas fa-info-circle me-2"></i><?php echo $info; ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger mb-4">
                            <i class="fas fa-exclamation-triangle me-2"></i><?php echo $error; ?>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST">
                        <div class="floating-label">
                            <input type="email" name="email" class="form-control" required 
                                   placeholder=" " value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                            <label class="form-label">
                                <i class="fas fa-envelope me-1"></i>Email
                            </label>
                        </div>
                        
                        <div class="floating-label">
                            <div class="input-group">
                                <input type="password" name="password" id="password" class="form-control" required 
                                       placeholder=" ">
                                <span class="input-group-text password-toggle" onclick="togglePassword()">
                                    <i class="fas fa-eye" id="password-icon"></i>
                                </span>
                            </div>
                            <label class="form-label">
                                <i class="fas fa-lock me-1"></i>Contraseña
                            </label>
                        </div>
                        
                        <button type="submit" class="btn btn-primary w-100 py-2 mt-3">
                            <i class="fas fa-sign-in-alt me-2"></i>Iniciar Sesión
                        </button>
                    </form>
                    
                 <!--    <div class="system-info">
                        <h5>Sistema de Gestión de Tickets</h5>
                        <ul class="feature-list">
                            <li><i class="fas fa-check-circle"></i> Creación y seguimiento de tickets</li>
                            <li><i class="fas fa-check-circle"></i> Asignación a técnicos especializados</li>
                            <li><i class="fas fa-check-circle"></i> Comunicación en tiempo real</li>
                            <li><i class="fas fa-check-circle"></i> Reportes y estadísticas</li>
                        </ul>
                        <small class="text-muted">
                            <i class="fas fa-shield-alt me-1"></i>
                            Acceso restringido al personal autorizado
                        </small>
                    </div> -->
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const passwordIcon = document.getElementById('password-icon');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                passwordIcon.classList.remove('fa-eye');
                passwordIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                passwordIcon.classList.remove('fa-eye-slash');
                passwordIcon.classList.add('fa-eye');
            }
        }
        
        // Efecto de focus en los inputs
        document.addEventListener('DOMContentLoaded', function() {
            const inputs = document.querySelectorAll('.form-control');
            inputs.forEach(input => {
                input.addEventListener('focus', function() {
                    this.parentElement.classList.add('focused');
                });
                
                input.addEventListener('blur', function() {
                    if (this.value === '') {
                        this.parentElement.classList.remove('focused');
                    }
                });
                
                // Check initial state
                if (input.value !== '') {
                    input.parentElement.classList.add('focused');
                }
            });
        });
    </script>
</body>
</html>
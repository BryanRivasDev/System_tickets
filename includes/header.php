<?php
// includes/header.php

// Iniciar sesión si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Incluir funciones de autenticación solo si no están incluidas
if (!function_exists('isLoggedIn')) {
    include 'auth.php';
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Tickets IT</title>
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
            background-color: var(--dark-bg);
            color: var(--text-light);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .navbar {
            background: linear-gradient(135deg, var(--dark-card) 0%, #1e293b 100%) !important;
            border-bottom: 1px solid var(--dark-border);
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
            padding: 0.75rem 0;
        }
        
        .navbar-brand {
            color: var(--text-light) !important;
            font-weight: 700;
            font-size: 1.3rem;
        }
        
        .nav-link {
            color: var(--text-muted) !important;
            transition: all 0.3s ease;
            border-radius: 6px;
            margin: 0 2px;
            padding: 0.5rem 1rem !important;
        }
        
        .nav-link:hover {
            color: var(--text-light) !important;
            background: rgba(59, 130, 246, 0.1);
        }
        
        .nav-link.active {
            color: var(--primary) !important;
            background: rgba(59, 130, 246, 0.15);
        }
        
        .dropdown-menu {
            background: var(--dark-card);
            border: 1px solid var(--dark-border);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.3);
            border-radius: 8px;
            padding: 0.5rem;
            min-width: 200px;
        }
        
        .dropdown-item {
            color: var(--text-muted);
            transition: all 0.3s ease;
            border-radius: 6px;
            padding: 0.75rem 1rem;
            margin: 0.1rem 0;
            display: flex;
            align-items: center;
        }
        
        .dropdown-item:hover {
            background: rgba(59, 130, 246, 0.1);
            color: var(--text-light);
        }
        
        .dropdown-item.text-danger:hover {
            background: rgba(239, 68, 68, 0.1);
            color: var(--danger) !important;
        }
        
        .badge {
            font-weight: 600;
            font-size: 0.7em;
            border-radius: 6px;
        }
        
        .user-avatar {
            width: 32px;
            height: 32px;
            background: linear-gradient(135deg, var(--primary), #8b5cf6);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 0.9rem;
            margin-right: 0.5rem;
        }
        
        .dropdown-toggle::after {
            margin-left: 0.5rem;
        }
        
        .navbar-toggler {
            border: 1px solid var(--dark-border);
        }
        
        .navbar-toggler:focus {
            box-shadow: 0 0 0 0.1rem rgba(59, 130, 246, 0.25);
        }
        
        .user-info {
            display: flex;
            flex-direction: column;
            line-height: 1.2;
        }
        
        .user-name {
            font-weight: 600;
            font-size: 0.9rem;
        }
        
        .user-role {
            font-size: 0.75rem;
            color: var(--text-muted);
        }
        
        /* Asegurar que el dropdown funcione correctamente */
        .dropdown:hover .dropdown-menu {
            display: block;
            margin-top: 0;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="../index.php">
                <i class="fas fa-ticket-alt me-2"></i>Sistema Tickets IT
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <?php if (isLoggedIn()): ?>
                        <?php if (isAdmin()): ?>
                            <!-- Menú solo para Administradores -->
                           <!--  <li class="nav-item">
                                <a class="nav-link" href="dashboard.php">
                                    <i class="fas fa-tachometer-alt me-1"></i> Dashboard
                                </a>
                            </li> -->
                           <!--  <li class="nav-item">
                                <a class="nav-link" href="tickets.php">
                                    <i class="fas fa-tickets me-1"></i> Todos los Tickets
                                </a>
                            </li> -->
                            <li class="nav-item">
                                <a class="nav-link" href="usuarios.php">
                                    <i class="fas fa-users me-1"></i> Usuarios
                                </a>
                            </li>
                        <?php elseif (isTecnico()): ?>
                            <!-- Menú solo para Técnicos -->
                            <li class="nav-item">
                                <a class="nav-link" href="../tecnico/dashboard.php">
                                    <i class="fas fa-tachometer-alt me-1"></i> Dashboard
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="../tecnico/mis_tickets.php">
                                    <i class="fas fa-tasks me-1"></i> Mis Tickets
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="../tecnico/reportes.php">
                                    <i class="fas fa-tachometer-alt me-1"></i> Resportes
                                </a>
                            </li>  
                        <?php else: ?>
                            <!-- Menú para Usuarios normales -->
                            <!-- <li class="nav-item">
                                <a class="nav-link" href="dashboard.php">
                                    <i class="fas fa-tachometer-alt me-1"></i> Mi Dashboard
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="crear_ticket.php">
                                    <i class="fas fa-plus-circle me-1"></i> Nuevo Ticket
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="mis_tickets.php">
                                    <i class="fas fa-list me-1"></i> Mis Tickets
                                </a>
                            </li> -->
                        <?php endif; ?>
                    <?php endif; ?>
                </ul>
                
                <ul class="navbar-nav ms-auto">
                    <?php if (isLoggedIn()): ?>
                        <!-- Usuario logueado - Dropdown -->
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <div class="user-avatar">
                                    <i class="fas fa-user"></i>
                                </div>
                                <div class="user-info">
                                    <span class="user-name"><?php echo htmlspecialchars(getUserName()); ?></span>
                                    <span class="user-role">
                                        <span class="badge" style="background: <?php 
                                            echo isAdmin() ? 'var(--danger)' : (isTecnico() ? 'var(--warning)' : 'var(--info)'); 
                                        ?>;">
                                            <?php echo getUserRole(); ?>
                                        </span>
                                    </span>
                                </div>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                                <li>
                                    <a class="dropdown-item" href="../user/profile.php">
                                        <i class="fas fa-user-circle me-2"></i> Mi Perfil
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="../user/mis_tickets.php">
                                        <i class="fas fa-ticket-alt me-2"></i> Mis Tickets
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider my-2"></li>
                                <li>
                                    <a class="dropdown-item text-danger" href="../logout.php">
                                        <i class="fas fa-sign-out-alt me-2"></i> Cerrar Sesión
                                    </a>
                                </li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <!-- Usuario no logueado -->
                        <li class="nav-item">
                            <a class="nav-link" href="../login.php">
                                <i class="fas fa-sign-in-alt me-1"></i> Iniciar Sesión
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Script de Bootstrap - DEBE ESTAR PRESENTE -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
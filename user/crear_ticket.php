<?php
include '../config/database.php';
include '../includes/auth.php';
redirectIfNotLoggedIn();

if ($_POST) {
    $titulo = $_POST['titulo'];
    $descripcion = $_POST['descripcion'];
    $categoria_id = $_POST['categoria_id'];
    $prioridad = $_POST['prioridad'];
    $user_id = $_SESSION['user_id'];
    
    $stmt = $pdo->prepare("INSERT INTO tickets (titulo, descripcion, usuario_id, categoria_id, prioridad) 
                          VALUES (?, ?, ?, ?, ?)");
    if ($stmt->execute([$titulo, $descripcion, $user_id, $categoria_id, $prioridad])) {
        $ticket_id = $pdo->lastInsertId();
        
        // Registrar en historial
        $historial = $pdo->prepare("INSERT INTO historial_tickets (ticket_id, usuario_id, accion, descripcion) 
                                   VALUES (?, ?, 'Creación', 'Ticket creado por el usuario')");
        $historial->execute([$ticket_id, $user_id]);
        
        header("Location: dashboard.php?success=Ticket creado correctamente");
        exit();
    }
}

// Obtener categorías
$categorias = $pdo->query("SELECT * FROM categorias_tickets")->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Nuevo Ticket</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --dark-bg: #0f172a;
            --dark-card: #1e293b;
            --dark-card-light: #2d3748;
            --dark-border: #374151;
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
            font-family: 'Inter', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .form-container {
            max-width: 800px;
            margin: 0 auto;
        }
        
        .form-card {
            background: var(--dark-card);
            border: 1px solid var(--dark-border);
            border-radius: 16px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
            overflow: hidden;
        }
        
        .form-header {
            background: linear-gradient(135deg, var(--primary) 0%, #1d4ed8 100%);
            color: white;
            padding: 2rem;
            text-align: center;
            border-bottom: 1px solid var(--dark-border);
        }
        
        .form-body {
            padding: 2.5rem;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-label {
            color: var(--text-light);
            font-weight: 600;
            margin-bottom: 0.75rem;
            font-size: 0.95rem;
            display: flex;
            align-items: center;
        }
        
        .form-label i {
            margin-right: 0.5rem;
            width: 20px;
            color: var(--primary);
        }
        
        .form-control {
            background: var(--dark-card-light);
            border: 1px solid var(--dark-border);
            color: var(--text-light);
            padding: 0.75rem 1rem;
            border-radius: 10px;
            font-size: 0.95rem;
            transition: all 0.3s ease;
        }
        
        .form-control:focus {
            background: var(--dark-card-light);
            border-color: var(--primary);
            color: var(--text-light);
            box-shadow: 0 0 0 0.2rem rgba(59, 130, 246, 0.25);
            transform: translateY(-2px);
        }
        
        .form-select {
            background: var(--dark-card-light);
            border: 1px solid var(--dark-border);
            color: var(--text-light);
            padding: 0.75rem 1rem;
            border-radius: 10px;
            font-size: 0.95rem;
            transition: all 0.3s ease;
        }
        
        .form-select:focus {
            background: var(--dark-card-light);
            border-color: var(--primary);
            color: var(--text-light);
            box-shadow: 0 0 0 0.2rem rgba(59, 130, 246, 0.25);
            transform: translateY(-2px);
        }
        
        textarea.form-control {
            min-height: 150px;
            resize: vertical;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-hover) 100%);
            border: none;
            border-radius: 10px;
            font-weight: 600;
            padding: 0.875rem 2rem;
            font-size: 1rem;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .btn-primary:hover {
            background: linear-gradient(135deg, var(--primary-hover) 0%, #1e40af 100%);
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(59, 130, 246, 0.3);
        }
        
        .btn-secondary {
            background: transparent;
            border: 2px solid var(--dark-border);
            color: var(--text-muted);
            border-radius: 10px;
            font-weight: 600;
            padding: 0.875rem 2rem;
            font-size: 1rem;
            transition: all 0.3s ease;
        }
        
        .btn-secondary:hover {
            background: var(--dark-border);
            color: var(--text-light);
            transform: translateY(-2px);
        }
        
        .priority-option {
            display: flex;
            align-items: center;
            padding: 0.5rem;
            border-radius: 8px;
            margin-bottom: 0.5rem;
            cursor: pointer;
            transition: all 0.3s ease;
            border: 2px solid transparent;
        }
        
        .priority-option:hover {
            background: var(--dark-card-light);
        }
        
        .priority-option.selected {
            border-color: var(--primary);
            background: rgba(59, 130, 246, 0.1);
        }
        
        .priority-badge {
            width: 20px;
            height: 20px;
            border-radius: 50%;
            margin-right: 0.75rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.7rem;
            color: white;
        }
        
        .priority-label {
            flex: 1;
            font-weight: 500;
        }
        
        .priority-description {
            font-size: 0.8rem;
            color: var(--text-muted);
            margin-top: 0.25rem;
        }
        
        .form-actions {
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
            padding-top: 1.5rem;
            border-top: 1px solid var(--dark-border);
        }
        
        .character-count {
            text-align: right;
            font-size: 0.8rem;
            color: var(--text-muted);
            margin-top: 0.5rem;
        }
        
        .form-section {
            background: var(--dark-card-light);
            padding: 1.5rem;
            border-radius: 10px;
            margin-bottom: 1.5rem;
            border-left: 4px solid var(--primary);
        }
        
        .form-section-title {
            color: var(--text-light);
            font-weight: 600;
            margin-bottom: 1rem;
            font-size: 1.1rem;
        }
        
        .input-with-icon {
            position: relative;
        }
        
        .input-icon {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
            z-index: 2;
        }
        
        .input-with-icon .form-control {
            padding-left: 3rem;
        }
        
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }
        
        .btn-primary:active {
            animation: pulse 0.3s ease;
        }
    </style>
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="container mt-4">
        <div class="form-container">
            <div class="form-card">
                <div class="form-header">
                    <h1 class="h3 mb-2">
                        <i class="fas fa-plus-circle me-2"></i>Crear Nuevo Ticket
                    </h1>
                    <p class="mb-0 opacity-85">Describe el problema que necesitas resolver</p>
                </div>
                
                <div class="form-body">
                    <form method="POST" id="ticketForm">
                        <!-- Sección de Información Básica -->
                        <div class="form-section">
                            <div class="form-section-title">
                                <i class="fas fa-info-circle me-2"></i>Información Básica
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label" for="titulo">
                                    <i class="fas fa-heading"></i>Título del Ticket
                                </label>
                                <div class="input-with-icon">
                                    <i class="fas fa-pencil-alt input-icon"></i>
                                    <input type="text" name="titulo" id="titulo" class="form-control" 
                                           placeholder="Ej: Problema con el correo electrónico" required
                                           maxlength="100">
                                </div>
                                <div class="character-count">
                                    <span id="titleCount">0</span>/100 caracteres
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label" for="categoria_id">
                                    <i class="fas fa-tag"></i>Categoría
                                </label>
                                <select name="categoria_id" id="categoria_id" class="form-select" required>
                                    <option value="">Selecciona una categoría...</option>
                                    <?php foreach ($categorias as $categoria): ?>
                                    <option value="<?php echo $categoria['id']; ?>">
                                        <?php echo htmlspecialchars($categoria['nombre']); ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        
                        <!-- Sección de Prioridad -->
                        <div class="form-section">
                            <div class="form-section-title">
                                <i class="fas fa-exclamation-circle me-2"></i>Nivel de Prioridad
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">
                                    <i class="fas fa-flag"></i>Selecciona la prioridad
                                </label>
                                <div id="priorityOptions">
                                    <div class="priority-option" data-value="Baja">
                                        <div class="priority-badge bg-success">
                                            <i class="fas fa-arrow-down"></i>
                                        </div>
                                        <div>
                                            <div class="priority-label">Baja</div>
                                            <div class="priority-description">Problema menor, sin impacto inmediato</div>
                                        </div>
                                    </div>
                                    <div class="priority-option" data-value="Media">
                                        <div class="priority-badge bg-info">
                                            <i class="fas fa-minus"></i>
                                        </div>
                                        <div>
                                            <div class="priority-label">Media</div>
                                            <div class="priority-description">Problema moderado, afecta algunas funciones</div>
                                        </div>
                                    </div>
                                    <div class="priority-option" data-value="Alta">
                                        <div class="priority-badge bg-warning">
                                            <i class="fas fa-arrow-up"></i>
                                        </div>
                                        <div>
                                            <div class="priority-label">Alta</div>
                                            <div class="priority-description">Problema significativo, afecta productividad</div>
                                        </div>
                                    </div>
                                    <div class="priority-option" data-value="Urgente">
                                        <div class="priority-badge bg-danger">
                                            <i class="fas fa-fire"></i>
                                        </div>
                                        <div>
                                            <div class="priority-label">Urgente</div>
                                            <div class="priority-description">Problema crítico, bloquea operaciones</div>
                                        </div>
                                    </div>
                                </div>
                                <input type="hidden" name="prioridad" id="prioridad" value="Media" required>
                            </div>
                        </div>
                        
                        <!-- Sección de Descripción -->
                        <div class="form-section">
                            <div class="form-section-title">
                                <i class="fas fa-align-left me-2"></i>Descripción Detallada
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label" for="descripcion">
                                    <i class="fas fa-file-alt"></i>Describe el problema
                                </label>
                                <textarea name="descripcion" id="descripcion" class="form-control" 
                                          placeholder="Por favor, describe el problema con el mayor detalle posible. Incluye pasos para reproducirlo, mensajes de error, y cualquier información relevante." 
                                          required></textarea>
                                <div class="character-count">
                                    <span id="descCount">0</span> caracteres
                                </div>
                            </div>
                        </div>
                        
                        <!-- Acciones del Formulario -->
                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary flex-fill">
                                <i class="fas fa-paper-plane me-2"></i>Crear Ticket
                            </button>
                            <a href="dashboard.php" class="btn btn-secondary">
                                <i class="fas fa-times me-2"></i>Cancelar
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Selección de prioridad visual
        document.addEventListener('DOMContentLoaded', function() {
            const priorityOptions = document.querySelectorAll('.priority-option');
            const priorityInput = document.getElementById('prioridad');
            
            // Seleccionar "Media" por defecto
            priorityOptions[1].classList.add('selected');
            
            priorityOptions.forEach(option => {
                option.addEventListener('click', function() {
                    // Remover selección anterior
                    priorityOptions.forEach(opt => opt.classList.remove('selected'));
                    
                    // Agregar selección actual
                    this.classList.add('selected');
                    
                    // Actualizar valor del input
                    const value = this.getAttribute('data-value');
                    priorityInput.value = value;
                });
            });
            
            // Contador de caracteres para título
            const titleInput = document.getElementById('titulo');
            const titleCount = document.getElementById('titleCount');
            
            titleInput.addEventListener('input', function() {
                titleCount.textContent = this.value.length;
            });
            
            // Contador de caracteres para descripción
            const descInput = document.getElementById('descripcion');
            const descCount = document.getElementById('descCount');
            
            descInput.addEventListener('input', function() {
                descCount.textContent = this.value.length;
            });
            
            // Validación del formulario
            const form = document.getElementById('ticketForm');
            form.addEventListener('submit', function(e) {
                const titulo = titleInput.value.trim();
                const descripcion = descInput.value.trim();
                
                if (titulo.length < 5) {
                    e.preventDefault();
                    alert('El título debe tener al menos 5 caracteres');
                    titleInput.focus();
                    return;
                }
                
                if (descripcion.length < 10) {
                    e.preventDefault();
                    alert('La descripción debe tener al menos 10 caracteres');
                    descInput.focus();
                    return;
                }
            });
        });
    </script>
</body>
</html>
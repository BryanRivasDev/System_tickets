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
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="container mt-4">
        <h2>Crear Nuevo Ticket</h2>
        
        <form method="POST">
            <div class="mb-3">
                <label>Título:</label>
                <input type="text" name="titulo" class="form-control" required>
            </div>
            
            <div class="mb-3">
                <label>Categoría:</label>
                <select name="categoria_id" class="form-control" required>
                    <option value="">Seleccionar categoría</option>
                    <?php foreach ($categorias as $categoria): ?>
                    <option value="<?php echo $categoria['id']; ?>"><?php echo $categoria['nombre']; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="mb-3">
                <label>Prioridad:</label>
                <select name="prioridad" class="form-control" required>
                    <option value="Baja">Baja</option>
                    <option value="Media" selected>Media</option>
                    <option value="Alta">Alta</option>
                    <option value="Urgente">Urgente</option>
                </select>
            </div>
            
            <div class="mb-3">
                <label>Descripción del problema:</label>
                <textarea name="descripcion" class="form-control" rows="5" required></textarea>
            </div>
            
            <button type="submit" class="btn btn-primary">Crear Ticket</button>
            <a href="dashboard.php" class="btn btn-secondary">Cancelar</a>
        </form>
    </div>
</body>
</html>
<?php
include '../config/database.php';
include '../includes/auth.php';
redirectIfNotLoggedIn();
redirectIfNotTecnico();

$tecnico_id = $_SESSION['user_id'];

// Obtener estadísticas para filtros
$categorias = $pdo->query("SELECT * FROM categorias_tickets ORDER BY nombre")->fetchAll();
$estados = $pdo->query("SELECT * FROM estados_ticket ORDER BY id")->fetchAll();

// Procesar filtros
$filtros = [];
$params = [$tecnico_id];
$where = "WHERE t.tecnico_asignado = ?";

if (isset($_GET['fecha_inicio']) && !empty($_GET['fecha_inicio'])) {
    $where .= " AND t.fecha_creacion >= ?";
    $params[] = $_GET['fecha_inicio'];
    $filtros['fecha_inicio'] = $_GET['fecha_inicio'];
}

if (isset($_GET['fecha_fin']) && !empty($_GET['fecha_fin'])) {
    $where .= " AND t.fecha_creacion <= ?";
    $params[] = $_GET['fecha_fin'] . ' 23:59:59';
    $filtros['fecha_fin'] = $_GET['fecha_fin'];
}

if (isset($_GET['categoria_id']) && !empty($_GET['categoria_id'])) {
    $where .= " AND t.categoria_id = ?";
    $params[] = $_GET['categoria_id'];
    $filtros['categoria_id'] = $_GET['categoria_id'];
}

if (isset($_GET['estado_id']) && !empty($_GET['estado_id'])) {
    $where .= " AND t.estado_id = ?";
    $params[] = $_GET['estado_id'];
    $filtros['estado_id'] = $_GET['estado_id'];
}

if (isset($_GET['prioridad']) && !empty($_GET['prioridad'])) {
    $where .= " AND t.prioridad = ?";
    $params[] = $_GET['prioridad'];
    $filtros['prioridad'] = $_GET['prioridad'];
}

// Obtener tickets filtrados
$query = "SELECT t.*, u.nombre as usuario_nombre, c.nombre as categoria, e.nombre as estado 
          FROM tickets t 
          JOIN usuarios u ON t.usuario_id = u.id 
          JOIN categorias_tickets c ON t.categoria_id = c.id 
          JOIN estados_ticket e ON t.estado_id = e.id 
          $where 
          ORDER BY t.fecha_creacion DESC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$tickets = $stmt->fetchAll();

// Estadísticas para mostrar
$total_tickets = count($tickets);
$tickets_abiertos = 0;
$tickets_resueltos = 0;
$tickets_urgentes = 0;

foreach ($tickets as $ticket) {
    if ($ticket['estado_id'] == 4) { // Resuelto
        $tickets_resueltos++;
    } else {
        $tickets_abiertos++;
    }
    
    if ($ticket['prioridad'] == 'Urgente') {
        $tickets_urgentes++;
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reportes - Sistema de Tickets IT</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary: #1a2a6c;
            --secondary: #3a4a9c;
            --success: #28a745;
            --warning: #ffc107;
            --danger: #dc3545;
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
        }
        
        .stat-card {
            border-left: 4px solid;
            transition: transform 0.3s;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
        }
        
        .btn-primary {
            background: var(--primary);
            border-color: var(--primary);
        }
        
        .btn-primary:hover {
            background: var(--secondary);
            border-color: var(--secondary);
        }
        
        .badge-priority {
            font-size: 0.75em;
        }
        
        .table-hover tbody tr:hover {
            background-color: rgba(26, 42, 108, 0.05);
        }
        
        .export-buttons .btn {
            margin-right: 10px;
            margin-bottom: 10px;
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

                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header bg-primary text-white">
                                <h4 class="mb-0"><i class="fas fa-chart-bar"></i> Reportes de Tickets</h4>
                            </div>
                            <div class="card-body">
                                <!-- Filtros -->
                                <form method="GET" action="" class="mb-4">
                                    <div class="row g-3">
                                        <div class="col-md-3">
                                            <label for="fecha_inicio" class="form-label">Fecha Inicio</label>
                                            <input type="date" class="form-control" id="fecha_inicio" name="fecha_inicio" 
                                                   value="<?php echo $filtros['fecha_inicio'] ?? ''; ?>">
                                        </div>
                                        <div class="col-md-3">
                                            <label for="fecha_fin" class="form-label">Fecha Fin</label>
                                            <input type="date" class="form-control" id="fecha_fin" name="fecha_fin"
                                                   value="<?php echo $filtros['fecha_fin'] ?? ''; ?>">
                                        </div>
                                        <div class="col-md-2">
                                            <label for="categoria_id" class="form-label">Categoría</label>
                                            <select class="form-select" id="categoria_id" name="categoria_id">
                                                <option value="">Todas</option>
                                                <?php foreach ($categorias as $categoria): ?>
                                                    <option value="<?php echo $categoria['id']; ?>" 
                                                        <?php echo ($filtros['categoria_id'] ?? '') == $categoria['id'] ? 'selected' : ''; ?>>
                                                        <?php echo $categoria['nombre']; ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="col-md-2">
                                            <label for="estado_id" class="form-label">Estado</label>
                                            <select class="form-select" id="estado_id" name="estado_id">
                                                <option value="">Todos</option>
                                                <?php foreach ($estados as $estado): ?>
                                                    <option value="<?php echo $estado['id']; ?>" 
                                                        <?php echo ($filtros['estado_id'] ?? '') == $estado['id'] ? 'selected' : ''; ?>>
                                                        <?php echo $estado['nombre']; ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="col-md-2">
                                            <label for="prioridad" class="form-label">Prioridad</label>
                                            <select class="form-select" id="prioridad" name="prioridad">
                                                <option value="">Todas</option>
                                                <option value="Urgente" <?php echo ($filtros['prioridad'] ?? '') == 'Urgente' ? 'selected' : ''; ?>>Urgente</option>
                                                <option value="Alta" <?php echo ($filtros['prioridad'] ?? '') == 'Alta' ? 'selected' : ''; ?>>Alta</option>
                                                <option value="Media" <?php echo ($filtros['prioridad'] ?? '') == 'Media' ? 'selected' : ''; ?>>Media</option>
                                                <option value="Baja" <?php echo ($filtros['prioridad'] ?? '') == 'Baja' ? 'selected' : ''; ?>>Baja</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="row mt-3">
                                        <div class="col-12">
                                            <button type="submit" class="btn btn-primary">
                                                <i class="fas fa-filter"></i> Aplicar Filtros
                                            </button>
                                            <a href="reportes.php" class="btn btn-outline-secondary">
                                                <i class="fas fa-times"></i> Limpiar
                                            </a>
                                        </div>
                                    </div>
                                </form>

                                <!-- Estadísticas -->
                                <div class="row mb-4">
                                    <div class="col-xl-3 col-md-6 mb-3">
                                        <div class="card text-white bg-primary stat-card h-100">
                                            <div class="card-body">
                                                <div class="d-flex justify-content-between">
                                                    <div>
                                                        <h3><?php echo $total_tickets; ?></h3>
                                                        <p class="mb-0">Total Tickets</p>
                                                    </div>
                                                    <div class="align-self-center">
                                                        <i class="fas fa-ticket-alt fa-2x opacity-50"></i>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-xl-3 col-md-6 mb-3">
                                        <div class="card text-white bg-warning stat-card h-100">
                                            <div class="card-body">
                                                <div class="d-flex justify-content-between">
                                                    <div>
                                                        <h3><?php echo $tickets_abiertos; ?></h3>
                                                        <p class="mb-0">Abiertos</p>
                                                    </div>
                                                    <div class="align-self-center">
                                                        <i class="fas fa-clock fa-2x opacity-50"></i>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-xl-3 col-md-6 mb-3">
                                        <div class="card text-white bg-success stat-card h-100">
                                            <div class="card-body">
                                                <div class="d-flex justify-content-between">
                                                    <div>
                                                        <h3><?php echo $tickets_resueltos; ?></h3>
                                                        <p class="mb-0">Resueltos</p>
                                                    </div>
                                                    <div class="align-self-center">
                                                        <i class="fas fa-check-circle fa-2x opacity-50"></i>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-xl-3 col-md-6 mb-3">
                                        <div class="card text-white bg-danger stat-card h-100">
                                            <div class="card-body">
                                                <div class="d-flex justify-content-between">
                                                    <div>
                                                        <h3><?php echo $tickets_urgentes; ?></h3>
                                                        <p class="mb-0">Urgentes</p>
                                                    </div>
                                                    <div class="align-self-center">
                                                        <i class="fas fa-exclamation-triangle fa-2x opacity-50"></i>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Botones de Exportación -->
                                <div class="row mb-4">
                                    <div class="col-12">
                                        <div class="card">
                                            <div class="card-header bg-info text-white">
                                                <h5 class="mb-0"><i class="fas fa-download"></i> Exportar Reporte</h5>
                                            </div>
                                            <div class="card-body export-buttons">
                                                <?php
                                                $query_string = http_build_query($filtros);
                                                $pdf_url = "generar_pdf.php?" . $query_string;
                                                ?>
                                                <a href="<?php echo $pdf_url; ?>" class="btn btn-danger" target="_blank">
                                                    <i class="fas fa-file-pdf"></i> Descargar PDF
                                                </a>
                                                <button class="btn btn-success" onclick="exportToExcel()">
                                                    <i class="fas fa-file-excel"></i> Descargar Excel
                                                </button>
                                                <button class="btn btn-primary" onclick="window.print()">
                                                    <i class="fas fa-print"></i> Imprimir Reporte
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Tabla de Resultados -->
                                <?php if ($total_tickets > 0): ?>
                                <div class="card">
                                    <div class="card-header bg-secondary text-white">
                                        <h5 class="mb-0">
                                            <i class="fas fa-table"></i> Tickets Encontrados (<?php echo $total_tickets; ?>)
                                        </h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table table-hover" id="tablaReportes">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th>ID</th>
                                                        <th>Título</th>
                                                        <th>Usuario</th>
                                                        <th>Categoría</th>
                                                        <th>Prioridad</th>
                                                        <th>Estado</th>
                                                        <th>Fecha Creación</th>
                                                        <th>Días Abierto</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($tickets as $ticket): 
                                                        $fecha_creacion = new DateTime($ticket['fecha_creacion']);
                                                        $hoy = new DateTime();
                                                        $dias_abierto = $hoy->diff($fecha_creacion)->days;
                                                    ?>
                                                    <tr>
                                                        <td><strong>#<?php echo $ticket['id']; ?></strong></td>
                                                        <td><?php echo htmlspecialchars($ticket['titulo']); ?></td>
                                                        <td><?php echo htmlspecialchars($ticket['usuario_nombre']); ?></td>
                                                        <td><?php echo $ticket['categoria']; ?></td>
                                                        <td>
                                                            <span class="badge badge-priority bg-<?php 
                                                                switch($ticket['prioridad']) {
                                                                    case 'Urgente': echo 'danger'; break;
                                                                    case 'Alta': echo 'warning'; break;
                                                                    case 'Media': echo 'info'; break;
                                                                    case 'Baja': echo 'success'; break;
                                                                }
                                                            ?>"><?php echo $ticket['prioridad']; ?></span>
                                                        </td>
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
                                                        <td><?php echo date('d/m/Y', strtotime($ticket['fecha_creacion'])); ?></td>
                                                        <td>
                                                            <span class="badge bg-<?php echo $dias_abierto > 7 ? 'danger' : ($dias_abierto > 3 ? 'warning' : 'success'); ?>">
                                                                <?php echo $dias_abierto; ?> días
                                                            </span>
                                                        </td>
                                                    </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                                <?php else: ?>
                                <div class="text-center py-5">
                                    <i class="fas fa-search fa-4x text-muted mb-3"></i>
                                    <h5 class="text-muted">No se encontraron tickets</h5>
                                    <p class="text-muted">Prueba con otros criterios de búsqueda</p>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function exportToExcel() {
            // Crear tabla HTML para Excel
            let tabla = document.getElementById('tablaReportes');
            let html = tabla.outerHTML;
            
            // Crear archivo Excel
            let blob = new Blob([html], { type: 'application/vnd.ms-excel' });
            let url = URL.createObjectURL(blob);
            let a = document.createElement('a');
            a.href = url;
            a.download = 'reporte_tickets_<?php echo date('Y-m-d'); ?>.xls';
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            URL.revokeObjectURL(url);
        }

        // Establecer fecha por defecto (últimos 30 días)
        document.addEventListener('DOMContentLoaded', function() {
            let fechaFin = document.getElementById('fecha_fin');
            let fechaInicio = document.getElementById('fecha_inicio');
            
            if (!fechaFin.value) {
                let hoy = new Date();
                fechaFin.value = hoy.toISOString().split('T')[0];
                
                let hace30Dias = new Date();
                hace30Dias.setDate(hace30Dias.getDate() - 30);
                fechaInicio.value = hace30Dias.toISOString().split('T')[0];
            }
        });
    </script>
</body>
</html>
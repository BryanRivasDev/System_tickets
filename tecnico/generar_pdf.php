<?php
include '../config/database.php';
include '../includes/auth.php';
redirectIfNotLoggedIn();
redirectIfNotTecnico();

$tecnico_id = $_SESSION['user_id'];

// Procesar filtros (misma lógica que reportes.php)
$params = [$tecnico_id];
$where = "WHERE t.tecnico_asignado = ?";

if (isset($_GET['fecha_inicio']) && !empty($_GET['fecha_inicio'])) {
    $where .= " AND t.fecha_creacion >= ?";
    $params[] = $_GET['fecha_inicio'];
}

if (isset($_GET['fecha_fin']) && !empty($_GET['fecha_fin'])) {
    $where .= " AND t.fecha_creacion <= ?";
    $params[] = $_GET['fecha_fin'] . ' 23:59:59';
}

if (isset($_GET['categoria_id']) && !empty($_GET['categoria_id'])) {
    $where .= " AND t.categoria_id = ?";
    $params[] = $_GET['categoria_id'];
}

if (isset($_GET['estado_id']) && !empty($_GET['estado_id'])) {
    $where .= " AND t.estado_id = ?";
    $params[] = $_GET['estado_id'];
}

if (isset($_GET['prioridad']) && !empty($_GET['prioridad'])) {
    $where .= " AND t.prioridad = ?";
    $params[] = $_GET['prioridad'];
}

// Obtener tickets
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

// Incluir TCPDF
require_once('../tcpdf/tcpdf.php');

// Crear PDF
class MYPDF extends TCPDF {
    // Page header
    public function Header() {
        // Logo
        $image_file = '../assets/logo.png'; // Ajusta la ruta a tu logo
        if (file_exists($image_file)) {
            $this->Image($image_file, 10, 10, 30, '', 'PNG', '', 'T', false, 300, '', false, false, 0, false, false, false);
        }
        
        // Set font
        $this->SetFont('helvetica', 'B', 16);
        // Title
        $this->Cell(0, 15, 'Reporte de Tickets - Sistema IT', 0, false, 'C', 0, '', 0, false, 'M', 'M');
        // Line break
        $this->Ln(10);
    }

    // Page footer
    public function Footer() {
        // Position at 15 mm from bottom
        $this->SetY(-15);
        // Set font
        $this->SetFont('helvetica', 'I', 8);
        // Page number
        $this->Cell(0, 10, 'Página '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
        // Fecha de generación
        $this->Cell(0, 10, 'Generado: '.date('d/m/Y H:i'), 0, false, 'R', 0, '', 0, false, 'T', 'M');
    }
}

// Crear nuevo PDF
$pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// Información del documento
$pdf->SetCreator('Sistema de Tickets IT');
$pdf->SetAuthor('Sistema IT');
$pdf->SetTitle('Reporte de Tickets');
$pdf->SetSubject('Reporte de Tickets del Sistema');

// Márgenes
$pdf->SetMargins(15, 35, 15);
$pdf->SetHeaderMargin(10);
$pdf->SetFooterMargin(10);

// Saltos de página automáticos
$pdf->SetAutoPageBreak(TRUE, 25);

// Agregar página
$pdf->AddPage();

// Contenido del PDF
$html = '
<style>
    table {
        width: 100%;
        border-collapse: collapse;
        font-size: 10px;
    }
    th {
        background-color: #1a2a6c;
        color: white;
        font-weight: bold;
        padding: 6px;
        border: 1px solid #ddd;
    }
    td {
        padding: 5px;
        border: 1px solid #ddd;
    }
    .header-info {
        background-color: #f8f9fa;
        padding: 10px;
        margin-bottom: 10px;
        border: 1px solid #dee2e6;
    }
    .badge {
        padding: 2px 6px;
        border-radius: 3px;
        font-size: 8px;
        color: white;
    }
    .bg-danger { background-color: #dc3545; }
    .bg-warning { background-color: #ffc107; color: black; }
    .bg-info { background-color: #17a2b8; }
    .bg-success { background-color: #28a745; }
    .bg-primary { background-color: #007bff; }
    .bg-secondary { background-color: #6c757d; }
</style>

<div class="header-info">
    <strong>Técnico:</strong> ' . $_SESSION['user_name'] . '<br>
    <strong>Fecha de Reporte:</strong> ' . date('d/m/Y H:i') . '<br>
    <strong>Total de Tickets:</strong> ' . count($tickets) . '
</div>

<table>
    <thead>
        <tr>
            <th width="8%">ID</th>
            <th width="25%">Título</th>
            <th width="15%">Usuario</th>
            <th width="12%">Categoría</th>
            <th width="10%">Prioridad</th>
            <th width="12%">Estado</th>
            <th width="10%">Fecha Creación</th>
            <th width="8%">Días</th>
        </tr>
    </thead>
    <tbody>';

foreach ($tickets as $ticket) {
    $fecha_creacion = new DateTime($ticket['fecha_creacion']);
    $hoy = new DateTime();
    $dias_abierto = $hoy->diff($fecha_creacion)->days;
    
    // Determinar color de prioridad
    $color_prioridad = '';
    switch($ticket['prioridad']) {
        case 'Urgente': $color_prioridad = 'bg-danger'; break;
        case 'Alta': $color_prioridad = 'bg-warning'; break;
        case 'Media': $color_prioridad = 'bg-info'; break;
        case 'Baja': $color_prioridad = 'bg-success'; break;
    }
    
    // Determinar color de estado
    $color_estado = '';
    switch($ticket['estado']) {
        case 'Nuevo': $color_estado = 'bg-primary'; break;
        case 'Asignado': $color_estado = 'bg-warning'; break;
        case 'En Progreso': $color_estado = 'bg-info'; break;
        case 'Resuelto': $color_estado = 'bg-success'; break;
        default: $color_estado = 'bg-secondary';
    }
    
    // Color de días abiertos
    $color_dias = $dias_abierto > 7 ? 'bg-danger' : ($dias_abierto > 3 ? 'bg-warning' : 'bg-success');
    
    $html .= '
        <tr>
            <td><strong>#' . $ticket['id'] . '</strong></td>
            <td>' . htmlspecialchars($ticket['titulo']) . '</td>
            <td>' . htmlspecialchars($ticket['usuario_nombre']) . '</td>
            <td>' . $ticket['categoria'] . '</td>
            <td><span class="badge ' . $color_prioridad . '">' . $ticket['prioridad'] . '</span></td>
            <td><span class="badge ' . $color_estado . '">' . $ticket['estado'] . '</span></td>
            <td>' . date('d/m/Y', strtotime($ticket['fecha_creacion'])) . '</td>
            <td><span class="badge ' . $color_dias . '">' . $dias_abierto . '</span></td>
        </tr>';
}

$html .= '
    </tbody>
</table>

<br><br>

<div style="text-align: center; font-size: 10px; color: #666;">
    <strong>Estadísticas del Reporte:</strong><br>
    Total Tickets: ' . count($tickets) . ' | 
    Generado por: ' . $_SESSION['user_name'] . ' | 
    Fecha: ' . date('d/m/Y H:i') . '
</div>';

// Escribir contenido
$pdf->writeHTML($html, true, false, true, false, '');

// Cerrar y generar PDF
$pdf->Output('reporte_tickets_' . date('Y-m-d_His') . '.pdf', 'D');
?>
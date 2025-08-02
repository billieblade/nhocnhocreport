<?php
session_start();
require_once 'includes/db.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: index.php");
    exit();
}

$usuario_id = intval($_SESSION['usuario_id']);
$nome = $_SESSION['nome'] ?? 'Usuário';

function diaSemanaPT($dateStr) {
    $dias = [
        'Sunday' => 'Domingo',
        'Monday' => 'Segunda-feira',
        'Tuesday' => 'Terça-feira',
        'Wednesday' => 'Quarta-feira',
        'Thursday' => 'Quinta-feira',
        'Friday' => 'Sexta-feira',
        'Saturday' => 'Sábado'
    ];
    $nomeIngles = date('l', strtotime($dateStr));
    return $dias[$nomeIngles] ?? $nomeIngles;
}

$hoje = new DateTime();
$inicioSemana = clone $hoje;
$inicioSemana->modify('last sunday');
$fimSemana = clone $inicioSemana;
$fimSemana->modify('+6 days');

$dataInicio = $inicioSemana->format('Y-m-d');
$dataFim = $fimSemana->format('Y-m-d');

$stmt = $conn->prepare("SELECT * FROM refeicoes WHERE usuario_id = ? AND data BETWEEN ? AND ? ORDER BY data, horario");
$stmt->bind_param("iss", $usuario_id, $dataInicio, $dataFim);
$stmt->execute();
$resultRefeicoes = $stmt->get_result();

$refeicoesPorDia = [];
while ($row = $resultRefeicoes->fetch_assoc()) {
    $dia = $row['data'];
    $refeicoesPorDia[$dia][] = $row;
}

$aguaSemana = [];
$queryAgua = $conn->prepare("SELECT data, quantidade_ml FROM agua WHERE usuario_id = ? AND data BETWEEN ? AND ?");
$queryAgua->bind_param("iss", $usuario_id, $dataInicio, $dataFim);
$queryAgua->execute();
$resAgua = $queryAgua->get_result();
while ($row = $resAgua->fetch_assoc()) {
    $aguaSemana[$row['data']] = $row['quantidade_ml'];
}
$queryAgua->close();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8" />
  <title>Relatório Semanal - Nhoc Report</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="styles.css" rel="stylesheet" />
  <style>
    @media print {
      .no-print { display: none !important; }
      body { background: white !important; color: black !important; }
      .card { box-shadow: none !important; border: 1px solid #ccc !important; }
    }
  </style>
</head>
<body>
<div class="container py-4">

  <div class="d-flex justify-content-between align-items-center no-print mb-4 flex-wrap gap-3">
    <h2>Relatório Semanal - <?= htmlspecialchars($nome) ?></h2>
    <div>
      <a href="dashboard.php" class="btn btn-outline-secondary">Voltar</a>
      <button onclick="window.print()" class="btn btn-info">Imprimir</button>
    </div>
  </div>

  <p class="mb-4"><strong>Período:</strong> <?= $inicioSemana->format('d/m/Y') ?> a <?= $fimSemana->format('d/m/Y') ?></p>

  <?php
  $intervalo = new DatePeriod($inicioSemana, new DateInterval('P1D'), (clone $fimSemana)->modify('+1 day'));
  foreach ($intervalo as $dia):
      $data = $dia->format('Y-m-d');
      $label = diaSemanaPT($data) . ', ' . $dia->format('d/m/Y');
      $hidr = $aguaSemana[$data] ?? 0;
  ?>
  <div class="card mb-4 shadow-sm">
    <div class="card-header d-flex justify-content-between align-items-center">
      <strong><?= $label ?></strong>
      <span>
        Água: <?= intval($hidr) ?> ml
        <?php if ($hidr >= 5000): ?>
          <span class="meta-ok">✓ Meta alcançada</span>
        <?php else: ?>
          <span class="meta-falta">❗ Meta não atingida</span>
        <?php endif; ?>
      </span>
    </div>
    <div class="card-body">
      <?php if (!empty($refeicoesPorDia[$data])): ?>
        <ul class="list-group">
          <?php foreach ($refeicoesPorDia[$data] as $r): ?>
            <li class="list-group-item">
              <strong><?= htmlspecialchars($r['tipo']) ?> - <?= substr($r['horario'], 0, 5) ?></strong><br>
              <em>Refeição:</em> <?= nl2br(htmlspecialchars($r['refeicao'])) ?><br>
              <em>Bebida:</em> <?= nl2br(htmlspecialchars($r['bebida'])) ?>
            </li>
          <?php endforeach; ?>
        </ul>
      <?php else: ?>
        <p class="text-muted fst-italic">Sem refeições registradas.</p>
      <?php endif; ?>
    </div>
  </div>
  <?php endforeach; ?>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

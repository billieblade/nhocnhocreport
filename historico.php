<?php
session_start();
require_once 'includes/db.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: index.php");
    exit();
}

$usuario_id = $_SESSION['usuario_id'];
$nome = $_SESSION['nome'];

$dataSelecionada = isset($_GET['data']) ? $_GET['data'] : date('Y-m-d');
$dataObj = new DateTime($dataSelecionada);

$inicioSemana = clone $dataObj;
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

$queryAgua = $conn->query("SELECT id, data, quantidade_ml FROM agua WHERE usuario_id = $usuario_id AND data BETWEEN '$dataInicio' AND '$dataFim'");
$aguaSemana = [];
while ($row = $queryAgua->fetch_assoc()) {
    $aguaSemana[$row['data']] = $row;
}

$semanaAnterior = clone $inicioSemana;
$semanaAnterior->modify('-7 days');
$semanaSeguinte = clone $inicioSemana;
$semanaSeguinte->modify('+7 days');
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8" />
  <title>Histórico Semanal - Nhoc Report</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="styles.css" rel="stylesheet" />
</head>
<body>
<div class="container py-4">

  <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3 no-print">
    <h2>Histórico Semanal - <?= htmlspecialchars($nome) ?></h2>
    <div>
      <a href="dashboard.php" class="btn btn-outline-secondary">Voltar</a>
      <button onclick="window.print()" class="btn btn-info">Imprimir</button>
    </div>
  </div>

  <form method="get" class="mb-3 no-print">
    <label for="data" class="form-label">Escolha uma data para a semana:</label>
    <input type="date" id="data" name="data" value="<?= $dataSelecionada ?>" class="form-control" onchange="this.form.submit()" max="<?= date('Y-m-d') ?>" />
  </form>

  <nav class="mb-3 no-print d-flex gap-2 flex-wrap">
    <a href="?data=<?= $semanaAnterior->format('Y-m-d') ?>" class="btn btn-outline-secondary">&laquo; Semana Anterior</a>
    <a href="?data=<?= $semanaSeguinte->format('Y-m-d') ?>" class="btn btn-outline-secondary">Semana Seguinte &raquo;</a>
  </nav>

  <p><strong>Período:</strong> <?= $inicioSemana->format('d/m/Y') ?> a <?= $fimSemana->format('d/m/Y') ?></p>

  <?php
  $fimSemanaClone = clone $fimSemana;
  $intervalo = new DatePeriod($inicioSemana, new DateInterval('P1D'), $fimSemanaClone->modify('+1 day'));

  $diasSemana = [
      'Sunday' => 'Domingo',
      'Monday' => 'Segunda-feira',
      'Tuesday' => 'Terça-feira',
      'Wednesday' => 'Quarta-feira',
      'Thursday' => 'Quinta-feira',
      'Friday' => 'Sexta-feira',
      'Saturday' => 'Sábado'
  ];

  foreach ($intervalo as $dia):
      $data = $dia->format('Y-m-d');
      $diaIngles = $dia->format('l');
      $diaPt = $diasSemana[$diaIngles] ?? $diaIngles;
      $label = $diaPt . ', ' . $dia->format('d/m/Y');
      $hidr = $aguaSemana[$data]['quantidade_ml'] ?? 0;
      $hidrId = $aguaSemana[$data]['id'] ?? null;
  ?>
    <div class="card mb-4 shadow-sm">
      <div class="card-header d-flex justify-content-between align-items-center">
        <strong><?= $label ?></strong>
        <div class="d-flex align-items-center gap-2">
          <span>
            Água: <?= intval($hidr) ?> ml
            <?php if ($hidr >= 5000): ?>
              <span class="meta-ok">✓ Meta alcançada</span>
            <?php else: ?>
              <span class="meta-falta">❗ Meta não atingida</span>
            <?php endif; ?>
          </span>
          <?php if ($hidrId): ?>
            <a href="?del_agua=<?= $hidrId ?>" onclick="return confirm('Confirma remover a hidratação deste dia?')" class="btn btn-sm btn-danger" title="Remover hidratação">
              Remover Água
            </a>
          <?php endif; ?>
        </div>
      </div>
      <div class="card-body">
        <?php if (!empty($refeicoesPorDia[$data])): ?>
          <ul class="list-group">
            <?php foreach ($refeicoesPorDia[$data] as $r): ?>
              <li class="list-group-item d-flex justify-content-between align-items-start">
                <div>
                  <strong><?= htmlspecialchars($r['tipo']) ?> - <?= substr($r['horario'], 0, 5) ?></strong><br>
                  <?= nl2br(htmlspecialchars($r['refeicao'])) ?><br>
                  <em><?= htmlspecialchars($r['bebida']) ?></em>
                </div>
                <a href="?del_refeicao=<?= $r['id'] ?>" onclick="return confirm('Confirma remover esta refeição?')" class="btn btn-sm btn-danger" title="Remover refeição">
                  Remover
                </a>
              </li>
            <?php endforeach; ?>
          </ul>
        <?php else: ?>
          <p class="text-muted fst-italic">Nenhuma refeição registrada neste dia.</p>
        <?php endif; ?>
      </div>
    </div>
  <?php endforeach; ?>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

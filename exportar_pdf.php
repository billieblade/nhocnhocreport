<?php
session_start();
require_once 'includes/db.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: index.php");
    exit();
}

require_once 'includes/dompdf/autoload.inc.php';
use Dompdf\Dompdf;

$usuario_id = $_SESSION['usuario_id'];
$nome = $_SESSION['nome'];

// Parâmetros para a semana (pode ser passado GET ou usar atual)
$dataSelecionada = isset($_GET['data']) ? $_GET['data'] : date('Y-m-d');
$dataObj = new DateTime($dataSelecionada);

$inicioSemana = clone $dataObj;
$inicioSemana->modify('last sunday');
$fimSemana = clone $inicioSemana;
$fimSemana->modify('+6 days');

$dataInicio = $inicioSemana->format('Y-m-d');
$dataFim = $fimSemana->format('Y-m-d');

// Busca dados como no relatório
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
$queryAgua = $conn->query("SELECT data, quantidade_ml FROM agua WHERE usuario_id = $usuario_id AND data BETWEEN '$dataInicio' AND '$dataFim'");
while ($row = $queryAgua->fetch_assoc()) {
    $aguaSemana[$row['data']] = $row['quantidade_ml'];
}

// Montar o HTML para PDF
ob_start();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<title>Relatório PDF - <?= htmlspecialchars($nome) ?></title>
<style>
  body { font-family: Arial, sans-serif; font-size: 12px; }
  h2 { text-align: center; }
  .dia-box { margin-bottom: 15px; border-bottom: 1px solid #ccc; padding-bottom: 10px; }
  .meta-ok { color: green; font-weight: bold; }
  .meta-falta { color: red; font-weight: bold; }
</style>
</head>
<body>
<h2>Relatório Semanal - <?= htmlspecialchars($nome) ?></h2>
<p><strong>Período:</strong> <?= $inicioSemana->format('d/m/Y') ?> a <?= $fimSemana->format('d/m/Y') ?></p>

<?php
$intervalo = new DatePeriod($inicioSemana, new DateInterval('P1D'), $fimSemana->modify('+1 day'));
foreach ($intervalo as $dia) :
    $data = $dia->format('Y-m-d');
    $label = $dia->format('l, d/m/Y');
    $hidr = $aguaSemana[$data] ?? 0;
?>
<div class="dia-box">
  <h3><?= $label ?></h3>
  <p><strong>Água:</strong> <?= $hidr ?>ml
    <?php if ($hidr >= 5000): ?>
      <span class="meta-ok">✓</span>
    <?php else: ?>
      <span class="meta-falta">❗</span>
    <?php endif; ?>
  </p>
  <?php if (!empty($refeicoesPorDia[$data])): ?>
    <ul>
      <?php foreach ($refeicoesPorDia[$data] as $r): ?>
        <li>
          <strong><?= $r['tipo'] ?> - <?= substr($r['horario'], 0, 5) ?></strong><br>
          Refeição: <?= nl2br(htmlspecialchars($r['refeicao'])) ?><br>
          Bebida: <?= nl2br(htmlspecialchars($r['bebida'])) ?>
        </li>
      <?php endforeach; ?>
    </ul>
  <?php else: ?>
    <p><em>Sem refeições registradas.</em></p>
  <?php endif; ?>
</div>
<?php endforeach; ?>

</body>
</html>

<?php
$html = ob_get_clean();

$dompdf = new Dompdf();
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();
$dompdf->stream("relatorio_semana_{$dataInicio}_a_{$dataFim}.pdf", ["Attachment" => false]);
exit;

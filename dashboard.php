<?php
session_start();
require_once 'includes/db.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: index.php");
    exit();
}

$usuario_id = intval($_SESSION['usuario_id']);
date_default_timezone_set('America/Fortaleza');
$hoje = date('Y-m-d');

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

// Inserir água
if (isset($_POST['salvar_agua'], $_POST['data_agua'], $_POST['quantidade_ml'])) {
    $dataAgua = $_POST['data_agua'];
    $quantidade = intval($_POST['quantidade_ml']);
    if ($quantidade > 0) {
        // Verifica se já existe registro para o dia
        $stmt = $conn->prepare("SELECT id, quantidade_ml FROM agua WHERE usuario_id = ? AND data = ?");
        $stmt->bind_param("is", $usuario_id, $dataAgua);
        $stmt->execute();
        $res = $stmt->get_result();

        if ($res->num_rows > 0) {
            $linha = $res->fetch_assoc();
            $novo_total = $linha['quantidade_ml'] + $quantidade;
            $stmt = $conn->prepare("UPDATE agua SET quantidade_ml = ? WHERE id = ?");
            $stmt->bind_param("ii", $novo_total, $linha['id']);
            $stmt->execute();
        } else {
            $stmt = $conn->prepare("INSERT INTO agua (usuario_id, data, quantidade_ml) VALUES (?, ?, ?)");
            $stmt->bind_param("isi", $usuario_id, $dataAgua, $quantidade);
            $stmt->execute();
        }
    }
    header("Location: dashboard.php");
    exit();
}

// Inserir refeição
if (isset($_POST['salvar_refeicao'], $_POST['data'], $_POST['tipo'], $_POST['horario'], $_POST['refeicao'])) {
    $data = $_POST['data'];
    $tipo = $_POST['tipo'];
    $horario = $_POST['horario'];
    $refeicao = $_POST['refeicao'];
    $bebida = $_POST['bebida'] ?? null;

    $stmt = $conn->prepare("INSERT INTO refeicoes (usuario_id, data, tipo, horario, refeicao, bebida) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("isssss", $usuario_id, $data, $tipo, $horario, $refeicao, $bebida);
    $stmt->execute();

    header("Location: dashboard.php");
    exit();
}

// Remover água
if (isset($_GET['del_agua'])) {
    $idAgua = intval($_GET['del_agua']);
    $stmt = $conn->prepare("DELETE FROM agua WHERE id = ? AND usuario_id = ?");
    $stmt->bind_param("ii", $idAgua, $usuario_id);
    $stmt->execute();
    header("Location: dashboard.php");
    exit();
}

// Remover refeição
if (isset($_GET['del_refeicao'])) {
    $idRef = intval($_GET['del_refeicao']);
    $stmt = $conn->prepare("DELETE FROM refeicoes WHERE id = ? AND usuario_id = ?");
    $stmt->bind_param("ii", $idRef, $usuario_id);
    $stmt->execute();
    header("Location: dashboard.php");
    exit();
}

// Busca dados últimos 7 dias
$inicioSemana = date('Y-m-d', strtotime('-6 days'));
$refeicoes = [];
$agua = [];

// Refeições agrupadas por dia
$stmt = $conn->prepare("SELECT id, data, tipo, horario, refeicao, bebida FROM refeicoes WHERE usuario_id = ? AND data >= ? ORDER BY data DESC, horario");
$stmt->bind_param("is", $usuario_id, $inicioSemana);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $refeicoes[$row['data']][] = $row;
}
$stmt->close();

// Água (soma total diária)
$stmt = $conn->prepare("SELECT data, SUM(quantidade_ml) as total_ml FROM agua WHERE usuario_id = ? AND data >= ? GROUP BY data ORDER BY data DESC");
$stmt->bind_param("is", $usuario_id, $inicioSemana);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $agua[$row['data']] = $row['total_ml'];
}
$stmt->close();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8" />
    <title>Dashboard - Nhoc Report</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="styles.css" rel="stylesheet" />
</head>
<body>
<div class="container py-4">

    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <h2>Olá, <?= htmlspecialchars($_SESSION['nome'] ?? 'Usuário') ?>!</h2>
        <a href="logout.php" class="btn btn-outline-secondary">Sair</a>
    </div>

    <div class="mb-3 d-flex gap-2 flex-wrap align-items-center">
        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalNovaAgua">Adicionar Água</button>
        <button class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#modalNovaRefeicao">Adicionar Refeição</button>
        <a href="historico.php" class="btn btn-info">Histórico Semanal</a>
        <a href="relatorio.php" class="btn btn-primary">Relatório Semanal</a>
        
    </div>

    <?php for ($i = 0; $i < 7; $i++):
        $dia = date('Y-m-d', strtotime("-$i days"));
        $nomeDia = diaSemanaPT($dia);
        $titulo = $nomeDia . ', ' . date('d/m/Y', strtotime($dia));
        $ref = $refeicoes[$dia] ?? [];
        $aguaDia = $agua[$dia] ?? 0;
        $progresso = min(100, round($aguaDia / 5000 * 100));
    ?>
    <div class="card mb-3 shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center">
            <strong><?= $titulo ?></strong>
            <?php if ($aguaDia > 0): ?>
                <?php
                // Buscar ids da água daquele dia para remoção
                $stmt = $conn->prepare("SELECT id FROM agua WHERE usuario_id = ? AND data = ?");
                $stmt->bind_param("is", $usuario_id, $dia);
                $stmt->execute();
                $resIds = $stmt->get_result();
                $idsAgua = [];
                while ($rowId = $resIds->fetch_assoc()) {
                    $idsAgua[] = $rowId['id'];
                }
                $stmt->close();
                ?>
                <?php foreach ($idsAgua as $idAgua): ?>
                    <a href="?del_agua=<?= $idAgua ?>" onclick="return confirm('Confirma remover um registro de água deste dia?')" class="btn btn-sm btn-danger ms-1">Remover Água</a>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        <div class="card-body">
            <h6 class="text-muted">Água consumida: <?= intval($aguaDia) ?> ml
                <?php if ($aguaDia >= 5000): ?>
                    <span class="meta-ok">✓ Meta alcançada</span>
                <?php else: ?>
                    <span class="meta-falta">❗ Meta não atingida</span>
                <?php endif; ?>
            </h6>
            <div class="progress mb-3" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="<?= $progresso ?>">
                <div class="progress-bar bg-info" style="width: <?= $progresso ?>%;"><?= $progresso ?>%</div>
            </div>

            <?php if ($ref): ?>
                <h6>Refeições registradas:</h6>
                <ul class="list-group">
                    <?php foreach ($ref as $r): ?>
                    <li class="list-group-item d-flex justify-content-between align-items-start">
                        <div>
                            <strong><?= htmlspecialchars($r['tipo']) ?> - <?= substr($r['horario'], 0, 5) ?></strong><br>
                            <?= nl2br(htmlspecialchars($r['refeicao'])) ?><br>
                            <em><?= htmlspecialchars($r['bebida']) ?></em>
                        </div>
                        <a href="?del_refeicao=<?= $r['id'] ?>" onclick="return confirm('Confirma remover esta refeição?')" class="btn btn-sm btn-danger">Remover</a>
                    </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p class="text-muted fst-italic">Nenhuma refeição registrada neste dia.</p>
            <?php endif; ?>
        </div>
    </div>
    <?php endfor; ?>

</div>

<!-- Modal para adicionar nova água -->
<div class="modal fade" id="modalNovaAgua" tabindex="-1" aria-labelledby="modalNovaAguaLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form method="POST" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalNovaAguaLabel">Adicionar Água</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
      </div>
      <div class="modal-body">
          <div class="mb-3">
              <label for="data_agua_modal" class="form-label">Data</label>
              <input type="date" class="form-control" id="data_agua_modal" name="data_agua" value="<?= $hoje ?>" max="<?= $hoje ?>" required>
          </div>
          <div class="mb-3">
              <label for="quantidade_ml" class="form-label">Quantidade em ml</label>
              <input type="number" class="form-control" id="quantidade_ml" name="quantidade_ml" min="1" max="10000" value="500" required>
          </div>
      </div>
      <div class="modal-footer">
        <button type="submit" name="salvar_agua" class="btn btn-success">Salvar Água</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
      </div>
    </form>
  </div>
</div>

<!-- Modal para adicionar nova refeição -->
<div class="modal fade" id="modalNovaRefeicao" tabindex="-1" aria-labelledby="modalNovaRefeicaoLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form method="POST" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalNovaRefeicaoLabel">Adicionar Nova Refeição</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
      </div>
      <div class="modal-body">
          <div class="mb-3">
              <label for="data" class="form-label">Data</label>
              <input type="date" class="form-control" id="data" name="data" value="<?= $hoje ?>" max="<?= $hoje ?>" required>
          </div>
          <div class="mb-3">
              <label for="horario" class="form-label">Horário</label>
              <input type="time" class="form-control" id="horario" name="horario" value="12:00" required>
          </div>
          <div class="mb-3">
              <label for="tipo" class="form-label">Tipo</label>
              <select class="form-select" id="tipo" name="tipo" required>
                  <option value="Café">Café</option>
                  <option value="Almoço">Almoço</option>
                  <option value="Lanche">Lanche</option>
                  <option value="Jantar">Jantar</option>
                  <option value="Extra" selected>Extra</option>
              </select>
          </div>
          <div class="mb-3">
              <label for="refeicao" class="form-label">Descrição da Refeição</label>
              <textarea class="form-control" id="refeicao" name="refeicao" rows="3" required></textarea>
          </div>
          <div class="mb-3">
              <label for="bebida" class="form-label">Bebida (opcional)</label>
              <input type="text" class="form-control" id="bebida" name="bebida" maxlength="100">
          </div>
      </div>
      <div class="modal-footer">
        <button type="submit" name="salvar_refeicao" class="btn btn-primary">Salvar Refeição</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
      </div>
    </form>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

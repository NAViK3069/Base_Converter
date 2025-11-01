<?php
date_default_timezone_set('Asia/Bangkok');

require_once __DIR__ . '/config.php';
spl_autoload_register(function($c){ $p=__DIR__."/classes/$c.php"; if(file_exists($p)) require_once $p; });

$rows = HistoryRepository::latest($pdo, 250);

function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

function fmtTime($t){
  $t = (string)$t;
  if ($t === '') return '';

  $tzTH  = new DateTimeZone('Asia/Bangkok');
  $nowTH = new DateTime('now', $tzTH);

  try {
    $dt = new DateTime($t);
    $dt->setTimezone($tzTH);
    return $dt->format('Y-m-d H:i');
  } catch (Exception $e) { }

  try {
    $asTH  = new DateTime($t, $tzTH);
    $asUTC = new DateTime($t, new DateTimeZone('UTC'));
    $asUTC->setTimezone($tzTH);

    $diffTH  = abs($nowTH->getTimestamp() - $asTH->getTimestamp());
    $diffUTC = abs($nowTH->getTimestamp() - $asUTC->getTimestamp());

    $chosen = ($diffTH <= $diffUTC) ? $asTH : $asUTC;
    return $chosen->format('Y-m-d H:i');
  } catch (Exception $e) {
    return h($t);
  }
}

$typeFilter = $_GET['type'] ?? 'ALL';
if ($typeFilter !== 'ALL') {
  $rows = array_values(array_filter($rows, fn($r) => ($r['type'] ?? '') === $typeFilter));
}
?>
<!doctype html>
<html lang="th">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>BaseLab — History</title>
  <link rel="stylesheet" href="assets/style.css">
  <style>
    .toolbar { display:flex; gap:10px; align-items:center; flex-wrap:wrap; }
    .pill { display:inline-block; padding:6px 10px; border-radius:999px;
            border:1px solid var(--border); background:var(--surface);
            color:var(--muted); font-size:13px; }
    .pill.blue { border-color: var(--accent); color: var(--accent-light); background: var(--accent-bg); }
    .truncate { max-width: 320px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; display:inline-block; }
    @media (max-width: 700px){ .truncate{ max-width: 160px; } }
    .section-title { font-size: 22px; font-weight: 800; color: var(--accent-light); margin-bottom: 12px; }
  </style>
</head>
<body>

<nav class="navbar">
  <div class="logo">
    <span class="logo-badge">B</span>
    <span>BaseLab</span>
  </div>
  <div class="nav-right">
    <a href="index.php">Home</a>
  </div>
</nav>

<main class="container">

  <section class="card">
    <h2 class="section-title">ประวัติการคำนวณ</h2>

    <div class="toolbar">
      <form method="get" style="display:flex; gap:8px; align-items:center;">
        <label style="margin:0">
          <span style="display:block; font-size:12px; color:var(--muted); margin-bottom:4px;">กรองตามประเภท</span>
          <select name="type">
            <?php
              $opts = ['ALL'=>'ทั้งหมด','BASE_CONVERT'=>'Base Convert','ARITHMETIC'=>'Arithmetic','CODE_TRANS'=>'Code Transform'];
              foreach ($opts as $val=>$label):
            ?>
              <option value="<?=h($val)?>" <?= $typeFilter===$val?'selected':''; ?>><?=h($label)?></option>
            <?php endforeach; ?>
          </select>
        </label>
        <button type="submit" class="button">Apply</button>
        <a class="button secondary" href="history.php">Reset</a>
      </form>
      <span class="pill">รายการทั้งหมด: <?= count($rows) ?></span>
    </div>
  </section>

  <section class="card">
    <table class="table">
      <thead>
        <tr>
          <th style="width:60px;">ID</th>
          <th style="width:140px;">Type</th>
          <th>Input</th>
          <th style="width:90px;">From</th>
          <th style="width:110px;">Op / Mode</th>
          <th style="width:90px;">To</th>
          <th style="width:80px;">Bit</th>
          <th>Result</th>
          <th style="width:160px;">Time (TH)</th>
        </tr>
      </thead>
      <tbody>
        <?php if (!$rows): ?>
          <tr><td colspan="9" style="text-align:center; color:var(--muted);">ยังไม่มีข้อมูล</td></tr>
        <?php else: foreach ($rows as $r): ?>
          <tr>
            <td><?= (int)$r['id'] ?></td>
            <td>
              <?php
                $t = $r['type'] ?? '';
                $label = $t==='BASE_CONVERT' ? 'Base Convert' : ($t==='ARITHMETIC' ? 'Arithmetic' : ($t==='CODE_TRANS' ? 'Code Transform' : h($t)));
              ?>
              <span class="pill blue"><?= h($label) ?></span>
            </td>
            <td><span class="truncate" title="<?= h($r['input_value'] ?? '') ?>"><?= h($r['input_value'] ?? '') ?></span></td>
            <td><?= h($r['input_base'] ?? '') ?></td>
            <td><?= h($r['op'] ?? '') ?></td>
            <td><?= h($r['target_base'] ?? '') ?></td>
            <td><?= h($r['bit_width'] ?? '') ?></td>
            <td><code class="truncate" title="<?= h($r['result_value'] ?? '') ?>"><?= h($r['result_value'] ?? '') ?></code></td>
            <td><?= fmtTime($r['created_at'] ?? '') ?></td>
          </tr>
        <?php endforeach; endif; ?>
      </tbody>
    </table>
  </section>

  <section class="center">
    <a class="button secondary" href="index.php">← กลับหน้าแรก</a>
  </section>

</main>
</body>
</html>

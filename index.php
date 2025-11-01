<?php
require_once __DIR__ . '/config.php';
spl_autoload_register(function($c){ $p=__DIR__."/classes/$c.php"; if(file_exists($p)) require_once $p; });

ini_set('display_errors', 1); error_reporting(E_ALL);

$result=null; $steps=[]; $error=null;
$tool=$_POST['tool']??'convert';

try{
  if($_SERVER['REQUEST_METHOD']==='POST'){
    if($tool==='convert'){
      $v=$_POST['cv_input']??''; $fb=(int)($_POST['cv_from']??2); $tb=(int)($_POST['cv_to']??10);
      $bw=$_POST['cv_bit']!==''?(int)$_POST['cv_bit']:null;
      [$result,$steps]=(new BaseConvertSolver(new BaseConvertProblem($v,$fb,$tb,$bw)))->getOutput($pdo);

    }elseif($tool==='arithmetic'){
      $a=$_POST['ar_a']??''; $ab=(int)($_POST['ar_a_base']??10); $op=$_POST['ar_op']??'+';
      $b=$_POST['ar_b']??''; $bb=(int)$_POST['ar_b_base']??10; $ob=(int)($_POST['ar_out_base']??2);
      $mode=$_POST['ar_mode']??'normal'; $bw=(int)($_POST['ar_bit']??8);
      [$result,$steps]=(new ArithmeticSolver(new ArithmeticProblem($a,$ab,$op,$b,$bb,$ob,$mode,$bw)))->getOutput($pdo);

    }elseif($tool==='codetrans'){
      $m=$_POST['ct_mode']??'B2G'; $x=$_POST['ct_input']??'';
      [$result,$steps]=(new CodeTransformSolver(new CodeTransformProblem($m,$x)))->getOutput($pdo);
    }
  }
}catch(Throwable $e){ $error=$e->getMessage(); }
?>
<!doctype html>
<html lang="th">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>BaseLab — เลขฐาน & โค้ด</title>
  <link rel="stylesheet" href="assets/style.css">
</head>
<body>

<!-- (1) Navbar -->
<nav class="navbar">
  <div class="logo">
    <span class="logo-badge">B</span>
    <span>BaseLab</span>
  </div>
  <div class="nav-right">
    <a href="history.php">History</a>
  </div>
</nav>

<main class="container">

<!-- (2) Tool selector -->
<section class="card">
  <h2 class="section-title">เลือกสิ่งที่ต้องการทำ</h2>
  <form method="post" id="toolForm">
    <div class="tool-grid">
      <label class="tool">
        <input type="radio" name="tool" value="convert" <?=($tool==='convert')?'checked':'';?>>
        <strong>แปลงเลขฐาน 2/8/10/16</strong>
        <small>Binary, Octal, Decimal, Hex</small>
      </label>
      <label class="tool">
        <input type="radio" name="tool" value="arithmetic" <?=($tool==='arithmetic')?'checked':'';?>>
        <strong>บวก/ลบ/คูณ/หาร + one’s/two’s complement</strong>
        <small>คำนวณในฐานที่เลือก</small>
      </label>
      <label class="tool">
        <input type="radio" name="tool" value="codetrans" <?=($tool==='codetrans')?'checked':'';?>>
        <strong>แปลงรหัส</strong>
        <small>Binary↔Gray / BCD↔DEC / ASCII↔BIN</small>
      </label>
    </div>

    <!-- ===== Subform: Convert ===== -->
    <div class="subform" data-form="convert" <?=($tool==='convert')?'':'hidden';?>>
      <div class="grid grid-4">
        <label>จากฐาน
          <select id="cv_from" name="cv_from">
            <?php foreach([2,8,10,16] as $b): ?>
              <option value="<?=$b?>" <?=(($_POST['cv_from']??2)==$b)?'selected':'';?>><?=$b?></option>
            <?php endforeach; ?>
          </select>
        </label>
        <label>อินพุต
          <input id="cv_input" name="cv_input" required placeholder="[ ใส่เลขฐาน 2 เช่น 101 ]"
                 value="<?=htmlspecialchars($_POST['cv_input'] ?? '')?>">
        </label>
        <label>ไปฐาน
          <select id="cv_to" name="cv_to">
            <?php foreach([2,8,10,16] as $b): ?>
              <option value="<?=$b?>" <?=(($_POST['cv_to']??10)==$b)?'selected':'';?>><?=$b?></option>
            <?php endforeach; ?>
          </select>
        </label>
        <label>Bit width (ถ้าจะ pad ไบนารี)
          <input type="number" name="cv_bit" min="1" placeholder="เช่น 8 หรือ 16"
                 value="<?=htmlspecialchars($_POST['cv_bit'] ?? '')?>">
        </label>
      </div>
      <div class="center" style="margin-top:10px">
        <button type="submit">แปลงเลย</button>
      </div>
    </div>

    <!-- Arithmetic -->
    <div class="subform" data-form="arithmetic" <?=($tool==='arithmetic')?'':'hidden';?>>
      <!-- แถว 1 -->
      <div class="grid grid-3">
        <label>ฐาน A
          <select name="ar_a_base">
            <?php foreach([2,8,10,16] as $b): ?>
              <option value="<?=$b?>" <?=(($_POST['ar_a_base']??2)==$b)?'selected':'';?>><?=$b?></option>
            <?php endforeach; ?>
          </select>
        </label>
        <label>อินพุต A
          <input name="ar_a" required placeholder="[ ตัวอย่างฐาน 2: 1011 ]"
                 value="<?=htmlspecialchars($_POST['ar_a'] ?? '')?>">
        </label>
        <label>เครื่องหมาย
          <select name="ar_op">
            <?php foreach(['+','-','*','/'] as $o): ?>
              <option value="<?=$o?>" <?=(($_POST['ar_op']??'+')===$o)?'selected':'';?>><?=$o?></option>
            <?php endforeach; ?>
          </select>
        </label>
      </div>

      <!-- แถว 2 -->
      <div class="grid grid-3" style="margin-top:8px">
        <label>ฐาน B
          <select name="ar_b_base">
            <?php foreach([2,8,10,16] as $b): ?>
              <option value="<?=$b?>" <?=(($_POST['ar_b_base']??2)==$b)?'selected':'';?>><?=$b?></option>
            <?php endforeach; ?>
          </select>
        </label>
        <label>อินพุต B
          <input name="ar_b" required placeholder="[ ตัวอย่างฐาน 2: 1101 ]"
                 value="<?=htmlspecialchars($_POST['ar_b'] ?? '')?>">
        </label>
        <label>ผลลัพธ์เป็นฐาน
          <select name="ar_out_base">
            <?php foreach([2,8,10,16] as $b): ?>
              <option value="<?=$b?>" <?=(($_POST['ar_out_base']??2)==$b)?'selected':'';?>><?=$b?></option>
            <?php endforeach; ?>
          </select>
        </label>
      </div>

      <!-- แถว 3 -->
      <div class="grid grid-3" style="margin-top:8px; align-items:end">
        <label>โหมดลบ
          <select name="ar_mode">
            <option value="normal"  <?=(($_POST['ar_mode']??'normal')==='normal')?'selected':'';?>>ปกติ</option>
            <option value="ones_sub" <?=(($_POST['ar_mode']??'')==='ones_sub')?'selected':'';?>>one’s complement (A - B)</option>
            <option value="twos_sub" <?=(($_POST['ar_mode']??'')==='twos_sub')?'selected':'';?>>two’s complement (A - B)</option>
          </select>
        </label>
        <label>Bit width
          <input type="number" name="ar_bit" min="1" max="32" value="<?=htmlspecialchars($_POST['ar_bit'] ?? '8')?>">
        </label>
        <div class="center">
          <button type="submit">คำนวณ</button>
        </div>
      </div>
    </div>

    <!-- Subform: -->
    <div class="subform" data-form="codetrans" <?=($tool==='codetrans')?'':'hidden';?>>
      <div class="grid grid-2">
        <label>เลือกโหมด
          <select id="ct_mode" name="ct_mode">
            <?php $m=$_POST['ct_mode']??'B2G'; ?>
            <option value="B2G"     <?=($m==='B2G')?'selected':'';?>>Binary → Gray</option>
            <option value="G2B"     <?=($m==='G2B')?'selected':'';?>>Gray → Binary</option>
            <option value="DEC2BCD" <?=($m==='DEC2BCD')?'selected':'';?>>Decimal → BCD</option>
            <option value="BCD2DEC" <?=($m==='BCD2DEC')?'selected':'';?>>BCD → Decimal</option>
            <option value="ASC2BIN" <?=($m==='ASC2BIN')?'selected':'';?>>ASCII → Binary(8-bit)</option>
            <option value="BIN2ASC" <?=($m==='BIN2ASC')?'selected':'';?>>Binary(8-bit) → ASCII</option>
          </select>
        </label>
        <label>อินพุต
          <input id="ct_input" name="ct_input" required placeholder="[ Binary เช่น 1011 ]"
                 value="<?=htmlspecialchars($_POST['ct_input'] ?? '')?>">
        </label>
      </div>
      <div class="center" style="margin-top:10px">
        <button type="submit">แปลงรหัส</button>
      </div>
    </div>

  </form>
</section>

<!-- (3) คำตอบ + วิธีทำ -->
<section class="card">
  <h2 class="section-title">คำตอบ</h2>
  <?php if ($error): ?><div class="error">❌ <?=htmlspecialchars($error)?></div><?php endif; ?>
  <?php if ($result!==null): ?><p class="result"><strong>ผลลัพธ์:</strong> <?=htmlspecialchars($result)?></p><?php endif; ?>
  <?php if ($steps): ?>
    <h3 style="margin:14px 0 8px">วิธีทำ (Step-by-Step)</h3>
    <ol style="margin:0; padding-left:18px">
      <?php foreach($steps as $s): ?><li><?=htmlspecialchars($s)?></li><?php endforeach; ?>
    </ol>
  <?php endif; ?>
</section>

<!-- (4) ปุ่มดูประวัติ -->
<section class="center">
  <a class="button secondary" href="history.php">เปิดดูประวัติการคำนวณ →</a>
</section>

</main>
<script src="assets/script.js"></script>
</body>
</html>

<?php
/* ---------------------------------------------------------
   include common files
----------------------------------------------------------*/
require_once '../includes/auth.php';
require_once '../includes/db.php';
require_once '../includes/header.php';

/* ---------------------------------------------------------
   load JSON lookup files
----------------------------------------------------------*/
$banksFile    = __DIR__ . '/banks.json';
$branchesFile = __DIR__ . '/branches.json';

$banksData    = json_decode(file_get_contents($banksFile), true);
$branchesData = json_decode(file_get_contents($branchesFile), true);

/* ---------------------------------------------------------
   permission + grab record
----------------------------------------------------------*/
$role  = $_SESSION['role']    ?? 'user';
$myId  = $_SESSION['user_id'] ?? null;
$id    = (int)($_GET['id'] ?? 0);

if (!$id) {
    echo "<div class='alert alert-danger m-5'>Invalid request.</div>";
    require_once '../includes/admin_footer.php';
    exit;
}

$record = $pdo->prepare("SELECT * FROM bank_details WHERE id = ?");
$record->execute([$id]);
$bank = $record->fetch(PDO::FETCH_ASSOC);

if (!$bank) {
    echo "<div class='alert alert-danger m-5'>Record not found.</div>";
    require_once '../includes/admin_footer.php';
    exit;
}

/* employer can edit only own record */
if ($role === 'employer' && $bank['user_id'] != $myId) {
    echo "<div class='alert alert-danger m-5'>Access denied.</div>";
    require_once '../includes/admin_footer.php';
    exit;
}

/* ---------------------------------------------------------
   user dropdown for admin / manager
----------------------------------------------------------*/
$users = [];
if (in_array($role, ['admin', 'manager'])) {
    $users = $pdo->query("
        SELECT id, username, role
        FROM users
        WHERE is_active = 1
          AND role IN ('admin','manager','employer')
        ORDER BY username
    ")->fetchAll(PDO::FETCH_ASSOC);
}

/* ---------------------------------------------------------
   form state
----------------------------------------------------------*/
$success = false;
$errors  = [];

$user_id        = $bank['user_id'];
$bank_id        = 0;                       // will resolve below
$branch_name    = $bank['branch_name'];
$account_number = $bank['account_number'];
$account_name   = $bank['account_name'];
$swift_code     = $bank['swift_code'];
$currency       = $bank['currency'];

/* find the bank ID matching saved name */
foreach ($banksData as $b) {
    if ($b['name'] === $bank['bank_name']) { $bank_id = $b['ID']; break; }
}

/* ---------------------------------------------------------
   handle POST
----------------------------------------------------------*/
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id        = (int)($_POST['user_id']   ?? $user_id);
    $bank_id        = (int)($_POST['bank_name'] ?? 0);
    $branch_name    = trim($_POST['branch_name']    ?? '');
    $account_number = trim($_POST['account_number'] ?? '');
    $account_name   = trim($_POST['account_name']   ?? '');
    $currency       = strtoupper(trim($_POST['currency'] ?? 'LKR'));
    $swift_code     = trim($_POST['swift_code']     ?? '');

    if ($role === 'employer') $user_id = $myId;

    /* validation */
    if (!$user_id)         $errors[] = 'User is required.';
    if (!$bank_id)         $errors[] = 'Bank is required.';
    if (!$branch_name)     $errors[] = 'Branch is required.';
    if (!$account_number)  $errors[] = 'Account number is required.';
    if (!$account_name)    $errors[] = 'Account name is required.';

    /* resolve bank name */
    $bank_name = '';
    foreach ($banksData as $b) {
        if ($b['ID'] == $bank_id) { $bank_name = $b['name']; break; }
    }

    /* update if valid */
    if (!$errors) {
        $stmt = $pdo->prepare("
           UPDATE bank_details SET
                user_id        = ?,
                bank_name      = ?,
                branch_name    = ?,
                account_number = ?,
                account_name   = ?,
                currency       = ?,
                swift_code     = ?
           WHERE id = ?
        ");
        $stmt->execute([
            $user_id, $bank_name, $branch_name, $account_number,
            $account_name, $currency, $swift_code ?: null, $id
        ]);
        $success = true;

        // refresh DB values
        header("Location: bank_accounts.php?updated=1");
        exit;
    }
}
?>

<!-- Select2 CSS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet"/>

<style>
  .form-control,.form-select{border-radius:10px;border:1px solid #ccc;font-size:.95rem;padding:.55rem .75rem}
  .form-control:focus,.form-select:focus{border-color:#5e72e4;box-shadow:none}
  .select2-container .select2-selection--single{height:40px;border-radius:10px;border:1px solid #ccc;padding:5px 10px}
  .btn{border-radius:10px;font-size:.9rem;padding:.45rem 1.2rem}
  .btn-danger{background:#e30613;border:none}.btn-danger:hover{background:#b6050e}
  .btn-secondary{background:#6c757d;border:none}.btn-secondary:hover{background:#5a6268}
</style>

<div class="container my-5">
  <h2 class="fw-bold mb-4">Edit Bank Details</h2>

  <?php if($success): ?>
    <div class="alert alert-success">Record updated.</div>
  <?php elseif($errors): ?>
    <div class="alert alert-danger"><ul class="mb-0">
      <?php foreach($errors as $e): ?><li><?=htmlspecialchars($e)?></li><?php endforeach; ?>
    </ul></div>
  <?php endif; ?>

  <form method="POST">
    <div class="row mb-3">
      <?php if(in_array($role,['admin','manager'])): ?>
        <div class="col-md-6">
          <label class="form-label">User</label>
          <select name="user_id" class="form-select select2" required>
            <?php foreach ($users as $u): ?>
              <option value="<?=$u['id']?>" <?=$user_id==$u['id']?'selected':''?>>
                <?=htmlspecialchars($u['username'])?> (<?=htmlspecialchars($u['role'])?>)
              </option>
            <?php endforeach; ?>
          </select>
        </div>
      <?php else: ?>
        <input type="hidden" name="user_id" value="<?=$myId?>">
      <?php endif; ?>

      <div class="col-md-6">
        <label class="form-label">Bank</label>
        <select name="bank_name" id="bank_name" class="form-select select2" required>
          <option value="">-- Select Bank --</option>
          <?php foreach($banksData as $bk): ?>
            <option value="<?=$bk['ID']?>" <?=$bank_id==$bk['ID']?'selected':''?>>
              <?=htmlspecialchars($bk['name'])?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
    </div>

    <div class="row mb-3">
      <div class="col-md-6">
        <label class="form-label">Branch</label>
        <select name="branch_name" id="branch_name" class="form-select select2" required>
          <option value="">-- Select Branch --</option>
          <?php
            if($bank_id && isset($branchesData[$bank_id])){
              foreach($branchesData[$bank_id] as $br){
                $sel = ($br['name']===$branch_name)?'selected':''; ?>
                <option value="<?=htmlspecialchars($br['name'])?>" <?=$sel?>>
                  <?=htmlspecialchars($br['name'])?>
                </option>
          <?php }} ?>
        </select>
      </div>

      <div class="col-md-6">
        <label class="form-label">Account Number</label>
        <input type="text" name="account_number" class="form-control"
               value="<?=htmlspecialchars($account_number)?>" required>
      </div>
    </div>

    <div class="row mb-3">
      <div class="col-md-6">
        <label class="form-label">Account Name</label>
        <input type="text" name="account_name" class="form-control"
               value="<?=htmlspecialchars($account_name)?>" required>
      </div>

      <div class="col-md-3">
        <label class="form-label">Currency</label>
        <select name="currency" class="form-select select2">
          <?php foreach(['LKR','USD','EUR','GBP'] as $c): ?>
            <option value="<?=$c?>" <?=$currency==$c?'selected':''?>><?=$c?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="col-md-3">
        <label class="form-label">SWIFT Code</label>
        <input type="text" name="swift_code" class="form-control"
               value="<?=htmlspecialchars($swift_code)?>">
      </div>
    </div>

    <div class="d-flex justify-content-between mt-4">
      <a href="bank_accounts.php" class="btn btn-secondary">Back</a>
      <button type="submit" class="btn btn-danger">Update</button>
    </div>
  </form>
</div>

<!-- Select2 + dynamic branch loader -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
$(function(){
  $('.select2').select2();

  $('#bank_name').on('change', function(){
    let bankId = $(this).val();
    let $branch = $('#branch_name');
    $branch.empty().append('<option value=\"\">-- Select Branch --</option>');
    if(!bankId) return;

    $.getJSON('branches.json', function(data){
      if(data[bankId]){
        data[bankId].forEach(function(br){
          $branch.append(`<option value=\"${br.name}\">${br.name}</option>`);
        });
      }
      $branch.trigger('change');
    });
  });

  // auto‑trigger to load branches for current bank
  if($('#bank_name').val()){ $('#bank_name').trigger('change'); }
});
</script>

<?php require_once '../includes/admin_footer.php'; ?>

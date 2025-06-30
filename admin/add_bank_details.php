<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';
require_once '../includes/header.php';

$banksFile    = __DIR__ . '/banks.json';
$branchesFile = __DIR__ . '/branches.json';

$banksData    = json_decode(file_get_contents($banksFile), true);
$branchesData = json_decode(file_get_contents($branchesFile), true);

$role = $_SESSION['role'] ?? 'user';
$myId = $_SESSION['user_id'] ?? null;

if (!in_array($role, ['admin', 'manager', 'employer'])) {
    echo "<div class='alert alert-danger m-5'>Access denied.</div>";
    require_once '../includes/admin_footer.php';
    exit;
}

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

$success = false;
$errors  = [];

$user_id = $bank_id = $branch_name = $account_number = $account_name = $swift_code = '';
$currency = 'LKR';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id        = (int)($_POST['user_id']   ?? 0);
    $bank_id        = (int)($_POST['bank_name'] ?? 0);
    $branch_name    = trim($_POST['branch_name']    ?? '');
    $account_number = trim($_POST['account_number'] ?? '');
    $account_name   = trim($_POST['account_name']   ?? '');
    $currency       = strtoupper(trim($_POST['currency'] ?? 'LKR'));
    $swift_code     = trim($_POST['swift_code']     ?? '');

    if ($role === 'employer') {
        $user_id = $myId;
    }

    if (!$user_id)         $errors[] = 'User is required.';
    if (!$bank_id)         $errors[] = 'Bank is required.';
    if (!$branch_name)     $errors[] = 'Branch is required.';
    if (!$account_number)  $errors[] = 'Account number is required.';
    if (!$account_name)    $errors[] = 'Account name is required.';

    if (!$errors) {
        $chk = $pdo->prepare("SELECT id FROM bank_details WHERE user_id = ?");
        $chk->execute([$user_id]);
        if ($chk->fetch()) {
            $errors[] = 'This user already has bank details recorded.';
        }
    }

    if (!$errors) {
        $bank_name = '';
        foreach ($banksData as $b) {
            if ($b['ID'] == $bank_id) { $bank_name = $b['name']; break; }
        }

        $stmt = $pdo->prepare("
            INSERT INTO bank_details
              (user_id, bank_name, branch_name, account_number, account_name,
               currency, swift_code)
            VALUES (?,?,?,?,?,?,?)
        ");
        $stmt->execute([
            $user_id, $bank_name, $branch_name, $account_number,
            $account_name, $currency, $swift_code ?: null
        ]);

        $success = true;
        $user_id = $bank_id = $branch_name = $account_number = $account_name = $swift_code = '';
        $currency = 'LKR';
    }
}
?>

<!-- Include Select2 CSS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

<style>
    
  body {
    font-family: 'Segoe UI', sans-serif;
  }

  .form-label {
    font-weight: 600;
    margin-bottom: 6px;
  }

  .form-control, .form-select {
    border-radius: 10px;
    border: 1px solid #ccc;
    font-size: 0.95rem;
    padding: 0.55rem 0.75rem;
    transition: border-color 0.3s ease;
  }

  .form-control:focus, .form-select:focus {
    border-color: #5e72e4;
    box-shadow: none;
  }

  .select2-container .select2-selection--single {
    height: 40px;
    border-radius: 10px;
    border: 1px solid #ccc;
    padding: 5px 10px;
  }

  .btn {
    border-radius: 10px;
    font-size: 0.9rem;
    padding: 0.45rem 1.2rem;
  }

  .btn-danger {
    background-color: #e30613;
    border: none;
  }

  .btn-danger:hover {
    background-color: #b6050e;
  }

  .btn-secondary {
    background-color: #6c757d;
    border: none;
  }

  .btn-secondary:hover {
    background-color: #5a6268;
  }
</style>

<div class="container my-5">
  <h2 class="fw-bold mb-4">Add Bank Details</h2>

  <?php if($success): ?>
    <div class="alert alert-success">Bank details saved successfully.</div>
  <?php elseif($errors): ?>
    <div class="alert alert-danger"><ul class="mb-0">
      <?php foreach($errors as $e): ?><li><?=htmlspecialchars($e)?></li><?php endforeach; ?>
    </ul></div>
  <?php endif; ?>

  <form method="POST" class="mt-3">
    <div class="row mb-3">
      <?php if(in_array($role,['admin','manager'])): ?>
        <div class="col-md-6">
          <label class="form-label">User</label>
          <select name="user_id" class="form-select select2" required>
            <option value="">-- Select User --</option>
            <?php foreach($users as $u): ?>
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
          <?php foreach($banksData as $bank): ?>
            <option value="<?=$bank['ID']?>" <?=$bank_id==$bank['ID']?'selected':''?>>
              <?=htmlspecialchars($bank['name'])?>
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
      <button type="submit" class="btn btn-danger">Save</button>
    </div>
  </form>
</div>

<!-- Select2 + branch dropdown logic -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
$(document).ready(function() {
  $('.select2').select2();

  $('#bank_name').on('change', function() {
    let bankId = $(this).val();
    let $branch = $('#branch_name');
    $branch.empty().append('<option value="">-- Select Branch --</option>');

    if (!bankId) return;

    $.getJSON('branches.json', function(data) {
      if (data[bankId]) {
        data[bankId].forEach(function(branch) {
          $branch.append(`<option value="${branch.name}">${branch.name}</option>`);
        });
      }
      $branch.trigger('change');
    });
  });

  // auto-load branches if bank already selected
  if ($('#bank_name').val()) {
    $('#bank_name').trigger('change');
  }
});
</script>

<?php require_once '../includes/admin_footer.php'; ?>

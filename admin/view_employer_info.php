<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';
require_once '../includes/header.php';

$employer_id = $_GET['id'] ?? null;
if (!$employer_id) {
    echo "<div class='alert alert-danger m-5'>Employer ID missing.</div>";
    require_once '../includes/admin_footer.php';
    exit;
}

// Fetch employer details
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$employer_id]);
$employer = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$employer) {
    echo "<div class='alert alert-warning m-5'>Employer not found.</div>";
    require_once '../includes/admin_footer.php';
    exit;
}

// Fetch bank details
$bankDetails = $pdo->prepare("SELECT * FROM bank_details WHERE user_id = ?");
$bankDetails->execute([$employer_id]);
$bankList = $bankDetails->fetchAll(PDO::FETCH_ASSOC);
?>

<style>
    /* Container */
    .container {
        max-width: 900px;
    }

    /* Section Header */
    .section-header {
        font-size: 1.3rem;
        font-weight: 700;
        color: #b6050e;
        border-bottom: 2px solid #ddd;
        padding-bottom: 8px;
        margin-bottom: 20px;
    }

    /* Info Rows */
    .info-row {
        display: flex;
        padding: 8px 0;
        border-bottom: 1px solid #f1f1f1;
    }

    .info-row:last-child {
        border-bottom: none;
    }

    .info-label {
        flex: 0 0 160px;
        font-weight: 600;
        color: #444;
    }

    .info-value {
        flex: 1;
        color: #222;
        word-wrap: break-word;
    }

    /* Bank Table */
    .bank-table {
        width: 100%;
        border-collapse: collapse;
    }
    .bank-table th, .bank-table td {
        padding: 8px 12px;
        border: 1px solid #ddd;
        text-align: left;
    }
    .bank-table th {
        background-color: #f9f9f9;
        font-weight: 600;
        color: #555;
    }

    .btn {
        font-size: 0.8rem;
        padding: 0.25rem 0.75rem;
    }

    .btn-danger {
        background-color: #e30613;
        border: none;
    }

    .btn-danger:hover {
        background-color: #b6050e;
    }

    .btn-secondary {
        background-color: #666;
        border: none;
    }

    .btn-secondary:hover {
        background-color: #444;
    }
    /* Responsive */
    @media (max-width: 767px) {
        .info-row {
            flex-direction: column;
        }
        .info-label {
            padding-bottom: 4px;
            flex: none;
            width: 100%;
        }
        .info-value {
            width: 100%;
        }
    }
    
</style>

<div class="container my-5">
    <h2 class="fw-bold mb-4">Employer Details – <?= htmlspecialchars($employer['username']) ?></h2>

    <!-- Personal Info -->
    <section>
        <div class="section-header">Personal Information</div>
        <div class="info-row">
            <div class="info-label">Email</div>
            <div class="info-value"><?= htmlspecialchars($employer['email']) ?></div>
        </div>
        <div class="info-row">
            <div class="info-label">Phone</div>
            <div class="info-value"><?= htmlspecialchars($employer['phone']) ?></div>
        </div>
        <div class="info-row">
            <div class="info-label">Date of Birth</div>
            <div class="info-value"><?= htmlspecialchars($employer['date_of_birth']) ?></div>
        </div>
        <div class="info-row">
            <div class="info-label">Job Title</div>
            <div class="info-value"><?= htmlspecialchars($employer['job_title']) ?></div>
        </div>
        <div class="info-row">
            <div class="info-label">Date of Joining</div>
            <div class="info-value"><?= htmlspecialchars($employer['date_of_joining']) ?></div>
        </div>
    </section>

    <!-- Company & Address -->
    <section class="mt-5">
        <div class="section-header">Company & Address</div>
        <div class="info-row">
            <div class="info-label">Company Name</div>
            <div class="info-value"><?= htmlspecialchars($employer['company_name']) ?></div>
        </div>
        <div class="info-row">
            <div class="info-label">NIC / Passport No</div>
            <div class="info-value"><?= htmlspecialchars($employer['nic_passport_number']) ?></div>
        </div>
        <div class="info-row">
            <div class="info-label">Street Address</div>
            <div class="info-value"><?= htmlspecialchars($employer['street_address']) ?></div>
        </div>
        <div class="info-row">
            <div class="info-label">City / State</div>
            <div class="info-value"><?= htmlspecialchars($employer['city']) ?>, <?= htmlspecialchars($employer['state']) ?></div>
        </div>
        <div class="info-row">
            <div class="info-label">Postal Code / Country</div>
            <div class="info-value"><?= htmlspecialchars($employer['postal_code']) ?>, <?= htmlspecialchars($employer['country']) ?></div>
        </div>
    </section>

    <!-- Bank Details -->
    <section class="mt-5">
        <div class="section-header">Bank Details</div>

        <?php if (count($bankList) > 0): ?>
            <table class="bank-table">
                <thead>
                    <tr>
                        <th>Bank Name</th>
                        <th>Branch</th>
                        <th>Account Name</th>
                        <th>Account Number</th>
                        <th>Currency</th>
                        <th>SWIFT Code</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($bankList as $bank): ?>
                        <tr>
                            <td><?= htmlspecialchars($bank['bank_name']) ?></td>
                            <td><?= htmlspecialchars($bank['branch_name']) ?></td>
                            <td><?= htmlspecialchars($bank['account_name']) ?></td>
                            <td><?= htmlspecialchars($bank['account_number']) ?></td>
                            <td><?= htmlspecialchars($bank['currency']) ?></td>
                            <td><?= htmlspecialchars($bank['swift_code'] ?? '-') ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No bank details available.</p>
        <?php endif; ?>
    </section>
<section class="mt-5"></section>
    <a href="manage_users.php" class="btn btn-secondary">Back to List</a>
</div>

<?php require_once '../includes/admin_footer.php'; ?>

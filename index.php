<?php
session_start();
require_once "config/db.php";

$ADMIN_PASSWORD = $_SESSION['admin_pass'] ?? "admin123";
if (!isset($_SESSION['admin_pass'])) {
    $_SESSION['admin_pass'] = $ADMIN_PASSWORD;
}

$login_error = "";
$step = $_SESSION['login_step'] ?? 1;

if (isset($_POST['send_otp'])) {
    $entered_password = $_POST['password'] ?? '';
    $phone_no = trim($_POST['phone'] ?? '');

    if ($entered_password === $_SESSION['admin_pass']) {
        $otp_code = rand(1000, 9999);
        $_SESSION['otp_code'] = $otp_code;
        $_SESSION['temp_phone'] = $phone_no;
        $_SESSION['login_step'] = 2;
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    } else {
        $login_error = "गलत पासवर्ड! कृपया सहि पासवर्ड राख्नुहोस्।";
    }
}

if (isset($_POST['verify_otp'])) {
    $entered_otp = trim($_POST['otp'] ?? '');
    if ($entered_otp == ($_SESSION['otp_code'] ?? '')) {
        $_SESSION['edu_logged_in'] = true;
        unset($_SESSION['login_step']);
        unset($_SESSION['otp_code']);
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    } else {
        $login_error = "गलत कोड (OTP)! कृपया सहि कोड राख्नुहोस्।";
    }
}

if (isset($_GET['logout'])) {
    unset($_SESSION['edu_logged_in']);
    unset($_SESSION['login_step']);
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

require_once "includes/header.php";
require_once "includes/sidebar.php";
?>

<link href="https://fonts.googleapis.com/css2?family=Kalimati&display=swap" rel="stylesheet">
<style>
body, .card, h2, h3, h4, h5, p, span, th, td, label, input {
    font-family: 'Kalimati', Arial, sans-serif !important;
}
.lock-box {
    max-width: 450px; margin: 40px auto; background: #fff; padding: 35px; border-radius: 12px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08); text-align: center;
}
</style>

<div class="flex-grow-1 p-4">
<?php if (!isset($_SESSION['edu_logged_in']) || $_SESSION['edu_logged_in'] !== true): ?>
    <div class="lock-box">
        <i class="fa-solid fa-graduation-cap text-primary fa-3x mb-3"></i>
        <h4 class="fw-bold mb-3">शैक्षिक प्रणाली सुरक्षित लगइन</h4>
        <?php if($login_error): ?>
            <div class="alert alert-danger py-2 small"><?php echo $login_error; ?></div>
        <?php endif; ?>

        <?php if (($s_step = ($_SESSION['login_step'] ?? 1)) == 1): ?>
            <form method="POST">
                <div class="mb-3 text-start">
                    <label class="form-label fw-bold">प्रधानाध्यापक/एडमिन पासवर्ड</label>
                    <input type="password" name="password" class="form-control text-center fw-bold" placeholder="********" required autofocus>
                </div>
                <div class="mb-3 text-start">
                    <label class="form-label fw-bold">सम्पर्क फोन नम्बर</label>
                    <input type="text" name="phone" class="form-control text-center fw-bold" placeholder="98XXXXXXXX" required>
                </div>
                <button type="submit" name="send_otp" class="btn btn-primary w-100 fw-bold py-2">प्रमाणीकरण कोड पठाउनुहोस्</button>
            </form>
        <?php else: ?>
            <div class="alert alert-info py-2 small">
                <b>सुचना:</b> तपाईको फोन नम्बर (<?php echo htmlspecialchars($_SESSION['temp_phone']); ?>) मा कोड पठाइएको छ।<br>
                <span class="text-danger fw-bold">(टेस्ट कोड: <?php echo $_SESSION['otp_code']; ?>)</span>
            </div>
            <form method="POST">
                <div class="mb-3 text-start">
                    <label class="form-label fw-bold">४ अंकको कोड (OTP) हाल्नुहोस्</label>
                    <input type="text" name="otp" class="form-control text-center fw-bold" placeholder="XXXX" maxlength="4" required autofocus style="font-size: 22px; letter-spacing: 5px;">
                </div>
                <button type="submit" name="verify_otp" class="btn btn-success w-100 fw-bold py-2 mb-2">प्रमाणित गर्नुहोस्</button>
                <a href="?logout=1" class="text-decoration-none small text-muted">पछाडि जाने</a>
            </form>
        <?php endif; ?>
    </div>
<?php else: ?>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold text-dark">🎓 विद्यालय व्यवस्थापन प्रणाली</h2>
            <p class="text-muted m-0">शैक्षिक प्रशासनिक गतिविधिहरू व्यवस्थापन गर्ने प्यानल।</p>
        </div>
        <a href="?logout=1" class="btn btn-sm btn-outline-danger fw-bold">लगआउट (Logout)</a>
    </div>

    <div class="row">
        <?php
        $edu_modules = [
            ['title' => 'विद्यार्थी विवरण', 'icon' => 'fa-users-rectangle', 'link' => 'students/index.php', 'color' => '#2563eb', 'desc' => 'विद्यार्थी भर्ना र अभिलेख।'],
            ['title' => 'शिक्षक तथा कर्मचारी', 'icon' => 'fa-chalkboard-user', 'link' => 'staff/index.php', 'color' => '#16a34a', 'desc' => 'शिक्षकहरूको विवरण र हाजिरी।'],
            ['title' => 'परीक्षा तथा नतिजा', 'icon' => 'fa-file-lines', 'link' => 'exams/index.php', 'color' => '#ca8a04', 'desc' => 'परीक्षा तालिका र प्राप्तांक।'],
            ['title' => 'हाजिरी व्यवस्थापन', 'icon' => 'fa-clipboard-user', 'link' => 'attendance/index.php', 'color' => '#9333ea', 'desc' => 'दैनिक हाजिरी प्रतिवेदन।'],
            ['title' => 'शुल्क तथा लेखा', 'icon' => 'fa-receipt', 'link' => 'accounts/index.php', 'color' => '#0d9488', 'desc' => 'शुल्क संकलन र आय-व्यय।'],
            ['title' => 'अनलाइन क्विज', 'icon' => 'fa-laptop-code', 'link' => 'quiz/index.php', 'color' => '#dc2626', 'desc' => 'विद्यार्थीहरूको अनलाइन टेस्ट।']
        ];

        foreach ($edu_modules as $mod) {
            echo '<div class="col-md-4 mb-4">
                    <div class="card p-3 shadow-sm border-0" style="border-top: 4px solid '.$mod['color'].';">
                        <div class="card-body text-center">
                            <i class="fa-solid '.$mod['icon'].' fa-3x mb-3" style="color: '.$mod['color'].';"></i>
                            <h4 class="fw-bold">'.$mod['title'].'</h4>
                            <p class="text-muted small">'.$mod['desc'].'</p>
                            <a href="'.$mod['link'].'" class="btn btn-sm btn-dark fw-bold px-4">सञ्चालन गर्नुहोस्</a>
                        </div>
                    </div>
                  </div>';
        }
        ?>
    </div>
<?php endif; ?>
</div>

<?php require_once "includes/footer.php"; ?>
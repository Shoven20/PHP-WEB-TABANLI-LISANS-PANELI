<?php
try {
    $db = new PDO("mysql:host=localhost;dbname=loginsystem;charset=utf8mb4", "root", "");
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

$successMessage = $errorMessage = "";
$randomLicense = 'XXXXX-XXXXX';

if (isset($_POST["manual_license"])) {
    $license = $_POST["manual_license"];
    $license_days = $_POST["manual_days"];

    $query = $db->prepare("SELECT * FROM license WHERE license = :license");
    $query->execute(['license' => $license]);
    $result = $query->fetch(PDO::FETCH_ASSOC);

    if ($result) {
        $errorMessage = "Lisans zaten veritabanında mevcut!";
    } else {
        $insert = $db->prepare("INSERT INTO license (license,expiryend) VALUES (:license,:expiryend)");
        $insert->execute([
            'license' => $license,
            'expiryend' => date('d-m-Y H:i:s', strtotime("+$license_days days"))
        ]);

        $successMessage = "Lisans başarıyla eklendi!";
        header("Refresh:0");
    }
}

if (isset($_POST["delete_license"])) {
    $delete_license = $_POST["delete_license"]; 

    $delete_query = $db->prepare("DELETE FROM license WHERE license = :delete_license");
    $delete_query->execute(['delete_license' => $delete_license]);
}

if (isset($_POST["clear_hwid"])) {
    $clear_hwid_license = $_POST["clear_hwid"];

    $clear_hwid_query = $db->prepare("UPDATE license SET hwid = NULL WHERE license = :clear_hwid_license");
    $clear_hwid_query->execute(['clear_hwid_license' => $clear_hwid_license]);
}

function removeExpiredLicenses($db) {
    $queryExpired = $db->query("SELECT * FROM license WHERE STR_TO_DATE(expiryend, '%d-%m-%Y %H:%i:%s') < DATE_ADD(NOW(), INTERVAL -1 HOUR)");
    $expiredLicenses = $queryExpired->fetchAll(PDO::FETCH_ASSOC);

    if (!empty($expiredLicenses)) {
        $expiredLicenseIDs = array_column($expiredLicenses, 'license');
        $inClause = implode(',', array_fill(0, count($expiredLicenseIDs), '?'));

        $deleteExpired = $db->prepare("DELETE FROM license WHERE license IN ($inClause)");
        $deleteExpired->execute($expiredLicenseIDs);
    }
}
removeExpiredLicenses($db);

$queryAll = $db->query("SELECT * FROM license");
$licenses = $queryAll->fetchAll(PDO::FETCH_ASSOC);

function generateRandomLicense() {
    $license = '';
    for ($i = 0; $i < 5; $i++) {
        $license .= chr(rand(65, 90));
    }
    $license .= '-';
    for ($i = 0; $i < 5; $i++) {
        $license .= chr(rand(65, 90));
    }
    return $license;
}

if (isset($_POST["generate_random_license"])) {
    $randomLicense = generateRandomLicense();
    $license_days = $_POST["manuald_days"];

    $query = $db->prepare("SELECT * FROM license WHERE license = :license");
    $query->execute(['license' => $randomLicense]);
    $result = $query->fetch(PDO::FETCH_ASSOC);

    if ($result) {
        $randomLicense = generateRandomLicense();
    } else {
        $insert = $db->prepare("INSERT INTO license (license,expiryend) VALUES (:license,:expiryend)");
        $insert->execute([
            'license' => $randomLicense,
            'expiryend' => date('d-m-Y H:i:s', strtotime("+$license_days days"))
        ]);
        header("Refresh:0");
    }
}

if (isset($_POST["ban_license"])) {
    $ban_license = $_POST["ban_license"];

    $query = $db->prepare("SELECT * FROM license WHERE license = :ban_license");
    $query->execute(['ban_license' => $ban_license]);
    $result = $query->fetch(PDO::FETCH_ASSOC);

    if ($result) {
        if ($result['hwid'] === 'Banned') {
            $unban_query = $db->prepare("UPDATE license SET hwid = NULL WHERE license = :ban_license");
            $unban_query->execute(['ban_license' => $ban_license]);
            header("Refresh:0");
        } else {
            $ban_query = $db->prepare("UPDATE license SET hwid = 'Banned' WHERE license = :ban_license");
            $ban_query->execute(['ban_license' => $ban_license]);
            header("Refresh:0");
        }
    }
}  

?>
 
<!DOCTYPE html>
<html>
<head>
    <title>Lisans Oluşturucu</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
     body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f9f9f9;
            zoom: 72%;
        }

        .main-container {
            max-width: 1200px;
            margin-top: 50px;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
            background-color: #fff;
        }

        
        .page-header {
            text-align: center;
            margin-top: 230px;
            font-size: 40px;
            color: rgb(255, 255, 255);
        }

        .login-header {
            text-align: center;
            margin-bottom: 30px;
            font-size: 28px;
            color: #007bff;
        }

        .form-label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #555555;
        }

        .form-input {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #dddddd;
            border-radius: 5px;
        }

        .form-submit {
            width: 100%;
            padding: 12px;
            background-color: #007bff;
            color: #ffffff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .form-submit:hover {
            background-color: #0056b3;
        }

        .green-message {
            text-align: center;
            margin-top: 10px;
            color: #00cc00;
        }

        .red-message {
            text-align: center;
            margin-top: 10px;
            color: #ff0000;
        }

        .license-container {
            margin-top: 150px;
        }

        .license-header {
            text-align: center;
            margin-bottom: 20px;
            font-size: 24px;
            color: #007bff;
        }

        .table-container {
            margin-top: -120px;

            border-radius: 15px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
            background-color: #fff;
            overflow: hidden;
        }

        .table-responsive {
            overflow-x: auto;
        }

        .btn-danger, .btn-warning, .btn-success {
            border-radius: 5px;
            cursor: pointer;
        }

        .btn-danger:hover, .btn-warning:hover, .btn-success:hover {
            transform: scale(1.05);
        }

        .background {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
            background-image: linear-gradient(-45deg, #007bff, #00cc99, #9b59b6, #f39c12);
            background-size: 200% 200%;
            animation: gradientAnimation 10s ease infinite;
        }

        @keyframes gradientAnimation {
            0% {
                background-position: 0% 50%;
            }
            50% {
                background-position: 100% 50%;
            }
            100% {
                background-position: 0% 50%;
            }
        }
    </style>
</head>
<body>
    <div class="background"></div>
    <h2 class="page-header">LISANS SISTEMI</h2>

     <div class="container">
        <div class="row">
            <div class="col-md-6">
                <div class="main-container">
                    <h2 class="login-header">MANUEL LİSANS OLUŞTURUCU</h2>
                    <form action="" method="post">
                        <label class="form-label" for="manual_license">Lisansı Girin:</label>
                        <input class="form-input" type="text" id="manual_license" name="manual_license" required>
                        <label class="form-label" for="manual_days">Süre Girin (Gün):</label>
                        <input class="form-input" type="number" id="manual_days" name="manual_days" placeholder="1" required>
                        <button class="form-submit" type="submit">Lisansı Ekle</button>
                        <?php if (!empty($errorMessage)) { ?>
                            <p class="red-message"><?php echo $errorMessage; ?></p>
                        <?php } ?>
                        <?php if (!empty($successMessage)) { ?>
                            <p class="green-message"><?php echo $successMessage; ?></p>
                        <?php } ?>
                    </form>
                </div>
            </div>
            <div class="col-md-6">
                <div class="main-container">
                    <h2 class="login-header">RASTGELE LİSANS OLUŞTURUCU</h2>
                    <form action="" method="post">
                        <label class="form-label" for="random_license">Rastgele lisansı oluşturun:</label>
                        <input class="form-input" type="text" id="random_license" name="random_license" value="<?php echo $randomLicense; ?>" readonly>
                        <label class="form-label" for="random_license">Süre Girin (Gün):</label>
                        <input class="form-input" type="number" id="random_license" name="manuald_days" placeholder="1" required>
                        <button class="form-submit" type="submit" name="generate_random_license">Rastgele Lisans Oluştur</button>
                    </form>
                </div>
            </div>
        </div>
        <div class="license-container">
            <div class="table-container">
                <h2 class="license-header">Veritabanı</h2>
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th scope="col">Lisans</th>
                                <th scope="col">HWID</th>
                                <th scope="col">Kalan Süre</th>
                                <th scope="col">İşlem</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($licenses as $licenseInfo) {
                                $startDate = new DateTime(date('d-m-Y H:i:s'));
                                $endDate = new DateTime($licenseInfo['expiryend']);
                                $interval = $startDate->diff($endDate);
                                $month = $interval->m;
                                $days = $interval->d;
                                $hours = $interval->h;
                                $minutes = $interval->i;
                            ?>
                            <tr>
                                <td><?php echo $licenseInfo['license']; ?></td>
                                <td><?php echo $licenseInfo['hwid']; ?></td>
                                <td><?php  echo "$month Ay,$days Gün,$hours Saat,$minutes Dakika"; ?></td>
                                <td>
                                    <form class="d-inline" method="post" action="">
                                        <input type="hidden" name="delete_license" value="<?php echo $licenseInfo['license']; ?>">
                                        <button class="btn btn-danger" type="submit">Sil</button>
                                    </form>
                                    <form class="d-inline" method="post" action="">
                                        <input type="hidden" name="clear_hwid" value="<?php echo $licenseInfo['license']; ?>">
                                        <?php if ($licenseInfo['hwid'] !== 'Banned') { ?>
                                            <button class="btn btn-warning" type="submit">HWID'yi Temizle</button>
                                        <?php } ?>
                                    </form>
                                    <form class="d-inline" method="post" action="">
                                        <input type="hidden" name="ban_license" value="<?php echo $licenseInfo['license']; ?>">
                                        <?php if ($licenseInfo['hwid'] === 'Banned') { ?>
                                            <button class="btn btn-success" type="submit">Unban</button>
                                        <?php } else { ?>
                                            <button class="btn btn-danger" type="submit">Ban</button>
                                        <?php } ?>
                                    </form>
                                </td>
                            </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
     

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.0.9/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>

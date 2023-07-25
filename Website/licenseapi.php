<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="refresh" content="0;url=YonetimSayfasi.php">
</head>

</html>

<?php // SHOVEN PHP API
try {
    $dsn = 'mysql:host=' . $config['host'] . ';dbname=' . $config['db_name'];
    $db = new PDO("mysql:host=localhost;dbname=loginsystem;charset=utf8mb4", "root", "");
} catch (PDOException $e) {
    print $e->getMessage();
}

if (isset($_GET["license"]) && isset($_GET["hwid"])) {
    $license = $_GET["license"];
    $hwid = $_GET["hwid"];

    $query = $db->prepare("SELECT * FROM license WHERE license = :license");
    $query->execute(['license' => $license]);
    $result = $query->fetch(PDO::FETCH_ASSOC);

    if ($result) {
        if (empty($result["hwid"])) {
            $update = $db->prepare("UPDATE license SET hwid = :hwid WHERE license = :license");
            $save = $update->execute([
                "hwid" => $hwid,
                "license" => $license
            ]);

            if ($save) {
                echo "HWID: " . $hwid . " ";
            } else {
                echo "HWID güncellenemedi";
            }
        } else {
            echo "HWID: " . $result["hwid"] . " ";
        }   

        $expiryend = $result["expiryend"];
        $currentDate = date('Y-m-d H:i:s');
        $expiryDate = date_create_from_format('d-m-Y H:i:s', $expiryend);
        $currentDateTime = date_create_from_format('Y-m-d H:i:s', $currentDate);

        if ($currentDateTime < $expiryDate) {
            $interval = date_diff($currentDateTime, $expiryDate);
            $remainingDays = $interval->format('%a');

            echo "Days: " . $remainingDays;
                } else {
            echo "Lisans süresi dolmuş!";
        }
    } else {
        echo "Geçersiz lisans";
    }
}
?>
<?php
// SHOVEN PHP API
try {
    $db = new PDO("mysql:host=localhost;dbname=loginsystem;charset=utf8mb4", "root", "");
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

if (isset($_GET["license"]) && isset($_GET["hwid"])) {
    $license = base64_decode(str_rot13(base64_decode($_GET["license"])));
    $hwid = base64_decode(str_rot13(base64_decode($_GET["hwid"])));

    $query = $db->prepare("SELECT * FROM license WHERE license = :license");
    $query->execute(['license' => $license]);
    $result = $query->fetch(PDO::FETCH_ASSOC);

    if ($result) {
        if (empty($result["hwid"])) {
            $update = $db->prepare("UPDATE license SET hwid = :hwid WHERE license = :license");
            $save = $update->execute([
                "hwid" =>  $hwid,
                "license" => $license
            ]);

            if ($save) {
                $response = "HWID: " . $hwid . " ";
            } else {
                $response = "Failed to update HWID";
            }
        } else {
            $response = "HWID: " . $result["hwid"] . " ";
        }   

        $expiryend = $result["expiryend"];
        $currentDate = date('Y-m-d H:i:s');
        $expiryDate = date_create_from_format('d-m-Y H:i:s', $expiryend);
        $currentDateTime = date_create_from_format('Y-m-d H:i:s', $currentDate);

        if ($currentDateTime < $expiryDate) {
            $interval = date_diff($currentDateTime, $expiryDate);
            $remainingDays = $interval->format('%a');

            $response .= "Days: " . $remainingDays;
        } else {
            $response .= "expiryed";
        }
    } else {
        $response = "Invalid license";
    }

    echo base64_encode(str_rot13(base64_encode($response)));
}
?>

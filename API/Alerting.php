<?php

/*function TryToConnect()
{
    if (empty($_ENV['TOKEN']) === null || empty($_ENV['TOKEN']) ){
        $userData = [
            "name"                  => $_ENV['NAME'],
            "email"                 => $_ENV['EMAIL'],
            "password"              => $_ENV['PASSWORD'],
            "password_confirmation" => $_ENV['PASSWORD'],
        ];

        $url = "https://monitoring-app.on-forge.com/api/v1/auth/register";

        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($userData));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Accept: application/json'
        ]);

        $response = curl_exec($ch);
        $result = json_decode($response, true);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $userDataLoggin = [
            "email"                 => $_ENV['EMAIL'],
            "password"              => $_ENV['PASSWORD'],
        ];

        $urlLog = "https://monitoring-app.on-forge.com/api/v1/auth/login";

        $chLog = curl_init($urlLog);

        curl_setopt($chLog, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($chLog, CURLOPT_POST, true);
        curl_setopt($chLog, CURLOPT_POSTFIELDS, json_encode($userDataLoggin));
        curl_setopt($chLog, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Accept: application/json'
        ]);

        $response = curl_exec($chLog);
        $result = json_decode($response, true);
        $httpCode = curl_getinfo($chLog, CURLINFO_HTTP_CODE);
        curl_close($chLog);
        if ($httpCode === 201 || $httpCode === 200) {

            $newToken = $result['token'];

            if ($newToken) {
                $envPath = __DIR__ . '/.env';
                $oldContent = file_get_contents($envPath);
                $newContent = preg_replace("/^TOKEN=.*/

/*m", "TOKEN=" . trim($newToken), $oldContent);

                file_put_contents($envPath, $newContent);

                $_ENV["TOKEN"] = $newToken;
            }
        } else {
            echo "Erreur ({$httpCode}) : " . ($result['message']);
            exit(1);
        }

        $APP_DATA=[
            "name"=> "Sonde Monitoring - API_PROJECT",
            "url"=> "http://localhost:8000"
        ];
        $urlRegisterAPP = "https://monitoring-app.on-forge.com/api/applications";
        $chRegisterAPP = curl_init($urlRegisterAPP);
        curl_setopt($chRegisterAPP, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($chRegisterAPP, CURLOPT_POST, true);
        curl_setopt($chRegisterAPP, CURLOPT_POSTFIELDS, json_encode($APP_DATA));
        curl_setopt($chRegisterAPP, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Accept: application/json',
            'Authorization: Bearer ' . $_ENV["TOKEN"]
        ]);
        $responseRegisterAPP = curl_exec($chRegisterAPP);

        $result = json_decode($responseRegisterAPP, true);
        $httpCodeRegisterAPP = curl_getinfo($chRegisterAPP, CURLINFO_HTTP_CODE);
        curl_close($chRegisterAPP);
        if ($httpCodeRegisterAPP === 201 || $httpCodeRegisterAPP === 200) {
            $IDAPP = $result['data']['id'];

            if ($IDAPP) {
                $envPath = __DIR__ . '/.env';
                $oldContent = file_get_contents($envPath);

                $newContent = preg_replace("/^ID_APP=.*/

/*m", "ID_APP=" . trim($IDAPP), $oldContent);

                file_put_contents($envPath, $newContent);

                $_ENV["ID_APP"] = $IDAPP;
                echo "APP registered successfully";
            } else {
                echo "Erreur ({$httpCode}) : " . ($result['message']);
            }


        }

    }
    echo ("RAS");

}*/



function CreateAlertingMemory($MemoryUsage, $Hostname) {
    //TryToConnect();

    if($MemoryUsage>=30){
        $severity="none";
        if ($MemoryUsage > 30 and $MemoryUsage <=60){
            $severity = "LOW";
        }elseif ($MemoryUsage > 60 and $MemoryUsage <=90){
            $severity = "HIGH";
        }elseif ($MemoryUsage > 90){
            $severity = "CRITICAL";
        }
        $AlertingData = [
            "Title" => "Alerte RAM -- Utilisation à" . $MemoryUsage,
            "Description" => "La Machine". $Hostname. "est arrivé à". $MemoryUsage. "% d'utilisation du CPU à". date("d/m/Y H:i:s"),
            "application_id" => $_ENV["ID_APP"],
            "status" => "OPEN",
            "severity" => $severity,
            "start_date" => date("Y-m-d"),
        ];
        $Alert_url = "https://monitoring-app.on-forge.com/api/v1/incidents";
        $chAlert = curl_init($Alert_url);
        curl_setopt($chAlert, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($chAlert, CURLOPT_POST, true);
        curl_setopt($chAlert, CURLOPT_POSTFIELDS, json_encode($AlertingData));
        curl_setopt($chAlert, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Accept: application/json',
            'Authorization: Bearer ' . $_ENV["TOKEN"]
        ]);

        $responseAlert = curl_exec($chAlert);
        $result = json_decode($responseAlert, true);
        var_dump($result);
        $httpCodeAlert = curl_getinfo($chAlert, CURLINFO_HTTP_CODE);
        curl_close($chAlert);
        if ($httpCodeAlert === 201 || $httpCodeAlert === 200) {
            return ['id' => $result['data']['id'], 'severity' => $severity];
        }
        else {
            echo "Erreur ({$httpCodeAlert}) : " . ($result['message']);

        }

    }
    echo ("RAS");
    exit(1);

}

function CreateAlertingCPU($cpuUsage, $Hostname) {
    //TryToConnect();

    if($cpuUsage>=30){
        $severity="none";
        if ($cpuUsage > 30 and $cpuUsage <=60){
            $severity = "LOW";
        }elseif ($cpuUsage > 60 and $cpuUsage <=90){
            $severity = "HIGH";
        }elseif ($cpuUsage > 90){
            $severity = "CRITICAL";
        }
        $AlertingData = [
            "Title" => "Alerte CPU -- Utilisation à" . $cpuUsage,
            "Description" => "La Machine". $Hostname. "est arrivé à". $cpuUsage. "% d'utilisation du CPU à". date("d/m/Y H:i:s"),
            "application_id" => $_ENV["APP_ID"],
            "status" => "OPEN",
            "severity" => $severity,
            "start_date" => date("Y-m-d"),
        ];
        $Alert_url = "https://monitoring-app.on-forge.com/api/v1/incidents";
        $chAlert = curl_init($Alert_url);
        curl_setopt($chAlert, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($chAlert, CURLOPT_POST, true);
        curl_setopt($chAlert, CURLOPT_POSTFIELDS, json_encode($AlertingData));
        curl_setopt($chAlert, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Accept: application/json',
            'Authorization: Bearer ' . $_ENV["TOKEN"]
        ]);

        $responseAlert = curl_exec($chAlert);
        $result = json_decode($responseAlert, true);
        $httpCodeAlert = curl_getinfo($chAlert, CURLINFO_HTTP_CODE);
        curl_close($chAlert);
        if ($httpCodeAlert === 201 || $httpCodeAlert === 200) {
            return ['id' => $result['data']['id'], 'severity' => $severity];
        }
        else {
            echo "Erreur ({$httpCodeAlert}) : " . ($result['message']);

        }

    }
    echo ("RAS");
    exit(1);

}
function CreateAlertingDisk($diskUsage, $Hostname) {
   // TryToConnect();

    if($diskUsage>=30){
        $severity="none";
        if ($diskUsage > 30 and $diskUsage <=60){
            $severity = "LOW";
        }elseif ($diskUsage > 60 and $diskUsage <=90){
            $severity = "HIGH";
        }elseif ($diskUsage > 90){
            $severity = "CRITICAL";
        }
        $AlertingData = [
            "Title" => "Alerte CPU -- Utilisation à" . $diskUsage,
            "Description" => "La Machine". $Hostname. "est arrivé à". $diskUsage. "% d'utilisation du disque à". date("d/m/Y H:i:s"),
            "application_id" => $_ENV["ID_APP"],
            "status" => "OPEN",
            "severity" => $severity,
            "start_date" => date("Y-m-d"),
        ];
        $Alert_url = "https://monitoring-app.on-forge.com/api/v1/incidents";
        $chAlert = curl_init($Alert_url);
        curl_setopt($chAlert, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($chAlert, CURLOPT_POST, true);
        curl_setopt($chAlert, CURLOPT_POSTFIELDS, json_encode($AlertingData));
        curl_setopt($chAlert, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Accept: application/json',
            'Authorization: Bearer ' . $_ENV["TOKEN"]
        ]);

        $responseAlert = curl_exec($chAlert);
        $result = json_decode($responseAlert, true);
        $httpCodeAlert = curl_getinfo($chAlert, CURLINFO_HTTP_CODE);
        curl_close($chAlert);
        if ($httpCodeAlert === 201 || $httpCodeAlert === 200) {
            return ($result['data']['id'] and  $severity);
        }
        else {
            echo "Erreur ";

        }

    }
    echo ("RAS");
    exit(1);

}
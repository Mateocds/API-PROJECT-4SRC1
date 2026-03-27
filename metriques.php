<?php

require_once 'API/Alerting.php';

function fmt_date() {
    return date('D, d M Y H:i:s T');
}

function run_ps($command) {
    return trim(shell_exec(
        'powershell -NoProfile -Command "' . $command . '"'
    ));
}

function get_health() {
    $hostname = run_ps('hostname');
    $os       = run_ps('(Get-WmiObject Win32_OperatingSystem).Caption');

    $cpu    = get_cpu();
    $memory = get_memory();
    $disk   = get_disk();

    $cpuUsage    = $cpu["total_usage_percent"] ?? 0;
    $memoryUsage = $memory["used_percent"] ?? 0;
    $diskUsage   = $disk["used_percent"] ?? 0;

    $status = "UP";

    if ($cpuUsage > 90 || $memoryUsage > 90 || $diskUsage > 90) {
        $status = "DOWN";
    } elseif ($cpuUsage > 70 || $memoryUsage > 70 || $diskUsage > 70) {
        $status = "WARNING";
    }

    return [
        "status"     => $status,
        "hostname"   => $hostname,
        "os"         => "windows",
        "platform"   => $os,
        "checked_at" => fmt_date()
    ];
}

function get_cpu() {
    $usage    = run_ps('(Get-WmiObject Win32_Processor | Measure-Object -Property LoadPercentage -Average).Average');
    $logical  = run_ps('(Get-WmiObject Win32_ComputerSystem).NumberOfLogicalProcessors');
    $physical = run_ps('(Get-WmiObject Win32_Processor).NumberOfCores');
    $hostname = run_ps('hostname');

    if ($usage >= 30){
         $result = CreateAlertingCPU($usage, $hostname);

        return [
            "total_usage_percent" => (float) $usage,
            "logical_cores"       => (int) $logical,
            "physical_cores"      => (int) $physical,
            "checked_at"          => fmt_date(),
            "alert_triggered"  =>  true,
            "incident" => [
                "id" => $result["id"],
                "severity" => $result["severity"],
                "message"=> "Incident created on monitoring platform"

            ]
        ];
    }else {
        return [
            "total_usage_percent" => (float) $usage,
            "logical_cores"       => (int) $logical,
            "physical_cores"      => (int) $physical,
            "checked_at"          => fmt_date(),
            "alert_triggered"    => false
        ];
    }


}

function get_incidents() {
    $Incidents_url = "https://monitoring-app.on-forge.com/api/v1/incidents";
    $chIncidents = curl_init($Incidents_url);
    curl_setopt($chIncidents, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($chIncidents, CURLOPT_HTTPGET, true);
    curl_setopt($chIncidents, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Accept: application/json',
        'Authorization: Bearer ' . $_ENV["TOKEN"]
    ]);

    $responseAlert = curl_exec($chIncidents);
    $result = json_decode($responseAlert, true);
    curl_close($chIncidents);

    $incidents = $result['data'];
    $formattedIncidents = array_map(function($incident) {
        return [
            "id"         => $incident['id'],
            "title"      => $incident['title'],
            "severity"   => $incident['severity'],
            "status"     => $incident['status'],
            "start_date" => $incident['started_at'],
        ];
    }, $incidents);

    $response = [
        "incidents"  => $formattedIncidents,
        "total"      => count($formattedIncidents),
        "checked_at" => date("D, d M Y H:i:s T"),
    ];

    header('Content-Type: application/json');
    echo json_encode($response, JSON_PRETTY_PRINT);
}
function get_memory() {
    $total_bytes = run_ps('(Get-WmiObject Win32_ComputerSystem).TotalPhysicalMemory');
    $free_bytes  = run_ps('(Get-WmiObject Win32_OperatingSystem).FreePhysicalMemory * 1024');

    $total = round($total_bytes / 1e9);
    $free  = round($free_bytes  / 1e9);
    $used  = $total - $free;
    $pct   = round(($used / $total) * 100, 2);
    $hostname = run_ps('hostname');

    if ($pct > 30 ){
        $result = CreateAlertingMemory($pct, $hostname);
        return [
            "total_gb"     => $total,
            "used_gb"      => $used,
            "free_gb"      => $free,
            "used_percent" => $pct,
            "checked_at"   => fmt_date(),
            "alert_triggered" => true,
            "incident" => [
                "id" => $result["id"],
                "severity" => $result["severity"],
                "message"=> "Incident created on monitoring platform"
            ]
        ];
    }else{
        return [
            "total_gb"     => $total,
            "used_gb"      => $used,
            "free_gb"      => $free,
            "used_percent" => $pct,
            "checked_at"   => fmt_date(),
            "alert_triggered" => false,
        ];
    }
    }




function get_disk() {
    $total = disk_total_space('C:');
    $free  = disk_free_space('C:');
    $used  = $total - $free;
    $pct   = round(($used / $total) * 100, 2);
    $hostname = run_ps('hostname');

    if ($pct > 30 ){
        $result = CreateAlertingDisk($pct, $hostname);
        return [
            "total_gb"     => round($total / 1e9),
            "used_gb"      => round($used  / 1e9),
            "free_gb"      => round($free  / 1e9),
            "used_percent" => $pct,
            "checked_at"   => fmt_date(),
            "alert_triggered" => true,
            "incident" => [
                "id" => $result["id"],
                "severity" => $result["severity"],
                "message"=> "Incident created on monitoring platform"
            ]
        ];
    }else {
        return [
            "total_gb"     => round($total / 1e9),
            "used_gb"      => round($used  / 1e9),
            "free_gb"      => round($free  / 1e9),
            "used_percent" => $pct,
            "checked_at"   => fmt_date(),
            "alert_triggered" => false,
        ];

    }

}

function get_all() {
    return [
        "host_info"   => get_health(),
        "cpu_info"    => get_cpu(),
        "memory_info" => get_memory(),
        "disk_info"   => get_disk()
    ];
}
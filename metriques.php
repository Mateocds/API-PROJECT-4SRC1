<?php

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

    return [
        "status"     => "UP",
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

    return [
        "total_usage_percent" => (float) $usage,
        "logical_cores"       => (int) $logical,
        "physical_cores"      => (int) $physical,
        "checked_at"          => fmt_date()
    ];
}

function get_memory() {
    $total_bytes = run_ps('(Get-WmiObject Win32_ComputerSystem).TotalPhysicalMemory');
    $free_bytes  = run_ps('(Get-WmiObject Win32_OperatingSystem).FreePhysicalMemory * 1024');

    $total = round($total_bytes / 1e9);
    $free  = round($free_bytes  / 1e9);
    $used  = $total - $free;
    $pct   = round(($used / $total) * 100, 2);

    return [
        "total_gb"     => $total,
        "used_gb"      => $used,
        "free_gb"      => $free,
        "used_percent" => $pct,
        "checked_at"   => fmt_date()
    ];
}

function get_disk() {
    $total = disk_total_space('C:');
    $free  = disk_free_space('C:');
    $used  = $total - $free;
    $pct   = round(($used / $total) * 100, 2);

    return [
        "total_gb"     => round($total / 1e9),
        "used_gb"      => round($used  / 1e9),
        "free_gb"      => round($free  / 1e9),
        "used_percent" => $pct,
        "checked_at"   => fmt_date()
    ];
}

function get_all() {
    return [
        "host_info"   => get_health(),
        "cpu_info"    => get_cpu(),
        "memory_info" => get_memory(),
        "disk_info"   => get_disk()
    ];
}
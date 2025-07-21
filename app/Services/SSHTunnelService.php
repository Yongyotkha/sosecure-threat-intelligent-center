<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Log;

class SSHTunnelService
{
    protected $config;

    public function __construct()
    {
        $this->config = [
            'host' => env('SSH_HOST', '10.129.0.93'),
            'port' => env('SSH_PORT', 22),
            'username' => env('SSH_USERNAME', 'sosecure'),
            'password' => env('SSH_PASSWORD'),
            'localPort' => env('SSH_TUNNEL_LOCAL_PORT', 3307),
            'remoteHost' => env('SSH_TUNNEL_REMOTE_HOST', '127.0.0.1'),
            'remotePort' => env('SSH_TUNNEL_REMOTE_PORT', 3306),
        ];
    }

    public function createTunnel()
    {
        // ตรวจสอบ config
        foreach (['host', 'username', 'password', 'localPort', 'remoteHost', 'remotePort'] as $key) {
            if (empty($this->config[$key])) {
                throw new Exception("Missing SSH config: $key");
            }
        }

        $sshUser = $this->config['username'];
        $sshHost = $this->config['host'];
        $sshPort = $this->config['port'];
        $localPort = $this->config['localPort'];
        $remoteHost = $this->config['remoteHost'];
        $remotePort = $this->config['remotePort'];
        $sshPassword = $this->config['password'];

        // ใช้ sshpass สำหรับส่งรหัสผ่านผ่าน SSH แบบไม่ interactive
        $cmd = "sshpass -p '{$sshPassword}' ssh -o StrictHostKeyChecking=no -N -L {$localPort}:{$remoteHost}:{$remotePort} {$sshUser}@{$sshHost} -p {$sshPort} > /dev/null 2>&1 &";

        exec($cmd);

        Log::info("🚀 [SSH TUNNEL - Linux] Executing: {$cmd}");
    }

    public function isTunnelRunning($port = 3307)
    {
        // เช็คว่า port นั้นมี process listen อยู่หรือไม่
        $output = shell_exec("lsof -i :$port | grep LISTEN");
        return !empty($output);
    }
}

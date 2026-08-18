<?php

namespace App\Services;

use phpseclib\Net\SSH2;
use Exception;
use Illuminate\Support\Facades\Log;


class SSHTunnelService
{
    protected $ssh;
    protected $config;

    public function __construct()
    {
        $this->config = [
            'host' => env('SSH_HOST', '10.129.0.93'),
            'port' => env('SSH_PORT', 22),
            'username' => env('SSH_USERNAME', 'sosecure'),
            'password' => env('SSH_PASSWORD'),
        ];
    }

    public function connect()
    {
        // ตรวจสอบ config
        if (empty($this->config['host']) || empty($this->config['username']) || empty($this->config['password'])) {
            throw new Exception('SSH configuration is missing. Please check your .env file');
        }

        try {
            $this->ssh = new SSH2($this->config['host'], $this->config['port']);

            if (!$this->ssh->login($this->config['username'], $this->config['password'])) {
                throw new Exception('SSH Authentication failed');
            }

            return true;
        } catch (\Exception $e) {
            throw new Exception('SSH Connection failed: ' . $e->getMessage());
        }
    }

    public function createTunnel()
    {
        // $plinkPath = base_path('vendor/PuTTY/plink.exe');
        // 'D:/xampp/htdocs/threat-intelligent-center/vendor/PuTTY/plink.exe';
        
        $plinkPath = base_path('vendor/PuTTY/plink.exe');
        if (!is_file($plinkPath)) {
            Log::warning("[SSH TUNNEL] plink.exe not found at {$plinkPath}; skip createTunnel");
            return false;
        }

        $sshUser = env('sshUser');
        $sshHost = env('sshHost');
        $localPort = env('localPort');
        $remoteHost = env('remoteHost');
        $remotePort = env('remotePort');
        $sshPassword = env('SSH_PASSWORD');

        $cmd = "start /B \"\" \"{$plinkPath}\" -ssh {$sshUser}@{$sshHost} -pw {$sshPassword} -L {$localPort}:{$remoteHost}:{$remotePort} -N -batch";

        // ✅ สั่งให้ Windows รันคำสั่งนี้จริง
        pclose(popen($cmd, "r"));

        // ✅ Log เพื่อ debug
        Log::info("🚀 [SSH TUNNEL] Executing: {$cmd}");
        return true;
    }

    public function disconnect()
    {
        if ($this->ssh) {
            $this->ssh->disconnect();
            $this->ssh = null;
        }
    }

    public function isConnected()
    {
        return $this->ssh && $this->ssh->isConnected();
    }

    public function isTunnelRunning($port = 3307)
    {
        // Windows เท่านั้น
        $output = shell_exec("netstat -ano | findstr :$port");

        return strpos($output, 'LISTENING') !== false;
    }
}

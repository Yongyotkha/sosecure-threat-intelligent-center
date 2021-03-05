<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class RemoveFileLogFromStorage extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:RemoveFileLogFromStorage';
    protected $description = 'RemoveFileLogFromStorage';

    
    public function __construct(){
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle(){
        $this -> runCommand();
    }

    private function runCommand(){
        try{
            Storage::disk('local')->deleteDirectory('/logs_dialy');
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
        
    }
}

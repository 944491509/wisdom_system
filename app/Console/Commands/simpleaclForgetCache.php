<?php

namespace App\Console\Commands;

use App\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class simpleaclForgetCache extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'simpleaclForgetCache';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '权限管理重置缓存';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        Cache::forget('simpleacl.getpermissionsBytype_1');
        Cache::forget('simpleacl.getpermissionsBytype_2');
        Cache::forget('simpleacl.getpermissionsBytype_9');
        $user1 = User::where('type', '=', '1')->get();
        foreach ($user1 as $user) {
            Cache::forget('simpleacl.getpermissionsByuserid_' . $user->id);
            Cache::forget('simpleacl.getmenuByuserid_' . $user->id);
        }
        $user2 = User::where('type', '=', '2')->get();
        foreach ($user2 as $user) {
            Cache::forget('simpleacl.getpermissionsByuserid_' . $user->id);
            Cache::forget('simpleacl.getmenuByuserid_' . $user->id);
        }
        $user9 = User::where('type', '=', '9')->get();
        foreach ($user9 as $user) {
            Cache::forget('simpleacl.getpermissionsByuserid_' . $user->id);
            Cache::forget('simpleacl.getmenuByuserid_' . $user->id);
        }
    }
}

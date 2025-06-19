<?php

namespace App\Console\Commands;

use App\Traits\PushNotificationTrait;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Madnest\Madzipper\Facades\Madzipper;

class DatabaseRefresh extends Command
{
    use PushNotificationTrait;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'database:refresh';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Refresh database after a certain time';

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
     * @return void
     */
    public function handle()
    {
        $this->demoResetNotification();
        Artisan::call('db:wipe');
        Artisan::call('cache:clear');

        Cache::put('demo_database_refresh', 1, 120);

        $sql_path = base_path('demo/database.sql');
        DB::unprepared(file_get_contents($sql_path));
        File::deleteDirectory('storage/app/public');
        Madzipper::make('demo/public.zip')->extractTo('storage/app');

        Cache::forget('demo_database_refresh');
    }
}

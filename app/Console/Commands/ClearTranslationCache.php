<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Artisan;

class ClearTranslationCache extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'translations:clear';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear all translation caches';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Clearing translation caches...');
        
        // Clear Laravel's translation cache
        Cache::forget('lang_ar.js');
        Cache::forget('lang_en.js');
        
        // Clear config cache
        Artisan::call('config:clear');
        
        // Clear view cache
        Artisan::call('view:clear');
        
        // Clear route cache
        Artisan::call('route:clear');
        
        $this->info('Translation caches cleared successfully!');
        
        return 0;
    }
}

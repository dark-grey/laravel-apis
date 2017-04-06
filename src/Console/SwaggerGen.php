<?php

namespace Sitesoft\LaravelApis\Console;

class SwaggerGen extends Apis
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'swaggen';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate Swagger Json File';

    protected $swagger_json = 'swagger.json';
    protected $swagger_controller = 'SwaggerController';
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
        $this->info("Collecting documentation ...");
        $paths = (array)config($this->config_paths_key);

        if (count($paths) == 0) {
            $this->error('Сначала задайте пути в файле настроек '.$this->config_paths_key);
            return false;
        }

        foreach ($paths as $path) {
            $full_path = app_path('Http/Controllers/'.$path);
            \Swagger\scan($full_path, ['exclude' => '/v[0-9]+/'])->saveAs($full_path.'/'.$this->swagger_json);
            $this->line('Written to '.$full_path.'/'.$this->swagger_json);
        }

        $this->info('Success!');
    }

}

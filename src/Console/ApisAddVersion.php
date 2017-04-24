<?php

namespace Sitesoft\LaravelApis\Console;

use Illuminate\Support\Facades\File;

class ApisAddVersion extends Apis
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'apis:add-version 
                            {version : New API project version} 
                            {url : Base URL to this version API} 
                            {--P|path= : Relative path to api directory}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Add new version on Kapibara';

    protected $apis_url = 'http://kapibara.sitesoft.ru/api/add-version';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->config_token_key = $this->config_file.'.'.$this->config_token_name;

        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $token = config($this->config_token_key);
        if (!isset($token)) {
            $this->error("Key '".$this->config_token_key."' not found in config.");
            return false;
        }

        $apiDir = ($this->option('path') != null) ? str_replace('/', '\\', $this->option('path')) : 'Api_v'.$this->argument('version');
        $this->addSwaggerController($apiDir);

        $data = array(
            "version" => $this->argument('version'),
            "url" => $this->argument('url'),
            "token" => $token
        );

        $ch = curl_init();

        curl_setopt_array($ch, array(
            CURLOPT_URL => $this->apis_url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 20,
            CURLOPT_HEADER => false,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 1,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $data
        ));

        $content = curl_exec($ch);
        if ($content == false || curl_getinfo($ch, CURLINFO_HTTP_CODE) != 200) {
            $this->error("Request error. Code: ".curl_getinfo($ch, CURLINFO_HTTP_CODE));
            return false;
        }

        $input = json_decode($content);
        if (!isset($input)) {
            $this->error("Can't parse input data.");
            return false;
        }

        if ($input->success == false) {
            $errors = (isset($input->errors)) ? $input->errors : null;
            $this->showErrors($errors);
            return false;
        } else {
            $route = $this->getSwaggerRoute($apiDir);
            try {
                if (!File::append(base_path('routes/api.php'), "\n".$route))
                    $this->showRouteError($route);
                else
                    $this->info("Маршрут '$route' добавлен в 'routes/api.php'");
            } catch (\Exception $e) {
                $this->showRouteError($route);
            }
            $this->info("Версия #" . $this->argument('version') . " успешно добавлена.");

            $apiDir = str_replace('\\', '/', $apiDir);
            $this->writePathToConfig($apiDir);
        }
        curl_close($ch);
    }
}

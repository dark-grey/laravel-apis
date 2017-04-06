<?php

namespace Sitesoft\LaravelApis\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use October\Rain\Config\Rewrite;

class Apis extends Command
{
    protected $config_file = 'apis';
    protected $config_token_name = 'token';
    protected $config_token_key;
    protected $config_paths_name = 'paths';
    protected $config_paths_key;
    protected $swagger_controller = 'SwaggerController';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->config_token_key = $this->config_file.'.'.$this->config_token_name;
        $this->config_paths_key = $this->config_file.'.'.$this->config_paths_name;

        parent::__construct();
    }

    /**
     * @return string
     */
    public function writeNewToken() {
        if (config($this->config_token_key) === null)
            copy(__DIR__ . '/../../config/'.$this->config_file.'.php', config_path($this->config_file.'.php'));

        if (function_exists('random_bytes')) {
            $token = bin2hex(random_bytes(30));
        } else {
            $token = sha1(mt_rand(1, getrandmax()));
        }
        $this->writeToConfig($this->config_token_name, $token);
        $this->info("Новый токен записан в 'config/".$this->config_file.".php'");

        return $token;
    }

    /**
     * @param string $apiDir
     */
    public function addSwaggerController($apiDir)
    {
        $fileContent = File::get(__DIR__ . "/../../app/Http/Controllers/Api/".$this->swagger_controller.'.php');

        $fileContent = str_replace('namespace App\Http\Controllers\Api;', 'namespace App\Http\Controllers\\'.$apiDir.';', $fileContent);

        $apiDir = str_replace('\\', '/', $apiDir);
        $apiPath = app_path('Http/Controllers/'.$apiDir.'/'.$this->swagger_controller.'.php');

        File::put($apiPath, $fileContent);
        $this->info('Файл '.$this->swagger_controller.'.php добавлен в app/Http/Controllers/'.$apiDir.'/');
    }

    /**
     * @param string $path
     * @return string
     */
    public function writePathToConfig($path) {
        if (config($this->config_paths_key) === null) {
            $token = config($this->config_token_key);
            copy(__DIR__ . '/../../config/' . $this->config_file . '.php', config_path($this->config_file . '.php'));
            $this->writeToConfig($this->config_token_name, $token);
        }

        $paths = (array)config($this->config_paths_key);
        if (!in_array($path, $paths)) {
            $paths[] = $path;

            $this->writeToConfig($this->config_paths_name, $paths);
            $this->info("Новый путь к api записан в 'config/".$this->config_file.".php'");
        } else {
            $this->info($path." уже существует в 'config/".$this->config_file.".php'");
        }
    }

    /**
     * @param string $apiDir
     * @return string
     */
    public function getSwaggerRoute($apiDir) {
        $path = preg_replace('/^\/api/', '', parse_url($this->argument('url'), PHP_URL_PATH), 1);
        if (!ends_with($path, '/'))
            $path .= '/';
        return "Route::get('".$path."swagger', '$apiDir\\".$this->swagger_controller."@index');";
    }

    /**
     * @param mixed $messages
     */
    public function showErrors($messages = null)
    {
        if (isset($messages) && $messages) {
            $messages = (array)$messages;
            foreach ($messages as $message)
                $this->error($message);
        } else
            $this->error("Unknown error.");
    }

    /**
     * @param string $route
     */
    public function showRouteError($route) {
        $this->error("Can not add route '$route' to 'routes/api.php'. You must do it manually.");
    }

    /**
     * @param string $key
     * @param mixed $value
     */
    public function writeToConfig($key, $value) {
        config([$this->config_file.'.'.$key => $value]);
        $writeConfig = new Rewrite();
        $writeConfig->toFile(config_path($this->config_file.'.php'), [$key => $value]);
    }
}

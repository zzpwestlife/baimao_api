<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
//        $this->app->bind('App\Repositories\Contracts\Seec\UserRepository', 'App\Repositories\Seec\UserRepositoryEloquent');
        $this->batchBind();
    }

    /**
     * 批量绑定 新增类时无需再手动添加绑定关系
     */
    protected function batchBind()
    {
        $rootNamespace = config('repository.generator.rootNamespace');
        $repositories = $rootNamespace . config('repository.generator.paths.repositories');
        $interfaces = $rootNamespace . config('repository.generator.paths.interfaces');
        $interfacePath = str_replace('\\', '/', config('repository.generator.paths.interfaces'));
        $files = getFiles(app_path($interfacePath), true);
        $classNames = null;
        if (!empty($files)) {
            foreach ($files as $index => $file) {
                $classNames[] = str_replace('/', '\\',
                    substr(substr($file, strpos($file, $interfacePath), -4), strlen($interfacePath) + 1));
            }
            $flip = array_flip($classNames);
            if (isset($flip['BaseRepositoryInterface'])) {
                unset($classNames[$flip['BaseRepositoryInterface']]);
            }
            if (!empty($classNames)) {
                foreach ($classNames as $index => $className) {
                    $this->app->bind($interfaces . '\\' . $className, $repositories . '\\' . $className . 'Eloquent');
                }
            }
        }
    }

}

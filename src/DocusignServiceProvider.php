<?php namespace Tjphippen\Docusign;

use Illuminate\Support\ServiceProvider;

class DocusignServiceProvider extends ServiceProvider
{
    protected $defer = true;

    public function boot()
    {
        $this->publishes([
            __DIR__.'/config/config.php' => config_path('docusign.php'),
        ]);
    }

    public function register()
    {
        $this->app->bindShared('docusign', function ($app)
        {
            return new Docusign($app->config->get('docusign', array()));
        });

        $this->app->booting(function()
        {
            $loader = \Illuminate\Foundation\AliasLoader::getInstance();
            $loader->alias('Docusign', 'Tjphippen\Docusign\Facades\Docusign');
        });
    }

    public function provides()
    {
        return ['docusign'];
    }

}
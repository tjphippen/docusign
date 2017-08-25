<?php namespace Tjphippen\Docusign;

use Illuminate\Support\ServiceProvider;
use Illuminate\Foundation\AliasLoader;

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
        $this->app->singleton('docusign', function ($app)
        {
            return new Docusign($app->config->get('docusign', array()));
        });

        $this->app->booting(function()
        {
            AliasLoader::getInstance()->alias('Docusign', 'Tjphippen\Docusign\Facades\Docusign');
        });
    }

    public function provides()
    {
        return ['docusign'];
    }

}

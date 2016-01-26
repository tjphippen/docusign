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
        $this->app->bind('docusign', function ()
        {
            return new Docusign(config('docusign'));
        });

        $this->app->alias('Docusign', \Tjphippen\Docusign\Facades\Docusign::class);
    }

    public function provides()
    {
        return ['docusign'];
    }

}
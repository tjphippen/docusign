<?php

namespace Tjphippen\Docusign;


use Illuminate\Support\ServiceProvider;

class DocusignLumenServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
        $this->app->configure('docusign');
    }

    public function register()
    {
        $this->app->bind('docusign', function ()
        {
            return new Docusign(config('docusign', array()));
        });

        if ( ! class_exists('Docusign') ) {
            class_alias('Tjphippen\Docusign\Facades\Docusign', 'Docusign');
        }
    }
}
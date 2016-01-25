<?php

namespace Tjphippen\Docusign;


use Illuminate\Support\ServiceProvider;

class DocusignLumenServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind('docusign', function ($app)
        {
            return new Docusign($app->config->get('docusign', array()));
        });

        $this->app->alias('Docusign', \Tjphippen\Docusign\Facades\Docusign::class);
    }
}
<?php
namespace plokko\TableHelper;

use Blade;
use Illuminate\Support\ServiceProvider;
use League\Flysystem\Adapter\Local;
use Plokko\LocaleManager\Console\GenerateCommand;
use Plokko\LocaleManager\LocaleManager;

class TableHelperServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'table-helper');
        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views/vendor/table-helper'),
            //__DIR__.'/../config/config.php' => config_path('table-helper.php'),
        ]);

        /*//--- Console commands ---///
        if ($this->app->runningInConsole())
        {
            $this->commands([
                GenerateCommand::class,
            ]);
        }
        */
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        /*// Merge default config ///
        $this->mergeConfigFrom(
            __DIR__.'/../config/config.php', 'table-helper'
        );

        // Facade accessor
        $this->app->bind(LocaleManager::class, function($app) {
            return new LocaleManager();
        });

        ///Blade directive
        Blade::directive('locales', function ($locale=null) {
            $lm = \App::make(LocaleManager::class);
            $urls = $lm->listLocaleUrls();
            return '<script src="<?php echo optional('.(var_export($urls,true)).')['.($locale?var_export($locale,true):'App::getLocale()').']; ?>" ></script>';
        });
        */
    }

    public function provides()
    {
        return [
            TableBuilder::class,
            //TableHelper::class,
        ];
    }
}

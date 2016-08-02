<?php namespace Vendor\DecimaModule;

// use Vendor\DecimaModule\Module\ModuleTable;

use Carbon\Carbon;

use Illuminate\Support\ServiceProvider;

class DecimaModuleServiceProvider extends ServiceProvider {

	/**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
	 */
	protected $defer = false;

	/**
	* Bootstrap any application services.
	*
	* @return void
	*/
	public function boot()
	{
		include __DIR__.'/../../routes.php';

		// include __DIR__.'/../../helpers.php';

		$this->loadViewsFrom(__DIR__.'/../../views', 'decima-module');

		$this->loadTranslationsFrom(__DIR__.'/../../lang', 'decima-module');

		$this->publishes([
				__DIR__ . '/../../config/config.php' => config_path('module-general.php'),
		], 'config');

		$this->mergeConfigFrom(
				__DIR__ . '/../../config/config.php', 'module-general'
		);

		$this->publishes([
				__DIR__ . '/../../config/journal.php' => config_path('module-journal.php'),
		], 'config');

		$this->mergeConfigFrom(
				__DIR__ . '/../../config/journal.php', 'module-journal'
		);

		$this->publishes([
    __DIR__.'/../../migrations/' => database_path('/migrations')
		], 'migrations');

		// $this->registerJournalConfiguration();

		$this->registerEmpleadoInterface();

		$this->registerEmpleadoManagementInterface();
	}

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		//
	}

	/**
	* Register a new organization trigger.
	*
	* @return void
	*/
	protected function registerJournalConfiguration()
	{
		$journalConfiguration = $this->app->make('AppJournalConfigurations');

		$this->app->instance('AppJournalConfigurations', array_merge($journalConfiguration, $this->app['config']->get('module-journal')));
	}

	protected function registerEmpleadoInterface()
	{
		$this->app->bind('Vendor\DecimaModule\Module\Repositories\Empleado\EmpleadoInterface', function($app)
		{
			$AuthenticationManager = $app->make('App\Kwaai\Security\Services\AuthenticationManagement\AuthenticationManagementInterface');
			return new \Vendor\DecimaModule\Module\Repositories\Empleado\EloquentEmpleado( new \Vendor\DecimaModule\Module\Empleado() , $AuthenticationManager->getCurrentUserOrganizationConnection());

				});
	}
	/**
	* Register a ... interface instance.
	*
	* @return void
	*/
	protected function registerEmpleadoManagementInterface()
	{
		$this->app->bind('Vendor\DecimaModule\Module\Services\EmpleadoManagement\EmpleadoManagementInterface', function($app)
		{
			return new \Vendor\DecimaModule\Module\Services\EmpleadoManagement\EmpleadoManager(
				$app->make('App\Kwaai\Security\Services\AuthenticationManagement\AuthenticationManagementInterface'),
				$app->make('App\Kwaai\Security\Services\JournalManagement\JournalManagementInterface'),
				$app->make('App\Kwaai\Security\Repositories\Journal\JournalInterface'),
				new	\Mgallegos\LaravelJqgrid\Encoders\JqGridJsonEncoder($app->make('excel')),
				new	\Vendor\DecimaModule\Module\Repositories\Empleado\EloquentEmpleadoGridRepository(
					$app['db'],
					$app->make('App\Kwaai\Security\Services\AuthenticationManagement\AuthenticationManagementInterface'),
					$app['translator']
				),
				$app->make('Vendor\DecimaModule\Module\Repositories\Empleado\EmpleadoInterface'),
				new Carbon(),
				$app['db'],
				$app['translator'],
				$app['config']
			);
		});
	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return [];
	}

}

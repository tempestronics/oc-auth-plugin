<?php namespace Mohsin\Auth\Classes;

use Closure;
use Illuminate\Auth\AuthManager as LaravelAuthManager;
use System\Classes\PluginManager;
use Mohsin\Rest\Classes\ApiManager;

/**
 * Manages all the Auth mechanisms.
 *
 * @package Mohsin.Auth
 * @author Saifur Rahman Mohsin
 */
class AuthManager extends LaravelAuthManager
{
    use \October\Rain\Support\Traits\Singleton;

    /**
     * @var collection Cache of the registered API nodes.
     */
    protected $availableAuthMechanisms = [];

    /**
     * @var System\Classes\PluginManager
     */
    protected $pluginManager;

    /**
     * @var Illuminate\Auth\AuthManage
     */
    protected $authManager;

    /**
     * Initialize this singleton.
     */
    protected function init()
    {
        // TODO: Move this to a web UI configurator.
        app()['config']['auth'] = [
            'defaults' => [
                'guard' => 'api',
                'passwords' => 'user'
            ],
            'providers' => [
                'user' => [
                    'driver' => 'eloquent',
                    'model' => \Backend\Models\User::class,
                ]
            ],
            'guards' => [
                'api' => [
                  'driver' => 'passport',
                  'provider' => 'user',
                ]
            ]
        ];
        $this->pluginManager = PluginManager::instance();
        $this->authManager = new LaravelAuthManager(app());
        $this->discoverAndRegisterAuthMechanisms();
    }

    /**
     * Registers the Auth mechanisms exposed by various auth plugins
     * @return void
     */
    public function discoverAndRegisterAuthMechanisms()
    {
        $plugins = $this->pluginManager->getPlugins();

        foreach ($plugins as $id => $plugin) {
            if (!method_exists($plugin, 'registerAuthMechanisms')) {
                continue;
            }

            $auth_mechanisms = $plugin->registerAuthMechanisms();
            if (!is_array($auth_mechanisms)) {
                continue;
            }

            $this->registerAuthMechanisms($id, $auth_mechanisms);
        }
    }

    public function getAvailableAuthMechanisms()
    {
        return array_filter(
            array_combine(
                array_keys($this->availableAuthMechanisms),
                array_column($this->availableAuthMechanisms, 'name')
            )
        );
    }

    /**
     * Registers the Auth mechanism exposed by a plugin into the system.
     * The argument is an array of the auth mechanisms and their configurations.
     * @param string $owner Specifies the owner plugin of the auth mechanism in the format Author.Plugin.
     * @param array $nodes An array of the auth mechanisms the plugin exposes.
     * @return void
     */
    public function registerAuthMechanisms($owner, array $auth_mechanisms)
    {
        foreach ($auth_mechanisms as $id => $mechanism) {
            $auth_mechanism = (object) [
                'owner'       => $owner,
                'name'        => $mechanism['name'],
                'identifier'  => $id,
                'callback'    => $mechanism['callback']
            ];

            $this->availableAuthMechanisms[$id] = $auth_mechanism;
        }
    }

    public function handle($request, Closure $next)
    {
        $fullPath = $request->path();
        $apiManager = ApiManager::instance();
        $prefix = $apiManager->getPrefix();

        if (substr($fullPath, 0, strlen($prefix)) == $prefix) {
            $path = substr($fullPath, strlen($prefix));
        }
        $mechanism = $apiManager->extendableCall('getAuthMechanismFromPath', [$path]);

        if (array_key_exists($mechanism, $this->availableAuthMechanisms)) {
            return call_user_func($this->availableAuthMechanisms[$mechanism]->callback, $request, $next);
        } else {
            return $next($request);
        }
    }
}

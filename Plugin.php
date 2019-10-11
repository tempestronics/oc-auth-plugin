<?php namespace Mohsin\Auth;

use App;
use Backend;
use System\Classes\PluginBase;
use Mohsin\Rest\Models\Node;
use Mohsin\Rest\Classes\ApiManager;
use Mohsin\Auth\Classes\AuthManager;
use Mohsin\Auth\Providers\BasicAuthProvider;
use Mohsin\Rest\Controllers\Settings as ApiSettingsController;

/**
 * Auth Plugin Information File
 */
class Plugin extends PluginBase
{
    /**
     * @var array Plugin dependencies
     */
    public $require = ['Mohsin.Rest'];

    /**
     * Returns information about this plugin.
     *
     * @return array
     */
    public function pluginDetails()
    {
        return [
            'name'        => 'Auth',
            'description' => 'Auth plugin for OctoberCMS',
            'author'      => 'Saifur Rahman Mohsin',
            'icon'        => 'icon-lock'
        ];
    }

    /**
     * Boot method, called right before the request route.
     *
     * @return array
     */
    public function boot()
    {
        App::singleton('auth', function () {
            return AuthManager::instance();
        });

        ApiManager::instance()->addDynamicMethod('getAuthMechanismFromPath', function ($path) {
                $node = Node::where('path', '=', $path)->first();
                return array_get($node->extras, 'auth_mechanism', 'open');
        });

        ApiSettingsController::extendListColumns(function ($list, $model) {
            $list->addColumns([
                'auth_mechanism' => [
                    'label' => 'Auth Mechanism',
                    'type'  => 'partial',
                    'path'  => '$/mohsin/auth/assets/partials/auth_dropdown.htm',
                    'options' => AuthManager::instance()->getAvailableAuthMechanisms()
                ]
            ]);
        });

        ApiSettingsController::extend(function ($controller) {
            $controller->addDynamicMethod('onAuthMechanism', function () {
                $data = post();
                $recordId = array_get($data, 'id');
                $auth_mechanism = array_get($data, 'auth_mechanisms')[$recordId];

                $record = \Mohsin\Rest\Models\Node::find($recordId);
                $record->addExtra('auth_mechanism', $auth_mechanism);
            });
        });
    }

    public function registerAuthMechanisms()
    {
        return [
            'open' => [
              'name'     => 'Open',
              'callback' => function ($request, $next) {
                  return $next($request);
              }
            ]
        ];
    }
}

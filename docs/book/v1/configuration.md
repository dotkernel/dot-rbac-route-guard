# Configuration

As with many Dotkernel modules, we focus on the configuration based approach of customizing the module for your needs.

After installing, merge the module's `ConfigProvider` with your application's config to make sure required dependencies and default module configuration are registered.
Create a configuration file for this module in your `config/autoload` folder.

## authorization-guards.global.php

```php
return [
    'dot_authorization' => [
    
        //define how it will treat non-matching guard rules, allow all by default
        'protection_policy' => \Dot\Rbac\Guard\GuardInterface::POLICY_ALLOW,
        
        'event_listeners' => [
            [
                'type' => 'class or service name of the listener',
                'priority' => 1,
            ],
        ],
        
        //define custom guards here
        'guard_manager' => [],
        
        //register custom guards providers here
        'guards_provider_manager' => [],
        
        //define which guards provider to use, along with its configuration
        //the guards provider should know how to build a list of GuardInterfaces based on its configuration
        'guards_provider' => [
            'type' => 'ArrayGuards',
            'options' => [
                'guards' => [
                    [
                        'type' => 'Route',
                        'options' => [
                            'rules' => [
                                'premium' => ['admin'],
                                'login' => ['guest'],
                                'logout' => ['admin', 'user', 'viewer'],
                                'account' => ['admin', 'user'],
                                'home' => ['*'],
                            ]
                        ]
                    ],
                    [
                        'type' => 'RoutePermission',
                        'options' => [
                            'rules' => [
                                'premium' => ['premium'],
                                'account' => ['my-account'],
                                'logout' => ['only-logged'],
                            ]
                        ]
                    ],
                ]
            ],
        ],

        //overwrite default messages
        'messages_options' => [
            'messages' => [
                //MessagesOptions::UNAUTHORIZED => 'You must sign in first to access the requested content',
                //MessagesOptions::FORBIDDEN => 'You don\'t have enough permissions to access the requested content',
            ]
        ],
    ],
];
```

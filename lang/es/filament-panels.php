<?php

return [
    'pages' => [
        'auth' => [
            'login' => [
                'title' => 'Iniciar sesión',
                'heading' => 'Iniciar sesión en tu cuenta',
                'form' => [
                    'email' => [
                        'label' => 'Correo electrónico',
                    ],
                    'password' => [
                        'label' => 'Contraseña',
                    ],
                    'remember' => [
                        'label' => 'Recordarme',
                    ],
                    'actions' => [
                        'authenticate' => [
                            'label' => 'Iniciar sesión',
                        ],
                    ],
                ],
                'actions' => [
                    'request_password_reset' => [
                        'label' => '¿Olvidaste tu contraseña?',
                    ],
                    'register' => [
                        'before' => '¿No tienes una cuenta?',
                        'label' => 'Crear una cuenta',
                    ],
                ],
                'messages' => [
                    'failed' => 'Estas credenciales no coinciden con nuestros registros.',
                ],
            ],
        ],
    ],

    'resources' => [
        'pages' => [
            'create_record' => [
                'title' => 'Crear :label',
                'breadcrumb' => 'Crear',
            ],
            'edit_record' => [
                'title' => 'Editar :label',
                'breadcrumb' => 'Editar',
            ],
            'list_records' => [
                'title' => ':label',
                'navigation_label' => ':label',
            ],
            'view_record' => [
                'title' => 'Ver :label',
                'breadcrumb' => 'Ver',
            ],
        ],
    ],

    'layout' => [
        'actions' => [
            'logout' => [
                'label' => 'Cerrar sesión',
            ],
            'open_database_notifications' => [
                'label' => 'Abrir notificaciones',
            ],
            'open_user_menu' => [
                'label' => 'Menú de usuario',
            ],
            'sidebar' => [
                'collapse' => [
                    'label' => 'Contraer barra lateral',
                ],
                'expand' => [
                    'label' => 'Expandir barra lateral',
                ],
            ],
            'theme_switcher' => [
                'dark' => [
                    'label' => 'Cambiar a tema oscuro',
                ],
                'light' => [
                    'label' => 'Cambiar a tema claro',
                ],
                'system' => [
                    'label' => 'Cambiar a tema del sistema',
                ],
            ],
        ],
    ],

    'widgets' => [
        'account' => [
            'widget' => [
                'actions' => [
                    'profile' => [
                        'label' => 'Perfil',
                    ],
                ],
            ],
        ],
    ],
];

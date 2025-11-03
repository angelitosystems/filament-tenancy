<?php

return [
    'attach' => [
        'single' => [
            'label' => 'Adjuntar',
            'modal' => [
                'heading' => 'Adjuntar :label',
                'fields' => [
                    'record_id' => [
                        'label' => ':label',
                    ],
                ],
                'actions' => [
                    'attach' => [
                        'label' => 'Adjuntar',
                    ],
                    'attach_another' => [
                        'label' => 'Adjuntar y adjuntar otro',
                    ],
                ],
            ],
            'notifications' => [
                'attached' => [
                    'title' => 'Adjuntado',
                ],
            ],
        ],
    ],

    'associate' => [
        'single' => [
            'label' => 'Asociar',
            'modal' => [
                'heading' => 'Asociar :label',
                'fields' => [
                    'record_id' => [
                        'label' => ':label',
                    ],
                ],
                'actions' => [
                    'associate' => [
                        'label' => 'Asociar',
                    ],
                    'associate_another' => [
                        'label' => 'Asociar y asociar otro',
                    ],
                ],
            ],
            'notifications' => [
                'associated' => [
                    'title' => 'Asociado',
                ],
            ],
        ],
    ],

    'clone' => [
        'single' => [
            'label' => 'Clonar',
            'modal' => [
                'heading' => 'Clonar :label',
                'actions' => [
                    'clone' => [
                        'label' => 'Clonar',
                    ],
                ],
            ],
            'notifications' => [
                'cloned' => [
                    'title' => 'Clonado',
                ],
            ],
        ],
    ],

    'create' => [
        'single' => [
            'label' => 'Crear :label',
            'modal' => [
                'heading' => 'Crear :label',
                'actions' => [
                    'create' => [
                        'label' => 'Crear',
                    ],
                    'create_another' => [
                        'label' => 'Crear y crear otro',
                    ],
                ],
            ],
            'notifications' => [
                'created' => [
                    'title' => 'Creado',
                ],
            ],
        ],
    ],

    'delete' => [
        'single' => [
            'label' => 'Eliminar',
            'modal' => [
                'heading' => 'Eliminar :label',
                'actions' => [
                    'delete' => [
                        'label' => 'Eliminar',
                    ],
                ],
            ],
            'notifications' => [
                'deleted' => [
                    'title' => 'Eliminado',
                ],
            ],
        ],
        'multiple' => [
            'label' => 'Eliminar seleccionados',
            'modal' => [
                'heading' => 'Eliminar :label seleccionados',
                'actions' => [
                    'delete' => [
                        'label' => 'Eliminar',
                    ],
                ],
            ],
            'notifications' => [
                'deleted' => [
                    'title' => 'Eliminados',
                ],
            ],
        ],
    ],

    'detach' => [
        'single' => [
            'label' => 'Separar',
            'modal' => [
                'heading' => 'Separar :label',
                'actions' => [
                    'detach' => [
                        'label' => 'Separar',
                    ],
                ],
            ],
            'notifications' => [
                'detached' => [
                    'title' => 'Separado',
                ],
            ],
        ],
        'multiple' => [
            'label' => 'Separar seleccionados',
            'modal' => [
                'heading' => 'Separar :label seleccionados',
                'actions' => [
                    'detach' => [
                        'label' => 'Separar',
                    ],
                ],
            ],
            'notifications' => [
                'detached' => [
                    'title' => 'Separados',
                ],
            ],
        ],
    ],

    'dissociate' => [
        'single' => [
            'label' => 'Desasociar',
            'modal' => [
                'heading' => 'Desasociar :label',
                'actions' => [
                    'dissociate' => [
                        'label' => 'Desasociar',
                    ],
                ],
            ],
            'notifications' => [
                'dissociated' => [
                    'title' => 'Desasociado',
                ],
            ],
        ],
        'multiple' => [
            'label' => 'Desasociar seleccionados',
            'modal' => [
                'heading' => 'Desasociar :label seleccionados',
                'actions' => [
                    'dissociate' => [
                        'label' => 'Desasociar',
                    ],
                ],
            ],
            'notifications' => [
                'dissociated' => [
                    'title' => 'Desasociados',
                ],
            ],
        ],
    ],

    'edit' => [
        'single' => [
            'label' => 'Editar',
            'modal' => [
                'heading' => 'Editar :label',
                'actions' => [
                    'save' => [
                        'label' => 'Guardar cambios',
                    ],
                ],
            ],
            'notifications' => [
                'saved' => [
                    'title' => 'Guardado',
                ],
            ],
        ],
    ],

    'force_delete' => [
        'single' => [
            'label' => 'Eliminar permanentemente',
            'modal' => [
                'heading' => 'Eliminar permanentemente :label',
                'actions' => [
                    'delete' => [
                        'label' => 'Eliminar',
                    ],
                ],
            ],
            'notifications' => [
                'deleted' => [
                    'title' => 'Eliminado',
                ],
            ],
        ],
        'multiple' => [
            'label' => 'Eliminar permanentemente seleccionados',
            'modal' => [
                'heading' => 'Eliminar permanentemente :label seleccionados',
                'actions' => [
                    'delete' => [
                        'label' => 'Eliminar',
                    ],
                ],
            ],
            'notifications' => [
                'deleted' => [
                    'title' => 'Eliminados',
                ],
            ],
        ],
    ],

    'replicate' => [
        'single' => [
            'label' => 'Replicar',
            'modal' => [
                'heading' => 'Replicar :label',
                'actions' => [
                    'replicate' => [
                        'label' => 'Replicar',
                    ],
                ],
            ],
            'notifications' => [
                'replicated' => [
                    'title' => 'Replicado',
                ],
            ],
        ],
    ],

    'restore' => [
        'single' => [
            'label' => 'Restaurar',
            'modal' => [
                'heading' => 'Restaurar :label',
                'actions' => [
                    'restore' => [
                        'label' => 'Restaurar',
                    ],
                ],
            ],
            'notifications' => [
                'restored' => [
                    'title' => 'Restaurado',
                ],
            ],
        ],
        'multiple' => [
            'label' => 'Restaurar seleccionados',
            'modal' => [
                'heading' => 'Restaurar :label seleccionados',
                'actions' => [
                    'restore' => [
                        'label' => 'Restaurar',
                    ],
                ],
            ],
            'notifications' => [
                'restored' => [
                    'title' => 'Restaurados',
                ],
            ],
        ],
    ],

    'view' => [
        'single' => [
            'label' => 'Ver',
        ],
    ],
];

<?php

return [
    'actions' => [
        'create' => 'Crear',
        'edit' => 'Editar', 
        'view' => 'Ver',
        'delete' => 'Eliminar',
        'save' => 'Guardar',
        'cancel' => 'Cancelar',
        'close' => 'Cerrar',
        'confirm' => 'Confirmar',
        'discard' => 'Descartar',
        'remove' => 'Quitar',
        'restore' => 'Restaurar',
        'reorder' => 'Reordenar',
        'move_up' => 'Mover arriba',
        'move_down' => 'Mover abajo',
        'collapse' => 'Contraer',
        'expand' => 'Expandir',
        'attach' => 'Adjuntar',
        'detach' => 'Separar',
        'associate' => 'Asociar',
        'dissociate' => 'Desasociar',
        'add' => 'Agregar',
        'add_between' => 'Agregar entre',
        'clone' => 'Clonar',
    ],

    'modal' => [
        'confirmation' => [
            'title' => 'Confirmar',
        ],
    ],

    'table' => [
        'actions' => [
            'filter' => 'Filtrar',
            'open_bulk_actions' => 'Abrir acciones masivas',
            'toggle_columns' => 'Alternar columnas',
        ],
        'bulk_actions' => [
            'label' => 'Acciones masivas',
        ],
        'columns' => [
            'text' => [
                'actions' => [
                    'collapse_list' => 'Mostrar :count menos',
                    'expand_list' => 'Mostrar :count más',
                ],
            ],
        ],
        'empty' => [
            'heading' => 'No se encontraron registros',
        ],
        'filters' => [
            'actions' => [
                'remove' => 'Quitar filtro',
                'remove_all' => 'Quitar todos los filtros',
                'reset' => 'Restablecer',
            ],
            'heading' => 'Filtros',
            'indicator' => 'Filtros activos',
            'multi_select' => [
                'placeholder' => 'Todos',
            ],
            'select' => [
                'placeholder' => 'Todos',
            ],
            'trashed' => [
                'label' => 'Registros eliminados',
                'only_trashed' => 'Solo eliminados',
                'with_trashed' => 'Con eliminados',
                'without_trashed' => 'Sin eliminados',
            ],
        ],
        'grouping' => [
            'fields' => [
                'group' => 'Agrupar por',
                'direction' => 'Dirección de agrupación',
            ],
        ],
        'reorder_indicator' => 'Arrastra y suelta los registros en orden.',
        'search' => [
            'label' => 'Buscar',
            'placeholder' => 'Buscar',
            'indicator' => 'Buscar',
        ],
        'sorting' => [
            'fields' => [
                'column' => 'Ordenar por',
                'direction' => 'Dirección de ordenamiento',
            ],
        ],
    ],

    'pagination' => [
        'label' => 'Navegación de paginación',
        'overview' => 'Mostrando :first a :last de :total resultados',
        'fields' => [
            'records_per_page' => [
                'label' => 'por página',
                'options' => [
                    'all' => 'Todos',
                ],
            ],
        ],
        'actions' => [
            'go_to_page' => [
                'label' => 'Ir a la página :page',
            ],
            'next' => [
                'label' => 'Siguiente',
            ],
            'previous' => [
                'label' => 'Anterior',
            ],
        ],
    ],
];

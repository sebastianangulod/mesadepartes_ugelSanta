<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default filesystem disk that should be used
    | by the framework. The "local" disk, as well as a variety of cloud
    | based disks are available to your application for file storage.
    |
    */

    'default' => env('FILESYSTEM_DISK', 'local'),

    /*
    |--------------------------------------------------------------------------
    | Disco activo para documentos de trámites
    |--------------------------------------------------------------------------
    |
    | Nombre del disco donde el sistema guarda y busca los PDFs.
    | 'documentos' por defecto (local). No cambiar salvo migración
    | de almacenamiento indicada por el área de TI.
    |
    */

    'documentos_disk' => env('FILESYSTEM_DOCUMENTOS_DISK', 'documentos'),

    /*
    |--------------------------------------------------------------------------
    | Filesystem Disks
    |--------------------------------------------------------------------------
    |
    | Below you may configure as many filesystem disks as necessary, and you
    | may even configure multiple disks for the same driver. Examples for
    | most supported storage drivers are configured here for reference.
    |
    | Supported drivers: "local", "ftp", "sftp", "s3"
    |
    */

    'disks' => [

        'local' => [
            'driver' => 'local',
            'root' => storage_path('app/private'),
            'serve' => true,
            'throw' => false,
            'report' => false,
        ],

        'public' => [
            'driver' => 'local',
            'root' => storage_path('app/public'),
            'url' => env('APP_URL').'/storage',
            'visibility' => 'public',
            'throw' => false,
            'report' => false,
        ],

        /*
        |--------------------------------------------------------------------------
        | Disco de documentos del sistema
        |--------------------------------------------------------------------------
        |
        | Todos los PDFs de trámites (adjuntos, oficios, vouchers) se guardan
        | en este disco. Por defecto es local (misma carpeta que 'public',
        | sin migrar nada). Para escalar a NAS o nube S3-compatible basta
        | cambiar FILESYSTEM_DOCUMENTOS_* en el .env, sin tocar código.
        |
        */

        'documentos' => [
            'driver' => env('FILESYSTEM_DOCUMENTOS_DRIVER', 'local'),
            'root' => env('FILESYSTEM_DOCUMENTOS_ROOT', storage_path('app/public')),
            'url' => env('FILESYSTEM_DOCUMENTOS_URL'),
            'visibility' => 'private',
            'throw' => false,
            'report' => false,
            'key' => env('FILESYSTEM_DOCUMENTOS_S3_KEY'),
            'secret' => env('FILESYSTEM_DOCUMENTOS_S3_SECRET'),
            'region' => env('FILESYSTEM_DOCUMENTOS_S3_REGION', 'us-east-1'),
            'bucket' => env('FILESYSTEM_DOCUMENTOS_S3_BUCKET'),
            'endpoint' => env('FILESYSTEM_DOCUMENTOS_S3_ENDPOINT'),
            'use_path_style_endpoint' => env('FILESYSTEM_DOCUMENTOS_S3_PATH_STYLE', false),
        ],

        's3' => [
            'driver' => 's3',
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'region' => env('AWS_DEFAULT_REGION'),
            'bucket' => env('AWS_BUCKET'),
            'url' => env('AWS_URL'),
            'endpoint' => env('AWS_ENDPOINT'),
            'use_path_style_endpoint' => env('AWS_USE_PATH_STYLE_ENDPOINT', false),
            'throw' => false,
            'report' => false,
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Symbolic Links
    |--------------------------------------------------------------------------
    |
    | Here you may configure the symbolic links that will be created when the
    | `storage:link` Artisan command is executed. The array keys should be
    | the locations of the links and the values should be their targets.
    |
    */

    'links' => [
        public_path('storage') => storage_path('app/public'),
    ],

];

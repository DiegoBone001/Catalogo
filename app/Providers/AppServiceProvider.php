<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Registrar el driver de Firebase Storage
        \Illuminate\Support\Facades\Storage::extend('firebase', function ($app, $config) {
            $storageClient = new \Google\Cloud\Storage\StorageClient([
                'keyFilePath' => base_path(env('FIREBASE_CREDENTIALS')),
                'projectId' => $config['project_id'] ?? env('FIREBASE_PROJECT_ID'),
            ]);
            
            $bucketName = $config['bucket'] ?? env('FIREBASE_STORAGE_BUCKET');
            $bucket = $storageClient->bucket($bucketName);
            
            $adapter = new \League\Flysystem\GoogleCloudStorage\GoogleCloudStorageAdapter($bucket);
            
            // Configurar el Filesystem con la URL base correcta de Firebase Storage
            $filesystem = new \League\Flysystem\Filesystem($adapter, [
                'url' => "https://firebasestorage.googleapis.com/v0/b/{$bucketName}/o"
            ]);
            
            return new \Illuminate\Filesystem\FilesystemAdapter(
                $filesystem,
                $adapter,
                array_merge($config, [
                    'url' => "https://firebasestorage.googleapis.com/v0/b/{$bucketName}/o",
                    'bucket' => $bucketName,
                ])
            );
        });
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Sushi\Sushi;

class Photo extends Model
{
    use Sushi;

    protected $sku = ''; // Default SKU
    protected $folder = ''; // Default folder

    /**
     * Set SKU dynamically
     */
    public function setSku($sku)
    {
        $this->sku = $sku;
    }

    /**
     * Set folder dynamically
     */
    public function setFolder($folder)
    {
        $this->folder = $folder;
    }

    /**
     * Get rows from external API
     *
     * @return array
     */
    public function getRows()
    {
        // Call the API with SKU and folder
        $response = Http::get(env('PHOTO_API_URL') . '/get-photos', [
            'sku' => $this->sku,
            'folder' => $this->folder,
        ])->json();

        // Check if response is successful
        if (Arr::get($response, 'status') !== 'success') {
            return [];
        }

        // Map the photos data to the required fields
        $photos = Arr::map($response['photos'] ?? [], function ($item) {
            return Arr::only($item, [
                'id',
                'name',
                'uploadedAt',
                'url',
            ]);
        });

        return $photos;
    }
    
    // /**
    //  * Upload images to the external API
    //  *
    //  * @param string $sku
    //  * @param string $folder
    //  * @param array $images
    //  * @param string $salesId
    //  * @return array
    //  */
    // public static function uploadImages(string $sku, string $folder, array $images, string $salesId = 'admin'): array
    // {
    //     $formData = [
    //         'sku' => $sku,
    //         'type' => $folder,
    //         'sales_id' => $salesId,
    //     ];
        
    //     $files = [];
    //     foreach ($images as $index => $image) {
    //         $files["images[$index]"] = $image;
    //     }
        
    //     $response = Http::attach(
    //         array_map(function ($file, $key) {
    //             return [$key, file_get_contents($file->getPathname()), $file->getClientOriginalName()];
    //         }, $files, array_keys($files))
    //     )->post(env('PHOTO_API_URL', 'http://34.50.83.56:5000') . '/upload-images-media', $formData);
    //     
    //     return $response->json();
    // }
}
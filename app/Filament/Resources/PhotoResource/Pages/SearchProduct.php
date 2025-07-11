<?php

namespace App\Filament\Resources\PhotoResource\Pages;

use App\Filament\Resources\PhotoResource;
use App\Models\Photo;
use Filament\Resources\Pages\Page;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Illuminate\Contracts\View\View;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Filament\Forms\Components\FileUpload;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;

class SearchProduct extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string $resource = PhotoResource::class;

    protected static string $view = 'filament.resources.photo-resource.pages.search-product';
    
    
    public ?array $data = [];
    public string $sku = '';
    public string $folder = 'QC'; // Default folder
    public array $photos = [];
    public bool $isLoading = false;
    public bool $hasSearched = false;
    
    // Lightbox properties
    public bool $isLightboxOpen = false;
    public ?array $currentPhoto = null;
    public int $currentPhotoIndex = 0;
    
    // Delete confirmation modal
    public bool $isDeleteModalOpen = false;

    public function mount(Request $request): void
    {
        $this->form->fill();

        // Ambil query param sku dan folder jika ada
        $sku = $request->query('sku');
        $folder = $request->query('folder');

        if ($sku) {
            $this->sku = $sku;
            $this->form->fill(['sku' => $sku]);
            $this->hasSearched = true;
        }
        if ($folder) {
            $this->folder = $folder;
        }
        if ($sku) {
            $this->fetchPhotos();
        }
    }

    public function form(Form $form): Form
    {
        return $form
        ->schema([
            TextInput::make('sku')
                ->label('Enter SKU')
                ->required()
                ->placeholder('Enter SKU (e.g: 1000001)')
                ->prefixIcon('heroicon-o-magnifying-glass'),
        ])
        ->statePath('data');
    }

    public function submit(): void
    {
        $data = $this->form->getState();
        $this->sku = $data['sku'];
        $this->hasSearched = true;
    
        // Redirect ke URL dengan query param sku dan folder (Livewire way)
        $this->redirect('/photos?sku=' . urlencode($this->sku) . '&folder=' . urlencode($this->folder));
    }

    public function changeFolder($folder)
    {
        $this->folder = $folder;
    
        // Redirect ke URL dengan query param sku dan folder (Livewire way)
        $this->redirect('/photos?sku=' . urlencode($this->sku) . '&folder=' . urlencode($this->folder));
    }

    public function fetchPhotos()
    {
        $this->isLoading = true;
        
        // Create a new Photo model instance and set the SKU and folder
        $photo = new Photo();
        $photo->setSku($this->sku);
        $photo->setFolder($this->folder);
        
        // Get the photos from the API
        $this->photos = $photo->getRows();
        $this->isLoading = false;
    }
    
    // Lightbox methods
    public function openLightbox($photoId)
    {
        foreach ($this->photos as $index => $photo) {
            if ($photo['id'] === $photoId) {
                $this->currentPhoto = $photo;
                $this->currentPhotoIndex = $index;
                $this->isLightboxOpen = true;
                break;
            }
        }
    }
    
    public function closeLightbox()
    {
        $this->isLightboxOpen = false;
        $this->currentPhoto = null;
    }
    
    public function nextPhoto()
    {
        if (count($this->photos) > 0) {
            $this->currentPhotoIndex = ($this->currentPhotoIndex + 1) % count($this->photos);
            $this->currentPhoto = $this->photos[$this->currentPhotoIndex];
        }
    }
    
    public function previousPhoto()
    {
        if (count($this->photos) > 0) {
            $this->currentPhotoIndex = ($this->currentPhotoIndex - 1 + count($this->photos)) % count($this->photos);
            $this->currentPhoto = $this->photos[$this->currentPhotoIndex];
        }
    }
    
    // Delete confirmation methods
    public function confirmDeletePhoto()
    {
        $this->isDeleteModalOpen = true;
    }
    
    public function cancelDeletePhoto()
    {
        $this->isDeleteModalOpen = false;
    }
    
    public function deletePhoto()
    {
        // Check permission before proceeding
        if (!Auth::user()->hasPermissionTo('photo.delete')) {
            Notification::make()
                ->title('Permission Denied')
                ->body('You do not have permission to delete images.')
                ->danger()
                ->send();
            return;
        }
        
        if (!$this->currentPhoto) {
            Notification::make()
                ->title('Error')
                ->body('No photo selected for deletion.')
                ->warning()
                ->send();
            return;
        }
        
        $salesId = auth()->user()->name;
        $photoName = $this->currentPhoto['name'];
        
        // Prepare request data
        $requestData = [
            'sku' => $this->sku,
            'type' => $this->folder,
            'sales_id' => $salesId,
            'links' => [$photoName]
        ];
        
        try {
            $response = Http::delete(env('PHOTO_API_URL') . '/delete-images', $requestData);
            
            if ($response->successful()) {
                Notification::make()
                    ->title('Photo Deleted')
                    ->body('The photo has been deleted successfully.')
                    ->success()
                    ->send();
                
                // Close modals
                $this->isDeleteModalOpen = false;
                $this->isLightboxOpen = false;
                
                // Refresh the photos list
                $this->fetchPhotos();
            } else {
                Notification::make()
                    ->title('Delete Failed')
                    ->body('There was an error deleting the photo: ' . $response->body())
                    ->danger()
                    ->send();
            }
        } catch (\Exception $e) {
            Notification::make()
                ->title('Delete Failed')
                ->body('There was an error deleting the photo: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function uploadImages($images)
    {
        // Check permission before proceeding
        if (!Auth::user()->hasPermissionTo('photo.create')) {
            Notification::make()
                ->title('Permission Denied')
                ->body('You do not have permission to upload images.')
                ->danger()
                ->send();
            return;
        }
        
        // Get the current user's ID
        $salesId = auth()->user()->name;
        
        // Create a new multipart form request
        $formData = [
            'sku' => $this->sku,
            'type' => $this->folder,
            'sales_id' => $salesId,
        ];
        
        try {
            // Create a new HTTP request
            $request = Http::asMultipart();
            
            // Add form data
            foreach ($formData as $key => $value) {
                $request = $request->attach($key, $value);
            }
            
            // Process each image
            foreach ($images as $index => $path) {
                $fullPath = storage_path('app/public/' . $path);
                
                if (!file_exists($fullPath)) {
                    Notification::make()
                        ->title('Upload Failed')
                        ->body("File not found: {$path}")
                        ->danger()
                        ->send();
                    return;
                }
                
                $filename = basename($path);
                $mimeType = mime_content_type($fullPath);
                
                // Use 'images' as the key for all image files
                $request = $request->attach(
                    'images', 
                    file_get_contents($fullPath), 
                    $filename, 
                    ['Content-Type' => $mimeType]
                );
            }
            
            // Send the request to the external API
            $response = $request->post(env('PHOTO_API_URL') . '/upload-images-media');
            
            if ($response->successful()) {
                Notification::make()
                    ->title('Images Uploaded')
                    ->body('Your images have been uploaded successfully.')
                    ->success()
                    ->send();
                
                // Refresh the photos list
                $this->fetchPhotos();
            } else {
                Notification::make()
                    ->title('Upload Failed')
                    ->body('There was an error uploading your images: ' . $response->body())
                    ->danger()
                    ->send();
            }
        } catch (\Exception $e) {
            Notification::make()
                ->title('Upload Failed')
                ->body('There was an error uploading your images: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }

    protected function getActions(): array
    {
        $actions = [];
        
        if ($this->hasSearched) {
            // Only show upload action if user has photo.create permission and has searched
            if (Auth::user()->hasPermissionTo('photo.create')) {
                $actions[] = Action::make('upload')
                    ->label('Upload Images')
                    ->icon('heroicon-o-arrow-up-tray')
                    ->form([
                        FileUpload::make('images')
                            ->label('Select Images')
                            ->multiple()
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/jpg'])
                            ->maxSize(5120) // 5MB max size
                            ->required()
                    ])
                    ->action(function (array $data): void {
                        $this->uploadImages($data['images']);
                    });
            }
        }
        
        return $actions;
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('submit')
                ->label('Search')
                ->submit('submit'),
        ];
    }
}
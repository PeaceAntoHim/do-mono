<x-filament-panels::page>
    <div class="space-y-6">
        <div class="p-6 rounded-xl shadow">
            <form wire:submit="submit" class="flex items-end gap-4">
                <div class="flex-1">
                    {{ $this->form }}
                </div>

                <x-filament::button
                    type="submit"
                    wire:loading.attr="disabled"
                    wire:loading.class="opacity-70 cursor-wait"
                    wire:target="submit">
                    <span wire:loading.remove wire:target="submit">Search</span>
                    <span wire:loading wire:target="submit" class="flex items-center gap-1">
                        Search
                    </span>
                </x-filament::button>
            </form>
        </div>

        @if($hasSearched)
        <x-filament::section>
            <div class="flex justify-between mb-10">
                <div>
                    <x-filament::input.wrapper>
                        <x-filament::input.select
                            id="folder"
                            wire:change="changeFolder($event.target.value)"
                            wire:loading.attr="disabled">
                            <option value="QC" {{ $folder === 'QC' ? 'selected' : '' }}>Quality Control</option>
                            <option value="SRV" {{ $folder === 'SRV' ? 'selected' : '' }}>Service</option>
                            <option value="MKT" {{ $folder === 'MKT' ? 'selected' : '' }}>Marketing</option>
                        </x-filament::input.select>
                    </x-filament::input.wrapper>
                </div>
                <div class="text-sm text-gray-600">
                    {{ count($photos) }} photos found
                </div>
            </div>

            <!-- Add a spacer div to increase the gap -->
            <div class="h-8"></div>

            <div wire:loading wire:target="fetchPhotos, changeFolder, submit" class="flex justify-center items-center py-8">
                <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-primary-500"></div>
            </div>

            <div wire:loading.remove wire:target="fetchPhotos, changeFolder, submit">
                @if(empty($photos))
                <x-filament::section.heading>
                    No photos found for this SKU and folder.
                </x-filament::section.heading>
                @else
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-4 gap-6">
                    @foreach($photos as $photo)
                    <!-- Use a button instead of a div to ensure clickability -->
                    <button
                        type="button"
                        wire:click="openLightbox('{{ $photo['id'] }}')"
                        class="text-left bg-transparent border-0 p-0 w-full focus:outline-none">
                        <x-filament::card class="relative hover:shadow-md transition-shadow duration-200">
                            <div class="aspect-w-1 aspect-h-1 mb-3">
                                <img src="{{ $photo['url'] }}" alt="{{ $photo['name'] }}" class="object-cover rounded-lg w-full h-40">
                            </div>
                            <div class="text-sm">
                                <p class="font-medium truncate">{{ $photo['name'] }}</p>
                                <p class="text-gray-500 text-xs">{{ \Carbon\Carbon::parse($photo['uploadedAt'])->format('Y-m-d H:i:s') }}</p>
                            </div>
                        </x-filament::card>
                    </button>
                    @endforeach
                </div>
                @endif
            </div>

            <!-- Lightbox Modal with dark mode support -->
            <!-- Lightbox Modal with dark mode support -->
            @if($isLightboxOpen)
            <div
                class="fixed inset-0 z-50 flex items-center justify-center overflow-hidden"
                style="background-color: rgba(0, 0, 0, 0.85);"
                x-data="imageEditor({
        imageUrl: {{ json_encode($currentPhoto ? $currentPhoto['url'] : '') }},
        sku: {{ json_encode($sku) }},
        folder: {{ json_encode($folder) }},
        salesId: {{ json_encode(auth()->user()->name) }},
        imageName: {{ json_encode($currentPhoto ? $currentPhoto['name'] : 'rotated.jpg') }}
    })"
                x-init="$el.focus()"
                x-on:keydown.escape.window="$wire.closeLightbox()"
                x-on:keydown.arrow-right.window="$wire.nextPhoto()"
                x-on:keydown.arrow-left.window="$wire.previousPhoto()"
                tabindex="0"
                wire:click.self="closeLightbox">
                <!-- Close button moved to header -->
                <div class="relative w-full max-w-4xl mx-auto flex flex-col bg-white dark:bg-gray-800 rounded-lg shadow-2xl overflow-hidden" style="max-height: 85vh;">
                    <!-- Header with photo details -->
                    <div class="p-4 bg-white dark:bg-gray-800 flex justify-between items-center border-b border-gray-200 dark:border-gray-700">
                        <div class="flex-1 min-w-0">
                            <h3 class="font-medium text-lg dark:text-white">Photo Detail</h3>
                        </div>

                        <button
                            wire:click="closeLightbox"
                            class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 focus:outline-none focus:ring-2 focus:ring-primary-500 rounded-full p-1"
                            aria-label="Close lightbox">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>

                    <!-- Photo name and date moved to below header -->
                    <div class="p-3 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
                        <h3 class="font-medium text-lg truncate dark:text-white">{{ $currentPhoto ? $currentPhoto['name'] : '' }}</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ $currentPhoto ? \Carbon\Carbon::parse($currentPhoto['uploadedAt'])->format('Y-m-d H:i:s') : '' }}</p>
                    </div>

                    <!-- Image container with navigation buttons -->
                    <div class="flex-1 flex items-center justify-center relative bg-gray-100 dark:bg-gray-900 overflow-auto" style="min-height: 300px; max-height: calc(85vh - 160px);">
                        @if(count($photos) > 1)
                        <!-- Navigation container with flex -->
                        <div class="w-full h-full flex items-center justify-between px-3">
                            <!-- Previous button -->
                            <button
                                wire:click="previousPhoto"
                                class="bg-gray-900 dark:bg-gray-700 dark:text-white rounded-full p-2 hover:bg-gray-700 dark:hover:bg-gray-600 focus:outline-none z-10 flex-shrink-0 shadow-lg"
                                aria-label="Previous photo">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                                </svg>
                            </button>

                            <!-- Image container -->
                            <div x-ref="imageContainer" class="flex-1 h-full flex justify-center items-center px-2">
                                <!-- Original image view (shown initially) -->
                                <div class="flex h-full w-full justify-center items-center" x-show="!isEditing">
                                    <img
                                        :src="imageUrl"
                                        alt="Original image"
                                        class="max-h-full max-w-full object-contain"
                                        style="max-height: calc(85vh - 200px);" />
                                </div>

                                <!-- Canvas for editing (hidden initially) -->
                                <div class="flex h-full w-full justify-center items-center" x-show="isEditing" x-cloak>
                                    <!-- Canvas for rotated image -->
                                    <canvas id="rotatedCanvas" x-ref="canvas" class="max-h-full max-w-full object-contain"
                                        style="max-height: calc(85vh - 200px);"></canvas>
                                    <!-- Hidden original image for loading -->
                                    <img id="originalImage" :src="imageUrl" crossorigin="anonymous" style="display:none;" @load="initCanvas()" />
                                </div>
                            </div>

                            <!-- Next button -->
                            <button
                                wire:click="nextPhoto"
                                class="bg-gray-900 dark:bg-gray-700 dark:text-white rounded-full p-2 hover:bg-gray-700 dark:hover:bg-gray-600 focus:outline-none z-10 flex-shrink-0 shadow-lg"
                                aria-label="Next photo">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                </svg>
                            </button>
                        </div>
                        @else
                        <!-- If there's only one photo, show the content without navigation buttons -->
                        <div x-ref="imageContainer" class="flex-1 h-full flex justify-center items-center px-2">
                            <!-- Original image view (shown initially) -->
                            <div class="flex h-full w-full justify-center items-center" x-show="!isEditing">
                                <img
                                    :src="imageUrl"
                                    alt="Original image"
                                    class="max-h-full max-w-full object-contain"
                                    style="max-height: calc(85vh - 200px);" />
                            </div>

                            <!-- Canvas for editing (hidden initially) -->
                            <div class="flex h-full w-full justify-center items-center" x-show="isEditing" x-cloak>
                                <!-- Canvas for rotated image -->
                                <canvas id="rotatedCanvas" x-ref="canvas" class="max-h-full max-w-full object-contain"
                                    style="max-height: calc(85vh - 200px);"></canvas>
                                <!-- Hidden original image for loading -->
                                <img id="originalImage" :src="imageUrl" crossorigin="anonymous" style="display:none;" @load="initCanvas()" />
                            </div>
                        </div>
                        @endif
                    </div>

                    <div class="p-4 bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700">
                        <div class="flex justify-end gap-2">
                            <!-- Edit Mode Actions -->
                            <template x-if="!isEditing">
                                <x-filament::button
                                    color="primary"
                                    size="sm"
                                    type="button"
                                    @click="startEditing">
                                    Edit
                                </x-filament::button>
                            </template>

                            <!-- Rotation Controls (only visible in edit mode) -->
                            <template x-if="isEditing">
                                <div class="flex gap-2">
                                    <x-filament::button
                                        color="gray"
                                        size="sm"
                                        type="button"
                                        @click="rotate(-90)">
                                        <x-fas-rotate-left class="w-4 h-4 mr-1" />
                                    </x-filament::button>
                                    <x-filament::button
                                        color="gray"
                                        size="sm"
                                        type="button"
                                        @click="rotate(90)">
                                        <x-fas-rotate-right class="w-4 h-4 mr-1" />
                                    </x-filament::button>
                                    <x-filament::button
                                        color="danger"
                                        size="sm"
                                        type="button"
                                        @click="cancelEditing">
                                        Cancel
                                    </x-filament::button>
                                    <x-filament::button
                                        size="sm"
                                        type="button"
                                        @click="saveRotatedImage"
                                        x-bind:disabled="saveInProgress">
                                        <span x-show="!saveInProgress">Save</span>
                                        <span x-show="saveInProgress" class="flex items-center">
                                            <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                            </svg>
                                            Saving...
                                        </span>
                                    </x-filament::button>
                                </div>
                            </template>

                            <!-- Delete button (always visible if user has permission) -->
                            @if(Auth::user()->hasPermissionTo('photo.delete'))
                            <x-filament::button
                                color="danger"
                                wire:click="confirmDeletePhoto"
                                wire:loading.attr="disabled"
                                size="sm">
                                Delete
                            </x-filament::button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <!-- Delete Confirmation Modal with dark mode support -->
            @if($isDeleteModalOpen)
            <div
                class="fixed inset-0 z-50 flex items-center justify-center"
                style="background-color: rgba(0, 0, 0, 0.4);">
                <div class="bg-white dark:bg-gray-800 rounded-lg p-6 max-w-md w-full shadow-2xl">
                    <h2 class="text-xl font-bold mb-4 dark:text-white">Confirm Deletion</h2>
                    <p class="mb-6 dark:text-gray-300">Are you sure you want to delete "{{ $currentPhoto ? $currentPhoto['name'] : '' }}"? This action cannot be undone.</p>

                    <div class="flex justify-end gap-2">
                        <x-filament::button
                            color="gray"
                            wire:click="cancelDeletePhoto">
                            Cancel
                        </x-filament::button>

                        <x-filament::button
                            color="danger"
                            wire:click="deletePhoto"
                            wire:loading.attr="disabled">
                            Delete
                        </x-filament::button>
                    </div>
                </div>
            </div>
            @endif
        </x-filament::section>
        @endif
    </div>
</x-filament-panels::page>

<script>
    function imageEditor({
        imageUrl,
        sku,
        folder,
        salesId,
        imageName
    }) {
        return {
            imageUrl,
            sku,
            folder,
            salesId,
            imageName,
            angle: 0,
            isEditing: false,
            isLoading: false,
            saveInProgress: false,
            containerWidth: 0,
            containerHeight: 0,

            // Start editing mode
            startEditing() {
                this.isEditing = true;
                this.angle = 0; // Reset angle when starting edit

                // Measure container dimensions
                const container = this.$refs.imageContainer;
                if (container) {
                    this.containerWidth = container.clientWidth;
                    this.containerHeight = container.clientHeight;
                }

                // Load the image into canvas on next tick to ensure DOM is ready
                setTimeout(() => {
                    this.initCanvas();
                }, 50);
            },

            // Cancel editing and return to view mode
            cancelEditing() {
                this.isEditing = false;
                this.angle = 0;
            },

            // Initialize canvas with the image
            initCanvas() {
                const img = document.getElementById('originalImage');
                if (!img.complete) {
                    // If image is not loaded yet, wait for it
                    img.onload = () => this.drawImage();
                    return;
                }
                this.drawImage();
            },

            // Draw the image on canvas with rotation
            drawImage() {
                const img = document.getElementById('originalImage');
                const canvas = this.$refs.canvas;
                if (!canvas || !img) return;

                const ctx = canvas.getContext('2d');

                // Original image dimensions
                const imgWidth = img.naturalWidth;
                const imgHeight = img.naturalHeight;

                // Determine canvas dimensions based on rotation
                let canvasWidth, canvasHeight;

                if (this.angle % 180 === 0) {
                    canvasWidth = imgWidth;
                    canvasHeight = imgHeight;
                } else {
                    canvasWidth = imgHeight;
                    canvasHeight = imgWidth;
                }

                // Set canvas dimensions to match the original image
                canvas.width = canvasWidth;
                canvas.height = canvasHeight;

                // Clear the canvas
                ctx.clearRect(0, 0, canvas.width, canvas.height);

                // Move to center and rotate
                ctx.save();
                ctx.translate(canvasWidth / 2, canvasHeight / 2);
                ctx.rotate(this.angle * Math.PI / 180);
                ctx.drawImage(img, -imgWidth / 2, -imgHeight / 2);
                ctx.restore();
            },

            // Rotate the image
            rotate(delta) {
                this.angle = (this.angle + delta) % 360;
                if (this.angle < 0) this.angle += 360;
                this.drawImage();
            },

            // Save the rotated image
            saveRotatedImage() {
                if (this.isLoading || this.saveInProgress) return;

                this.isLoading = true;
                this.saveInProgress = true;
                const canvas = this.$refs.canvas;

                canvas.toBlob(blob => {
                    const formData = new FormData();
                    formData.append('sku', this.sku);
                    formData.append('type', this.folder);
                    formData.append('sales_id', this.salesId);
                    formData.append('images', blob, this.imageName);

                    // Show loading indicator
                    this.saveInProgress = true;

                    // Debug - log the URL we're posting to
                    console.log('Posting to:', '{{ env("PHOTO_API_URL") }}/upload-images-media');

                    fetch('{{ env("PHOTO_API_URL") }}/upload-images-media', {
                            method: 'POST',
                            body: formData
                        })
                        .then(response => response.json().then(data => ({
                            status: response.status,
                            body: data
                        })))
                        .then(({
                            status,
                            body
                        }) => {
                            this.isLoading = false;
                            this.saveInProgress = false;

                            if (status === 202) {
                                this.showNotification('Success', 'Image updated successfully!', 'success');
                                window.location.reload();
                            } else {
                                this.showNotification('Error', 'Upload failed: ' + (body.message || JSON.stringify(body)), 'error');
                            }
                        })
                        .catch(error => {
                            this.isLoading = false;
                            this.saveInProgress = false;
                            this.showNotification('Error', 'Upload failed: ' + error, 'error');
                        });
                }, 'image/jpeg', 0.95);
            },

            // Helper to show notifications (compatible with Filament)
            showNotification(title, message, type) {
                if (window.Livewire) {
                    window.Livewire.dispatch('notify', {
                        title: title,
                        body: message,
                        status: type,
                    });
                } else {
                    alert(`${title}: ${message}`);
                }
            }
        }
    }
</script>
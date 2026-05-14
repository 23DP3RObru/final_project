<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class ItemController extends Controller
{
    private const IMAGE_DISK = 'public';
    private const IMAGE_DIR = 'items';

    public function index()
    {
        try {
            $items = Item::with('user')->latest()->get();

            return response()->json($items);
        } catch (\Exception $e) {
            Log::error('Error fetching items: ' . $e->getMessage());

            return response()->json(['error' => 'Server error'], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate($this->storeRules());

            // Get user from request (set by CheckAuth middleware)
            if (!$request->user) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            $validated['user_id'] = $request->user->id;
            $validated['shipping_cost'] = $this->normaliseShippingCost($validated);
            $validated['images'] = $this->storeImages($request);

            $item = Item::create($validated);

            return response()->json($item->load('user'), 201);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Error creating item: ' . $e->getMessage());

            return response()->json(['error' => 'Server error'], 500);
        }
    }

    public function show(Item $item)
    {
        return response()->json($item->load('user'));
    }

    public function update(Request $request, Item $item)
    {
        try {
            // Check authorization: user must be owner or admin
            if (!$request->user) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            if ($request->user->id !== $item->user_id && !$request->user->is_admin) {
                return response()->json(['error' => 'Forbidden - You can only edit your own items'], 403);
            }

            $validated = $request->validate($this->updateRules());
            $removeImages = $request->boolean('remove_images');
            unset($validated['remove_images']);

            if (array_key_exists('shipping_type', $validated) || array_key_exists('shipping_cost', $validated)) {
                $validated['shipping_cost'] = $this->normaliseShippingCost($validated, $item);
            }

            if ($removeImages || $request->hasFile('images')) {
                $this->deleteStoredImages($item->images);
                $validated['images'] = $request->hasFile('images') ? $this->storeImages($request) : [];
            }

            $item->update($validated);

            return response()->json($item->fresh()->load('user'));
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Error updating item: ' . $e->getMessage());

            return response()->json(['error' => 'Server error'], 500);
        }
    }

    public function destroy(Request $request, Item $item)
    {
        try {
            // Check authorization: user must be owner or admin
            if (!$request->user) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            if ($request->user->id !== $item->user_id && !$request->user->is_admin) {
                return response()->json(['error' => 'Forbidden - You can only delete your own items'], 403);
            }

            $this->deleteStoredImages($item->images);
            $item->delete();

            return response()->json(null, 204);
        } catch (\Exception $e) {
            Log::error('Error deleting item: ' . $e->getMessage());

            return response()->json(['error' => 'Server error'], 500);
        }
    }

    private function storeRules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'quantity' => 'required|integer|min:0',
            'shipping_type' => 'required|in:free,flat_rate,calculated',
            'shipping_cost' => 'required_if:shipping_type,flat_rate|nullable|numeric|min:0',
            'condition' => 'nullable|string|in:Jauns,Lietots,Atjaunots',
            'category' => 'nullable|string|max:100',
            'images' => 'sometimes|array|max:6',
            'images.*' => 'image|mimes:jpeg,jpg,png,gif,webp|max:4096',
        ];
    }

    private function updateRules(): array
    {
        return [
            'name' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|nullable|string',
            'price' => 'sometimes|required|numeric|min:0',
            'quantity' => 'sometimes|required|integer|min:0',
            'shipping_type' => 'sometimes|required|in:free,flat_rate,calculated',
            'shipping_cost' => 'required_if:shipping_type,flat_rate|nullable|numeric|min:0',
            'condition' => 'sometimes|nullable|string|in:Jauns,Lietots,Atjaunots',
            'category' => 'sometimes|nullable|string|max:100',
            'images' => 'sometimes|array|max:6',
            'images.*' => 'image|mimes:jpeg,jpg,png,gif,webp|max:4096',
            'remove_images' => 'sometimes|boolean',
        ];
    }

    private function storeImages(Request $request): array
    {
        if (!$request->hasFile('images')) {
            return [];
        }

        $files = $request->file('images');
        if (!is_array($files)) {
            $files = [$files];
        }

        return collect($files)
            ->map(fn ($file) => $file->store(self::IMAGE_DIR, self::IMAGE_DISK))
            ->values()
            ->all();
    }

    private function deleteStoredImages(?array $paths): void
    {
        foreach ($paths ?? [] as $path) {
            if ($path && !str_starts_with($path, 'http://') && !str_starts_with($path, 'https://')) {
                Storage::disk(self::IMAGE_DISK)->delete($path);
            }
        }
    }

    private function normaliseShippingCost(array $data, ?Item $item = null): float
    {
        $shippingType = $data['shipping_type'] ?? $item?->shipping_type ?? 'free';

        if ($shippingType !== 'flat_rate') {
            return 0;
        }

        return (float) ($data['shipping_cost'] ?? $item?->shipping_cost ?? 0);
    }
}

<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use App\Models\Seller\Item;
use App\Models\Seller\ItemImage;

class ItemController extends Controller
{
    public function store(Request $request){

        $rules = [
            'name' => 'required',
            'category_id' => 'required',
            'item_type' => 'required',
            'size' => 'required',
            'images' => 'required',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
        ];
        
        if ($request->item_type === 'for_sale') {
            $rules['sale_price'] = 'required|numeric';
        } elseif ($request->item_type === 'for_rent') {
            $rules['rental_price'] = 'required|numeric';
        }
        
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            $errors = [];
            foreach ($validator->errors()->getMessages() as $field => $messages) {
                $errors[] = [
                    'field' => $field,
                    'message' => $messages[0]
                ];
            }
        
            return response()->json(['success' => false, 'errors' => $errors], 422);
        }

        $data = $request->all();

        // later we will use auth id of logged in user
        $data['user_id'] = 1;

        $item = Item::create($data);

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $destinationPath = public_path('item-images');
                $originalName = $image->getClientOriginalName();
                $uniqueName = time() . '_' . uniqid() . '_' . $originalName;
                $image->move($destinationPath, $uniqueName);
                ItemImage::create([
                    'item_id' => $item->id,
                    'image_name' => $uniqueName,
                ]);
            }
        }

        return response()->json(['success' => true, 'message' => 'Item created successfully!']);
    }
}

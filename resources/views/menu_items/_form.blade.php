@csrf
<div class="row">
<div class="col-md-6 mb-3"><label class="form-label">Name</label><input name="name" class="form-control" value="{{ old('name', $menuItem->name) }}" required></div>
<div class="col-md-3 mb-3"><label class="form-label">Category</label><input name="category" class="form-control" value="{{ old('category', $menuItem->category) }}" required></div>
<div class="col-md-3 mb-3"><label class="form-label">Price</label><input type="number" step="0.01" name="price" class="form-control" value="{{ old('price', $menuItem->price) }}" required></div>
<div class="col-md-3 mb-3"><label class="form-label">Stock Quantity</label><input type="number" min="0" name="stock_quantity" class="form-control" value="{{ old('stock_quantity', $menuItem->stock_quantity ?? 0) }}"></div>
<div class="col-md-3 mb-3"><label class="form-label">Low Stock Alert</label><input type="number" min="0" name="low_stock_quantity" class="form-control" value="{{ old('low_stock_quantity', $menuItem->low_stock_quantity ?? 5) }}"></div>
<div class="col-12 mb-3"><label class="form-label">Description</label><textarea name="description" class="form-control">{{ old('description', $menuItem->description) }}</textarea></div>
<div class="col-12 mb-3"><label class="form-check"><input type="checkbox" name="is_available" value="1" class="form-check-input" @checked(old('is_available', $menuItem->is_available ?? true))> <span class="form-check-label">Available</span></label></div>
</div><button class="btn btn-primary">Save Item</button><a href="{{ route('menu-items.index') }}" class="btn btn-secondary">Back</a>

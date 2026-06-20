@csrf
@if(($method ?? 'POST') !== 'POST')
    @method($method)
@endif

@if($errors->any())
    <div class="alert" style="display:grid; gap:6px;">
        <strong>Please fix these fields:</strong>
        <ul>
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="panel" style="display: grid; gap: 14px;">
    <input name="title" value="{{ old('title', $item['title'] ?? '') }}" placeholder="Event title" required>
    <select name="category_id" required>
        @foreach([1 => 'Music', 2 => 'Sport', 3 => 'Seminar', 4 => 'Festival'] as $id => $name)
            <option value="{{ $id }}" @selected((string) old('category_id', $item['category_id'] ?? '') === (string) $id)>{{ $name }}</option>
        @endforeach
    </select>
    <textarea name="description" placeholder="Description" rows="5" style="border:1px solid var(--line); border-radius:18px; padding:14px;">{{ old('description', $item['description'] ?? '') }}</textarea>
    <input name="date" type="datetime-local" value="{{ old('date', isset($item['date']) ? \Illuminate\Support\Carbon::parse($item['date'])->format('Y-m-d\TH:i') : '') }}" required>
    <input name="location" value="{{ old('location', $item['location'] ?? '') }}" placeholder="Location" required>
    <input name="price" type="number" min="0" value="{{ old('price', $item['price'] ?? '') }}" placeholder="Price" required>
    <input name="quota" type="number" min="1" value="{{ old('quota', $item['quota'] ?? '') }}" placeholder="Quota" required>
    <label>
        Event image (jpg/png/jpeg, max 5MB)
        <input name="image" type="file" accept="image/jpeg,image/jpg,image/png">
    </label>
    <button type="submit">{{ $submitLabel ?? 'Save Event' }}</button>
</div>

@extends('layouts.app')

@section('content')
<div class="container">
    <h2>{{ isset($brandGroup) ? 'Edit' : 'Add' }} Brand Group</h2>

    <form method="POST" action="{{ isset($brandGroup) ? route('brand-groups.update', $brandGroup) : route('brand-groups.store') }}">
        @csrf
        @if(isset($brandGroup))
            @method('PUT')
        @endif

        <div class="mb-3">
            <label>Supplier</label>
            <input type="text" name="supplier" class="form-control" value="{{ old('supplier', $brandGroup->supplier ?? '') }}">
        </div>

        <div class="mb-3">
            <label>Display Name</label>
            <input type="text" name="display_name" class="form-control" value="{{ old('display_name', $brandGroup->display_name ?? '') }}" required>
        </div>

        <div class="mb-3">
            <label>Variants (comma-separated)</label>
            <textarea name="variants" class="form-control" rows="3">{{ old('variants', isset($brandGroup->variants) ? implode(', ', $brandGroup->variants) : '') }}</textarea>
        </div>

        <button type="submit" class="btn btn-success">{{ isset($brandGroup) ? 'Update' : 'Create' }}</button>
        <a href="{{ route('brand-groups.index') }}" class="btn btn-secondary">Cancel</a>
    </form>
</div>
@endsection

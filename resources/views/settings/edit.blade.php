@extends('layouts.app')

@section('title', 'Настройки')

@section('content')
<div class="container" style="max-width: 600px; margin: 30px 0;">
    <h1 style="margin-bottom: 20px;">Настройки</h1>

    {{-- 🔹 Update global margin --}}
    <form action="{{ route('settings.update') }}" method="POST" style="display: flex; flex-direction: column; gap: 15px;">
        @csrf

        <label for="margin" style="font-weight: bold;">Маржа (%)</label>
        <input 
            type="number" 
            step="0.01" 
            min="0" 
            max="100"
            name="margin" 
            id="margin" 
            value="{{ old('margin', $setting->margin) }}"
            style="padding: 8px; border: 1px solid #ccc; border-radius: 5px;"
        >

        <button type="submit" 
                style="padding: 10px 15px; background: #4CAF50; color: white; border: none; border-radius: 5px; cursor: pointer; width: 140px">
            💾 Сохранить
        </button>
    </form>

    <br><hr><br>

    {{-- 🔹 Manage suppliers --}}
    <h3>Поставщики</h3>
    <form action="{{ route('settings.updateSuppliers') }}" method="POST">
        @csrf
        <ul style="list-style: none; padding: 0; margin: 0;">
            @foreach(($setting->suppliers ?? []) as $name => $active)
                <li style="margin-bottom: 5px;">
                    <label>
                        <input type="checkbox" name="suppliers[{{ $name }}]" value="1" {{ $active ? 'checked' : '' }}>
                        {{ $name }}
                    </label>
                </li>
            @endforeach
        </ul>

        <div style="margin-top: 10px;">
            <label style="font-weight: bold;">Добавить нового поставщика:</label>
            <input type="text" name="new_supplier" placeholder="Название поставщика"
                   style="padding: 8px; border: 1px solid #ccc; border-radius: 5px; width: 100%;">
        </div>

        <button type="submit"
                style="padding: 10px 15px; background: #4CAF50; color: white; border: none; border-radius: 5px; cursor: pointer; width: 160px; margin-top: 10px">
            💾 Сохранить
        </button>
    </form>

    <br><hr><br>

    {{-- 🔹 Import parts file --}}
    <form action="{{ route('parts.import') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <input type="file" name="file" accept=".xls,.xlsx">
        <br><br>
        <button type="submit" 
                style="padding: 10px 15px; background: #4CAF50; color: white; border: none; border-radius: 5px; cursor: pointer; width: 140px">
            📦 Импортировать
        </button>
    </form>
</div>
@endsection

@extends('layouts.app')

@section('title', 'Настройки')

@section('content')
<div class="container" style="max-width: 600px; margin: 30px 0;">
    <h1 style="margin-bottom: 20px;">Настройки</h1>


    <form action="{{ route('settings.update') }}" method="POST" style="display: flex; flex-direction: column; gap: 15px;">
        @csrf

        <label for="margin" style="font-weight: bold;">Маржа (%)</label>
        <input type="number" step="0.01" min="0" max="100" 
               name="margin" id="margin" 
               value="{{ old('margin', $setting->margin) }}"
               style="padding: 8px; border: 1px solid #ccc; border-radius: 5px;">

        @error('margin')
            <div style="color: red; font-size: 14px;">{{ $message }}</div>
        @enderror

        <button type="submit" 
                style="padding: 10px 15px; background: #4CAF50; color: white; border: none; border-radius: 5px; cursor: pointer; width: 140px">
            💾 Сохранить
        </button>
    </form>
</div>
@endsection

@extends('layouts.app')

@section('title', 'Добавить клиента')

@section('content')
  <div class="page-header">
    <div>Клиенты > Добавить</div>
  </div>

  <form method="POST" action="{{ route('clients.store') }}" style="background:#fff;padding:20px;border-radius:6px;">
    @csrf

    <div style="margin-bottom:15px;">
      <label>Имя</label><br>
      <input type="text" name="first_name" value="{{ old('first_name') }}" required style="width:100%;padding:8px;border:1px solid #ccc;border-radius:4px;">
      @error('first_name') <small style="color:red">{{ $message }}</small> @enderror
    </div>

    <div style="margin-bottom:15px;">
      <label>Фамилия</label><br>
      <input type="text" name="last_name" value="{{ old('last_name') }}" style="width:100%;padding:8px;border:1px solid #ccc;border-radius:4px;">
      @error('last_name') <small style="color:red">{{ $message }}</small> @enderror
    </div>

    <div style="margin-bottom:15px;">
      <label>Отчество</label><br>
      <input type="text" name="middle_name" value="{{ old('middle_name') }}" style="width:100%;padding:8px;border:1px solid #ccc;border-radius:4px;">
      @error('middle_name') <small style="color:red">{{ $message }}</small> @enderror
    </div>

    <div style="margin-bottom:15px;">
      <label>Телефон</label><br>
      <input type="text" name="phone" value="{{ old('phone') }}"  style="width:100%;padding:8px;border:1px solid #ccc;border-radius:4px;">
      @error('phone') <small style="color:red">{{ $message }}</small> @enderror
    </div>

    <div style="margin-bottom:15px;">
      <label>Email</label><br>
      <input type="email" name="email" value="{{ old('email') }}"  style="width:100%;padding:8px;border:1px solid #ccc;border-radius:4px;">
      @error('email') <small style="color:red">{{ $message }}</small> @enderror
    </div>

    <div style="margin-bottom:15px;">
  <label>Сегмент</label><br><br>
  @php
      $segments = ['Розница', 'Сотрудники', 'СТО'];
  @endphp
  @foreach($segments as $seg)
    <label style="margin-right:15px;">
      <input type="radio" name="segment" value="{{ $seg }}" {{ old('segment') === $seg ? 'checked' : '' }}>
      {{ $seg }}
    </label>
  @endforeach
  @error('segment') <br><small style="color:red">{{ $message }}</small> @enderror
</div>

    <div style="margin-bottom:15px;">
      <label>Скидка (%)</label><br>
      <input type="number" step="0.01" name="discount" value="{{ old('discount', 0) }}" style="width:100%;padding:8px;border:1px solid #ccc;border-radius:4px;">
      @error('discount') <small style="color:red">{{ $message }}</small> @enderror
    </div>

    <button type="submit" style="background:#14213d;color:#fff;padding:10px 20px;border:none;border-radius:4px;cursor:pointer;">
      Сохранить
    </button>
  </form>
@endsection

@extends('layouts.app')

@section('title', 'Редактировать клиента')

@section('content')
  <div class="page-header">
    <div>Клиенты > Редактировать</div>
  </div>

  <form method="POST" action="{{ route('clients.update', $client->id) }}" style="background:#fff;padding:20px;border-radius:6px;">
    @csrf
    @method('PUT')

    <div style="margin-bottom:15px;">
      <label>Полное имя</label><br>
      <input type="text" name="full_name" value="{{ old('full_name', $client->full_name) }}" required style="width:100%;padding:8px;border:1px solid #ccc;border-radius:4px;">
      @error('full_name') <small style="color:red">{{ $message }}</small> @enderror
    </div>

    <div style="margin-bottom:15px;">
      <label>Телефон</label><br>
      <input type="text" name="phone" value="{{ old('phone', $client->phone) }}" required style="width:100%;padding:8px;border:1px solid #ccc;border-radius:4px;">
      @error('phone') <small style="color:red">{{ $message }}</small> @enderror
    </div>

    <div style="margin-bottom:15px;">
      <label>Способ связи</label><br>
      <input type="text" name="contact_method" value="{{ old('contact_method', $client->contact_method) }}" style="width:100%;padding:8px;border:1px solid #ccc;border-radius:4px;">
    </div>

    <div style="margin-bottom:15px;">
      <label>Дата рождения</label><br>
      <input type="date" name="birth_date" value="{{ old('birth_date', $client->birth_date) }}" style="padding:8px;border:1px solid #ccc;border-radius:4px;">
    </div>

    <button type="submit" style="background:#14213d;color:#fff;padding:10px 20px;border:none;border-radius:4px;cursor:pointer;">
      Обновить
    </button>
  </form>
@endsection

@extends('layouts.app')

@section('title', 'Группы брендов')

@section('content')
<div class="container" style="margin: 30px 0;">
    <h1 style="margin-bottom: 20px;">Группы брендов</h1>

    {{-- Add new group --}}
    <form action="{{ route('brand-groups.store') }}" method="POST" style="display: flex; flex-direction: column; gap: 10px; margin-bottom: 30px;">
        @csrf
        <label style="font-weight: bold;">Название для отображения</label>
        <input type="text" name="display_name" style="padding: 8px; border: 1px solid #ccc; border-radius: 5px;" required>

        <label style="font-weight: bold;">Синонимы (через запятую)</label>
        <textarea name="aliases" style="padding: 8px; border: 1px solid #ccc; border-radius: 5px;" placeholder="пример: Тойота, TOYO, Тойота Мотор"></textarea>

        <button type="submit" style="padding: 10px 15px; background: #4CAF50; color: white; border: none; border-radius: 5px; cursor: pointer; width: 140px;">Добавить</button>
    </form>

    {{-- Existing groups --}}
    <table style="width: 100%; border-collapse: collapse;">
        <thead>
            <tr style="background: #f0f0f0;">
                <th style="padding: 8px; border: 1px solid #ccc;">Название</th>
                <th style="padding: 8px; border: 1px solid #ccc;">Синонимы</th>
                <th style="padding: 8px; border: 1px solid #ccc; width: 120px;">Действия</th>
            </tr>
        </thead>
        <tbody>
            @foreach($groups as $group)
            <tr data-id="{{ $group->id }}">
                <td style="width: 20%">
                    <input type="text" class="display-name" value="{{ $group->display_name }}" 
                           style="padding: 6px; border: 1px solid #ccc; border-radius: 4px; width: 95%">
                </td>
                <td>
                    <textarea type="text" class="grouped-names" 
                           style="padding: 6px; border: 1px solid #ccc; border-radius: 4px; width: 98%">{{ implode(',', $group->aliases) }}</textarea>
                </td>
                <td style="display:flex; gap:4px;">
                    <button class="save-row" 
                            style="padding: 6px 10px; background: #4CAF50; color: white; border: none; border-radius: 4px; cursor: pointer;">
                        💾
                    </button>

                    <form class="delete-row" action="{{ route('brand-groups.destroy', $group) }}" method="POST" style="display:inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" 
                                style="padding: 6px 10px; background: #f44336; color: white; border: none; border-radius: 4px; cursor: pointer;">
                            🗑
                        </button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    // ✅ AJAX save row
    const saveButtons = document.querySelectorAll('.save-row');
    saveButtons.forEach(button => {
        button.addEventListener('click', (e) => {
            e.preventDefault();
            const row = e.target.closest('tr');
            const id = row.dataset.id;
            const display_name = row.querySelector('.display-name').value.trim();
            const grouped_names = row.querySelector('.grouped-names').value
                                    .split(',')
                                    .map(n => n.trim()) // trim spaces
                                    .filter(n => n !== '') // remove empty
                                    .join(',');

            fetch('{{ route("brand-groups.update-ajax") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    id: id,
                    display_name: display_name,
                    grouped_names: grouped_names
                })
            })
            .then(res => res.json())
            .then(data => {
                if(data.success) {
                    row.style.backgroundColor = '#d4edda';
                    setTimeout(() => row.style.backgroundColor = '', 800);
                } else {
                    alert('Ошибка при сохранении');
                }
            })
            .catch(() => alert('Ошибка при запросе к серверу'));
        });
    });

    // ✅ Delete confirmation
    const deleteForms = document.querySelectorAll('.delete-row');
    deleteForms.forEach(form => {
        form.addEventListener('submit', (e) => {
            if(!confirm('Вы уверены, что хотите удалить эту группу брендов?')) {
                e.preventDefault();
            }
        });
    });
});
</script>
@endsection

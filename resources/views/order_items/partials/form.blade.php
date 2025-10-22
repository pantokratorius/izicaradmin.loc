<form id="orderForm" action="{{ route('orders.store') }}" method="POST">
    @csrf


<input type="hidden" name="order_id" id="order_id">
<input type="hidden" name="item_id" id="item_id">


<br><br>

    <!-- № заказа -->
    <div class="form-group">
        <label for="order_number">Бренд</label>
        <input type="text" id="part_make" name="part_make" value="{{ old('part_make') }}">
    </div>

    <!-- Дата -->
    <div class="form-group">
        <label for="created_at">Название</label>
        <input type="text" id="part_name" name="part_name" value="{{ old('part_name') }}">
    </div>

    <!-- Пробег -->
    <div class="form-group">
        <label for="mileage">Закупка</label>
        <input type="text" id="purchase_price" name="purchase_price" value="{{ old('purchase_price') }}">
    </div>

    <!-- Предоплата -->
    <div class="form-group">
        <label for="prepayment">Продажа</label>
        <input type="text" id="sell_price" name="sell_price" value="{{ old('sell_price') }}">
    </div>


    <div class="form-group">
        <label for="prepayment">Поставщик</label>
        <input type="text" id="supplier" name="supplier" value="{{ old('supplier') }}">
    </div>


    <div class="form-group">
        <label for="prepayment">Количество</label>
        <input type="text" id="quantity" name="quantity" value="{{ old('quantity') }}">
    </div>

        <!-- Ответственный менеджер -->
    <div class="form-group">
        <label for="manager_id">Ответственный менеджер</label>
        <select id="manager_id" name="manager_id">
            <option value="">-- Не указан --</option>
             @foreach(\App\Models\User::all() as $user)
                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                    @endforeach
        </select>
    </div>

    <!-- Статус -->
    <div class="form-group">
        <label for="status">Статус</label>
        <select id="status" name="status">
            <option value="1" selected>Новый</option>
            <option value="2">В работе</option>
            <option value="3">Пришел</option>
            <option value="4">Выдан</option>
            <option value="5">Отменен</option>
        </select>
    </div>

    <!-- Наценка -->
    <div class="form-group">
        <label for="margin">Наценка %</label>
        <input type="text" id="margin" name="margin" value="{{ old('margin') }}">
    </div>

    <!-- Комментарий -->
    <div class="form-group">
        <label for="comment">Комментарий</label>
        <input type="text" id="comment" name="comment" value="{{ old('comment') }}">
    </div>

</form>

<style>
.form-group { margin-bottom: 15px; }
label { display: block; margin-bottom: 5px; font-weight: bold; }
select, input { width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px; }
button { padding: 10px 20px; background: #2d89ef; color: white; border: none; border-radius: 4px; cursor: pointer; }
button:hover { background: #1b5fbd; }
.accordion-toggle { background: #2d89ef; color: white; padding: 8px 12px; border: none; border-radius: 4px; cursor: pointer; margin-bottom: 10px; }
.accordion-toggle:hover { background: #1b5fbd; }
.accordion-content .form-group { margin-bottom: 10px; }
</style>


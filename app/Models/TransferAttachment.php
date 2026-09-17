<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransferAttachment extends Model
{
    public $timestamps = false;

    protected $fillable = ['path', 'original_name', 'mime_type', 'size'];

    protected $casts = ['size' => 'integer'];

    public function entry(): BelongsTo
    {
        return $this->belongsTo(TransferEntry::class, 'transfer_entry_id');
    }
}

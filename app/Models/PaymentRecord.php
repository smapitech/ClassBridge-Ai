<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentRecord extends Model
{
    protected $fillable = ['school_id','invoice_id','amount','currency','payment_method','transaction_reference','status','paid_at','metadata'];
    protected function casts(): array { return ['amount'=>'decimal:2','paid_at'=>'datetime','metadata'=>'array']; }
    public function school(): BelongsTo { return $this->belongsTo(School::class); }
    public function invoice(): BelongsTo { return $this->belongsTo(Invoice::class); }
}
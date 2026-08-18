<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Invoice extends Model
{
    protected $fillable = ['school_id','subscription_plan_id','invoice_number','amount','currency','status','due_at','paid_at','billing_period_start','billing_period_end','metadata'];
    protected function casts(): array { return ['amount'=>'decimal:2','due_at'=>'datetime','paid_at'=>'datetime','billing_period_start'=>'datetime','billing_period_end'=>'datetime','metadata'=>'array']; }
    public function school(): BelongsTo { return $this->belongsTo(School::class); }
    public function plan(): BelongsTo { return $this->belongsTo(SubscriptionPlan::class,'subscription_plan_id'); }
}
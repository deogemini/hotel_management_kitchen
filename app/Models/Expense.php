<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Expense extends Model {
    protected $fillable = ['lodge_id','category','description','amount','payment_method','spent_at','created_by'];
    protected function casts(): array { return ['amount' => 'decimal:2', 'spent_at' => 'date']; }
}

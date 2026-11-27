<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Task extends Model {
 protected $fillable=['user_id','title','description','status','due_date','is_archived'];
 protected function casts(): array { return ['due_date'=>'date','is_archived'=>'boolean']; }
 public function user() { return $this->belongsTo(User::class); }
}

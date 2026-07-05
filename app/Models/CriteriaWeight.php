<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CriteriaWeight extends Model
{
    use HasFactory;
    protected $fillable = ['criteria_id', 'weight'];

    public function criteria(){
        return $this->belongsTo(Criteria::class);
    }
   
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AhpComparison extends Model
{
    use HasFactory, SoftDeletes;
   
    protected $fillable = ['criteria_id_1' , 'criteria_id_2' , 'value']; 

    public function criteriaOne(){
        return $this->belongsTo(Criteria::class, 'criteria_id_1');
    }
    public function criteriaTwo(){
        return $this->belongsTo(Criteria::class, 'criteria_id_2');  
    }
}

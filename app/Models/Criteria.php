<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Criteria extends Model
{
use HasFactory,SoftDeletes;
    protected $table = 'criteria';

    protected $fillable = ['name','type'];
    public function firstComprison(){
        return $this->hasMany(AhpComparison::class, 'criteria_id_1');
    }

    public function secondComprison(){
        return $this->hasMany(AhpComparison::class, 'second_criteria_id_2');
    }

    public function weight(){
        return $this->hasOne(CriteriaWeight::class);
    }
}

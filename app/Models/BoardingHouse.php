<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BoardingHouse extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'thumbnail',
        'city_id',
        'category_id',
        'description',
        'rules',
        'price',
        'address',
        'gender_type',
        'owner_id',
        'distance',
        'latitude',
        'longitude',
    ];
    
    public function owner(){
        return $this->belongsTo(User::class, 'owner_id');

    }

    public function facilities(){
        return $this->belongsToMany(facility::class, 'boarding_house_facilities');
    }
    
    public function city(){
        return $this->belongsTo(city::class);
    }

      public function category(){
        return $this->belongsTo(category::class);
    }
    
      public function rooms(){
        return $this->hasMany(Room::class);
    }
    
     public function bonuses(){
        return $this->hasMany(Bonus::class);
    }
    
     public function testimonials(){
        return $this->hasMany(Testimonial::class);
    }
     public function transactions(){
        return $this->hasMany(Transaction::class);
    }

    // Ranking attributes
    public function getRankingDataAttribute(): array
    {
        static $rankings = null;

        if ($rankings === null) {
            $service = new \App\Services\KosRankingService;
            $rankings = $service->calculateRankings()->keyBy('boarding_house_id');
        }

        return $rankings->get($this->id, [
            'rank' => 0,
            'distance' => 0,
            'rating' => 0,
            'facility_scores' => [],
            'total_facility_score' => 0,
            'norm_harga' => 0,
            'norm_jarak' => 0,
            'norm_fasilitas' => 0,
            'norm_rating' => 0,
            'final_score' => 0,
        ]);
    }

    public function getRankAttribute(): int
    {
        return (int) $this->ranking_data['rank'];
    }

    public function getDistanceAttribute(): float
    {
        return (float) $this->ranking_data['distance'];
    }

    public function getRatingAttribute(): float
    {
        return (float) $this->ranking_data['rating'];
    }

    public function getFacilityScoresAttribute(): array
    {
        return $this->ranking_data['facility_scores'];
    }

    public function getTotalFacilityScoreAttribute(): int
    {
        return (int) $this->ranking_data['total_facility_score'];
    }

    public function getNormHargaAttribute(): float
    {
        return (float) $this->ranking_data['norm_harga'];
    }

    public function getNormJarakAttribute(): float
    {
        return (float) $this->ranking_data['norm_jarak'];
    }

    public function getNormFasilitasAttribute(): float
    {
        return (float) $this->ranking_data['norm_fasilitas'];
    }

    public function getNormRatingAttribute(): float
    {
        return (float) $this->ranking_data['norm_rating'];
    }

    public function getFinalScoreAttribute(): float
    {
        return (float) $this->ranking_data['final_score'];
    }
}

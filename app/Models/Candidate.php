<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Candidate extends Model
{
    // Menambahkan 'is_active' ke dalam properti $fillable
    protected $fillable = ['name', 'photo', 'description', 'is_active'];

    // Relasi: 1 Kandidat punya banyak vote
    public function votes(): HasMany
    {
        // Menggunakan 'Vote' dengan huruf kapital sesuai konvensi
        return $this->hasMany(Vote::class);
    }
}

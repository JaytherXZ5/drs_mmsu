<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Institution extends Model
{
    use HasFactory;
    protected $fillable = ['name', 'institution_id'];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function folders()
    {
        return $this->hasMany(Folder::class);
    }
    public function archives()
    {
        return $this->hasMany(Archive::class);
    }

}

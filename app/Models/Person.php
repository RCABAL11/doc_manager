<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Person extends Model
{
    protected $fillable = ['name', 'company', 'folder_path'];
    
    protected static function booted()
    {
        static::creating(function ($person) {
            $folderName = preg_replace('/[^a-zA-Z0-9_-]/', '_', 
                "{$person->company}_{$person->name}");
            $person->folder_path = "documents/{$folderName}";
            
            $fullPath = storage_path('app/' . $person->folder_path);
            if (!file_exists($fullPath)) {
                mkdir($fullPath, 0755, true);
            }
        });
    }

    public function documents()
    {
        return $this->hasMany(Document::class);
    }

}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InCategory extends Model
{
    use HasFactory;
    public function menus(){
        return $this->hasmany(InMenu::class);
}
}

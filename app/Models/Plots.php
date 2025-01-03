<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Plots extends Model
{
    // Define fillable fields to allow mass assignment
    protected $fillable = [
        'plot_number', 
        'block', 
        'plot_size', 
        'price', 
        'status'
    ];
}

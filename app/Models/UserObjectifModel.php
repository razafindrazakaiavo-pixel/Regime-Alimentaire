<?php

namespace App\Models;

use CodeIgniter\Model;

class UserObjectifModel extends Model
{
    protected $table = 'user_objectif';
    protected $primaryKey = 'id';

    protected $allowedFields = [
        'user_id',
        'objectif_id',
        'date_choix',
    ];

    protected $useTimestamps = false;
}

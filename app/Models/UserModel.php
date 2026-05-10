<?php
namespace App\Models;
use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table = 'users';
    protected $primaryKey = 'id';

    protected $allowedFields = [
        'nom',
        'email',
        'mot_de_passe',
        'genre',
        'date_de_naissance',
        'role',
        'porte_monnaie',
        'is_gold',
    ];

    protected $useTimestamps = false;
}


?>
<?php 

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model 
{
    protected $primaryKey = 'id';
    protected $table = 'users';
    protected $returnType = 'object';
    protected $allowedFields = ['username', 'password', 'mail', 'account_created'];
    
    protected $beforeInsert = ['hashPassword'];
    protected $beforeUpdate = ['hashPassword'];

    protected function hashPassword(array $data)
    {
        if (isset($data['data']['password'])) {
            $data['data']['password'] = password_hash($data['data']['password'], PASSWORD_BCRYPT);
        } else {
            throw new \RuntimeException('No password was submitted, unable to process the data.');
        }

        return $data;
    }

    public function getUserDataById(int $userId) 
    {
        return $this->select('id, username')->where('id', $userId)->first();
    }
}
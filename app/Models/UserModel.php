<?php

namespace App\Models;

use CodeIgniter\Shield\Models\UserModel as ShieldUserModel;

class UserModel extends ShieldUserModel
{
    protected function initialize(): void
    {
        parent::initialize();

        $this->allowedFields[] = 'avatar';
        $this->allowedFields[] = 'empresa_id';
    }
}

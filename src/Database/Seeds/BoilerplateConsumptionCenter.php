<?php

namespace julio101290\boilerplatelocations\Database\Seeds;

use CodeIgniter\Config\Services;
use CodeIgniter\Database\Seeder;
use Myth\Auth\Entities\User;
use Myth\Auth\Models\UserModel;

/**
 * Class BoilerplateSeeder.
 */
class BoilerplateConsumptionCenter extends Seeder {

    /**
     * @var Authorize
     */
    protected $authorize;

    /**
     * @var Db
     */
    protected $db;

    /**
     * @var Users
     */
    protected $users;

    public function __construct() {
        $this->authorize = Services::authorization();
        $this->db = \Config\Database::connect();
        $this->users = new UserModel();
    }

    public function run() {


        // Permission
        $this->authorize->createPermission('consumptioncenter-permission', 'Permiso para la lista de consumptioncenter');

        // Assign Permission to user
        $this->authorize->addPermissionToUser('consumptioncenter-permission', 1);

    }

    public function down() {
        //
    }
}

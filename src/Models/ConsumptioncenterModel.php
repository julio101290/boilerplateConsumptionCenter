<?php
namespace julio101290\boilerplateconsumptioncenter\Models;


use CodeIgniter\Model;

class ConsumptioncenterModel extends Model
{
    protected $table            = 'consumptioncenter';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;
    protected $allowedFields    = ['id', 'idEmpresa', 'descripcion', 'created_at', 'updated_at', 'deleted_at'];
    protected $useTimestamps    = true;
    protected $createdField     = 'created_at';
    protected $updatedField     = 'updated_at';
    protected $deletedField     = 'deleted_at';

    protected $validationRules    = [];
    protected $validationMessages = [];
    protected $skipValidation     = false;

    public function mdlGetConsumptioncenter(array $idEmpresas)
    {
        return $this->db->table('consumptioncenter a')
            ->join('empresas b', 'a.idEmpresa = b.id')
            ->select("a.id, a.idEmpresa, a.descripcion, a.created_at, a.updated_at, a.deleted_at, b.nombre AS nombreEmpresa")
            ->whereIn('a.idEmpresa', $idEmpresas);
    }
}
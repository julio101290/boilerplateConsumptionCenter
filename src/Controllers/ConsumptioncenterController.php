<?php

namespace julio101290\boilerplateconsumptioncenter\Controllers;

use App\Controllers\BaseController;
use julio101290\boilerplateconsumptioncenter\Models\{ConsumptioncenterModel};
use CodeIgniter\API\ResponseTrait;
use julio101290\boilerplatelog\Models\LogModel;
use julio101290\boilerplatecompanies\Models\EmpresasModel;

class ConsumptioncenterController extends BaseController
{
    use ResponseTrait;

    protected $log;
    protected $consumptioncenter;
    protected $empresa;

    public function __construct()
    {
        $this->consumptioncenter = new ConsumptioncenterModel();
        $this->log = new LogModel();
        $this->empresa = new EmpresasModel();
        helper(['menu', 'utilerias']);
    }

    public function index()
    {
        helper('auth');

        $idUser = user()->id;
        $titulos["empresas"] = $this->empresa->mdlEmpresasPorUsuario($idUser);
        $empresasID = count($titulos["empresas"]) === 0 ? [0] : array_column($titulos["empresas"], "id");

        if ($this->request->isAJAX()) {
            $request = service('request');

            $draw = (int) $request->getGet('draw');
            $start = (int) $request->getGet('start');
            $length = (int) $request->getGet('length');
            $searchValue = $request->getGet('search')['value'] ?? '';
            $orderColumnIndex = (int) $request->getGet('order')[0]['column'] ?? 0;
            $orderDir = $request->getGet('order')[0]['dir'] ?? 'asc';

            $fields = $this->consumptioncenter->allowedFields;
            $orderField = $fields[$orderColumnIndex] ?? 'id';

            $builder = $this->consumptioncenter->mdlGetConsumptioncenter($empresasID);

            $total = clone $builder;
            $recordsTotal = $total->countAllResults(false);

            if (!empty($searchValue)) {
                $builder->groupStart();
                foreach ($fields as $field) {
                    $builder->orLike("a." . $field, $searchValue);
                }
                $builder->groupEnd();
            }

            $filteredBuilder = clone $builder;
            $recordsFiltered = $filteredBuilder->countAllResults(false);

            $data = $builder->orderBy("a." . $orderField, $orderDir)
                             ->get($length, $start)
                             ->getResultArray();

            return $this->response->setJSON([
                'draw' => $draw,
                'recordsTotal' => $recordsTotal,
                'recordsFiltered' => $recordsFiltered,
                'data' => $data,
            ]);
        }

        $titulos["title"] = lang('consumptioncenter.title');
        $titulos["subtitle"] = lang('consumptioncenter.subtitle');
        return view('julio101290\boilerplateconsumptioncenter\Views\consumptioncenter', $titulos);
    }

    public function getConsumptioncenter()
    {
        helper('auth');

        $idUser = user()->id;
        $titulos["empresas"] = $this->empresa->mdlEmpresasPorUsuario($idUser);
        $empresasID = count($titulos["empresas"]) === 0 ? [0] : array_column($titulos["empresas"], "id");

        $idConsumptioncenter = $this->request->getPost("idConsumptioncenter");
        $dato = $this->consumptioncenter->whereIn('idEmpresa', $empresasID)
                                   ->where('id', $idConsumptioncenter)
                                   ->first();

        return $this->response->setJSON($dato);
    }

    public function save()
    {
        helper('auth');

        $userName = user()->username;
        $datos = $this->request->getPost();
        $idKey = $datos["idConsumptioncenter"] ?? 0;

        if ($idKey == 0) {
            try {
                if (!$this->consumptioncenter->save($datos)) {
                    $errores = implode(" ", $this->consumptioncenter->errors());
                    return $this->respond(['status' => 400, 'message' => $errores], 400);
                }
                $this->log->save([
                    "description" => lang("consumptioncenter.logDescription") . json_encode($datos),
                    "user" => $userName
                ]);
                return $this->respond(['status' => 201, 'message' => 'Guardado correctamente'], 201);
            } catch (\Throwable $ex) {
                return $this->respond(['status' => 500, 'message' => 'Error al guardar: ' . $ex->getMessage()], 500);
            }
        } else {
            if (!$this->consumptioncenter->update($idKey, $datos)) {
                $errores = implode(" ", $this->consumptioncenter->errors());
                return $this->respond(['status' => 400, 'message' => $errores], 400);
            }
            $this->log->save([
                "description" => lang("consumptioncenter.logUpdated") . json_encode($datos),
                "user" => $userName
            ]);
            return $this->respond(['status' => 200, 'message' => 'Actualizado correctamente'], 200);
        }
    }

    public function delete($id)
    {
        helper('auth');

        $userName = user()->username;
        $registro = $this->consumptioncenter->find($id);

        if (!$this->consumptioncenter->delete($id)) {
            return $this->respond(['status' => 404, 'message' => lang("consumptioncenter.msg.msg_get_fail")], 404);
        }

        $this->consumptioncenter->purgeDeleted();
        $this->log->save([
            "description" => lang("consumptioncenter.logDeleted") . json_encode($registro),
            "user" => $userName
        ]);

        return $this->respondDeleted($registro, lang("consumptioncenter.msg_delete"));
    }
}
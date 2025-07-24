<?php

namespace julio101290\boilerplatelocations\Controllers;

use App\Controllers\BaseController;
use julio101290\boilerplatelocations\Models\{
    UbicacionesModel
};
use julio101290\boilerplatelog\Models\LogModel;
use CodeIgniter\API\ResponseTrait;
use julio101290\boilerplatecompanies\Models\EmpresasModel;

class UbicacionesController extends BaseController {

    use ResponseTrait;

    protected $log;
    protected $ubicaciones;

    public function __construct() {
        $this->ubicaciones = new UbicacionesModel();
        $this->log = new LogModel();
        $this->empresa = new EmpresasModel();
        helper('menu');
        helper('utilerias');
    }

    public function index() {



        helper('auth');

        $idUser = user()->id;
        $titulos["empresas"] = $this->empresa->mdlEmpresasPorUsuario($idUser);

        if (count($titulos["empresas"]) == "0") {

            $empresasID[0] = "0";
        } else {

            $empresasID = array_column($titulos["empresas"], "id");
        }




        if ($this->request->isAJAX()) {

            $request = service('request');
            $draw = $request->getGet('draw');
            $start = $request->getGet('start');
            $length = $request->getGet('length');
            $search = $request->getGet('search')['value'] ?? '';
            $order = $request->getGet('order');
            $columns = $request->getGet('columns');

            // Orden dinámico
            $orderColumnIndex = $order[0]['column'] ?? 0;
            $orderColumn = $columns[$orderColumnIndex]['data'] ?? 'a.id';
            $orderDir = $order[0]['dir'] ?? 'asc';

            $resultado = $this->ubicaciones->mdlGetUbicacionesServerSide(
                    $empresasID,
                    $search,
                    $orderColumn,
                    $orderDir,
                    $start,
                    $length
            );

            return $this->response->setJSON([
                        'draw' => intval($draw),
                        'recordsTotal' => $resultado['total'],
                        'recordsFiltered' => $resultado['filtered'],
                        'data' => $resultado['data']
            ]);
        }

        $titulos["title"] = lang('ubicaciones.title');
        $titulos["subtitle"] = lang('ubicaciones.subtitle');
        return view('julio101290\boilerplatelocations\Views\ubicaciones', $titulos);
    }

    /**
     * Read Ubicaciones
     */
    public function getUbicaciones() {

        helper('auth');

        $idUser = user()->id;
        $titulos["empresas"] = $this->empresa->mdlEmpresasPorUsuario($idUser);

        if (count($titulos["empresas"]) == "0") {

            $empresasID[0] = "0";
        } else {

            $empresasID = array_column($titulos["empresas"], "id");
        }


        $idUbicaciones = $this->request->getPost("idUbicaciones");
        $datosUbicaciones = $this->ubicaciones->whereIn('idEmpresa', $empresasID)
                        ->where("id", $idUbicaciones)->first();

        //$datosPaises = $this->catalogosSAT->paises40()->obtain($datosUbicaciones["pais"]);

        $datosPaises = $this->catalogosSAT->paises40()->searchByField("id", $datosUbicaciones["pais"]);

        // $datosEstado = $this->catalogosSAT->estados40()->obtain($datosUbicaciones["estado"], $datosUbicaciones["pais"]);

        $datosEstado = $this->catalogosSAT->estados40()->searchEstados("%", $datosUbicaciones["pais"], $datosUbicaciones["estado"]);

        //$datosMunicipio = $this->catalogosSAT->municipios40()->obtain($datosUbicaciones["municipio"], $datosUbicaciones["estado"]);
        $datosMunicipio = $this->catalogosSAT->municipios40()->search($datosUbicaciones["municipio"], $datosUbicaciones["estado"]);

        $datosLocalidad = $this->catalogosSAT->localidades40()->search($datosUbicaciones["localidad"], $datosUbicaciones["estado"]);

        //$datosColonia = $this->catalogosSAT->colonias40()->obtain($datosUbicaciones["colonia"], $datosUbicaciones["codigoPostal"]);

        $datosColonia = $this->catalogosSAT->colonias40()->searchColonias($datosUbicaciones["colonia"], $datosUbicaciones["codigoPostal"]);

        if (count($datosPaises) > 0) {

            $datosUbicaciones["nombrePais"] = $datosPaises[0]->texto();
        } else {

            $datosUbicaciones["nombrePais"] = "Sin Pais";
        }

        if (count($datosEstado) > 0) {

            $datosUbicaciones["nombreEstado"] = $datosEstado[0]->texto();
        } else {

            $datosUbicaciones["nombreEstado"] = "Sin Estado";
        }



        if (count($datosMunicipio) > 0) {

            $datosUbicaciones["nombreMunicipio"] = $datosMunicipio[0]->texto();
        } else {

            $datosUbicaciones["nombreMunicipio"] = "Sin Municipio";
        }


        if (count($datosLocalidad) > 0) {

            $datosUbicaciones["nombreLocalidad"] = $datosLocalidad[0]->texto();
        } else {

            $datosUbicaciones["nombreLocalidad"] = "";
        }

        if (count($datosColonia) > 0) {

            $datosUbicaciones["nombreColonia"] = $datosColonia[0]->asentamiento();
        } else {

            $datosUbicaciones["nombreColonia"] = "Sin colonia";
        }

        echo json_encode($datosUbicaciones);
    }

    /**
     * Save or update Ubicaciones
     */
    public function save() {
        helper('auth');
        $userName = user()->username;
        $idUser = user()->id;
        $datos = $this->request->getPost();
        if ($datos["idUbicaciones"] == 0) {
            try {
                if ($this->ubicaciones->save($datos) === false) {
                    $errores = $this->ubicaciones->errors();
                    foreach ($errores as $field => $error) {
                        echo $error . " ";
                    }
                    return;
                }
                $dateLog["description"] = lang("vehicles.logDescription") . json_encode($datos);
                $dateLog["user"] = $userName;
                $this->log->save($dateLog);
                echo "Guardado Correctamente";
            } catch (\PHPUnit\Framework\Exception $ex) {
                echo "Error al guardar " . $ex->getMessage();
            }
        } else {
            if ($this->ubicaciones->update($datos["idUbicaciones"], $datos) == false) {
                $errores = $this->ubicaciones->errors();
                foreach ($errores as $field => $error) {
                    echo $error . " ";
                }
                return;
            } else {
                $dateLog["description"] = lang("ubicaciones.logUpdated") . json_encode($datos);
                $dateLog["user"] = $userName;
                $this->log->save($dateLog);
                echo "Actualizado Correctamente";
                return;
            }
        }
        return;
    }

    /**
     * Delete Ubicaciones
     * @param type $id
     * @return type
     */
    public function delete($id) {
        $infoUbicaciones = $this->ubicaciones->find($id);
        helper('auth');
        $userName = user()->username;
        if (!$found = $this->ubicaciones->delete($id)) {
            return $this->failNotFound(lang('ubicaciones.msg.msg_get_fail'));
        }
        $this->ubicaciones->purgeDeleted();
        $logData["description"] = lang("ubicaciones.logDeleted") . json_encode($infoUbicaciones);
        $logData["user"] = $userName;
        $this->log->save($logData);
        return $this->respondDeleted($found, lang('ubicaciones.msg_delete'));
    }

    /**
     * Para obtener las colonias
     * @return type
     */
    public function getColoniasSAT() {

        $request = service('request');
        $postData = $request->getPost();

        $response = array();

        // Read new token and assign in $response['token']
        $response['token'] = csrf_hash();

        if ($postData["codigoPostal"] == "") {

            $codigoPostal = "%";
        } else {

            $codigoPostal = $postData["codigoPostal"];
        }

        if (!isset($postData['searchTerm'])) {
            // Fetch record

            if ($codigoPostal == "%") {

                $searchTerm = "";
            } else {

                $searchTerm = "%%";
            }

            $listColoniasSAT = $this->catalogosSAT->colonias40()->searchColonias("%", $codigoPostal, $searchTerm);
        } else {
            $searchTerm = $postData['searchTerm'];

            // Fetch record

            $listColoniasSAT = $this->catalogosSAT->colonias40()->searchColonias("%", $codigoPostal, "%$searchTerm%");
        }

        $data = array();
        foreach ($listColoniasSAT as $coloniaSAT => $value) {

            $data[] = array(
                "id" => $value->colonia(),
                "text" => $value->colonia() . ' ' . $value->asentamiento(),
            );
        }

        $response['data'] = $data;

        return $this->response->setJSON($response);
    }

    /**
     * Para obtener las localidades
     * @return type
     */
    public function getLocalidadSAT() {

        $request = service('request');
        $postData = $request->getPost();

        $response = array();

        // Read new token and assign in $response['token']
        $response['token'] = csrf_hash();

        if ($postData["estado"] == "") {

            $estado = "%";
        } else {

            $estado = $postData["estado"];
        }

        if (!isset($postData['searchTerm'])) {



            if ($estado == "%") {

                $searchTerm = "";
            } else {

                $searchTerm = "%%";
            }
            // Fetch record



            $listLocalidadesSAT = $this->catalogosSAT->localidades40()->search("%", $estado, $searchTerm);
        } else {
            $searchTerm = $postData['searchTerm'];

            // Fetch record

            $listLocalidadesSAT = $this->catalogosSAT->localidades40()->search("%", $estado, "%$searchTerm%");
        }

        $data = array();
        foreach ($listLocalidadesSAT as $localidadSAT => $value) {

            $data[] = array(
                "id" => $value->codigo(),
                "text" => $value->codigo() . ' ' . $value->estado() . ' ' . $value->texto(),
            );
        }

        $response['data'] = $data;

        return $this->response->setJSON($response);
    }

    /**
     * Para obtener las municipios
     * @return type
     */
    public function getMunicipiosSAT() {

        $request = service('request');
        $postData = $request->getPost();

        if ($postData["estado"] == "") {

            $estado = "%";
        } else {

            $estado = $postData["estado"];
        }

        $response = array();

        // Read new token and assign in $response['token']
        $response['token'] = csrf_hash();

        if (!isset($postData['searchTerm'])) {
            // Fetch record


            if ($estado == "%") {

                $searchTerm = "";
            } else {

                $searchTerm = "%%";
            }

            $listMunicipiosSAT = $this->catalogosSAT->municipios40()->search("%", $estado, "%$searchTerm%");
        } else {
            $searchTerm = $postData['searchTerm'];

            // Fetch record

            $listMunicipiosSAT = $this->catalogosSAT->municipios40()->search("%", $estado, "%$searchTerm%");
        }

        $data = array();
        foreach ($listMunicipiosSAT as $municipioSAT => $value) {

            $data[] = array(
                "id" => $value->codigo(),
                "text" => $value->codigo() . ' ' . $value->texto(),
            );
        }

        $response['data'] = $data;

        return $this->response->setJSON($response);
    }

    /**
     * Para obtener las estados
     * @return type
     */
    public function getEstadosSAT() {

        $request = service('request');
        $postData = $request->getPost();

        $response = array();

        // Read new token and assign in $response['token']
        $response['token'] = csrf_hash();

        if ($postData["pais"] == "") {

            $pais = "%";
        } else {

            $pais = $postData["pais"];
        }

        if (!isset($postData['searchTerm'])) {
            // Fetch record

            $listEstadosSAT = $this->catalogosSAT->estados40()->searchEstados("%%", $pais);
        } else {
            $searchTerm = $postData['searchTerm'];

            // Fetch record

            $listEstadosSAT = $this->catalogosSAT->estados40()->searchEstados("%$searchTerm%", $pais);
        }

        $data = array();
        foreach ($listEstadosSAT as $estadosSAT => $value) {

            $data[] = array(
                "id" => $value->codigo(),
                "text" => $value->codigo() . ' ' . $value->texto(),
            );
        }

        $response['data'] = $data;

        return $this->response->setJSON($response);
    }

    /**
     * Para obtener las estados
     * @return type
     */
    public function getPaisesSAT() {

        $request = service('request');
        $postData = $request->getPost();

        $response = array();

        // Read new token and assign in $response['token']
        $response['token'] = csrf_hash();

        if (!isset($postData['searchTerm'])) {
            // Fetch record

            $listPaisesSAT = $this->catalogosSAT->paises40()->searchByField("texto", "%%", 1000);
        } else {
            $searchTerm = $postData['searchTerm'];

            // Fetch record

            $listPaisesSAT = $this->catalogosSAT->paises40()->searchByField("texto", "%$searchTerm%", 1000);
            $listPaisesSAT2 = $this->catalogosSAT->paises40()->searchByField("id", "%$searchTerm%", 1000);
        }

        $data = array();
        foreach ($listPaisesSAT as $paisSAT => $value) {

            $data[] = array(
                "id" => $value->id(),
                "text" => $value->id() . ' ' . $value->texto(),
            );
        }

        foreach ($listPaisesSAT2 as $paisSAT => $value) {

            $data[] = array(
                "id" => $value->id(),
                "text" => $value->id() . ' ' . $value->texto(),
            );
        }

        $response['data'] = $data;

        return $this->response->setJSON($response);
    }

    /**
     * Get Storages via AJax
     */
    public function getUbicacionesAjax() {

        $request = service('request');
        $postData = $request->getPost();

        $response = array();

        // Read new token and assign in $response['token']
        $response['token'] = csrf_hash();

        helper('auth');
        $userName = user()->username;
        $idUser = user()->id;

        $idEmpresa = $postData['idEmpresa'];

        if (!isset($postData['searchTerm'])) {

            $searchTerm = "";
        } else {

            $searchTerm = $postData['searchTerm'];
        }

        $searchTerm = strtolower(
                $this->ubicaciones->db->escapeLikeString($searchTerm)
        );

        $listUbicaciones = $this->ubicaciones
                ->select("id, descripcion")
                ->where("idEmpresa", $idEmpresa)
                ->where("LOWER(descripcion) LIKE", "%{$searchTerm}%")
                ->findAll();

        $data = array();

        foreach ($listUbicaciones as $ubicaciones) {
            $data[] = array(
                "id" => $ubicaciones['id'],
                "text" => $ubicaciones['id'] . ' ' . $ubicaciones['descripcion'],
            );
        }

        $response['data'] = $data;

        return $this->response->setJSON($response);
    }
}

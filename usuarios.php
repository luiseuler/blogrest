<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Authorization, Access-Control-Allow-Methods, Access-Control-Allow-Headers, Allow, Access-Control-Allow-Origin");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS, HEAD");
header("Allow: GET, POST, PUT, DELETE, OPTIONS, HEAD");
require_once 'database.php';
require_once 'jwt.php';
if($_SERVER['REQUEST_METHOD']=="OPTIONS"){
    exit();
}

$header = apache_request_headers();
$jwt = trim($header['Authorization']);
switch (JWT::verify($jwt, CONFIG::SECRET_JWT)) {
    case 1:
        header("HTTP/1.1 401 Unauthorized");
        echo "El token no es válido";
        exit();
        break;
    case 2:
        header("HTTP/1.1 408 Request Timeout");
        echo "La sesión caduco";
        exit();
        break;
}

switch($_SERVER['REQUEST_METHOD']){
    case "GET":
        if(isset($_GET['user'])){
            $usuarios = new DataBase('usuarios');
            $where = array('user'=>$_GET['user']);
            $res = $usuarios->Read($where);
        }else{
            $usuarios = new DataBase('usuarios');
            $res = $usuarios->ReadAll();
        }
        header("HTTP/1.1 200 OK");
        echo json_encode($res);
    break;
    case "POST":
        if(isset($_POST['user']) && isset($_POST['pass']) && isset($_POST['tipo']) && isset($_POST['nombre'])){
            
            $usuarios = new DataBase('usuarios');
            $datos = array(
                'user'=>$_POST['user'],
                'pass'=>$_POST['pass'],
                'tipo'=>$_POST['tipo'],
                'nombre'=>$_POST['nombre']
            );
            try{
                $reg = $usuarios->create($datos);
                $res = array("result"=>"ok","msg"=>"Se guardo el tema", "id"=>$reg);
            }catch(PDOException $e){
                $res = array("result"=>"no","msg"=>$e->getMessage());
            }
        }else{
            $res = array("result"=>"no","msg"=>"Faltan datos");
        }
        header("HTTP/1.1 200 OK");
        echo json_encode($res);
    break;
    case "PUT":
        $data = JWT::get_data($jwt, CONFIG::SECRET_JWT);
        $usuarios = new DataBase('usuarios');
        if(isset($_GET['user']) && isset($_GET['pass']) && isset($_GET['tipo']) && isset($_GET['nombre'])){
            if($data['level']=='A'){
                $where = array('user'=>$_GET['user']);
            }else{
                $where = array('user'=>$_GET['user'], 'user'=>$data['user']);
            }
            $datos = array(
                'pass'=>$_GET['pass'],
                'tipo'=>$_GET['tipo'],
                'nombre'=>$_GET['nombre']
            );
            $reg = $usuarios->update($datos,$where);
            $res = array("result"=>"ok","msg"=>"Se guardo el mensaje", "num"=>$reg);
        }else{
            $res = array("result"=>"no","msg"=>"Faltan datos");
        }
        echo json_encode($res);
    break;
    case "DELETE":
        $data = JWT::get_data($jwt, CONFIG::SECRET_JWT);
        $usuarios = new DataBase('usuarios');
        if(isset($_GET['user'])){
            if($data['level']=='A'){
                $where = array('user'=>$_GET['user']);
            }else{
                $where = array('user'=>$_GET['user'], 'user'=>$data['user']);
            }
            $where = array('user'=>$_GET['user']);
            $reg = $usuarios->delete($where);  
            $res = array("result"=>"ok","msg"=>"Se elimino el mensaje", "num"=>$reg);
        }else{
            $res = array("result"=>"no","msg"=>"Faltan datos");
        }
        echo json_encode($res);
    break;
    default:
        header("HTTP/1.1 401 Bad Request");
}
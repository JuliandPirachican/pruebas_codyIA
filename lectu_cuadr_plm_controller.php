<?php 

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once("lectu_cuadr_plm_model.php");
require_once($_SERVER['DOCUMENT_ROOT']."/CI/system/libraries/curl/autoload.php");
use \Curl\Curl;
use \Curl\MultiCurl;

Class Lectu_Cuadr_PLM_controller{
    
    public $response=array();
    private $model=null;
    private $session_data=null;

    public function __construct(){
        self::valid_session();
        $log=self::createLog();
        $this->model=new Lectu_Cuadr_PLM_Model(); 
        $this->model->send_file_log($log);
    }

    public function createLog(){
        $date = date('Y-m-d', time());
        $dir_log=RUTA_REGISTROS."debug";
        $ruta_log = RUTA_REGISTROS."debug/cuadr_plm_{$date}_".$this->session_data['codi_usua'].".txt";

        if(!is_dir($dir_log)){
            mkdir($dir_log, 0777, true);
        }

        $stdlog=fopen($ruta_log,'a');
        return $stdlog;
    }

    public function return_page(){
        $return_home=$_SERVER['HTTP_ORIGIN']."/desarrollo/cgis/lectu_cuadr_plm/lectu_cuadr_plm_form.php";
        header("Location: {$return_home}");
    } 

    private function valid_session(){
        $session=$_SESSION;
        if(count($session)==0){
           self::return_page();
        }
        $this->session_data=$session;
    }

    public function msg($response){
       echo json_encode($this->response,JSON_FORCE_OBJECT);
    }

    public function save_arch($temp_route,$server_route,$server_folders){
        try {
            $Date = date('Y-m-d', time());
            $dir=$server_folders.$Date;
            $dir_data_temp=$server_folders.$Date."/data";
    
            if(!is_dir($dir)){
                mkdir($dir, 0777, true);
            }
            
            if(!is_dir($dir_data_temp)){
                mkdir($dir_data_temp, 0777, true);
            }
        
            // if(!file_exists($server_route)){
                $moved = rename($temp_route, $server_route);
            // }

            if(file_exists($server_route)){
                $this->response['status']="Correcto";
                $this->response['msg']="Archivo Guardado Exitosamente";
                $this->response['mode']="success";
                self::msg($this->response);
            }
        } catch (\Throwable $th) {
            $this->response['status']="Error De Sistema";
            $this->response['msg']="Error, ".$th->getMessage()." en la linea {$th->getCode()}. Intente nuevamente el proceso y si el error persiste comuniquese con sistemas";
            $this->response['mode']="error";
            self::msg($this->response);
        }
        
    }

    public function move_file($file){
        try {
            $ext_allow=array('xlsx','xls');
            $file=$_FILES['file'];
            $name_arch=$file['name'];
            $tmp_name=$file['tmp_name'];
            $size = $file["size"];
            $Date = date('Y-m-d', time());
            $tmp_route=RUTA_ARCHIVOS."cuadriculas_plm/";

            $ext=pathinfo($file['name'],PATHINFO_EXTENSION);
            if(!in_array($ext, $ext_allow) ) {
                $msg= 'Error formato no permitido !!';
                $this->response['status']="Error";
                $this->response['msg']=$msg;
                $this->response['mode']="error";
                self::msg($this->response);
            }else{
                $DateAndTime = date('Y-m-d', time());
                $server_route=$tmp_route."{$Date}/cuadricula_plm_{$DateAndTime}.".$ext;
                self::save_arch($tmp_name,$server_route,$tmp_route);
            }
        } catch (\Throwable $th) {
            $this->response['status']="Error De Sistema";
            $this->response['msg']="Error, ".$th->getMessage()." en la linea {$th->getCode()}. Intente nuevamente el proceso y si el error persiste comuniquese con sistemas";
            $this->response['mode']="error";
            self::msg($this->response);
        }
        
    }

    public function camp_valid($data){
        $camp_validate=$data['camp'];
        $result_validate=$this->model->cons_camp($camp_validate);
        if(count($result_validate)==0){
            $msg= 'La campaña digitada no existe o esta inactiva .Por favor verifique e intente nuevamente';
            $this->response['status']="Error";
            $this->response['msg']=$msg;
            $this->response['mode']="error";
            self::msg($this->response);
        }
    }

    public function call_read_py($row,$camp_adv,$camp_nal){
        try {
            $date = date('Y-m-d', time());
            $tmp_route=RUTA_ARCHIVOS."cuadriculas_plm/{$date}/cuadricula_plm_{$date}.xlsx";
            $tmp_routetxt=RUTA_ARCHIVOS;
            $codi_usua=$this->session_data['codi_usua'];
            $datetime = date('Y-m-d H:m:s', time());
       
            //crea la linea con el nombre de documento .py y los parametros que se pasaran
            $ruta_py = $_SERVER['DOCUMENT_ROOT']."/desarrollo/cgis/lectu_cuadr_plm/LeerExcel.py  $tmp_route $tmp_routetxt $row $camp_adv $camp_nal $codi_usua "; 
           
            /* Ejecutar el script de python y guardar el resultado en la variable ``. */
            $sali_proc = shell_exec("python3 $ruta_py 2>&1");
        } catch (\Throwable $th) {
            $this->response['status']="Error De Sistema";
            $this->response['msg']="Error en ejecucion python, ".$th->getMessage()." en la linea {$th->getCode()}. Intente nuevamente el proceso y si el error persiste comuniquese con sistemas";
            $this->response['mode']="error";
            self::msg($this->response);
        }  
    }

    public function load_data_txt(){
        try {
            $date = date('Y-m-d', time());
            $tmp_routetxt=RUTA_ARCHIVOS;
            if(is_dir($tmp_routetxt)){
                $get_all_files_dir=scandir($tmp_routetxt);
                $clean_array=array_search(".",$get_all_files_dir);
                $clean_array2=array_search("..",$get_all_files_dir);
                $delete_array=array($clean_array,$clean_array2);
                foreach ($delete_array as $key => $value) {
                    unset($get_all_files_dir[$value]);
                }

                $file_to_load=array();
                foreach ($get_all_files_dir as $key => $value) {
                    $info_doc=PATHINFO($value);
                    $doc_name=$info_doc['filename'];
                    $ext=(!empty($info_doc['extension']) && isset($info_doc['extension'])) ? $info_doc['extension']:'';

                    if(is_numeric(strpos($doc_name,"data_CUADR")) && $ext=='txt' && is_numeric(strpos($doc_name,$this->session_data['codi_usua']))){
                        $file_to_load[]=$value;
                    }
                }

                // var_dump($file_to_load);
                $htmlresponse="";
                $htmlresponse.="<div class='table-responsive'><table class='table table-striped '>";
                $htmlresponse.="    <thead>";
                $htmlresponse.="        <tr>";
                $htmlresponse.="            <th>Hoja</th>";
                $htmlresponse.="            <th>Datos Registrados en sistema</th>";
                $htmlresponse.="        </tr>";
                $htmlresponse.="    </thead>";
                $htmlresponse.="    <tbody>";
                $htmlresponse.="        <tr>";
                
                $curl = new Curl();
                foreach ($file_to_load as $key => $value) {
                    $info_doc=PATHINFO($value);
                    $table_name=trim($info_doc['filename']);
                    $this->model->inic_tran();
                    $result_delete_temp_tables=$this->model->delete_temp_table($table_name);
                    $result_create_temp_tables=$this->model->create_temp_table($table_name);
                    $this->model->fina_tran();

                    $result_insert_temp_tables=$this->model->insert_data_temp_table($table_name,$curl,$this->session_data['codi_usua']);

                    $this->model->inic_tran();
                    $result_insert_info_table_no_temp=$this->model->insert_info($table_name);
                    $this->model->fina_tran();
                    $htmlresponse.="            <td> ".$result_insert_temp_tables[0]['nomb_cuad']."</td>";
                    $htmlresponse.="            <td>".$result_insert_temp_tables[0]['total_reg']."</td>";
                    $htmlresponse.="        </tr>";
                }
                $htmlresponse.="    </tbody>";
                $htmlresponse.="    <tfoot>";
                $htmlresponse.="        <tr>";
                $htmlresponse.="            <th>Hoja</th>";
                $htmlresponse.="            <th>Datos Registrados en sistema</th>";
                $htmlresponse.="        </tr>";
                $htmlresponse.="    </tfoot>";
                $htmlresponse.="</table></div>";
                // $this->response['status']="Error";
                $this->response['html']=$htmlresponse;
                // $this->response['mode']="error";
                self::msg($this->response);
            }
        } catch (\Throwable $th) {
            $this->response['status']="Error De Sistema";
            $this->response['msg']="Error en lectura de archivo, ".$th->getMessage()." en la linea {$th->getCode()}. Intente nuevamente el proceso y si el error persiste comuniquese con sistemas";
            $this->response['mode']="error";
            self::msg($this->response);
        }  
        
    }

    public function recep_data($data){
       $camp_adv=$data['camp_ad'];
       $camp_nal=$data['camp_nal'];
       $col_ini=$data['col_ini'];

       if(!is_numeric($col_ini)){
           $column=substr($col_ini,0,1);
           $nume_row = (int) filter_var($col_ini, FILTER_SANITIZE_NUMBER_INT);  
           
           if($column!='A'){
            $msg= 'Recuerde que el documento debe iniciar en la columna A. Verifique e intente nuevamente';
            $this->response['status']="Error";
            $this->response['msg']=$msg;
            $this->response['mode']="error";
            self::msg($this->response);
           }else{
            self::call_read_py($nume_row,$camp_adv,$camp_nal);
            self::load_data_txt();
           }   
       }else{
        $msg= 'Campo columna inicial deber estar compuesto por una letra y un numero. Verifique e intente nuevamente';
        $this->response['status']="Error";
        $this->response['msg']=$msg;
        $this->response['mode']="error";
        self::msg($this->response);
       }
    }  
}

$obj=(!empty($_POST) && isset($_POST)) ? new Lectu_Cuadr_PLM_controller():'';
$return_home=($obj=='') ? new Lectu_Cuadr_PLM_controller():'';
$method=($obj!='') ? method_exists($obj,$_POST['method_class']):'';
$method_name=($method) ? (String)$_POST['method_class']:'';
if($method_name==''){
    echo json_encode("no existe",JSON_FORCE_OBJECT);
}else{
    $recep_data=($obj!='' && $method=true) ? $obj->$method_name($_POST):$return_home->return_page();
}
?>
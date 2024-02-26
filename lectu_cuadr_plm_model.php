<?php 


require_once ("cadena.php");
require_once ("Conexion_Pdo.php");
require("functions/funciones_globales.php");

Class Lectu_Cuadr_PLM_Model{

    private $conexion = null;
    private $log=null;
    public function __construct(){ 
    }
 
    public function connection(){
        $servidor = $_SERVER['SERVER_NAME'];
        $nume_serv = (int) filter_var($servidor, FILTER_SANITIZE_NUMBER_INT);  
        switch($nume_serv){
            case '2':
             $this->conexion = new conexion_pdo("Informix","DesarrolloColombia",$this->log,false);
            break;
            case '3':
             $this->conexion = new conexion_pdo("Informix","CalidadColombia",$this->log,false);
            break;
          default:
             $this->conexion = new conexion_pdo("Informix","ProduccionColombia",$this->log,false);
            break;
        };

        $this->conexion->ejecutar_consulta("SET ISOLATION TO DIRTY READ");
    }

     /**
        * Metodo Para Iniciar Transaccion DB
    */
    public function inic_tran() {
        $inic_tran = "BEGIN WORK;";
        $this->conexion->ejecutar_consulta($inic_tran);
        $wait_mode = "SET LOCK MODE TO WAIT;";
        $this->conexion->ejecutar_consulta($wait_mode);
    }

  
    /**
     * Metodo Para Finalizar Transaccion DB
     */
    public function fina_tran() {
        $fina_tran = "COMMIT WORK;";
        $this->conexion->ejecutar_consulta($fina_tran);
    }
 
    public function send_file_log($log_file){
        $this->log=$log_file;
        self::connection();
    }

    public function cons_camp($campana){
        $sql="SELECT codi_camp 
              FROM tab_inic_camp
              WHERE acti_esta='ACT' 
                AND  codi_camp='{$campana}';";
        $result=$this->conexion->ejecutar_consulta($sql);
        return $result;
    }

    public function delete_temp_table($table_name){
        $sql="DROP TABLE IF EXISTS {$table_name} ;";
        $result=$this->conexion->ejecutar_consulta($sql);
    }

    public function create_temp_table($table_name){
        $sql="CREATE  TABLE IF NOT EXISTS {$table_name}  (cons_cuad serial,
                nomb_cuad varchar(200), --Nombre de la hoja
                camp_naci varchar(200),--
                camp_adva varchar(200),--
                line_prod varchar(200),
                grup_prod varchar(200),--
                rang_prec varchar(200),--
                tipo_prod varchar(200),--
                desc_prod varchar(200),
                cost_obje varchar(200),
                cost_colo varchar(200),
                prec_colo varchar(200),
                cmvv_colo varchar(200),
                cost_peru varchar(200),
                prec_peru varchar(200),
                cmvv_peru varchar(200),
                segu_sali varchar(200),
                tota_mixx varchar(200),
                mixx_traf varchar(200),
                mixx_line varchar(200),
                mixx_basi varchar(200),
                mixx_moda varchar(200),
                mixx_embl varchar(200),
                lanz_traf varchar(200),
                obse_traf varchar(200),
                lanz_line varchar(200),
                obse_line varchar(200),
                lanz_basi varchar(200),
                obse_basi varchar(200),
                lanz_moda varchar(200),
                obse_moda varchar(200),
                lanz_embl varchar(200),
                obse_embl varchar(200),
                tota_lanz varchar(200),
                -- nume_refe varchar(6),
                acti_usua varchar(200),
                acti_hora varchar(200),
                acti_esta varchar(200)
                 )";
        $result=$this->conexion->ejecutar_consulta($sql);
        return $result;
    }

    public function cons_cant_reg($table_name,$codi_usua){
        $sql="SELECT nomb_cuad,COUNT(*) AS total_reg 
              FROM $table_name 
              WHERE acti_usua='{$codi_usua}'
              GROUP BY nomb_cuad;";
            //   echo $sql;
        $result=$this->conexion->ejecutar_consulta($sql);
        return $result;
    }
    public function insert_info($table_name){
        $sql="INSERT INTO plm_cuad_prod(cons_cuad 
                                       ,nomb_cuad
                                       , camp_naci 
                                       ,camp_adva 
                                       ,line_prod
                                       ,grup_prod
                                       ,rang_prec
                                       ,tipo_prod
                                       ,desc_prod 
                                       ,cost_obje 
                                       ,cost_colo 
                                       ,prec_colo 
                                       ,cmvv_colo 
                                       ,cost_peru 
                                       ,prec_peru 
                                       ,cmvv_peru 
                                       ,segu_sali 
                                       ,tota_mixx 
                                      	,mixx_traf 
                                        ,mixx_line 
                                        ,mixx_basi 
                                        ,mixx_moda 
                                        ,mixx_embl 
                                        ,lanz_traf 
                                        ,obse_traf
                                        ,lanz_line 
                                        ,obse_line
                                        ,lanz_basi 
                                        ,obse_basi
                                        ,lanz_moda 
                                        ,obse_moda
                                        ,lanz_embl 
                                        ,obse_embl
                                        ,tota_lanz 
                                        ,acti_usua 
                                        ,acti_hora 
                                        ,acti_esta 
              )
              SELECT 0
                ,trim(nomb_cuad) AS nomb_cuad
                ,trim(camp_naci)::INTEGER AS camp_naci
                ,trim(camp_adva)::INTEGER AS camp_adva
                ,trim(line_prod) AS line_prod
                ,trim(grup_prod) AS grup_prod
                ,trim(replace(replace(rang_prec,'$',''),'A','-')) AS rang_prec
                ,trim(tipo_prod) AS tipo_prod
                ,trim(desc_prod) AS desc_prod
                ,round(cost_obje)::INTEGER AS cost_obje
                ,round(cost_colo)::INTEGER AS const_colo
                ,round(prec_colo)::INTEGER AS prec_colo
                ,round((cmvv_colo*100))::DECIMAL(10,2) AS cmvv_colo
                ,round(cost_peru)::INTEGER AS cost_peru
                ,round(prec_peru)::DECIMAL(10,2) AS prec_peru
                ,round((cmvv_peru*100))::DECIMAL(10,2) As cmvv_peru
                ,trim(segu_sali)::SMALLINT AS segu_sali
                ,trim(tota_mixx)::SMALLINT AS  tota_mixx
                ,trim(mixx_traf)::SMALLINT AS mixx_traf
                ,trim(mixx_line)::SMALLINT AS mixx_line
                ,trim(mixx_basi)::SMALLINT AS mixx_basi
                ,trim(mixx_moda)::SMALLINT AS mixx_moda
                ,trim(mixx_embl)::SMALLINT AS mixx_embl
                ,trim(lanz_traf)::SMALLINT AS lanz_traf
                ,trim(obse_traf) AS obse_traf
                ,trim(lanz_line)::SMALLINT AS lanz_line
                ,trim(obse_line) AS obse_line
                ,trim(lanz_basi)::SMALLINT AS lanz_basi
                ,trim(obse_basi) AS obse_basi
                ,trim(lanz_moda)::SMALLINT AS lanz_moda
                ,trim(obse_moda) AS obse_moda
                ,trim(lanz_embl)::SMALLINT AS lanz_embl  
                ,trim(obse_embl) AS lanz_embl 
                ,tota_lanz::SMALLINT
                ,acti_usua
                ,acti_hora
                ,acti_esta
            FROM $table_name
            WHERE rang_prec!='' AND tipo_prod!=''
            ORDER BY cons_cuad ASC;";
        $result=$this->conexion->ejecutar_consulta($sql);
        return $result;
    }

    public function insert_data_temp_table($table_name,$class,$codi_usua){
     $class->writeSQL($table_name);
     return self::cons_cant_reg($table_name,$codi_usua);
    } 
}
?>
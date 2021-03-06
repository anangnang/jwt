<?php

defined('BASEPATH') OR exit('No direct script access allowed');
use \Firebase\JWT\JWT;

class Jwts extends REST_Controller {

    private $secretkey = '09dhUbdis8fomcoay7xb';

    function __construct()
    {
        // Construct the parent class
        parent::__construct();
        $this->load->model('M_main');
    }

    // noperk
    public function noperk_get($id){
        $jwt = $this->input->get_request_header('Authorization');
        try {
            $decode = JWT::decode($jwt,$this->secretkey,array('HS256'));
            if ($this->M_main->is_valid($decode-> noHP)>0) {
                $nik = $this->M_main->detail_user_coba($id);
                $noperk = $this->M_main->cek_noperk1($nik); 
                $output['error'] = false;
                $output['msg'] = "Data Ditemukan";
                $output['nik'] = $nik;
                $output['data'] = $noperk;
                $this->set_response($output, REST_Controller::HTTP_OK);
            }
        } catch (Exception $e) {
            $output['error'] = true;
            $output['status'] = 200;
            $output['response'] = 'Unauthorized';
            $this->set_response($output, REST_Controller::HTTP_OK);
            //exit();
        }
    }
    // end

    public function login_post()
    {
        $u = $this->post('noHP');
        $p = sha1($this->post('password'));
        $q = array('noHP' => $u); 
       
        $val = $this->M_main->get_user($q)->row(); 
        if($this->M_main->get_user($q)->num_rows() == 0){
            
            $this->response([
                "error"=>true,
                "msg"=>"Login gagal",
                "data"=>null
            ], REST_Controller::HTTP_OK);
        }
        $match = $val->password;  
        if($p == $match){  
            //$token['id'] = $val->id;
            $token['noHP'] = $u;
            $date = new DateTime();
            $token['iat'] = $date->getTimestamp();
            $token['exp'] = $date->getTimestamp() + 60*60*5; 
            $outputs['token'] = JWT::encode($token,$this->secretkey); 
            $outputs['id'] = $val->id;
            $this->M_main->login($outputs['token'],$outputs['id']); 
     //       $output['data']=$this->M_main->logined($outputs['token'],$outputs['id']); 
            
            $data=$this->M_main->logined($outputs['token'],$outputs['id']); 
            $this->set_response([
                "error" => false,
                "msg" => "Login sukses",
                "data" => $data
            ], REST_Controller::HTTP_OK);
        }
        else {
            $output['error'] = true;
            $output['status'] = 200;
            $output['data'] = null;
            $output['msg'] = "Login gagal";
            $this->set_response($output, REST_Controller::HTTP_OK);
        }
    }


    public function register_post()
    {   
        $nik = $this->post('nik'); 
        $noHP = $this->post('noHP'); 
        $password = sha1($this->post('password')); 
        $kunci = $this->config->item('thekey');
        $val = $this->M_main->register_user($nik,$noHP,$password); 
            $token['nik'] = $nik;
            $date = new DateTime();
            $token['iat'] = $date->getTimestamp();
            $token['exp'] = $date->getTimestamp() + 60*60*5; 
            $output['nik'] = $nik;
            $output['noHP'] = $noHP;
            $this->set_response($output, REST_Controller::HTTP_OK); 
      
    }


    public function verifikasi_put($id)
    {   
        $cek_token=$this->cektoken_post();
        if($cek_token){
        $kode = $this->put('otp'); 
        if($kode==1234){
            $this->M_main->verifikasi_user($id); 
            $output['error'] = false;
            $output['message'] = 'User Verified';
            $this->set_response($output, REST_Controller::HTTP_OK);
        }
        else{
            $output['error'] = true;
            $output['message'] = 'User Not Verified';
            $this->set_response($output, REST_Controller::HTTP_OK); 
        }
        }
        else{
            $output['error'] = true;
            $output['status'] = 200;
            $output['response'] = 'Unauthorized';
            $this->set_response($output, REST_Controller::HTTP_OK);
        }
      
      
    }


    public function listUsers_get()
    {   
        $cek_token=$this->cektoken_post();
        if($cek_token){
        $var = $this->M_main->get_user_all(); 
        $this->response($var);
        $this->set_response($output, REST_Controller::HTTP_OK);
        }
        else{
            exit('Wrong Token');
        }
    }


    //pakai auth
    public function listUserss_get()
    {   
                $response = $this->M_main->auth();
                if($response['status'] == 200){
                     $var = $this->M_main->get_user_all(); 
                $this->response($var);
                $this->set_response($output, REST_Controller::HTTP_OK);
                } else {
                    $this->response($response);
                }
    }


    public function cektoken_post(){
        $jwt = $this->input->get_request_header('Authorization');
        try {
            $decode = JWT::decode($jwt,$this->secretkey,array('HS256'));
                return true;
            
        } catch (Exception $e) {
            return false;
        }
    }


    public function dataUmum_get($id){
        $jwt = $this->input->get_request_header('Authorization');
        try {
            $decode = JWT::decode($jwt,$this->secretkey,array('HS256'));
            if ($this->M_main->is_valid($decode-> noHP)>0) {
                $nik = $this->M_main->detail_user_coba($id);
                $noperk = $this->M_main->cek_noperk($nik); 
                $output['error'] = false;
                $output['status'] = 200;
                $output['nik'] = $nik;
                $output['dataUmum'] = $noperk;
                $this->set_response($output, REST_Controller::HTTP_OK);
            }
        } catch (Exception $e) {
            $output['error'] = true;
            $output['status'] = 401;
            $output['response'] = 'Unauthorized';
            $this->set_response($output, REST_Controller::HTTP_OK);
            //exit();
        }
    }

    public function ambil_antrian_get($perkara_id){
        $cek_token=$this->cektoken_post();
        if($cek_token){
        $waktu = $this->post('waktu'); 
        $pagi=123;
        if($waktu==$pagi){
            $output['error'] = true;
            $output['status'] = 401;
            $output['response'] = 'Unauthorized';
            $this->set_response($output, REST_Controller::HTTP_OK);
        }
        else{ 
            $output['error'] = false;
            $this->set_response($output, REST_Controller::HTTP_OK);
        }
        }
        else{
            exit('Wrong Token');
        }
    }


    public function sidang_get($nik){
        $jwt = $this->input->get_request_header('Authorization');
        try {
            $decode = JWT::decode($jwt,$this->secretkey,array('HS256'));
            if ($this->M_main->is_valid($decode-> noHP)>0) {
                $var = $this->M_main->detail_user_coba($nik);
                $sidang = $this->M_main->get_jadwal_sidang($var);
                 $this->response($sidang);
                $this->set_response($output, REST_Controller::HTTP_OK);
            }
        } catch (Exception $e) {
            $output['error'] = true;
            $output['status'] = 401;
            $output['response'] = 'Unauthorized';
            $this->set_response($output, REST_Controller::HTTP_OK);
        }
    }

    public function sidang_perkara_get($perkara_id){
        $jwt = $this->input->get_request_header('Authorization');
        try {
            $decode = JWT::decode($jwt,$this->secretkey,array('HS256'));
            if ($this->M_main->is_valid($decode-> noHP)>0) {
                $sidang = $this->M_main->get_jadwal_sidang_per_perkara($perkara_id);
                 $this->response($sidang);
                $this->set_response($output, REST_Controller::HTTP_OK);
            }
        } catch (Exception $e) {
            $output['error'] = true;
            $output['status'] = 200;
            $output['response'] = 'Unauthorized';
            $this->set_response($output, REST_Controller::HTTP_OK);
        }
    }


    public function biaya_perkara_get($nik){
        $jwt = $this->input->get_request_header('Authorization');
        try {
            $decode = JWT::decode($jwt,$this->secretkey,array('HS256'));
            if ($this->M_main->is_valid($decode-> noHP)>0) {
                $var = $this->M_main->detail_user_coba($nik);
                $sidang = $this->M_main->get_biaya_sidang($var);
                 $this->response($sidang);
                $this->set_response($output, REST_Controller::HTTP_OK);
            }
        } catch (Exception $e) {
            $output['error'] = true;
            $output['status'] = 401;
            $output['response'] = 'Unauthorized';
            $this->set_response($output, REST_Controller::HTTP_OK);
        }
    }


    public function riwayat_perkara_get($nik){
        $jwt = $this->input->get_request_header('Authorization');
        try {
            $decode = JWT::decode($jwt,$this->secretkey,array('HS256'));
            if ($this->M_main->is_valid($decode-> noHP)>0) {
                $var = $this->M_main->detail_user_coba($nik);
                $riwayat = $this->M_main->get_riwayat($var);
                $this->response($riwayat);
                $this->set_response($output, REST_Controller::HTTP_OK);
            }
        } catch (Exception $e) {
            $output['error'] = true;
            $output['status'] = 401;
            $output['response'] = 'Unauthorized';
            $this->set_response($output, REST_Controller::HTTP_OK);;
        }
    }


    public function logout_post() {
         $cek_token=$this->cektoken_post();
         $jwt = $this->input->get_request_header('Authorization');
         $userid  = $this->input->get_request_header('User-ID');
        if($cek_token){
        $var = $this->M_main->logout($jwt, $userid); 
        $this->response($var);
        $this->set_response($output, REST_Controller::HTTP_OK);
        }
        else{
            exit('Wrong Token');
        }

    }


}
?>

<?php if(!defined('BASEPATH')) exit('No direct script allowed');

class M_main extends CI_Model{

	private $db2;

	public function __construct()
    {
        parent::__construct();
        $this->db2  = $this->load->database('sipp_db', TRUE);
    }

	public function get_user($q) {
		return $this->db->get_where('m_user',$q);
	}

	public function listToken() {
		$all = $this->db->get("m_user_authentication")->result();
	    return $all;
	}

	public function login($token, $id) {
		$expired_at = date("Y-m-d H:i:s", strtotime('+12 hours'));
		$this->db->insert('m_user_authentication',array('user_id' => $id,
                                    'token' => $token,'expired_at' => $expired_at));
	}

	public function auth() {
        $users_id  = $this->input->get_request_header('User-ID', TRUE);
        $token     = $this->input->get_request_header('Authorization', TRUE);

        if ($users_id == ""){
                return array('status' => 204,'message' => 'Headers ID is Null.');
            } elseif ($token == "") {
                return array('status' => 204,'message' => 'headers Auth is Null.');
            } else {
                $q  = $this->db->select('expired_at')->from('m_user_authentication')->where('user_id',$users_id)->where('token',$token)->get()->row();
                if($q == ""){
                    return array('status' => 401,'message' => 'Unauthorized.');
                } else {
                    if($q->expired_at < date('Y-m-d H:i:s')){
                        return array('status' => 401,'message' => 'Your session has been expired.');
                    } else {
                        $updated_at = date('Y-m-d H:i:s');
                        $expired_at = date("Y-m-d H:i:s", strtotime('+12 hours'));
                        $this->db->where('user_id',$users_id)->where('token',$token)->update('m_user_authentication',array('expired_at' => $expired_at,'updated_at' => $updated_at));
                        return array('status' => 200,'message' => 'Authorized.');
                    }
                }
        }
    }

	public function register($uuid,$nik,$noHP,$encrypted_password, $password) {
		$this->db->set('nik',  $nik);
		$this->db->set('noHP',  $noHP);
		$this->db->set('unique_id', $uuid);
		$this->db->set('encrypted_password', $encrypted_password);
		$this->db->set('salt', $password);
		$this->db->insert('userweb');
		return $this->db->insert_id();
	}

	public function register_user($nik,$noHP,$password) {
		$this->db->set('nik',  $nik);
		$this->db->set('noHP',  $noHP);
		$this->db->set('password', $password);
		$this->db->insert('m_user');
		return $this->db->insert_id();
	}

	public function cek_noperk($nik) 
  	{
	    $this->db2->select('perkara.perkara_id, perkara.tanggal_pendaftaran as tanggal_daftar, perkara.nomor_perkara');
		$this->db2->from('perkara_pihak2');
        $this->db2->join('perkara', 'perkara.perkara_id=perkara_pihak2.perkara_id');
        $this->db2->join('dirput_dokumen', 'perkara.perkara_id=dirput_dokumen.perkara_id','left');
        $this->db2->join('perkara_akta_cerai', 'perkara.perkara_id=perkara_akta_cerai.perkara_id','left');
        $this->db2->join('perkara_putusan', 'perkara.perkara_id=perkara_putusan.perkara_id','left');
		$this->db2->join('pihak', 'perkara_pihak2.pihak_id=pihak.id');
		$this->db2->where('pihak.nomor_indentitas' , $nik);
	    $query = $this->db2->get()->result();
	    $response['status']=200;
	    $response['noperk']=$query;
	    return $query;
	}
	
	public function cek_noperk2($nik) 
  	{
	    $this->db2->select('perkara.perkara_id, perkara.nomor_perkara');
		$this->db2->from('perkara_pihak2');
        $this->db2->join('perkara', 'perkara.perkara_id=perkara_pihak2.perkara_id');
		$this->db2->join('pihak', 'perkara_pihak2.pihak_id=pihak.id');
		$this->db2->where('pihak.nomor_indentitas' , $nik);
	    $query = $this->db2->get()->result();
	    $response['status']=200;
	    $response['noperk']=$query;
	    return $query;
	}

	public function cek_noperk1($nik) 
  	{
	    $query = $this->db2->query("select perkara.perkara_id, perkara.nomor_perkara FROM pihak
LEFT JOIN perkara_pihak2 ON pihak.`id`=perkara_pihak2.`pihak_id`
LEFT JOIN perkara_pihak1 ON pihak.`id`=perkara_pihak1.`pihak_id`
JOIN perkara ON perkara.`perkara_id`=(IF(perkara_pihak2.`perkara_id` IS NULL, perkara_pihak1.`perkara_id`, perkara_pihak2.`perkara_id`))
WHERE pihak.`nomor_indentitas`=$nik");
	    $query1=$query->result();
	    $response['status']=200;
	    $response['noperk']=$query;
	    return $query1;

	public function logined($token, $id) 
  	{
	    $this->db->select('id,user_id,token');
		$this->db->from('m_user_authentication');
		$this->db->where('token' , $token);
		$this->db->where('user_id' , $id);
	    $query = $this->db->get()->result();
	    return $query;
	}

	public function jum_noperk($nik) 
  	{
	    $this->db2->select('*');
		$this->db2->from('perkara_pihak2');
		$this->db2->join('pihak', 'perkara_pihak2.pihak_id=pihak.id');
        $this->db2->join('perkara', 'perkara.perkara_id=perkara_pihak2.perkara_id');
		$this->db2->where('pihak.nomor_indentitas' , $nik);
	    $query = $this->db2->get();
	    return $query->num_rows();
	}

	public function get_user_all() {
		$all = $this->db->get("m_user")->result();
	    $response['status']=200;
	    $response['error']=false;
	    $response['users']=$all;
	    return $response;
	}


	public function is_valid($noHP)
	{
	    $this->db->select('*');
	    $this->db->from('m_user');
	    $this->db->where('noHP',$noHP);
	    $query = $this->db->get();
	    return $query->num_rows();
  	}

  	public function cek_nik($nik)
	{
	    $this->db2->select('*');
		$this->db2->from('perkara_pihak2');
		$this->db2->join('pihak', 'perkara_pihak2.pihak_id=pihak.id');
        $this->db2->join('perkara', 'perkara.perkara_id=perkara_pihak2.perkara_id');
		$this->db2->where('pihak.nomor_indentitas' , $nik);
	    $query = $this->db2->get();
	    return $query->num_rows();
  	}

  	public function cek_niknya($nik)
	{
	    $this->db->select('*');
		$this->db->from('userweb');
		$this->db->where('nik' , $nik);
	    $query = $this->db->get();
	    return $query->num_rows();
  	}


  	public function cek_user($noHP)
	{
	    $this->db->select('*');
		$this->db->from('userweb');
		$this->db->where('noHP' , $noHP);
	    $query = $this->db->get();
	    return $query->num_rows();
  	}

  	public function detail_user($id) 
  	{
	    $this->db->select('*');
	    $this->db->from('m_user');
	    $this->db->where('id',$id);
	    $query = $this->db->get()->result();
	    $response['status']=200;
	    $response['user']=$query;
	    return $response;
	}

	public function detail_users($noHP) 
  	{
	    $this->db->select('*');
	    $this->db->from('userweb');
	    $this->db->where('noHP',$noHP);
	    $query = $this->db->get()->result();
	    $response['status']=200;
	    $response['user']=$query;
	    return $query;
	}


	public function detail_user_coba($id) 
  	{	
  		$query = $this->db->query("select nik from m_user where id='$id'");
        $row = $query->row();
	    return $row->nik;
	}


	public function get_salting($noHP) 
  	{	
  		$query = $this->db->query("select * from userweb where noHP='$noHP'");
        $row = $query->row_array();
	    return $row;
	}

	public function get_noperk($nik) 
  	{	
  		$this->db2->select('perkara.perkara_id');
		$this->db2->from('perkara_pihak2');
		$this->db2->join('pihak', 'perkara_pihak2.pihak_id=pihak.id');
        $this->db2->join('perkara', 'perkara.perkara_id=perkara_pihak2.perkara_id');
		$this->db2->where('pihak.nomor_indentitas' , $nik);
	    $query = $this->db2->get()->result();
	    return $query;
	}

	public function cek_antrian($nik) 
  	{
	    $this->db->select('*');
	    $this->db->from('m_user');
	    $this->db->where('nik',$nik);
	    $query = $this->db->get()->result();
	    $response['status']=200;
	    $response['user']=$query;
	    return $response;
	}

	public function registered($id) 
  	{	
  		$this->db->select('*');
	    $this->db->from('userweb');
	    $this->db->where('nik',$id);
        $query = $this->db->get()->result();
	    return $query;
	}


    public function get_jadwal_sidang($nik)
	{
	    $this->db2->select('perkara_jadwal_sidang.id, perkara_jadwal_sidang.perkara_id, perkara_jadwal_sidang.verzet, perkara_jadwal_sidang.keberatan, perkara_jadwal_sidang.ikrar_talak, perkara_jadwal_sidang.urutan, perkara_jadwal_sidang.tanggal_sidang, perkara_jadwal_sidang.jam_sidang, perkara_jadwal_sidang.sampai_jam, perkara_jadwal_sidang.agenda_id, perkara_jadwal_sidang.agenda, perkara_jadwal_sidang.ruangan_id, perkara_jadwal_sidang.ruangan, perkara_jadwal_sidang.sidang_keliling, perkara_jadwal_sidang.dihadiri_oleh, perkara_jadwal_sidang.ditunda, perkara_jadwal_sidang.alasan_ditunda, perkara_jadwal_sidang.sidang_ditempat, perkara_jadwal_sidang.sifat_sidang, perkara_jadwal_sidang.keterangan');
		$this->db2->from('perkara_pihak2');
		$this->db2->join('pihak', 'perkara_pihak2.pihak_id=pihak.id');
        $this->db2->join('perkara_jadwal_sidang', 'perkara_jadwal_sidang.perkara_id=perkara_pihak2.perkara_id', 'left');
		$this->db2->where('pihak.nomor_indentitas' , $nik);
	    $query = $this->db2->get()->result();
	    $response['error']=false;
	    $response['status']=200;
	    $response['response']='jadwal sidang ditemukan';
	    $response['sidang_detil']=$query;
	    return $response;
	}


	public function get_biaya_sidang($nik)
	{
	    $this->db2->select('perkarabiayaweb.ID,perkarabiayaweb.IDPerkara, perkarabiayaweb.IDTahapan, perkarabiayaweb.jenisTransaksi, perkarabiayaweb.tglTransaksi, perkarabiayaweb.uraian, perkarabiayaweb.nominal, perkarabiayaweb.keterangan');
		$this->db2->from('perkara_pihak2');
		$this->db2->join('pihak', 'perkara_pihak2.pihak_id=pihak.id');
        $this->db2->join('perkarabiayaweb', 'perkarabiayaweb.IDPerkara=perkara_pihak2.perkara_id');
		$this->db2->where('pihak.nomor_indentitas' , $nik);
	    $query = $this->db2->get()->result();
	    $response['error']=false;
	    $response['status']=200;
	    $response['response']='biaya ditemukan';
	    $response['biaya_detil']=$query;
	    return $response;
	}


	public function get_riwayat($nik)
	{
	    $this->db2->select('perkaraprosesweb.IDPerkara, perkaraprosesweb.IDTahapan, perkaraprosesweb.tahapan, perkaraprosesweb.IDProses, perkaraprosesweb.proses, perkaraprosesweb.tanggal');
		$this->db2->from('perkara_pihak2');
		$this->db2->join('pihak', 'perkara_pihak2.pihak_id=pihak.id');
        $this->db2->join('perkaraprosesweb', 'perkaraprosesweb.IDPerkara=perkara_pihak2.perkara_id');
		$this->db2->where('pihak.nomor_indentitas' , $nik);
	    $query = $this->db2->get()->result();
	    $response['error']=false;
	    $response['status']=200;
	    $response['response']='riwayat ditemukan';
	    $response['biaya_detil']=$query;
	    return $response;
	}

	 public function logout($token,$userid) {
        //$users_id  = $this->input->get_request_header('User-ID', TRUE);
        //$token     = $this->input->get_request_header('Authorization', TRUE);

        if ($userid == ""){
            return array('error' => true,'status' => 204,'message' => 'Headers ID is Null.');
        } elseif ($token == "") {
            return array('error' => true,'status' => 204,'message' => 'headers Auth is Null.');
        } else {
            $this->db->where('user_id',$userid)->where('token',$token)->delete('m_user_authentication');
            return array('error' => false,'status' => 200,'message' => 'Successfully logout.');
        }
    }


}

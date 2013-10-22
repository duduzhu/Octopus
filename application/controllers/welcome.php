<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Welcome extends CI_Controller {

	/**
	 * Index Page for this controller.
	 *
	 * Maps to the following URL
	 * 		http://example.com/index.php/welcome
	 *	- or -  
	 * 		http://example.com/index.php/welcome/index
	 *	- or -
	 * Since this controller is set as the default controller in 
	 * config/routes.php, it's displayed at http://example.com/
	 *
	 * So any other public methods not prefixed with an underscore will
	 * map to /index.php/welcome/<method_name>
	 * @see http://codeigniter.com/user_guide/general/urls.html
	 */
    function get_current_user()
    {
        if(isset($_SESSION['csl']))
        {
            return $_SESSION['csl'];
        }
        else
        {
            return "anonymous";
        }
    }
    function __construct()
    {
        parent::__construct();
        $this->load->helper(array('form','url'));
        session_start();
    }
    public function index()
    {
        /*$ip_record = $this->db->query('select * from meta where SN = "'.$this->get_current_user().'"')->result();
        if(count($ip_record)==1 && $ip_record[0]->USER!=$this->get_current_user())
        {
            $this->ownmeta($ip_record[0]->id);
        }*/

        if(!$this->logged()){$this->login();return;}

        if(isset($_REQUEST['category']))
        {
            switch($_REQUEST['category'])
            {
                case 'myri' : $this->myri(); break;
                case 'mx' : $this->mx(); break;
                case 'upload' : $this->upload(); break;
                case 'do_upload' : $this->do_upload(); break;
                case 'misalign' : $this->misalign(); break;
                case 'allri' : $this->allri(); break;
                case 'ownparent' : $this->ownparent(); break;
                case 'ownmeta' : $this->ownmeta(); break;
                case 'showmeta' : $this->showmeta(); break;
                case 'showmetatype' : $this->showmetatype(); break;
                case 'showparent' : $this->showparent(); break;
                case 'showparenttype' : $this->showparenttype(); break;
                case 'ri_by_user' : $this->ri_by_user(); break;
                case 'releasemeta' : $this->releasemeta(); break;
                case 'releaseparent' : $this->releaseparent(); break;
                case 'ip' : $this->ip(); break;
                case 'updatenote' : $this->updatenote(); break;
                case 'login' : $this->login(); break;
                case 'logout' : $this->logout(); break;
            }
        }
        else
            $this->myri();
    }
    private function upload($error=array('error' => ''))
    {
        $this->load->view('header', array('vampireuser' => $this->get_current_user()));
        $this->load->view('upload_csv', $error);
        $this->load->view('tail');
    }
    private function logout()
    {
        $_SESSION['csl']='anonymous';
        session_destroy();
        $this->login();
    }
    private function registercsl($user)
    {
        session_register('csl');
        $_SESSION['csl']=$user;
    }
    private function logged()
    {
        return isset($_SESSION['csl']);
    }
    private function login()
    {
        $login_success=false;

        if(isset($_REQUEST['csl']))
        {
            $user=trim($_REQUEST['csl']);
            $password=trim($_REQUEST['password']);
        }
        else
        {
            $user="";
            $password="";
        }
        if(isset($_SERVER['REMOTE_USER']))
        {
            $login_success=true;
            $user=$_SERVER['REMOTE_USER'];
        }
        else if(isset($user) && $user && isset($password) && $password)
        {
            $ds = ldap_connect('ldapca.na.alcatel.com');
            @ldap_bind($ds);
            $search = ldap_search($ds, "o=Alcatel", "uid=".$user);
            if( ldap_count_entries($ds,$search) == 1 )
            {
                $info = ldap_get_entries($ds, $search);
                $bind = @ldap_bind($ds, $info[0]['dn'], $password);
                if( !$bind || !isset($bind))
                {
                    echo "Login Failed!<br/>";
                }
                else
                {
                    $login_success=true;
                }
                ldap_unbind($ds);
            }
            else
            {
                echo "Unknow CSL!<br/>";
            }
        }

        if($login_success)
        {
            $this->registercsl($user);
            $this->myri();
            return;
        }
        else
        {
            $this->load->view('header', array('vampireuser' => $this->get_current_user()));
            $this->load->view('login');
            $this->load->view('tail');
        }
    }
    private function do_upload()
    {
        $config['upload_path']='./uploads/';
        $config['allowed_types']='*';
        $config['max_size']='10240';
        $this->load->library('upload',$config);
        if ( ! $this->upload->do_upload())
        {
            $error = array('error' => $this->upload->display_errors());
            $this->upload($error);
        }
        else
        {
            $uploaddata=$this->upload->data();
            $cmd="perl cronjobs/process.pl uploads/".$uploaddata['file_name'];
            $this->success_upload(`$cmd`);
        }
    }
    private function success_upload($message="")
    {
        $this->load->view('header', array('vampireuser' => $this->get_current_user()));
        echo "Upload Successfully.<br/>";
        echo $message;
        $this->load->view('tail');
    }
    private function metas_by_parent($id_parent)
    {
        return $this->db->query('select id_meta from link where id_parent = '.$id_parent)->result();
    }
    public function releasemeta()
    {
        $this->db->query('update meta set USER = "" where id = "'.$_REQUEST['meta_id'].'"');
        $this->myri();
    }
    public function releaseparent()
    {
        $this->db->query('update parent set USER = "" where id = "'.$_REQUEST['parent_id'].'"');
        foreach ($this->metas_by_parent($_REQUEST['parent_id']) as $meta_id)
        {
            $this->db->query('update meta set USER = "" where id = '.$meta_id->id_meta.' and USER= "'.$this->get_current_user().'"');
        }
        $this->myri();
    }
    public function ownmeta($metaid="")
    {
        if($metaid=="")
        {
            $metaid=$_REQUEST['meta_id'];
        }
        $this->db->query('update meta set USER = "'.$this->get_current_user().'" where id = "'.$metaid.'"');
        $this->myri();
    }
    public function ownparent()
    {
        $this->db->query('update parent set USER = "'.$this->get_current_user().'" where id = "'.$_REQUEST['parent_id'].'"');
        foreach ($this->metas_by_parent($_REQUEST['parent_id']) as $meta_id)
        {
            $this->db->query('update meta set USER = "'.$this->get_current_user().'" where id = "'.$meta_id->id_meta.'" and USER = "" ');
        }
        $this->myri();
    }

    private function row_by_record($linkrecord)
    {
        $meta_id = $linkrecord->id_meta;
        $parent_id = $linkrecord->id_parent;
        $parent_info = $this->db->query('select * from parent where id = '. $parent_id)->result();
        $meta_info = $this->db->query('select * from meta where id = '. $meta_id)->result();

        $row['parent_type']=$parent_info[0]->MNEMONIC;
        $row['parent_sn']=$parent_info[0]->SN;
        $row['parent_user']=$parent_info[0]->USER;
        $row['parent_id']=$parent_id;
        $row['parent_note']=$parent_info[0]->NOTE;
        $row['meta_type']=$meta_info[0]->MNEMONIC;
        $row['meta_sn']=$meta_info[0]->SN;
        $row['meta_user']=$meta_info[0]->USER;
        $row['meta_id']=$meta_id;
        $row['meta_note']=$meta_info[0]->NOTE;
        $row['timestamp'] = $linkrecord->TIMESTAMP;
        return $row;
    }

	public function allri()
	{
        $this->ri_with_filter("All equipments",NULL,NULL,NULL);
	}
    public function showmetatype()
    {
        $this->ri_with_filter("All ".$_REQUEST['meta_type']."s ",NULL,NULL,array($_REQUEST['meta_type']));
    }
    public function showparent()
    {
        $note = $this->db->query("select * from parent where SN = '".$_REQUEST['parent_sn']."'")->result();
        $data['note']=$note[0]->NOTE;
        $data['databasetable']='parent';
        $data['id']=$_REQUEST['parent_sn'];
        $data['vampireuser']=$this->get_current_user();
        $data['heading']="All equipments attached to ".$_REQUEST['parent_sn'];
        $data['table']=array();
        $link = $this->db->get("link")->result();
        foreach ($link as $linkrecord)
        {
            $row=$this->row_by_record($linkrecord);
            if($row['parent_sn'] != $_REQUEST['parent_sn'])
            {
                continue;
            }
            array_push($data['table'], $row);
        }

        $this->load->view('header', array('vampireuser' => $this->get_current_user()));
		$this->load->view('show_record',$data);
		$this->load->view('tail');
    }

    public function showparenttype()
    {
        $data['vampireuser']=$this->get_current_user();
        $data['heading']="All ".$_REQUEST['parent_type'];
        $data['table']=array();
        $parent = $this->db->get("parent")->result();
        foreach ($parent as $parentrecord)
        {
            if($parentrecord->MNEMONIC != $_REQUEST['parent_type'])
                continue;
            $row['parent_type']=$parentrecord->MNEMONIC;
            $row['parent_sn']=$parentrecord->SN;
            $row['parent_user']=$parentrecord->USER;
            $row['parent_id']=$parentrecord->id;
            $row['parent_note']=$parentrecord->NOTE;
            $row['meta_type']='';
            $row['meta_sn']='';
            $row['meta_user']='';
            $row['meta_id']='';
            $row['meta_note']='';
            $row['timestamp'] = '';
            array_push($data['table'], $row);
        }

        $this->load->view('header', array('vampireuser' => $this->get_current_user()));
		$this->load->view('show_record',$data);
		$this->load->view('tail');
    }
    public function myri()
    {
        $this->ri_with_filter("Your equipments",$this->get_current_user(),NULL,NULL);
    }
    public function ri_by_user()
    {
        $this->ri_with_filter("Equipments of ".$_REQUEST['user'],$_REQUEST['user'],NULL,NULL);
    }

    public function updatenote()
    {
        if($_REQUEST['databasetable'] == 'meta' )
            $this->db->query('update meta set NOTE = \''.$_REQUEST['note'].'\' where id = '.$_REQUEST['id']);
        else
            $this->db->query('update parent set NOTE = \''.$_REQUEST['note'].'\' where sn = "'.$_REQUEST['id'].'"');
        $this->myri();
    }
    public function showmeta()
    {
        $data['vampireuser']=$this->get_current_user();
        $data['table']=array();
        $note = $this->db->query("select * from meta where id = ".$_REQUEST['meta_id'])->result();
        $data['note']=$note[0]->NOTE;
        $data['databasetable']='meta';
        $data['id']=$_REQUEST['meta_id'];
        $data['heading']="Resource History: ".$note[0]->MNEMONIC."-".$note[0]->SN;
        $history = $this->db->query("select * from history where id_meta = ".$_REQUEST['meta_id']." order by id desc")->result();
        foreach ($history as $item)
        {
            $row=$this->row_by_record($item);
            array_push($data['table'], $row);
        }

        $this->load->view('header', array('vampireuser' => $this->get_current_user()));
		$this->load->view('show_record',$data);
		$this->load->view('tail');
    }

    public function mx()
    {
        $this->ri_with_filter("MX Related Equipments",NULL,NULL,array("TMXA18","TMXA09","AGX9E","AGX18"));
    }
    public function ip()
    {
        $this->ri_with_filter("Lab IP Address Management",NULL,NULL,array("IP"));
    }
    private function misalign()
    {
        $data['vampireuser']=$this->get_current_user();
        $data['heading']="Misaligned Equipments";
        $data['table']=array();
        $link = $this->db->get("link")->result();
        foreach ($link as $linkrecord)
        {
            $row=$this->row_by_record($linkrecord);
            if(($row['parent_user'] || $row['meta_user']) && $row['parent_user'] != $row['meta_user'])
            {
                array_push($data['table'], $row);
            }
        }

        $this->load->view('header', array('vampireuser' => $this->get_current_user()));
		$this->load->view('show_record',$data);
		$this->load->view('tail');
    }
    private function ri_with_filter($heading,$user,$parent_sn,$meta_type)
    {
        $data['vampireuser']=$this->get_current_user();
        $data['heading']=$heading;
        $data['table']=array();
        $link = $this->db->get("link")->result();
        foreach ($link as $linkrecord)
        {
            $row=$this->row_by_record($linkrecord);
            if(!is_null($user) && $row['parent_user'] != $user && $row['meta_user'] != $user)
            {
                continue;
            }
            if(!is_null($meta_type) && !in_array($row['meta_type'] , $meta_type))
            {
                continue;
            }
            if(!is_null($parent_sn) && $row['parent_sn'] != $parent_sn)
            {
                continue;
            }
            array_push($data['table'], $row);
        }

        $this->load->view('header', array('vampireuser' => $this->get_current_user()));
		$this->load->view('show_record',$data);
		$this->load->view('tail');
    }
}

/* End of file welcome.php */
/* Location: ./application/controllers/welcome.php */

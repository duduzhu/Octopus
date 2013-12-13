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
        extract($_REQUEST);
        /*$ip_record = $this->db->query('select * from meta where SN = "'.$this->get_current_user().'"')->result();
        if(count($ip_record)==1 && $ip_record[0]->USER!=$this->get_current_user())
        {
            $this->ownmeta($ip_record[0]->id);
        }*/

        if(isset($touchUser))
	{
	    $this->touchUser($touchUser); return;
	}
        if(isset($preloginuser))
            $this->registercsl($preloginuser);

        if(!$this->logged()){$this->login();return;}

        if(isset($category))
        {
            switch($category)
            {
                case 'myri' : $this->myri(); break;
                case 'rio' : $this->load->view('rio'); break;
                case 'mx' : $this->mx(); break;
                case 'upload' : $this->upload(); break;
                case 'do_upload' : $this->do_upload(); break;
                case 'misalign' : $this->misalign(); break;
                case 'allri' : $this->allri(); break;
                case 'ownparent' : $this->ownparent(); break;
                case 'ownmeta' : $this->ownmeta(); break;
                #case 'showmeta' : $this->showmeta(); break;
                case 'showmetatype' : $this->showmetatype(); break;
                case 'showparent' : $this->showparent(); break;
                case 'showparenttype' : $this->showparenttype(); break;
                case 'ri_by_user' : $this->ri_by_user(); break;
                case 'releasemeta' : $this->releasemeta(); break;
                case 'deletemeta' : $this->deletemeta(); break;
                case 'releaseparent' : $this->releaseparent(); break;
                case 'deleteparent' : $this->deleteparent(); break;
                case 'ip' : $this->ip(); break;
                case 'updatenote' : $this->updatenote(); break;
                case 'login' : $this->login(); break;
                case 'logout' : $this->logout(); break;
                case 'transfermeta' : $this->transfermeta(); break;
                case 'transferparent' : $this->transferparent(); break;
            }
        }
        else
            $this->myri();
    }
    private function upload($error=array('error' => ''))
    {
        extract($_REQUEST);
        $this->load->view('header', array('vampireuser' => $this->get_current_user()));
        $this->load->view('upload_csv', $error);
        $this->load->view('tail');
    }
    private function logout()
    {
        extract($_REQUEST);
        $_SESSION['csl']='anonymous';
        session_destroy();
        $this->login();
    }
    private function registercsl($user,$checked="off")
    {
        session_register('csl');
        $_SESSION['csl']=$user;
	if($checked == "on")
	    setcookie('csl',$user,time()+3600*24*365);
    }
    private function logged()
    {
	if(!isset($_SESSION['csl']) && isset($_COOKIE['csl']))
	{
	    $_SESSION['csl'] = $_COOKIE['csl'];
	}
        return isset($_SESSION['csl']);
    }
    public function touchUser($touchUser)
    {
	$user=trim($touchUser);	
        $ds = ldap_connect('ldapca.na.alcatel.com');
        @ldap_bind($ds);
        $search = ldap_search($ds, "o=Alcatel", "uid=".$user);
        if( ldap_count_entries($ds,$search) == 1 )
	{
	    echo $user;
	}
    }
    private function login()
    {
        extract($_REQUEST);
        $login_success=false;

        if(isset($csl))
        {
            $user=trim($csl);
            if(isset($password))$password=trim($password);
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
            $this->registercsl($user,$keeplogin);
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
        extract($_REQUEST);
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
        extract($_REQUEST);
        $this->load->view('header', array('vampireuser' => $this->get_current_user()));
        echo "Upload Successfully.<br/>";
        echo $message;
        $this->load->view('tail');
    }
    private function metas_by_parent($id_parent)
    {
        extract($_REQUEST);
        return $this->db->query('select id_meta from link where id_parent = '.$id_parent)->result();
    }
    public function deletemeta()
    {
        extract($_REQUEST);
        $this->db->query('DELETE from meta where id = "'.$meta_id.'"');
        $this->db->query('DELETE from link where id_meta = "'.$meta_id.'"');
        $this->myri();
    }
    public function releasemeta($newuser="")
    {
        extract($_REQUEST);
        $this->db->query('update meta set USER = "'.$newuser.'" where id = "'.$meta_id.'"');
        $this->myri();
    }
    public function deleteparent()
    {
        extract($_REQUEST);
        $this->db->query('DELETE from parent where id = "'.$parent_id.'"');
        foreach ($this->metas_by_parent($parent_id) as $meta_id)
        {
            $this->db->query('DELETE from meta where id = '.$meta_id->id_meta);
        }
        $this->db->query('DELETE from link where id_parent = "'.$parent_id.'"');
        $this->myri();
    }
    public function releaseparent($newuser="")
    {
        extract($_REQUEST);
        $this->db->query('update parent set USER = "'.$newuser.'" where id = "'.$parent_id.'"');
        foreach ($this->metas_by_parent($parent_id) as $meta_id)
        {
            $this->db->query('update meta set USER = "'.$newuser.'" where id = '.$meta_id->id_meta.' and USER= "'.$this->get_current_user().'"');
        }
        $this->showparent();
    }
    public function ownmeta($metaid="")
    {
        extract($_REQUEST);
        if($metaid=="")
        {
            $metaid=$meta_id;
        }
        $this->db->query('update meta set USER = "'.$this->get_current_user().'" where id = "'.$metaid.'"');
        $this->myri();
    }
    public function ownparent()
    {
        extract($_REQUEST);
        $this->db->query('update parent set USER = "'.$this->get_current_user().'" where id = "'.$parent_id.'"');
        foreach ($this->metas_by_parent($parent_id) as $meta_id)
        {
            $this->db->query('update meta set USER = "'.$this->get_current_user().'" where id = "'.$meta_id->id_meta.'" and (USER = "" or USER = "TRANSFERING...") ');
        }
        $this->showparent();
    }

    private function row_by_record($linkrecord)
    {
        extract($_REQUEST);
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
        $row['source'] = $meta_info[0]->SOURCE;
        return $row;
    }

	public function allri()
	{
        extract($_REQUEST);
        $this->ri_with_filter("All equipments",NULL,NULL,NULL);
	}
    public function showmetatype()
    {
        extract($_REQUEST);
        $types=array();
        array_push($types,$meta_type);
        $this->ri_with_filter("All ".$meta_type."s ",NULL,NULL,$types);
    }
    public function showparent()
    {
        extract($_REQUEST);
        $note = $this->db->query("select * from parent where id = '".$parent_id."'")->result();
        $data['note']=$note[0]->NOTE;
        $data['databasetable']='parent';
        $data['id']=$note[0]->SN;
        $data['vampireuser']=$this->get_current_user();
        $data['heading']="All equipments attached to ".$note[0]->SN;
        $data['table']=array();
        $link = $this->db->get("link")->result();
        foreach ($link as $linkrecord)
        {
            $row=$this->row_by_record($linkrecord);
            if($row['parent_id'] != $parent_id)
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
        extract($_REQUEST);
        $data['vampireuser']=$this->get_current_user();
        $data['heading']="All ".$parent_type;
        $data['table']=array();
        $parent = $this->db->get("parent")->result();
        foreach ($parent as $parentrecord)
        {
            if($parentrecord->MNEMONIC != $parent_type)
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
            $row['source'] = $parentrecord->SOURCE;
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
        $this->ri_with_filter("Equipments of ".$user,$user,NULL,NULL);
    }

    public function updatenote()
    {
        extract($_REQUEST);
        if($databasetable == 'meta' )
            $this->db->query('update meta set NOTE = \''.$note.'\' where id = '.$id);
        else
            $this->db->query('update parent set NOTE = \''.$note.'\' where sn = "'.$id.'"');
        $this->myri();
    }
    /*public function showmeta()
    {
        extract($_REQUEST);
        $data['vampireuser']=$this->get_current_user();
        $data['table']=array();
        $note = $this->db->query("select * from meta where id = ".$meta_id)->result();
        $data['note']=$note[0]->NOTE;
        $data['databasetable']='meta';
        $data['id']=$meta_id;
        $data['heading']="Resource History: ".$note[0]->MNEMONIC."-".$note[0]->SN;
        $history = $this->db->query("select * from history where id_meta = ".$meta_id." order by id desc")->result();
        foreach ($history as $item)
        {
            $row=$this->row_by_record($item);
            array_push($data['table'], $row);
        }

        $this->load->view('header', array('vampireuser' => $this->get_current_user()));
		$this->load->view('show_record',$data);
		$this->load->view('tail');
    }*/
    public function transferparent()
    {
        extract($_REQUEST);
        $acceptlink="http://172.24.12.75/BSC_web/Vampire?category=ownparent&preloginuser=".$targetuser."&parent_id=".$parent_id;
        $rejectlink="http://172.24.12.75/BSC_web/Vampire?category=ownparent&preloginuser=".$this->get_current_user()."&parent_id=".$parent_id;
        $queryresult = $this->db->query("select * from parent where id = ".$parent_id)->result();
        $name=$queryresult[0]->MNEMONIC." - ".$queryresult[0]->SN;
        $TO=$targetuser."@sh.ad4.ad.alcatel.com";

        $HEADER="MIME-Version: 1.0\nContent-type: text/html; charset=utf-8\n";
        $HEADER.="Cc: ".$this->get_current_user()."@sh.ad4.ad.alcatel.com\n";
        $HEADER.="From: VAMPIRE_NO_REPLY\n";
        $SUBJECT="[Vampire]Equipment Transfering Request";
        $CONTENT="
            Hello\n".$this->get_current_user()." just transfered ".$name." to you. \n<a href='".$acceptlink."'>Click here</a> to accept(Force Transfer)\nor <a href='".$rejectlink."'>Click here</a> to reject(Withdraw).";
        mail($TO, $SUBJECT, $CONTENT, $HEADER);

        $this->releaseparent("TRANSFERING...");
    }
    public function transfermeta()
    {
        extract($_REQUEST);
        $acceptlink="http://172.24.12.75/BSC_web/Vampire?category=ownmeta&preloginuser=".$targetuser."&meta_id=".$meta_id;
        $rejectlink="http://172.24.12.75/BSC_web/Vampire?category=ownmeta&preloginuser=".$this->get_current_user()."&meta_id=".$meta_id;
        $queryresult = $this->db->query("select * from meta where id = ".$meta_id)->result();
        $name=$queryresult[0]->MNEMONIC." - ".$queryresult[0]->SN;
        $TO=$targetuser."@sh.ad4.ad.alcatel.com";

        $HEADER="MIME-Version: 1.0\nContent-type: text/html; charset=utf-8\n";
        $HEADER.="Cc: ".$this->get_current_user()."@sh.ad4.ad.alcatel.com\n";
        $HEADER.="From: VAMPIRE_NO_REPLY\n";
        $SUBJECT="[Vampire]Equipment Transfering Request";
        $CONTENT="
            Hello\n".$this->get_current_user()." just transfered ".$name." to you. \n<a href='".$acceptlink."'>Click here</a> to accept(Force Transfer)\nor <a href='".$rejectlink."'>Click here</a> to reject(Withdraw).";
        mail($TO, $SUBJECT, $CONTENT, $HEADER);
        $this->releasemeta("TRANSFERING...");
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

    private function ri_with_filter($heading,$user,$parent_sn,$meta_type=array())
    {
        extract($_REQUEST);
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
            if(!is_null($meta_type) && !in_array($row['meta_type'], (array)$meta_type))
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

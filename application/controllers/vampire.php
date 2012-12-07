<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Vampire extends CI_Controller {

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
    public function index()
    {
        $ip_record = $this->db->query('select * from meta where SN = "'.$_SERVER['REMOTE_ADDR'].'"')->result();
        if(count($ip_record)==1 && $ip_record[0]->USER!=$_SERVER['REMOTE_USER'])
        {
            $this->ownmeta($ip_record[0]->id);
            return;
        }

        if(isset($_REQUEST['category']))
        {
            switch($_REQUEST['category'])
            {
                case 'myri' : $this->myri(); break;
                case 'misalign' : $this->misalign(); break;
                case 'allri' : $this->allri(); break;
                case 'ownparent' : $this->ownparent(); break;
                case 'ownmeta' : $this->ownmeta(); break;
                case 'showmetahistory' : $this->showmetahistory(); break;
                case 'showmetatype' : $this->showmetatype(); break;
                case 'showparent' : $this->showparent(); break;
                case 'ri_by_user' : $this->ri_by_user(); break;
                case 'releasemeta' : $this->releasemeta(); break;
                case 'releaseparent' : $this->releaseparent(); break;
                case 'ip' : $this->ip(); break;
            }
        }
        else
        {
            $this->myri();
        }
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
            $this->db->query('update meta set USER = "" where id = '.$meta_id->id_meta.' and USER= "'.$_SERVER['REMOTE_USER'].'"');
        }
        $this->myri();
    }
    public function ownmeta($metaid="")
    {
        if($metaid=="")
        {
            $metaid=$_REQUEST['meta_id'];
        }
        $this->db->query('update meta set USER = "'.$_SERVER['REMOTE_USER'].'" where id = "'.$metaid.'"');
        $this->myri();
    }
    public function ownparent()
    {
        $this->db->query('update parent set USER = "'.$_SERVER['REMOTE_USER'].'" where id = "'.$_REQUEST['parent_id'].'"');
        foreach ($this->metas_by_parent($_REQUEST['parent_id']) as $meta_id)
        {
            $this->db->query('update meta set USER = "'.$_SERVER['REMOTE_USER'].'" where id = "'.$meta_id->id_meta.'" and USER = "" ');
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
        $row['meta_type']=$meta_info[0]->MNEMONIC;
        $row['meta_sn']=$meta_info[0]->SN;
        $row['meta_user']=$meta_info[0]->USER;
        $row['meta_id']=$meta_id;
        $row['timestamp'] = $linkrecord->TIMESTAMP;
        return $row;
    }

	public function allri()
	{
        $this->ri_with_filter("All equipments",NULL,NULL,NULL);
	}
    public function showmetatype()
    {
        $this->ri_with_filter("All ".$_REQUEST['meta_type']."s ",NULL,NULL,$_REQUEST['meta_type']);
    }
    public function showparent()
    {
        $this->ri_with_filter("All equipments attached to ".$_REQUEST['parent_sn'],NULL,$_REQUEST['parent_sn'],NULL);
    }

    public function myri()
    {
        $this->ri_with_filter("Your equipments",$_SERVER['REMOTE_USER'],NULL,NULL);
    }
    public function ri_by_user()
    {
        $this->ri_with_filter("Equipments of ".$_REQUEST['user'],$_REQUEST['user'],NULL,NULL);
    }

    public function showmetahistory()
    {
        $data['heading']="Resource History";
        $data['table']=array();
        $linkrecord = $this->db->query("select * from link where id_meta = ".$_REQUEST['meta_id'])->result();
        $linkrecord = $linkrecord[0];
        array_push($data['table'], $this->row_by_record($linkrecord));
        $history = $this->db->query("select * from history where id_meta = ".$_REQUEST['meta_id']." order by id desc")->result();
        foreach ($history as $item)
        {
            $row=$this->row_by_record($item);
            array_push($data['table'], $row);
        }
		$this->load->view('header');
		$this->load->view('show_record',$data);
		$this->load->view('tail');
    }

    public function ip()
    {
        $this->ri_with_filter("Lab IP Address Management",NULL,NULL,"IP");
    }
    private function misalign()
    {
        $data['heading']="Misaligned Equipments";
        $data['table']=array();
        $link = $this->db->get("link")->result();
        foreach ($link as $linkrecord)
        {
            $row=$this->row_by_record($linkrecord);
            if($row['parent_user'] && $row['meta_user'] && $row['parent_user'] != $row['meta_user'])
            {
                array_push($data['table'], $row);
            }
        }

		$this->load->view('header');
		$this->load->view('show_record',$data);
		$this->load->view('tail');
    }
    private function ri_with_filter($heading,$user,$parent_id,$meta_type)
    {
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
            if(!is_null($meta_type) && $row['meta_type'] != $meta_type)
            {
                continue;
            }
            if(!is_null($parent_id) && $row['parent_id'] != $parent_sn)
            {
                continue;
            }
            array_push($data['table'], $row);
        }

		$this->load->view('header');
		$this->load->view('show_record',$data);
		$this->load->view('tail');
    }
}

/* End of file welcome.php */
/* Location: ./application/controllers/welcome.php */

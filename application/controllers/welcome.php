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
	public function index()
	{
        $data['table']=array();
        $data['title'] = "Vampire -- GSM Shanghai Remote Inventory";
        $data['heading'] = "Hello ".$_SERVER['REMOTE_USER'].", All recorded RI";
#$data['link'] 

        $link = $this->db->get("link")->result();
        foreach ($link as $linkrecord)
        {
            $meta_id = $linkrecord->id_meta;
            $parent_id = $linkrecord->id_parent;
            $parent_info = $this->db->query('select * from parent where id = '. $parent_id)->result();
            $meta_info = $this->db->query('select * from meta where id = '. $meta_id)->result();

            $row['parent_type']=$parent_info[0]->MNEMONIC;
            $row['parent_sn']=$parent_info[0]->SN;
            $row['meta_type']=$meta_info[0]->MNEMONIC;
            $row['meta_sn']=$meta_info[0]->SN;
            $row['timestamp'] = $linkrecord->TIMESTAMP;
            array_push($data['table'], $row);
        }
		$this->load->view('show_all_ri',$data);
	}
}

/* End of file welcome.php */
/* Location: ./application/controllers/welcome.php */

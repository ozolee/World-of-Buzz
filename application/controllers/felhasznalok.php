<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class felhasznalok extends CI_Controller {


  public function __construct() {

      parent::__construct();

      if(!$this->session->userdata('logged_in')) {
          redirect(site_url());
      }
  }

	public function index(){

    $users = $this->wob_model->get_all_users();

    $data = array(
      'users' => $users,
    );

    $this->home->show($data);

	}

  public function save_new_user(){
      if($this->input->is_ajax_request()){

          $pass = sha1($this->input->post('pass'));

          $name =  $this->input->post('name');
          $email  =  $this->input->post('email');
          $permission = $this->input->post('permission');

          if($name && $email && $pass && ($permission || ($permission == "0"))) {

              // check email
              $check_email = $this->wob_model->get_users_where(array('email' => $email));

              if(!empty($check_email)) {
                  $this->output->set_output(json_encode(array(
                      'success'   => false,
                      'msg'       => lang('error_reserved_email')
                  )));
                  return;

              } else {

                  $user_id = $this->generate_user_id();
                  $user       = $this->wob_model->get_user_where(array('user_id' => $user_id));

                  while(!empty($user)){
                      $user_id    = $this->generate_user_id();
                      $user       = $this->wob_model->get_user_where(array('user_id' => $user_id));
                  }

                  $insert = array(
                    'user_id' => $user_id,
                    'name' => $name,
                    'email' => $email,
                    'pass'  => $pass,
                    'permission' => $permission
                  );

                  $insert_user = $this->wob_model->insert_user($insert);

                  if($insert_user) {

                      $this->session->set_flashdata('success',lang('success_user_registration'));

                      $this->output->set_output(json_encode(array(
                          'success'   => true,
                      )));
                      return;

                  } else {
                      $this->output->set_output(json_encode(array(
                          'success'   => false,
                          'msg'       => lang('error_registration')
                      )));
                      return;
                  }
              }

          } else {
              $this->output->set_output(json_encode(array(
                  'success'   => false,
                  'msg'       => lang('error_empty_field')
              )));
              return;
          }

      } else {
          redirect('/');
      }

  }

  public function delete_user(){
      if($this->input->is_ajax_request()){

          $user_id  =  $this->input->post('user_id');

          $check = $this->wob_model->get_user_where(array('user_id' => $user_id));

          if(empty($check)) {

              $this->output->set_output(json_encode(array(
                  'success'   => false,
                  'msg'       => lang('error_data_values')
              )));
              return;

          } else {

              $delete = $this->wob_model->delete_user(array('user_id' => $user_id));

              if($delete){

                  $count = $this->wob_model->count_users(array());

                  $this->output->set_output(json_encode(array(
                      'success'   => true,
                      'count'     => $count,
                      'msg'   => lang('success_user_delete')
                  )));
                  return;

              } else {
                  $this->output->set_output(json_encode(array(
                      'success'   => false,
                      'msg'       => lang('error_delete')
                  )));
                  return;
              }
          }


      } else {
          redirect('/');
      }

  }

  public function generate_user_id($length = 6) {
    $charset    = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
    $key        = '';

    for($i=0; $i < $length; $i++) {
            $key .= $charset[(mt_rand(0, (strlen($charset) - 1)))];
    }

    return $key;
  }


}

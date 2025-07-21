<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Register extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Menu_model');
        $this->load->model('Register_model');
        $this->load->model('Common_model');
        $this->load->library('session');
        $this->load->library('email');
        $this->load->library('zoom');
    }

    public function Registers()
    {
        $this->load->helper('url');

        $data = array();

        $lang = $this->session->userdata('lang') == null ? "thailand" : $this->session->userdata('lang');
        $data['menu_bar'] = $this->Menu_model->menu_bar();

        $this->lang->load($lang, $lang);
        $this->load->view('dashboard/header', $data, $lang);
        $this->load->view('Register', $lang);
        $this->load->view('dashboard/footer', $lang);
    }

    public function Landings()
    {
        $this->load->helper('url');

        $data = array();

        $lang = $this->session->userdata('lang') == null ? "thailand" : $this->session->userdata('lang');
        $data['menu_bar'] = $this->Menu_model->menu_bar();

        $this->lang->load($lang, $lang);
        $this->load->view('dashboard/header', $data, $lang);
        $this->load->view('register-landing', $lang);
        $this->load->view('dashboard/footer', $lang);
    }

    public function checkEmailExists()
    {
        $userId = $this->input->post("userId");
        $email = $this->input->post("email");

        if (empty($userId)) {
            $result = $this->Register_model->checkEmailExists($email);
        } else {
            $result = $this->Register_model->checkEmailExists($email, $userId);
        }

        if (empty($result)) {
            echo("true");
        } else {
            echo("false");
        }
    }


    public function RegistersMe()
    {
        $fname = $this->input->post('fname');
        $sname = $this->input->post('sname');
        $email = $this->input->post('email');
        $password = $this->input->post('password');
        $confirm_password = $this->input->post('cpassword');

        $created_at = date("Y-m-d H:i:s");
        
        
        $yourbrowser= $_SERVER['HTTP_USER_AGENT'];
        //insert the user registration details into database
        if ($password == $confirm_password) {
            $userdata = array(
                'name' => $fname . ' ' . $sname,
                'email' => $email,
                'password' => getHashedPassword($password),
                'roleID' => 4,
                'createdDtm' => $created_at,
                'yourbrowser'=> $yourbrowser,
            );

            // insert form data into database
            //if ($this->captcha_invis()&&$this->db->insert('tbl_users', $userdata))
            if ($this->db->insert('tbl_users', $userdata)) {
                $adddata = array(
                    'first_name' => $fname,
                    'last_name' => $sname,
                    'created_at' => $created_at,
                    'userID' => $this->db->insert_id(),
                );
                if ($this->Register_model->insertstudent($adddata)) {
                    $this->Register_model->sendVerifyEmail($email);

                    //send email to role admin
                    $data2 = array();
                    $data2['subject'] = 'MyCourseLive New Tutor Registration';  //email subject

                    //$data2['message'] ='แจ้งเตือนมีติวเตอร์สมัครใหม่ ชื่อ : '.$fname.' '.$sname.'<br>อีเมล์ : '.$email.'</br>'.'เมื่อวันที่ : '.$created_at;
                    $data2['message'] = "";
                    $this->Register_model->sendAdminEmail($data2);


                    $this->session->set_flashdata('msg', '<div class="alert alert-success text-center">Successfully registered. Please confirm the mail that has been sent to your email.' .
                        '<br/><span id="resend-email" style="cursor:pointer;" onclick="ResendEmail()"><u>Resend</u></span></div>' .
                        '<input type="hidden" id="user-email" value="' . $email . '"/>');
                    redirect('Register');
                } else {
                    $this->session->set_flashdata('msg', '<div class="alert alert-danger text-center">Oops! Error.  Please try again later !!!</div>' .
                        '<br/><span id="resend-email" style="cursor:pointer;" onclick="ResendEmail()"><u>Resend</u></span></div>');
                    redirect('Register');
                }
            } else {
                // error
                $this->session->set_flashdata('msg', '<div class="alert alert-danger text-center">Oops! Error.  Please try again later!!!</div>');
                redirect('Register');
            }
        } else {
            $this->session->set_flashdata('msg', '<div class="alert alert-danger text-center">กรุณากรอกข้อมูล Password ให้เหมือนกัน</div>');
            redirect('Register');
        }
    }

    public function confirmEmail()
    {
        $email = $this->input->get('email');
        $verifycode = $this->input->get('verifycode');
        if ($this->Register_model->verifyEmail($email, $verifycode)) {
            $this->session->set_flashdata('error', '<div class="alert alert-success text-center">Email address is confirmed. Please login to the system</div>');
            redirect('Login');
        } else {
            $this->session->set_flashdata('error', '<div class="alert alert-danger text-center">Email address is not confirmed. Please try to re-register.</div>');
            redirect('Login');
        }
    }

    public function captcha()
    {
        if ($_SERVER['REMOTE_ADDR'] == '127.0.0.1') {
            return true;
        } else {
            $captcha = $_POST["g-recaptcha-response"];
            $response = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=6Lf1AW4UAAAAANYIdI6CUdsOlWsWQ7BhWGdjSy_S&response=" . $captcha . "&remoteip=" . $_SERVER['REMOTE_ADDR']);
            $obj = json_decode($response);
            return $obj->success;
        }
    }
    public function captcha_invis()
    {
        if ($_SERVER['REMOTE_ADDR'] == '127.0.0.1') {
            return true;
        } else {
            $captcha = $_POST["g-recaptcha-response"];
            $response = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=6Lfd1nEUAAAAAKzV2HOMLMp-BXhDRHoZ4-9YT0q3&response=" . $captcha . "&remoteip=" . $_SERVER['REMOTE_ADDR']);
            $obj = json_decode($response);
            return $obj->success;
        }
    }

    public function change($type)
    {
        $this->session->set_userdata('lang', $type);
        $page = $this->input->get('page');

        $url = $_SERVER['REQUEST_URI'];
        $url =  str_replace("/Register/change/english?page=/", "", $url);
        $url =  str_replace("/Register/change/thailand?page=/", "", $url);
        redirect('https://www.mycourselive.com/'.$url);
    }

    public function Teacher_Register()
    {
        $this->load->helper('url');
        $data = array();
        $data['menu_bar'] = $this->Menu_model->menu_bar();
        $lang = $this->session->userdata('lang') == null ? "thailand" : $this->session->userdata('lang');
        if (empty($_POST)) {
            // if($this->session->userdata('email')&&!$this->Register_model->isTeacherRegister($this->session->userdata('email'))){
            $this->lang->load($lang, $lang);

            $this->load->view('dashboard/header', $data, $lang);
            $this->load->view('Become-tutor-badge', $lang);
            $this->load->view('Become-tutor', $lang);
            $this->load->view('dashboard/footer', $lang);
        // }
            // else{
            //     $this->load->view('dashboard/header',$data);
            //     $this->load->view('Become-tutor-badge');
            //     $this->load->view('dashboard/footer');
            // }
        } else {
            if ($this->captcha()) {
                if (!$this->session->userdata('isLoggedIn')) {
                    $yourbrowser= $_SERVER['HTTP_USER_AGENT'];
                    $userdata = array(
                        'name' => $this->input->post('fname') . ' ' . $this->input->post('sname'),
                        'email' => $this->input->post('email'),
                      //  'profile_pic_string' => $this->input->post('profile_pic_string'),
                        'password' => getHashedPassword($this->input->post('pass')),
                        'roleID' => 3,
                        'createdDtm' => date("Y-m-d H:i:s"),
                        'yourbrowser'=> $yourbrowser,
                    );
                    $fname = $this->input->post('fname');
                    $sname = $this->input->post('sname');
                    $is_createUser = $this->db->insert('tbl_users', $userdata);
                    $user_id = $this->db->insert_id();
                } else {
                    $user_id = $this->session->userdata('userId');
                    $is_createUser = true;
                    $this->db->where('userId', $user_id);
                    $user_db = $this->db->get('tbl_users')->row_array();
                    $userdata = array(
                        'email' => $user_db['email'],
                    );
                    $fname = explode(" ", $user_db['name'])[0];
                    $sname = count(explode(" ", $user_db['name'])) - 1 ? explode(" ", $user_db['name'])[1] : '';

                    $this->db->where('userId', $this->session->userdata('userId'));
                    $this->db->update('tbl_users', array('roleId' => 3));
                    $this->session->set_userdata('role', 3);
                    $this->session->set_userdata('roleText', 'Teacher');
                }

                // insert form data into database
                if ($is_createUser && $this->Register_model->sendVerifyEmail1($userdata['email'])) {
                    if (!$this->session->userdata('isLoggedIn')) {
                        $stu_data = array(
                            'first_name' => $this->input->post('fname'),
                            'last_name' => $this->input->post('sname'),
                            'nick_name' => $this->input->post('nname'),
                            'created_at' => date("Y-m-d H:i:s"),
                            'phone' => $this->input->post('phone'),
                            'userID' => $user_id,
                        );
                    }

                    $zoom_res = $this->zoom->create_user($userdata['email'], $fname, $sname);
                    //print_r($zoom_res);
                    $zoom_id = $zoom_res['status'] ? $zoom_res['id'] : 0;
                    $this->db->where('userId', $user_id);
                    $is_zoom_success = $zoom_id ? $this->db->update('tbl_users', array('zoom_id' => $zoom_id)) : false;



                    if ($is_zoom_success) {
                        $ap = $this->input->post('ap');
                        // Map with db structure
                        $teacher_data = array(
                            "userID" => $user_id,
                            "first_name" => $this->input->post('fname'),
                            "last_name" => $this->input->post('sname'),
                            "nick_name" => $this->input->post('nname'),
                            "first_name_en" => $this->input->post('fname_en'),
                            "last_name_en" => $this->input->post('sname_en'),
                            "nick_name_en" => $this->input->post('nname_en'),
                            "birth_day" => date("Y-m-d H:i", strtotime($this->input->post('dob'))),
                            "address" => $this->input->post('address'),
                         //   "province_id" => $this->input->post('province'),
                          //  "district_id" => $this->input->post('district'),
                        //    "subdistrict_id" => $this->input->post('subdistrict'),
                         //   "post_code" => $this->input->post('postcode'),
                            "phone" => $this->input->post('phone'),
                            "qualification_content" => $this->input->post('qualification'),
                            "experience_content" => $this->input->post('experience'),
                            "vdo_content" => $this->input->post('video'),
                            "remark" => $this->input->post('remark'),
                            "content" => $this->input->post('content'),
                            "status" => "Waiting",
                           // "profile_pic_string" =>$this->input->post('profile_pic_string'),
                            "email" => $this->session->userdata('isLoggedIn') ? $this->session->userdata('email') : $this->input->post('email'),
                        );



                        if (!file_exists('./uploads/users/' . $user_id . "/" .'Profile_picture')) {
                            mkdir('./uploads/users/' . $user_id . "/" . 'Profile_picture', 0777, true);
                        }

                        $length = 10;
                        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
                        $charactersLength = strlen($characters);
                        $randomString = '';
                        for ($i = 0; $i < $length; $i++) {
                            $randomString .= $characters[rand(0, $charactersLength - 1)];
                        }
                        $filename =   $randomString.'.png';

                        $teacher_data['path'] = '/uploads/users/' . $user_id . "/" . 'Profile_picture/';
                        $teacher_data['file_name'] =  $filename;
                        $stu_data['path'] = '/uploads/users/' . $user_id . "/" .'Profile_picture/';
                        $stu_data['file_name'] = $filename;

                        $dataURL = $this->input->post('profile_pic_string');
                        $dataURL = str_replace('data:image/png;base64,', '', $dataURL);
                        $dataURL = str_replace('data:image/jpeg;base64,', '', $dataURL);
                        $dataURL = str_replace(' ', '+', $dataURL);
                        $image = base64_decode($dataURL);
                        file_put_contents('./uploads/users/' . $user_id . "/" . 'Profile_picture/'.$filename, $image);



                        



                       // if (!$this->session->userdata('isLoggedIn')) {
                         //   $this->Register_model->insertstudent($stu_data);

                         //   $this->Register_model->insertTeacher($teacher_data);
                          //  $teacher_id = $this->db->insert_id();
                       // }
                        $this->Register_model->insertTeacher($teacher_data);
                        $teacher_id = $this->db->insert_id();
                        $temp = array();
                        $subjectGroupSend = array();
                        foreach ($ap as $k => $value) {
                            $this->db->where('id', $value);
                            $subGroup = $this->db->get('mcl_subject')->row_array()['subject_group_id'];
                            if (!array_key_exists($subGroup, $temp)) {
                                $temp[$subGroup] = true;
                                array_push($subjectGroupSend, $subGroup);
                            }
                            $this->db->insert('mcl_teachers_aptitude', array('teacher_id' => $teacher_id, 'subject_id' => $value));
                        }
                        $mail_data = array(
                            'subject'       =>'New teacher registerd',
                            'receiver'      =>'',
                            'message'       =>"Please check new registered tutor in admin management system",
                            'subjectGroup'  =>$subjectGroupSend
                        );
                        $this->Register_model->sendSupervisorEmail($mail_data);

                    //  Test data

                        //  $teacher_data = array(
                        //     "first_name"  =>"fna",
                        //     "last_name"  =>"sna",
                        //     "nick_name"  =>"nna",
                        //     "birth_day"  =>"hbd",
                        //     "address"  =>"adrs",
                        //     "province_id"  =>"prvn",
                        //     "district_id"  =>"dstrt",
                        //     "subdistrict_id"  =>"sdstrt",
                        //     "post_code"  => "pcode",
                        //     "phone"  =>"phn",
                        //     "category_id"  =>"cat",
                        //     "qualification_content"  =>"qlfct",
                        //     "experience_content"  =>"exp",
                        //     "vdo_content"  =>"vdo",
                        //     "remark"  =>"rmk",
                        //     "email"  => "hello@ex.com"
                        // );
                        // echo $this->Register_model->insertTeacher($teacher_data);
                    } else {
                       $this->db->where('userId', $user_id);
                       $this->db->delete('tbl_users'); 
                       echo "There're problem with zoom process";
                   }
               } else {
                echo "Create user or send email fail.";
            }
            unset($_POST);
            unset($_FILES);
        } else {
            echo "Captcha fail.";
        }
    }
}


public function whoweare()
{
    $this->load->helper('url');
    $data = array();
    $data['menu_bar'] = $this->Menu_model->menu_bar();
    $lang = $this->session->userdata('lang') == null ? "thailand" : $this->session->userdata('lang');


    $this->lang->load($lang, $lang);

    $this->load->view('dashboard/header', $data, $lang);
    $this->load->view('who-we-are-badge', $lang);
    $this->load->view('who-we-are', $lang);
    $this->load->view('dashboard/footer', $lang);
}
public function blog()
{
    $this->load->helper('url');
    $data = array();
    $data['menu_bar'] = $this->Menu_model->menu_bar();
    $lang = $this->session->userdata('lang') == null ? "thailand" : $this->session->userdata('lang');
    $id = $this->input->get('id');

    $this->lang->load($lang, $lang);

    $this->load->view('dashboard/header', $data, $lang);
    if ($id) {
        $this->load->view('BlogDetail', $lang);
    }else{
        $this->load->view('BlogList', $lang);
    }

    $this->load->view('dashboard/footer', $lang);
}
}

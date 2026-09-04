<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');

class StudentController extends Controller
{
    public function index()
    {
        session_start();
        $_SESSION['student_access'] = true;

        $data['title'] = 'My Student Page';
        $this->call->view('student_home', $data);
    }

    public function profile()
    {
        $student = [
            'student_id' => 'MCC2024-00081',
            'name'       => 'Kent T. Ampoloquio',
            'course'     => 'BSIT',
            'year'       => '3rd Year',
            'section'    => 'F2',
            'email'      => 'ampoloquiokenttalaroc@gmail.com',
            'address'    => 'Brgy. Bulusan, Calapan City, Oriental Mindoro, Philippines',
            'contact_number' => '0994-338-8503',
            'hobbies'  => 'Playing Video Games, Watching Movies, Listening to Music',
            'social_media' => [
                'facebook' => 'https://www.facebook.com/kent.talaroc.ampoloquio',
                'github' => 'https://github.com/Kenttalarocampoloquio',
                'instagram' => 'https://www.instagram.com/ryuu_kxn/'
            ],
        ];

        $this->call->view('student_profile', $student);
    }
}
?>
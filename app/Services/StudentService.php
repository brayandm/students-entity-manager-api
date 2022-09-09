<?php

namespace App\Services;

use App\Models\Student;
class StudentService
{
    private function save(Student $student, $data)
    {
        $student->firstname = $data->firstname;
        $student->lastname = $data->lastname;
        $student->save();
    }

    public function add($data)
    {
        $this->save(new Student(), $data);
    }

    public function get($id)
    {
        return Student::find($id);
    }

    public function getAll()
    {
        return Student::all();
    }

    public function delete($id)
    {
        Student::find($id)->delete();
    }

    public function exists($id)
    {
        return Student::find($id) != NULL;
    }

    public function edit($id, $data)
    {
        $this->save($this->get($id), $data);
    }
}
